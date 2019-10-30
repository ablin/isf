<?php

class BlocktopmenuOverride extends Blocktopmenu
{
    private $count;

    protected function generateCategoriesMenu($categories, $is_children = 0)
    {
        $html = '';

        foreach ($categories as $key => $category) {

            if (!$is_children) {
                $this->count = 0;
            }

            $nb_products = Db::getInstance()->ExecuteS('
                SELECT COUNT(cp.`id_product`) as totalProducts
                FROM `'._DB_PREFIX_.'category_product` cp
                LEFT JOIN `'._DB_PREFIX_.'product` p ON p.`id_product` = cp.`id_product`
                WHERE cp.`id_category` = '.$category['id_category'].' AND p.`active` = 1');

            if ($nb_products[0]['totalProducts'] == 0) {
                continue;
            }

            if ($category['level_depth'] > 1) {
                $cat = new Category($category['id_category']);
                $link = Tools::HtmlEntitiesUTF8($cat->getLink());
            } else {
                $link = $this->context->link->getPageLink('index');
            }

            /* Whenever a category is not active we shouldnt display it to customer */
            if ((bool)$category['active'] === false) {
                continue;
            }

            $html .= '<li'.(($this->page_name == 'category'
                    && (int)Tools::getValue('id_category') == (int)$category['id_category']) ? ' class="sfHoverForce"' : '').'>';
            $html .= '<a href="'.$link.'" title="'.strip_tags(stripslashes($category['description'])).'">'.substr(strip_tags(stripslashes($category['description'])), 0, 40);

            if ($is_children) {

                $files = scandir(_PS_CAT_IMG_DIR_);

                if (count(preg_grep('/^'.$category['id_category'].'-medium_default.jpg/i', $files)) > 0) {

                    foreach ($files as $file) {
                        if (preg_match('/^'.$category['id_category'].'-medium_default.jpg/i', $file) === 1) {
                            $html .= '<div><img src="'.$this->context->link->getMediaLink(_THEME_CAT_DIR_.$file).'" alt="'.Tools::SafeOutput($category['description']).'" title="'.Tools::SafeOutput($category['description']).'" class="imgm" /></div>';
                            break;
                        }
                    }
                } else {
                    $html .= '<div><img src="'.$this->context->link->getMediaLink(_THEME_CAT_DIR_.$this->context->language->iso_code.'-default-medium_default.jpg').'" alt="'.Tools::SafeOutput($category['description']).'" title="'.Tools::SafeOutput($category['description']).'" class="imgm" /></div>';
                }

            }

            $html .= '</a>';

            if (isset($category['children']) && !empty($category['children']) && $this->count < 1) {
                $this->count++;
                $html .= '<ul>';
                $html .= $this->generateCategoriesMenu($category['children'], 1);

                if ((int)$category['level_depth'] > 1 && !$is_children) {
                    $files = scandir(_PS_CAT_IMG_DIR_);

                    if (count(preg_grep('/^'.$category['id_category'].'-([0-9])?_thumb.jpg/i', $files)) > 0) {

                        $html .= '<li class="category-thumbnail">';

                        foreach ($files as $file) {
                            if (preg_match('/^'.$category['id_category'].'-([0-9])?_thumb.jpg/i', $file) === 1) {
                                $html .= '<div><img src="'.$this->context->link->getMediaLink(_THEME_CAT_DIR_.$file)
                                    .'" alt="'.Tools::SafeOutput($category['name']).'" title="'
                                    .Tools::SafeOutput($category['name']).'" class="imgm" /></div>';
                            }
                        }

                        $html .= '</li>';
                    }
                }

                $html .= '</ul>';
            }

            $html .= '</li>';
        }

        return $html;
    }
}
