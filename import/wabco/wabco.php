<?php
set_time_limit(0);
ini_set('memory_limit', '-1');

// Include the library
include('../simple_html_dom.php');

header('Content-Type: text/html; charset=utf-8');

$fp = fopen("wabco.csv", 'w+');
$fpFiles = fopen("wabco_fichiers.csv", 'w+');
$fpCorrespondances = fopen("wabco_correspondances.csv", 'w+');

if (($handle = fopen("references.csv", "r")) !== false) {

    $array_header = ['Reference', 'Trouve', 'Libelle', 'Nb images', 'Nb Docs'];
    $array_line = [];

    $array_header_files = ['Reference'];
    $array_line_files = [];

    $array_header_correspondances = ['Reference'];
    $array_line_correspondances = [];

    while (($data = fgetcsv($handle, 1000, ";")) !== false) {

        $reference = trim($data[0]);
        echo $reference."\n";

        $page = @file_get_contents('https://www.wabco-customercentre.com/catalog/fr_FR/'.$reference.'?cclcl=fr_FR');
        preg_match('/extendedModel = (.+?);\n/', $page, $extendedModel);
        preg_match('/CCRZ.detailData.jsonProductData = (.+?);\n/', $page, $productData);

        if ($extendedModel && $productData) {

            $extendedModel = json_decode($extendedModel[1]);
            $productData = json_decode($productData[1]);

            $array_line[$reference][0] = $reference;
            $array_line_files[$reference][0] = $reference;
            $array_line_correspondances[$reference][0] = $reference;
            $array_line[$reference][1] = 'Non';
            $array_line[$reference][2] = '';
            $array_line[$reference][3] = 0;
            $array_line[$reference][4] = 0;

            if (count($extendedModel) > 0) {

                $array_line[$reference][1] = 'Oui';

                //Libelle
                $array_line[$reference][2] = utf8_decode($productData->product->prodBean->name);

                //Images
                if (!is_dir("images")) {
                    mkdir("images", 0777);
                }
                $i = 0;

                if (isset($productData->mediaWrappers->{'Product Image'})) {
                    foreach ($productData->mediaWrappers->{'Product Image'} as $image) {
                        if ($image->uri != 'https://www.wabco-customercentre.com/catalog/productImage/wabco_logo_vector_eps.png') {
                            $i++;
                            $array_line[$reference][3] = $array_line[$reference][3] + 1;
                            $extension = substr($image->uri, strrpos($image->uri, '.') + 1);
                            download_image($image->uri, "images/ref_" . $reference . '_' . $i . '.' . $extension);
                        }
                    }
                }

                //Données techniques
                if (isset($extendedModel->extProdData->techSpecWrappers)) {
                    $index = 0;
                    foreach ($extendedModel->extProdData->techSpecWrappers as $tech) {
                        if ($tech->showInSection) {
                            $index++;
                            $libelle = trim(utf8_decode($tech->Label));
                            $valeur = trim(utf8_decode($tech->Value));
                            if (!$key = array_search($libelle, $array_header)) {
                                array_push($array_header, $libelle);
                                $key = count($array_header) - 1;
                            }
                            $array_line[$reference][$key] = $valeur;
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

                //Correspondances
                $i = 0;
                foreach ($extendedModel->extProdData->crossRerefences as $correspondance) {
                    $i++;
                    $array_line_correspondances[$reference][] = trim(utf8_decode($correspondance->productName));
                    $array_line_correspondances[$reference][] = trim(utf8_decode($correspondance->partNumber));
                }

                //Documents
                if (!is_dir("doc")) {
                    mkdir("doc", 0777);
                }

                if ($extendedModel->extProdData->hasDocs) {
                    $i = 0;
                    foreach ($extendedModel->extProdData->docs as $doc) {
                        $i++;
                        $array_line[$reference][4] = $array_line[$reference][4] + 1;
                        $filename = trim(utf8_decode(basename($doc->dataForCurrentLocale->url)));
                        $name = trim(utf8_decode($doc->dataForCurrentLocale->name));

                        if (!file_exists("doc/".$filename)) {
                            downloadFile($doc->dataForCurrentLocale->url, "doc/".$filename);
                        }

                        $array_line_files[$reference][$i] = $name;
                        $i++;
                        $array_line_files[$reference][$i] = $filename;
                    }
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

fputcsv($fpCorrespondances, $array_header_correspondances, ';');
foreach($array_line_correspondances as $ligne_correspondances) {
    fputcsv($fpCorrespondances, $ligne_correspondances, ';');
}

// fermeture du fichier csv
fclose($fp);
fclose($fpFiles);
fclose($fpCorrespondances);

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
