<?php

namespace Ifraktal\TranslatorBundle\Model\Translator;

use Ifraktal\TranslatorBundle\Model\Translator\Exception\InvalidArgumentException;
use Ifraktal\TranslatorBundle\Model\Translator\Exception\InvalidClassException;
use Ifraktal\TranslatorBundle\Model\Translator\Exception\NotImplementedException;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogueInterface;

/**
 * Translation Scanner
 *
 * @package Ifraktal\TranslatorBundle\Model\Translator
 * @author David Amigo <davamigo@gmail.com>
 * @service ifraktal.translator.scanner
 */
class Scanner implements ScannerInterface
{
    /** @var array */
    protected $bundles;

    /** @var string */
    protected $appFolder;

    /** @var array */
    protected $fileLoaders;

    /** The translation folder inside a bundle */
    const TRANSLATION_RESOURCE_FOLDER = '/Resources/translations';

    /**
     * Constructor
     *
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->bundles = $kernel->getBundles();
        $this->appFolder = $this->realPath($kernel->getRootDir());
        $this->fileLoaders = array();
        $this->addFileLoader('php', 'Symfony\Component\Translation\Loader\PhpFileLoader');
        $this->addFileLoader('yml', 'Symfony\Component\Translation\Loader\YamlFileLoader');
        $this->addFileLoader('xlf', 'Symfony\Component\Translation\Loader\XliffFileLoader');
    }

    /**
     * Scan for all the translations in the app
     *
     * @return Translations
     */
    public function scan()
    {
        $translations = new Translations();

        // Scan for the global translations in the app folder.
        $translations->merge(
            $this->scanBundle('App', $this->appFolder)
        );

        /** @var BundleInterface $bundle */
        foreach ($this->bundles as $bundleName => $bundle) {
            // Scan for the bundle translations
            $translations->merge(
                $this->scanBundle($bundleName, $bundle->getPath())
            );
        }

        return $translations;
    }

    /**
     * @param string                 $name
     * @param LoaderInterface|string $loader
     * @return $this
     * @throws InvalidArgumentException
     */
    public function addFileLoader($name, $loader)
    {
        if (!$name || !$loader) {
            throw new InvalidArgumentException('Invalid argument. Required [name, loader]');
        }

        $this->fileLoaders[$name] = $loader;
        return $this;
    }

    /**
     * Get loader object
     *
     * @param string $loader
     * @return LoaderInterface
     * @throws InvalidArgumentException
     * @throws NotImplementedException
     * @throws InvalidClassException
     */
    public function getFileLoader($loader)
    {
        if (!$loader) {
            throw new InvalidArgumentException('Invalid argument. Required [loader]');
        }

        if (!array_key_exists($loader, $this->fileLoaders)) {
            throw new NotImplementedException('Loader ' . $loader . ' not implemented!');
        }

        $loaderObj = $this->fileLoaders[$loader];
        if (is_string($loaderObj)) {
            $loaderObj = new $loaderObj();
            $this->fileLoaders[$loader] = $loaderObj;
        }

        if (!($loaderObj instanceof LoaderInterface)) {
            throw new InvalidClassException(
                'The loader object must be an instance of LoaderInterface. ' . get_class($loaderObj) . 'isn\'t'
            );
        }

        return $loaderObj;
    }

    /**
     * Scan for all the translations in a bundle
     *
     * @param string $bundleName
     * @param string $bundlePath
     * @return Translations
     */
    protected function scanBundle($bundleName, $bundlePath)
    {
        $translations = new Translations();

        $resourcesFolder = $this->realPath($bundlePath . static::TRANSLATION_RESOURCE_FOLDER);
        if ($this->isDir($resourcesFolder)) {
            $dir = $this->scanDir($resourcesFolder);
            foreach ($dir as $fileName) {
                $filePath = $this->realPath($resourcesFolder . '/' . $fileName);
                if ($filePath && $this->isFile($filePath)) {
                    $translations->merge(
                        $this->scanFile($bundleName, $resourcesFolder, $fileName)
                    );
                }
            }
        }
        return $translations;
    }

    /**
     * Scan for all the translations in a file (yml, xlf or php)
     *
     * @param string $bundleName
     * @param string $resourcesFolder
     * @param string $fileName
     * @return Translations
     * @throws NotImplementedException
     */
    protected function scanFile($bundleName, $resourcesFolder, $fileName)
    {
        $translations = new Translations();

        list($domain, $locale, $loader) = explode('.', $fileName);
        if ($domain && $locale && $loader) {
            $loaderObj = $this->getFileLoader($loader);
            $resource = $resourcesFolder . '/' . $fileName;

            /** @var MessageCatalogueInterface $catalogue */
            $catalogue = $loaderObj->load($resource, $locale, $domain);

            $translations->addCatalogue($bundleName, $resourcesFolder, $catalogue);
        }

        return $translations;
    }

    /**
     * Aux. wrapper function to allow unit testing.
     *
     * @param string $path
     * @return string
     */
    protected function realPath($path)
    {
        return @realpath($path);
    }

    /**
     * Aux. wrapper function to allow unit testing.
     *
     * @param $path
     * @return bool
     */
    protected function isDir($path)
    {
        return @is_dir($path);
    }

    /**
     * Aux. wrapper function to allow unit testing.
     *
     * @param $path
     * @return bool
     */
    protected function isFile($path)
    {
        return @is_file($path);
    }

    /**
     * Aux. wrapper function to allow unit testing.
     *
     * @param string   $path
     * @param int      $order
     * @return array
     */
    protected function scanDir($path, $order = null)
    {
        return @scandir($path, $order);
    }
}
