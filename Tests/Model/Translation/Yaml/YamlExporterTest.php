<?php

namespace Davamigo\TranslatorBundle\Tests\Model\Translation\Yaml;

use Davamigo\TranslatorBundle\Model\Translator\Translations;
use Davamigo\TranslatorBundle\Model\Translator\Yaml\YamlExporter;
use Davamigo\TranslatorBundle\Tests\BaseTestCase;

/**
 * Class YamlExporterTest
 *
 * @package Davamigo\TranslatorBundle\Tests\Model\Translation\Yaml
 * @author David Amigo <davamigo@gmail.com>
 */
class YamlExporterTest extends BaseTestCase
{
    /**
     * Test of the export() method
     */
    public function testExportWithoutAnyDataExportsNothing()
    {
        // Configure the test
        $yamlExporter = $this
            ->getMockBuilder('Davamigo\TranslatorBundle\Model\Translator\Yaml\YamlExporter')
            ->setMethods(array('createResponse'))
            ->getMock();

        $yamlExporter
            ->expects($this->once())
            ->method('createResponse')
            ->will($this->returnArgument(0));

        $translations = new Translations();

        /** @var YamlExporter $yamlExporter */
        $result = $yamlExporter->export($translations);

        // Expected result
        $expected = '';

        // Assertions
        $this->assertEquals($expected, $result);
    }

    /**
     * Test of the export() method
     */
    public function testExportAllDataExportsEverything()
    {
        // Configure the test
        $yamlExporter = $this
            ->getMockBuilder('Davamigo\TranslatorBundle\Model\Translator\Yaml\YamlExporter')
            ->setMethods(array('createResponse'))
            ->getMock();

        $yamlExporter
            ->expects($this->once())
            ->method('createResponse')
            ->will($this->returnArgument(0));

        $translations = $this->getTranslationsTestObject();

        /** @var YamlExporter $yamlExporter */
        $result = $yamlExporter->export($translations);

        // Expected result
        $expected = '# --------------------------------------------------------------------------------' . PHP_EOL;
        $expected .= '# App/messages.en.yml' . PHP_EOL;
        $expected .= 'app:' . PHP_EOL;
        $expected .= '    name: \'The app name\'' . PHP_EOL;
        $expected .= PHP_EOL . PHP_EOL;
        $expected .= '# --------------------------------------------------------------------------------' . PHP_EOL;
        $expected .= '# App/messages.es.yml' . PHP_EOL;
        $expected .= 'app:' . PHP_EOL;
        $expected .= '    name: \'La aplicación\'' . PHP_EOL;
        $expected .= PHP_EOL . PHP_EOL;
        $expected .= '# --------------------------------------------------------------------------------' . PHP_EOL;
        $expected .= '# App/validators.en.yml' . PHP_EOL;
        $expected .= 'error:' . PHP_EOL;
        $expected .= '    not-found: \'Not found\'' . PHP_EOL;
        $expected .= PHP_EOL . PHP_EOL;
        $expected .= '# --------------------------------------------------------------------------------' . PHP_EOL;
        $expected .= '# App/validators.es.yml' . PHP_EOL;
        $expected .= 'error:' . PHP_EOL;
        $expected .= '    not-found: \'No encontrado\'' . PHP_EOL;
        $expected .= PHP_EOL . PHP_EOL;

        // Assertions
        $this->assertEquals($expected, $result);
    }

    /**
     * Test of the export() method
     */
    public function testExportSomeDataExportsSomething()
    {
        // Configure the test
        $yamlExporter = $this
            ->getMockBuilder('Davamigo\TranslatorBundle\Model\Translator\Yaml\YamlExporter')
            ->setMethods(array('createResponse'))
            ->getMock();

        $yamlExporter
            ->expects($this->once())
            ->method('createResponse')
            ->will($this->returnArgument(0));

        $translations = $this->getTranslationsTestObject();

        /** @var YamlExporter $yamlExporter */
        $result = $yamlExporter->export($translations, array('App'), array('messages'), array('en'));

        // Expected result
        $expected = '# --------------------------------------------------------------------------------' . PHP_EOL;
        $expected .= '# App/messages.en.yml' . PHP_EOL;
        $expected .= 'app:' . PHP_EOL;
        $expected .= '    name: \'The app name\'' . PHP_EOL;
        $expected .= PHP_EOL . PHP_EOL;

        // Assertions
        $this->assertEquals($expected, $result);
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
