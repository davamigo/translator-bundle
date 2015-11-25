<?php

namespace Ifraktal\TranslatorBundle\Model\Translator;

/**
 * Translation Storage interface
 *
 * @package Ifraktal\TranslatorBundle\Model\Translator
 * @author David Amigo <davamigo@gmail.com>
 */
interface StorageInterface
{
    /** Default key */
    const DEFAULT_SESSION_KEY = 'ifraktal.translators';

    /**
     * Save the translations
     *
     * @param Translations $translations
     * @param string       $key
     * @return bool
     */
    public function save(Translations $translations, $key = self::DEFAULT_SESSION_KEY);

    /**
     * Load the translations
     *
     * @param string $key
     * @return Translations
     */
    public function load($key = self::DEFAULT_SESSION_KEY);

    /**
     * Return if the translations are saved
     *
     * @param string $key
     * @return bool
     */
    public function hasValid($key = self::DEFAULT_SESSION_KEY);

    /**
     * Remove the saved translations
     *
     * @param string $key
     * @return bool
     */
    public function reset($key = self::DEFAULT_SESSION_KEY);
}
