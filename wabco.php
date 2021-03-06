<?php
set_time_limit(0);
ini_set('memory_limit', '-1');

// Include the library
include('simple_html_dom.php');

header('Content-Type: text/html; charset=utf-8');

if (!is_dir("wabco")) {
    mkdir("wabco", 0777);
}

$fichier_csv = fopen("wabco/test.csv", 'w+');
fprintf($fichier_csv, chr(0xEF).chr(0xBB).chr(0xBF));

$array_header = ['Reference', 'Libelle', 'Nb images', 'Nb Docs'];
$array_line = [];

$bdd = new PDO('mysql:host=localhost;dbname=prestashop;charset=utf8', 'root', 'root');
$products = $bdd->query('select p.id_product, p.reference, pl.name from ps_category_product inner join ps_product p using(id_product) inner join ps_product_lang pl using(id_product) inner join ps_category_lang cl using(id_category) where cl.name = "WABCO";');

while ($product = $products->fetch())
{
    $id = $product["id_product"];
    $ref = $product["reference"];
    $name = $product["name"];

    echo "**************************************************************************************************************\n";
    echo "*********************************************".$ref."*********************************************************\n";
    echo "**************************************************************************************************************\n";

    $doc = new \DOMDocument();
    @$doc->loadHTML(file_get_html('http://inform.wabco-auto.com/intl/fr/inform.php?save=1&keywords='.$ref.'&category=18', false, null, -1, -1, true, true, 'ISO-8859-1'));

    $xpath = new \DOMXPath($doc);

    $array_line[$ref][0] = $ref;
    $array_line[$ref][1] = $name;
    $array_line[$ref][2] = 0;
    $array_line[$ref][3] = 0;

    //Images
    echo "Images :\n";
    if ($xpath->query('//table//p/img/@src')->length > 0) {
        $i = 0;
        foreach ($xpath->query('//table//p/img/@src') as $node) {
            $i++;
            $array_line[$ref][2] = $array_line[$ref][2] + 1;
            if (!is_dir("wabco/images")) {
                mkdir("wabco/images", 0777);
            }
            $extension = substr($node->nodeValue, strrpos($node->nodeValue, '.') + 1);
            grab_image('http://inform.wabco-auto.com/' . $node->nodeValue, "wabco/images/ref_" . $ref . '_' . $i . '.' . $extension);
        }
    } else {
        echo "Pas d'images\n";
    }

    echo "\n";

    //Données techniques page 1
    echo "Données techniques :\n";
    if ($xpath->query('//body//table//table[2]//tr/td[2]')->length > 0) {
        foreach ($xpath->query('//body//table//table[2]//tr/td[2]') as $node) {
            if (preg_match('/(.*):(.*)/', $node->nodeValue, $matches)) {
                if (!$key = array_search($matches[1], $array_header)) {
                    array_push($array_header, $matches[1]);
                    $key = count($array_header) - 1;
                }
                $array_line[$ref][$key] = $matches[2];
            }
        }

        //Pages suivantes
        foreach ($xpath->query('//p[contains(.,"Page de résultats")]/a[position()<last()]/@href') as $node) {
            @$doc->loadHTML(file_get_html('http://inform.wabco-auto.com/intl/fr/inform.php'.$node->value, false, null, -1, -1, true, true, 'ISO-8859-1'));
            $xpath = new \DOMXPath($doc);

            foreach ($xpath->query('//body//table//table[2]//tr/td[2]') as $node) {
                if (preg_match('/(.*):(.*)/', $node->nodeValue, $matches)) {
                    if (!$key = array_search(trim($matches[1]), $array_header)) {
                        array_push($array_header, trim($matches[1]));
                        $array_line[$ref][count($array_header) - 1] = $matches[2];
                    } else {
                        $array_line[$ref][$key] = trim($matches[2]);
                    }
                }
            }
        }

        for ($i = 0; $i < count($array_line[$ref]); $i++)
        {
            if (!isset($array_line[$ref][$i])) {
                $array_line[$ref][$i] = '';
            }
        }
        ksort($array_line[$ref]);

    } else {
        echo "Pas de données techniques\n";
    }

    echo "\n";

    //Plan
    @$doc->loadHTML(file_get_html('http://inform.wabco-auto.com/intl/fr/inform.php?save=1&keywords='.$ref.'&category=20', false, null, -1, -1, true, true, 'ISO-8859-1'));

    $xpath = new \DOMXPath($doc);

    echo "Plan :\n";

    if ($xpath->query('//table//table[2]//td[@class="clsWh"][2]/a[contains(.,"Page")]/@href')->length > 0) {
        $i = 0;
        foreach ($xpath->query('//table//table[2]//td[@class="clsWh"][2]/a/@href') as $node) {
            $i++;
            $array_line[$ref][3] = $array_line[$ref][3] + 1;
            if (!is_dir("wabco/doc")) {
                mkdir("wabco/doc", 0777);
            }
            $extension = substr($node->nodeValue, strrpos($node->nodeValue, '.') + 1);
            downloadFile('http://inform.wabco-auto.com/' . $node->nodeValue, "wabco/doc/ref_" . $ref . '_' . $i . '.' . $extension);
        }
    } else {
        echo "Pas de plan\n";
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


//9254229040 soit l'id 12067