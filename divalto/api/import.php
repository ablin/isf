<?php
require(dirname(__FILE__).'/../../config/config.inc.php');

set_time_limit(0);

class Import
{
    const CATEGORY = "famille";
    const PRODUCT = "produit";

    private $topMenu = null;
    private $TopMenuHasChanged = false;

    /**
     * Import constructor.
     */
    public function __construct()
    {
        $this->checkMethod();
        $this->checkRights();
        $this->doImport();
    }

    /**
     *
     */
    private function checkMethod()
    {
        if ($_SERVER['REQUEST_METHOD'] != "POST") {
            http_response_code(405);
            exit;
        }
    }

    /**
     *
     */
    private function checkRights()
    {
        $headers = getallheaders();
        if (!isset($headers["Authorization"]) || $headers["Authorization"] != "DZQ4V5hyne,TR.K56r_IL6J'he(Nfjy?KrysEHvQZGzku.") {
            http_response_code(401);
            exit;
        }
    }

    /**
     *
     */
    private function doImport()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

        $data = json_decode(file_get_contents("php://input"));
        switch ($data->table) {
            case self::CATEGORY:
                $this->importCategory($data->file);
                break;
            case self::PRODUCT:
                $this->importProduct($data->file);
                break;
        }

        http_response_code(200);
    }

    /**
     * @param $file
     */
    private function importCategory($file)
    {
        $familles = $this->load($file);
        $this->getTopMenu();

        foreach ($familles as $famille) {

            // Check if category must be updated
            $categoryHasChanged = Category::categoryHasChanged((string) $famille->name, (string) $famille->date_upd);
            $categoryExists = Category::getCategory((string) $famille->name);
            $categoryId = $categoryExists['id_category'];

            if (!$categoryId || $categoryHasChanged) {
                $category = new Category();

                // Category default values if not exist
                if (!$categoryId) {
                    $category->date_add = date('Y-m-d H:i:s');
                    $category->position = (int)Category::getLastPosition((int) $category->id_parent, 1);
                    $category->id_shop_default = 1;
                }

                // Category parent
                if ((string) $famille->parent) {
                    $categoryParent = Category::getCategory((string) $famille->parent);
                    if ($categoryParent) {
                        $category->id_parent = $categoryParent['id_category'];
                        $category->level_depth = !$categoryId ? (int) $categoryParent['level_depth'] + 1 : $categoryExists['level_depth'];
                    } else {
                        $webServiceDiva = new WebServiceDiva(
                            '<ACTION>ERREUR',
                            sprintf(
                                'La famille parent %s n\'existe pas',
                                (string) $famille->parent
                            )
                        );
                        $webServiceDiva->call();
                        continue;
                    }
                } else {
                    $category->id_parent = 2;
                    $category->level_depth = !$categoryId ? 2 : $categoryExists['level_depth'];
                }

                // Category informations
                $category->active = (int) $famille->active;
                $category->is_root_category = 0;
                $category->date_upd_divalto = date((string) $famille->date_upd);
                $category->name = AdminImportController::createMultiLangField(addslashes((string) $famille->name));
                $category->description = AdminImportController::createMultiLangField(addslashes((string) $famille->description));
                $category->link_rewrite = AdminImportController::createMultiLangField(Tools::link_rewrite((string) $famille->name));
                $category->meta_title = AdminImportController::createMultiLangField((string) $famille->name);
                $category->meta_keywords = AdminImportController::createMultiLangField((string) $famille->description);
                $category->meta_description = AdminImportController::createMultiLangField((string) $famille->description);

                // Category update/add
                if ($categoryId && $categoryHasChanged) {
                    $category->id = $categoryId;
                    $category->id_category = $categoryId;
                    $category->position = $categoryExists['position'];
                    $category->nleft = $categoryExists['nleft'];
                    $category->nright = $categoryExists['nright'];
                    $category->update();
                } else {
                    $category->add();
                    $categoryNew = Category::getCategory((string) $famille->name);
                    $category->id = $categoryNew['id_category'];
                }

                // Category image
                if ((string) $famille->image) {
                    AdminImportController::copyImg($category->id, null, $famille->image, 'categories', true);
                }

                // Category blocklayered
                if (isset($famille->features)) {
                    Db::getInstance()->execute(sprintf('DELETE FROM %slayered_filter WHERE name = "%s"',_DB_PREFIX_, (string) $famille->name));
                    Db::getInstance()->execute(sprintf('DELETE FROM %slayered_category WHERE id_category = %d',_DB_PREFIX_, $category->id));

                    $attributeGroup = AttributeGroup::getAttributeGroupByName('Sous référence');
                    $filter_data = array(
                        'categories' => array($category->id),
                        'shop_list' => Shop::getShops(false, null, true),
                        'layered_selection_subcategories' => array('filter_type' => 0, 'filter_show_limit' => 0),
                        'layered_selection_ag_'.$attributeGroup['id_attribute_group'] => array('filter_type' => 0, 'filter_show_limit' => 0)
                    );

                    Db::getInstance()->execute(sprintf(
                        'INSERT INTO %slayered_category (id_category, id_shop, id_value, type, position, filter_show_limit, filter_type) VALUES (%d, 1, NULL,\'category\', 1, 0, 0)',
                        _DB_PREFIX_, $category->id
                    ));

                    Db::getInstance()->execute(sprintf(
                        'INSERT INTO %slayered_category (id_category, id_shop, id_value, type, position, filter_show_limit, filter_type) VALUES (%d, 1, %d,\'id_attribute_group\', 2, 0, 0)',
                        _DB_PREFIX_, $category->id, $attributeGroup['id_attribute_group']
                    ));

                    $position = 3;
                    foreach ($famille->features->feature as $familleFeature) {
                        if (isset($familleFeature->active) && (int) $familleFeature->active == 1) {
                            $feature = Feature::getFeatureByName((string) $familleFeature->name);

                            // Add feature if not exists
                            if (!$feature) {
                                $featureAdd = new Feature();
                                $featureAdd->name = AdminImportController::createMultiLangField((string) $familleFeature->name);
                                $featureAdd->add();
                                $feature = Feature::getFeatureByName((string) $familleFeature->name);
                            }

                            Db::getInstance()->execute(sprintf(
                                'INSERT INTO %slayered_category (id_category, id_shop, id_value, type, position, filter_show_limit, filter_type) VALUES (%d, 1, %d,\'id_feature\', %d, 0, %d)',
                                _DB_PREFIX_, $category->id, $feature['id_feature'], $position, (int) $familleFeature->type
                            ));
                            $filter_data['layered_selection_feat_'.$feature['id_feature']] = array('filter_type' => (int) $familleFeature->type, 'filter_show_limit' => 0);
                            $position++;
                        }
                    }

                    Db::getInstance()->execute(sprintf(
                        'INSERT INTO %slayered_filter(name, filters, n_categories, date_add) VALUES (\'%s\', \'%s\', 1, NOW())',
                        _DB_PREFIX_,
                        (string) $famille->name,
                        pSQL(serialize($filter_data))
                    ));

                    $blockLayered = new BlockLayered();
                    $blockLayered->indexAttribute();
                }
                
                // Category top menu
                if ((int) $famille->top_menu == 1) {
                    if (!in_array('CAT'.$category->id, $this->topMenu)) {
                        $this->topMenu[] = 'CAT'.$category->id;
                        $this->TopMenuHasChanged = true;
                    }
                } else {
                    $this->topMenu = array_diff( $this->topMenu, array('CAT'.$category->id_category));
                    $this->TopMenuHasChanged = true;
                }

            }

        }

        if ($this->TopMenuHasChanged) {
            $this->updateTopMenu();
        }
    }

    /**
     * @param $file
     */
    private function importProduct($file)
    {
        $produits = $this->load($file);

        foreach ($produits as $produit) {
            // Check if product must be updated
            $productHasChanged = Product::productHasChanged((string) $produit->reference, (string) $produit->date_upd);
            $productId = Product::getProductByReference((string) $produit->reference);
            $productHasChanged = true; //TODO a supprimer

            if (!$productId || $productHasChanged) {
                $product = new Product();
                $product->reference = (string) $produit->reference;

                // Product default values if not exist
                if (!$productId) {
                    $product->date_add = date('Y-m-d H:i:s');
                    $product->id_shop_default = 1;
                }

                // Product categories
                $productCategories = array();
                if (isset($produit->familles)) {
                    foreach ($produit->familles->famille as $famille) {
                        $category = Category::getCategory((string) $famille->name);
                        if ($category) {
                            $productCategories[] = $category['id_category'];
                            if ((int) $famille->default == 1) {
                                $product->id_category_default = $category['id_category'];
                            }
                        } else {
                            $webServiceDiva = new WebServiceDiva(
                                '<ACTION>ERREUR',
                                sprintf(
                                    'La famille %s n\'existe pas',
                                    (string) $famille->name
                                )
                            );
                            $webServiceDiva->call();
                            continue;
                        }
                    }
                }

                // Product informations
                $product->active = (int) $produit->active;
                $product->date_upd_divalto = date((string) $produit->date_upd);
                $product->name = AdminImportController::createMultiLangField((string) $produit->name);
                $product->description_short = AdminImportController::createMultiLangField(addslashes((string) $produit->description_short));
                $product->description = AdminImportController::createMultiLangField(addslashes((string) $produit->description));
                $product->link_rewrite = AdminImportController::createMultiLangField(Tools::link_rewrite((string) $produit->name));
                $product->price = (float) $produit->price;

                // Product update/add
                if ($productId && $productHasChanged) {
                    $product->id = $productId;
                    $product->update();
                } else {
                    $product->add();
                    $product->id = Product::getProductByReference((string) $produit->reference);
                }

                // Product images
                if ($produit->images) {
                    $i = 0;
                    Db::getInstance()->execute(sprintf('DELETE FROM %simage WHERE id_product = "%d"', _DB_PREFIX_, $product->id));
                    Db::getInstance()->execute(sprintf('DELETE FROM %simage_shop WHERE id_product = "%d"', _DB_PREFIX_, $product->id));
                    foreach ($produit->images->image as $imageDivalto) {
                        $image = new Image();
                        $image->id_product = $product->id;
                        $image->position = Image::getHighestPosition($product->id) + 1;
                        $image->cover = ($i == 0) ? true : false;
                        $image->add();
                        AdminImportController::copyImg($product->id, $image->id, (string)$imageDivalto->url, 'products', true);
                        $i++;
                    }
                }

                // Product attributes
                if ($produit->attributes) {
                    foreach ($produit->attributes->attribute as $attribute) {
                        $attributeList = array();
                        if (false == $id_product_attribute = $this->productAttributeExists($attribute, $product->id)) {
                            if (false == $attributeId = $this->attributeExists($attribute)) {
                                $attributeNew = new AttributeCore();
                                $attributeNew->id_attribute_group = (int) $attribute->id_attribute_group;
                                $attributeNew->name = AdminImportController::createMultiLangField((string)$attribute->value);
                                $attributeNew->add();
                                $attributeId = $this->attributeExists($attribute);
                            }
                            $attributeList[] = $attributeId;
                            $id_product_attribute = $product->addCombinationEntity(0, 0, 0, 0, 0, 0, array(), "", null, "", (int) $attribute->default, null, null, 1, array(), "0000-00-00");

                            $combination = new Combination((int) $id_product_attribute);
                            $combination->setAttributes($attributeList);
                            $product->checkDefaultAttributes();
                            if ($attribute->default) {
                                Product::updateDefaultAttribute((int) $product->id);
                                if (isset($id_product_attribute)) {
                                    $product->cache_default_attribute = (int) $id_product_attribute;
                                    $product->update();
                                }
                            }
                        } else {
                            $product->updateAttribute((int) $id_product_attribute,null,null, null,null,null,array(),"","",(int) $attribute->default, null, null,null, false);
                        }
                    }
                }
                //TODO Supprimer attributs qui n'existent pas dans divalto   <========= 1
                /* A tester
                Récupérer le $id_product_attribute
                $combination = new Combination($id_product_attribute);
                $res = $combination->delete();
                }*/


                //TODO restent caractéristiques    <========= 2

                if (count($productCategories) > 0  && $product->id) {
                    $product->updateCategories($productCategories);
                }
            }

            //TODO réindexer les produits    <========= 3
        }
    }

    /**
     * @param $attribute
     * @param $productId
     * @return false|string|null
     */
    private function productAttributeExists($attribute, $productId)
    {
        $sql = sprintf(
            "SELECT pac.* FROM %sattribute_lang al
                            INNER JOIN %sproduct_attribute_combination pac using (id_attribute)
                            INNER JOIN %sproduct_attribute pa using (id_product_attribute)
                            INNER JOIN %sattribute a using (id_attribute)
                            WHERE a.id_attribute_group = %d AND al.name = '%s' AND al.id_lang = %d AND pa.id_product = %d",
            _DB_PREFIX_,
            _DB_PREFIX_,
            _DB_PREFIX_,
            _DB_PREFIX_,
            (int) $attribute->id_attribute_group,
            (string) $attribute->value,
            (int) Context::getContext()->language->id,
            $productId
        );

        return Db::getInstance()->getValue($sql);
    }

    /**
     * @param $attribute
     * @param $productId
     * @return false|string|null
     */
    private function attributeExists($attribute)
    {
        $sql = sprintf(
            "SELECT al.* FROM %sattribute_lang al
                            INNER JOIN %sattribute a using (id_attribute)
                            WHERE a.id_attribute_group = %d AND al.name = '%s' AND al.id_lang = %d",
            _DB_PREFIX_,
            _DB_PREFIX_,
            (int) $attribute->id_attribute_group,
            (string) $attribute->value,
            (int) Context::getContext()->language->id
        );

        return Db::getInstance()->getValue($sql);
    }

    /**
     *
     */
    private function getLastProductAttribute()
    {
        $sql = sprintf(
            "SELECT pa.id_product_attribute FROM %sproduct_attribute pa order by 1 DESC",
            _DB_PREFIX_
        );

        return Db::getInstance()->getValue($sql);
    }

    /**
     *
     */
    private function getTopMenu()
    {
        $this->topMenu = Configuration::get('MOD_BLOCKTOPMENU_ITEMS') ? explode(",", Configuration::get('MOD_BLOCKTOPMENU_ITEMS')) : array();
    }

    /**
     *
     */
    private function updateTopMenu()
    {
        Configuration::updateValue('MOD_BLOCKTOPMENU_ITEMS', (string)implode(',', $this->topMenu));
    }

    /**
     * @param $path
     * @return SimpleXMLElement
     */
    private function load($path)
    {
        return simplexml_load_file($path);
    }

}

new Import();
