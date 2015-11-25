<?php

namespace Ifraktal\TranslatorBundle\Model\Translator;

/**
 * Translation Scanner interface
 *
 * @package Ifraktal\TranslatorBundle\Model\Translator
 * @author David Amigo <davamigo@gmail.com>
 */
interface ScannerInterface
{
    /**
     * Scan for all the translations in the app
     *
     * @return Translations
     */
    public function scan();
}
