<?php
require(dirname(__FILE__).'/../../config/config.inc.php');

class Import
{
    const CATEGORY = "famille";
    const PRODUCT = "produit";

    private $topMenu = null;
    private $TopMenuHasChanged = false;

    public function __construct()
    {
        $this->checkMethod();
        $this->checkRights();
        $this->doImport();
    }

    private function checkMethod()
    {
        if ($_SERVER['REQUEST_METHOD'] != "POST") {
            http_response_code(405);
            exit;
        }
    }

    private function checkRights()
    {
        $headers = getallheaders();
        if (!isset($headers["Authorization"]) || $headers["Authorization"] != "tralala") {
            http_response_code(401);
            exit;
        }
    }

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

    private function importCategory($file)
    {
        $familles = $this->load($file);
        $this->getTopMenu();

        foreach ($familles as $famille) {

            $category_has_changed = Category::categoryHasChanged((string) $famille->name, (string) $famille->date_upd);
            $categoryExists = Category::getCategory((string) $famille->name);
            $category_id = $categoryExists['id_category'];

            if (!$category_id || $category_has_changed) {
                $category = new Category();
                $category->active = 1;
                $category->is_root_category = 0;
                if ((string) $famille->parent) {
                    $categoryParent = Category::getCategory((string) $famille->parent);
                    if ($categoryParent) {
                        $category->id_parent = $categoryParent['id_category'];
                        $category->level_depth = !$category_id ? (int)$categoryParent['level_depth'] + 1 : $categoryExists['level_depth'];
                    } else {
                        continue;
                    }
                } else {
                    $category->id_parent = 2;
                    $category->level_depth = !$category_id ? 2 : $categoryExists['level_depth'];
                }
                $category->date_upd_divalto = date((string) $famille->date_upd);
                $category->name[1] = addslashes((string) $famille->name);
                $category->description[1] = addslashes((string) $famille->description);
                $category->link_rewrite[1] = Tools::link_rewrite((string) $famille->name);
                $category->meta_title[1] = (string) $famille->name;
                $category->meta_keywords[1] = (string) $famille->description;
                $category->meta_description[1] = (string) $famille->description;

                if (!$category_id) {
                    $category->date_add = date('Y-m-d H:i:s');
                    $category->position = (int)Category::getLastPosition((int)$category->id_parent, 1);
                    $category->id_shop_default = 1;
                }

                if ($category_id && $category_has_changed) {
                    $category->id = $category_id;
                    $category->id_category = $category_id;
                    $category->position = $categoryExists['position'];
                    $category->nleft = $categoryExists['nleft'];
                    $category->nright = $categoryExists['nright'];
                    $category->update();
                } else {
                    $category->add();
                    $categoryNew = Category::getCategory((string) $famille->name);
                    $category->id = $categoryNew['id_category'];
                }

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

    private function importProduct($file)
    {
        $produits = $this->load($file);

        foreach ($produits as $produit) {
            $product_has_changed = Product::productHasChanged((string) $produit->reference, (string) $produit->date_upd);
            $productExists = Product::getProductByReference((string) $produit->reference);
            $product_id = $productExists['id_product'];

            if (!$product_id || $product_has_changed) {
                $product = new Product();
                $product->name = (string) $produit->name;
                $product->description_short = addslashes((string) $produit->description_short);
                $product->description = addslashes((string) $produit->description);
                $product->link_rewrite = Tools::link_rewrite((string) $produit->name);

                $productCategories = array();
                if (isset($produit->familles)) {
                    foreach ($produit->familles->famille as $famille) {
                        $category = Category::getCategory($famille->name);
                        if ($category) {
                            $productCategories[] = $category['id_category'];
                        } else {
                            die('categorie pas touvée'); //TODO Catégorie pas trouvée donc à créer
                        }
                    }
                }

                //TODO restent déclinaisons et caractéristiques



                $product->active = (int) $produit->active;
                $product->date_upd_divalto = date((string) $produit->date_upd);

                if (!$product_id) {
                    $product->date_add = date('Y-m-d H:i:s');
                    $product->id_shop_default = 1;
                }

                if ($product_id && $product_has_changed) {
                    $product->id = $product_id;
                    //$product->update();
                } else {
                    //$product->add();
                    $product->id = Product::getProduct((string) $produit->reference);
                }

                if (count($productCategories) > 0  && $product->id) {
                    $product->updateCategories($productCategories);
                }
            }

            var_dump($product);
        }
    }

    private function getTopMenu()
    {
        $this->topMenu = Configuration::get('MOD_BLOCKTOPMENU_ITEMS') ? explode(",", Configuration::get('MOD_BLOCKTOPMENU_ITEMS')) : array();
    }

    private function updateTopMenu()
    {
        Configuration::updateValue('MOD_BLOCKTOPMENU_ITEMS', (string)implode(',', $this->topMenu));
    }

    private function load($path)
    {
        return simplexml_load_file($path);
    }

}

new Import();
