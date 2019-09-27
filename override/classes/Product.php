<?php
class Product extends ProductCore
{
    /** @var string Name */
    public $name;

    /** @var string Object last modification date in divalto */
    public $date_upd_divalto;

    public function __construct($id_product = null, $full = false, $id_lang = null, $id_shop = null, Context $context = null)
    {
        Product::$definition['fields']['name'] = array('type' => self::TYPE_STRING, 'lang' => true, 'required' => true, 'size' => 128);
        Product::$definition['fields']['date_upd_divalto'] = array('type' => self::TYPE_DATE, 'validate' => 'isDate');
        parent::__construct($id_product, false, $id_lang, $id_shop);
    }

    public static function getProductByReference($reference)
    {
        $sql = sprintf(
            "SELECT id_product FROM %sproduct WHERE reference = '%s'",
            _DB_PREFIX_,
            $reference
        );

        $product_id = Db::getInstance()->getValue($sql);

        return $product_id;
    }

    public static function getProduct($reference)
    {
        $sql = sprintf(
            'SELECT p.id_product FROM %sproduct p WHERE p.reference = "%s"',
            _DB_PREFIX_,
            $reference
        );
        return Db::getInstance()->getRow($sql);
    }

    public static function productHasChanged($reference, $date)
    {
        $sql = sprintf(
            'SELECT p.date_upd_divalto FROM %sproduct p WHERE p.reference = "%s"',
            _DB_PREFIX_,
            $reference
        );
        return Db::getInstance()->getValue($sql) < $date;
    }
}
