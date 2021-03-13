<?php
set_time_limit(0);
ini_set('memory_limit', '-1');

// Include the library
include('simple_html_dom.php');

header('Content-Type: text/html; charset=utf-8');

if (!is_dir("haldex")) {
    mkdir("haldex", 0777);
}

$fichier_csv = fopen("haldex/test.csv", 'w+');
fprintf($fichier_csv, chr(0xEF).chr(0xBB).chr(0xBF));

$array_header = ['Reference', 'Trouve', 'Lien', 'Libelle', 'Statut', 'Ligne de produit', 'Nb images'];
$array_line = [];

$bdd = new PDO('mysql:host=localhost;dbname=prestashop;charset=utf8', 'root', 'root');
$products = $bdd->query('select p.reference, pl.name from ps_category_product inner join ps_product p using(id_product) inner join ps_product_lang pl using(id_product) inner join ps_category_lang cl using(id_category) where cl.name = "HALDEX";');

$nb = 0;

while ($product = $products->fetch())
{
    $nb++;

    $ref = (string) $product["reference"];
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
    $array_line[$ref][4] = '';
    $array_line[$ref][5] = '';
    $array_line[$ref][6] = 0;

    if ($xpath->query('//body//div[@id="products"]//div[@class="name"]')->length > 0) {
        $array_line[$ref][1] = 'Oui';

        //Lien
        foreach ($xpath->query('//body//div[@id="products"]//div[@class="part-number"]/a/@href[contains(.,"'.strtolower($ref).'")]') as $node) {
            $link = 'https://www.haldex.com'.$node->nodeValue;
            $array_line[$ref][2] = $link;
            break;
        }

        $doc = new \DOMDocument();
        @$doc->loadHTML(file_get_html($link));
        $xpath = new \DOMXPath($doc);

        //Libelle
        foreach ($xpath->query('//body//div[@id="product-page"]//h1') as $node) {
            $array_line[$ref][3] = $node->nodeValue;
        }

        //Statut
        foreach ($xpath->query('//body//div[@id="product-page"]//div[@class="part-status"]') as $node) {
            $array_line[$ref][4] = trim(str_replace('statut', '', $node->nodeValue));
        }

        //Ligne de produit
        foreach ($xpath->query('//body//div[@id="product-page"]//div[@class="part-line"]') as $node) {
            $array_line[$ref][5] = trim(str_replace('Ligne de produit', '', $node->nodeValue));
        }

        //Images
        if (!is_dir("haldex/images")) {
            mkdir("haldex/images", 0777);
        }
        $i = 0;

        foreach ($xpath->query('//body//div[@id="preview"]/div[contains(@class,"image-preview")]/div[@class="image"]/@data-image-url') as $node) {
            if (!$node->nodeValue) {
                continue;
            }
            $i++;
            $array_line[$ref][6] = $array_line[$ref][6] + 1;
            $extension = substr($node->nodeValue, strrpos($node->nodeValue, '.') + 1);
            if (!file_exists("haldex/images/ref_".$ref.'_'.$i.'.'.$extension)) {
                download_image("https://www.haldex.com/".$node->nodeValue, "haldex/images/ref_".$ref.'_'.$i.'.'.$extension);
            }
        }

        //Données techniques
        if ($xpath->query('(//div[@class="product-description"])[1]/ul')->length > 0) {
            foreach ($xpath->query('(//div[@class="product-description"])[1]/ul/li') as $node) {
                preg_match('/(.*):(.*)/', $node->nodeValue, $matches);
                if (!$key = array_search(trim($matches[1]), $array_header)) {
                    array_push($array_header, trim($matches[1]));
                    $array_line[$ref][count($array_header) - 1] = trim($matches[2]);
                } else {
                    $array_line[$ref][$key] = trim($matches[2]);
                }
            }
            for ($i = 0; $i < count($array_line[$ref]); $i++)
            {
                if (!isset($array_line[$ref][$i])) {
                    $array_line[$ref][$i] = '';
                }
            }
            ksort($array_line[$ref]);
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

function download_image($image_url, $image_file){
    $fp = fopen ($image_file, 'w+');              // open file handle

    $ch = curl_init($image_url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // enable if you want
    curl_setopt($ch, CURLOPT_FILE, $fp);          // output to file
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 1000);      // some large value to allow curl to run for a long time
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    // curl_setopt($ch, CURLOPT_VERBOSE, true);   // Enable this line to see debug prints
    curl_exec($ch);

    curl_close($ch);                              // closing curl handle
    fclose($fp);                                  // closing file handle
}

function downloadFile($url, $desti) {
    $file= file_get_contents($url);
    file_put_contents ($desti, $file);
}