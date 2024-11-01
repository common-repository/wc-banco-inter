<?php
function banco_inter_license(){
    /***/
    $host = $_SERVER['HTTP_HOST'];
    if(file_exists(plugin_dir_path(__DIR__).'protected/license/license.txt')){
        $tuData = banco_inter_licenseVerify();
    }else{
        $prodId = 5;
        $dominio = $_SERVER['HTTP_HOST'];
        $user = wp_get_current_user();
        $email = $user->user_email;
        $tipo = 'free';
        $token = '';
        $url = "https://cp.diletec.com.br/ecommerce/licenca?token=$token&dominio=$dominio&idproduto=$prodId&email=$email&tipo=$tipo";
        $response = wp_remote_get($url);
        $tuData = json_decode($response['body']);
        if(isset($tuData->status)){
            try{
                $archive = fopen(plugin_dir_path(__DIR__).'protected/license/license.txt', 'w');
                fwrite($archive, $tuData->status);
                fclose($archive);
            }catch (Exception $e) {

            }
        }
    }
	return $tuData;
}
function banco_inter_licenseVerify(){
    $key = file(plugin_dir_path(__DIR__).'protected/license/license.txt');
    $kg = htmlspecialchars($key[0]);
    $prodId = 5;
    $dominio = $_SERVER['HTTP_HOST'];
    $user = wp_get_current_user();
    $email = $user->user_email;
    $tipo = 'free';
    $token = '';
    $url = "https://cp.diletec.com.br/ecommerce/licenca?token=$token&dominio=$dominio&idproduto=$prodId&email=$email&tipo=$tipo";
    $response = wp_remote_get($url);
    $tuData = json_decode($response['body']);
    if(isset($tuData->status) AND $tuData->status == 'error'){
        $archive = fopen(plugin_dir_path(__DIR__).'protected/license/validate.txt', 'w');
        fwrite($archive, 'false');
        fclose($archive);
    }elseif($tuData->message === 'Registration expired.'){
        $archive = fopen(plugin_dir_path(__DIR__).'protected/license/validate.txt', 'w');
        fwrite($archive, 'false');
        fclose($archive);
    }else{
        $archive = fopen(plugin_dir_path(__DIR__).'protected/license/validate.txt', 'w');
        fwrite($archive, 'true');
        fclose($archive);
    }
    return $tuData;
}
function banco_inter_lv(){
    if(file_exists(plugin_dir_path(__DIR__).'protected/license/validate.txt'))
        return file(plugin_dir_path(__DIR__).'protected/license/validate.txt');
    else
    return [0 => false];
}