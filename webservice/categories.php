<?php
require(dirname(__FILE__).'/../config/config.inc.php');

define('DEBUG', true);
define('PS_SHOP_PATH', Context::getContext()->shop->getBaseURL(true));
define('PS_WS_AUTH_KEY', 'CWI4IUFYCHYBZA7M96BQJPVX17KE84K1');
require_once('./PSWebServiceLibrary.php');

try {
    $webService = new PrestaShopWebservice(PS_SHOP_PATH, PS_WS_AUTH_KEY, DEBUG);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://interface59.ath.cx:8081/dl_pdf/tables/familles.xml');
    curl_setopt($ch, CURLOPT_FAILONERROR, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $xml = curl_exec($ch);
    curl_close($ch);
    $categories = simplexml_load_string($xml,'SimpleXMLElement', LIBXML_NOCDATA);

    foreach ($categories as $category) {

        $sql = sprintf(
            'SELECT cl.id_category FROM %scategory_lang cl WHERE cl.id_lang = %s AND cl.name = "%s"',
            _DB_PREFIX_,
            (int)$context->language->id,
            $category->name
        );
        $category_id = Db::getInstance()->getValue($sql);

        if ($category_id) {
            $xml = $webService->get(array('url' => PS_SHOP_PATH.'api/categories/'.$category_id));
        } else {
            $xml = $webService->get(array('url' => PS_SHOP_PATH.'api/categories?schema=blank'));
        }
        $resources = $xml->category->children();

        unset($resources->level_depth);
        unset($resources->nb_products_recursive);

        $resources->active = $category->active;
        $resources->id_parent = $category->parent;

        for($i = 0; $i < count($resources->name->language); $i++) {
            $resources->name->language[$i] = $category->name;
            $resources->link_rewrite->language[$i] = strtolower($category->name);
            $resources->description->language[$i] = $category->description;
        }

        $opt = array('resource' => 'categories');
        if ($category_id) {
            $resources->id = $category_id;
            $opt['putXml'] = $xml->asXML();
            $opt['id'] = $category_id;
//            $xml = $webService->edit($opt);
        } else {
            $opt['postXml'] = $xml->asXML();
//            $xml = $webService->add($opt);
        }

    }

}
catch (PrestaShopWebserviceException $ex) {
    // Shows a message related to the error
    echo 'Other error: <br />' . $ex->getMessage();
}