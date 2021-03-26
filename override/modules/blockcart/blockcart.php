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

class BlockCartOverride extends BlockCart
{

    public function assignContentVars($params)
    {
        global $errors;

        // Set currency
        if ((int)$params['cart']->id_currency && (int)$params['cart']->id_currency != $this->context->currency->id)
            $currency = new Currency((int)$params['cart']->id_currency);
        else
            $currency = $this->context->currency;

        $taxCalculationMethod = Group::getPriceDisplayMethod((int)Group::getCurrent()->id);

        $useTax = !($taxCalculationMethod == PS_TAX_EXC);

        $products = $params['cart']->getProducts(true);
        $nbTotalProducts = 0;
        foreach ($products as $product)
            $nbTotalProducts += (int)$product['cart_quantity'];
        $cart_rules = $params['cart']->getCartRules();

        if (empty($cart_rules))
            $base_shipping = $params['cart']->getOrderTotal($useTax, Cart::ONLY_SHIPPING);
        else
        {
            $base_shipping_with_tax    = $params['cart']->getOrderTotal(true, Cart::ONLY_SHIPPING);
            $base_shipping_without_tax = $params['cart']->getOrderTotal(false, Cart::ONLY_SHIPPING);
            if ($useTax)
                $base_shipping = $base_shipping_with_tax;
            else
                $base_shipping = $base_shipping_without_tax;
        }
        $shipping_cost = Tools::displayPrice($base_shipping, $currency);
        $shipping_cost_float = Tools::convertPrice($base_shipping, $currency);
        $wrappingCost = (float)($params['cart']->getOrderTotal($useTax, Cart::ONLY_WRAPPING));
        $totalToPay = $params['cart']->getOrderTotal($useTax);

        if ($useTax && Configuration::get('PS_TAX_DISPLAY') == 1)
        {
            $totalToPayWithoutTaxes = $params['cart']->getOrderTotal(false);
            $this->smarty->assign('tax_cost', Tools::displayPrice($totalToPay - $totalToPayWithoutTaxes, $currency));
        }

        // The cart content is altered for display
        foreach ($cart_rules as &$cart_rule)
        {
            if ($cart_rule['free_shipping'])
            {
                $shipping_cost = Tools::displayPrice(0, $currency);
                $shipping_cost_float = 0;
                $cart_rule['value_real'] -= Tools::convertPrice($base_shipping_with_tax, $currency);
                $cart_rule['value_tax_exc'] = Tools::convertPrice($base_shipping_without_tax, $currency);
            }
            if ($cart_rule['gift_product'])
            {
                foreach ($products as $key => &$product)
                {
                    if ($product['id_product'] == $cart_rule['gift_product']
                        && $product['id_product_attribute'] == $cart_rule['gift_product_attribute'])
                    {
                        $product['total_wt'] = Tools::ps_round($product['total_wt'] - $product['price_wt'],
                            (int)$currency->decimals * _PS_PRICE_DISPLAY_PRECISION_);
                        $product['total'] = Tools::ps_round($product['total'] - $product['price'],
                            (int)$currency->decimals * _PS_PRICE_DISPLAY_PRECISION_);
                        if ($product['cart_quantity'] > 1)
                        {
                            array_splice($products, $key, 0, array($product));
                            $products[$key]['cart_quantity'] = $product['cart_quantity'] - 1;
                            $product['cart_quantity'] = 1;
                        }
                        $product['is_gift'] = 1;
                        $cart_rule['value_real'] = Tools::ps_round($cart_rule['value_real'] - $product['price_wt'],
                            (int)$currency->decimals * _PS_PRICE_DISPLAY_PRECISION_);
                        $cart_rule['value_tax_exc'] = Tools::ps_round($cart_rule['value_tax_exc'] - $product['price'],
                            (int)$currency->decimals * _PS_PRICE_DISPLAY_PRECISION_);
                    }
                }
            }
        }

        $total_free_shipping = 0;
        if ($free_shipping = Tools::convertPrice(floatval(Configuration::get('PS_SHIPPING_FREE_PRICE')), $currency))
        {
            $total_free_shipping =  floatval($free_shipping - ($params['cart']->getOrderTotal(true, Cart::ONLY_PRODUCTS) +
                $params['cart']->getOrderTotal(true, Cart::ONLY_DISCOUNTS)));
            $discounts = $params['cart']->getCartRules(CartRule::FILTER_ACTION_SHIPPING);
            if ($total_free_shipping < 0)
                $total_free_shipping = 0;
            if (is_array($discounts) && count($discounts))
                $total_free_shipping = 0;
        }

        $this->smarty->assign(array(
            'products' => $products,
            'customizedDatas' => Product::getAllCustomizedDatas((int)($params['cart']->id)),
            'CUSTOMIZE_FILE' => Product::CUSTOMIZE_FILE,
            'CUSTOMIZE_TEXTFIELD' => Product::CUSTOMIZE_TEXTFIELD,
            'discounts' => $cart_rules,
            'nb_total_products' => (int)($nbTotalProducts),
            'shipping_cost' => $shipping_cost,
            'shipping_cost_float' => $shipping_cost_float,
            'show_wrapping' => false,
            'show_tax' => (int)(Configuration::get('PS_TAX_DISPLAY') == 1 && (int)Configuration::get('PS_TAX')),
            'wrapping_cost' => Tools::displayPrice($wrappingCost, $currency),
            'product_total' => Tools::displayPrice($params['cart']->getOrderTotal($useTax, Cart::BOTH_WITHOUT_SHIPPING), $currency),
            'total' => Tools::displayPrice($this->context->cookie->montant_total, $currency),
            'order_process' => Configuration::get('PS_ORDER_PROCESS_TYPE') ? 'order-opc' : 'order',
            'ajax_allowed' => (int)(Configuration::get('PS_BLOCK_CART_AJAX')) == 1 ? true : false,
            'static_token' => Tools::getToken(false),
            'free_shipping' => $total_free_shipping
        ));
        if (count($errors))
            $this->smarty->assign('errors', $errors);
        if (isset($this->context->cookie->ajax_blockcart_display))
            $this->smarty->assign('colapseExpandStatus', $this->context->cookie->ajax_blockcart_display);
    }
}
