<?php
set_time_limit(0);
ini_set('memory_limit', '-1');

// Include the library
include('../simple_html_dom.php');

header('Content-Type: text/html; charset=utf-8');

$fp = fopen("knorr.csv", 'w+');
$fpFiles = fopen("knorr_fichiers.csv", 'w+');

if (($handle = fopen("references.csv", "r")) !== false) {

    $array_header = ['Reference', 'Trouve', 'Reference IAM', 'Lien', 'Libelle', 'Nb images', 'Nb Docs', 'Type', 'Statut', 'Correspondances'];
    $array_line = [];

    $array_header_files = ['Reference'];
    $array_line_files = [];

    while (($data = fgetcsv($handle, 1000, ";")) !== false) {

        $reference = trim($data[0]);
        echo $reference."\n";

        $doc = new \DOMDocument();
        @$doc->loadHTML(file_get_html('https://www.knorr-bremsesfn.biz/com/partnumbersearch.aspx?lang=fr-fr&search='.$reference, false, null, -1, -1, true, true));

        $xpath = new \DOMXPath($doc);
        $link = null;

        $array_line[$reference][0] = $reference;
        $array_line_files[$reference][0] = $reference;
        $array_line[$reference][1] = 'Non';
        $array_line[$reference][2] = '';
        $array_line[$reference][3] = '';
        $array_line[$reference][4] = '';
        $array_line[$reference][5] = 0;
        $array_line[$reference][6] = 0;
        $array_line[$reference][7] = '';
        $array_line[$reference][8] = '';
        $array_line[$reference][9] = '';

        //Libelle & IAM
        if ($xpath->query('//body//table[@class="dataView"]')->length > 0) {
            $array_line[$reference][1] = 'Oui';
            
            //Libelle
            foreach ($xpath->query('//body//table[@class="dataView"]//tr[2]/td[4]/a') as $node) {
                if ($node->nodeValue) {
                    $array_line[$reference][4] = utf8_decode(utf8_decode($node->nodeValue));
                }
            }

            //IAM
            foreach ($xpath->query('//body//table[@class="dataView"]//tr[2]/td[6]/a') as $node) {
                if ($node->nodeValue) {
                    $array_line[$reference][2] = utf8_decode(utf8_decode($node->nodeValue));
                    if ($xpath->query('//body//table[@class="dataView"]//tr[2]/td[6]/a[@class="disableLink"]')->length == 0) {
                        foreach ($xpath->query('//body//table[@class="dataView"]//tr[2]/td[6]/a/@href') as $node) {
                            if (strpos($node->nodeValue, "lang=fr-fr")) {
                                $link = 'https://www.knorr-bremsesfn.biz/com/'.$node->nodeValue;
                            } else {
                                $link = 'https://www.knorr-bremsesfn.biz/com/'.$node->nodeValue.'&lang=fr-fr';
                            }
                        }
                    }
                }
            }

            //Correspondances
            foreach ($xpath->query('//body//table[@class="dataView"]//tr/td[2]') as $node) {
                if ($node->nodeValue && $node->nodeValue != $reference) {
                    $array_line[$reference][9] = $array_line[$reference][9] . utf8_decode(utf8_decode($node->nodeValue)) . "|";
                }
            }

            //Lien
            if (!$link) {
                if ($xpath->query('//body//table[@class="dataView"]//tr[2]/td[2]/a[@class="disableLink"]')->length == 0) {
                    foreach ($xpath->query('//body//table[@class="dataView"]//tr[2]/td[2]/a/@href') as $node) {
                        if (strpos($node->nodeValue, "lang=fr-fr")) {
                            $link = 'https://www.knorr-bremsesfn.biz/com/'.$node->nodeValue;
                        } else {
                            $link = 'https://www.knorr-bremsesfn.biz/com/'.$node->nodeValue.'&lang=fr-fr';
                        }
                    }
                }
            }

            if ($link) {
                $array_line[$reference][3] = $link;
                $doc = new \DOMDocument();
                @$doc->loadHTML(file_get_html($link));
                $xpath = new \DOMXPath($doc);

                //Type
                foreach ($xpath->query('//body//div[@class="productInformation"]//table//tr[1]//td[2]') as $node) {
                    $array_line[$reference][7] = utf8_decode(utf8_decode($node->nodeValue));
                }

                //Description
                foreach ($xpath->query('//body//div[@class="productInformation"]//table//tr[2]//td[2]') as $node) {
                    $array_line[$reference][8] = utf8_decode(utf8_decode($node->nodeValue));
                }

                //Statut
                foreach ($xpath->query('//body//div[@class="productInformation"]//table//tr[3]//td[2]') as $node) {
                    $array_line[$reference][8] = utf8_decode(utf8_decode($node->nodeValue));
                }

                //Données techniques
                if ($xpath->query('//table[@class="mediaArea"]//tr[2]/td[1]/ul')->length > 0) {
                    $index = 0;
                    foreach ($xpath->query('//table[@class="mediaArea"]//tr[2]/td[1]/ul/li/text()') as $node) {
                        $index++;
                        if (!$key = array_search(trim(utf8_decode(utf8_decode($node->nodeValue))), $array_header)) {
                            array_push($array_header, trim(utf8_decode(utf8_decode($node->nodeValue))));
                            foreach ($xpath->query('//table[@class="mediaArea"]//tr[2]/td[1]/ul/li['.$index.']/span') as $node) {
                                $array_line[$reference][count($array_header) - 1] = trim(utf8_decode(utf8_decode($node->nodeValue)));
                            }
                        } else {
                            foreach ($xpath->query('//table[@class="mediaArea"]//tr[2]/td[1]/ul/li['.$index.']/span') as $node) {
                                $array_line[$reference][$key] = trim(utf8_decode(utf8_decode($node->nodeValue)));
                            }
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

            //Documents
            if (!is_dir("doc")) {
                mkdir("doc", 0777);
            }

            $i = 0;
            foreach ($xpath->query('//body//table[@id="ctl00_cphContent_MediaArea"]//tr[2]//td[2]//ul//li//a/@href') as $node) {
                $i++;
                $array_line[$reference][6] = $array_line[$reference][6] + 1;
                $filepath = $node->nodeValue;
                $extension = substr($node->nodeValue, strrpos($node->nodeValue, '.') + 1);

                foreach ($xpath->query('//body//table[@id="ctl00_cphContent_MediaArea"]//tr[2]//td[2]//ul//li['.$i.']//a/text()') as $node) {
                    $filename = trim(utf8_decode(utf8_decode($node->nodeValue)));
                    if (!file_exists("doc/".$filename.'.'.$extension)) {
                        downloadFile($filepath, "doc/".$filename.'.'.$extension);
                    }

                    $array_line_files[$reference][$i] = $filename.'.'.$extension;
                }
            }

            //Images
            if (!is_dir("images")) {
                mkdir("images", 0777);
            }

            $i = 0;
            foreach ($xpath->query('//body//table[@id="ctl00_cphContent_MediaArea"]//tr[2]//td[3]/img/@src') as $node) {
                $i++;
                $array_line[$reference][5] = $array_line[$reference][5] + 1;
                $extension = substr($node->nodeValue, strrpos($node->nodeValue, '.') + 1);
                if (!file_exists("images/ref_".$reference.'_'.$i.'.'.$extension) && substr($node->nodeValue, 0, 4) == "http") {
                    download_image($node->nodeValue, "images/ref_".$reference.'_'.$i.'.'.$extension);
                }
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

fputcsv($fpFiles, $array_header_files, ';');
foreach($array_line_files as $ligne_files) {
    fputcsv($fpFiles, $ligne_files, ';');
}

// fermeture du fichier csv
fclose($fp);
fclose($fpFiles);

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
