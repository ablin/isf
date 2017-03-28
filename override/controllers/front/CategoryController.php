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

class CategoryController extends CategoryControllerCore
{
    /**
     * Assigns product list template variables
     */
    public function assignProductList()
    {
        $hook_executed = false;
        Hook::exec('actionProductListOverride', array(
            'nbProducts'   => &$this->nbProducts,
            'catProducts'  => &$this->cat_products,
            'hookExecuted' => &$hook_executed,
        ));

        // The hook was not executed, standard working
        if (!$hook_executed) {
            $this->context->smarty->assign('categoryNameComplement', '');
            $this->nbProducts = $this->category->getProducts(null, null, null, $this->orderBy, $this->orderWay, true);
            $this->pagination((int)$this->nbProducts); // Pagination must be call after "getProducts"
            $this->cat_products = $this->category->getProducts($this->context->language->id, (int)$this->p, (int)$this->n, $this->orderBy, $this->orderWay);
        }
        // Hook executed, use the override
        else {
            // Pagination must be call after "getProducts"
            $this->pagination($this->nbProducts);
        }
        
        $this->addColorsToProductList($this->cat_products);

        Hook::exec('actionProductListModifier', array(
            'nb_products'  => &$this->nbProducts,
            'cat_products' => &$this->cat_products,
        ));

        $this->context->smarty->assign('nb_products', $this->nbProducts);

        $productIds = array();
        $products = array();

        foreach ($this->cat_products as &$product) {
            $productIds[] = $product['reference'];
        }
        $productIds = implode(";", $productIds);

        $webServiceDiva = new WebServiceDiva('<ACTION>TARIF_ART', '<DOS>1<TIERS>'.$this->context->cookie->tiers.'<REF>'.$productIds);

        try {
            $datas = $webServiceDiva->call();

            if ($datas && $datas->references) {

                foreach ($datas->references as $reference) {

                    if ($reference->trouve == 1) {
                        $products[$reference->ref] = array(
                            'stock' => $reference->qteStock,
                            'tarif' => $reference->tarifs
                        );
                    } else {
                        $products[$reference->ref] = array(
                            'stock' => 0,
                            'tarif' => array()
                        );
                    }
                }
            }

        } catch (SoapFault $fault) {
            throw new Exception('Error: SOAP Fault: (faultcode: {'.$fault->faultcode.'}, faultstring: {'.$fault->faultstring.'})');
        }

        $this->context->smarty->assign('references', $products);
    }
}
