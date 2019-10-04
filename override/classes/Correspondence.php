<?php
class Correspondence extends ObjectModel
{
    /** @var string Name */
    public $name;

    /** @var string Value */
    public $value;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'product_correspondence',
        'primary' => 'id_product_correspondence',
        'fields' => array(
            'id_product' =>  array('type' => self::TYPE_INT, 'shop' => 'both', 'validate' => 'isUnsignedId', 'required' => true),
            'name' => array('type' => self::TYPE_STRING, 'validate' => 'isAnything', 'required' => true, 'size' => 128),
            'value' => array('type' => self::TYPE_STRING, 'validate' => 'isAnything', 'required' => true, 'size' => 255),
        ),
    );
}
