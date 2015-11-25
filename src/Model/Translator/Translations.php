<?php

namespace Ifraktal\TranslatorBundle\Model\Translator;

use Ifraktal\TranslatorBundle\Model\Translator\Exception\InvalidArgumentException;
use Symfony\Component\Config\Resource\ResourceInterface;
use Symfony\Component\Translation\MessageCatalogueInterface;

/**
 * Scanned translations
 *
 * @package Ifraktal\TranslatorBundle\Model\Translator
 * @author David Amigo <davamigo@gmail.com>
 */
class Translations
{
    /** @var  array */
    protected $bundles;

    /** @var  array */
    protected $domains;

    /** @var  array */
    protected $locales;

    /** @var array */
    protected $files;

    /** @var  array */
    protected $messages;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->bundles  = array();
        $this->domains  = array();
        $this->locales  = array();
        $this->files    = array();
        $this->messages = array();
    }

    /**
     * Add translation
     *
     * @param string $bundle
     * @param string $domain
     * @param string $locale
     * @param string $resource
     * @param string $translation
     * @return $this
     * @throws InvalidArgumentException
     */
    public function addTranslation($bundle, $domain, $locale, $resource, $translation)
    {
        if (!$bundle || !$domain || !$locale || !$resource) {
            throw new InvalidArgumentException('All arguments are mandatory.');
        }

        $this->addBundle($bundle);

        $this->addDomain($domain);

        $this->addLocale($locale);

        if (!array_key_exists($bundle, $this->messages)) {
            $this->messages[$bundle] = array();
        }

        if (!array_key_exists($domain, $this->messages[$bundle])) {
            $this->messages[$bundle][$domain] = array();
        }

        if (!array_key_exists($locale, $this->messages[$bundle][$domain])) {
            $this->messages[$bundle][$domain][$locale] = array();
        }

        $this->messages[$bundle][$domain][$locale][$resource] = $translation;
        return $this;
    }

    /**
     * @param string                    $bundle
     * @param string                    $path
     * @param MessageCatalogueInterface $catalogue
     * @return $this
     */
    public function addCatalogue($bundle, $path, MessageCatalogueInterface $catalogue)
    {
        $locale = $catalogue->getLocale();
        $domains = $catalogue->getDomains();
        foreach ($domains as $domain) {
            $messages = $catalogue->all($domain);
            foreach ($messages as $key => $translation) {
                $this->addTranslation($bundle, $domain, $locale, $key, $translation);
            }
        }

        /** @var ResourceInterface $resource */
        foreach ($catalogue->getResources() as $resource) {
            $fileName = basename($resource->getResource());
            $this->addFile($bundle, $path, $fileName);
        }

        return $this;
    }

    /**
     * Merge the data of two Translation objects
     *
     * @param Translations $translations
     * @return $this
     */
    public function merge(Translations $translations)
    {
        foreach ($translations->messages as $bundle => $domains) {
            foreach ($domains as $domain => $locales) {
                foreach ($locales as $locale => $messages) {
                    foreach ($messages as $key => $translation) {
                        $this->addTranslation($bundle, $domain, $locale, $key, $translation);
                    }
                }
            }
        }

        foreach ($translations->files as $bundle => $files) {
            foreach ($files as $path => $fileNames) {
                foreach ($fileNames as $fileName) {
                    $this->addFile($bundle, $path, $fileName);
                }
            }
        }

        return $this;
    }

    /**
     * Sort all the translations data
     *
     * @return $this
     */
    public function sort()
    {
        // Sort bundles
        sort($this->bundles, SORT_STRING | SORT_FLAG_CASE);

        // Sort domains
        sort($this->domains, SORT_STRING | SORT_FLAG_CASE);

        // Sort locales
        sort($this->locales, SORT_STRING | SORT_FLAG_CASE);

        // Sort files
        ksort($this->files, SORT_STRING | SORT_FLAG_CASE);
        foreach (array_keys($this->files) as $bundle) {
            ksort($this->files[$bundle], SORT_STRING | SORT_FLAG_CASE);
            foreach (array_keys($this->files[$bundle]) as $path) {
                sort($this->files[$bundle][$path], SORT_STRING | SORT_FLAG_CASE);
            }
        }

        // Sort messages
        ksort($this->messages, SORT_STRING | SORT_FLAG_CASE);
        foreach (array_keys($this->messages) as $bundle) {
            ksort($this->messages[$bundle], SORT_STRING | SORT_FLAG_CASE);
            foreach (array_keys($this->messages[$bundle]) as $domain) {
                ksort($this->messages[$bundle][$domain], SORT_STRING | SORT_FLAG_CASE);
                foreach (array_keys($this->messages[$bundle][$domain]) as $locale) {
                    ksort($this->messages[$bundle][$domain][$locale], SORT_STRING | SORT_FLAG_CASE);
                }
            }
        }

        return $this;
    }

    /**
     * Get bundles (all)
     *
     * @return array ["bundle-1", "bundle-2", ..., "bundle-n"]
     */
    public function getBundles()
    {
        return $this->bundles;
    }

    /**
     * Get domains (all or by bundle)
     *
     * @param string $bundle
     * @return array ["domain-1", "domain-2", ..., "domain-n"]
     */
    public function getDomains($bundle = null)
    {
        if (!$bundle) {
            return $this->domains;
        }

        if (array_key_exists($bundle, $this->messages)) {
            return array_keys($this->messages[$bundle]);
        }

        return array();
    }

    /**
     * Get locales (all or by bundle and domain)
     *
     * @param string $bundle
     * @param string $domain
     * @return array ["locale-1", "locale-2", ..., "locale-n"]
     */
    public function getLocales($bundle = null, $domain = null)
    {
        if (!$bundle) {
            return $this->locales;
        }

        if ($domain
            && array_key_exists($bundle, $this->messages)
            && array_key_exists($domain, $this->messages[$bundle])) {
            return array_keys($this->messages[$bundle][$domain]);
        }

        return array();
    }

    /**
     * Get resources (by bundle, domain and optionally by locale)
     *
     * @param string $bundle
     * @param string $domain
     * @param string $locale
     * @return array ["resource-1", "resource-2", ..., "resource-n"]
     * @throws InvalidArgumentException
     */
    public function getResources($bundle, $domain, $locale = null)
    {
        if (!$bundle || !$domain) {
            throw new InvalidArgumentException('All arguments are mandatory.');
        }

        if (array_key_exists($bundle, $this->messages)
            && array_key_exists($domain, $this->messages[$bundle])) {

            if (!$locale) {
                $resources = array();
                foreach ($this->messages[$bundle][$domain] as $locale => $messages) {
                    $resources = array_merge($resources, array_keys($messages));
                }

                $resources = array_unique($resources, SORT_STRING);
                sort($resources, SORT_STRING | SORT_FLAG_CASE);

                return $resources;
            }

            if (array_key_exists($locale, $this->messages[$bundle][$domain])) {
                return array_keys($this->messages[$bundle][$domain][$locale]);
            }
        }

        return array();
    }

    /**
     * Get messages (by bundle, domain and locale)
     *
     * @param string $bundle
     * @param string $domain
     * @param string $locale
     * @return array ["resource-1"=>"translation-1", ..., "resource-n"=>"translation-n"]
     * @throws InvalidArgumentException
     */
    public function getMessages($bundle, $domain, $locale)
    {
        if (!$bundle || !$domain || !$locale) {
            throw new InvalidArgumentException('All arguments are mandatory.');
        }

        if (array_key_exists($bundle, $this->messages)
            && array_key_exists($domain, $this->messages[$bundle])
            && array_key_exists($locale, $this->messages[$bundle][$domain])) {
            return $this->messages[$bundle][$domain][$locale];
        }

        return array();
    }

    /**
     * Get all translation (by bundle, domain, locale and resource)
     *
     * @param string $bundle
     * @param string $domain
     * @param string $locale
     * @param string $resource
     * @return string|null
     * @throws InvalidArgumentException
     */
    public function getTranslation($bundle, $domain, $locale, $resource)
    {
        if (!$bundle || !$domain || !$locale || !$resource) {
            throw new InvalidArgumentException('All arguments are mandatory.');
        }

        if (array_key_exists($bundle, $this->messages)
            && array_key_exists($domain, $this->messages[$bundle])
            && array_key_exists($locale, $this->messages[$bundle][$domain])
            && array_key_exists($resource, $this->messages[$bundle][$domain][$locale])) {
            return $this->messages[$bundle][$domain][$locale][$resource];
        }

        return null;
    }

    /**
     * Get an array of translation files.
     *
     * @param array $bundles
     * @param array $domains
     * @param array $locales
     * @return array [{'bundle': str, 'domain': str, 'locale': str, 'folder': str, 'filename': str, 'messages':int}]
     */
    public function getFiles(
        array $bundles = array(),
        array $domains = array(),
        array $locales = array()
    ) {
        if (!count($bundles)) {
            $bundles = $this->bundles;
        }

        if (!count($domains)) {
            $domains = $this->domains;
        }

        if (!count($locales)) {
            $locales = $this->locales;
        }

        $result = array();
        foreach ($bundles as $bundle) {
            if (array_key_exists($bundle, $this->files)) {
                $folders = $this->files[$bundle];
                foreach ($folders as $folder => $files) {
                    foreach ($files as $filename) {
                        $parts = explode('.', $filename);
                        $domain = $parts[0];
                        if (in_array($domain, $domains)) {
                            foreach ($locales as $locale) {
                                $messages = $this->getMessages($bundle, $domain, $locale);
                                if (!empty($messages)) {
                                    $key = implode('|', array($bundle, $domain, $locale));
                                    $file = implode('.', array($domain, $locale, 'yml'));
                                    $result[$key] = array(
                                        'bundle'    => $bundle,
                                        'domain'    => $domain,
                                        'locale'    => $locale,
                                        'folder'    => $folder,
                                        'filename'  => $file,
                                        'messages'  => count($messages)
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Return all the translation as a simple array
     *
     * @param bool $assoc
     * @return array [["bundle-1", "domain-1", "resource-1", "locale-1-trans", ... "locale-n-trans"], ...]
     */
    public function asArray($assoc = false)
    {
        $result = array();

        foreach ($this->getBundles() as $bundle) {
            foreach ($this->getDomains($bundle) as $domain) {
                foreach ($this->getResources($bundle, $domain) as $resource) {
                    $translations = array();

                    foreach ($this->getLocales() as $locale) {
                        $translations[$locale] = $this->getTranslation($bundle, $domain, $locale, $resource);
                    }

                    if ($assoc) {
                        $result[] = array(
                            'bundle'        => $bundle,
                            'domain'        => $domain,
                            'resource'      => $resource,
                            'translations'  => $translations
                        );
                    }
                    else {
                        $item = array(
                            $bundle,
                            $domain,
                            $resource
                        );

                        foreach ($translations as $translation) {
                            $item[] = $translation;
                        }

                        $result[] = $item;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Get all translations data (as an array)
     *
     * @return array
     */
    public function getRawData()
    {
        return array(
            'bundles'   => $this->bundles,
            'domains'   => $this->domains,
            'locales'   => $this->locales,
            'files'     => $this->files,
            'messages'  => $this->messages
        );
    }

    /**
     * Set all translations data (from an array)
     *
     * @param array $data
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setRawData(array $data)
    {
        if (!isset($data['bundles'])) {
            throw new InvalidArgumentException('Key "bundles" not found in array.');
        }

        if (!isset($data['domains'])) {
            throw new InvalidArgumentException('Key "domains" not found in array.');
        }

        if (!isset($data['locales'])) {
            throw new InvalidArgumentException('Key "locales" not found in array.');
        }

        if (!isset($data['files'])) {
            throw new InvalidArgumentException('Key "files" not found in array.');
        }

        if (!isset($data['messages'])) {
            throw new InvalidArgumentException('Key "messages" not found in array.');
        }

        $this->bundles  = $data['bundles'];
        $this->domains  = $data['domains'];
        $this->locales  = $data['locales'];
        $this->files    = $data['files'];
        $this->messages = $data['messages'];

        return $this;
    }

    /**
     * Add bundle
     *
     * @param string $bundle
     * @return $this
     */
    public function addBundle($bundle)
    {
        if (false === array_search($bundle, $this->bundles)) {
            $this->bundles[] = $bundle;
        }

        return $this;
    }

    /**
     * Add domain
     *
     * @param string $domain
     * @return $this
     */
    public function addDomain($domain)
    {
        if (false === array_search($domain, $this->domains)) {
            $this->domains[] = $domain;
        }

        return $this;
    }

    /**
     * Add locale
     *
     * @param string $locale
     * @return $this
     */
    public function addLocale($locale)
    {
        if (false === array_search($locale, $this->locales)) {
            $this->locales[] = $locale;
        }

        return $this;
    }

    /**
     * Add file
     *
     * @param string $bundle
     * @param string $path
     * @param string $fileName
     * @return $this
     * @throws InvalidArgumentException
     */
    public function addFile($bundle, $path, $fileName)
    {
        if (!$bundle || !$path || !$fileName) {
            throw new InvalidArgumentException('All arguments are mandatory.');
        }

        if (!array_key_exists($bundle, $this->files)) {
            $this->files[$bundle] = array();
        }

        if (!array_key_exists($path, $this->files[$bundle])) {
            $this->files[$bundle][$path] = array();
        }

        if (false === array_search($fileName, $this->files[$bundle][$path])) {
            $this->files[$bundle][$path][] = $fileName;
        }

        return $this;
    }
}
