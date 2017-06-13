<?php
class Product extends ProductCore
{
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
}
