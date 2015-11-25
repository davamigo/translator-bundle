<?php

namespace Ifraktal\TranslatorBundle\Model\Translator;

use Ifraktal\TranslatorBundle\Model\Translator\Exception\FileCreatorException;

/**
 * File creator interface
 *
 * @package Ifraktal\TranslatorBundle\Model\Translator
 * @author David Amigo <davamigo@gmail.com>
 */
interface FileCreatorInterface
{
    /**
     * Export the translations
     *
     * @param Translations  $translations   The translations object
     * @param string        $bundle         The bundle
     * @param string        $domain         The domain
     * @param string        $locale         The locale
     * @param string        $filename       The filename to export
     * @return $this
     * @throws FileCreatorException
     */
    public function createFile(Translations $translations, $bundle, $domain, $locale, $filename);
}
