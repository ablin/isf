<?php
class Address extends AddressCore
{
    /** @var string Address third line (optional) */
    public $address3;

    /** @var string locality (optional) */
    public $locality;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'address',
        'primary' => 'id_address',
        'fields' => array(
            'id_customer' =>        array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'copy_post' => false),
            'id_manufacturer' =>    array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'copy_post' => false),
            'id_supplier' =>        array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'copy_post' => false),
            'id_warehouse' =>        array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'copy_post' => false),
            'id_country' =>        array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_state' =>            array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId'),
            'alias' =>                array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => false, 'size' => 32),
            'company' =>            array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 64),
            'lastname' =>            array('type' => self::TYPE_STRING, 'validate' => 'isName', 'required' => true, 'size' => 32),
            'firstname' =>            array('type' => self::TYPE_STRING, 'validate' => 'isName', 'required' => true, 'size' => 32),
            'vat_number' =>            array('type' => self::TYPE_STRING, 'validate' => 'isGenericName'),
            'address1' =>            array('type' => self::TYPE_STRING, 'validate' => 'isAddress', 'required' => true, 'size' => 128),
            'address2' =>            array('type' => self::TYPE_STRING, 'validate' => 'isAddress', 'size' => 128),
            'postcode' =>            array('type' => self::TYPE_STRING, 'validate' => 'isPostCode', 'size' => 12),
            'city' =>                array('type' => self::TYPE_STRING, 'validate' => 'isCityName', 'required' => true, 'size' => 64),
            'other' =>                array('type' => self::TYPE_STRING, 'validate' => 'isMessage', 'size' => 300),
            'phone' =>                array('type' => self::TYPE_STRING, 'validate' => 'isPhoneNumber', 'size' => 32),
            'phone_mobile' =>        array('type' => self::TYPE_STRING, 'validate' => 'isPhoneNumber', 'size' => 32),
            'dni' =>                array('type' => self::TYPE_STRING, 'validate' => 'isDniLite', 'size' => 16),
            'deleted' =>            array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false),
            'date_add' =>            array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
            'date_upd' =>            array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
        ),
    );

    /**
     * {@inheritdoc}
     */
    public  function __construct($id_address = NULL, $id_lang = NULL)
    {
        Address::$definition['fields']['address3'] = array('type' => self::TYPE_STRING, 'validate' => 'isAddress', 'size' => 128);
        Address::$definition['fields']['locality'] = array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 128);

        parent::__construct($id_address);
    }

    /**
     * {@inheritdoc}
     */
    public function getFields()
    {
        $fields = parent::getFields();

        $fields['address3'] = pSQL($this->address3);
        $fields['locality'] = pSQL($this->locality);

        return $fields;
    }
}
