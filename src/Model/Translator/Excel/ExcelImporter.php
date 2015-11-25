<?php

namespace Ifraktal\TranslatorBundle\Model\Translator\Excel;

use Ifraktal\TranslatorBundle\Model\Translator\Exception\ImporterException;
use Ifraktal\TranslatorBundle\Model\Translator\ImporterInterface;
use Ifraktal\TranslatorBundle\Model\Translator\Translations;
use Liuggio\ExcelBundle\Factory as PhpExcel;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Service to import translations from an excel file
 *
 * @package Ifraktal\TranslatorBundle\Model\Translator\Excel
 * @author David Amigo <davamigo@gmail.com>
 * @service ifraktal.translator.importer.excel
 */
class ExcelImporter extends ExcelBase implements ImporterInterface
{
    /** @var int Total resources processed */
    protected $readResources;

    /** @var int Total new translations inserted */
    protected $newTranslations;

    /**
     * Constructor
     *
     * @param PhpExcel $phpExcelFactory
     */
    public function __construct(PhpExcel $phpExcelFactory)
    {
        parent::__construct($phpExcelFactory);
        $this->newTranslations  = 0;
        $this->readResources = 0;
    }

    /**
     * Import the translations
     *
     * @param UploadedFile|string   $file The filename to read
     * @param Translations          $translations   The translations to export
     * @param array                 $bundles List of bundles (empty array: all)
     * @param array                 $domains List of domains (empty array: all)
     * @param array                 $locales List of locales (empty array: all)
     * @return Translations
     * @throws ImporterException
     */
    public function import(
        $file,
        Translations $translations,
        array $bundles = array(),
        array $domains = array(),
        array $locales = array()
    ) {
        // Init
        $this->readResources = 0;
        $this->newTranslations = 0;

        // Get the file name
        $originalName = $this->getFileName($file);

        // Read the Excel file
        $worksheet = $this->readExcelFile($file);

        // Validate the headers
        try {
            $headers = $this->validateHeader($worksheet);

        } catch (\Exception $exc) {
            throw new ImporterException('Invalid Excel file ' . $originalName, 0, $exc);
        }

        $length = count($headers);
        $row = 1;

        do {
            ++$row;

            $bundle = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
            $domain = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
            $resource = $worksheet->getCellByColumnAndRow(2, $row)->getValue();

            if (($bundle && $domain && $resource)) {
                ++$this->readResources;

                if ((empty($bundles) || in_array($bundle, $bundles))
                    && (empty($domains) || in_array($domain, $domains))) {
                    //
                    for ($col = count(static::$fixedHeader); $col < $length; ++$col) {
                        $locale = $headers[$col];

                        if (empty($locales) || in_array($locale, $locales)) {
                            $value = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
                            $current = $translations->getTranslation($bundle, $domain, $locale, $resource);
                            if ($value && $value != $current) {
                                $translations->addTranslation($bundle, $domain, $locale, $resource, $value);
                                ++$this->newTranslations;
                            }
                        }
                    }
                }
            }
        } while ($bundle && $domain && $resource);

        return $translations;
    }

    /**
     * Get total resources processed
     *
     * @return int
     */
    final public function getReadResources()
    {
        return $this->readResources;
    }

    /**
     * Get total new translations inserted
     *
     * @return int
     */
    final public function getNewTranslations()
    {
        return $this->newTranslations;
    }

    /**
     * Read the Excel file and return the first worksheet
     *
     * @param UploadedFile|File|string $file
     * @return \PHPExcel_Worksheet
     * @throws ImporterException
     */
    protected function readExcelFile($file)
    {
        $filename = $this->getPathname($file);
        $originalName = $this->getFileName($file);

        try {
            $excelObj = $this->phpExcelFactory->createPHPExcelObject($filename);

        } catch (\Exception $ecx) {
            throw new ImporterException('Error loading file:' . $originalName, 0, $ecx);
        }

        if (null == $excelObj) {
            throw new ImporterException('Error loading file: ' . $originalName);
        }

        try {
            $worksheet = $excelObj->getSheet(0);

        } catch (\PHPExcel_Exception $ecx) {
            throw new ImporterException('Corrupted Excel file: ' . $originalName, 0, $ecx);
        }

        if (null == $worksheet) {
            throw new ImporterException('Corrupted Excel file: ' . $originalName);
        }

        return $worksheet;
    }

    /**
     * Validate and return the header of the Excel file
     *
     * @param \PHPExcel_Worksheet $worksheet
     * @return string[]
     * @throws ImporterException
     */
    protected function validateHeader(\PHPExcel_Worksheet $worksheet)
    {
        $row = 1;
        $col = 0;
        $headers = array();

        do {
            $value = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
            if ($value) {
                $headers[$col++] = $value;
            }

        } while ($value);

        $valid = true;
        if ($col <= count(static::$fixedHeader)) {
            $valid = false;
        } else {
            foreach (static::$fixedHeader as $key => $value) {
                if ($headers[$key] != $value) {
                    $valid = false;
                }
            }
        }

        if (!$valid) {
            throw new ImporterException('The header of the Excel file is invalid');
        }

        return $headers;
    }

    /**
     * Get the full path of the file
     *
     * @param UploadedFile|File|string $file
     * @return string
     */
    protected function getPathName($file)
    {
        if ($file instanceof UploadedFile) {
            return $file->getPathname();

        } elseif ($file instanceof File) {
            return $file->getPathname();

        } else {
            return realpath($file);
        }
    }

    /**
     * Get the name of the file
     *
     * @param UploadedFile|File|string $file
     * @return string
     */
    protected function getFileName($file)
    {
        if ($file instanceof UploadedFile) {
            return $file->getClientOriginalName();

        } elseif ($file instanceof File) {
            return $file->getFilename();

        } else {
            return basename($file);
        }
    }
}
