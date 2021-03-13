<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_ClassLoader.php';

class TNTOfficiel_ManifestPDF extends PDF
{
    /**
     * Constructor
     * @param $objects
     * @param $template
     * @param $smarty
     * @param string $orientation
     */
    public function __construct($objects, $template, $smarty, $orientation = 'P')
    {
        TNTOfficiel_Logstack::log();
        // PDFGenerator
        $this->pdf_renderer = new TNTOfficiel_ManifestPDFGenerator((bool)Configuration::get('PS_PDF_USE_CACHE'), $orientation);
        $this->template = $template;
        $this->smarty = $smarty;
        $this->objects = $objects;
        if (!($objects instanceof Iterator) && !is_array($objects))
            $this->objects = array($objects);
    }

}
