<?php

namespace Ifraktal\TranslatorBundle\Model\Translator\Excel;

use Liuggio\ExcelBundle\Factory as PhpExcel;

/**
 * Base class to Excel services
 *
 * @package Ifraktal\TranslatorBundle\Model\Translator\Excel
 * @author David Amigo <davamigo@gmail.com>
 */
class ExcelBase
{
    /** @var PhpExcel */
    protected $phpExcelFactory;

    /** Fixed Excel file */
    protected static $fixedHeader = array(
        'Bundle',
        'Domain',
        'Resource'
    );

    /**
     * Constructor
     *
     * @param PhpExcel $phpExcelFactory
     */
    public function __construct(PhpExcel $phpExcelFactory)
    {
        $this->phpExcelFactory = $phpExcelFactory;
    }
}
