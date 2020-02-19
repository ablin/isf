<?php
/*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class Link extends LinkCore
{
    /**
     * Returns a link to a product image for display
     * Note: the new image filesystem stores product images in subdirectories of img/p/
     *
     * @param string $name rewrite link of the image
     * @param string $ids id part of the image filename - can be "id_product-id_image" (legacy support, recommended) or "id_image" (new)
     * @param string $type
     * @return string
     */
    public function getImageLink($name, $ids, $type = null, $id_product = null)
    {
        $not_default = false;

        // Check if module is installed, enabled, customer is logged in and watermark logged option is on
        if (Configuration::get('WATERMARK_LOGGED') && (Module::isInstalled('watermark') && Module::isEnabled('watermark')) && isset(Context::getContext()->customer->id)) {
            $type .= '-'.Configuration::get('WATERMARK_HASH');
        }

        // legacy mode or default image
        $theme = ((Shop::isFeatureActive() && file_exists(_PS_PROD_IMG_DIR_.$ids.($type ? '-'.$type : '').'-'.(int)Context::getContext()->shop->id_theme.'.jpg')) ? '-'.Context::getContext()->shop->id_theme : '');
        if ((Configuration::get('PS_LEGACY_IMAGES')
                && (file_exists(_PS_PROD_IMG_DIR_.$ids.($type ? '-'.$type : '').$theme.'.jpg')))
            || ($not_default = strpos($ids, 'default') !== false)) {
            if ($this->allow == 1 && !$not_default) {
                $uri_path = __PS_BASE_URI__.$ids.($type ? '-'.$type : '').$theme.'/'.$name.'.jpg';
            } else {
                if ($this->getParentImage($id_product, $type)) {
                    return $this->getParentImage($id_product, $type);
                }
                $uri_path = _THEME_PROD_DIR_.$ids.($type ? '-'.$type : '').$theme.'.jpg';
            }
        } else {
            // if ids if of the form id_product-id_image, we want to extract the id_image part
            $split_ids = explode('-', $ids);
            $id_image = (isset($split_ids[1]) ? $split_ids[1] : $split_ids[0]);
            $theme = ((Shop::isFeatureActive() && file_exists(_PS_PROD_IMG_DIR_.Image::getImgFolderStatic($id_image).$id_image.($type ? '-'.$type : '').'-'.(int)Context::getContext()->shop->id_theme.'.jpg')) ? '-'.Context::getContext()->shop->id_theme : '');
            if ($this->allow == 1) {
                $uri_path = __PS_BASE_URI__.$id_image.($type ? '-'.$type : '').$theme.'/'.$name.'.jpg';
            } else {
                $uri_path = _THEME_PROD_DIR_.Image::getImgFolderStatic($id_image).$id_image.($type ? '-'.$type : '').$theme.'.jpg';
            }
        }

        return $this->protocol_content.Tools::getMediaServer($uri_path).$uri_path;
    }

    /**
     * @param $id_product
     * @return mixed
     */
    private function getParentImage($id_product, $type)
    {
        $sql = sprintf(
            "SELECT c.id_category FROM %sfeature_product fp
                      INNER JOIN %sfeature_value fv USING (id_feature_value)
                      INNER JOIN %scategory c ON fv.id_category = c.id_category
                      WHERE fp.id_product = %d
                      ORDER BY fv.level DESC",
            _DB_PREFIX_,
            _DB_PREFIX_,
            _DB_PREFIX_,
            $id_product
        );
        $categories = Db::getInstance()->executeS($sql);

        $files = scandir(_PS_CAT_IMG_DIR_);
        foreach ($categories as $category) {

            if (count(preg_grep('/^'.$category['id_category'].($type ? '-'.$type : '').'.jpg/i', $files)) > 0) {

                foreach ($files as $file) {
                    if (preg_match('/^'.$category['id_category'].($type ? '-'.$type : '').'.jpg/i', $file) === 1) {
                        return $this->getMediaLink(_THEME_CAT_DIR_.$file);
                        break;
                    }
                }
            }
        }

        return null;
    }
}
