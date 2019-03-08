<?php
set_time_limit(0);

// Include the library
include('simple_html_dom.php');

header('Content-Type: text/html; charset=utf-8');

if (!is_dir("haldex")) {
    mkdir("haldex", 0777);
}

$fichier_csv = fopen("haldex/test.csv", 'w+');
fprintf($fichier_csv, chr(0xEF).chr(0xBB).chr(0xBF));

$array_header = ['Reference', 'Trouve', 'Lien', 'Libelle', 'Nb images'];
$array_line = [];

$bdd = new PDO('mysql:host=localhost;dbname=prestashop;charset=utf8', 'root', 'root');
$products = $bdd->query('select p.reference, pl.name from ps_product p inner join ps_product_lang pl using(id_product) where p.id_manufacturer = 82');

$nb = 0;

while ($product = $products->fetch())
{
    $nb++;

    $ref = $product["reference"];
    $name = $product["name"];

    echo "**************************************************************************************************************\n";
    echo "*********************************************".$ref."*********************************************************\n";
    echo "**************************************************************************************************************\n";


    echo $nb."\n";

    $doc = new \DOMDocument();
    @$doc->loadHTML(file_get_html('https://www.haldex.com/fr/europe/search/?q='.$ref, false, null, -1, -1, true, true));

    $xpath = new \DOMXPath($doc);
    $link = null;

    $array_line[$ref][0] = $ref;
    $array_line[$ref][1] = 'Non';
    $array_line[$ref][2] = '';
    $array_line[$ref][3] = '';
    $array_line[$ref][4] = 0;

    if ($xpath->query('//body//div[@id="products"]//div[@class="name"]')->length > 0) {
        $array_line[$ref][1] = 'Oui';

        //Lien
        foreach ($xpath->query('//body//div[@id="products"]//div[@class="name"]/a/@href') as $node) {
            $link = 'https://www.haldex.com'.$node->nodeValue;
            $array_line[$ref][2] = $link;
        }

        $doc = new \DOMDocument();
        @$doc->loadHTML(file_get_html($link));
        $xpath = new \DOMXPath($doc);

        //Libelle
        foreach ($xpath->query('//body//div[@id="product-page"]//h1') as $node) {
            $array_line[$ref][3] = $node->nodeValue;
        }

        //Image
        if (!is_dir("haldex/images")) {
            mkdir("haldex/images", 0777);
        }
        $i = 0;

        foreach ($xpath->query('//body//div[@id="preview"]/img[contains(@class, \'print-image\')]/@src') as $node) {
            $i++;
            $array_line[$ref][4] = $array_line[$ref][4] + 1;
            $extension = substr($node->nodeValue, strrpos($node->nodeValue, '.') + 1);
            if (!file_exists("haldex/images/ref_".$ref.'_'.$i.'.'.$extension)) {
                grab_image("https://www.haldex.com/".$node->nodeValue, "haldex/images/ref_".$ref.'_'.$i.'.'.$extension);
            }
        }

    }

}

echo "**************************************************************************************************************\n";
echo "*************************************** Ecriture fichier *****************************************************\n";
echo "**************************************************************************************************************\n";

fputcsv($fichier_csv, $array_header, ';');
foreach($array_line as $ligne) {
    fputcsv($fichier_csv, $ligne, ';');
}

// fermeture du fichier csv
fclose($fichier_csv);


echo "**************************************************************************************************************\n";
echo "******************************************** fonctions *******************************************************\n";
echo "**************************************************************************************************************\n";

function grab_image($url, $saveto){
    $ch = curl_init ($url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
    $raw=curl_exec($ch);
    curl_close ($ch);
    if(file_exists($saveto)){
        unlink($saveto);
    }
    $fp = fopen($saveto,'x');
    fwrite($fp, $raw);
    fclose($fp);
}

function downloadFile($url, $desti) {
    $file= file_get_contents($url);
    file_put_contents ($desti, $file);
}