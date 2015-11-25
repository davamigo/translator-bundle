<?php

namespace Davamigo\TranslatorBundle\Model\Translator\Session;

use Davamigo\TranslatorBundle\Model\Translator\StorageInterface;
use Davamigo\TranslatorBundle\Model\Translator\Translations;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Translation Storage - Loads and saves the translation data in the session
 *
 * @package Davamigo\TranslatorBundle\Model\Translator\Session
 * @author David Amigo <davamigo@gmail.com>
 * @service davamigo.translator.storage.session
 */
class SessionStorage implements StorageInterface
{
    /** @var SessionInterface */
    private $session;

    /**
     * Constructor
     *
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * Save the translations in the session
     *
     * @param Translations $translations
     * @param string       $key
     * @return bool
     */
    public function save(Translations $translations, $key = self::DEFAULT_SESSION_KEY)
    {
        $rawTranslations = $translations->getRawData();
        $this->session->set($key, $rawTranslations);
        $this->session->save();
        return true;
    }

    /**
     * Load the translations form the session
     *
     * @param string $key
     * @return Translations
     */
    public function load($key = self::DEFAULT_SESSION_KEY)
    {
        $rawTranslations = $this->session->get($key);
        $translations = new Translations();
        $translations->setRawData($rawTranslations);
        return $translations;
    }

    /**
     * Return if the translations are saved in the session
     *
     * @param string $key
     * @return bool
     */
    public function hasValid($key = self::DEFAULT_SESSION_KEY)
    {
        if (!$this->session->has($key)) {
            return false;
        }

        $rawTranslations = $this->session->get($key);
        if (!is_array($rawTranslations)) {
            return false;
        }

        if (!isset($rawTranslations['bundles'])
            || !isset($rawTranslations['domains'])
            || !isset($rawTranslations['locales'])
            || !isset($rawTranslations['bundles'])
            || !isset($rawTranslations['messages'])) {
            return false;
        }

        return true;
    }

    /**
     * Remove from the session the saved translations
     *
     * @param string $key
     * @return bool
     */
    public function reset($key = self::DEFAULT_SESSION_KEY)
    {
        $this->session->remove($key);
        return true;
    }
}
