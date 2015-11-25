<?php

namespace Ifraktal\TranslatorBundle\Tests\Model\Translation\Excel;

use Ifraktal\TranslatorBundle\Model\Translator\Excel\ExcelImporter;
use Ifraktal\TranslatorBundle\Model\Translator\Exception\ImporterException;
use Ifraktal\TranslatorBundle\Model\Translator\Translations;
use Ifraktal\TranslatorBundle\Tests\IfraktalTestCase;

/**
 * Class ExcelImporterTest
 *
 * @package Ifraktal\TranslatorBundle\Tests\Model\Translation\Excel
 * @author David Amigo <davamigo@gmail.com>
 */
class ExcelImporterTest extends IfraktalTestCase
{
    /**
     * Test of the getReadResources() and getNewTranslations() methods
     */
    public function testGettersReturnTheCorrectValues()
    {
        // Create mocks
        $phpExcelFactoryMock = $this
            ->getMockBuilder('\Liuggio\ExcelBundle\Factory')
            ->setMethods(array('createPHPExcelObject', 'createWriter', 'createStreamedResponse'))
            ->getMock();

        // Configure the test
        $importer = new ExcelImporter($phpExcelFactoryMock);
        $this->setPrivateValue($importer, 'readResources', 101);
        $this->setPrivateValue($importer, 'newTranslations', 259);

        // Assertions
        $this->assertEquals(101, $importer->getReadResources());
        $this->assertEquals(259, $importer->getNewTranslations());
    }

    /**
     * Test of the import() method
     */
    public function testImportWorksFine()
    {
        // Create mocks
        $excelImporterMock = $this
            ->getMockBuilder('Ifraktal\TranslatorBundle\Model\Translator\Excel\ExcelImporter')
            ->setMethods(array('getFileName', 'readExcelFile', 'validateHeader'))
            ->disableOriginalConstructor()
            ->getMock();

        $worksheetMock = $this
            ->getMockBuilder('\PHPExcel_Worksheet')
            ->setMethods(array('getCellByColumnAndRow'))
            ->getMock();

        $cellMock = $this
            ->getMockBuilder('\PHPExcel_Cell')
            ->setMethods(array('getValue'))
            ->disableOriginalConstructor()
            ->getMock();

        // Configure the test
        $excelImporterMock
            ->expects($this->once())
            ->method('getFileName')
            ->will($this->returnArgument(0));

        $excelImporterMock
            ->expects($this->once())
            ->method('readExcelFile')
            ->will($this->returnValue($worksheetMock));

        $excelImporterMock
            ->expects($this->once())
            ->method('validateHeader')
            ->will($this->returnValue(array(
                'Bundle',
                'Domain',
                'Resource',
                'en',
                'es'
            )));

        $worksheetMock
            ->expects($this->atLeastOnce())
            ->method('getCellByColumnAndRow')
            ->will($this->returnValue($cellMock));

        $cellMock
            ->expects($this->atLeastOnce())
            ->method('getValue')
            ->will($this->onConsecutiveCallsArray(array(
                'App', 'messages', 'app.name', 'The app name', 'La aplicación',
                'App', 'validators', 'error.not-found', 'Not found', 'No encontrado',
                null, null, null, null, null
            )));

        // Run the test
        /** @var ExcelImporter $excelImporterMock */
        $result = $excelImporterMock->import('some_file', new Translations());
        $readResources = $excelImporterMock->getReadResources();
        $newTranslations = $excelImporterMock->getNewTranslations();

        // Expected
        $expected = new Translations();
        $expected->addTranslation('App', 'messages', 'en', 'app.name', 'The app name');
        $expected->addTranslation('App', 'messages', 'es', 'app.name', 'La aplicación');
        $expected->addTranslation('App', 'validators', 'en', 'error.not-found', 'Not found');
        $expected->addTranslation('App', 'validators', 'es', 'error.not-found', 'No encontrado');

        // Assertions
        $this->assertEquals($expected, $result);
        $this->assertEquals(2, $readResources);
        $this->assertEquals(4, $newTranslations);
    }

    /**
     * Test of the import() method
     */
    public function testImportWithInvalidFileThrowsAnException()
    {
        // Create mocks
        $excelImporterMock = $this
            ->getMockBuilder('Ifraktal\TranslatorBundle\Model\Translator\Excel\ExcelImporter')
            ->setMethods(array('getFileName', 'readExcelFile', 'validateHeader'))
            ->disableOriginalConstructor()
            ->getMock();

        $worksheetMock = $this
            ->getMockBuilder('\PHPExcel_Worksheet')
            ->setMethods(array('getCellByColumnAndRow'))
            ->getMock();

        // Configure the test
        $excelImporterMock
            ->expects($this->once())
            ->method('getFileName')
            ->will($this->returnArgument(0));

        $excelImporterMock
            ->expects($this->once())
            ->method('readExcelFile')
            ->will($this->returnValue($worksheetMock));

        $excelImporterMock
            ->expects($this->once())
            ->method('validateHeader')
            ->will($this->throwException(new \Exception('some_error')));

        $this->setExpectedException('Ifraktal\TranslatorBundle\Model\Translator\Exception\ImporterException');

        // Run the test
        /** @var ExcelImporter $excelImporterMock */
         $excelImporterMock->import('some_file', new Translations());
    }

    /**
     * Test of the readExcelFile() method
     */
    public function testReadExcelFileWorksFine()
    {
        // Create mocks
        $phpExcelFactoryMock = $this
            ->getMockBuilder('\Liuggio\ExcelBundle\Factory')
            ->setMethods(array('createPHPExcelObject'))
            ->getMock();

        $phpExcelMock = $this
            ->getMockBuilder('\PHPExcel')
            ->setMethods(array('getSheet'))
            ->getMock();

        $worksheetMock = $this
            ->getMockBuilder('\PHPExcel_Worksheet')
            ->setMethods(array(/* none */))
            ->getMock();

        // Configure the test
        $phpExcelFactoryMock
            ->expects($this->once())
            ->method('createPHPExcelObject')
            ->will($this->returnValue($phpExcelMock));

        $phpExcelMock
            ->expects($this->once())
            ->method('getSheet')
            ->will($this->returnValue($worksheetMock));

        // Run the test
        $importer = new ExcelImporter($phpExcelFactoryMock);
        $result = $this->runPrivateMethod($importer, 'readExcelFile', array(sys_get_temp_dir()));

        // Assertions
        $this->assertEquals($worksheetMock, $result);
    }

    /**
     * Test of the readExcelFile() method
     */
    public function testReadExcelFileWhenCanNotCreateExcelObjectThrowsAnException()
    {
        // Create mocks
        $phpExcelFactoryMock = $this
            ->getMockBuilder('\Liuggio\ExcelBundle\Factory')
            ->setMethods(array('createPHPExcelObject'))
            ->getMock();

        // Configure the test
        $phpExcelFactoryMock
            ->expects($this->once())
            ->method('createPHPExcelObject')
            ->will($this->returnValue(null));

        $this->setExpectedException('Ifraktal\TranslatorBundle\Model\Translator\Exception\ImporterException');

        // Run the test
        $importer = new ExcelImporter($phpExcelFactoryMock);
        $this->runPrivateMethod($importer, 'readExcelFile', array(sys_get_temp_dir()));
    }

    /**
     * Test of the readExcelFile() method
     */
    public function testReadExcelFileWhenCreateExcelObjectThrowsAnExceptionThrowsNewException()
    {
        // Create mocks
        $phpExcelFactoryMock = $this
            ->getMockBuilder('\Liuggio\ExcelBundle\Factory')
            ->setMethods(array('createPHPExcelObject'))
            ->getMock();

        // Configure the test
        $phpExcelFactoryMock
            ->expects($this->once())
            ->method('createPHPExcelObject')
            ->will($this->throwException(new ImporterException('Some error text.')));

        $this->setExpectedException('Ifraktal\TranslatorBundle\Model\Translator\Exception\ImporterException');

        // Run the test
        $importer = new ExcelImporter($phpExcelFactoryMock);
        $this->runPrivateMethod($importer, 'readExcelFile', array(sys_get_temp_dir()));
    }

    /**
     * Test of the readExcelFile() method
     */
    public function testReadExcelFileWhenGetWorksheetFailsThrowsAnException()
    {
        // Create mocks
        $phpExcelFactoryMock = $this
            ->getMockBuilder('\Liuggio\ExcelBundle\Factory')
            ->setMethods(array('createPHPExcelObject'))
            ->getMock();

        $phpExcelMock = $this
            ->getMockBuilder('\PHPExcel')
            ->setMethods(array('getSheet'))
            ->getMock();

        // Configure the test
        $phpExcelFactoryMock
            ->expects($this->once())
            ->method('createPHPExcelObject')
            ->will($this->returnValue($phpExcelMock));

        $phpExcelMock
            ->expects($this->once())
            ->method('getSheet')
            ->will($this->throwException(new \PHPExcel_Exception('Some error text.')));

        $this->setExpectedException('Ifraktal\TranslatorBundle\Model\Translator\Exception\ImporterException');

        // Run the test
        $importer = new ExcelImporter($phpExcelFactoryMock);
        $this->runPrivateMethod($importer, 'readExcelFile', array(sys_get_temp_dir()));
    }

    /**
     * Test of the readExcelFile() method
     */
    public function testReadExcelFileWhenGetWorksheetThrowsAnExceptionThrowsNewException()
    {
        // Create mocks
        $phpExcelFactoryMock = $this
            ->getMockBuilder('\Liuggio\ExcelBundle\Factory')
            ->setMethods(array('createPHPExcelObject'))
            ->getMock();

        $phpExcelMock = $this
            ->getMockBuilder('\PHPExcel')
            ->setMethods(array('getSheet'))
            ->getMock();

        // Configure the test
        $phpExcelFactoryMock
            ->expects($this->once())
            ->method('createPHPExcelObject')
            ->will($this->returnValue($phpExcelMock));

        $phpExcelMock
            ->expects($this->once())
            ->method('getSheet')
            ->will($this->returnValue(null));

        $this->setExpectedException('Ifraktal\TranslatorBundle\Model\Translator\Exception\ImporterException');

        // Run the test
        $importer = new ExcelImporter($phpExcelFactoryMock);
        $this->runPrivateMethod($importer, 'readExcelFile', array(sys_get_temp_dir()));
    }

    /**
     * Test of the validateHeader() method
     */
    public function testValidateHeaderWorksFine()
    {
        // Create mocks
        $phpExcelFactoryMock = $this
            ->getMockBuilder('\Liuggio\ExcelBundle\Factory')
            ->setMethods(array(/* none */))
            ->getMock();

        $worksheetMock = $this
            ->getMockBuilder('\PHPExcel_Worksheet')
            ->setMethods(array('getCellByColumnAndRow'))
            ->getMock();

        $cellMock = $this
            ->getMockBuilder('\PHPExcel_Cell')
            ->setMethods(array('getValue'))
            ->disableOriginalConstructor()
            ->getMock();

        // Configure the test
        $worksheetMock
            ->expects($this->atLeastOnce())
            ->method('getCellByColumnAndRow')
            ->will($this->returnValue($cellMock));

        $cellMock
            ->expects($this->atLeastOnce())
            ->method('getValue')
            ->will($this->onConsecutiveCallsArray(array(
                'Bundle',
                'Domain',
                'Resource',
                'en',
                'es',
                null
            )));

        // Run the test
        $importer = new ExcelImporter($phpExcelFactoryMock);
        $header = $this->runPrivateMethod($importer, 'validateHeader', array($worksheetMock));

        // Expected result
        $expected = array(
            'Bundle',
            'Domain',
            'Resource',
            'en',
            'es'
        );

        // Assertions
        $this->assertEquals($expected, $header);
    }

    /**
     * Test of the validateHeader() method
     */
    public function testValidateHeaderWithInvalidDataThrowsAnException()
    {
        // Create mocks
        $phpExcelFactoryMock = $this
            ->getMockBuilder('\Liuggio\ExcelBundle\Factory')
            ->setMethods(array(/* none */))
            ->getMock();

        $worksheetMock = $this
            ->getMockBuilder('\PHPExcel_Worksheet')
            ->setMethods(array('getCellByColumnAndRow'))
            ->getMock();

        $cellMock = $this
            ->getMockBuilder('\PHPExcel_Cell')
            ->setMethods(array('getValue'))
            ->disableOriginalConstructor()
            ->getMock();

        // Configure the test
        $worksheetMock
            ->expects($this->atLeastOnce())
            ->method('getCellByColumnAndRow')
            ->will($this->returnValue($cellMock));

        $cellMock
            ->expects($this->atLeastOnce())
            ->method('getValue')
            ->will($this->onConsecutiveCallsArray(array(
                'Bundle',
                'Domain',
                'Bad-data',
                'en',
                null
            )));

        $this->setExpectedException('Ifraktal\TranslatorBundle\Model\Translator\Exception\ImporterException');

        // Run the test
        $importer = new ExcelImporter($phpExcelFactoryMock);
        $this->runPrivateMethod($importer, 'validateHeader', array($worksheetMock));
    }

    /**
     * Test of the validateHeader() method
     */
    public function testValidateHeaderWithoutAnyDataThrowsAnException()
    {
        // Create mocks
        $phpExcelFactoryMock = $this
            ->getMockBuilder('\Liuggio\ExcelBundle\Factory')
            ->setMethods(array(/* none */))
            ->getMock();

        $worksheetMock = $this
            ->getMockBuilder('\PHPExcel_Worksheet')
            ->setMethods(array('getCellByColumnAndRow'))
            ->getMock();

        $cellMock = $this
            ->getMockBuilder('\PHPExcel_Cell')
            ->setMethods(array('getValue'))
            ->disableOriginalConstructor()
            ->getMock();

        // Configure the test
        $worksheetMock
            ->expects($this->atLeastOnce())
            ->method('getCellByColumnAndRow')
            ->will($this->returnValue($cellMock));

        $cellMock
            ->expects($this->atLeastOnce())
            ->method('getValue')
            ->will($this->onConsecutiveCallsArray(array(
                null
            )));

        $this->setExpectedException('Ifraktal\TranslatorBundle\Model\Translator\Exception\ImporterException');

        // Run the test
        $importer = new ExcelImporter($phpExcelFactoryMock);
        $this->runPrivateMethod($importer, 'validateHeader', array($worksheetMock));
    }

    /**
     * Test of the getPathName() and getFileName() methods
     */
    public function testGetPathNameWithUploadedFileWorksFine()
    {
        // Create mocks
        $phpExcelFactoryMock = $this
            ->getMockBuilder('\Liuggio\ExcelBundle\Factory')
            ->setMethods(array(/* none */))
            ->getMock();

        $uploadedFileMock = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->setMethods(array('getPathname', 'getClientOriginalName'))
            ->disableOriginalConstructor()
            ->getMock();

        // Configure the test
        $uploadedFileMock
            ->expects($this->once())
            ->method('getPathname')
            ->will($this->returnValue('some_value'));

        $uploadedFileMock
            ->expects($this->once())
            ->method('getClientOriginalName')
            ->will($this->returnValue('other_value'));

        // Run the rest
        $importer = new ExcelImporter($phpExcelFactoryMock);
        $pathName = $this->runPrivateMethod($importer, 'getPathName', array($uploadedFileMock));
        $fileName = $this->runPrivateMethod($importer, 'getFileName', array($uploadedFileMock));

        // Assertions
        $this->assertEquals('some_value', $pathName);
        $this->assertEquals('other_value', $fileName);
    }

    /**
     * Test of the getPathName() and getFileName() methods
     */
    public function testGetPathNameWithFileObjectWorksFine()
    {
        // Create mocks
        $phpExcelFactoryMock = $this
            ->getMockBuilder('\Liuggio\ExcelBundle\Factory')
            ->setMethods(array(/* none */))
            ->getMock();

        $fileMock = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\File\File')
            ->setMethods(array('getPathname', 'getFilename'))
            ->disableOriginalConstructor()
            ->getMock();

        // Configure the test
        $fileMock
            ->expects($this->once())
            ->method('getPathname')
            ->will($this->returnValue('some_value'));

        $fileMock
            ->expects($this->once())
            ->method('getFilename')
            ->will($this->returnValue('other_value'));

        // Run the rest
        $importer = new ExcelImporter($phpExcelFactoryMock);
        $pathName = $this->runPrivateMethod($importer, 'getPathName', array($fileMock));
        $fileName = $this->runPrivateMethod($importer, 'getFileName', array($fileMock));

        // Assertions
        $this->assertEquals('some_value', $pathName);
        $this->assertEquals('other_value', $fileName);
    }

    /**
     * Test of the getPathName() and getFileName() methods
     */
    public function testGetPathNameWithStringWorksFine()
    {
        // Create mocks
        $phpExcelFactoryMock = $this
            ->getMockBuilder('\Liuggio\ExcelBundle\Factory')
            ->setMethods(array(/* none */))
            ->getMock();

        $tempDir = sys_get_temp_dir();

        // Run the rest
        $importer = new ExcelImporter($phpExcelFactoryMock);
        $pathName = $this->runPrivateMethod($importer, 'getPathName', array($tempDir . '/.'));
        $fileName = $this->runPrivateMethod($importer, 'getFileName', array($tempDir . '/.'));

        // Assertions
        $this->assertEquals($tempDir, $pathName);
        $this->assertEquals('.', $fileName);
    }
}
