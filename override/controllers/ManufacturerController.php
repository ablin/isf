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

class ManufacturerController extends ManufacturerControllerCore
{
    /**
     * Assign template vars if displaying one manufacturer
     */
    protected function assignOne()
    {
        $this->manufacturer->description = Tools::nl2br(trim($this->manufacturer->description));
        $nbProducts = $this->manufacturer->getProducts($this->manufacturer->id, null, null, null, $this->orderBy, $this->orderWay, true);
        $this->pagination((int)$nbProducts);

        $products = $this->manufacturer->getProducts($this->manufacturer->id, $this->context->language->id, (int)$this->p, (int)$this->n, $this->orderBy, $this->orderWay);
        $this->addColorsToProductList($products);
        $this->addStockAndProductsToProductList($products);

        $this->context->smarty->assign(array(
            'nb_products' => $nbProducts,
            'products' => $products,
            'path' => ($this->manufacturer->active ? Tools::safeOutput($this->manufacturer->name) : ''),
            'manufacturer' => $this->manufacturer,
            'comparator_max_item' => Configuration::get('PS_COMPARATOR_MAX_ITEM'),
            'body_classes' => array($this->php_self.'-'.$this->manufacturer->id, $this->php_self.'-'.$this->manufacturer->link_rewrite)
        ));
    }

    private function addStockAndProductsToProductList($products)
    {
        $productDatas = array();

        foreach ($products as &$product) {
            $productIds[] = $product['reference'];
        }
        $productIds = implode(";", $productIds);

        $webServiceDiva = new WebServiceDiva('<ACTION>TARIF_ART', '<DOS>1<TIERS>'.$this->context->cookie->tiers.'<REF>'.$productIds.'<FICHE>0');

        try {
            $datas = $webServiceDiva->call();

            if ($datas && $datas->references) {

                foreach ($datas->references as $reference) {

                    if ($reference->trouve == 1) {
                        $productDatas[$reference->ref] = array(
                            'stock' => $reference->total_stock,
                            'tarif' => $reference->max_pun,
                            'nb_tarif' => $reference->nbTarifs
                        );
                    } else {
                        $productDatas[$reference->ref] = array(
                            'stock' => 0,
                            'tarif' => 0,
                            'nb_tarif' => 0
                        );
                    }
                }
            }

        } catch (SoapFault $fault) {
            throw new Exception('Error: SOAP Fault: (faultcode: {'.$fault->faultcode.'}, faultstring: {'.$fault->faultstring.'})');
        }

        $this->context->smarty->assign('references', $productDatas);
    }
}
