<?php

/**
 * Operate Banco Inter on the order screen
 */
add_action( 'woocommerce_order_details_after_order_table', 'wc_banco_inter_custom_field_display_cust_order_meta', 10, 1 );
function wc_banco_inter_custom_field_display_cust_order_meta($order){

    /**Status do pedido */
    $status = $order->get_status();

    $setings = wc_get_payment_gateway_by_order( $order->get_id() );
    if($order->get_payment_method() == 'interboleto'){
        inter_boleto_view($order, $setings);
        inter_pix_html_view($order->get_meta('pixCopiaECola'), $order->get_id());
    }elseif($order->get_payment_method() == 'interpix'){
        inter_pix_view($order, $setings);
    }
}

function inter_boleto_view($order, $setings){
    _e('<p><strong>'.__( $setings->settings['instructions'], 'inter_boleto' ).'</strong></p>');

    if(get_post_meta( $order->get_id(), 'boleto', true )){
            $boleto = get_post_meta( $order->get_id(), 'boleto', true );
    }else{
        $boleto = $order->get_meta('boleto');
    }
    if(get_post_meta( $order->get_id(), 'nossonumero', true )){
        $nossonumero = get_post_meta( $order->get_id(), 'nossonumero', true );
    }else{
        $nossonumero = $order->get_meta('nossonumero');
    }

    $codigoSolicitacao = null;
    if($order->get_meta('codigoSolicitacao')){
        $codigoSolicitacao = $order->get_meta('codigoSolicitacao');
    }elseif(get_post_meta( $order->get_id(), 'codigoSolicitacao', true )){
        $codigoSolicitacao = get_post_meta( $order->get_id(), 'codigoSolicitacao', true );
    }

    if(!$codigoSolicitacao){
        return;
    }
    $dadosCobranca = WC_Banco_Inter::buscarCobrancaV3($codigoSolicitacao);
    if(isset($dadosCobranca->violacoes)){

    }
    if(!$nossonumero OR $nossonumero == '' OR $nossonumero == null){
        /**Boleto */
        $nossoNumero = $dadosCobranca->boleto->nossoNumero;
        $codigoBarras = $dadosCobranca->boleto->codigoBarras;
        $order->update_meta_data( 'boleto', $codigoBarras );
        $order->update_meta_data( 'nossonumero', $nossoNumero );
        $order->update_meta_data( 'transaction', $nossoNumero );
        update_post_meta( $order->get_id(), 'boleto', $codigoBarras );
        update_post_meta( $order->get_id(), 'nossonumero', $nossoNumero );
        update_post_meta( $order->get_id(), 'transaction', $nossoNumero );

        /**Pix */
        $pixCopiaECola = $dadosCobranca->pix->pixCopiaECola;
        $txid = $dadosCobranca->pix->txid;
        $order->update_meta_data( 'pixCopiaECola', $pixCopiaECola );
        $order->update_meta_data( 'pixTxid', $txid );
        update_post_meta( $order->get_id(), 'pixCopiaECola', $pixCopiaECola );
        update_post_meta( $order->get_id(), 'pixTxid', $txid );
        $order->save();

        /**PDF */
        WC_Banco_Inter::buscarPDFBoleto($codigoSolicitacao, $order->get_id());
    }

    if($boleto){
        if(!file_exists(WC_BANCO_INTER_BOLETO_DIR.'boleto-'.$order->get_id().'.pdf')){
            /**PDF */
            WC_Banco_Inter::buscarPDFBoleto($codigoSolicitacao, $order->get_id());
        }
        if(file_exists(WC_BANCO_INTER_BOLETO_DIR.'boleto-'.$order->get_id().'.pdf')){
            echo
            '
                <label for="boleto" class="labelBoleto">Pagamento por Boleto</label>
                <div class="copy-link-boleto input-group pt-5" id="boleto-copy">
                <span class="download input-group-left" style="cursor: pointer;">
                    <a target="_blank" href="'.site_url().'/wp-content/banco-inter/boleto/boleto-'.$order->get_id().'.pdf">
                        <i class="fas fa-download"></i>
                    </a>
                </span>
                <input id="boleto" type="text" class="copy-link-input-boleto form-control" value="'.$boleto.'" readonly>
                <span class="copy-link-button input-group-addon" style="cursor: pointer;">
                    <i class="fa-regular fa-copy" id="copiarCDBarras"></i>
                </span>
            </div><br>';
        }
    }
}

function inter_pix_html_view($pix, $id){
    if(!$pix){
        return;
    }
    echo '
    <label for="pix" class="labelPix">Pagamento por PIX</label>
    <div class="copy-link input-group pt-5" id="pix-copy">
        <input id="pix" type="text" class="copy-link-input form-control" value="'.$pix.'" readonly>
        <span class="copy-link-button input-group-addon" style="cursor: pointer;">
            <i class="fa-regular fa-copy" id="copiarPix"></i>
        </span>
    </div>';

    $http = _wp_http_get_object();
    $tuBillet = $http->get("https://qrcode.diletec.com.br/index.php?qrcode=".$pix, '&token=2024');
    if(isset($tuBillet["body"]) AND $tuBillet["body"] != ''){
        /**Pegar o link da página do plugin */
        $url = plugins_url( 'wc-banco-inter-pro' );
        _e( '<img src="'.$tuBillet["body"].'" style="max-width: 49%;">' );
        echo '<img src="'.$url.'/assets/img/instrucao-pix.png" style="width: 50%;">';
    }
}

function inter_pix_view($order, $setings){
    /** Verificar pagamento */
    // var_dump("pixCopiaECola", get_post_meta( $order->get_id(), 'pixCopiaECola', true ), $order->get_meta('pixCopiaECola'), $order->get_meta('pixErro')); //exit;
    if( (get_post_meta( $order->get_id(), 'pixCopiaECola', true ) != '' OR $order->get_meta('pixCopiaECola') != '') AND ($order->get_status() == 'on-hold' OR $order->get_status() == 'parcial')){
        /** Mostra o QRCode */
        if(get_post_meta( $order->get_id(), 'pixCopiaECola', true ) != ''){
            $pix = get_post_meta( $order->get_id(), 'pixCopiaECola', true );
        }else{
            $pix = $order->get_meta('pixCopiaECola');
        }

        _e('<p><strong>'.__( $setings->settings['instructions'], 'inter_pix' ).'</strong></p>');
        inter_pix_html_view($pix, $order->get_id());
    }
}

function bancointer_css_customer() {
    wp_enqueue_style('interboleto', plugins_url( 'wc-banco-inter/assets/css/interboleto.css' ));
    /**fontwesome */
    wp_enqueue_style('fontwesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
}
add_action( 'wp_enqueue_scripts', 'bancointer_css_customer' );

/**Carregar JS quando a página estiver carregada */
function bancointer_js_customer() {
    wp_enqueue_script('interboleto', plugins_url( 'wc-banco-inter/assets/js/inter-comand-cop.js' ));
}
add_action( 'wp_footer', 'bancointer_js_customer' );