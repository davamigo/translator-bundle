<?php

namespace Davamigo\TranslatorBundle\Tests\Model\Translation\Yaml;

use Davamigo\TranslatorBundle\Model\Translator\Translations;
use Davamigo\TranslatorBundle\Model\Translator\Yaml\YamlFileCreator;
use Davamigo\TranslatorBundle\Tests\BaseTestCase;

/**
 * Class YamlFileCreatorTest
 *
 * @package Davamigo\TranslatorBundle\Tests\Model\Translation\Yaml
 * @author David Amigo <davamigo@gmail.com>
 */
class YamlFileCreatorTest extends BaseTestCase
{
    /**
     * Test of the createFile() method
     */
    public function testCreateFileWithoutAnyDataExportsNothing()
    {
        // Configure the test
        $yamlFileCreator = $this
            ->getMockBuilder('Davamigo\TranslatorBundle\Model\Translator\Yaml\YamlFileCreator')
            ->setMethods(array('filePutContents'))
            ->getMock();

        $yamlFileCreator
            ->expects($this->exactly(0))
            ->method('filePutContents');

        $translations = new Translations();

        /** @var YamlFileCreator $yamlFileCreator */
        $yamlFileCreator->createFile($translations, 'App', 'messages', 'en', 'text.yml');
    }
    /**
     * Test of the createFile() method
     */
    public function testCreateFileWithDataWorksFine()
    {
        $filename = null;
        $buffer = null;

        // Configure the test
        $yamlFileCreator = $this
            ->getMockBuilder('Davamigo\TranslatorBundle\Model\Translator\Yaml\YamlFileCreator')
            ->setMethods(array('filePutContents'))
            ->getMock();

        $yamlFileCreator
            ->expects($this->once())
            ->method('filePutContents')
            ->will($this->returnCallback(function($f, $b) use (&$filename, &$buffer) {
                $filename = $f;
                $buffer = $b;
                return 0;
            }));

        $translations = $this->getTranslationsTestObject();

        /** @var YamlFileCreator $yamlFileCreator */
        $yamlFileCreator->createFile($translations, 'App', 'messages', 'en', 'text.yml');

        // Expected result
        $expected = '# App/messages.en.yml' . PHP_EOL;
        $expected .= 'app:' . PHP_EOL;
        $expected .= '    name: \'The app name\'' . PHP_EOL;
        $expected .= PHP_EOL . PHP_EOL;

        // Assertions
        $this->assertEquals($expected, $buffer);
        $this->assertEquals('text.yml', $filename);
    }

    /**
     * Returns a translation object for many tests
     *
     * @return Translations
     */
    protected function getTranslationsTestObject()
    {
        $translations = new Translations();
        $translations->addTranslation('App', 'messages', 'en', 'app.name', 'The app name');
        $translations->addTranslation('App', 'messages', 'es', 'app.name', 'La aplicación');
        $translations->addTranslation('App', 'validators', 'en', 'error.not-found', 'Not found');
        $translations->addTranslation('App', 'validators', 'es', 'error.not-found', 'No encontrado');

        return $translations;
    }
}
