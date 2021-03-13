<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_ClassLoader.php';

class TNTOfficiel_Cart
{
    /**
     * Prevent Construct.
     */
    final private function __construct()
    {
        trigger_error(sprintf('%s() %s is static.', __FUNCTION__, get_class($this)), E_USER_ERROR);
    }

    /**
     * Is shipping free for cart, through configuration.
     *
     * @param Cart $objArgCart
     * @param null $intArgCarrierID the current carrier for which price is to determine
     *
     * @return bool
     */
    public static function isCartShippingFree($objArgCart, $intArgCarrierID = null)
    {
        TNTOfficiel_Logstack::log();

        if ($intArgCarrierID === null) {
            $intArgCarrierID = $objArgCart->id_carrier;
        }

        $arrConfigShipping = Configuration::getMultiple(array(
            'PS_SHIPPING_FREE_PRICE',
            'PS_SHIPPING_FREE_WEIGHT'
        ));

        // Load carrier object.
        $objCarrier = TNTOfficiel_Carrier::loadCarrier($intArgCarrierID);
        // If carrier object not available.
        if ($objCarrier === null) {
            return true;
        }

        // If carrier is inactive or free.
        if (!$objCarrier->active || $objCarrier->getShippingMethod() == Carrier::SHIPPING_METHOD_FREE) {
            return true;
        }

        // Get cart amount to reach for free shipping.
        $fltFreeFeesPrice = 0;
        if (isset($arrConfigShipping['PS_SHIPPING_FREE_PRICE'])) {
            $fltFreeFeesPrice = (float)Tools::convertPrice(
                (float)$arrConfigShipping['PS_SHIPPING_FREE_PRICE'],
                Currency::getCurrencyInstance((int)$objArgCart->id_currency)
            );
        }
        // Free shipping if cart amount, inc. taxes, inc. product & discount, exc. shipping > PS_SHIPPING_FREE_PRICE
        if ($fltFreeFeesPrice > 0
            && $objArgCart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING, null, null, false) >= $fltFreeFeesPrice
        ) {
            return true;
        }

        // Free shipping if cart weight > PS_SHIPPING_FREE_WEIGHT
        if (isset($arrConfigShipping['PS_SHIPPING_FREE_WEIGHT'])
            && $objArgCart->getTotalWeight() >= (float)$arrConfigShipping['PS_SHIPPING_FREE_WEIGHT']
            && (float)$arrConfigShipping['PS_SHIPPING_FREE_WEIGHT'] > 0
        ) {
            return true;
        }

        return false;
    }

    /**
     * Get additional shipping cost for cart (exc. taxes).
     *
     * @param Cart $objArgCart
     * @param null $intArgCarrierID the current carrier for which price is to determine
     *
     * @return float
     */
    public static function getCartExtraShippingCost($objArgCart, $intArgCarrierID = null)
    {
        TNTOfficiel_Logstack::log();

        if ($intArgCarrierID === null) {
            $intArgCarrierID = $objArgCart->id_carrier;
        }

        $fltShippingCost = 0;
        $arrProducts = $objArgCart->getProducts();

        // If no product, no shipping extra cost.
        if (!count($arrProducts)) {
            return 0;
        }

        // If only virtual products in cart, no extra shipping cost.
        if ($objArgCart->isVirtualCart()) {
            return 0;
        }

        // If TNT carrier is free.
        $boolIsCartShippingFree = TNTOfficiel_Cart::isCartShippingFree($objArgCart, $intArgCarrierID);
        if ($boolIsCartShippingFree) {
            return 0;
        }

        // Load carrier object.
        $objCarrier = TNTOfficiel_Carrier::loadCarrier($intArgCarrierID);
        // If TNT carrier object not available, no extra shipping cost.
        if ($objCarrier === null) {
            return 0;
        }

        // Adding handling charges.
        $shipping_handling = Configuration::get('PS_SHIPPING_HANDLING');
        if (isset($shipping_handling) && $objCarrier->shipping_handling) {
            $fltShippingCost += (float)$shipping_handling;
        }

        // Adding additional shipping cost per product.
        foreach ($arrProducts as $product) {
            if (!$product['is_virtual']) {
                $fltShippingCost += $product['additional_shipping_cost'] * $product['cart_quantity'];
            }
        }

        $fltShippingCost = (float)Tools::convertPrice(
            $fltShippingCost,
            Currency::getCurrencyInstance((int)$objArgCart->id_currency)
        );

        return $fltShippingCost;
    }

    /**
     * Get all the cart discount codes separated.
     * Note: using Cart->getDiscounts() may cause infinite loop through Module->getOrderShippingCost().
     * Note: Cart->getOrderedCartRulesIds() unavailable in Prestashop 1.6.0.5.
     *
     * @param Cart $objArgCart
     *
     * @return array
     */
    public static function getCartDiscountCodes($objArgCart)
    {
        TNTOfficiel_Logstack::log();

        $intCartID = (int)$objArgCart->id;
        $intLangID = (int)$objArgCart->id_lang;

        $arrDiscountCodes = array();

        $strCacheKey = TNTOfficielCache::getKeyIdentifier(__CLASS__, __FUNCTION__, array(
            'cartID' => $intCartID,
            'langID' => $intLangID
        ));

        if (!Cache::isStored($strCacheKey)) {
            $strTablePrefix = _DB_PREFIX_;
            $strSQLSelectCartRules = <<<SQL
SELECT cr.`id_cart_rule`
FROM `${strTablePrefix}cart_cart_rule` cd
LEFT JOIN `${strTablePrefix}cart_rule` cr
  ON cd.`id_cart_rule` = cr.`id_cart_rule`
LEFT JOIN `${strTablePrefix}cart_rule_lang` crl
  ON (cd.`id_cart_rule` = crl.`id_cart_rule`
    AND crl.id_lang = ${intLangID})
WHERE `id_cart` = ${intCartID}
ORDER BY cr.priority ASC;
SQL;
            $arrCartRulesIdList = Db::getInstance()->executeS($strSQLSelectCartRules);
            Cache::store($strCacheKey, $arrCartRulesIdList);
        } else {
            $arrCartRulesIdList = Cache::retrieve($strCacheKey);
        }

        foreach ($arrCartRulesIdList as $arrCartRulesRow) {
            $objCartRule = new CartRule($arrCartRulesRow['id_cart_rule']);
            $arrDiscountCodes[] = $objCartRule->code;
        }

        return $arrDiscountCodes;
    }

    /**
     * Get delivery option preventing recursions.
     *
     * @return array
     */
    public static function getDeliveryOption($objArgCart)
    {
        TNTOfficiel_Logstack::log();

        if ($objArgCart === null) {
            return array();
        }

        static $arrMemoize = array();

        $strMemKey = $objArgCart->delivery_option;
        if (isset($arrMemoize[$strMemKey])) {
            return $arrMemoize[$strMemKey];
        }

        // PS 1.6.21 use JSON.
        $arrDeliveryOption = Tools::jsonDecode($objArgCart->delivery_option, true);
        if (!is_array($arrDeliveryOption)) {
            $arrDeliveryOption = Tools::unSerialize($objArgCart->delivery_option);
        }
        if (!is_array($arrDeliveryOption)) {
            $arrDeliveryOption = array();
        }

        // Mem.
        $arrMemoize[$strMemKey] = $arrDeliveryOption;

        return $arrDeliveryOption;
    }

    /**
     * Determine if multi-shipping state from cart is supported.
     *
     * @param Cart $objArgCart
     *
     * @return bool
     */
    public static function isMultiShippingSupport($objArgCart)
    {
        TNTOfficiel_Logstack::log();

        $boolMultiShippingSupport = true;

        $arrDeliveryOption = TNTOfficiel_Cart::getDeliveryOption($objArgCart);
        // If multiple address for cart.
        if (count($arrDeliveryOption) > 1) {
            // Not supported.
            $boolMultiShippingSupport = false;
        } elseif (is_array($arrDeliveryOption)) {
            // If an address have an option with different carrier.
            foreach ($arrDeliveryOption as $id_address_delivery => $strCarrierIDList) {
                if (preg_match('/^(?:([0-9]++),?(?:\1,?)*)$/ui', $strCarrierIDList) !== 1) {
                    // Not supported.
                    $boolMultiShippingSupport = false;
                    break;
                }
            }
        }

        return $boolMultiShippingSupport;
    }

    /**
     * @param Cart $objArgCart
     *
     * @return array
     */
    public static function isPaymentReady($objArgCart)
    {
        TNTOfficiel_Logstack::log();

        $intCartID = (int)$objArgCart->id;

        $arrResult = array(
            'error' => null,
            'carrier' => null
        );

        $arrDeliveryOption = TNTOfficiel_Cart::getDeliveryOption($objArgCart);

        if (!$objArgCart) {
            $arrResult['error'] = 'errorTechnical'; // 'ErrorNoCartObject';
        } elseif (count($arrDeliveryOption) === 0) {
            $arrResult['error'] = 'errorNoDeliveryOptionSelected';
        }

        // If no error.
        if (!is_string($arrResult['error'])) {
            // Multi-Shipping with multiple address or different carrier not supported.
            $boolMultiShippingSupport = TNTOfficiel_Cart::isMultiShippingSupport($objArgCart);
            if (!$boolMultiShippingSupport) {
                // TNT shouldn't be selected (no price available).
            } else {
                if ($objArgCart->id_carrier == 0) {
                    $arrResult['error'] = 'errorNoDeliveryOptionSelected';
                } elseif (!$objArgCart->id_address_delivery) {
                    $arrResult['error'] = 'errorNoDeliveryAddressSelected';
                } else {
                    $boolIsTntCarrier = (bool)TNTOfficiel_Carrier::isTNTOfficielCarrierID($objArgCart->id_carrier);
                    // TNT option selected.
                    if ($boolIsTntCarrier) {
                        // If the selected carrier (core) is TNT, we need handle it.
                        $arrResult['carrier'] = TNTOfficiel::MODULE_NAME;

                        // Get the current TNT carrier code name from current ID.
                        $strCarrierCode = TNTOfficiel_Carrier::getCurrentCarrierCode($objArgCart->id_carrier);
                        // Load TNT cart info or create a new one for it's ID.
                        $objTNTCartModel = TNTOfficielCart::loadCartID($intCartID);

                        if ($objTNTCartModel === null) {
                            $arrResult['error'] = 'errorTechnical';
                        } elseif ($strCarrierCode === null) {
                            // If carrier of cart is not a current carrier from TNT module.
                            $arrResult['error'] = 'errorTechnical';
                        } elseif (!($strCarrierCode && count($arrDeliveryOption) !== 0)) {
                            $arrResult['error'] = 'errorNoDeliveryOptionSelected';
                        } else {
                            $arrDeliveryPoint = $objTNTCartModel->getDeliveryPoint();
                            if (strpos($strCarrierCode, 'DEPOT') && !isset($arrDeliveryPoint['pex'])) {
                                $arrResult['error'] = 'errorNoDeliveryPointSelected';
                            } elseif (strpos($strCarrierCode, 'DROPOFFPOINT') && !isset($arrDeliveryPoint['xett'])) {
                                $arrResult['error'] = 'errorNoDeliveryPointSelected';
                            } else {
                                // Validate using the customer and address as default values.
                                $arrFormCartAddressValidate = TNTOfficiel_Address::validateDeliveryInfo(
                                    $objTNTCartModel->customer_email,
                                    $objTNTCartModel->customer_mobile,
                                    $objTNTCartModel->address_building,
                                    $objTNTCartModel->address_accesscode,
                                    $objTNTCartModel->address_floor
                                );
                                if ($arrFormCartAddressValidate['length'] !== 0) {
                                    $arrResult['error'] = 'validateAdditionalCarrierInfo';
                                }
                            }
                        }
                    }
                }
            }
        }

        return $arrResult;
    }
}
