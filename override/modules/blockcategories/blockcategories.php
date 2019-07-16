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

class BlockCategoriesOverride extends BlockCategories
{
    public function hookLeftColumn($params)
    {
        $this->setLastVisitedCategory();
        $phpself = $this->context->controller->php_self;
        $current_allowed_controllers = array('category');

        if ($phpself != null && in_array($phpself, $current_allowed_controllers) && Configuration::get('BLOCK_CATEG_ROOT_CATEGORY') && isset($this->context->cookie->last_visited_category) && $this->context->cookie->last_visited_category)
        {
            $category = new Category($this->context->cookie->last_visited_category, $this->context->language->id);
            if (Configuration::get('BLOCK_CATEG_ROOT_CATEGORY') == 2 && !$category->is_root_category && $category->id_parent)
                $category = new Category($category->id_parent, $this->context->language->id);
            elseif (Configuration::get('BLOCK_CATEG_ROOT_CATEGORY') == 3 && !$category->is_root_category && !$category->getSubCategories($category->id, true))
                $category = new Category($category->id_parent, $this->context->language->id);
        }
        else
            $category = new Category((int)Configuration::get('PS_HOME_CATEGORY'), $this->context->language->id);

        $cacheId = $this->getCacheId($category ? $category->id : null);

        if (!$this->isCached('blockcategories.tpl', $cacheId))
        {
            $range = '';
            $maxdepth = Configuration::get('BLOCK_CATEG_MAX_DEPTH');
            if (Validate::isLoadedObject($category))
            {
                if ($maxdepth > 0)
                    $maxdepth += $category->level_depth;
                $range = 'AND nleft >= '.(int)$category->nleft.' AND nright <= '.(int)$category->nright;
            }

            $resultIds = array();
            $resultParents = array();
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT c.id_parent, c.id_category, cl.name, cl.description, cl.link_rewrite
			FROM `'._DB_PREFIX_.'category` c
			INNER JOIN `'._DB_PREFIX_.'category_lang` cl ON (c.`id_category` = cl.`id_category` AND cl.`id_lang` = '.(int)$this->context->language->id.Shop::addSqlRestrictionOnLang('cl').')
			INNER JOIN `'._DB_PREFIX_.'category_shop` cs ON (cs.`id_category` = c.`id_category` AND cs.`id_shop` = '.(int)$this->context->shop->id.')
			INNER JOIN `'._DB_PREFIX_.'category_product` cp ON (c.`id_category` = cp.`id_category`)
            LEFT JOIN `'._DB_PREFIX_.'product` p ON p.`id_product` = cp.`id_product`
			WHERE (c.`active` = 1 OR c.`id_category` = '.(int)Configuration::get('PS_HOME_CATEGORY').')
			AND c.`id_category` != '.(int)Configuration::get('PS_ROOT_CATEGORY').'
			'.((int)$maxdepth != 0 ? ' AND `level_depth` <= '.(int)$maxdepth : '').'
			'.$range.'
			AND c.id_category IN (
				SELECT id_category
				FROM `'._DB_PREFIX_.'category_group`
				WHERE `id_group` IN ('.pSQL(implode(', ', Customer::getGroupsStatic((int)$this->context->customer->id))).')
			)
			GROUP BY c.id_category
			ORDER BY cl.`description` ASC, `level_depth` ASC, '.(Configuration::get('BLOCK_CATEG_SORT') ? 'cl.`name`' : 'cs.`position`').' '.(Configuration::get('BLOCK_CATEG_SORT_WAY') ? 'DESC' : 'ASC'));

            foreach ($result as &$row)
            {
                $resultParents[$row['id_parent']][] = &$row;
                $resultIds[$row['id_category']] = &$row;
            }

            $blockCategTree = $this->getTree($resultParents, $resultIds, $maxdepth, ($category ? $category->id : null));
            $this->smarty->assign('blockCategTree', $blockCategTree);

            if ((Tools::getValue('id_product') || Tools::getValue('id_category')) && isset($this->context->cookie->last_visited_category) && $this->context->cookie->last_visited_category)
            {
                $category = new Category($this->context->cookie->last_visited_category, $this->context->language->id);
                if (Validate::isLoadedObject($category))
                    $this->smarty->assign(array('currentCategory' => $category, 'currentCategoryId' => $category->id));
            }

            $this->smarty->assign('isDhtml', Configuration::get('BLOCK_CATEG_DHTML'));
            if (file_exists(_PS_THEME_DIR_.'modules/blockcategories/blockcategories.tpl'))
                $this->smarty->assign('branche_tpl_path', _PS_THEME_DIR_.'modules/blockcategories/category-tree-branch.tpl');
            else
                $this->smarty->assign('branche_tpl_path', _PS_MODULE_DIR_.'blockcategories/category-tree-branch.tpl');
        }
        return $this->display(__FILE__, 'blockcategories.tpl', $cacheId);
    }
}
