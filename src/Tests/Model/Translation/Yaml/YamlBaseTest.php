<?php

namespace Ifraktal\TranslatorBundle\Tests\Model\Translation\Yaml;

use Ifraktal\TranslatorBundle\Model\Translator\Yaml\YamlBase;
use Ifraktal\TranslatorBundle\Tests\IfraktalTestCase;

/**
 * Class YamlBaseTest
 *
 * @package Ifraktal\TranslatorBundle\Tests\Model\Translation\Yaml
 * @author David Amigo <davamigo@gmail.com>
 */
class YamlBaseTest extends IfraktalTestCase
{
    /**
     * Test the constructor
     */
    public function testConstructor()
    {
        // Run the test
        $yaml = new YamlBase();

        // Assertions
        $dumper = $this->getPrivateValue($yaml, 'yamlDumper');
        $this->assertInstanceOf('Symfony\Component\Yaml\Dumper', $dumper);
    }

    /**
     * Test of the prepareYamlArray method
     */
    public function testPrepareYamlArrayWorksFine()
    {
        // Source data
        $source = array(
            'one.one.one'   => '111',
            'one.one.two'   => '112',
            'one.two'       => '12',
            'two.one'       => '21',
            'two.two'       => '22',
            'two.three.one' => '231',
            'two.three.two' => '232',
            'three'         => '3'
        );

        // Run the test
        $yaml = new YamlBase();
        $result = $yaml->prepareYamlArray($source);

        // Expected data
        $expected = array(
            'one'   => array(
                'one'   => array(
                    'one'   => '111',
                    'two'   => '112'
                ),
                'two'   => '12'
            ),
            'two'   => array(
                'one'   => '21',
                'two'   => '22',
                'three' => array(
                    'one'   => '231',
                    'two'   => '232'
                )
            ),
            'three' => '3'
        );

        // Assertions
        $this->assertEquals($expected, $result);
    }

    /**
     * Test of the prepareYamlArray method
     */
    public function testPrepareYamlArrayThrowsAnException()
    {
        // Source data
        $source = array(
            'one'       => '1',
            'one.one'   => '11'
        );

        // Configure the test
        $this->setExpectedException('Ifraktal\TranslatorBundle\Model\Translator\Exception\InvalidResourceException');

        // Run the test
        $yaml = new YamlBase();
        $yaml->prepareYamlArray($source);
    }
}
