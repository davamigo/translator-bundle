<?php

namespace Ifraktal\TranslatorBundle\Model\Translator;

use Ifraktal\TranslatorBundle\Model\Translator\Exception\ImporterException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Importer interface
 *
 * @package Ifraktal\TranslatorBundle\Model\Translator
 * @author David Amigo <davamigo@gmail.com>
 */
interface ImporterInterface
{
    /**
     * Import the translations
     *
     * @param UploadedFile|string   $filename       The filename to read
     * @param Translations          $translations   The translations to export
     * @param array                 $bundles        List of bundles (empty array: all)
     * @param array                 $domains        List of domains (empty array: all)
     * @param array                 $locales        List of locales (empty array: all)
     * @return Translations
     * @throws ImporterException
     */
    public function import(
        $filename,
        Translations $translations,
        array $bundles = array(),
        array $domains = array(),
        array $locales = array()
    );
}
