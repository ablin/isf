<?php
require_once __DIR__.'/../../config/config.inc.php';

set_time_limit(0);
ini_set('memory_limit','512M');

class Import
{
    const CATEGORY = "famille";
    const PRODUCT = "produit";

    private $topMenu = null;
    private $TopMenuHasChanged = false;

    /**
     * Import constructor.
     */
    public function __construct($data)
    {
        $this->doImport($data);
    }

    /**
     *
     */
    private function doImport($data)
    {
        switch ($data->table) {
            case self::CATEGORY:
                $this->importCategory($data->file);
                break;
            case self::PRODUCT:
                $this->importProduct($data->file);
                break;
        }
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
                        $category->is_root_category = 0;
                    } else {
                        $webServiceDiva = new WebServiceDiva(
                            '<ACTION>ERREUR',
                            sprintf(
                                date('Y-m-d H:i:s').' : La famille parent %s n\'existe pas',
                                (string) $famille->parent
                            )
                        );
                        $webServiceDiva->call();
                        continue;
                    }
                } else {
                    $category->id_parent = 1;
                    $category->level_depth = !$categoryId ? 1 : $categoryExists['level_depth'];
                    $category->is_root_category = 1;
                }

                // Category informations
                $category->active = (int) $famille->active;
                $category->link = (string) $famille->link;
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
                    AdminImportController::copyImg($category->id, null, (string) $famille->image, 'categories', true);
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
                        'layered_selection_price_slider' => array('filter_type' => 0, 'filter_show_limit' => 0),
                        'layered_selection_ag_'.$attributeGroup['id_attribute_group'] => array('filter_type' => 0, 'filter_show_limit' => 0)
                    );

                    if ($category->level_depth > 2) {
                        Db::getInstance()->execute(sprintf(
                            'INSERT INTO %slayered_category (id_category, id_shop, id_value, type, position, filter_show_limit, filter_type) VALUES (%d, 1, NULL,\'category\', 1, 0, 0)',
                            _DB_PREFIX_, $category->id
                        ));
                        Db::getInstance()->execute(sprintf(
                            'INSERT INTO %slayered_category (id_category, id_shop, id_value, type, position, filter_show_limit, filter_type) VALUES (%d, 1, NULL,\'price\', 2, 0, 0)',
                            _DB_PREFIX_, $category->id
                        ));
                        Db::getInstance()->execute(sprintf(
                            'INSERT INTO %slayered_category (id_category, id_shop, id_value, type, position, filter_show_limit, filter_type) VALUES (%d, 1, %d,\'id_attribute_group\', 3, 0, 0)',
                            _DB_PREFIX_, $category->id, $attributeGroup['id_attribute_group']
                        ));
                    }

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
                                    date('Y-m-d H:i:s').' : La famille %s n\'existe pas',
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
                if ($produit->features) {
                    foreach ($produit->features->feature as $productFeature) {
                        if ((string) $productFeature->name == "Marque") {
                            $product->link_rewrite = AdminImportController::createMultiLangField(Tools::link_rewrite((string) $productFeature->value . "-" . (string) $produit->reference . "-" . $produit->name));
                            break;
                        }
                    }
                } else {
                    $product->link_rewrite = AdminImportController::createMultiLangField(Tools::link_rewrite((string) $produit->reference . "-" . $produit->name));
                }
                $product->price = (float) $produit->price;

                // Product update/add
                if ($productId && $productHasChanged) {
                    $product->id = $productId;
                    $product->update();
                } else {
                    $product->add();
                    $product->id = Product::getProductByReference((string) $produit->reference);
                }

                // Product price indexes
                $blockLayered = new BlockLayered();
                $blockLayered->indexProductPrices((int) $product->id);

                // Product images
                if ($produit->images) {
                    $i = 0;
                    Db::getInstance()->execute(sprintf('DELETE FROM %simage WHERE id_product = %d', _DB_PREFIX_, $product->id));
                    Db::getInstance()->execute(sprintf('DELETE FROM %simage_shop WHERE id_product = %d', _DB_PREFIX_, $product->id));
                    foreach ($produit->images->image as $imageDivalto) {
                        $image = new Image();
                        $image->id_product = $product->id;
                        $image->position = Image::getHighestPosition($product->id) + 1;
                        $image->cover = ($i == 0) ? true : false;
                        $image->add();
                        AdminImportController::copyImg($product->id, $image->id, (string) $imageDivalto->url, 'products', true);
                        $i++;
                    }
                }

                // Product attributes
                if ($produit->attributes) {
                    $attributes = $this->getAllproductAttributes($product->id);
                    foreach ($produit->attributes->attribute as $attribute) {
                        $attributeList = array();
                        if (false == $id_product_attribute = $this->productAttributeExists($attribute, $product->id)) {
                            if (false == $attributeId = $this->attributeExists($attribute)) {
                                $attributeNew = new AttributeCore();
                                $attributeNew->id_attribute_group = (int) $attribute->id_attribute_group;
                                $attributeNew->name = AdminImportController::createMultiLangField((string) $attribute->value);
                                $attributeNew->add();
                                $attributeId = $this->attributeExists($attribute);
                            }
                            $attributeList[] = $attributeId;
                            if ((int) $attribute->default) {
                                $product->deleteDefaultAttributes();
                            }
                            $id_product_attribute = $product->addCombinationEntity(0, 0, 0, 0, 0, 0, array(), "", null, "", (int) $attribute->default, null, null, 1, array(), "0000-00-00");

                            $combination = new Combination((int) $id_product_attribute);
                            $combination->setAttributes($attributeList);
                            $product->checkDefaultAttributes();
                            if ((int) $attribute->default) {
                                Product::updateDefaultAttribute((int) $product->id);
                                if (isset($id_product_attribute)) {
                                    $product->cache_default_attribute = (int) $id_product_attribute;
                                    $product->update();
                                }
                            }
                        } else {
                            $product->updateAttribute((int) $id_product_attribute,null,null, null,null,null,array(),"","",(int) $attribute->default, null, null,null, false);
                        }
                        unset($attributes[array_search($id_product_attribute, $attributes)]);
                    }
                }

                if (isset($attributes) && $attributes) {
                    foreach ($attributes as $id_product_attribute) {
                        $product->deleteAttributeCombination((int) $id_product_attribute);
                    }
                }

                $blockLayered = new BlockLayered();
                $blockLayered->indexAttribute((int) $product->id);

                // Product features
                if ($produit->features) {
                    $features = $this->getAllproductFeatures($product->id);
                    foreach ($produit->features->feature as $productFeature) {
                        $feature = Feature::getFeatureByName((string) $productFeature->name);

                        // Add feature if not exists
                        if (!$feature) {
                            $featureAdd = new Feature();
                            $featureAdd->name = AdminImportController::createMultiLangField((string) $productFeature->name);
                            $featureAdd->add();
                            $feature = Feature::getFeatureByName((string) $productFeature->name);
                        } else {
                            unset($features[array_search($feature['id_feature'], $features)]);
                        }

                        $featureValue = FeatureValue::getFeatureValueByName((string) $productFeature->value, $feature['id_feature']);
                        // Add feature value if not exists, update otherwise
                        if (!$featureValue) {
                            $featureValueAdd = new FeatureValue();
                            $featureValueAdd->id_feature = $feature['id_feature'];
                        } else {
                            $featureValueAdd = new FeatureValue($featureValue['id_feature_value']);
                        }
                        $featureValueAdd->value = AdminImportController::createMultiLangField((string) $productFeature->value);
                        $featureValueAdd->custom = (int) $productFeature->custom;
                        if (!$featureValue) {
                            $featureValueAdd->add();
                        } else {
                            $featureValueAdd->update();
                        }
                        $featureValue = FeatureValue::getFeatureValueByName((string) $productFeature->value, $feature['id_feature']);

                        Product::addFeatureProductImport($product->id, $feature['id_feature'], $featureValue['id_feature_value']);
                    }
                }

                if (isset($features) && $features) {
                    foreach ($features as $id_feature) {
                        Db::getInstance()->execute(sprintf('DELETE FROM %sfeature_product WHERE id_product = %d AND id_feature = %d', _DB_PREFIX_, $product->id, $id_feature));
                    }
                }

                // Product correspondences
                if (isset($produit->correspondences)) {
                    $correspondences = $this->getAllproductCorrespondences($product->id);
                    foreach ($produit->correspondences->correspondence as $correspondence) {
                        if (false == $id_product_correspondence = $this->productCorrespondenceExists($correspondence, $product->id)) {
                            $correspondenceNew = new Correspondence();
                            $correspondenceNew->id_product = $product->id;
                            $correspondenceNew->name = (string) $correspondence->name;
                            $correspondenceNew->value = (string) $correspondence->value;
                            $correspondenceNew->add();
                        } else {
                            unset($correspondences[array_search($id_product_correspondence, $correspondences)]);
                        }
                    }
                }

                if (isset($correspondences) && $correspondences) {
                    foreach ($correspondences as $id_product_correspondence) {
                        $correspondence = new Correspondence($id_product_correspondence);
                        $correspondence->delete();
                    }
                }

                // Product accessories
                if ($produit->accessories) {
                    Db::getInstance()->execute(sprintf('DELETE FROM %saccessory WHERE id_product_1 = %d', _DB_PREFIX_, $product->id));
                    foreach ($produit->accessories->accessory as $accessory) {
                        $product_2 = Product::getProductByReference((string) $accessory->reference);
                        if (!$product_2) {
                            $webServiceDiva = new WebServiceDiva(
                                '<ACTION>ERREUR',
                                sprintf(
                                    date('Y-m-d H:i:s').' : Le produit associé %s n\'existe pas',
                                    (string) $accessory->reference
                                )
                            );
                            $webServiceDiva->call();
                            continue;
                        }
                        Db::getInstance()->execute(sprintf(
                            'INSERT INTO %saccessory (id_product_1, id_product_2) VALUES (%d, %d)',
                            _DB_PREFIX_, $product->id, (int) $product_2
                        ));
                    }
                }

                if (count($productCategories) > 0  && $product->id) {
                    $product->updateCategories($productCategories);
                }
            }

            Search::indexation(false);

        }
    }

    /**
     * @param $productId
     * @return array
     */
    private function getAllproductAttributes($productId)
    {
        $attributes = array();

        $sql = sprintf(
            "SELECT pac.id_product_attribute FROM %sproduct_attribute_combination pac
                            INNER JOIN %sproduct_attribute pa using (id_product_attribute)
                            WHERE pa.id_product = %d",
            _DB_PREFIX_,
            _DB_PREFIX_,
            $productId
        );

        $result = Db::getInstance()->executeS($sql);
        foreach ($result as $attribute) {
            $attributes[] = $attribute['id_product_attribute'];
        }

        return $attributes;
    }

    /**
     * @param $attribute
     * @param $productId
     * @return false|string|null
     */
    private function productAttributeExists($attribute, $productId)
    {
        $sql = sprintf(
            "SELECT pac.id_product_attribute FROM %sattribute_lang al
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
     * @param $productId
     * @return array
     */
    private function getAllproductFeatures($productId)
    {
        $features = array();

        $sql = sprintf(
            "SELECT fp.id_feature FROM %sfeature_product fp WHERE fp.id_product = %d",
            _DB_PREFIX_,
            $productId
        );

        $result = Db::getInstance()->executeS($sql);
        foreach ($result as $feature) {
            $features[] = $feature['id_feature'];
        }

        return $features;
    }

    /**
     * @param $productId
     * @return array
     */
    private function getAllproductCorrespondences($productId)
    {
        $correspondences = array();

        $sql = sprintf(
            "SELECT pc.id_product_correspondence FROM %sproduct_correspondence pc WHERE pc.id_product = %d",
            _DB_PREFIX_,
            $productId
        );

        $result = Db::getInstance()->executeS($sql);
        foreach ($result as $correspondence) {
            $correspondences[] = $correspondence['id_product_correspondence'];
        }

        return $correspondences;
    }

    /**
     * @param $correspondence
     * @param $productId
     * @return false|string|null
     */
    private function productCorrespondenceExists($correspondence, $productId)
    {
        $sql = sprintf(
            "SELECT pc.id_product_correspondence FROM %sproduct_correspondence pc WHERE pc.name = '%s' AND pc.value = '%s' AND pc.id_product = %d",
            _DB_PREFIX_,
            (string) $correspondence->name,
            (string) $correspondence->value,
            $productId
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
