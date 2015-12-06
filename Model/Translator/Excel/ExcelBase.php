<?php

namespace Davamigo\TranslatorBundle\Model\Translator\Excel;

use Liuggio\ExcelBundle\Factory as PhpExcelFactory;

/**
 * Base class to Excel services
 *
 * @package Davamigo\TranslatorBundle\Model\Translator\Excel
 * @author David Amigo <davamigo@gmail.com>
 */
class ExcelBase
{
    /** @var PhpExcelFactory */
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
     * @param PhpExcelFactory $phpExcelFactory
     */
    public function __construct(PhpExcelFactory $phpExcelFactory)
    {
        $this->phpExcelFactory = $phpExcelFactory;
    }
}
