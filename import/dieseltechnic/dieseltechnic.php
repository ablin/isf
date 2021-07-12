<?php
set_time_limit(0);

// Include the library
include('../simple_html_dom.php');

$fp = fopen("correspondances.csv", 'w+');

if (($handle = fopen("dieseltechnic.csv", "r")) !== false) {
    fputcsv($fp, array(utf8_decode("Référence"), utf8_decode("Trouvé ?"), "Nom", "Url", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé"), "Valeur", utf8_decode("Libellé")), ";");
    while (($data = fgetcsv($handle, 1000, ";")) !== false) {
        $reference = $data[0];
        echo $reference."\n";
        $line = array(
            0 => (string) $reference,
            1 => "Non",
        );

        $postdata = http_build_query(
            array(
                'k' => $reference,
            )
        );

        $opts = array(
            'http' => array(
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postdata
            )
        );
        $context  = stream_context_create($opts);
        $result = file_get_contents('https://partnerportal.dieseltechnic.com/modules/unitm/um_es_search/ajax/main_search.php?csrf_token=MTU3ODA0NzQ2NWVkMGNiZGMxNzg5MTAzMTJjY2MwNjNkOGQyNjllMmI5MTNiZDhhNjRYUXJTUGdqUXFYV1lkQ2VFajBXMjQ1ZU5mQnc5cHhhQw==', false, $context);

        if (preg_match("/Hits/m", $result)) {
            if (preg_match_all("/<divclass=\"suggest__tool\"><ahref=\"(.+?)\".+?replaces(.+?):".$reference."<\/p>/m", str_replace(" ", "", $result), $matches)) {
                $line[1] = "Oui";
                $url = $matches[1][0];
                $line[3] = $url;
                $doc = new \DOMDocument();
                @$doc->loadHTML(file_get_contents($url, false, $context));
                $xpath = new \DOMXPath($doc);
                $line = download_correspondances($line, $xpath, (string) $reference);
            } else {
                preg_match_all("/<divclass=\"suggest__tool\"><ahref=\"(.+?)\"/m", str_replace(" ", "", $result), $matches);
                foreach ($matches[1] as $url) {
                    $doc = new \DOMDocument();
                    @$doc->loadHTML(file_get_contents($url, false, $context));
                    $xpath = new \DOMXPath($doc);

                    $elements = $xpath->query("//table[@id=\"details-table__table--produktdetails\"]/tbody/tr/td[2]");
                    if (!is_null($elements)) {
                        foreach ($elements as $element) {
                            if ((string) str_replace(" ", "", $element->nodeValue) === (string) $reference) {
                                $line[1] = "Oui";
                                $line[3] = $url;
                                $line = download_correspondances($line, $xpath, (string) $reference);
                                break;
                            }
                        }
                    }
                }
            }
        }

        fputcsv($fp, $line, ";");
    }

    fclose($handle);
}

function download_correspondances($line, $xpath, $reference)
{
    $element = $xpath->evaluate("string(//div[@id=\"nxsZoomContainer\"]/div[@class=\"row product-detail__info-container\"]//div[@class=\"is-visible-lg\"]/h2)");
    if (!is_null($element)) {
        $line[2] = (string) utf8_decode($element);
    }

    $element = $xpath->evaluate("string(//div[@id=\"nxsZoomContainer\"]/div[@class=\"row product-detail__info-container\"]//div[@class=\"is-visible-lg\"]/p[@class=\"is-lighter\"][1])");
    if (!is_null($element)) {
        if (preg_match("/replaces(.+?):(.+)/m", str_replace(" ", "", $element), $matches)) {
            $line[count($line) + 1] = $matches[1];
            $line[count($line) + 1] = str_replace(' ', '', $matches[2]);
        }
    }

    $i = 1;
    foreach ($xpath->query('//table[@id="details-table__table--produktdetails"]/tbody/tr/td[1]') as $node) {
        $value = (string) $xpath->evaluate("string(//table[@id=\"details-table__table--produktdetails\"]/tbody/tr[".$i."]/td[2])");
        if ((string) str_replace(" ", "", $value) !== (string) $reference) {
            $line[count($line) + 1] = (string) utf8_decode($node->nodeValue);
            $line[count($line) + 1] = str_replace(' ', '', (string) $value);
        }
        $i++;
    }

    ksort($line);

    return $line;
}
