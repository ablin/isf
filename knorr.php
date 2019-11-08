<?php
set_time_limit(0);

// Include the library
include('simple_html_dom.php');

header('Content-Type: text/html; charset=utf-8');

if (!is_dir("knorr")) {
    mkdir("knorr", 0777);
}

$fichier_csv = fopen("knorr/test.csv", 'w+');
fprintf($fichier_csv, chr(0xEF).chr(0xBB).chr(0xBF));

$array_header = ['Reference', 'Trouve', 'Reference IAM', 'Lien', 'Libelle', 'Nb images', 'Nb Docs', 'Type', 'Description', 'Statut'];
$array_line = [];

$bdd = new PDO('mysql:host=localhost;dbname=prestashop;charset=utf8', 'root', 'root');
$products = $bdd->query('select p.reference, pl.name from ps_category_product inner join ps_product p using(id_product) inner join ps_product_lang pl using(id_product) inner join ps_category_lang cl using(id_category) where cl.name = "KNORR";');

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
    @$doc->loadHTML(file_get_html('https://www.knorr-bremsesfn.biz/com/partnumbersearch.aspx?lang=fr-fr&search='.$ref, false, null, -1, -1, true, true));

    $xpath = new \DOMXPath($doc);
    $link = null;

    $array_line[$ref][0] = $ref;
    $array_line[$ref][1] = 'Non';
    $array_line[$ref][2] = '';
    $array_line[$ref][3] = '';
    $array_line[$ref][4] = $name;
    $array_line[$ref][5] = 0;
    $array_line[$ref][6] = 0;
    $array_line[$ref][7] = '';
    $array_line[$ref][8] = '';
    $array_line[$ref][9] = '';

    //Description & IAM
    echo "Description & IAM :\n";
    if ($xpath->query('//body//table[@class="dataView"]')->length > 0) {
        $array_line[$ref][1] = 'Oui';
        
        //Description
        foreach ($xpath->query('//body//table[@class="dataView"]//tr[2]/td[4]/a') as $node) {
            if ($node->nodeValue) {
                $array_line[$ref][8] = $node->nodeValue;
            }
        }

        //IAM
        foreach ($xpath->query('//body//table[@class="dataView"]//tr[2]/td[6]/a') as $node) {
            if ($node->nodeValue) {
                $array_line[$ref][2] = $node->nodeValue;
                if ($xpath->query('//body//table[@class="dataView"]//tr[2]/td[6]/a[@class="disableLink"]')->length == 0) {
                    foreach ($xpath->query('//body//table[@class="dataView"]//tr[2]/td[6]/a/@href') as $node) {
                        $link = 'https://www.knorr-bremsesfn.biz/com/'.$node->nodeValue.'&lang=fr-fr';
                    }
                }
            }
        }

        //Lien
        if (!$link) {
            if ($xpath->query('//body//table[@class="dataView"]//tr[2]/td[2]/a[@class="disableLink"]')->length == 0) {
                foreach ($xpath->query('//body//table[@class="dataView"]//tr[2]/td[2]/a/@href') as $node) {
                    $link = 'https://www.knorr-bremsesfn.biz/com/'.$node->nodeValue.'&lang=fr-fr';
                }
            }
        }

        if ($link) {
            $array_line[$ref][3] = $link;
            $doc = new \DOMDocument();
            @$doc->loadHTML(file_get_html($link));
            $xpath = new \DOMXPath($doc);

            //Type
            foreach ($xpath->query('//body//div[@class="productInformation"]//table//tr[1]//td[2]') as $node) {
                $array_line[$ref][7] = $node->nodeValue;
            }

            //Description
            foreach ($xpath->query('//body//div[@class="productInformation"]//table//tr[2]//td[2]') as $node) {
                $array_line[$ref][8] = $node->nodeValue;
            }

            //Statut
            foreach ($xpath->query('//body//div[@class="productInformation"]//table//tr[3]//td[2]') as $node) {
                $array_line[$ref][9] = $node->nodeValue;
            }
        }

        //Documents
        echo "Documents :\n";
        if (!is_dir("knorr/doc")) {
            mkdir("knorr/doc", 0777);
        }

        $i = 0;
        foreach ($xpath->query('//body//table[@id="ctl00_cphContent_MediaArea"]//tr[2]//td[2]//ul//li//a/@href') as $node) {
            $i++;
            $array_line[$ref][6] = $array_line[$ref][6] + 1;
            $extension = substr($node->nodeValue, strrpos($node->nodeValue, '.') + 1);
            if (!file_exists("knorr/doc/ref_".$ref.'_'.$i.'.'.$extension)) {
                downloadFile($node->nodeValue, "knorr/doc/ref_".$ref.'_'.$i.'.'.$extension);
            }
        }

        echo "\n";

        //Images
        echo "Images :\n";
        $i = 0;
        if (!is_dir("knorr/images")) {
            mkdir("knorr/images", 0777);
        }

        $i = 0;
        foreach ($xpath->query('//body//table[@id="ctl00_cphContent_MediaArea"]//tr[2]//td[3]/img/@src') as $node) {
            $i++;
            $array_line[$ref][5] = $array_line[$ref][5] + 1;
            $extension = substr($node->nodeValue, strrpos($node->nodeValue, '.') + 1);
            if (!file_exists("knorr/images/ref_".$ref.'_'.$i.'.'.$extension)) {
                grab_image($node->nodeValue, "knorr/images/ref_".$ref.'_'.$i.'.'.$extension);
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