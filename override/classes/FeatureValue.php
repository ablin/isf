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

class FeatureValue extends FeatureValueCore
{
    /** @var int id category */
    public $id_category;

    /** @var int level */
    public $level;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'feature_value',
        'primary' => 'id_feature_value',
        'multilang' => true,
        'fields' => array(
            'id_feature' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'custom' =>    array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'id_category' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'level' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),

            /* Lang fields */
            'value' =>        array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isAnything', 'required' => true, 'size' => 255),
        ),
    );

    public static function getFeatureValueByName($name, $id_feature)
    {
        $sql = sprintf(
            'SELECT fvl.id_feature_value FROM %sfeature_value_lang fvl inner join ps_feature_value fv using(id_feature_value) WHERE fvl.id_lang = %s AND fvl.value = "%s" AND fv.id_feature = %d',
            _DB_PREFIX_,
            (int)Context::getContext()->language->id,
            addslashes($name),
            $id_feature
        );
        return Db::getInstance()->getRow($sql);
    }
}
