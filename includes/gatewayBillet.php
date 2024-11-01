<?php

/**
 * Add Banco Inter as a gateway method
 */
add_filter( 'woocommerce_payment_gateways', 'add_wc_banco_inter_gateway_class' );
function add_wc_banco_inter_gateway_class( $methods ) {
    $methods[] = 'WC_Banco_Inter';
    return $methods;
}

/**
* Operate Banco Inter at checkout
*/
add_action('woocommerce_checkout_process', 'process_wc_banco_inter_payment', 10);
function process_wc_banco_inter_payment(){

    if($_POST['payment_method'] != 'interboleto'){
        return;
    }

    /**Validate CNPJ OR CPF */
    if (class_exists( 'Extra_Checkout_Fields_For_Brazil' )){
        if(isset($_POST['billing_cpf']) AND strlen($_POST['billing_cpf']) != 0){
            $vat = sanitize_text_field( $_POST['billing_cpf']);
        }elseif(isset($_POST['billing_cnpj']) AND strlen($_POST['billing_cnpj']) != 0){
            $vat = sanitize_text_field( $_POST['billing_cnpj'] );
        }else{
            wc_add_notice( sprintf( '<strong>CNPJ ou CPF</strong> é obrigatório.' ), 'error' );
        }
    }else{
        ( isset($_POST['vat_inter'])
            AND strlen($_POST['vat_inter']) != 0
        ) ?
            $vat = sanitize_text_field( $_POST['vat_inter'] ) :
            wc_add_notice( sprintf( '<strong>CNPJ ou CPF</strong> é obrigatório.' ), 'error' );
    }

    if ( empty( $_POST['billing_postcode'] ) ) {
        wc_add_notice( sprintf( 'Campo <strong>CEP</strong> é obrigatório.' ), 'error' );
    }

}

/**
 * Update the order meta with field value
 */
add_action( 'woocommerce_checkout_update_order_meta', 'wc_banco_inter_payment_update_order_meta' );
function wc_banco_inter_payment_update_order_meta( $order_id ) {

    if($_POST['payment_method'] != 'interboleto'){
        return;
    }
    $vat = '';
    $order = wc_get_order( $order_id );

    /**API Cep */
    $bp = sanitize_text_field( $_POST['billing_postcode'] );
    $urlCep = "https://viacep.com.br/ws/".preg_replace("/[^0-9]/", "", $bp)."/json/";
    $argsCep = array(
        'timeout'       => 30,
        'method'        => 'GET'
    );
    $htt = _wp_http_get_object();
    $ends = $htt->get($urlCep, $argsCep);
    $end = json_decode($ends['body']);


    $vencimento = date('Y-m-d', (time()+250000));
    $data = date('Y-m-d');

    if (class_exists( 'Extra_Checkout_Fields_For_Brazil' )){
        if(isset($_POST['billing_cpf']) AND strlen($_POST['billing_cpf']) != 0){
            $vat = sanitize_text_field( $_POST['billing_cpf'] );
        }elseif(isset($_POST['billing_cnpj']) AND strlen($_POST['billing_cnpj']) != 0){
            $vat = sanitize_text_field( $_POST['billing_cnpj'] );
        }
    }else{
        ( isset($order->data['billing']['vat_inter'])
            AND strlen($order->data['billing']['vat_inter']) != 0
        ) ?
            $vat = sanitize_text_field( $_POST['vat_inter'] ) :
            false;
    }
    if(empty($vat) || $vat == ""){
        $vat = sanitize_text_field( $_POST['vat_inter'] );
    }

    /**if !$vat pega o billing_cpf do cadastro do cliente*/
    if(strlen($vat) < 11){
        $vat = preg_replace("/[^0-9]/", "", $vat);
        $user_id = $order->get_user_id();
        if(strlen($vat) <= 11){
            $user = get_user_meta( $user_id, 'billing_cpf', true );
            if(isset($user)){
                $vat = preg_replace("/[^0-9]/", "", $user);
            }
        }else{
            $user = get_user_meta( $user_id, 'billing_cnpj', true );
            if(isset($user)){
                $vat = preg_replace("/[^0-9]/", "", $user);
            }
        }
    }

    /**Tratar o número do endereço com o Plugin Brazilian e sem ele */
    if(isset($_POST['billing_number'])){
        $number = sanitize_text_field( $_POST['billing_number'] );
    }elseif(isset($order->data['billing']['address_1'])){
        if(is_int(preg_replace("/[^0-9]/", "", $order->data['billing']['address_1']))){
            $number = preg_replace("/[^0-9]/", "", $order->data['billing']['address_1']);
        }else{
            $number = "0";
        }
    }else{
        $number = "0";
    }


    ( strlen(preg_replace("/[^0-9]/", "", $vat)) > 11 ) ? $tipo = "JURIDICA" : $tipo = "FISICA";

    $setings = wc_get_payment_gateway_by_order( $order_id );

    $billetData = [
        "pagador"=>[
           "cpfCnpj"=> preg_replace("/[^0-9]/", "", $vat),
           "nome"=>$order->get_billing_first_name().' '.$order->get_billing_last_name(),
           "email"=>"",
           "telefone"=>"",
           "cep"=>preg_replace("/[^0-9]/", "", $bp),
           "complemento"=>"",
           "bairro"=> $end->bairro,
           "numero"=>$number,
           "cidade"=>$end->localidade,
           "uf"=> $end->uf,
           "endereco"=>$end->logradouro,
           "ddd"=>"",
           "tipoPessoa"=>$tipo
        ],
        "dataEmissao"=> $data,
        "seuNumero"=>$order_id,
        "dataVencimento"=>$vencimento,
        "mensagem"=>[
           "linha1"=>"Compra de Nº ".$order_id,
           "linha2"=>"Caso o boleto não seja pago até a data",
           "linha3"=>"a compra será cancelada.",
           "linha4"=>"Fique atendo nos vencimentos e descontos.",
           "linha5"=>"Dúvidas, fale conosco."
        ],
        "desconto1"=>[
           "codigoDesconto"=>"NAOTEMDESCONTO",
           "taxa"=>0,
           "valor"=>0,
           "data"=>""
        ],
        "desconto2"=>[
           "codigoDesconto"=>"NAOTEMDESCONTO",
           "taxa"=>0,
           "valor"=>0,
           "data"=>""
        ],
        "desconto3"=>[
           "codigoDesconto"=>"NAOTEMDESCONTO",
           "taxa"=>0,
           "valor"=>0,
           "data"=>""
        ],
        "valorNominal"=>$order->get_total(),
        "valorAbatimento"=>0,
        // "cnpjCPFBeneficiario"=>preg_replace("/[^0-9]/", "", $setings->settings['cnpjCPFBeneficiario']),
        "numDiasAgenda" => 30
    ];

    /**Salvar sem dados */
    $order->update_meta_data( 'boleto', '' );
    $order->update_meta_data( 'nossonumero', '' );
    $order->update_meta_data( 'transaction', '' );
    update_post_meta( $order_id, 'boleto', '' );
    update_post_meta( $order_id, 'nossonumero', '' );
    update_post_meta( $order_id, 'transaction', '' );
    $order->update_meta_data( 'pixCopiaECola', '' );
    $order->update_meta_data( 'pixTxid', '' );
    update_post_meta( $order_id, 'pixCopiaECola', '' );
    update_post_meta( $order_id, 'pixTxid', '' );
    $order->save();

    $token = WC_Banco_Inter::wc_banco_inter_get_token();
    if($token == false){
        // return false;
    }

    /**Gerar Cobrança v3 */
    $cobranca = WC_Banco_Inter::gerarBoletoPix($billetData, $token, $order_id);
    if($cobranca == null){
        // return false;
    }
    // exit("Token: ".$cobranca);
    $order->update_meta_data('codigoSolicitacao', $cobranca);
    update_post_meta( $order_id, 'codigoSolicitacao', $cobranca);
    $order->save();

   /**Buscar as informações da cobrança */
    $dadosCobranca = WC_Banco_Inter::buscarCobrancaV3($cobranca, $token);
    if($dadosCobranca == null){
        return false;
    }

    /**Boleto */
    try{
        $nossoNumero = $dadosCobranca->boleto->nossoNumero;
        $codigoBarras = $dadosCobranca->boleto->codigoBarras;
        $order->update_meta_data( 'boleto', $codigoBarras );
        $order->update_meta_data( 'nossonumero', $nossoNumero );
        $order->update_meta_data( 'transaction', $nossoNumero );
        update_post_meta( $order_id, 'boleto', $codigoBarras );
        update_post_meta( $order_id, 'nossonumero', $nossoNumero );
        update_post_meta( $order_id, 'transaction', $nossoNumero );
    }catch(Exception $e){
        echo "Erro ao salvar Boleto: ".$e->getMessage();
    }

    /**Pix */
    try{
        $pixCopiaECola = $dadosCobranca->pix->pixCopiaECola;
        $txid = $dadosCobranca->pix->txid;
        $order->update_meta_data( 'pixCopiaECola', $pixCopiaECola );
        $order->update_meta_data( 'pixTxid', $txid );
        update_post_meta( $order_id, 'pixCopiaECola', $pixCopiaECola );
        update_post_meta( $order_id, 'pixTxid', $txid );
    }catch(Exception $e){
        echo "Erro ao salvar Pix: ".$e->getMessage();
    }

    $order->save();

    /**PDF */
    WC_Banco_Inter::buscarPDFBoleto($cobranca, $order_id, $token);
    //exit();
}
