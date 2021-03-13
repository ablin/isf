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

class ProductController extends ProductControllerCore
{
    /**
     * Assign template vars related to page content
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();
        $this->addFeatureLink();
        $this->addCorrespondences();
        $this->addAccessories();
        $this->addTabs();
    }

    private function addFeatureLink()
    {
        $features = $this->product->getFrontFeatures($this->context->language->id);

        foreach (Product::getProductCategoriesFull($this->product->id) as $productCategory) {
            if ($productCategory['link'] != '') {
                $features[] = array(
                    'name' => $productCategory['name'],
                    'value' => "<a href=".str_replace("#reference#", $this->product->reference, $productCategory['link'])." target=\"_blank\">".$this->product->reference." - ".$this->product->name."</a>",
                    'id_feature' => null,
                );
                break;
            }
        }

        $this->context->smarty->assign(array(
            'features' => $features,
        ));
    }

    private function addCorrespondences()
    {
        $correspondences = $this->product->getProductCorrespondences();

        $this->context->smarty->assign(array(
            'correspondences' => $correspondences,
        ));
    }

    private function addAccessories()
    {
        $accessories = $this->product->getProductAccessories();

        $this->context->smarty->assign(array(
            'references' => $accessories,
        ));
    }

    /**
     * Assign price and tax to the template
     */
    protected function assignPriceAndTax()
    {
        $webServiceDiva = new WebServiceDiva('<ACTION>TARIF_ART', '<DOS>1<TIERS>'.$this->context->cookie->tiers.'<REF>'.$this->product->reference.'<FICHE>1');

        try {
            $datas = $webServiceDiva->call();
            $stock = 0;
            $dispo = 0;
            $jauge = 0;
            $tarif = 0;
            $sousRefs = array();
            $nb_tarif = 0;
            $alerte = "";

            if ($datas && $datas->references) {
                foreach ($datas->references as $reference) {
                    if ($reference->trouve == 1) {
                        $stock = $reference->total_stock ? $reference->total_stock : 0;
                        $dispo = $reference->total_dispo ? $reference->total_dispo : 0;
                        $jauge = $reference->total_jauge ? $reference->total_jauge : 0;
                        $tarif = $reference->max_pun;
                        $sousRefs = $reference->sousRefs;
                        $nb_tarif = $reference->nbTarifs;
                        $alerte = $reference->alerte;
                    }
                }
            }

        } catch (SoapFault $fault) {
            throw new Exception('Error: SOAP Fault: (faultcode: {'.$fault->faultcode.'}, faultstring: {'.$fault->faultstring.'})');
        }

        $this->context->smarty->assign(array(
            'stock' => $stock,
            'dispo' => $dispo,
            'jauge' => $jauge,
            'tarif' => $tarif,
            'sousRefs' => $sousRefs,
            'nb_tarif' => $nb_tarif,
            'alerte' => $alerte
        ));

        $id_customer = (isset($this->context->customer) ? (int)$this->context->customer->id : 0);
        $id_group = (int)Group::getCurrent()->id;
        $id_country = $id_customer ? (int)Customer::getCurrentCountry($id_customer) : (int)Tools::getCountry();

        $group_reduction = GroupReduction::getValueForProduct($this->product->id, $id_group);
        if ($group_reduction === false) {
            $group_reduction = Group::getReduction((int)$this->context->cookie->id_customer) / 100;
        }

        // Tax
        $tax = (float)$this->product->getTaxesRate(new Address((int)$this->context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')}));
        $this->context->smarty->assign('tax_rate', $tax);

        $product_price_with_tax = Product::getPriceStatic($this->product->id, true, null, 6);
        if (Product::$_taxCalculationMethod == PS_TAX_INC) {
            $product_price_with_tax = Tools::ps_round($product_price_with_tax, 2);
        }
        $product_price_without_eco_tax = (float)$product_price_with_tax - $this->product->ecotax;

        $ecotax_rate = (float)Tax::getProductEcotaxRate($this->context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')});
        $ecotax_tax_amount = Tools::ps_round($this->product->ecotax, 2);
        if (Product::$_taxCalculationMethod == PS_TAX_INC && (int)Configuration::get('PS_TAX')) {
            $ecotax_tax_amount = Tools::ps_round($ecotax_tax_amount * (1 + $ecotax_rate / 100), 2);
        }

        $id_currency = (int)$this->context->cookie->id_currency;
        $id_product = (int)$this->product->id;
        $id_shop = $this->context->shop->id;

        $quantity_discounts = SpecificPrice::getQuantityDiscounts($id_product, $id_shop, $id_currency, $id_country, $id_group, null, true, (int)$this->context->customer->id);
        foreach ($quantity_discounts as &$quantity_discount) {
            if ($quantity_discount['id_product_attribute']) {
                $combination = new Combination((int)$quantity_discount['id_product_attribute']);
                $attributes = $combination->getAttributesName((int)$this->context->language->id);
                foreach ($attributes as $attribute) {
                    $quantity_discount['attributes'] = $attribute['name'].' - ';
                }
                $quantity_discount['attributes'] = rtrim($quantity_discount['attributes'], ' - ');
            }
            if ((int)$quantity_discount['id_currency'] == 0 && $quantity_discount['reduction_type'] == 'amount') {
                $quantity_discount['reduction'] = Tools::convertPriceFull($quantity_discount['reduction'], null, Context::getContext()->currency);
            }
        }

        $product_price = $this->product->getPrice(Product::$_taxCalculationMethod == PS_TAX_INC, false);
        $address = new Address($this->context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')});
        $this->context->smarty->assign(array(
            'quantity_discounts' => $this->formatQuantityDiscounts($quantity_discounts, $product_price, (float)$tax, $ecotax_tax_amount),
            'ecotax_tax_inc' => $ecotax_tax_amount,
            'ecotax_tax_exc' => Tools::ps_round($this->product->ecotax, 2),
            'ecotaxTax_rate' => $ecotax_rate,
            'productPriceWithoutEcoTax' => (float)$product_price_without_eco_tax,
            'group_reduction' => $group_reduction,
            'no_tax' => Tax::excludeTaxeOption() || !$this->product->getTaxesRate($address),
            'ecotax' => (!count($this->errors) && $this->product->ecotax > 0 ? Tools::convertPrice((float)$this->product->ecotax) : 0),
            'tax_enabled' => Configuration::get('PS_TAX') && !Configuration::get('AEUC_LABEL_TAX_INC_EXC'),
            'customer_group_without_tax' => Group::getPriceDisplayMethod($this->context->customer->id_default_group),
        ));
    }

    private function addTabs()
    {
        $tabs = $this->context->cookie->tabs;

        $this->context->smarty->assign(array(
            'tabs' => unserialize($tabs),
        ));
    }
}
