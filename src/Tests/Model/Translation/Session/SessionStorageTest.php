<?php

namespace Ifraktal\TranslatorBundle\Tests\Model\Translation\Session;

use Ifraktal\TranslatorBundle\Model\Translator\Session\SessionStorage;
use Ifraktal\TranslatorBundle\Model\Translator\Translations;
use Ifraktal\TranslatorBundle\Tests\IfraktalTestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class SessionStorageTest
 *
 * @package Ifraktal\TranslatorBundle\Tests\Model\Translation\Session
 * @author David Amigo <davamigo@gmail.com>
 */
class SessionStorageTest extends IfraktalTestCase
{
    /** @var SessionInterface */
    protected $sessionMock;

    /** @var SessionStorage */
    protected $sessionStorage;

    /**
     * Test of the save method
     */
    public function testSave()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject $sessionMock */
        $sessionMock = $this->sessionMock;

        // Configure the test
        $sessionMock->expects($this->once())->method('set');
        $sessionMock->expects($this->once())->method('save');

        // Run the test
        $translations = new Translations();
        $result = $this->sessionStorage->save($translations);

        // Assertions
        $this->assertTrue($result);
    }

    /**
     * Test of the load method
     */
    public function testLoad()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject $sessionMock */
        $sessionMock = $this->sessionMock;

        // Configure the test
        $sessionMock
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(array(
                'bundles'   => array(),
                'domains'   => array(),
                'locales'   => array(),
                'files'     => array(),
                'messages'  => array()
            )));

        // Run the test
        $translations = $this->sessionStorage->load();

        // Assertions
        $this->assertInstanceOf('Ifraktal\TranslatorBundle\Model\Translator\Translations', $translations);
    }

    /**
     * Test of the hasValid method
     */
    public function testHasValidWithValidData()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject $sessionMock */
        $sessionMock = $this->sessionMock;

        // Configure the test
        $sessionMock
            ->expects($this->once())
            ->method('has')
            ->will($this->returnValue(true));

        $sessionMock
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(array(
                'bundles'   => array(),
                'domains'   => array(),
                'locales'   => array(),
                'files'     => array(),
                'messages'  => array()
            )));

        // Run the test
        $result = $this->sessionStorage->hasValid();

        // Assertions
        $this->assertTrue($result);
    }

    /**
     * Test of the hasValid method
     */
    public function testHasValidWithInvalidData()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject $sessionMock */
        $sessionMock = $this->sessionMock;

        // Configure the test
        $sessionMock
            ->expects($this->once())
            ->method('has')
            ->will($this->returnValue(true));

        $sessionMock
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(array(
                'something'
            )));

        // Run the test
        $result = $this->sessionStorage->hasValid();

        // Assertions
        $this->assertFalse($result);
    }

    /**
     * Test of the hasValid method
     */
    public function testHasValidWithInvalidContent()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject $sessionMock */
        $sessionMock = $this->sessionMock;

        // Configure the test
        $sessionMock
            ->expects($this->once())
            ->method('has')
            ->will($this->returnValue(true));

        $sessionMock
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(null));

        // Run the test
        $result = $this->sessionStorage->hasValid();

        // Assertions
        $this->assertFalse($result);
    }

    /**
     * Test of the hasValid method
     */
    public function testHasValidWithInvalidKey()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject $sessionMock */
        $sessionMock = $this->sessionMock;

        // Configure the test
        $sessionMock
            ->expects($this->once())
            ->method('has')
            ->will($this->returnValue(false));

        // Run the test
        $result = $this->sessionStorage->hasValid();

        // Assertions
        $this->assertFalse($result);
    }

    /**
     * Test of the reset method
     */
    public function testReset()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject $sessionMock */
        $sessionMock = $this->sessionMock;

        // Configure the test
        $sessionMock
            ->expects($this->once())
            ->method('remove');

        // Run the test
        $result = $this->sessionStorage->reset();

        // Assertions
        $this->assertTrue($result);
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->sessionMock = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\Session\SessionInterface')
            ->getMock();

        $this->sessionStorage = new SessionStorage($this->sessionMock);
    }
}
