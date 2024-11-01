<?php

/**
 * Get the status of the billet
 */
add_action( 'woocommerce_order_details_after_order_table', 'wc_banco_inter_boleto_status', 10, 1 );
function wc_banco_inter_boleto_status( $order ){
    /**https://woocommerce.wp-a2z.org/oik_api/wc_get_order_statuses/ */

    $method = get_post_meta( $order->get_id(), '_payment_method', true );
    if($method != 'interboleto')
        return;
    $nossonumero = get_post_meta( $order->get_id(), 'nossonumero', true );
    $token = WC_Banco_Inter::wc_banco_inter_get_token();

    $url = "https://cdpj.partners.bancointer.com.br/cobranca/v2/boletos/".$nossonumero;

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

	$tuBillet =  json_decode( sanitize_text_field( $tuBilletHttp['body'] ) );

    if($tuBilletHttp["response"]["code"] != '200'){
        bancointer_register_error($tuBilletHttp["response"]["message"].": ".$tuBilletHttp["response"]["code"]);
    }

    if($tuBillet->situacao != ''){
        if($tuBillet->situacao == "BAIXADO"){
            //$order->update_status('wc-cancelled', __( 'Checkout with inter. ', 'inter_boleto' ) );
        }elseif($tuBillet->situacao == "PAGO"){
            //$order->update_status('wc-processing', __( 'Checkout with inter. ', 'inter_boleto' ) );
        }
    }

}