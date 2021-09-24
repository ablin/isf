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
*  International Registred Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
    exit;

class BlockLayeredOverride extends BlockLayered
{
    private $page;

    public function ajaxCall()
    {
        global $smarty, $cookie;

        $selected_filters = $this->getSelectedFilters();
        $filter_block = $this->getFilterBlock($selected_filters);
        $this->getProducts($selected_filters, $products, $nb_products, $p, $n, $pages_nb, $start, $stop, $range);

        // Add pagination variable
        $nArray = (int)Configuration::get('PS_PRODUCTS_PER_PAGE') != 10 ? array((int)Configuration::get('PS_PRODUCTS_PER_PAGE'), 10, 20, 50) : array(10, 20, 50);
        // Clean duplicate values
        $nArray = array_unique($nArray);
        asort($nArray);

        Hook::exec(
            'actionProductListModifier',
            array(
                'nb_products' => &$nb_products,
                'cat_products' => &$products,
            )
        );

        if (version_compare(_PS_VERSION_, '1.6.0', '>=') === true)
            $this->context->controller->addColorsToProductList($products);

        $category = new Category(Tools::getValue('id_category_layered', Configuration::get('PS_HOME_CATEGORY')), (int)$cookie->id_lang);

        // Generate meta title and meta description
        $category_title = (empty($category->meta_description) ? $category->description : $category->meta_description);
        $category_metas = Meta::getMetaTags((int)$cookie->id_lang, 'category');
        $title = '';
        $keywords = '';

        if (is_array($filter_block['title_values']))
            foreach ($filter_block['title_values'] as $key => $val)
            {
                $title .= ' > '.$key.' '.implode('/', $val);
                $keywords .= $key.' '.implode('/', $val).', ';
            }

        $title = $category_title.$title;

        if (!empty($title))
            $meta_title = $title;
        else
            $meta_title = $category_metas['meta_title'];

        $meta_description = $category_metas['meta_description'];

        $keywords = Tools::substr(Tools::strtolower($keywords), 0, 1000);
        if (!empty($keywords))
            $meta_keywords = rtrim($category_title.', '.$keywords.', '.$category_metas['meta_keywords'], ', ');

        $smarty->assign(
            array(
                'homeSize' => Image::getSize(ImageType::getFormatedName('home')),
                'nb_products' => $nb_products,
                'category' => $category,
                'pages_nb' => (int)$pages_nb,
                'p' => (int)$p,
                'n' => (int)$n,
                'range' => (int)$range,
                'start' => (int)$start,
                'stop' => (int)$stop,
                'n_array' => ((int)Configuration::get('PS_PRODUCTS_PER_PAGE') != 10) ? array((int)Configuration::get('PS_PRODUCTS_PER_PAGE'), 10, 20, 50) : array(10, 20, 50),
                'comparator_max_item' => (int)(Configuration::get('PS_COMPARATOR_MAX_ITEM')),
                'products' => $products,
                'products_per_page' => (int)Configuration::get('PS_PRODUCTS_PER_PAGE'),
                'static_token' => Tools::getToken(false),
                'page_name' => 'category',
                'nArray' => $nArray,
                'compareProducts' => CompareProduct::getCompareProducts((int)$this->context->cookie->id_compare)
            )
        );

        $productIds = array();
        $productsLayered = array();

        foreach ($products as &$product) {
            $productIds[] = $product['reference'];
        }
        $productIds = implode(";", $productIds);

        $webServiceDiva = new WebServiceDiva('<ACTION>TARIF_ART', '<DOS>1<TIERS>'.$this->context->cookie->tiers.'<REF>'.$productIds.'<FICHE>0');

        try {
            $datas = $webServiceDiva->call();

            if ($datas && $datas->references) {

                foreach ($datas->references as $reference) {

                    if ($reference->trouve == 1) {
                        $productsLayered[$reference->ref] = array(
                            'total_stock' => $reference->total_stock,
                            'total_dispo' => $reference->total_dispo,
                            'total_jauge' => $reference->total_jauge,
                            'tarif' => $reference->max_pun,
                            'nb_tarif' => $reference->nbTarifs
                        );
                    } else {
                        $productsLayered[$reference->ref] = array(
                            'total_stock' => -1,
                            'total_dispo' => -1,
                            'total_jauge' => -1,
                            'tarif' => 0,
                            'nb_tarif' => 0,
                            'alerte' => ""
                        );
                    }
                }
            }

        } catch (SoapFault $fault) {
            throw new Exception('Error: SOAP Fault: (faultcode: {'.$fault->faultcode.'}, faultstring: {'.$fault->faultstring.'})');
        }

        $smarty->assign('references', $productsLayered);

        // Prevent bug with old template where category.tpl contain the title of the category and category-count.tpl do not exists
        if (file_exists(_PS_THEME_DIR_.'category-count.tpl'))
            $category_count = $smarty->fetch(_PS_THEME_DIR_.'category-count.tpl');
        else
            $category_count = '';

        if ($nb_products == 0)
            $product_list = $this->display(__FILE__, 'blocklayered-no-products.tpl');
        else
            $product_list = $smarty->fetch(_PS_THEME_DIR_.'product-list.tpl');

        $vars = array(
            'filtersBlock' => utf8_encode($this->generateFiltersBlock($selected_filters)),
            'productList' => utf8_encode($product_list),
            'pagination' => $smarty->fetch(_PS_THEME_DIR_.'pagination.tpl'),
            'categoryCount' => $category_count,
            'meta_title' => $meta_title.' - '.Configuration::get('PS_SHOP_NAME'),
            'heading' => $meta_title,
            'meta_keywords' => isset($meta_keywords) ? $meta_keywords : null,
            'meta_description' => $meta_description,
            'current_friendly_url' => ((int)$n == (int)$nb_products) ? '#/show-all': '#'.$filter_block['current_friendly_url'],
            'filters' => $filter_block['filters'],
            'nbRenderedProducts' => (int)$nb_products,
            'nbAskedProducts' => (int)$n
        );

        if (version_compare(_PS_VERSION_, '1.6.0', '>=') === true)
            $vars = array_merge($vars, array('pagination_bottom' => $smarty->assign('paginationId', 'bottom')
                ->fetch(_PS_THEME_DIR_.'pagination.tpl')));
        /* We are sending an array in jSon to the .js controller, it will update both the filters and the products zones */
        return Tools::jsonEncode($vars);
    }

    private function getSelectedFilters()
    {
        $home_category = Configuration::get('PS_HOME_CATEGORY');
        $id_parent = (int)Tools::getValue('id_category', Tools::getValue('id_category_layered', $home_category));
        if ($id_parent == $home_category)
            return;

        // Force attributes selection (by url '.../2-mycategory/color-blue' or by get parameter 'selected_filters')
        if (strpos($_SERVER['SCRIPT_FILENAME'], 'blocklayered-ajax.php') === false || Tools::getValue('selected_filters') !== false)
        {
            if (Tools::getValue('selected_filters'))
                $url = Tools::getValue('selected_filters');
            else
                $url = preg_replace('/\/(?:\w*)\/(?:[0-9]+[-\w]*)([^\?]*)\??.*/', '$1', Tools::safeOutput($_SERVER['REQUEST_URI'], true));

            $url_attributes = explode('/', ltrim($url, '/'));
            $selected_filters = array('category' => array($id_parent));
            if (!empty($url_attributes))
            {
                foreach ($url_attributes as $url_attribute)
                {
                    /* Pagination uses - as separator, can be different from $this->getAnchor()*/
                    if (strpos($url_attribute, 'page-') === 0)
                        $url_attribute = str_replace('-', $this->getAnchor(), $url_attribute);
                    $url_parameters = explode($this->getAnchor(), $url_attribute);
                    $attribute_name  = array_shift($url_parameters);
                    if ($attribute_name == 'page')
                        $this->page = (int)$url_parameters[0];
                    else if (in_array($attribute_name, array('price', 'weight')))
                        $selected_filters[$attribute_name] = array($this->filterVar($url_parameters[0]), $this->filterVar($url_parameters[1]));
                    else
                    {
                        foreach ($url_parameters as $url_parameter)
                        {
                            $data = Db::getInstance()->getValue('SELECT data FROM `'._DB_PREFIX_.'layered_friendly_url` WHERE `url_key` = \''.md5('/'.$attribute_name.$this->getAnchor().$url_parameter).'\'');
                            if ($data)
                                foreach (Tools::unSerialize($data) as $key_params => $params)
                                {
                                    if (!isset($selected_filters[$key_params]))
                                        $selected_filters[$key_params] = array();
                                    foreach ($params as $key_param => $param)
                                    {
                                        if (!isset($selected_filters[$key_params][$key_param]))
                                            $selected_filters[$key_params][$key_param] = array();
                                        $selected_filters[$key_params][$key_param] = $this->filterVar($param);
                                    }
                                }
                        }
                    }
                }
                return $selected_filters;
            }
        }

        /* Analyze all the filters selected by the user and store them into a tab */
        $selected_filters = array('category' => array(), 'manufacturer' => array(), 'quantity' => array(), 'condition' => array());
        foreach ($_GET as $key => $value) {
            if (substr($key, 0, 8) == 'layered_')
            {
                preg_match('/^(.*)_([0-9]+|new|used|refurbished|slider)$/', substr($key, 8, strlen($key) - 8), $res);
                if (isset($res[1]))
                {
                    $tmp_tab = explode('_', $this->filterVar($value));
                    $value = $this->filterVar($tmp_tab[0]);
                    $id_key = false;
                    if (isset($tmp_tab[1]))
                        $id_key = $tmp_tab[1];
                    if ($res[1] == 'condition' && in_array($value, array('new', 'used', 'refurbished')))
                        $selected_filters['condition'][] = $value;
                    else if ($res[1] == 'quantity' && (!$value || $value == 1))
                        $selected_filters['quantity'][] = $value;
                    else if (in_array($res[1], array('category', 'manufacturer')))
                    {
                        if (!isset($selected_filters[$res[1].($id_key ? '_'.$id_key : '')]))
                            $selected_filters[$res[1].($id_key ? '_'.$id_key : '')] = array();
                        $selected_filters[$res[1].($id_key ? '_'.$id_key : '')][] = (int)$value;
                    }
                    else if (in_array($res[1], array('id_attribute_group', 'id_feature')))
                    {
                        if (!isset($selected_filters[$res[1]]))
                            $selected_filters[$res[1]] = array();
                        $selected_filters[$res[1]][(int)$value] = $id_key.'_'.(int)$value;
                    }
                    else if ($res[1] == 'weight')
                        $selected_filters[$res[1]] = $tmp_tab;
                    else if ($res[1] == 'price')
                        $selected_filters[$res[1]] = $tmp_tab;
                }
            }
        }

        if (empty($selected_filters['category'])) {
            $selected_filters['category'][] = $id_parent;
        }

        return $selected_filters;
    }

    public function getFilterBlock($selected_filters = array())
    {
        global $cookie;
        static $cache = null;

        $context = Context::getContext();

        $id_lang = $context->language->id;
        $currency = $context->currency;
        $id_shop = (int) $context->shop->id;
        $alias = 'product_shop';

        if (is_array($cache))
            return $cache;

        $home_category = Configuration::get('PS_HOME_CATEGORY');
        $id_parent = (int)Tools::getValue('id_category', Tools::getValue('id_category_layered', $home_category));
        if ($id_parent == $home_category)
            return;

        $parent = new Category((int)$id_parent, $id_lang);

        /* Get the filters for the current category */
        $filterProductList = array();
        foreach (_DV_LAYERED_FEATURE_ALWAYS_DISPLAYED_ as $featureDisplayed) {
            $filterProductList[] = $featureDisplayed;
        }

        $filterProducts =  sprintf("
                OR id_value IN (%s)",
            implode(",", $filterProductList)
        );

        if (isset($selected_filters['id_feature'])) {
            $filterProductList = array();
            $restriction = true;
            foreach ($selected_filters['id_feature'] as $feature) {
                $filterProductList[] = substr(stristr($feature, "_"), 1);
                if (in_array(stristr($feature, "_", true), _DV_LAYERED_FEATURE_ACTIVE_SUB_FEATURES_)) {
                    $restriction = false;
                }
            }
            if (!$restriction) {
                $filterProducts =  sprintf("
                        OR id_value IN (
                            SELECT distinct(id_feature) FROM %sfeature_product where id_product IN (SELECT distinct(id_product) FROM %sfeature_product WHERE id_feature_value IN (%s))
                        )",
                    _DB_PREFIX_,
                    _DB_PREFIX_,
                    implode(",", $filterProductList)
                );
            }
        }

        $features = "";
        $nb = 1;

        foreach (_DV_FEATURE_NOT_DISPLAYED_PRODUCT as $feature) {
            $features .= 'WHEN id_feature = '.$feature.' THEN "3'.$nb.'" ';
            $nb++;
        }

        $filters = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
        SELECT type, id_value, filter_show_limit, filter_type FROM ' . _DB_PREFIX_ . 'layered_category
            LEFT JOIN ' . _DB_PREFIX_ . 'feature_lang ON type = "id_feature" AND ' . _DB_PREFIX_ . 'feature_lang.id_feature = ' . _DB_PREFIX_ . 'layered_category.id_value
            WHERE id_category = ' . (int)$id_parent . '
                AND id_shop = ' . $id_shop . ' AND (type IN ("id_attribute_group", "category", "price")' . $filterProducts .')
            GROUP BY `type`, id_value 
            ORDER BY CASE WHEN type = "category" THEN "1"
                WHEN type = "id_attribute_group" THEN "2"
                WHEN type = "id_feature" THEN CASE '.$features.' ELSE "4" END END, ' . _DB_PREFIX_ . 'feature_lang.name ASC'
        );

        /* Create the table which contains all the id_product in a cat or a tree */

        Db::getInstance()->execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'cat_restriction', false);
        Db::getInstance()->execute('CREATE TABLE '._DB_PREFIX_.'cat_restriction ENGINE=MEMORY
													SELECT DISTINCT cp.id_product, p.id_manufacturer, product_shop.condition, p.weight FROM '._DB_PREFIX_.'category c
													STRAIGHT_JOIN '._DB_PREFIX_.'category_product cp ON (c.id_category = cp.id_category AND
													'.(Configuration::get('PS_LAYERED_FULL_TREE') ? 'c.nleft >= '.(int)$parent->nleft.'
													AND c.nright <= '.(int)$parent->nright : 'c.id_category = '.(int)$id_parent).'
													AND c.active = 1)
													STRAIGHT_JOIN '._DB_PREFIX_.'product_shop product_shop ON (product_shop.id_product = cp.id_product
													AND product_shop.id_shop = '.(int)$context->shop->id.')
													STRAIGHT_JOIN '._DB_PREFIX_.'product p ON (p.id_product=cp.id_product)
													WHERE product_shop.`active` = 1 AND product_shop.`visibility` IN ("both", "catalog")', false);


        Db::getInstance()->execute('ALTER TABLE '._DB_PREFIX_.'cat_restriction ADD PRIMARY KEY (id_product),
													ADD KEY `id_manufacturer` (`id_manufacturer`,`id_product`) USING BTREE,
													ADD KEY `condition` (`condition`,`id_product`) USING BTREE,
													ADD KEY `weight` (`weight`,`id_product`) USING BTREE', false);

        // Remove all empty selected filters
        foreach ($selected_filters as $key => $value)
            switch ($key)
            {
                case 'price':
                case 'weight':
                    if ($value[0] === '' && $value[1] === '')
                        unset($selected_filters[$key]);
                    break;
                default:
                    if ($value == '')
                        unset($selected_filters[$key]);
                    break;
            }

        $filter_blocks = array();
        foreach ($filters as $filter)
        {
            $sql_query = array('select' => '', 'from' => '', 'join' => '', 'where' => '', 'group' => '', 'second_query' => '');
            switch ($filter['type'])
            {
                case 'price':
                    $sql_query['select'] = 'SELECT p.`id_product`, psi.price_min, psi.price_max ';
                    // price slider is not filter dependent
                    $sql_query['from'] = '
					FROM '._DB_PREFIX_.'cat_restriction p';
                    $sql_query['join'] = 'INNER JOIN `'._DB_PREFIX_.'layered_price_index` psi
								ON (psi.id_product = p.id_product AND psi.id_currency = '.(int)$context->currency->id.' AND psi.id_shop='.(int)$context->shop->id.')';
                    $sql_query['where'] = 'WHERE 1';
                    break;
                case 'weight':
                    $sql_query['select'] = 'SELECT p.`id_product`, p.`weight` ';
                    // price slider is not filter dependent
                    $sql_query['from'] = '
					FROM '._DB_PREFIX_.'cat_restriction p';
                    $sql_query['where'] = 'WHERE 1';
                    break;
                case 'condition':
                    $sql_query['select'] = 'SELECT p.`id_product`, product_shop.`condition` ';
                    $sql_query['from'] = '
					FROM '._DB_PREFIX_.'cat_restriction p';
                    $sql_query['where'] = 'WHERE 1';
                    $sql_query['from'] .= Shop::addSqlAssociation('product', 'p');
                    break;
                case 'quantity':
                    $sql_query['select'] = 'SELECT p.`id_product`, sa.`quantity`, sa.`out_of_stock` ';

                    $sql_query['from'] = '
					FROM '._DB_PREFIX_.'cat_restriction p';

                    $sql_query['join'] .= 'LEFT JOIN `'._DB_PREFIX_.'stock_available` sa
						ON (sa.id_product = p.id_product AND sa.id_product_attribute=0 '.StockAvailable::addSqlShopRestriction(null, null,  'sa').') ';
                    $sql_query['where'] = 'WHERE 1';
                    break;

                case 'manufacturer':
                    $sql_query['select'] = 'SELECT COUNT(DISTINCT p.id_product) nbr, m.id_manufacturer, m.name ';
                    $sql_query['from'] = '
					FROM '._DB_PREFIX_.'cat_restriction p
					INNER JOIN '._DB_PREFIX_.'manufacturer m ON (m.id_manufacturer = p.id_manufacturer) ';
                    $sql_query['where'] = 'WHERE 1';
                    $sql_query['group'] = ' GROUP BY p.id_manufacturer ORDER BY m.name';

                    if (!Configuration::get('PS_LAYERED_HIDE_0_VALUES'))
                    {
                        $sql_query['second_query'] = '
							SELECT m.name, 0 nbr, m.id_manufacturer

							FROM '._DB_PREFIX_.'cat_restriction p
							INNER JOIN '._DB_PREFIX_.'manufacturer m ON (m.id_manufacturer = p.id_manufacturer)
							WHERE 1
							GROUP BY p.id_manufacturer ORDER BY m.name';
                    }

                    break;
                case 'id_attribute_group':// attribute group
                    $sql_query['select'] = '
					SELECT COUNT(DISTINCT lpa.id_product) nbr, lpa.id_attribute_group,
					a.color, al.name attribute_name, agl.public_name attribute_group_name , lpa.id_attribute, ag.is_color_group,
					liagl.url_name name_url_name, liagl.meta_title name_meta_title, lial.url_name value_url_name, lial.meta_title value_meta_title';
                    $sql_query['from'] = '
					FROM '._DB_PREFIX_.'layered_product_attribute lpa
					INNER JOIN '._DB_PREFIX_.'attribute a
					ON a.id_attribute = lpa.id_attribute
					INNER JOIN '._DB_PREFIX_.'attribute_lang al
					ON al.id_attribute = a.id_attribute
					AND al.id_lang = '.(int)$id_lang.'
					INNER JOIN '._DB_PREFIX_.'cat_restriction p
					ON p.id_product = lpa.id_product
					INNER JOIN '._DB_PREFIX_.'attribute_group ag
					ON ag.id_attribute_group = lpa.id_attribute_group
					INNER JOIN '._DB_PREFIX_.'attribute_group_lang agl
					ON agl.id_attribute_group = lpa.id_attribute_group
					AND agl.id_lang = '.(int)$id_lang.'
					LEFT JOIN '._DB_PREFIX_.'layered_indexable_attribute_group_lang_value liagl
					ON (liagl.id_attribute_group = lpa.id_attribute_group AND liagl.id_lang = '.(int)$id_lang.')
					LEFT JOIN '._DB_PREFIX_.'layered_indexable_attribute_lang_value lial
					ON (lial.id_attribute = lpa.id_attribute AND lial.id_lang = '.(int)$id_lang.') ';

                    $sql_query['where'] = 'WHERE lpa.id_attribute_group = '.(int)$filter['id_value'];
                    $sql_query['where'] .= ' AND lpa.`id_shop` = '.(int)$context->shop->id;
                    $sql_query['group'] = '
					GROUP BY lpa.id_attribute
					ORDER BY ag.`position` ASC, a.`position` ASC';

                    if (!Configuration::get('PS_LAYERED_HIDE_0_VALUES'))
                    {
                        $sql_query['second_query'] = '
							SELECT 0 nbr, lpa.id_attribute_group,
								a.color, al.name attribute_name, agl.public_name attribute_group_name , lpa.id_attribute, ag.is_color_group,
								liagl.url_name name_url_name, liagl.meta_title name_meta_title, lial.url_name value_url_name, lial.meta_title value_meta_title
							FROM '._DB_PREFIX_.'layered_product_attribute lpa'.
                            Shop::addSqlAssociation('product', 'lpa').'
							INNER JOIN '._DB_PREFIX_.'attribute a
								ON a.id_attribute = lpa.id_attribute
							INNER JOIN '._DB_PREFIX_.'attribute_lang al
								ON al.id_attribute = a.id_attribute AND al.id_lang = '.(int)$id_lang.'
							INNER JOIN '._DB_PREFIX_.'product as p
								ON p.id_product = lpa.id_product
							INNER JOIN '._DB_PREFIX_.'attribute_group ag
								ON ag.id_attribute_group = lpa.id_attribute_group
							INNER JOIN '._DB_PREFIX_.'attribute_group_lang agl
								ON agl.id_attribute_group = lpa.id_attribute_group
							AND agl.id_lang = '.(int)$id_lang.'
							LEFT JOIN '._DB_PREFIX_.'layered_indexable_attribute_group_lang_value liagl
								ON (liagl.id_attribute_group = lpa.id_attribute_group AND liagl.id_lang = '.(int)$id_lang.')
							LEFT JOIN '._DB_PREFIX_.'layered_indexable_attribute_lang_value lial
								ON (lial.id_attribute = lpa.id_attribute AND lial.id_lang = '.(int)$id_lang.')
							WHERE lpa.id_attribute_group = '.(int)$filter['id_value'].'
							AND lpa.`id_shop` = '.(int)$context->shop->id.'
							GROUP BY lpa.id_attribute
							ORDER BY id_attribute_group, id_attribute';
                    }
                    break;

                case 'id_feature':
                    $sql_query['select'] = 'SELECT fl.name feature_name, fp.id_feature, fv.id_feature_value, fvl.value,
					COUNT(DISTINCT p.id_product) nbr,
					lifl.url_name name_url_name, lifl.meta_title name_meta_title, lifvl.url_name value_url_name, lifvl.meta_title value_meta_title ';
                    $sql_query['from'] = '
					FROM '._DB_PREFIX_.'feature_product fp
					INNER JOIN '._DB_PREFIX_.'cat_restriction p
					ON p.id_product = fp.id_product
					LEFT JOIN '._DB_PREFIX_.'feature_lang fl ON (fl.id_feature = fp.id_feature AND fl.id_lang = '.$id_lang.')
					INNER JOIN '._DB_PREFIX_.'feature_value fv ON (fv.id_feature_value = fp.id_feature_value AND (fv.custom IS NULL OR fv.custom = 0))
					LEFT JOIN '._DB_PREFIX_.'feature_value_lang fvl ON (fvl.id_feature_value = fp.id_feature_value AND fvl.id_lang = '.$id_lang.')
					LEFT JOIN '._DB_PREFIX_.'layered_indexable_feature_lang_value lifl
					ON (lifl.id_feature = fp.id_feature AND lifl.id_lang = '.$id_lang.')
					LEFT JOIN '._DB_PREFIX_.'layered_indexable_feature_value_lang_value lifvl
					ON (lifvl.id_feature_value = fp.id_feature_value AND lifvl.id_lang = '.$id_lang.') ';
                    $sql_query['where'] = 'WHERE fp.id_feature = '.(int)$filter['id_value'];
                    $sql_query['group'] = 'GROUP BY fv.id_feature_value ';

                    if (!Configuration::get('PS_LAYERED_HIDE_0_VALUES'))
                    {
                        $sql_query['second_query'] = '
							SELECT fl.name feature_name, fp.id_feature, fv.id_feature_value, fvl.value,
							0 nbr,
							lifl.url_name name_url_name, lifl.meta_title name_meta_title, lifvl.url_name value_url_name, lifvl.meta_title value_meta_title

							FROM '._DB_PREFIX_.'feature_product fp'.
                            Shop::addSqlAssociation('product', 'fp').'
							INNER JOIN '._DB_PREFIX_.'product p ON (p.id_product = fp.id_product)
							LEFT JOIN '._DB_PREFIX_.'feature_lang fl ON (fl.id_feature = fp.id_feature AND fl.id_lang = '.(int)$id_lang.')
							INNER JOIN '._DB_PREFIX_.'feature_value fv ON (fv.id_feature_value = fp.id_feature_value AND (fv.custom IS NULL OR fv.custom = 0))
							LEFT JOIN '._DB_PREFIX_.'feature_value_lang fvl ON (fvl.id_feature_value = fp.id_feature_value AND fvl.id_lang = '.(int)$id_lang.')
							LEFT JOIN '._DB_PREFIX_.'layered_indexable_feature_lang_value lifl
								ON (lifl.id_feature = fp.id_feature AND lifl.id_lang = '.(int)$id_lang.')
							LEFT JOIN '._DB_PREFIX_.'layered_indexable_feature_value_lang_value lifvl
								ON (lifvl.id_feature_value = fp.id_feature_value AND lifvl.id_lang = '.(int)$id_lang.')
							WHERE fp.id_feature = '.(int)$filter['id_value'].'
							GROUP BY fv.id_feature_value';
                    }

                    break;

                case 'category':
                    if (Group::isFeatureActive())
                        $this->user_groups =  ($this->context->customer->isLogged() ? $this->context->customer->getGroups() : array(Configuration::get('PS_UNIDENTIFIED_GROUP')));

                    $depth = Configuration::get('PS_LAYERED_FILTER_CATEGORY_DEPTH');
                    if ($depth === false)
                        $depth = 1;

                    $sql_query['select'] = '
					SELECT c.id_category, c.id_parent, cl.name, (SELECT count(DISTINCT p.id_product) # ';
                    $sql_query['from'] = '
					FROM '._DB_PREFIX_.'category_product cp
					LEFT JOIN '._DB_PREFIX_.'product p ON (p.id_product = cp.id_product) ';
                    $sql_query['where'] = '
					WHERE cp.id_category = c.id_category
					AND '.$alias.'.active = 1 AND '.$alias.'.`visibility` IN ("both", "catalog")';
                    $sql_query['group'] = ') count_products
					FROM '._DB_PREFIX_.'category c
					LEFT JOIN '._DB_PREFIX_.'category_lang cl ON (cl.id_category = c.id_category AND cl.`id_shop` = '.(int)Context::getContext()->shop->id.' and cl.id_lang = '.(int)$id_lang.') ';

                    if (Group::isFeatureActive())
                        $sql_query['group'] .= 'RIGHT JOIN '._DB_PREFIX_.'category_group cg ON (cg.id_category = c.id_category AND cg.`id_group` IN ('.implode(', ', $this->user_groups).')) ';

                    $sql_query['group'] .= 'WHERE c.nleft > '.(int)$parent->nleft.'
					AND c.nright < '.(int)$parent->nright.'
					'.($depth ? 'AND c.level_depth <= '.($parent->level_depth+(int)$depth) : '').'
					AND c.active = 1
					GROUP BY c.id_category ORDER BY c.nleft, c.position';

                    $sql_query['from'] .= Shop::addSqlAssociation('product', 'p');
            }

            foreach ($filters as $filter_tmp)
            {
                $method_name = 'get'.ucfirst($filter_tmp['type']).'FilterSubQuery';
                if (method_exists('BlockLayered', $method_name) &&
                    ($filter['type'] != 'price' && $filter['type'] != 'weight' && $filter['type'] != $filter_tmp['type'] || $filter['type'] == $filter_tmp['type']))
                {
                    if ($filter['type'] == $filter_tmp['type'] && $filter['id_value'] == $filter_tmp['id_value'])
                        $sub_query_filter = self::$method_name(array(), true);
                    else
                    {
                        if (!is_null($filter_tmp['id_value']))
                            $selected_filters_cleaned = $this->cleanFilterByIdValue(@$selected_filters[$filter_tmp['type']], $filter_tmp['id_value']);
                        else
                            $selected_filters_cleaned = @$selected_filters[$filter_tmp['type']];
                        $sub_query_filter = self::$method_name($selected_filters_cleaned, $filter['type'] == $filter_tmp['type']);
                    }
                    foreach ($sub_query_filter as $key => $value)
                        $sql_query[$key] .= $value;
                }
            }

            $products = false;
            if (!empty($sql_query['from']))
            {
                $products = Db::getInstance()->executeS($sql_query['select']."\n".$sql_query['from']."\n".$sql_query['join']."\n".$sql_query['where']."\n".$sql_query['group'], true, false);
            }

            // price & weight have slidebar, so it's ok to not complete recompute the product list
            if (!empty($selected_filters['price']) && $filter['type'] != 'price' && $filter['type'] != 'weight') {
                $products = self::filterProductsByPrice(@$selected_filters['price'], $products);
            }

            if (!empty($sql_query['second_query']))
            {
                $res = Db::getInstance()->executeS($sql_query['second_query']);
                if ($res)
                    $products = array_merge($products, $res);
            }

            switch ($filter['type'])
            {
                case 'price':
                    if ($this->showPriceFilter()) {
                        $price_array = array(
                            'type_lite' => 'price',
                            'type' => 'price',
                            'id_key' => 0,
                            'name' => $this->l('Price'),
                            'slider' => true,
                            'max' => '0',
                            'min' => null,
                            'values' => array ('1' => 0),
                            'unit' => $currency->sign,
                            'format' => $currency->format,
                            'filter_show_limit' => $filter['filter_show_limit'],
                            'filter_type' => $filter['filter_type']
                        );
                        if (isset($products) && $products)
                            foreach ($products as $product)
                            {
                                if (is_null($price_array['min']))
                                {
                                    $price_array['min'] = $product['price_min'];
                                    $price_array['values'][0] = $product['price_min'];
                                }
                                else if ($price_array['min'] > $product['price_min'])
                                {
                                    $price_array['min'] = $product['price_min'];
                                    $price_array['values'][0] = $product['price_min'];
                                }

                                if ($price_array['max'] < $product['price_max'])
                                {
                                    $price_array['max'] = $product['price_max'];
                                    $price_array['values'][1] = $product['price_max'];
                                }
                            }

                        if ($price_array['max'] != $price_array['min'] && $price_array['min'] != null)
                        {
                            if ($filter['filter_type'] == 2)
                            {
                                $price_array['list_of_values'] = array();
                                $nbr_of_value = $filter['filter_show_limit'];
                                if ($nbr_of_value < 2)
                                    $nbr_of_value = 4;
                                $delta = ($price_array['max'] - $price_array['min']) / $nbr_of_value;
                                $current_step = $price_array['min'];
                                for ($i = 0; $i < $nbr_of_value; $i++)
                                    $price_array['list_of_values'][] = array(
                                        (int)($price_array['min'] + $i * $delta),
                                        (int)($price_array['min'] + ($i + 1) * $delta)
                                    );
                            }
                            if (isset($selected_filters['price']) && isset($selected_filters['price'][0])
                                && isset($selected_filters['price'][1]))
                            {
                                $price_array['values'][0] = $selected_filters['price'][0];
                                $price_array['values'][1] = $selected_filters['price'][1];
                            }
                            $filter_blocks[] = $price_array;
                        }
                    }
                    break;

                case 'weight':
                    $weight_array = array(
                        'type_lite' => 'weight',
                        'type' => 'weight',
                        'id_key' => 0,
                        'name' => $this->l('Weight'),
                        'slider' => true,
                        'max' => '0',
                        'min' => null,
                        'values' => array ('1' => 0),
                        'unit' => Configuration::get('PS_WEIGHT_UNIT'),
                        'format' => 5, // Ex: xxxxx kg
                        'filter_show_limit' => $filter['filter_show_limit'],
                        'filter_type' => $filter['filter_type']
                    );
                    if (isset($products) && $products)
                        foreach ($products as $product)
                        {
                            if (is_null($weight_array['min']))
                            {
                                $weight_array['min'] = $product['weight'];
                                $weight_array['values'][0] = $product['weight'];
                            }
                            else if ($weight_array['min'] > $product['weight'])
                            {
                                $weight_array['min'] = $product['weight'];
                                $weight_array['values'][0] = $product['weight'];
                            }

                            if ($weight_array['max'] < $product['weight'])
                            {
                                $weight_array['max'] = $product['weight'];
                                $weight_array['values'][1] = $product['weight'];
                            }
                        }
                    if ($weight_array['max'] != $weight_array['min'] && $weight_array['min'] != null)
                    {
                        if (isset($selected_filters['weight']) && isset($selected_filters['weight'][0])
                            && isset($selected_filters['weight'][1]))
                        {
                            $weight_array['values'][0] = $selected_filters['weight'][0];
                            $weight_array['values'][1] = $selected_filters['weight'][1];
                        }
                        $filter_blocks[] = $weight_array;
                    }
                    break;

                case 'condition':
                    $condition_array = array(
                        'new' => array('name' => $this->l('New'),'nbr' => 0),
                        'used' => array('name' => $this->l('Used'), 'nbr' => 0),
                        'refurbished' => array('name' => $this->l('Refurbished'),
                            'nbr' => 0)
                    );
                    if (isset($products) && $products)
                        foreach ($products as $product)
                            if (isset($selected_filters['condition']) && in_array($product['condition'], $selected_filters['condition']))
                                $condition_array[$product['condition']]['checked'] = true;
                    foreach ($condition_array as $key => $condition)
                        if (isset($selected_filters['condition']) && in_array($key, $selected_filters['condition']))
                            $condition_array[$key]['checked'] = true;
                    if (isset($products) && $products)
                        foreach ($products as $product)
                            if (isset($condition_array[$product['condition']]))
                                $condition_array[$product['condition']]['nbr']++;
                    $filter_blocks[] = array(
                        'type_lite' => 'condition',
                        'type' => 'condition',
                        'id_key' => 0,
                        'name' => $this->l('Condition'),
                        'values' => $condition_array,
                        'filter_show_limit' => $filter['filter_show_limit'],
                        'filter_type' => $filter['filter_type']
                    );
                    break;

                case 'quantity':
                    $quantity_array = array (
                        0 => array('name' => $this->l('Not available'), 'nbr' => 0),
                        1 => array('name' => $this->l('In stock'), 'nbr' => 0)
                    );
                    foreach ($quantity_array as $key => $quantity)
                        if (isset($selected_filters['quantity']) && in_array($key, $selected_filters['quantity']))
                            $quantity_array[$key]['checked'] = true;
                    if (isset($products) && $products)
                        foreach ($products as $product)
                        {
                            //If oosp move all not available quantity to available quantity
                            if ((int)$product['quantity'] > 0 || Product::isAvailableWhenOutOfStock($product['out_of_stock']))
                                $quantity_array[1]['nbr']++;
                            else
                                $quantity_array[0]['nbr']++;
                        }

                    $filter_blocks[] = array(
                        'type_lite' => 'quantity',
                        'type' => 'quantity',
                        'id_key' => 0,
                        'name' => $this->l('Availability'),
                        'values' => $quantity_array,
                        'filter_show_limit' => $filter['filter_show_limit'],
                        'filter_type' => $filter['filter_type']
                    );

                    break;

                case 'manufacturer':
                    if (isset($products) && $products)
                    {
                        $manufaturers_array = array();
                        foreach ($products as $manufacturer)
                        {
                            if (!isset($manufaturers_array[$manufacturer['id_manufacturer']]))
                                $manufaturers_array[$manufacturer['id_manufacturer']] = array('name' => $manufacturer['name'], 'nbr' => $manufacturer['nbr']);
                            if (isset($selected_filters['manufacturer']) && in_array((int)$manufacturer['id_manufacturer'], $selected_filters['manufacturer']))
                                $manufaturers_array[$manufacturer['id_manufacturer']]['checked'] = true;
                        }
                        $filter_blocks[] = array(
                            'type_lite' => 'manufacturer',
                            'type' => 'manufacturer',
                            'id_key' => 0,
                            'name' => $this->l('Manufacturer'),
                            'values' => $manufaturers_array,
                            'filter_show_limit' => $filter['filter_show_limit'],
                            'filter_type' => $filter['filter_type']
                        );
                    }
                    break;

                case 'id_attribute_group':
                    $attributes_array = array();
                    if (isset($products) && $products)
                    {
                        foreach ($products as $attributes)
                        {
                            if (!isset($attributes_array[$attributes['id_attribute_group']]))
                                $attributes_array[$attributes['id_attribute_group']] = array (
                                    'type_lite' => 'id_attribute_group',
                                    'type' => 'id_attribute_group',
                                    'id_key' => (int)$attributes['id_attribute_group'],
                                    'name' =>  $attributes['attribute_group_name'],
                                    'is_color_group' => (bool)$attributes['is_color_group'],
                                    'values' => array(),
                                    'url_name' => $attributes['name_url_name'],
                                    'meta_title' => $attributes['name_meta_title'],
                                    'filter_show_limit' => $filter['filter_show_limit'],
                                    'filter_type' => $filter['filter_type']
                                );

                            if (!isset($attributes_array[$attributes['id_attribute_group']]['values'][$attributes['id_attribute']]))
                                $attributes_array[$attributes['id_attribute_group']]['values'][$attributes['id_attribute']] = array(
                                    'color' => $attributes['color'],
                                    'name' => $attributes['attribute_name'],
                                    'nbr' => (int)$attributes['nbr'],
                                    'url_name' => $attributes['value_url_name'],
                                    'meta_title' => $attributes['value_meta_title']
                                );

                            if (isset($selected_filters['id_attribute_group'][$attributes['id_attribute']]))
                                $attributes_array[$attributes['id_attribute_group']]['values'][$attributes['id_attribute']]['checked'] = true;
                        }

                        $filter_blocks = array_merge($filter_blocks, $attributes_array);
                    }
                    break;
                case 'id_feature':
                    $feature_array = array();
                    if (isset($products) && $products)
                    {
                        foreach ($products as $feature)
                        {
                            if (!isset($feature_array[$feature['id_feature']]))
                                $feature_array[$feature['id_feature']] = array(
                                    'type_lite' => 'id_feature',
                                    'type' => 'id_feature',
                                    'id_key' => (int)$feature['id_feature'],
                                    'values' => array(),
                                    'name' => $feature['feature_name'],
                                    'url_name' => $feature['name_url_name'],
                                    'meta_title' => $feature['name_meta_title'],
                                    'filter_show_limit' => $filter['filter_show_limit'],
                                    'filter_type' => $filter['filter_type']
                                );

                            if (!isset($feature_array[$feature['id_feature']]['values'][$feature['id_feature_value']]))
                                $feature_array[$feature['id_feature']]['values'][$feature['id_feature_value']] = array(
                                    'nbr' => (int)$feature['nbr'],
                                    'name' => $feature['value'],
                                    'url_name' => $feature['value_url_name'],
                                    'meta_title' => $feature['value_meta_title']
                                );

                            if (isset($selected_filters['id_feature'][$feature['id_feature_value']]))
                                $feature_array[$feature['id_feature']]['values'][$feature['id_feature_value']]['checked'] = true;
                        }

                        //Natural sort
                        foreach ($feature_array as $key => $value)
                        {
                            $temp = array();
                            foreach ($feature_array[$key]['values'] as $keyint => $valueint)
                                $temp[$keyint] = $valueint['name'];

                            natcasesort($temp);
                            $temp2 = array();

                            foreach ($temp as $keytemp => $valuetemp)
                                $temp2[$keytemp] = $feature_array[$key]['values'][$keytemp];

                            $feature_array[$key]['values'] = $temp2;
                        }

                        $filter_blocks = array_merge($filter_blocks, $feature_array);
                    }
                    break;

                case 'category':
                    $tmp_array = array();
                    if (isset($products) && $products)
                    {
                        $categories_with_products_count = 0;
                        foreach ($products as $category)
                        {
                            $tmp_array[$category['id_category']] = array(
                                'name' => $category['name'],
                                'nbr' => (int)$category['count_products']
                            );

                            if ((int)$category['count_products'])
                                $categories_with_products_count++;

                            if (isset($selected_filters['category']) && in_array($category['id_category'], $selected_filters['category']))
                                $tmp_array[$category['id_category']]['checked'] = true;
                        }
                        if ($categories_with_products_count || !Configuration::get('PS_LAYERED_HIDE_0_VALUES'))
                            $filter_blocks[] = array (
                                'type_lite' => 'category',
                                'type' => 'category',
                                'id_key' => 0, 'name' => $this->l('Categories'),
                                'values' => $tmp_array,
                                'filter_show_limit' => $filter['filter_show_limit'],
                                'filter_type' => $filter['filter_type']
                            );
                    }
                    break;
            }

            if (count($filter_blocks) >= 30) {
                break;    
            }
        }

        // All non indexable attribute and feature
            $non_indexable = array();

            // Get all non indexable attribute groups
            foreach (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT public_name
		FROM `'._DB_PREFIX_.'attribute_group_lang` agl
		LEFT JOIN `'._DB_PREFIX_.'layered_indexable_attribute_group` liag
		ON liag.id_attribute_group = agl.id_attribute_group
		WHERE indexable IS NULL OR indexable = 0
		AND id_lang = '.(int)$id_lang) as $attribute)
                $non_indexable[] = Tools::link_rewrite($attribute['public_name']);

            // Get all non indexable features
            foreach (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT name
		FROM `'._DB_PREFIX_.'feature_lang` fl
		LEFT JOIN  `'._DB_PREFIX_.'layered_indexable_feature` lif
		ON lif.id_feature = fl.id_feature
		WHERE indexable IS NULL OR indexable = 0
		AND id_lang = '.(int)$id_lang) as $attribute)
                $non_indexable[] = Tools::link_rewrite($attribute['name']);

            //generate SEO link
            $param_selected = '';
            $param_product_url = '';
            $option_checked_array = array();
            $param_group_selected_array = array();
            $title_values = array();
            $meta_values = array();

            //get filters checked by group

            foreach ($filter_blocks as $type_filter)
            {
                $filter_name = (!empty($type_filter['url_name']) ? $type_filter['url_name'] : $type_filter['name']);
                $filter_meta = (!empty($type_filter['meta_title']) ? $type_filter['meta_title'] : $type_filter['name']);
                $attr_key = $type_filter['type'].'_'.$type_filter['id_key'];

                $param_group_selected = '';
                $lower_filter = strtolower($type_filter['type']);
                $filter_name_rewritten = Tools::link_rewrite($filter_name);

                if (($lower_filter == 'price' || $lower_filter == 'weight')
                    && (float)$type_filter['values'][0] > (float)$type_filter['min']
                    && (float)$type_filter['values'][1] > (float)$type_filter['max'])
                {
                    $param_group_selected .= $this->getAnchor().str_replace($this->getAnchor(), '_', $type_filter['values'][0])
                        .$this->getAnchor().str_replace($this->getAnchor(), '_', $type_filter['values'][1]);
                    $param_group_selected_array[$filter_name_rewritten][] = $filter_name_rewritten;

                    if (!isset($title_values[$filter_meta]))
                        $title_values[$filter_meta] = array();
                    $title_values[$filter_meta][] = $filter_meta;
                    if (!isset($meta_values[$attr_key]))
                        $meta_values[$attr_key] = array('title' => $filter_meta, 'values' => array());
                    $meta_values[$attr_key]['values'][] = $filter_meta;
                }
                else
                {
                    foreach ($type_filter['values'] as $key => $value)
                    {
                        if (is_array($value) && array_key_exists('checked', $value ))
                        {
                            $value_name = !empty($value['url_name']) ? $value['url_name'] : $value['name'];
                            $value_meta = !empty($value['meta_title']) ? $value['meta_title'] : $value['name'];
                            $param_group_selected .= $this->getAnchor().str_replace($this->getAnchor(), '_', Tools::link_rewrite($value_name));
                            $param_group_selected_array[$filter_name_rewritten][] = Tools::link_rewrite($value_name);

                            if (!isset($title_values[$filter_meta]))
                                $title_values[$filter_meta] = array();
                            $title_values[$filter_meta][] = $value_name;
                            if (!isset($meta_values[$attr_key]))
                                $meta_values[$attr_key] = array('title' => $filter_meta, 'values' => array());
                            $meta_values[$attr_key]['values'][] = $value_meta;
                        }
                        else
                            $param_group_selected_array[$filter_name_rewritten][] = array();
                    }
                }

                if (!empty($param_group_selected))
                {
                    $param_selected .= '/'.str_replace($this->getAnchor(), '_', $filter_name_rewritten).$param_group_selected;
                    $option_checked_array[$filter_name_rewritten] = $param_group_selected;
                }
                // select only attribute and group attribute to display an unique product combination link
                if (!empty($param_group_selected) && $type_filter['type'] == 'id_attribute_group')
                    $param_product_url .= '/'.str_replace($this->getAnchor(), '_', $filter_name_rewritten).$param_group_selected;

            }

            if ($this->page > 1)
                $param_selected .= '/page-'.$this->page;

            $blacklist = array('weight', 'price');

            if (!Configuration::get('PS_LAYERED_FILTER_INDEX_CDT'))
                $blacklist[] = 'condition';
            if (!Configuration::get('PS_LAYERED_FILTER_INDEX_QTY'))
                $blacklist[] = 'quantity';
            if (!Configuration::get('PS_LAYERED_FILTER_INDEX_MNF'))
                $blacklist[] = 'manufacturer';
            if (!Configuration::get('PS_LAYERED_FILTER_INDEX_CAT'))
                $blacklist[] = 'category';

            $global_nofollow = false;
            $categorie_link = Context::getContext()->link->getCategoryLink($parent, null, null);

            foreach ($filter_blocks as &$type_filter)
            {
                $filter_name = (!empty($type_filter['url_name']) ? $type_filter['url_name'] : $type_filter['name']);
                $filter_link_rewrite = Tools::link_rewrite($filter_name);

                if (count($type_filter) > 0 && !isset($type_filter['slider']))
                {
                    foreach ($type_filter['values'] as $key => $values)
                    {
                        $nofollow = false;
                        if (!empty($values['checked']) && in_array($type_filter['type'], $blacklist))
                            $global_nofollow = true;

                        $option_checked_clone_array = $option_checked_array;

                        // If not filters checked, add parameter
                        $value_name = !empty($values['url_name']) ? $values['url_name'] : $values['name'];

                        if (!in_array(Tools::link_rewrite($value_name), $param_group_selected_array[$filter_link_rewrite]))
                        {
                            // Update parameter filter checked before
                            if (array_key_exists($filter_link_rewrite, $option_checked_array))
                            {
                                $option_checked_clone_array[$filter_link_rewrite] = $option_checked_clone_array[$filter_link_rewrite].$this->getAnchor().str_replace($this->getAnchor(), '_', Tools::link_rewrite($value_name));

                                if (in_array($type_filter['type'], $blacklist))
                                    $nofollow = true;
                            }
                            else
                                $option_checked_clone_array[$filter_link_rewrite] = $this->getAnchor().str_replace($this->getAnchor(), '_', Tools::link_rewrite($value_name));
                        }
                        else
                        {
                            // Remove selected parameters
                            $option_checked_clone_array[$filter_link_rewrite] = str_replace($this->getAnchor().str_replace($this->getAnchor(), '_', Tools::link_rewrite($value_name)), '', $option_checked_clone_array[$filter_link_rewrite]);
                            if (empty($option_checked_clone_array[$filter_link_rewrite]))
                                unset($option_checked_clone_array[$filter_link_rewrite]);
                        }
                        $parameters = '';
                        ksort($option_checked_clone_array); // Order parameters
                        foreach ($option_checked_clone_array as $key_group => $value_group)
                            $parameters .= '/'.str_replace($this->getAnchor(), '_', $key_group).$value_group;

                        // Add nofollow if any blacklisted filters ins in parameters
                        foreach ($filter_blocks as $filter)
                        {
                            $name = Tools::link_rewrite((!empty($filter['url_name']) ? $filter['url_name'] : $filter['name']));
                            if (in_array($filter['type'], $blacklist) && strpos($parameters, $name.'-') !== false)
                                $nofollow = true;
                        }

                        // Check if there is an non indexable attribute or feature in the url
                        foreach ($non_indexable as $value)
                            if (strpos($parameters, '/'.$value) !== false)
                                $nofollow = true;

                        $type_filter['values'][$key]['link'] = $categorie_link.'#'.ltrim($parameters, '/');
                        $type_filter['values'][$key]['rel'] = ($nofollow) ? 'nofollow' : '';
                    }
                }
        }

        $n_filters = 0;

        if (isset($selected_filters['price']))
            if ($price_array['min'] == $selected_filters['price'][0] && $price_array['max'] == $selected_filters['price'][1])
                unset($selected_filters['price']);
        if (isset($selected_filters['weight']))
            if ($weight_array['min'] == $selected_filters['weight'][0] && $weight_array['max'] == $selected_filters['weight'][1])
                unset($selected_filters['weight']);

        foreach ($selected_filters as $filters)
            $n_filters += count($filters);

        $cache = array(
            'layered_show_qties' => (int)Configuration::get('PS_LAYERED_SHOW_QTIES'),
            'id_category_layered' => (int)$id_parent,
            'selected_filters' => $selected_filters,
            'n_filters' => (int)$n_filters,
            'nbr_filterBlocks' => count($filter_blocks),
            'filters' => $filter_blocks,
            'title_values' => $title_values,
            'meta_values' => $meta_values,
            'current_friendly_url' => $param_selected,
            'param_product_url' => $param_product_url,
            'no_follow' => (!empty($param_selected) || $global_nofollow)
        );

        return $cache;
    }

    private static function filterProductsByPrice($filter_value, $product_collection)
    {
        static $ps_layered_filter_price_usetax = null;
        static $ps_layered_filter_price_rounding = null;

        if (empty($filter_value))
            return $product_collection;

        if ($ps_layered_filter_price_usetax === null) {
            $ps_layered_filter_price_usetax = Configuration::get('PS_LAYERED_FILTER_PRICE_USETAX');
        }

        if ($ps_layered_filter_price_rounding === null) {
            $ps_layered_filter_price_rounding = Configuration::get('PS_LAYERED_FILTER_PRICE_ROUNDING');
        }

        foreach ($product_collection as $key => $product)
        {
            if (isset($filter_value) && $filter_value && isset($product['price_min']) && isset($product['id_product'])
                && (($product['price_min'] < (int)$filter_value[0] && $product['price_max'] > (int)$filter_value[0])
                    || ($product['price_max'] > (int)$filter_value[1] && $product['price_min'] < (int)$filter_value[1])))
            {
                $price = Product::getPriceStatic($product['id_product'], $ps_layered_filter_price_usetax);
                if ($ps_layered_filter_price_rounding) {
                    $price = (int)$price;
                }
                if ($price < $filter_value[0] || $price > $filter_value[1]) {
                    unset($product_collection[$key]);
                }
            }
        }
        return $product_collection;
    }

    private static function getId_attribute_groupFilterSubQuery($filter_value, $ignore_join = false)
    {
        if (empty($filter_value))
            return array();
        $query_filters = '
		AND EXISTS (SELECT *
		FROM `'._DB_PREFIX_.'product_attribute_combination` pac
		LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (pa.`id_product_attribute` = pac.`id_product_attribute`)
		WHERE pa.id_product = p.id_product AND pac.`id_attribute` IN ('.implode(",", $filter_value).')) ';

        return array('where' => $query_filters);
    }

    private static function getId_featureFilterSubQuery($filter_value, $ignore_join = false)
    {
        if (empty($filter_value))
            return array();
        $query_filters = ' AND EXISTS (SELECT * FROM '._DB_PREFIX_.'feature_product fp WHERE fp.id_product = p.id_product AND fp.`id_feature_value` IN ('.implode(",", $filter_value).')) ';

        return array('where' => $query_filters);
    }

    private static function getCategoryFilterSubQuery($filter_value, $ignore_join = false)
    {
        if (empty($filter_value))
            return array();
        $query_filters_where = ' AND EXISTS (SELECT * FROM '._DB_PREFIX_.'category_product cp WHERE id_product = p.id_product AND cp.`id_category` IN ('.implode(",", $filter_value).')) ';

        return array('where' => $query_filters_where);
    }

    private static function getPriceFilterSubQuery($filter_value, $ignore_join = false)
    {
        $id_currency = (int)Context::getContext()->currency->id;

        if (isset($filter_value) && $filter_value)
        {
            $price_filter_query = '
			INNER JOIN `'._DB_PREFIX_.'layered_price_index` psi ON (psi.id_product = p.id_product AND psi.id_currency = '.(int)$id_currency.'
			AND psi.price_min <= '.(int)$filter_value[1].' AND psi.price_max >= '.(int)$filter_value[0].' AND psi.id_shop='.(int)Context::getContext()->shop->id.') ';
            return array('join' => $price_filter_query);
        }
        return array();
    }
}
