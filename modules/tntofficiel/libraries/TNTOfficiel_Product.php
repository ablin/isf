<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_ClassLoader.php';

class TNTOfficiel_Product
{
    /**
     * Prevent Construct.
     */
    final private function __construct()
    {
        trigger_error(sprintf('%s() %s is static.', __FUNCTION__, get_class($this)), E_USER_ERROR);
    }

    /**
     * Get a list of attribute name/value from a product ID and his attribute combination ID, plus language ID.
     *
     * @param $intArgProductID
     * @param $intArgProductAttributeID
     * @param $intArgLangID
     *
     * @return array
     */
    public static function getAttributeCombinationsText($intArgProductID, $intArgProductAttributeID, $intArgLangID)
    {
        TNTOfficiel_Logstack::log();

        $intProductID = (int)$intArgProductID;
        $intProductAttributeID = (int)$intArgProductAttributeID;
        $intLangID = (int)$intArgLangID;

        $arrAttributesList = array();

        if ($intProductAttributeID > 0) {
            // Load the product.
            $objProduct = new Product($intProductID);
            // Get product attributes combination.
            $arrAttributeCombinations = $objProduct->getAttributeCombinationsById(
                $intProductAttributeID,
                $intLangID
            );

            foreach ($arrAttributeCombinations as $arrAttribute) {
                $arrAttributesList[] = array(
                    // Attribute Name (id_attribute_group).
                    'code' => $arrAttribute['group_name'],
                    // Attribute Value (id_attribute).
                    'value' => $arrAttribute['attribute_name']
                );
            }
        }

        return $arrAttributesList;
    }
}
