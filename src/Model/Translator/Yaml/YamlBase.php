<?php

namespace Ifraktal\TranslatorBundle\Model\Translator\Yaml;

use Ifraktal\TranslatorBundle\Model\Translator\Exception\ComponentRequiredException;
use Ifraktal\TranslatorBundle\Model\Translator\Exception\InvalidResourceException;
use Symfony\Component\Yaml\Dumper as YamlDumper;

/**
 * Base class to Yaml services
 *
 * @package Ifraktal\TranslatorBundle\Model\Translator\Yaml
 * @author David Amigo <davamigo@gmail.com>
 */
class YamlBase
{
    /** @var YamlDumper */
    protected $yamlDumper;

    /**
     * Constructor
     *
     * @throws ComponentRequiredException
     */
    public function __construct()
    {
        if (!class_exists('Symfony\Component\Yaml\Dumper')) {
            throw new ComponentRequiredException('Symfony Yaml component required.');
        }

        $this->yamlDumper = new YamlDumper();
    }

    /**
     * Prepares the array translations data to Yaml dumper. Converts array( "one.long.string" => "some translation" )
     * to array( "one" => array( "long" => array( "sting" => "some value" )))
     *
     * @param array $messages
     * @return array
     * @throws InvalidResourceException
     */
    public function prepareYamlArray(array $messages)
    {
        $result = array();
        foreach ($messages as $resource => $translation) {
            $pos = strpos($resource, ' ');
            if (false !== $pos) {
                $result[$resource] = $translation;
            } else {
                $pointer = &$result;
                $parts = explode('.', $resource);
                foreach ($parts as $part) {
                    if (!is_array($pointer)) {
                        throw new InvalidResourceException('Invalid Yaml resource ' . $resource);
                    } elseif (!isset($pointer[$part])) {
                        $pointer[$part] = array();
                    }
                    $pointer = &$pointer[$part];
                }
                $pointer = $translation;
            }
        }

        return $result;
    }
}
