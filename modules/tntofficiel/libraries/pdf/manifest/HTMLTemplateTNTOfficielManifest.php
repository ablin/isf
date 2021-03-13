<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_ClassLoader.php';

// HTMLTemplate<NAME>.
class HTMLTemplateTNTOfficielManifest extends HTMLTemplate
{
    public $custom_model;

    public function __construct($custom_object, $smarty)
    {
        TNTOfficiel_Logstack::log();

        $this->custom_model = $custom_object;
        $this->smarty = $smarty;

        // header informations
        $id_lang = Context::getContext()->language->id;
        $this->title = HTMLTemplateTNTOfficielManifest::l('Titre Test');
        // footer informations
        $this->shop = new Shop(Context::getContext()->shop->id);
    }

    /**
     * Returns the template's HTML content
     * @return string HTML content
     */
    public function getContent()
    {
        TNTOfficiel_Logstack::log();

        $this->smarty->assign(array(
            'manifestData' => $this->custom_model,
        ));

        return $this->smarty->fetch(_PS_MODULE_DIR_.'tntofficiel/views/templates/admin/manifest/custom_template_content.tpl');
    }

    public function getHeader()
    {
        TNTOfficiel_Logstack::log();

        $this->smarty->assign(array(
            'manifestData' => $this->custom_model,
        ));

        return $this->smarty->fetch(_PS_MODULE_DIR_.'tntofficiel/views/templates/admin/manifest/custom_template_header.tpl');
    }

    /**
     * Returns the template filename
     * @return string filename
     */
    public function getFooter()
    {
        TNTOfficiel_Logstack::log();

        return $this->smarty->fetch(_PS_MODULE_DIR_.'tntofficiel/views/templates/admin/manifest/custom_template_footer.tpl');
    }

    /**
     * Returns the template filename
     * @return string filename
     */
    public function getFilename()
    {
        TNTOfficiel_Logstack::log();

        return 'Manifeste.pdf';
    }

    /**
     * Returns the template filename when using bulk rendering
     * @return string filename
     */
    public function getBulkFilename()
    {
        TNTOfficiel_Logstack::log();

        return 'Manifeste.pdf';
    }

}
