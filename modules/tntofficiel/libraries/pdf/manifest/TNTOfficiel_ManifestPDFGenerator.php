<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_ClassLoader.php';

class TNTOfficiel_ManifestPDFGenerator extends PDFGenerator
{
    public function Header()
    {
        TNTOfficiel_Logstack::log();

        $this->writeHTML($this->header);

        $this->writeHTML('<table style="width: 100%;"><tr style="width: 100%;"><td style="width: 33%"></td><td style="width: 33%"></td><td style="width: 33%; text-align: right">Page : '.$this->getAliasNumPage().' de '.$this->getAliasNbPages().'</td></tr></table>');
    }

}
