<?php

#!/usr/bin/env php
header('Content-Type: application/json');

function parser($output)
{
    echo json_encode($output, JSON_PRETTY_PRINT);
}

$file = null;
$items = [];
$params = count($argv);

if ($params == 2)
{
    $file = array_pop($argv);
} else {
    echo "Укажите файл для обработки!\r\n";
    die();
}

if ($file)
{
    $file = trim($file);
    $file = strip_tags($file);
    $file = htmlspecialchars($file);
    $fileArray = file($file);

    $views = count($fileArray);
    $traffic = 0;
    $links = [];
    $codes = [];

    $patterns = [
        "/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/",
        "/(\s|\-)\s\-\s/",
        "/\[\d{1,2}\/\w{1,9}\/\d{1,4}\:\d{1,2}\:\d{1,2}\:\d{1,2}\s(\+|\-)\d{4}\]/",
        "/\"\w{1,6}\s/",
        "/HTTP\/\d{1}\.\d{1}\"\s/",
        "/\s\-\s\d{1}/"
    ];

    $crawlers = [];
    $crawlers_options = ['Yandexbot', 'Googlebot', 'Baidubot', 'Bingbot'];
    $crawlers_formalize = preg_grep("/Googlebot|Yandexbot|Baidubot|Bingbot/",$fileArray);

    foreach ($crawlers_options as $type) $crawlers[$type] = 0;

    foreach ($crawlers_formalize as $crawler) {
        foreach ($crawlers_options as $crawler_option) {
            if (preg_match("/$crawler_option/", $crawler)) {
                $crawlers[$crawler_option]++;
            }
        }
    }

    for ($i=0;$i<$views;$i++) {
        $string[$i] = preg_replace($patterns, '', $fileArray[$i]);
        $string[$i] = strstr($string[$i], '"http://',true);
        $result[$i] = preg_split("/[\s,]+/",$string[$i],null,PREG_SPLIT_NO_EMPTY);
        $traffic += $result[$i][2];
        $links[$i] = $result[$i][0];
        $codes[$i] = $result[$i][1];
    }
    $links = array_unique($links);
    $links = count($links);
    $codes = array_count_values($codes);

    $result = ['views'=>$views, 'urls'=>$links,'traffic'=>$traffic,'crawlers'=>$crawlers,'statusCodes'=>$codes];

    parser($result);

}
