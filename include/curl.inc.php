<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
function get_latest() {
    global $cfg;

    $url = 'http://192.168.2.10:9117/api/v2.0/indexers/newpct/results/torznab/api?apikey=k1ryk9av87gxjk9e9sj7kpka5mommaxc&t=search&cat=&q=';
    return curl_get($url);

    //return $response;
}

function curl_get($url) {
    global $cfg;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    $xml = simplexml_load_string($response);
    $json = json_encode($xml);
    $array = json_decode($json, TRUE);
    //echo "*" . var_dump($array);
    return $array;
}

function curl_get_jackett($url) {
    global $cfg;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    //FIXME tornzb:attr lost by simplexml/json_encode this is a temporary/fast fix

    $response = preg_replace('/:attr/', '', $response);
    //clean empty description
    $response = preg_replace('/<description \/>/', '', $response);

    $xml = @simplexml_load_string($response);

    if (!$xml) {
        return false;
    }
    $json = json_encode($xml);

    $array = json_decode($json, TRUE);

    return $array;
}

function curl_get_json($url) {
    global $cfg;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);

    curl_close($ch);

    $array = json_decode($response, TRUE);
    //var_dump($array);

    return $array;
}
