<?php
set_time_limit(0);
ini_set('memory_limit', '-1');

// Include the library
include('simple_html_dom.php');

header('Content-Type: text/html; charset=utf-8');

if (!is_dir("knorr")) {
    mkdir("knorr", 0777);
}

$fichier_csv = fopen("knorr/test.csv", 'w+');
fprintf($fichier_csv, chr(0xEF).chr(0xBB).chr(0xBF));

$array_header = ['Reference', 'Trouve', 'Reference IAM', 'Lien', 'Libelle', 'Nb images', 'Nb Docs', 'Type', 'Description', 'Statut', 'Correspondances'];
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
    $array_line[$ref][10] = '';

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

        //Correspondances
        foreach ($xpath->query('//body//table[@class="dataView"]//tr/td[2]') as $node) {
            if ($node->nodeValue && $node->nodeValue != $ref) {
                $array_line[$ref][10] = $array_line[$ref][10] . $node->nodeValue . "|";
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

            //Données techniques
            if ($xpath->query('//table[@class="mediaArea"]//tr[2]/td[1]/ul')->length > 0) {
                $index = 0;
                foreach ($xpath->query('//table[@class="mediaArea"]//tr[2]/td[1]/ul/li/text()') as $node) {
                    $index++;
                    if (!$key = array_search(trim(utf8_decode($node->nodeValue)), $array_header)) {
                        array_push($array_header, trim(utf8_decode($node->nodeValue)));
                        foreach ($xpath->query('//table[@class="mediaArea"]//tr[2]/td[1]/ul/li['.$index.']/span') as $node) {
                            $array_line[$ref][count($array_header) - 1] = trim(utf8_decode($node->nodeValue));
                        }
                    } else {
                        foreach ($xpath->query('//table[@class="mediaArea"]//tr[2]/td[1]/ul/li['.$index.']/span') as $node) {
                            $array_line[$ref][$key] = trim(utf8_decode($node->nodeValue));
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
            if (!file_exists("knorr/images/ref_".$ref.'_'.$i.'.'.$extension) && substr($node->nodeValue, 0, 4) == "http") {
                download_image($node->nodeValue, "knorr/images/ref_".$ref.'_'.$i.'.'.$extension);
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