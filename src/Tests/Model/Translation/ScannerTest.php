<?php

namespace Ifraktal\TranslatorBundle\Tests\Model\Translation;

use Ifraktal\TranslatorBundle\Model\Translator\Scanner;
use Ifraktal\TranslatorBundle\Model\Translator\Translations;
use Ifraktal\TranslatorBundle\Tests\IfraktalTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class ScannerTest
 *
 * @package Ifraktal\TranslatorBundle\Tests\Model\Translation
 * @author David Amigo <davamigo@gmail.com>
 */
class ScannerTest extends IfraktalTestCase
{
    /** @var string */
    protected $testDir;

    /** @var KernelInterface */
    protected $kernelMock;

    /**
     * Test of the constructor
     */
    public function testConstructor()
    {
        // Test data
        $scanner = new Scanner($this->kernelMock);

        // Run the test
        $appFolder = $this->getPrivateValue($scanner, 'appFolder');
        $bundles = $this->getPrivateValue($scanner, 'bundles');

        // Assertions
        $this->assertEquals($this->testDir, $appFolder);
        $this->assertInternalType('array', $bundles);
        $this->assertCount(1, $bundles);
        $this->assertInstanceOf('Symfony\Component\HttpKernel\Bundle\BundleInterface', reset($bundles));
    }

    /**
     * Test of the scan public method
     */
    public function testScan()
    {
        // Test data
        $trans1 = new Translations();
        $trans1->addTranslation('bundle#1', 'domain#1', 'locale#1', 'resource#1', 'translation#1');

        $trans2 = new Translations();
        $trans2->addTranslation('bundle#2', 'domain#2', 'locale#2', 'resource#2', 'translation#2');

        $expected = new Translations();
        $expected->merge($trans1);
        $expected->merge($trans2);

        // Configure the test
        $scanner = $this
            ->getMockBuilder('Ifraktal\TranslatorBundle\Model\Translator\Scanner')
            ->setConstructorArgs(array($this->kernelMock))
            ->setMethods(array('scanBundle'))
            ->getMock();

        $scanner
            ->expects($this->any())
            ->method('scanBundle')
            ->will($this->onConsecutiveCallsArray(array( $trans1, $trans2 )));

        // Run the test
        /** @var Scanner $scanner */
        $result = $scanner->scan();

        // Assertions
        $this->assertEquals($expected, $result);
    }

    /**
     * Test of the scanBundle protected method
     */
    public function testScanBundle()
    {
        // Configure the test
        $scanner = $this
            ->getMockBuilder('Ifraktal\TranslatorBundle\Model\Translator\Scanner')
            ->setConstructorArgs(array($this->kernelMock))
            ->setMethods(array('realPath', 'isDir', 'isFile', 'scanDir', 'scanFile'))
            ->getMock();

        $scanner
            ->expects($this->any())
            ->method('realPath')
            ->will($this->returnArgument(0));

        $scanner
            ->expects($this->any())
            ->method('isDir')
            ->will($this->returnValue(true));

        $scanner
            ->expects($this->any())
            ->method('isFile')
            ->will($this->returnValue(true));

        $scanner
            ->expects($this->any())
            ->method('scanDir')
            ->will($this->returnValue(array( 'test.yml ')));

        $scanner
            ->expects($this->any())
            ->method('scanFile')
            ->will($this->returnCallback(function($bundleName, $resourcesFolder, $fileName) {
                $translations = new Translations();
                $translations->addFile($bundleName, $resourcesFolder, $fileName);
                $translations->addTranslation($bundleName, 'messages', 'en', 'app_name', 'The app name');
                $translations->addTranslation($bundleName, 'messages', 'es', 'app_name', 'La aplicación');
                return $translations;
            }));

        // Run the test
        /** @var Translations $translations */
        $translations = $this->runPrivateMethod($scanner, 'scanBundle', array('App', $this->testDir));

        // Expected result
        $expected = array( array('App', 'messages', 'app_name', 'The app name', 'La aplicación') );
        $locales = array( 'en', 'es' );
        $domains = array( 'messages' );

        // Assertions
        $this->assertEquals($expected, $translations->asArray(false));
        $this->assertEquals($locales, $translations->getLocales());
        $this->assertEquals($domains, $translations->getDomains());
    }

    /**
     * Test of the scanFile protected method
     */
    public function testScanFile()
    {
        // Configure the test
        $scanner = $this
            ->getMockBuilder('Ifraktal\TranslatorBundle\Model\Translator\Scanner')
            ->setConstructorArgs(array($this->kernelMock))
            ->setMethods(array('getFileLoader'))
            ->getMock();

        $loader = $this
            ->getMockBuilder('Symfony\Component\Translation\Loader\LoaderInterface')
            ->setMethods(array('load'))
            ->getMock();

        $catalogue = $this
            ->getMockBuilder('Symfony\Component\Translation\MessageCatalogueInterface')
            ->getMock();

        $scanner
            ->expects($this->once())
            ->method('getFileLoader')
            ->will($this->returnValue($loader));

        $loader
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($catalogue));

        $catalogue
            ->expects($this->once())
            ->method('getLocale')
            ->will($this->returnValue('en'));

        $catalogue
            ->expects($this->once())
            ->method('getDomains')
            ->will($this->returnValue(array('messages')));

        $catalogue
            ->expects($this->once())
            ->method('getResources')
            ->will($this->returnValue(array()));

        $catalogue
            ->expects($this->once())
            ->method('all')
            ->will($this->returnValue(array('app_name' => 'The app name')));

        // Run the test
        /** @var Translations $translations */
        $translations = $this->runPrivateMethod($scanner, 'scanFile', array('App', $this->testDir, 'messages.en.yml'));

        // Expected result
        $expected = array( array('App', 'messages', 'app_name', 'The app name') );
        $locales = array( 'en' );
        $domains = array( 'messages' );

        // Assertions
        $this->assertEquals($expected, $translations->asArray(false));
        $this->assertEquals($locales, $translations->getLocales());
        $this->assertEquals($domains, $translations->getDomains());
    }

    /**
     * Test of the addFileLoader method
     */
    public function testAddFileLoaderChangesTheLoader()
    {
        // Run the test
        $scanner = new Scanner($this->kernelMock);
        $scanner->addFileLoader('yml', 'MyTestClass');

        // Assertions
        $loaders = $this->getPrivateValue($scanner, 'fileLoaders');
        $this->assertInternalType('array', $loaders);
        $this->assertEquals('MyTestClass', $loaders['yml']);
    }

    /**
     * Test of the addFileLoader method
     */
    public function testAddFileLoaderThrowsAnException()
    {
        // Configure the test
        $this->setExpectedException('Ifraktal\TranslatorBundle\Model\Translator\Exception\InvalidArgumentException');

        // Run the test
        $scanner = new Scanner($this->kernelMock);
        $scanner->addFileLoader('yml', '');
    }

    /**
     * Test of the getFileLoader method
     */
    public function testGetBuiltInFileLoaderEndsProperly()
    {
        // Run the test
        $scanner = new Scanner($this->kernelMock);
        $loader = $scanner->getFileLoader('yml');

        // Assertions
        $this->assertInternalType('object', $loader);
        $this->assertInstanceOf('Symfony\Component\Translation\Loader\LoaderInterface', $loader);
    }

    /**
     * Test of the getFileLoader method
     */
    public function testGetNewFileLoaderEndsProperly()
    {
        // Mocks
        $loaderMock = $this
            ->getMockBuilder('Symfony\Component\Translation\Loader\LoaderInterface')
            ->getMock();

        // Run the test
        $scanner = new Scanner($this->kernelMock);
        $scanner->addFileLoader('new', $loaderMock);
        $loader = $scanner->getFileLoader('new');

        // Assertions
        $this->assertEquals($loaderMock, $loader);
    }

    /**
     * Test of the getFileLoader method
     */
    public function testGetFileLoaderThrowsInvalidArgumentException()
    {
        // Configure the test
        $this->setExpectedException('Ifraktal\TranslatorBundle\Model\Translator\Exception\InvalidArgumentException');

        // Run the test
        $scanner = new Scanner($this->kernelMock);
        $scanner->getFileLoader('');
    }

    /**
     * Test of the getFileLoader method
     */
    public function testGetFileLoaderThrowsNotImplementedException()
    {
        // Configure the test
        $this->setExpectedException('Ifraktal\TranslatorBundle\Model\Translator\Exception\NotImplementedException');

        // Run the test
        $scanner = new Scanner($this->kernelMock);
        $scanner->getFileLoader('bad');
    }

    /**
     * Test of the getFileLoader method
     */
    public function testGetFileLoaderThrowsInvalidClassException()
    {
        // Configure the test
        $this->setExpectedException('Ifraktal\TranslatorBundle\Model\Translator\Exception\InvalidClassException');

        // Run the test
        $scanner = new Scanner($this->kernelMock);
        $scanner->addFileLoader('new', '\DateTime');
        $scanner->getFileLoader('new');
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->testDir = sys_get_temp_dir();

        $bundleMock = $this
            ->getMockBuilder('Symfony\Component\HttpKernel\Bundle\BundleInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $bundleMock
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue($this->testDir));

        $this->kernelMock = $this
            ->getMockBuilder('Symfony\Component\HttpKernel\KernelInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->kernelMock
            ->expects($this->once())
            ->method('getRootDir')
            ->will($this->returnValue($this->testDir));

        $this->kernelMock
            ->expects($this->once())
            ->method('getBundles')
            ->will($this->returnValue(array( 'bundle#1' => $bundleMock )));
    }
}
