<?php

namespace Davamigo\TranslatorBundle\Model\Translator;

/**
 * Translation Storage interface - Stores the translation data
 *
 * @package Davamigo\TranslatorBundle\Model\Translator
 * @author David Amigo <davamigo@gmail.com>
 */
interface StorageInterface
{
    /** Default key */
    const DEFAULT_KEY = 'davamigo.translations';

    /**
     * Save the translations
     *
     * @param Translations $translations
     * @param string       $key
     * @return bool
     */
    public function save(Translations $translations, $key = self::DEFAULT_KEY);

    /**
     * Load the translations
     *
     * @param string $key
     * @return Translations
     */
    public function load($key = self::DEFAULT_KEY);

    /**
     * Return if the translations are saved
     *
     * @param string $key
     * @return bool
     */
    public function hasValid($key = self::DEFAULT_KEY);

    /**
     * Remove the saved translations
     *
     * @param string $key
     * @return bool
     */
    public function reset($key = self::DEFAULT_KEY);
}
