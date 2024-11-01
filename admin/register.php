<?php

function wc_banco_inter_sub_registro(){

    $link = "https://cp.diletec.com.br/produto/banco-inter-para-woocommerce-pro-vitalicio-e-ilimitado?utm_source=wordpress&utm_medium=plugin&utm_campaign=banco%20inter&utm_content=".$_SERVER['HTTP_HOST'];
    $dados = banco_inter_license_r();
    if(isset($dados->status) AND $dados->status == 'error'){
        echo '<div class="wrap"><h2>Registro</h2>';
        echo '<div class="banner">
            <a href='.$link.'>
                <img src="https://d17lbu6bbzbdc8.cloudfront.net/wp-content/uploads/2023/08/11153956/diletec-ads-descktop.webp" alt="Banner WooCommerce Banco Inter">
            </a';
        echo '</div>';
        echo '<h3>Versão FREE Limitada! Essa versão não faz a baixa automática.</h3>';
        echo '<p>Para ter acesso a todas as funcionalidades do plugin, adquira a versão PRO.</p>';
        echo '<p>Para adquirir a versão PRO, clique no botão abaixo:</p>';
        echo '<a href='.$link.' class="button-primary">Adquirir Versão PRO</a>';
    }else{
        _e( '<div class="wrap"><h2>Registro</h2></div>
        <table class="wc_status_table widefat wrap" cellspacing="0">
        <thead>
            <tr>
                <th colspan="3" data-export-label="WordPress Environment"><h2>Registro de Licença</h2></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Key:</td>
                <td>');
                $dados = banco_inter_license_r()->data;
                printf($dados->keygen);
                _e( '</td>
            </tr>
            <tr>
                <td>Data de Validade da Licença:</td>
                <td>');
                    $dados = banco_inter_license_r()->data;
                    _e( date('d/m/Y H:i:s', ($dados->limitedate)) );
                    _e( '
                    <code style="opacity : 0">
                        C:\xampp\htdocs\vds\wp-content\plugins\woocommerce\packages\woocommerce-blocks/
                    </code>
                </td>
            </tr>
            <tr>
                <td>Data de Criação da Licença:</td>
                <td>');
                $dados = banco_inter_license_r()->data;
                _e( date('d/m/Y', strtotime($dados->firstdate)) );
                _e( '</td>
            </tr>
            <tr>
                <td>Última Atualização:</td>
                <td>'.date("d/m/Y H:i").'</td>
            </tr>
            <tr>
                <td>Renove a Licença:</td>
                <td><a href='.$link.'>Aqui<a></td>
            </tr>
        </tbody>
        </table>');
    }
}

function banco_inter_license_r(){
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
	return $tuData;
}