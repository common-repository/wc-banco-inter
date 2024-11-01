<?php

add_action('http_api_curl', function( $handle ){
    // $setings = WC_Banco_Inter::setingsApi();
    // $CCInter = $setings["settings"][0]['CCInter'];
    // (file_exists(getcwd() .'/../'.$setings["settings"][0]['PathCRT'])) ? $codificado = getcwd() .'/../'.$setings["settings"][0]['PathCRT'] : $codificado = $setings["settings"][0]['PathCRT'];
    // (file_exists(getcwd() .'/../'.$setings["settings"][0]['PathKey'])) ? $decodificado = getcwd() .'/../'.$setings["settings"][0]['PathKey'] : $decodificado = $setings["settings"][0]['PathKey'];

    $upload_dir = wp_upload_dir();
    $target_dir = $upload_dir['basedir'] . '/diletec/';

    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    $codificado = $target_dir . 'inter.crt';
    $decodificado = $target_dir . 'inter.key';

    curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);

    curl_setopt($handle, CURLOPT_SSLCERT, $codificado);
    curl_setopt($handle, CURLOPT_SSLKEY, $decodificado);
    curl_setopt($handle, CURLOPT_CAINFO, $codificado);
}, 10);
