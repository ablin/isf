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

class Category extends CategoryCore
{
    /** @var string Object last modification date in divalto */
    public $date_upd_divalto;

    public function __construct($id_category = null, $id_lang = null, $id_shop = null)
    {
        Category::$definition['fields']['date_upd_divalto'] = array('type' => self::TYPE_DATE, 'validate' => 'isDate');
        parent::__construct($id_category, $id_lang, $id_shop);
    }

    public static function getCategory($name)
    {
        $sql = sprintf(
            'SELECT c.id_category, c.level_depth, c.position, c.nleft, c.nright FROM %scategory_lang cl INNER JOIN %scategory c using (id_category) WHERE cl.id_lang = %s AND cl.name = "%s"',
            _DB_PREFIX_,
            _DB_PREFIX_,
            (int)Context::getContext()->language->id,
            $name
        );
        return Db::getInstance()->getRow($sql);
    }

    public static function categoryHasChanged($name, $date)
    {
        $sql = sprintf(
            'SELECT c.date_upd_divalto FROM %scategory_lang cl INNER JOIN %scategory c using (id_category) WHERE cl.id_lang = %s AND cl.name = "%s"',
            _DB_PREFIX_,
            _DB_PREFIX_,
            (int)Context::getContext()->language->id,
            $name
        );
        return Db::getInstance()->getValue($sql) < $date;
    }

    /**
     * Return current category childs
     *
     * @param int $id_lang Language ID
     * @param bool $active return only active categories
     * @return array Categories
     */
    public function getSubCategories($id_lang, $active = true)
    {
        $sql_groups_where = '';
        $sql_groups_join = '';
        if (Group::isFeatureActive()) {
            $sql_groups_join = 'LEFT JOIN `'._DB_PREFIX_.'category_group` cg ON (cg.`id_category` = c.`id_category`)';
            $groups = FrontController::getCurrentCustomerGroups();
            $sql_groups_where = 'AND cg.`id_group` '.(count($groups) ? 'IN ('.implode(',', $groups).')' : '='.(int)Group::getCurrent()->id);
        }

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT c.*, cl.id_lang, cl.name, cl.description, cl.link_rewrite, cl.meta_title, cl.meta_keywords, cl.meta_description
		FROM `'._DB_PREFIX_.'category` c
		'.Shop::addSqlAssociation('category', 'c').'
		LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (c.`id_category` = cl.`id_category` AND `id_lang` = '.(int)$id_lang.' '.Shop::addSqlRestrictionOnLang('cl').')
		'.$sql_groups_join.'
		INNER JOIN `'._DB_PREFIX_.'category_product` cp ON (c.`id_category` = cp.`id_category`)
        LEFT JOIN `'._DB_PREFIX_.'product` p ON p.`id_product` = cp.`id_product`
		WHERE `id_parent` = '.(int)$this->id.'
		'.($active ? 'AND c.`active` = 1' : '').'
		'.$sql_groups_where.'
		GROUP BY c.`id_category`
		ORDER BY cl.`description` ASC, category_shop.`position` ASC');

        foreach ($result as &$row) {
            $row['id_image'] = Tools::file_exists_cache(_PS_CAT_IMG_DIR_.$row['id_category'].'.jpg') ? (int)$row['id_category'] : Language::getIsoById($id_lang).'-default';
            $row['legend'] = 'no picture';
        }

        return $result;
    }

    public static function getNestedCategories($root_category = null, $id_lang = false, $active = true, $groups = null,
                                               $use_shop_restriction = true, $sql_filter = '', $sql_sort = '', $sql_limit = '')
    {
        if (isset($root_category) && !Validate::isInt($root_category)) {
            die(Tools::displayError());
        }

        if (!Validate::isBool($active)) {
            die(Tools::displayError());
        }

        if (isset($groups) && Group::isFeatureActive() && !is_array($groups)) {
            $groups = (array)$groups;
        }

        $cache_id = 'Category::getNestedCategories_'.md5((int)$root_category.(int)$id_lang.(int)$active.(int)$use_shop_restriction
                .(isset($groups) && Group::isFeatureActive() ? implode('', $groups) : ''));

        if (!Cache::isStored($cache_id)) {
            $result = Db::getInstance()->executeS('
				SELECT c.*, cl.*
				FROM `'._DB_PREFIX_.'category` c
				'.($use_shop_restriction ? Shop::addSqlAssociation('category', 'c') : '').'
				LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON c.`id_category` = cl.`id_category`'.Shop::addSqlRestrictionOnLang('cl').'
				'.(isset($groups) && Group::isFeatureActive() ? 'LEFT JOIN `'._DB_PREFIX_.'category_group` cg ON c.`id_category` = cg.`id_category`' : '').'
				'.(isset($root_category) ? 'RIGHT JOIN `'._DB_PREFIX_.'category` c2 ON c2.`id_category` = '.(int)$root_category.' AND c.`nleft` >= c2.`nleft` AND c.`nright` <= c2.`nright`' : '').'
				WHERE 1 '.$sql_filter.' '.($id_lang ? 'AND `id_lang` = '.(int)$id_lang : '').'
				'.($active ? ' AND c.`active` = 1' : '').'
				'.(isset($groups) && Group::isFeatureActive() ? ' AND cg.`id_group` IN ('.implode(',', $groups).')' : '').'
				'.(!$id_lang || (isset($groups) && Group::isFeatureActive()) ? ' GROUP BY c.`id_category`' : '').'
				'.($sql_sort != '' ? $sql_sort : ' ORDER BY c.`level_depth` ASC').'
				'.($sql_sort == '' && $use_shop_restriction ? ', cl.`description` ASC' : '').'
				'.($sql_limit != '' ? $sql_limit : '')
            );

            $categories = array();
            $buff = array();

            if (!isset($root_category)) {
                $root_category = Category::getRootCategory()->id;
            }

            foreach ($result as $row) {
                $current = &$buff[$row['id_category']];
                $current = $row;

                if ($row['id_category'] == $root_category) {
                    $categories[$row['id_category']] = &$current;
                } else {
                    $buff[$row['id_parent']]['children'][$row['id_category']] = &$current;
                }
            }

            Cache::store($cache_id, $categories);
        } else {
            $categories = Cache::retrieve($cache_id);
        }

        return $categories;
    }
}
