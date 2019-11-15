<?php
class Product extends ProductCore
{
    /** @var string Name */
    public $name;

    /** @var string Object last modification date in divalto */
    public $date_upd_divalto;

    public static $definition = array(
        'table' => 'product',
        'primary' => 'id_product',
        'multilang' => true,
        'multilang_shop' => true,
        'fields' => array(
            /* Classic fields */
            'id_shop_default' =>            array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_manufacturer' =>            array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_supplier' =>                array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'reference' =>                    array('type' => self::TYPE_STRING, 'validate' => 'isReference', 'size' => 32),
            'supplier_reference' =>        array('type' => self::TYPE_STRING, 'validate' => 'isReference', 'size' => 32),
            'location' =>                    array('type' => self::TYPE_STRING, 'validate' => 'isReference', 'size' => 64),
            'width' =>                        array('type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat'),
            'height' =>                    array('type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat'),
            'depth' =>                        array('type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat'),
            'weight' =>                    array('type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat'),
            'quantity_discount' =>            array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'ean13' =>                        array('type' => self::TYPE_STRING, 'validate' => 'isEan13', 'size' => 13),
            'upc' =>                        array('type' => self::TYPE_STRING, 'validate' => 'isUpc', 'size' => 12),
            'cache_is_pack' =>                array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'cache_has_attachments' =>        array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'is_virtual' =>                array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),

            /* Shop fields */
            'id_category_default' =>        array('type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isUnsignedId'),
            'id_tax_rules_group' =>        array('type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isUnsignedId'),
            'on_sale' =>                    array('type' => self::TYPE_BOOL, 'shop' => true, 'validate' => 'isBool'),
            'online_only' =>                array('type' => self::TYPE_BOOL, 'shop' => true, 'validate' => 'isBool'),
            'ecotax' =>                    array('type' => self::TYPE_FLOAT, 'shop' => true, 'validate' => 'isPrice'),
            'minimal_quantity' =>            array('type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isUnsignedInt'),
            'price' =>                        array('type' => self::TYPE_FLOAT, 'shop' => true, 'validate' => 'isPrice', 'required' => true),
            'wholesale_price' =>            array('type' => self::TYPE_FLOAT, 'shop' => true, 'validate' => 'isPrice'),
            'unity' =>                        array('type' => self::TYPE_STRING, 'shop' => true, 'validate' => 'isString'),
            'unit_price_ratio' =>            array('type' => self::TYPE_FLOAT, 'shop' => true),
            'additional_shipping_cost' =>    array('type' => self::TYPE_FLOAT, 'shop' => true, 'validate' => 'isPrice'),
            'customizable' =>                array('type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isUnsignedInt'),
            'text_fields' =>                array('type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isUnsignedInt'),
            'uploadable_files' =>            array('type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isUnsignedInt'),
            'active' =>                    array('type' => self::TYPE_BOOL, 'shop' => true, 'validate' => 'isBool'),
            'redirect_type' =>                array('type' => self::TYPE_STRING, 'shop' => true, 'validate' => 'isString'),
            'id_product_redirected' =>        array('type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isUnsignedId'),
            'available_for_order' =>        array('type' => self::TYPE_BOOL, 'shop' => true, 'validate' => 'isBool'),
            'available_date' =>            array('type' => self::TYPE_DATE, 'shop' => true, 'validate' => 'isDateFormat'),
            'condition' =>                    array('type' => self::TYPE_STRING, 'shop' => true, 'validate' => 'isGenericName', 'values' => array('new', 'used', 'refurbished'), 'default' => 'new'),
            'show_price' =>                array('type' => self::TYPE_BOOL, 'shop' => true, 'validate' => 'isBool'),
            'indexed' =>                    array('type' => self::TYPE_BOOL, 'shop' => true, 'validate' => 'isBool'),
            'visibility' =>                array('type' => self::TYPE_STRING, 'shop' => true, 'validate' => 'isProductVisibility', 'values' => array('both', 'catalog', 'search', 'none'), 'default' => 'both'),
            'cache_default_attribute' =>    array('type' => self::TYPE_INT, 'shop' => true),
            'advanced_stock_management' =>    array('type' => self::TYPE_BOOL, 'shop' => true, 'validate' => 'isBool'),
            'date_add' =>                    array('type' => self::TYPE_DATE, 'shop' => true, 'validate' => 'isDate'),
            'date_upd' =>                    array('type' => self::TYPE_DATE, 'shop' => true, 'validate' => 'isDate'),
            'pack_stock_type' =>            array('type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isUnsignedInt'),

            /* Lang fields */
            'meta_description' =>            array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255),
            'meta_keywords' =>                array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255),
            'meta_title' =>                array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 128),
            'link_rewrite' =>    array(
                'type' => self::TYPE_STRING,
                'lang' => true,
                'validate' => 'isLinkRewrite',
                'required' => true,
                'size' => 128,
                'ws_modifier' => array(
                    'http_method' => WebserviceRequest::HTTP_POST,
                    'modifier' => 'modifierWsLinkRewrite'
                )
            ),
            'name' =>                        array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isAnything', 'required' => true, 'size' => 128),
            'description' =>                array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml'),
            'description_short' =>            array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml'),
            'available_now' =>                array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255),
            'available_later' =>            array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'IsGenericName', 'size' => 255),
            'date_upd_divalto' =>           array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
        'associations' => array(
            'manufacturer' =>                array('type' => self::HAS_ONE),
            'supplier' =>                    array('type' => self::HAS_ONE),
            'default_category' =>            array('type' => self::HAS_ONE, 'field' => 'id_category_default', 'object' => 'Category'),
            'tax_rules_group' =>            array('type' => self::HAS_ONE),
            'categories' =>                    array('type' => self::HAS_MANY, 'field' => 'id_category', 'object' => 'Category', 'association' => 'category_product'),
            'stock_availables' =>            array('type' => self::HAS_MANY, 'field' => 'id_stock_available', 'object' => 'StockAvailable', 'association' => 'stock_availables'),
        ),
    );

    public function __construct($id_product = null, $full = false, $id_lang = null, $id_shop = null, Context $context = null)
    {
        parent::__construct($id_product, false, $id_lang, $id_shop);
    }

    public static function getProductByReference($reference)
    {
        $sql = sprintf(
            "SELECT id_product FROM %sproduct WHERE reference = '%s'",
            _DB_PREFIX_,
            addslashes($reference)
        );

        $product_id = Db::getInstance()->getValue($sql);

        return $product_id;
    }

    public static function getProduct($reference)
    {
        $sql = sprintf(
            'SELECT p.id_product FROM %sproduct p WHERE p.reference = "%s"',
            _DB_PREFIX_,
            addslashes($reference)
        );
        return Db::getInstance()->getRow($sql);
    }

    public static function productHasChanged($reference, $date)
    {
        $sql = sprintf(
            'SELECT p.date_upd_divalto FROM %sproduct p WHERE p.reference = "%s"',
            _DB_PREFIX_,
            addslashes($reference)
        );
        return Db::getInstance()->getValue($sql) < $date;
    }

    public static function getProductCategoriesFull($id_product = '', $id_lang = null)
    {
        if (!$id_lang) {
            $id_lang = Context::getContext()->language->id;
        }

        $ret = array();
        $row = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT cp.`id_category`, cl.`name`, cl.`link_rewrite`, c.`link` FROM `'._DB_PREFIX_.'category_product` cp
			LEFT JOIN `'._DB_PREFIX_.'category` c ON (c.id_category = cp.id_category)
			LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (cp.`id_category` = cl.`id_category`'.Shop::addSqlRestrictionOnLang('cl').')
			'.Shop::addSqlAssociation('category', 'c').'
			WHERE cp.`id_product` = '.(int)$id_product.'
				AND cl.`id_lang` = '.(int)$id_lang
        );

        foreach ($row as $val) {
            $ret[$val['id_category']] = $val;
        }

        return $ret;
    }

    public function getProductCorrespondences()
    {
        $row = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(sprintf(
                'SELECT pc.name, pc.value FROM %sproduct_correspondence pc WHERE pc.id_product = %d',
                _DB_PREFIX_,
                $this->id
            )
        );

        $correspondences = array();
        $context = Context::getContext();

        foreach ($row as $correspondence) {
            $product = self::getProduct($correspondence['value']);
            $correspondences[] = array(
                'name' => $correspondence['name'],
                'value' => $product ? "<a href=".$context->link->getProductLink($product)." target=\"_blank\">".$correspondence['value']."</a>" : $correspondence['value']
            );
        }

        return $correspondences;
    }

    public function getProductAccessories()
    {
        $accessories = $this->getAccessories(Context::getContext()->language->id);

        if (!$accessories) {
            return;
        }

        $productIds = array();

        foreach ($accessories as &$accessory) {
            $productIds[] = $accessory['reference'];
        }
        $productIds = implode(";", $productIds);

        $webServiceDiva = new WebServiceDiva('<ACTION>TARIF_ART', '<DOS>1<TIERS>'.Context::getContext()->cookie->tiers.'<REF>'.$productIds.'<FICHE>0');

        try {
            $datas = $webServiceDiva->call();

            if ($datas && $datas->references) {

                foreach ($datas->references as $reference) {

                    if ($reference->trouve == 1) {
                        $products[$reference->ref] = array(
                            'total_stock' => $reference->total_stock,
                            'total_dispo' => $reference->total_dispo,
                            'total_jauge' => $reference->total_jauge,
                            'tarif' => $reference->max_pun,
                            'nb_tarif' => $reference->nbTarifs
                        );
                    } else {
                        $products[$reference->ref] = array(
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

        return $products;
    }
}
