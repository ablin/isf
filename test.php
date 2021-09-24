<?php
//file_put_contents("articles.txt", file_get_contents("http://interface59.ath.cx:8081/documents/ARTICLES_XML/haldex_articles.xml"));
$url = "http://interface59.ath.cx:8081/documents/FIC_ARTICLES/DOCUMENTS\/Manuel de réparation_Y062522_EN.pdf";
$path = substr($url, 0, strrpos($url, '/') + 1);
file_put_contents("pj.pdf", file_get_contents($path.rawurlencode(basename($url))));

