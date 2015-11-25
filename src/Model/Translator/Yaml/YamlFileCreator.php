<?php

namespace Ifraktal\TranslatorBundle\Model\Translator\Yaml;

use Ifraktal\TranslatorBundle\Model\Translator\Exception\FileCreatorException;
use Ifraktal\TranslatorBundle\Model\Translator\FileCreatorInterface;
use Ifraktal\TranslatorBundle\Model\Translator\Translations;

/**
 * Service to export translations in yaml format
 *
 * @package Ifraktal\TranslatorBundle\Model\Translator\Yaml
 * @author David Amigo <davamigo@gmail.com>
 * @service ifraktal.translator.file-creator.yaml
 */
class YamlFileCreator extends YamlBase implements FileCreatorInterface
{
    /**
     * Export the translations
     *
     * @param Translations $translations The translations object
     * @param string $bundle The bundle
     * @param string $domain The domain
     * @param string $locale The locale
     * @param string $filename The filename to export
     * @return $this
     * @throws FileCreatorException
     */
    public function createFile(Translations $translations, $bundle, $domain, $locale, $filename)
    {
        $messages = $translations->getMessages($bundle, $domain, $locale);
        if (count($messages) > 0) {
            $data = $this->prepareYamlArray($messages);

            $buffer = '# ' . $bundle . '/' . $domain . '.' . $locale . '.yml' . PHP_EOL;
            $buffer .= $this->yamlDumper->dump($data, 100);
            $buffer .= PHP_EOL . PHP_EOL;

            $this->filePutContents($filename, $buffer);
        }

        return $this;
    }


    /**
     * Aux. wrapper function to allow unit testing.
     *
     * @param string $filename
     * @param string $buffer
     * @return int
     */
    protected function filePutContents($filename, $buffer)
    {
        return file_put_contents($filename, $buffer);
    }
}
