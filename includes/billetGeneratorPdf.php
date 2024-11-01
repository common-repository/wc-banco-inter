<?php
/**
 * Generate the billet in PDF (Boleto PDF)
 */
function wc_banco_inter_boleto_pdf($order){
    $method = get_post_meta( $order->get_id(), '_payment_method', true );
    if($method != 'interboleto')
        return;

    $nossonumero = get_post_meta( $order->get_id(), 'nossonumero', true );
    $token = WC_Banco_Inter::wc_banco_inter_get_token();

    /**
     * https://github.com/humanmade/wp-irc-plugins/blob/master/includes/backpress/class.wp-http.php
     * https://github.com/dcid/wordpress-plugin/blob/master/sucuri.php
     */
    $url = "https://cdpj.partners.bancointer.com.br/cobranca/v2/boletos/".$nossonumero."/pdf";

    $http = _wp_http_get_object();
	$argsCard = array(
		'timeout'       => 30,
		'method'        => 'GET',
		'httpversion'   => '1.1',
		//'body'          => $datas,
		'headers'       => [
			"Authorization" => "Bearer $token",
			"Content-Type" => "application/json"
		],
	);
	$tuBilletHttp =  wp_remote_get($url, $argsCard);
	$tuBillet =  json_decode($tuBilletHttp['body']);

    if($tuBilletHttp["response"]["code"] != '200'){
        bancointer_register_error($tuBilletHttp["response"]["message"].": ".$tuBilletHttp["response"]["code"]);
    }


    if($tuBillet != ''){
        $content = base64_decode($tuBillet->pdf);
        $archive = fopen(__DIR__.'/../tmp/boleto-'.$order->get_id().'.pdf', 'w');
        fwrite($archive, $content);
        fclose($archive);

    }

}