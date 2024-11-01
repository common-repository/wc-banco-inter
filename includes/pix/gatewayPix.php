<?php
/**
 * Configurações do PIX
 * https://github.com/william-costa/wdev-qrcode-pix-estatico-php
 */

add_action('plugins_loaded', 'init_wc_banco_inter_gateway_pix_free_class', 0);

function init_wc_banco_inter_gateway_pix_free_class(){

    /**Verificar se existe a class WC_Banco_Inter_PIX */
    if(class_exists('WC_Banco_Inter_PIX')){
        return;
    }

    if ( !class_exists( 'WC_Payment_Gateway' ) ) {
        add_action ('admin_notices', 'inter_gateway_class_wc_notice');
        return;
    }

    class WC_Banco_Inter_PIX_free extends WC_Payment_Gateway {

        public $domain;
        public $order_status;
        public $instructions;
        public $pixKey;
        public $pixMerchantName;
        public $pixMerchantCity;

        /**
        * Constructor for the gateway.
        */
        public function __construct() {

            $this->domain = 'inter_boleto';

            $this->id                 = 'interpixfree';
            $this->icon               = apply_filters('woocommerce_custom_gateway_icon', '');
            $this->has_fields         = false;
            $this->method_title       = __( 'Banco Inter PIX', $this->domain );
            $this->method_description = sprintf (
                 __( 'Aviso: Essa funcionalidade só da baixa automática na versão PRO <a href="https://cp.diletec.com.br/produto/banco-inter-para-woocommerce-pro-vitalicio-e-ilimitado">Atualizar para Versão PRO</a>', $this->domain)
                ).
                sprintf (
                    __( '<br/>Precisa de ajuda para configurar? <a href="%s">Suporte</a>', $this->domain),
                    esc_url( 'https://cp.diletec.com.br/produto/instalacao-do-plugin-do-plugin-banco-inter-para-woocommerce-pro-6?utm_source=wordpress&utm_medium=plugin&utm_campaign=bancointer&utm_term=suporte&utm_content=config' )
                ).
                /**Ative a versão PRO */
                sprintf (
                    __( '<br/>Precisa de mais funcionalidades? <a href="%s">Versão PRO</a>', $this->domain),
                    esc_url( 'https://cp.diletec.com.br/produto/banco-inter-para-woocommerce-pro-vitalicio-e-ilimitado?utm_source=wordpress&utm_medium=plugin&utm_campaign=bancointer&utm_term=pro&utm_content=config&ra=13' )
                );

            // Load the settings.
            $this->init_form_fields();
            $this->init_settings();

            // Define user set variables
            $this->title                    = $this->get_option( 'title' );
            $this->description              = $this->get_option( 'description' );
            $this->instructions             = $this->get_option( 'instructions', $this->description );
            $this->order_status             = $this->get_option( 'order_status', 'on-hold' );
            $this->pixKey                   = $this->get_option( 'pix_key' );
            $this->pixMerchantName          = $this->get_option( 'pix_merchant_name' );
            $this->pixMerchantCity          = $this->get_option( 'pix_merchant_city' );


            // Actions
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );

            // Customer Emails.
		    add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );

            /**callback */
            add_action( 'woocommerce_api_wc_banco_inter_pix', array( $this, 'webhook_banco_inter_pix') );
        }

        /**
        * Initialise Gateway Settings Form Fields.
        */
        public function init_form_fields() {

            $this->form_fields = array(
                'enabled' => array(
                    'title'   => __( 'Enable/Disable', $this->domain ),
                    'type'    => 'checkbox',
                    'label'   => __( 'Enable inter', $this->domain ),
                    'default' => 'yes'
                ),
                'title' => array(
                    'title'       => __( 'Title', $this->domain ),
                    'type'        => 'text',
                    'description' => __( 'This controls the title which the user sees during checkout.', $this->domain ),
                    'default'     => __( 'Pix', $this->domain ),
                    'desc_tip'    => true,
                ),
                'order_status' => array(
                    'title'       => __( 'Order Status', $this->domain ),
                    'type'        => 'select',
                    'class'       => 'wc-enhanced-select',
                    'description' => __( 'Choose whether status you wish after checkout.', $this->domain ),
                    'default'     => 'wc-on-hold',
                    'desc_tip'    => true,
                    'options'     => wc_get_order_statuses()
                ),
                'description' => array(
                    'title'       => __( 'Description', $this->domain ),
                    'type'        => 'textarea',
                    'description' => __( 'Descrição do pagamento por PIX', $this->domain ),
                    'default'     => __('Descrição do pagamento por PIX', $this->domain),
                    'desc_tip'    => true,
                ),
                'instructions' => array(
                    'title'       => __( 'Instructions', $this->domain ),
                    'type'        => 'textarea',
                    'description' => __( 'Instruções de como realizar o pagamento por PIX', $this->domain ),
                    'default'     => 'Instruções de como realizar o pagamento por PIX',
                    'desc_tip'    => true,
                ),
                'pix_key' => array(
                    'title'       => __( 'Chave Pix', $this->domain ),
                    'type'        => 'text',
                    'description' => __( 'Chave do Pix a ser utilizada.', $this->domain ),
                    'default'     => '',
                    'desc_tip'    => true,
                ),
                'pix_merchant_name' => array(
                    'title'       => __( 'Nome do beneficiário', $this->domain ),
                    'type'        => 'text',
                    'description' => __( 'Nome do beneficiário', $this->domain ),
                    'default'     => '',
                    'desc_tip'    => true,
                ),
                'pix_merchant_city' => array(
                    'title'       => __( 'Cidade do beneficiário', $this->domain ),
                    'type'        => 'text',
                    'description' => __( 'Cidade, letras maiusculas e sem caracteres especiais.', $this->domain ),
                    'default'     => 'BELO HORIZONTE',
                    'desc_tip'    => true,
                ),
                'parcelamento' => array(
                    'title'       => __( 'Quantidade de Parcelas (Ative a Versão PRO)', $this->domain ),
                    'type'        => 'number',
                    'label'       => __( 'Quantidade de parcelas', $this->domain ),
                    'default'     => '0',
                    'desc_tip'    => true,
                ),
                'parcelamento_sem_juros' => array(
                    'title'       => __( 'Quantidade de parcelas sem juros (Ative a Versão PRO)', $this->domain ),
                    'type'        => 'number',
                    'label'       => __( 'Quantidade de parcelas sem juros', $this->domain ),
                    'default'     => '0',
                    'desc_tip'    => true,
                ),
                'parcelamento_valor_minimo' => array(
                    'title'       => __( 'Valor mínimo para parcelamento (Ative a Versão PRO)', $this->domain ),
                    'type'        => 'text',
                    'label'       => __( 'Valor mínimo para parcelamento', $this->domain ),
                    'default'     => '0',
                    'desc_tip'    => true,
                ),
                'parcelamento_juros' => array(
                    'title'       => __( 'Juros do parcelamento (%)a.m (Ative a Versão PRO)', $this->domain ),
                    'type'        => 'text',
                    'label'       => __( 'Juros do parcelamento (%)a.m', $this->domain ),
                    'default'     => '0',
                    'desc_tip'    => true,
                ),
                'webhook' => array(
                    'title'       => __( 'Webhook (Ative a Versão PRO)', $this->domain ),
                    'type'        => 'text',
                    'description' => __( 'Webhook para receber notificações de pagamento', $this->domain ),
                    'default'     => site_url().'?/wc-api=WC_Banco_Inter_PIX_free',
                    'desc_tip'    => true,
                ),
            );

        }

        /**
        * Output for the order received page.
        */
        public function thankyou_page() {
            if ( $this->instructions )
                return wpautop( wptexturize( $this->instructions ) );
        }


        /**
         * Get templates path.
         *
         * @return string
         */
        public static function get_templates_path() {
            return plugin_dir_path( __FILE__ ) . '../../templates/';
        }


        /**
         * Add content to the WC emails.
         *
         * @param  WC_Order $order         Order object.
         * @param  bool     $sent_to_admin Send to admin.
         * @param  bool     $plain_text    Plain text or HTML.
         * @return string
         */
        public static function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
            $setings = wc_get_payment_gateway_by_order( $order->get_id() );
            $base    = get_option( 'woocommerce_email_base_color' );

            if ( $setings->settings['instructions'] && ! $sent_to_admin && 'interpixfree' === $order->get_payment_method() && $order->has_status( 'on-hold' ) ) {
                _e( wp_kses_post( wpautop(
                    wptexturize( $setings->settings['instructions'] )
                     ) . PHP_EOL
                     /**Link do boleto */
                     .'<br><p class="billetdownload"><a style="
                     font-weight: 700 !important;
                     color: white !important;
                     font-size: larger !important;
                     padding: 15px !important;
                     background-color: '.$base.' !important;
                     margin: 10px 0px 10px 0px !important;
                     display: block !important;
                     text-decoration: none !important;
                     max-width: 50% !important;
                     text-align: center !important;" target="blank" href="'.$order->get_view_order_url().'">'._( 'Realizar o pagamento').'</a></p><br>'. PHP_EOL
                     ) );
            }
        }

        /**Registro de Webhook */
        function webhook_banco_inter_pix(){
            if(isset($_GET['id']) AND $_GET['id'] != '' AND $_GET['id'] != 0 AND $_GET['id'] != null){
                /**Order */
                $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
                $order = wc_get_order( $id );
                $segue = 0;
                /**Verificar se o pedido já está cancelado */
                if($order->get_status() == 'cancelled'){
                    _e(json_encode(array('status' => 'error', 'message' => 'Pedido já cancelado.', 'instrucao' => 'reload'))); exit;
                }elseif($order->get_status() == 'completed'){
                    $segue = 0;
                    exit;
                }elseif($order->get_status() == 'processing'){
                    $segue = 0;
                    exit;
                }
                if($order->get_status() == 'wc-on-hold'){ $segue = 1; }
                if($order->get_status() == 'on-hold'){ $segue = 1; }
                if($order->get_status() == 'wc-parcial'){ $segue = 1; }
                if($segue != 1){
                    echo 'Não segue: '.$order->get_status();
                    exit;
                }
            }

            /**verificar se tem uma request post */
            if(isset($_SERVER['CONTENT_TYPE']) AND 'application/json' === $_SERVER['CONTENT_TYPE']){
                $data = wc_clean( wp_unslash( file_get_contents("php://input") ) );

                $dados = json_decode($data);
                if(isset($dados->info)){
                    $file = fopen(WC_BANCO_INTER_DIR.'/info.txt', 'w');
                    fwrite($file, $data);
                    fclose($file);
                }
            }
            exit;
        }

        /**
         * Send email notification.
         *
         * @param string $subject Email subject.
         * @param string $title   Email title.
         * @param string $message Email message.
         */
        public static function send_email( $subject, $title, $message ) {
            $mailer = WC()->mailer();
            $order = $message;

            $orders = new WC_Order_Item_Product( $order );
            $product = $orders->get_product();

            $message = '<div style="margin-bottom: 40px;">
                <table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: Helvetica, Roboto, Arial, sans-serif;" border="1">
                    <thead>
                        <tr>
                            <th class="td" scope="col" style="text-align:left;">Produtos</th>
                            <th class="td" scope="col" style="text-align:left;">Quantidade</th>
                            <th class="td" scope="col" style="text-align:left;">Preço</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>'.
                        wc_get_email_order_items(
                            $order,
                            array(
                                'show_sku'      => false,
                                'show_image'    => false,
                                'image_size'    => array( 32, 32 ),
                                'plain_text'    => 'left',
                                'sent_to_admin' => false,
                            )
                        )
                    .'</tr>
                    </tbody>
                    <tfoot>
                    </tfoot>
                </table>
            </div>';

            $mailer->send( get_option( 'admin_email' ), $subject, $mailer->wrap_message( $title, $message ) );
        }

        /**
        * Process the payment and return the result.
        *
        * @param int $order_id
        * @return array
        */
        public function process_payment( $order_id ) {

            $order = wc_get_order( $order_id );
            $status = 'wc-' === substr( $this->order_status, 0, 3 ) ? substr( $this->order_status, 3 ) : $this->order_status;

            // Set order status
            $order->update_status( $status, __( 'Checkout with inter. ', $this->domain ) );

            // Reduce stock levels
            $order->reduce_order_stock();

            // Remove cart
            WC()->cart->empty_cart();

            // Return thankyou redirect
            return array(
                'result'    => 'success',
                'redirect'  => $this->get_return_url( $order )
            );
        }


        static public function setingsApi(){
            global $wpdb;
            $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}options
                WHERE option_name = 'woocommerce_interpixfree_settings'
            ");

            return [
                "settings" => [
                    unserialize( $results[0]->option_value )
                ]
            ];

        }


    }

}

/**
 * Add Banco Inter as a gateway method
 */
add_filter( 'woocommerce_payment_gateways', 'add_wc_banco_inter_gateway_pix_free_class' );
function add_wc_banco_inter_gateway_pix_free_class( $methods ) {
    $methods[] = 'WC_Banco_Inter_PIX_free';
    return $methods;
}

/**
 * Update the order meta with field value PIX
 */
add_action( 'woocommerce_checkout_update_order_meta', 'WC_Banco_Inter_PIX_free_payment_update_order_meta' );
function WC_Banco_Inter_PIX_free_payment_update_order_meta( $order_id ) {

    if($_POST['payment_method'] != 'interpixfree'){
        return;
    }

    $order = wc_get_order( $order_id );
    $setings = wc_get_payment_gateway_by_order( $order_id );
    $order_id;

    require __DIR__.'/Payload.php';

    //INSTANCIA PRINCIPAL DO PAYLOAD PIX
    $obPayload = (new Banco_Inter_Payload)->setPixKey($setings->settings['pix_key'])
                            ->setDescription($order_id)
                            /**Máximo 25 caracteres */
                            ->setMerchantName(mb_strimwidth($setings->settings['pix_merchant_name'], 0, 25, ))
                            /**Máximo 15 caracteres */
                            ->setMerchantCity(mb_strimwidth($setings->settings['pix_merchant_city'], 0, 15, ))
                            ->setAmount($order->get_total())
                            ->setTxid($order_id);


    //CÓDIGO DE PAGAMENTO PIX
    $payloadQrCode = $obPayload->getPayload();
    update_post_meta( $order_id, 'pix', $payloadQrCode );

    WC_Banco_Inter_PIX_free::email_instructions($order, 1, 0);
}