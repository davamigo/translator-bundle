<?php

namespace Davamigo\TranslatorBundle\Model\Translator;

use Davamigo\TranslatorBundle\Model\Translator\Exception\ExporterException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exporter interface
 *
 * @package Davamigo\TranslatorBundle\Model\Translator
 * @author David Amigo <davamigo@gmail.com>
 */
interface ExporterInterface
{
    /**
     * Export the translations
     *
     * @param Translations  $translations   The translations to export
     * @param array         $bundles        List of bundles (empty array: all)
     * @param array         $domains        List of domains (empty array: all)
     * @param array         $locales        List of locales (empty array: all)
     * @param string        $filename       The filename to export
     * @return Response
     * @throws ExporterException
     */
    public function export(
        Translations $translations,
        array $bundles = array(),
        array $domains = array(),
        array $locales = array(),
        $filename = null
    );
}
