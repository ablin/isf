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
                            'total_stock' => 0,
                            'total_dispo' => 0,
                            'total_jauge' => 0,
                            'tarif' => 0,
                            'nb_tarif' => 0
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
}
