<?php
/*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
    exit;

class HomeFeaturedOverride extends HomeFeatured
{
    public function _cacheProducts()
    {
        if (!isset(HomeFeatured::$cache_products))
        {
            $category = new Category((int)Configuration::get('HOME_FEATURED_CAT'), (int)Context::getContext()->language->id);
            $nb = (int)Configuration::get('HOME_FEATURED_NBR');
            if (Configuration::get('HOME_FEATURED_RANDOMIZE'))
                HomeFeatured::$cache_products = $category->getProducts((int)Context::getContext()->language->id, 1, ($nb ? $nb : 8), null, null, false, true, true, ($nb ? $nb : 8));
            else
                HomeFeatured::$cache_products = $category->getProducts((int)Context::getContext()->language->id, 1, ($nb ? $nb : 8), 'position');
        }

        if (HomeFeatured::$cache_products === false || empty(HomeFeatured::$cache_products))
            return false;

        $productIds = array();
        $productsFeatured = array();

        foreach (HomeFeatured::$cache_products as &$product) {
            $productIds[] = $product['reference'];
        }
        $productIds = implode(";", $productIds);

        $webServiceDiva = new WebServiceDiva('<ACTION>TARIF_ART', '<DOS>1<TIERS>'.$this->context->cookie->tiers.'<REF>'.$productIds.'<FICHE>0');

        try {
            $datas = $webServiceDiva->call();

            if ($datas && $datas->references) {

                foreach ($datas->references as $reference) {

                    if ($reference->trouve == 1) {
                        $productsFeatured[$reference->ref] = array(
                            'total_stock' => $reference->total_stock,
                            'total_dispo' => $reference->total_dispo,
                            'total_jauge' => $reference->total_jauge,
                            'tarif' => $reference->max_pun,
                            'nb_tarif' => $reference->nbTarifs
                        );
                    } else {
                        $productsFeatured[$reference->ref] = array(
                            'total_stock' => -1,
                            'total_dispo' => -1,
                            'total_jauge' => -1,
                            'tarif' => 0,
                            'nb_tarif' => 0,
                            'alerte' => ""
                        );
                    }
                }
            }

        } catch (SoapFault $fault) {
            throw new Exception('Error: SOAP Fault: (faultcode: {'.$fault->faultcode.'}, faultstring: {'.$fault->faultstring.'})');
        }

        $this->smarty->assign('references', $productsFeatured);
    }
}
