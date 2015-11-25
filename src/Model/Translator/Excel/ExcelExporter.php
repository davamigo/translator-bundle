<?php

namespace Ifraktal\TranslatorBundle\Model\Translator\Excel;

use Ifraktal\TranslatorBundle\Model\Translator\Exception\ExporterException;
use Ifraktal\TranslatorBundle\Model\Translator\ExporterInterface;
use Ifraktal\TranslatorBundle\Model\Translator\Translations;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Service to export translations to an excel file
 *
 * @package Ifraktal\TranslatorBundle\Model\Translator\Excel
 * @author David Amigo <davamigo@gmail.com>
 * @service ifraktal.translator.exporter.excel
 */
class ExcelExporter extends ExcelBase implements ExporterInterface
{
    /** Header cell style */
    protected static $headerCellStyle = array(
        'font'          => array(
            'bold'          => true,
            'color'         => array(
                'rgb'           => '000000'
            )
        ),
        'alignment'     => array(
            'horizontal'    => 'center',
            'vertical'      => 'top',
        ),
        'fill'          => array(
            'type'          => 'solid',
            'startcolor'    => array(
                'rgb'           => 'CCCCCC'
            ),
        )
    );

    /** Text cell style */
    protected static $textCellStyle = array(
        'font'          => array(
            'color'         => array(
                'rgb'           => '444444'
            )
        ),
        'alignment'     => array(
            'horizontal'    => 'left',
            'vertical'      => 'top',
        )
    );

    /**
     * Export the translations
     *
     * @param Translations $translations The translations to export
     * @param array        $bundles List of bundles (empty array: all)
     * @param array        $domains List of domains (empty array: all)
     * @param array        $locales List of locales (empty array: all)
     * @param string       $filename The filename to export
     * @return Response
     * @throws ExporterException
     */
    public function export(
        Translations $translations,
        array $bundles = array(),
        array $domains = array(),
        array $locales = array(),
        $filename = null
    ) {
        // Current date and time
        $now = new \DateTime();

        // Validate the params
        if (!count($bundles)) {
            $bundles = $translations->getBundles();
        }

        if (!count($domains)) {
            $domains = $translations->getDomains();
        }

        if (!count($locales)) {
            $locales = $translations->getLocales();
        }

        if (null == $filename) {
            $filename = 'ifraktal_translator_' . $now->format('Y-m-d_H-i-s') . '.xls';
        }

        // Create the Excel object
        $excelObj = $this->phpExcelFactory->createPHPExcelObject();
        if (null == $excelObj) {
            throw new ExporterException('Can\'t create the Excel object.');
        }

        // Se the Excel properties
        $excelObj
            ->getProperties()
            ->setTitle('Ifraktal Symfony Translations')
            ->setDescription('Ifraktal Symfony Translations')
            ->setCompany('Ifraktal')
            ->setCreator('Ifraktal')
            ->setLastModifiedBy('Ifraktal')
            ->setCreated($now->getTimestamp())
            ->setModified($now->getTimestamp());

        // Create active sheet
        $excelObj->setActiveSheetIndex(0);
        $worksheet = $excelObj->getSheet(0);
        $worksheet->setTitle('Translations');

        // Create the Excel header (first row)
        $row = 1;
        $col = 0;

        $headers = array_merge(static::$fixedHeader, $locales);
        foreach ($headers as $string) {
            $cell = $worksheet->getCellByColumnAndRow($col, $row);
            $cell->setValue($string);
            $style = $worksheet->getStyle($cell->getCoordinate());
            $style->applyFromArray(static::$headerCellStyle);
            ++$col;
        }

        foreach ($bundles as $bundle) {
            foreach ($domains as $domain) {
                $resources = $translations->getResources($bundle, $domain);
                foreach ($resources as $resource) {
                    //
                    $content = array(
                        $bundle,
                        $domain,
                        $resource
                    );

                    foreach ($locales as $locale) {
                        $content[] = $translations->getTranslation($bundle, $domain, $locale, $resource);
                    }

                    ++$row;
                    $col = 0;

                    foreach ($content as $string) {
                        $cell = $worksheet->getCellByColumnAndRow($col, $row);
                        $cell->setValue($string);
                        $style = $worksheet->getStyle($cell->getCoordinate());
                        $style->applyFromArray(static::$textCellStyle);
                        ++$col;
                    }
                }
            }
        }

        // Auto-size all columns
        for ($col = 0; $col < count($headers); ++$col) {
            $dimension = $worksheet->getColumnDimensionByColumn($col);
            $dimension->setAutoSize(true);
        }

        // Create the response
        return $this->createResponse($excelObj, $filename);
    }

    /**
     * Creates and configures a response.
     *
     * @param \PHPExcel $excelObj
     * @param string    $filename
     * @return Response
     */
    protected function createResponse(\PHPExcel $excelObj, $filename)
    {
        // Create the writer
        $writer = $this->phpExcelFactory->createWriter($excelObj, 'Excel5');

        // Create the response
        $response = $this->phpExcelFactory->createStreamedResponse($writer);

        // Adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename
        );

        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }
}
