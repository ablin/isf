<?php
class Cart extends CartCore
{

    private $_productList = array();

    /** @var string Carrier */
    public $carrier;

    /**
     * Return cart products
     *
     * @result array Products
     */
    public function getProducts($refresh = false, $id_product = false, $id_country = null)
    {
        if (!$this->id) {
            return array();
        }
        // Product cache must be strictly compared to NULL, or else an empty cart will add dozens of queries
        if ($this->_products !== null && !$refresh) {
            // Return product row with specified ID if it exists
            if (is_int($id_product)) {
                foreach ($this->_products as $product) {
                    if ($product['id_product'] == $id_product) {
                        return array($product);
                    }
                }
                return array();
            }
            return $this->_products;
        }

        // Build query
        $sql = new DbQuery();

        // Build SELECT
        $sql->select('cp.`id_product_attribute`, cp.`id_product`, cp.`quantity` AS cart_quantity, cp.id_shop, pl.`name`, p.`is_virtual`,
                        pl.`description_short`, pl.`available_now`, pl.`available_later`, product_shop.`id_category_default`, p.`id_supplier`,
                        p.`id_manufacturer`, product_shop.`on_sale`, product_shop.`ecotax`, product_shop.`additional_shipping_cost`,
                        product_shop.`available_for_order`, product_shop.`price`, product_shop.`active`, product_shop.`unity`, product_shop.`unit_price_ratio`,
                        stock.`quantity` AS quantity_available, p.reference, p.`width`, p.`height`, p.`depth`, stock.`out_of_stock`, p.`weight`,
                        p.`date_add`, p.`date_upd`, IFNULL(stock.quantity, 0) as quantity, pl.`link_rewrite`, cl.`link_rewrite` AS category,
                        CONCAT(LPAD(cp.`id_product`, 10, 0), LPAD(IFNULL(cp.`id_product_attribute`, 0), 10, 0), IFNULL(cp.`id_address_delivery`, 0)) AS unique_id, cp.id_address_delivery,
                        product_shop.advanced_stock_management, ps.product_supplier_reference supplier_reference');

        // Build FROM
        $sql->from('cart_product', 'cp');

        // Build JOIN
        $sql->leftJoin('product', 'p', 'p.`id_product` = cp.`id_product`');
        $sql->innerJoin('product_shop', 'product_shop', '(product_shop.`id_shop` = cp.`id_shop` AND product_shop.`id_product` = p.`id_product`)');
        $sql->leftJoin('product_lang', 'pl', '
            p.`id_product` = pl.`id_product`
            AND pl.`id_lang` = '.(int)$this->id_lang.Shop::addSqlRestrictionOnLang('pl', 'cp.id_shop')
        );

        $sql->leftJoin('category_lang', 'cl', '
            product_shop.`id_category_default` = cl.`id_category`
            AND cl.`id_lang` = '.(int)$this->id_lang.Shop::addSqlRestrictionOnLang('cl', 'cp.id_shop')
        );

        $sql->leftJoin('product_supplier', 'ps', 'ps.`id_product` = cp.`id_product` AND ps.`id_product_attribute` = cp.`id_product_attribute` AND ps.`id_supplier` = p.`id_supplier`');

        // @todo test if everything is ok, then refactorise call of this method
        $sql->join(Product::sqlStock('cp', 'cp'));

        // Build WHERE clauses
        $sql->where('cp.`id_cart` = '.(int)$this->id);
        if ($id_product) {
            $sql->where('cp.`id_product` = '.(int)$id_product);
        }
        $sql->where('p.`id_product` IS NOT NULL');

        // Build ORDER BY
        $sql->orderBy('cp.`date_add`, cp.`id_product`, cp.`id_product_attribute` ASC');

        if (Customization::isFeatureActive()) {
            $sql->select('cu.`id_customization`, cu.`quantity` AS customization_quantity');
            $sql->leftJoin('customization', 'cu',
                'p.`id_product` = cu.`id_product` AND cp.`id_product_attribute` = cu.`id_product_attribute` AND cu.`id_cart` = '.(int)$this->id);
            $sql->groupBy('cp.`id_product_attribute`, cp.`id_product`, cp.`id_shop`');
        } else {
            $sql->select('NULL AS customization_quantity, NULL AS id_customization');
        }

        if (Combination::isFeatureActive()) {
            $sql->select('
                product_attribute_shop.`price` AS price_attribute, product_attribute_shop.`ecotax` AS ecotax_attr,
                IF (IFNULL(pa.`reference`, \'\') = \'\', p.`reference`, pa.`reference`) AS reference,
                (p.`weight`+ pa.`weight`) weight_attribute,
                IF (IFNULL(pa.`ean13`, \'\') = \'\', p.`ean13`, pa.`ean13`) AS ean13,
                IF (IFNULL(pa.`upc`, \'\') = \'\', p.`upc`, pa.`upc`) AS upc,
                IFNULL(product_attribute_shop.`minimal_quantity`, product_shop.`minimal_quantity`) as minimal_quantity,
                IF(product_attribute_shop.wholesale_price > 0,  product_attribute_shop.wholesale_price, product_shop.`wholesale_price`) wholesale_price,
                al.name AS sous_reference
            ');

            $sql->leftJoin('product_attribute', 'pa', 'pa.`id_product_attribute` = cp.`id_product_attribute`');
            $sql->leftJoin('product_attribute_shop', 'product_attribute_shop', '(product_attribute_shop.`id_shop` = cp.`id_shop` AND product_attribute_shop.`id_product_attribute` = pa.`id_product_attribute`)');
            $sql->leftJoin('product_attribute_combination', 'product_attribute_combination', 'pa.`id_product_attribute` = product_attribute_combination.`id_product_attribute`');
            $sql->leftJoin('attribute_lang', 'al', 'product_attribute_combination.`id_attribute` = al.`id_attribute` AND al.id_lang =  '.(int)$this->id_lang);
        } else {
            $sql->select(
                'p.`reference` AS reference, p.`ean13`,
                p.`upc` AS upc, product_shop.`minimal_quantity` AS minimal_quantity, product_shop.`wholesale_price` wholesale_price'
            );
        }

        $sql->select('image_shop.`id_image` id_image, il.`legend`');
        $sql->leftJoin('image_shop', 'image_shop', 'image_shop.`id_product` = p.`id_product` AND image_shop.cover=1 AND image_shop.id_shop='.(int)$this->id_shop);
        $sql->leftJoin('image_lang', 'il', 'il.`id_image` = image_shop.`id_image` AND il.`id_lang` = '.(int)$this->id_lang);

        $result = Db::getInstance()->executeS($sql);

        // Reset the cache before the following return, or else an empty cart will add dozens of queries
        $products_ids = array();
        $products = array();
        $pa_ids = array();
        if ($result) {
            foreach ($result as $key => $row) {
                $products_ids[] = $row['id_product'];
                $products[$row['id_product']] = array(
                    'reference' => $row['reference'],
                    'sous_reference' => $row['sous_reference'],
                    'quantity' => $row['cart_quantity']
                );
                $pa_ids[] = $row['id_product_attribute'];
                $specific_price = SpecificPrice::getSpecificPrice($row['id_product'], $this->id_shop, $this->id_currency, $id_country, $this->id_shop_group, $row['cart_quantity'], $row['id_product_attribute'], $this->id_customer, $this->id);
                if ($specific_price) {
                    $reduction_type_row = array('reduction_type' => $specific_price['reduction_type']);
                } else {
                    $reduction_type_row = array('reduction_type' => 0);
                }

                $result[$key] = array_merge($row, $reduction_type_row);
            }
        }
        // Thus you can avoid one query per product, because there will be only one query for all the products of the cart
        Product::cacheProductsFeatures($products_ids);
        Cart::cacheSomeAttributesLists($pa_ids, $this->id_lang);

        $this->_products = array();
        if (empty($result)) {
            return array();
        }

        $cart_shop_context = Context::getContext()->cloneContext();

        $accessories = array();
        foreach ($result as &$row) {
            if (isset($row['ecotax_attr']) && $row['ecotax_attr'] > 0) {
                $row['ecotax'] = (float)$row['ecotax_attr'];
            }

            $row['stock_quantity'] = (int)$row['quantity'];
            // for compatibility with 1.2 themes
            $row['quantity'] = (int)$row['cart_quantity'];

            if ($cart_shop_context->shop->id != $row['id_shop']) {
                $cart_shop_context->shop = new Shop((int)$row['id_shop']);
            }

            $row['description_short'] = Tools::nl2br($row['description_short']);

            // check if a image associated with the attribute exists
            if ($row['id_product_attribute']) {
                $row2 = Image::getBestImageAttribute($row['id_shop'], $this->id_lang, $row['id_product'], $row['id_product_attribute']);
                if ($row2) {
                    $row = array_merge($row, $row2);
                }
            }

            $row['id_image'] = Product::defineProductImage($row, $this->id_lang);
            $row['allow_oosp'] = Product::isAvailableWhenOutOfStock($row['out_of_stock']);
            $row['features'] = Product::getFeaturesStatic((int)$row['id_product']);

            if (array_key_exists($row['id_product_attribute'].'-'.$this->id_lang, self::$_attributesLists)) {
                $row = array_merge($row, self::$_attributesLists[$row['id_product_attribute'].'-'.$this->id_lang]);
            }

            $row = Product::getTaxesInformations($row, $cart_shop_context);

            $product = new Product((int)$row['id_product']);
            $row['accessories'] = $product->getAccessories((int)$this->id_lang);
            if ($row['accessories'] && count($row['accessories']) > 0) {
                foreach ($row['accessories'] as $accessory) {
                    if (!in_array($accessory['reference'], $accessories)) {
                        $accessories[] = $accessory['reference'];
                    }
                }
            }

            $this->_products[] = $row;
        }

        if (count($accessories) > 0) {

            $productIds = implode(";", $accessories);
            $artParams = '<DOS>1<TIERS>'.Context::getContext()->cookie->tiers.'<REF>'.$productIds.'<FICHE>0';

            $webServiceDiva = new WebServiceDiva('<ACTION>TARIF_ART', $artParams);

            try {
                $datas = $webServiceDiva->call();
                if ($datas && $datas->references) {

                    foreach ($datas->references as $reference) {

                        if ($reference->trouve == 1) {
                            foreach ($this->_products as $keyProduct => $product) {
                                if ($product['accessories'] && count($product['accessories'] > 0)) {
                                    foreach ($product['accessories'] as $keyAccessory => $accessory) {
                                        if ($accessory['reference'] == (string) $reference->ref) {
                                            $this->_products[$keyProduct]['accessories'][$keyAccessory]['total_stock'] = $reference->total_stock;
                                            $this->_products[$keyProduct]['accessories'][$keyAccessory]['total_dispo'] = $reference->total_dispo;
                                            $this->_products[$keyProduct]['accessories'][$keyAccessory]['total_jauge'] = $reference->total_jauge;
                                            $this->_products[$keyProduct]['accessories'][$keyAccessory]['tarif'] = $reference->max_pun;
                                            $this->_products[$keyProduct]['accessories'][$keyAccessory]['nb_tarif'] = $reference->nbTarifs;
                                            $this->_products[$keyProduct]['accessories'][$keyAccessory]['alerte'] = $reference->alerte;
                                        }
                                    }
                                }
                            }
                        } else {
                            foreach ($this->_products as $keyProduct => $product) {
                                if ($product['accessories'] && count($product['accessories'] > 0)) {
                                    foreach ($product['accessories'] as $keyAccessory => $accessory) {
                                        if ($accessory['reference'] == (string) $reference->ref) {
                                            $this->_products[$keyProduct]['accessories'][$keyAccessory]['total_stock'] = -1;
                                            $this->_products[$keyProduct]['accessories'][$keyAccessory]['total_dispo'] = -1;
                                            $this->_products[$keyProduct]['accessories'][$keyAccessory]['total_jauge'] = -1;
                                            $this->_products[$keyProduct]['accessories'][$keyAccessory]['tarif'] = 0;
                                            $this->_products[$keyProduct]['accessories'][$keyAccessory]['nb_tarif'] = 0;
                                            $this->_products[$keyProduct]['accessories'][$keyAccessory]['alerte'] = "";
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

            } catch (SoapFault $fault) {
                throw new Exception('Error: SOAP Fault: (faultcode: {'.$fault->faultcode.'}, faultstring: {'.$fault->faultstring.'})');
            }
        }

        $params = '';

        if (Tools::getValue('delivery_option')) {
            $params .= "<BLMOD>".Tools::getValue('delivery_option');
        }

        foreach ($this->_products as $product) {
            $params .= '<REF>'.$product['reference'].'<SREF1>'.$product['sous_reference'].'<SREF2> <QTE>'.$product['quantity'];
        }

        $cartParams = '<DOS>1<TIERS>'.Context::getContext()->cookie->tiers.'<LOGIN>'.Context::getContext()->cookie->email.$params;

        $webServiceDiva = new WebServiceDiva('<ACTION>PANIER', $cartParams);

        try {
            $datas = $webServiceDiva->call();
            if ($datas && isset($datas->montantTotal)) {
                foreach ($datas->references as $reference) {
                    $this->_productList[$reference->ref.$reference->sref1] = array(
                        'pub' => isset($reference->pub) ? $reference->pub : "",
                        'remise' => isset($reference->remise) ? $reference->remise : "",
                        'pun' => isset($reference->pun) ? $reference->pun : "",
                        'mont' => isset($reference->mont) ? $reference->mont : "",
                        'stock' => isset($reference->qteStock) ? $reference->qteStock : "",
                        'ref_des' => isset($reference->ref_des) ? $reference->ref_des : "",
                        'frais_supp' => isset($reference->frais_supp) ? $reference->frais_supp : "",
                        'alerte' => isset($reference->alerte) ? $reference->alerte : "",
                    );
                }

                Context::getContext()->cookie->montant_total = $datas->montantTotal;
                $this->port_ht = $datas->portHT;
                Context::getContext()->cookie->montant_ht = $datas->montantHT;
                Context::getContext()->cookie->montant_ttc = $datas->montantTTC;
                Context::getContext()->cookie->montant_tva = $datas->montantTVA;
                Context::getContext()->cookie->poids_total = $datas->poidsTotal;
            }

        } catch (SoapFault $fault) {
            throw new Exception('Error: SOAP Fault: (faultcode: {'.$fault->faultcode.'}, faultstring: {'.$fault->faultstring.'})');
        }

        if ($datas && $datas->references) {
            foreach ($datas->references as $reference) {
                $this->_productList[$reference->ref.$reference->sref1] = array(
                    'pub' => isset($reference->pub) ? $reference->pub : "",
                    'remise' => isset($reference->remise) ? $reference->remise : "",
                    'pun' => isset($reference->pun) ? $reference->pun : "",
                    'mont' => isset($reference->mont) ? $reference->mont : "",
                    'stock' => isset($reference->qteStock) ? $reference->qteStock : "",
                    'ref_des' => isset($reference->ref_des) ? $reference->ref_des : "",
                    'frais_supp' => isset($reference->frais_supp) ? $reference->frais_supp : "",
                    'alerte' => isset($reference->alerte) ? $reference->alerte : "",
                );
            }

            Context::getContext()->cookie->montant_total = $datas->montantTotal;
            $this->port_ht = $datas->portHT;
            Context::getContext()->cookie->montant_ht = $datas->montantHT;
            Context::getContext()->cookie->montant_ttc = $datas->montantTTC;
            Context::getContext()->cookie->montant_tva = $datas->montantTVA;
            Context::getContext()->cookie->poids_total = $datas->poidsTotal;
        }

        foreach ($this->_products as $key => $product) {
            $reference = $product['reference'].$product['sous_reference'];
            if (isset($this->_productList[$reference])) {
                $this->_products[$key]['price'] = $this->_productList[$reference]['pun'];
                $this->_products[$key]['price_without_reduction'] = $this->_productList[$reference]['pun'];
                $this->_products[$key]['price_with_reduction'] = $this->_productList[$reference]['pun'] - $this->_productList[$reference]['remise'];
                $this->_products[$key]['price_with_reduction_without_tax'] = $this->_productList[$reference]['pub'] - $this->_productList[$reference]['remise'];
                $this->_products[$key]['price_wt'] = $this->_productList[$reference]['pun'];
                $this->_products[$key]['total'] = $this->_productList[$reference]['mont'];
                $this->_products[$key]['total_wt'] = $this->_productList[$reference]['mont'];
                $this->_products[$key]['quantity_available'] = $this->_productList[$reference]['stock'];
                $this->_products[$key]['ref_des'] = $this->_productList[$reference]['ref_des'];
                $this->_products[$key]['frais_supp'] = $this->_productList[$reference]['frais_supp'];
                $this->_products[$key]['alerte'] = $this->_productList[$reference]['alerte'];
            }
        }

        return $this->_products;
    }

    /**
    * Return useful informations for cart
    *
    * @return array Cart details
    */
    public function getSummaryDetails($id_lang = null, $refresh = false)
    {
        $context = Context::getContext();
        if (!$id_lang) {
            $id_lang = $context->language->id;
        }

        $delivery = new Address((int)$this->id_address_delivery);
        $invoice = new Address((int)$this->id_address_invoice);

        // New layout system with personalization fields
        $formatted_addresses = array(
            'delivery' => AddressFormat::getFormattedLayoutData($delivery),
            'invoice' => AddressFormat::getFormattedLayoutData($invoice)
        );

        $base_total_tax_inc = $this->getOrderTotal(true);
        $base_total_tax_exc = $this->getOrderTotal(false);

        $total_tax = $this->getTVA();

        $currency = new Currency($this->id_currency);

        $products = $this->getProducts($refresh);

        foreach ($products as $key => &$product) {
            $product['price_without_quantity_discount'] = Product::getPriceStatic(
                $product['id_product'],
                !Product::getTaxCalculationMethod(),
                $product['id_product_attribute'],
                6,
                null,
                false,
                false
            );

            if ($product['reduction_type'] == 'amount') {
                $reduction = (!Product::getTaxCalculationMethod() ? (float)$product['price_wt'] : (float)$product['price']) - (float)$product['price_without_quantity_discount'];
                $product['reduction_formatted'] = Tools::displayPrice($reduction);
            }
        }

        $gift_products = array();
        $cart_rules = $this->getCartRules();
        $total_shipping = $this->getTotalShippingCost();
        $total_shipping_tax_exc = $this->getTotalShippingCost(null, false);
        $total_products_wt = $this->getOrderTotal(true, Cart::ONLY_PRODUCTS);
        $total_products = $this->getOrderTotal(false, Cart::ONLY_PRODUCTS);
        $total_discounts = $this->getOrderTotal(true, Cart::ONLY_DISCOUNTS);
        $total_discounts_tax_exc = $this->getOrderTotal(false, Cart::ONLY_DISCOUNTS);

        // The cart content is altered for display
        foreach ($cart_rules as &$cart_rule) {
            // If the cart rule is automatic (wihtout any code) and include free shipping, it should not be displayed as a cart rule but only set the shipping cost to 0
            if ($cart_rule['free_shipping'] && (empty($cart_rule['code']) || preg_match('/^'.CartRule::BO_ORDER_CODE_PREFIX.'[0-9]+/', $cart_rule['code']))) {

                $cart_rule['value_real'] -= $total_shipping;
                $cart_rule['value_tax_exc'] -= $total_shipping_tax_exc;
                $cart_rule['value_real'] = Tools::ps_round($cart_rule['value_real'], (int)$context->currency->decimals * _PS_PRICE_COMPUTE_PRECISION_);
                $cart_rule['value_tax_exc'] = Tools::ps_round($cart_rule['value_tax_exc'], (int)$context->currency->decimals * _PS_PRICE_COMPUTE_PRECISION_);
                if ($total_discounts > $cart_rule['value_real']) {
                    $total_discounts -= $total_shipping;
                }
                if ($total_discounts_tax_exc > $cart_rule['value_tax_exc']) {
                    $total_discounts_tax_exc -= $total_shipping_tax_exc;
                }

                // Update total shipping
                $total_shipping = 0;
                $total_shipping_tax_exc = 0;
            }

            if ($cart_rule['gift_product']) {
                foreach ($products as $key => &$product) {
                    if (empty($product['gift']) && $product['id_product'] == $cart_rule['gift_product'] && $product['id_product_attribute'] == $cart_rule['gift_product_attribute']) {
                        // Update total products
                        $total_products_wt = Tools::ps_round($total_products_wt - $product['price_wt'], (int)$context->currency->decimals * _PS_PRICE_COMPUTE_PRECISION_);
                        $total_products = Tools::ps_round($total_products - $product['price'], (int)$context->currency->decimals * _PS_PRICE_COMPUTE_PRECISION_);

                        // Update total discounts
                        $total_discounts = Tools::ps_round($total_discounts - $product['price_wt'], (int)$context->currency->decimals * _PS_PRICE_COMPUTE_PRECISION_);
                        $total_discounts_tax_exc = Tools::ps_round($total_discounts_tax_exc - $product['price'], (int)$context->currency->decimals * _PS_PRICE_COMPUTE_PRECISION_);

                        // Update cart rule value
                        $cart_rule['value_real'] = Tools::ps_round($cart_rule['value_real'] - $product['price_wt'], (int)$context->currency->decimals * _PS_PRICE_COMPUTE_PRECISION_);
                        $cart_rule['value_tax_exc'] = Tools::ps_round($cart_rule['value_tax_exc'] - $product['price'], (int)$context->currency->decimals * _PS_PRICE_COMPUTE_PRECISION_);

                        // Update product quantity
                        $product['total_wt'] = Tools::ps_round($product['total_wt'] - $product['price_wt'], (int)$currency->decimals * _PS_PRICE_COMPUTE_PRECISION_);
                        $product['total'] = Tools::ps_round($product['total'] - $product['price'], (int)$currency->decimals * _PS_PRICE_COMPUTE_PRECISION_);
                        $product['cart_quantity']--;

                        if (!$product['cart_quantity']) {
                            unset($products[$key]);
                        }

                        // Add a new product line
                        $gift_product = $product;
                        $gift_product['cart_quantity'] = 1;
                        $gift_product['price'] = 0;
                        $gift_product['price_wt'] = 0;
                        $gift_product['total_wt'] = 0;
                        $gift_product['total'] = 0;
                        $gift_product['gift'] = true;
                        $gift_products[] = $gift_product;

                        break; // One gift product per cart rule
                    }
                }
            }
        }

        foreach ($cart_rules as $key => &$cart_rule) {
            if (((float)$cart_rule['value_real'] == 0 && (int)$cart_rule['free_shipping'] == 0)) {
                unset($cart_rules[$key]);
            }
        }

        $summary = array(
            'delivery' => $delivery,
            'delivery_state' => State::getNameById($delivery->id_state),
            'invoice' => $invoice,
            'invoice_state' => State::getNameById($invoice->id_state),
            'formattedAddresses' => $formatted_addresses,
            'products' => array_values($products),
            'gift_products' => $gift_products,
            'discounts' => array_values($cart_rules),
            'is_virtual_cart' => (int)$this->isVirtualCart(),
            'total_discounts' => $total_discounts,
            'total_discounts_tax_exc' => $total_discounts_tax_exc,
            'total_wrapping' => $this->getOrderTotal(true, Cart::ONLY_WRAPPING),
            'total_wrapping_tax_exc' => $this->getOrderTotal(false, Cart::ONLY_WRAPPING),
            'total_shipping' => $total_shipping,
            'total_shipping_tax_exc' => $total_shipping_tax_exc,
            'total_products_wt' => $total_products_wt,
            'total_products' => $total_products,
            'total_price' => $base_total_tax_inc,
            'total_tax' => $total_tax,
            'total_price_without_tax' => $base_total_tax_exc,
            'is_multi_address_delivery' => $this->isMultiAddressDelivery() || ((int)Tools::getValue('multi-shipping') == 1),
            'free_ship' =>!$total_shipping && !count($this->getDeliveryAddressesWithoutCarriers(true, $errors)),
            'carrier' => new Carrier($this->id_carrier, $id_lang),
        );

        $hook = Hook::exec('actionCartSummary', $summary, null, true);
        if (is_array($hook)) {
            $summary = array_merge($summary, array_shift($hook));
        }

        return $summary;
    }

    /**
    * This function returns the total cart amount
    *
    * Possible values for $type:
    * Cart::ONLY_PRODUCTS
    * Cart::ONLY_DISCOUNTS
    * Cart::BOTH
    * Cart::BOTH_WITHOUT_SHIPPING
    * Cart::ONLY_SHIPPING
    * Cart::ONLY_WRAPPING
    * Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING
    * Cart::ONLY_PHYSICAL_PRODUCTS_WITHOUT_SHIPPING
    *
    * @param bool $withTaxes With or without taxes
    * @param int $type Total type
    * @param bool $use_cache Allow using cache of the method CartRule::getContextualValue
    * @return float Order total
    */
    public function getOrderTotal($with_taxes = true, $type = Cart::BOTH, $products = null, $id_carrier = null, $use_cache = true)
    {
        if ($with_taxes) {
            return Context::getContext()->cookie->montant_ttc;
        } else {
            if ($type == Cart::ONLY_PRODUCTS) {
                return Context::getContext()->cookie->montant_total;
            } else {
                return Context::getContext()->cookie->montant_ht;
            }
        }
    }

    /**
    * Return shipping total for the cart
    *
    * @param array|null $delivery_option Array of the delivery option for each address
    * @param bool $use_tax
    * @param Country|null $default_country
    * @return float Shipping total
    */
    public function getTotalShippingCost($delivery_option = null, $use_tax = true, Country $default_country = null)
    {
        return isset($this->port_ht) ? $this->port_ht : 0;
    }

    public function getTva()
    {
        $base_total_tax_inc = $this->getOrderTotal(true);
        $base_total_tax_exc = $this->getOrderTotal(false);

        $tax = $base_total_tax_inc - $base_total_tax_exc;
        Context::getContext()->cookie->montant_tva = $tax;

        return Context::getContext()->cookie->montant_tva;
    }

}
