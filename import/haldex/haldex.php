<?php
set_time_limit(0);
ini_set('memory_limit', '-1');

// Include the library
include('../simple_html_dom.php');

header('Content-Type: text/html; charset=utf-8');

$fp = fopen("haldex.csv", 'w+');

if (($handle = fopen("references.csv", "r")) !== false) {

    $array_header = ['Reference', 'Trouve', 'Lien', 'Libelle', 'Statut', 'Ligne de produit', 'Nb images'];
    $array_line = [];

    while (($data = fgetcsv($handle, 1000, ";")) !== false) {

        $reference = trim($data[0]);
        echo $reference."\n";

        $doc = new \DOMDocument();
        @$doc->loadHTML(file_get_html('https://www.haldex.com/fr/europe/search/?q='.$reference, false, null, -1, -1, true, true));

        $xpath = new \DOMXPath($doc);
        $link = null;

        $array_line[$reference][0] = $reference;
        $array_line[$reference][1] = 'Non';
        $array_line[$reference][2] = '';
        $array_line[$reference][3] = '';
        $array_line[$reference][4] = '';
        $array_line[$reference][5] = '';
        $array_line[$reference][6] = 0;

        if ($xpath->query('//body//div[@id="products"]//div[@class="name"]')->length > 0) {
            $array_line[$reference][1] = 'Oui';

            //Lien
            foreach ($xpath->query('//body//div[@id="products"]//div[@class="part-number"]/a/@href[contains(.,"'.strtolower($reference).'")]') as $node) {
                $link = 'https://www.haldex.com'.$node->nodeValue;
                $array_line[$reference][2] = $link;
                break;
            }

            $doc = new \DOMDocument();
            @$doc->loadHTML(file_get_html($link));
            $xpath = new \DOMXPath($doc);

            //Libelle
            foreach ($xpath->query('//body//div[@id="product-page"]//h1') as $node) {
                $array_line[$reference][3] = utf8_decode($node->nodeValue);
            }

            //Statut
            foreach ($xpath->query('//body//div[@id="product-page"]//div[@class="part-status"]') as $node) {
                $array_line[$reference][4] = trim(str_replace('statut', '', utf8_decode($node->nodeValue)));
            }

            //Ligne de produit
            foreach ($xpath->query('//body//div[@id="product-page"]//div[@class="part-line"]') as $node) {
                $array_line[$reference][5] = trim(str_replace('Ligne de produit', '', utf8_decode($node->nodeValue)));
            }

            //Images
            if (!is_dir("images")) {
                mkdir("images", 0777);
            }
            $i = 0;

            foreach ($xpath->query('//body//div[@id="preview"]/div[contains(@class,"image-preview")]/div[@class="image"]/@data-image-url') as $node) {
                if (!$node->nodeValue) {
                    continue;
                }
                $i++;
                $array_line[$reference][6] = $array_line[$reference][6] + 1;
                $extension = substr($node->nodeValue, strrpos($node->nodeValue, '.') + 1);
                if (!file_exists("images/ref_".$reference.'_'.$i.'.'.$extension)) {
                    download_image("https://www.haldex.com/".$node->nodeValue, "images/ref_".$reference.'_'.$i.'.'.$extension);
                }
            }

            //Données techniques
            if ($xpath->query('(//div[@class="product-description"])[1]/ul')->length > 0) {
                foreach ($xpath->query('(//div[@class="product-description"])[1]/ul/li') as $node) {
                    preg_match('/(.*):(.*)/', $node->nodeValue, $matches);
                    if (!$key = array_search(trim(utf8_decode($matches[1])), $array_header)) {
                        array_push($array_header, trim(utf8_decode($matches[1])));
                        $array_line[$reference][count($array_header) - 1] = trim(utf8_decode($matches[2]));
                    } else {
                        $array_line[$reference][$key] = trim(utf8_decode($matches[2]));
                    }
                }
                for ($i = 0; $i < count($array_line[$reference]); $i++)
                {
                    if (!isset($array_line[$reference][$i])) {
                        $array_line[$reference][$i] = '';
                    }
                }
                ksort($array_line[$reference]);
            }

        }

    }

}

echo "**************************************************************************************************************\n";
echo "*************************************** Ecriture fichier *****************************************************\n";
echo "**************************************************************************************************************\n";

fputcsv($fp, $array_header, ';');
foreach($array_line as $ligne) {
    fputcsv($fp, $ligne, ';');
}

// fermeture du fichier csv
fclose($fp);

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
