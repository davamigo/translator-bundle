<?php

namespace Davamigo\TranslatorBundle\Tests\Model\Translation\Excel;

use Davamigo\TranslatorBundle\Model\Translator\Excel\ExcelExporter;
use Davamigo\TranslatorBundle\Model\Translator\Translations;
use Davamigo\TranslatorBundle\Tests\BaseTestCase;

/**
 * Class ExcelExporterTest
 *
 * @package Davamigo\TranslatorBundle\Tests\Model\Translation\Excel
 * @author David Amigo <davamigo@gmail.com>
 */
class ExcelExporterTest extends BaseTestCase
{
    /**
     * Test of the export() method
     */
    public function testExport()
    {
        // Create mocks
        $phpExcelFactoryMock = $this
            ->getMockBuilder('\Liuggio\ExcelBundle\Factory')
            ->setMethods(array('createPHPExcelObject', 'createWriter', 'createStreamedResponse'))
            ->getMock();

        $phpExcelMock = $this
            ->getMockBuilder('\PHPExcel')
            ->setMethods(array('getProperties', 'setActiveSheetIndex', 'getSheet'))
            ->getMock();

        $propertiesMethods = array(
            'setTitle'              => $this->once(),
            'setDescription'        => $this->once(),
            'setCompany'            => $this->once(),
            'setCreator'            => $this->once(),
            'setLastModifiedBy'     => $this->once(),
            'setCreated'            => $this->once(),
            'setModified'           => $this->once()
        );

        $propertiesMock = $this
            ->getMockBuilder('\PHPExcel_DocumentProperties')
            ->setMethods(array_keys($propertiesMethods))
            ->getMock();

        $worksheetMock = $this
            ->getMockBuilder('\PHPExcel_Worksheet')
            ->setMethods(array('setTitle', 'getCellByColumnAndRow', 'getStyle', 'getColumnDimensionByColumn'))
            ->getMock();

        $cellMock = $this
            ->getMockBuilder('\PHPExcel_Cell')
            ->setMethods(array('setValue', 'getCoordinate'))
            ->disableOriginalConstructor()
            ->getMock();

        $styleMock = $this
            ->getMockBuilder('\PHPExcel_Style')
            ->setMethods(array('applyFromArray'))
            ->disableOriginalConstructor()
            ->getMock();

        $dimensionMock = $this
            ->getMockBuilder('\PHPExcel_Worksheet_ColumnDimension')
            ->setMethods(array('setAutoSize'))
            ->disableOriginalConstructor()
            ->getMock();

        $exporter = $this
            ->getMockBuilder('Davamigo\TranslatorBundle\Model\Translator\Excel\ExcelExporter')
            ->setConstructorArgs(array($phpExcelFactoryMock))
            ->setMethods(array('createResponse'))
            ->getMock();

        // Configure the test
        $globals = array(
            'result' => array(),
            'row'    => null,
            'col'    => null
        );

        $phpExcelFactoryMock
            ->expects($this->once())
            ->method('createPHPExcelObject')
            ->will($this->returnValue($phpExcelMock));

        $phpExcelMock
            ->expects($this->once())
            ->method('getProperties')
            ->will($this->returnValue($propertiesMock));

        foreach ($propertiesMethods as $method => $expected) {
            if ($expected) {
                $propertiesMock->expects($expected)->method($method)->willReturnSelf();
            }
        }

        $phpExcelMock
            ->expects($this->once())
            ->method('getSheet')
            ->will($this->returnValue($worksheetMock));

        $worksheetMock
            ->expects($this->atLeastOnce())
            ->method('getCellByColumnAndRow')
            ->willReturnCallback(function($c, $r) use (&$globals, $cellMock) {
                $globals['col'] = $c;
                $globals['row'] = $r;
                return $cellMock;
            });

        $cellMock
            ->expects($this->atLeastOnce())
            ->method('setValue')
            ->willReturnCallback(function($value) use (&$globals, $cellMock) {
                $row = $globals['row'];
                $col = $globals['col'];
                $globals['result'][$row][$col] = $value;
                return $cellMock;
            });

        $worksheetMock
            ->expects($this->atLeastOnce())
            ->method('getStyle')
            ->willReturn($styleMock);

        $worksheetMock
            ->expects($this->atLeastOnce())
            ->method('getColumnDimensionByColumn')
            ->willReturn($dimensionMock);

        // Run the test
        $translations = $this->getTranslationsTestObject();

        /** @var ExcelExporter $exporter */
        $exporter->export($translations);

        // Expected result
        $expected = array(
            1 => array("Bundle", "Domain", "Resource", "en", "es"),
            2 => array("App",  "messages", "app.name", "The app name", "La aplicación"),
            3 => array("App", "validators", "error.not-found", "Not found", "No encontrado")
        );

        // Assertions
        $this->assertEquals($expected, $globals['result']);
    }

    /**
     * Test of the export() method
     */
    public function testExportCanNotCreateExcelObjectThrowsAnException()
    {
        // Create mocks
        $phpExcelFactoryMock = $this
            ->getMockBuilder('\Liuggio\ExcelBundle\Factory')
            ->setMethods(array('createPHPExcelObject', 'createWriter', 'createStreamedResponse'))
            ->getMock();

        $exporter = $this
            ->getMockBuilder('Davamigo\TranslatorBundle\Model\Translator\Excel\ExcelExporter')
            ->setConstructorArgs(array($phpExcelFactoryMock))
            ->setMethods(array('createResponse'))
            ->getMock();

        // Configure the test
        $phpExcelFactoryMock
            ->expects($this->once())
            ->method('createPHPExcelObject')
            ->will($this->returnValue(null));

        $this->setExpectedException('Davamigo\TranslatorBundle\Model\Translator\Exception\ExporterException');

        // Run the test
        $translations = new Translations();

        /** @var ExcelExporter $exporter */
        $exporter->export($translations);
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
