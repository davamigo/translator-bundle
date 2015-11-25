<?php

namespace Davamigo\TranslatorBundle\Model\Translator\Yaml;

use Davamigo\TranslatorBundle\Model\Translator\Exception\ExporterException;
use Davamigo\TranslatorBundle\Model\Translator\Exception\InvalidResourceException;
use Davamigo\TranslatorBundle\Model\Translator\ExporterInterface;
use Davamigo\TranslatorBundle\Model\Translator\Translations;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Service to export translations in yaml format
 *
 * @package Davamigo\TranslatorBundle\Model\Translator\Yaml
 * @author David Amigo <davamigo@gmail.com>
 * @service davamigo.translator.exporter.yaml
 */
class YamlExporter extends YamlBase implements ExporterInterface
{
    /**
     * Export the translations
     *
     * @param Translations $translations The translations to export
     * @param array        $bundles List of bundles (empty array: all)
     * @param array        $domains List of domains (empty array: all)
     * @param array        $locales List of locales (empty array: all)
     * @param string       $filename The filename to export
     * @return Response
     * @throws ExporterException
     */
    public function export(
        Translations $translations,
        array $bundles = array(),
        array $domains = array(),
        array $locales = array(),
        $filename = null
    ) {
        // Current date and time
        $now = new \DateTime();

        // Validate the params
        if (!count($bundles)) {
            $bundles = $translations->getBundles();
        }

        if (!count($domains)) {
            $domains = $translations->getDomains();
        }

        if (!count($locales)) {
            $locales = $translations->getLocales();
        }

        if (null == $filename) {
            $filename = 'davamigo_translator_' . $now->format('Y-m-d_H-i-s') . '.yml';
        }

        // Create the Yaml file
        $buffer = '';
        foreach ($bundles as $bundle) {
            foreach ($domains as $domain) {
                foreach ($locales as $locale) {
                    $messages = $translations->getMessages($bundle, $domain, $locale);

                    if (count($messages) > 0) {
                        $buffer .= '# ' . str_repeat('-', 80) . PHP_EOL;
                        $buffer .= '# ' . $bundle . '/' . $domain . '.' . $locale . '.yml' . PHP_EOL;
                        try {
                            $data = $this->prepareYamlArray($messages);
                            $buffer .= $this->yamlDumper->dump($data, 100);
                        } catch (InvalidResourceException $exc) {
                            $buffer .= $exc->getMessage();
                        }
                        $buffer .= PHP_EOL . PHP_EOL;
                    }
                }
            }
        }

        // Create the response
        return $this->createResponse($buffer, $filename);
    }

    /**
     * Creates and configures a response.
     *
     * @param string $buffer
     * @param string $filename
     * @return Response
     */
    protected function createResponse($buffer, $filename)
    {
        // Create the response object
        $response = new Response($buffer);

        // Adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename
        );

        $response->headers->set('Content-Type', 'text/yml; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }
}
