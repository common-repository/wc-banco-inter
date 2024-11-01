<?php
/*
 * Plugin Name: Adicionar Banco Inter ao WooCommerce
 * Plugin URL: https://cp.diletec.com.br/ecommerce
 * Description: Adiciona o Banco Inter como método de pagamento no seu WooCommerce
 * Version: 2.1.0
 * Author: Diletec
 * Author URI: https://cp.diletec.com.br/ecommerce
 * Domain Path: /wc-banco-inter/
 * Requires at least: 4.4
 * Requires PHP: 7.0
 * WC requires at least: 3.0.0
 * WC tested up to: 6.6.2
 * Video: https://www.youtube.com/watch?v=CvPZweJQNEw
 */

/**
 * https://stackoverflow.com/questions/17081483/custom-payment-method-in-woocommerce/37631908
 * https://docs.woocommerce.com/document/payment-gateway-api/
 * https://woocommerce.github.io/woocommerce-rest-api-docs/#order-properties
 * crons https://developer.wordpress.org/plugins/cron/scheduling-wp-cron-events/
 */

/** DIR */
include_once plugin_dir_path(__FILE__).'includes/dir.php';

/**
 * Custom Payment Gateway.
 *f
 * Provides a Custom Payment Gateway, mainly for testing purposes.
 */

add_action('plugins_loaded', 'init_wc_banco_inter_gateway_class', 0);

function init_wc_banco_inter_gateway_class()
{

    if (!class_exists('WC_Payment_Gateway')) {
        add_action('admin_notices', 'inter_gateway_class_wc_notice');
        return;
    }
    class WC_Banco_Inter extends WC_Payment_Gateway
    {

        public $domain;
        public $instructions;
        public $order_status;
        public $PathKey;
        public $PathCRT;
        public $CCInter;
        public $cnpjCPFBeneficiario;
        /** /cobrancas */
        const urlV3Prod = 'https://cdpj.partners.bancointer.com.br/cobranca/v3';
        const urlV3SendBox = 'https://cdpj-sandbox.partners.uatinter.co/cobranca/v3';

        /**
         * Constructor for the gateway.
         */
        public function __construct()
        {

            $this->domain = 'inter_boleto';

            $this->id = 'interboleto';
            $this->icon = apply_filters('woocommerce_custom_gateway_icon', '');
            $this->has_fields = false;
            $this->method_title = __('Banco Inter ', $this->domain);
            $this->method_description = sprintf(
                __('Allows payments with Banco Inter gateway <a href="%s">Testar conexão com o banco.</a>', $this->domain),
                esc_url(admin_url('admin.php?page=wc-settings&tab=checkout&section=interboleto&test=bancointerapi'))
                ) .
                sprintf(
                    __('<br/>Precisa de um técnico para configurar? <a href="%s">Contratar</a>', $this->domain),
                    esc_url('https://cp.diletec.com.br/produto/instalacao-do-plugin-do-plugin-banco-inter-para-woocommerce-pro-6?utm_source=wordpress&utm_medium=plugin&utm_campaign=bancointer&utm_term=suporte&utm_content=config')
                ).
                /**Ative a versão PRO */
                sprintf (
                    __( '<br/>Precisa de mais funcionalidades? <a href="%s">Versão PRO</a>', $this->domain),
                    esc_url( 'https://cp.diletec.com.br/produto/banco-inter-para-woocommerce-pro-vitalicio-e-ilimitado?utm_source=wordpress&utm_medium=plugin&utm_campaign=bancointer&utm_term=pro&utm_content=config&ra=13' )
                );

            // Load the settings.
            $this->init_form_fields();
            $this->init_settings();
            $this->seting_alert_error();
            if (isset($_GET['test']) and $_GET['test'] == 'bancointerapi') {$this->banco_inter_test_api_billet();}

            // Define user set variables
            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->instructions = $this->get_option('instructions', $this->description);
            $this->order_status = $this->get_option('order_status', 'on-hold');
            $this->PathKey = $this->get_option('PathKey');
            $this->PathCRT = $this->get_option('PathCRT');
            $this->CCInter = $this->get_option('CCInter');
            $this->cnpjCPFBeneficiario = $this->get_option('cnpjCPFBeneficiario');

            // Actions
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            add_action('woocommerce_thankyou_' . $this->id, array($this, 'thankyou_page'));

            // Customer Emails.
            add_action('woocommerce_email_before_order_table', array($this, 'email_instructions'), 10, 3);

            if(isset($_GET['section']) AND $_GET['section'] == 'interboleto'){
                WC_Banco_Inter::reject();
            }
        }

        /**
         * Initialise Gateway Settings Form Fields.
         */
        public function init_form_fields()
        {
            /**Verificar se tem arquivo key */
            $key = (file_exists(wp_upload_dir()['basedir']. '/diletec/inter.key'))? 'inter.key': 'Nenhum arquivo enviado.';
            $crt = (file_exists(wp_upload_dir()['basedir']. '/diletec/inter.crt'))? 'inter.crt': 'Nenhum arquivo enviado.';
            $ca23 = (file_exists(wp_upload_dir()['basedir']. '/diletec/ca23.crt'))? 'ca23.crt': 'Nenhum arquivo enviado.';

            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('Enable/Disable', $this->domain),
                    'type' => 'checkbox',
                    'label' => __('Enable inter', $this->domain),
                    'default' => 'yes',
                ),
                'title' => array(
                    'title' => __('Title', $this->domain),
                    'type' => 'text',
                    'description' => __('This controls the title which the user sees during checkout.', $this->domain),
                    'default' => __('inter', $this->domain),
                    'desc_tip' => true,
                ),
                'order_status' => array(
                    'title' => __('Order Status', $this->domain),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select',
                    'description' => __('Choose whether status you wish after checkout.', $this->domain),
                    'default' => 'wc-on-hold', //'wc-pending',
                    'desc_tip' => true,
                    'options' => wc_get_order_statuses(),
                ),
                'description' => array(
                    'title' => __('Description', $this->domain),
                    'type' => 'textarea',
                    'description' => __('Payment method description that the customer will see on your checkout.', $this->domain),
                    'default' => __('Dados para o boleto', $this->domain),
                    'desc_tip' => true,
                ),
                'instructions' => array(
                    'title' => __('Instructions', $this->domain),
                    'type' => 'textarea',
                    'description' => __('Instructions that will be added to the thank you page and emails.', $this->domain),
                    'default' => '',
                    'desc_tip' => true,
                ),
                'PathKey' => array(
                    'title'       => __( 'Path Key', $this->domain ),
                    'type'        => 'file',
                    'description' => __( 'Arquivo: '.$key , $this->domain ),
                    'default'     => 'keys/meu.key',
                    'desc_tip'    => false,
                    'label'       => 'Arquivo Key',
                    'custom_attributes' => array(
                        'accept' => '.key'
                    ),
                ),
                'PathCRT' => array(
                    'title'       => __( 'Path CRT', $this->domain ),
                    'type'        => 'file',
                    'description' => __( 'Arquivo: '.$crt, $this->domain ),
                    'default'     => 'keys/meu.crt',
                    'desc_tip'    => false,
                    'label'       => 'Arquivo CRT',
                    'custom_attributes' => array(
                        'accept' => '.crt'
                    ),
                ),
                'WebhookCRT' => array(
                    'title'       => __( 'Path CRT Webhook', $this->domain ),
                    'type'        => 'file',
                    'description' => __( 'Arquivo: '.$ca23, $this->domain ),
                    'default'     => 'keys/ca.crt',
                    'desc_tip'    => false,
                    'label'       => 'Arquivo CRT Webhook',
                    'custom_attributes' => array(
                        'accept' => '.crt'
                    ),
                ),
                'CCInter' => array(
                    'title' => __('Conta Corrente', $this->domain),
                    'type' => 'text',
                    'description' => __('Conta corrente com o dígito (Apenas numeros)', $this->domain),
                    'default' => '',
                    'desc_tip' => true,
                ),
                'cnpjCPFBeneficiario' => array(
                    'title' => __('CPF/CNPJ do beneficiário', $this->domain),
                    'type' => 'text',
                    'description' => __('CPF ou CNPJ do proprietário da conta bancária', $this->domain),
                    'default' => '',
                    'desc_tip' => true,
                ),
                'clientId' => array(
                    'title' => __('ID do cliente', $this->domain),
                    'type' => 'text',
                    'description' => __('client_id', $this->domain),
                    'default' => '',
                    'desc_tip' => true,
                ),
                'clientSecret' => array(
                    'title' => __('Secret do cliente', $this->domain),
                    'type' => 'text',
                    'description' => __('client_secret', $this->domain),
                    'default' => '',
                    'desc_tip' => true,
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

                // inicio da versão PRO
                //Linha de informações no boleto
                "mensagem1" => array(
                    'title'       => __( 'Mensagem 1 (Ative a Versão PRO)', $this->domain ),
                    'type'        => 'text',
                    'description' => __( 'Mensagem 1', $this->domain ),
                    'default'     => '',
                    'desc_tip'    => true,
                ),
                "mensagem2" => array(
                    'title'       => __( 'Mensagem 2 (Ative a Versão PRO)', $this->domain ),
                    'type'        => 'text',
                    'description' => __( 'Mensagem 2', $this->domain ),
                    'default'     => '',
                    'desc_tip'    => true,
                ),
                "mensagem3" => array(
                    'title'       => __( 'Mensagem 3 (Ative a Versão PRO)', $this->domain ),
                    'type'        => 'text',
                    'description' => __( 'Mensagem 3', $this->domain ),
                    'default'     => '',
                    'desc_tip'    => true,
                ),
                "mensagem4" => array(
                    'title'       => __( 'Mensagem 4 (Ative a Versão PRO)', $this->domain ),
                    'type'        => 'text',
                    'description' => __( 'Mensagem 4', $this->domain ),
                    'default'     => '',
                    'desc_tip'    => true,
                ),
                "mensagem5" => array(
                    'title'       => __( 'Mensagem 5 (Ative a Versão PRO)', $this->domain ),
                    'type'        => 'text',
                    'description' => __( 'Mensagem 5', $this->domain ),
                    'default'     => '',
                    'desc_tip'    => true,
                ),

                //Juros e multa
                "codigoMulta" => array(
                    'title'       => __( 'Código da multa (Ative a Versão PRO)', $this->domain ),
                    'type'        => 'select',
                    'description' => __( 'Código da multa', $this->domain ),
                    'default'     => 'NAOTEMMULTA',
                    'desc_tip'    => true,
                    'options'     => array(
                        'NAOTEMMULTA' => 'NAOTEMMULTA',
                        'VALORFIXO' => 'VALORFIXO',
                        'PERCENTUAL' => 'PERCENTUAL',
                    ),
                ),
                "multa" => array(
                    'title'       => __( 'Multa (Ative a Versão PRO)', $this->domain ),
                    'type'        => 'text',
                    'description' => __( 'Multa', $this->domain ),
                    'default'     => '',
                    'desc_tip'    => true,
                ),
                "codigoMora" => array(
                    'title'       => __( 'Código do Mora (Ative a Versão PRO)', $this->domain ),
                    'type'        => 'select',
                    'description' => __( 'Código do Mora', $this->domain ),
                    'default'     => 'ISENTO',
                    'desc_tip'    => true,
                    'options'     => array(
                        'ISENTO' => 'ISENTO',
                        'VALORDIA' => 'VALORDIA',
                        'TAXAMENSAL' => 'TAXAMENSAL',
                    ),
                ),
                "mora" => array(
                    'title'       => __( 'Mora (Ative a Versão PRO)', $this->domain ),
                    'type'        => 'text',
                    'description' => __( 'Mora', $this->domain ),
                    'default'     => '',
                    'desc_tip'    => true,
                ),

                //Beneficiário nome, cpfCnpj, tipoPessoa, cep, endereco, bairro, cidade, uf
                "nomeBeneficiario" => array(
                    'title'       => __( 'Nome do beneficiário (Ative a Versão PRO)', $this->domain ),
                    'type'        => 'text',
                    'description' => __( 'Nome do beneficiário', $this->domain ),
                    'default'     => '',
                    'desc_tip'    => true,
                ),
                "cpfCnpjBeneficiario" => array(
                    'title'       => __( 'CPF/CNPJ do beneficiário (Ative a Versão PRO)', $this->domain ),
                    'type'        => 'text',
                    'description' => __( 'CPF/CNPJ do beneficiário', $this->domain ),
                    'default'     => '',
                    'desc_tip'    => true,
                ),
                "tipoPessoaBeneficiario" => array(
                    'title'       => __( 'Tipo de pessoa do beneficiário (Ative a Versão PRO)', $this->domain ),
                    'type'        => 'select',
                    'description' => __( 'Tipo de pessoa do beneficiário', $this->domain ),
                    'default'     => 'JURIDICA',
                    'desc_tip'    => true,
                    'options'     => array(
                        'FISICA' => 'FISICA',
                        'JURIDICA' => 'JURIDICA',
                    ),
                ),
                "cepBeneficiario" => array(
                    'title'       => __( 'CEP do beneficiário (Ative a Versão PRO)', $this->domain ),
                    'type'        => 'text',
                    'description' => __( 'CEP do beneficiário', $this->domain ),
                    'default'     => '',
                    'desc_tip'    => true,
                ),
                "enderecoBeneficiario" => array(
                    'title'       => __( 'Endereço do beneficiário (Ative a Versão PRO)', $this->domain ),
                    'type'        => 'text',
                    'description' => __( 'Endereço do beneficiário', $this->domain ),
                    'default'     => '',
                    'desc_tip'    => true,
                ),
                "bairroBeneficiario" => array(
                    'title'       => __( 'Bairro do beneficiário (Ative a Versão PRO)', $this->domain ),
                    'type'        => 'text',
                    'description' => __( 'Bairro do beneficiário', $this->domain ),
                    'default'     => '',
                    'desc_tip'    => true,
                ),
                "cidadeBeneficiario" => array(
                    'title'       => __( 'Cidade do beneficiário (Ative a Versão PRO)', $this->domain ),
                    'type'        => 'text',
                    'description' => __( 'Cidade do beneficiário', $this->domain ),
                    'default'     => '',
                    'desc_tip'    => true,
                ),
                "ufBeneficiario" => array(
                    'title'       => __( 'UF do beneficiário (Ative a Versão PRO)', $this->domain ),
                    'type'        => 'select',
                    'description' => __( 'UF do beneficiário', $this->domain ),
                    'default'     => '',
                    'desc_tip'    => true,
                    'options'     => array(
                        'AC' => 'AC',
                        'AL' => 'AL',
                        'AP' => 'AP',
                        'AM' => 'AM',
                        'BA' => 'BA',
                        'CE' => 'CE',
                        'DF' => 'DF',
                        'ES' => 'ES',
                        'GO' => 'GO',
                        'MA' => 'MA',
                        'MT' => 'MT',
                        'MS' => 'MS',
                        'MG' => 'MG',
                        'PA' => 'PA',
                        'PB' => 'PB',
                        'PR' => 'PR',
                        'PE' => 'PE',
                        'PI' => 'PI',
                        'RJ' => 'RJ',
                        'RN' => 'RN',
                        'RS' => 'RS',
                        'RO' => 'RO',
                        'RR' => 'RR',
                        'SC' => 'SC',
                        'SP' => 'SP',
                        'SE' => 'SE',
                        'TO' => 'TO',
                    ),
                ),
                // fim da versão PRO

                'webhook' => array(
                    'title'       => __( 'Webhook (Ative a Versão PRO)', $this->domain ),
                    'type'        => 'text',
                    'description' => __( 'Webhook para receber notificações de pagamento', $this->domain ),
                    'default'     => site_url().'?/wc-api=wc_banco_inter_boleto',
                    'desc_tip'    => true,
                ),
            );

        }

        /**
         * Output for the order received page.
         */
        public function thankyou_page()
        {
            if ($this->instructions) {
                return wpautop(wptexturize($this->instructions));
            }

        }

        /**
         * Add content to the WC emails.
         *
         * @access public
         * @param WC_Order $order
         * @param bool $sent_to_admin
         * @param bool $plain_text
         * https://woocommerce.wp-a2z.org/oik_api/wc_gateway_chequeemail_instructions/
         */
        public static function email_instructions($order, $sent_to_admin, $plain_text = false)
        {
            $setings = wc_get_payment_gateway_by_order($order->get_id());
            $base = get_option('woocommerce_email_base_color');

            if ($setings->settings['instructions'] && !$sent_to_admin && 'interboleto' == $order->get_payment_method() && $order->has_status('on-hold')) {
                _e(wp_kses_post(
                    /**Instruções configuradas pelo Adm */
                    wpautop(
                        wptexturize($setings->settings['instructions'])
                    ) . PHP_EOL
                    /**Link do boleto */
                    . '<br><p class="billetdownload"><a style="
                        font-weight: 700 !important;
                        color: white !important;
                        font-size: larger !important;
                        padding: 15px !important;
                        background-color: ' . $base . ' !important;
                        margin: 10px 0px 10px 0px !important;
                        display: block !important;
                        text-decoration: none !important;
                        max-width: 50% !important;
                        text-align: center !important;" target="_blank" href="' . plugin_dir_url(__FILE__) . '/tmp/boleto-' . $order->get_id() . '.pdf">' . __('Baixar boleto') . '</a></p><br>' . PHP_EOL
                ));
            }

        }

        public function payment_fields()
        {

            if ($description = $this->get_description()) {
                _e(wpautop(wptexturize($description)));
            }
            if (!class_exists('Extra_Checkout_Fields_For_Brazil')) {
                ?>
                <div id="custom_input">
                    <div class="form-group">
                        <label>
                            CPF/CNPJ
                            <abbr class="required" title="obrigatório">*</abbr>
                        </label><br/>
                        <input type="text" name="vat_inter" required="required">
                    </div>
                </div>
                <?php
            }
        }

        /**
         * Webhook
         * ?wc-api=wc_banco_inter_boleto
         */
        public function webhook(){
            $data = json_decode(file_get_contents("php://input"),true);
            exit;
        }

        public static function reject(){
            $token = '';
            if(get_option('plugin_banco_inter_licence_key')){
                if(isset(get_option('plugin_banco_inter_licence_key')['keygen'])) {
                    $token = get_option('plugin_banco_inter_licence_key')['keygen'];
                }
            }
            $dominio = site_url();
            $prodId = 5;
            $email = get_option('admin_email');
            $tipo = 'free';
            $url = "https://cp.diletec.com.br/ecommerce/licenca?token=$token&dominio=$dominio&idproduto=$prodId&email=$email&tipo=$tipo";
            try{
                $http = _wp_http_get_object();
                $http->get($url);
            }catch (Exception $e){
                return false;
            }
        }

        /**
         * Process the payment and return the result.
         *
         * @param int $order_id
         * @return array
         */
        public function process_payment($order_id)
        {

            $order = wc_get_order($order_id);

            $status = 'wc-' === substr($this->order_status, 0, 3) ? substr($this->order_status, 3) : $this->order_status;

            // Set order status
            $order->update_status($status, __('Checkout with inter. ', $this->domain));

            // Reduce stock levels
            $order->reduce_order_stock();

            // Remove cart
            WC()->cart->empty_cart();

            // Return thankyou redirect
            return array(
                'result' => 'success',
                'redirect' => $this->get_return_url($order),
            );
        }

        public function seting_alert_error()
        {

            if (isset($_GET['page']) and isset($_GET['tab']) and isset($_GET['section'])) {
                if (!file_exists(getcwd() . '/../' . $this->get_option("PathCRT"))) {
                    //bancointer_acf_notice_dainamic("Não encontramos o arquivo ". $this->get_option("PathCRT"));
                }
                if (!file_exists(getcwd() . '/../' . $this->get_option("PathKey"))) {
                    //bancointer_acf_notice_dainamic("Não encontramos o arquivo ". $this->get_option("PathKey"));
                }
            }

        }

        public function banco_inter_test_api_billet()
        {
            $token = WC_Banco_Inter::wc_banco_inter_get_token();

            $url1 = "https://cdpj.partners.bancointer.com.br/cobranca/v2/boletos";
            $datas = "?dataInicial=2022-01-01&dataFinal=2022-03-28&filtrarDataPor=VENCIMENTO&situacao=EMABERTO&itensPorPagina=2&paginaAtual=0&ordenarPor=PAGADOR&tipoOrdenacao=ASC";

            $http = _wp_http_get_object();
            $argsCard = array(
                'timeout' => 30,
                'method' => 'GET',
                'httpversion' => '1.1',
                //'body'          => json_encode( $registerCart ),
                'headers' => [
                    "Authorization" => "Bearer $token",
                    "Content-Type" => "application/json",
                ],
            );
            $tuBilletHttp = wp_remote_get($url1 . $datas, $argsCard);
            $tuBillet = json_decode(sanitize_text_field($tuBilletHttp['body']));

            if ($tuBilletHttp["response"]["code"] != '200') {
                bancointer_register_error($tuBilletHttp["response"]["message"] . ": " . $tuBilletHttp["response"]["code"]);
            }

            if (isset($tuBillet->totalPages)) {
                $menssage = 'resultou em Sucesso!';
                $typeError = 'success';
            } else {
                $menssage = 'teve erro de comunicação! Para suporte acesse o portal do cliente cp.diletec.com.br';
                $typeError = 'error';
            }
            _e(sprintf(__('<div class="notice notice-' . $typeError . ' is-dismissible interboleto-acf-notice-dainamic"><p>A conexão com o Banco Inter ' . $menssage . '</p></div>', 'bancointer')));
            false;

        }

        public static function wc_banco_inter_get_pedidos_boletos($numero, $id)
        {
            $token = WC_Banco_Inter::wc_banco_inter_get_token();
            if ($token == false) {
                return false;
            }
            $http = _wp_http_get_object();

            $url = "https://cdpj.partners.bancointer.com.br/cobranca/v2/boletos/";
            $datas = $numero;
            $args = array(
                'timeout' => 30,
                'method' => 'GET',
                'httpversion' => '1.1',
                //'body'          => $datas,
                'headers' => [
                    "Authorization" => "Bearer $token",
                    "Content-Type" => "application/json",
                ],
            );

            $tuBilletHttp = wp_remote_get($url . $datas, $args);
            $tuBillet = json_decode($tuBilletHttp['body']);

            if ($tuBilletHttp["response"]["code"] != '200') {
                bancointer_register_error($tuBilletHttp["response"]["message"] . ": " . $tuBilletHttp["response"]["code"] . " - Item: Gerar da venda " . $id . " - Resposta: (624) " . json_encode($tuBillet));
            }

            $order = wc_get_order($id);

            $situacao = $tuBillet->situacao;
            if ($situacao == "BAIXADO") {
                $order->update_status('wc-cancelled', __('Checkout with inter. ', 'inter_boleto'));
            } else if ($situacao == "PAGO") {
                $order->update_status('wc-processing', __('Checkout with inter. ', 'inter_boleto'));
            } else if ($situacao == "VENCIDO") {
                $order->update_status('wc-cancelled', __('Checkout with inter. ', 'inter_boleto'));
            }

            return $situacao;
        }

        /**
         * Registra o Token access
         */
        public static function wc_banco_inter_get_token()
        {
            include_once plugin_dir_path(__FILE__) . 'includes/registerKeys.php';
            /**criar uma sessão para o token que dure 3600s */
            ob_start();
            if (!isset($_SESSION)) {
                session_start();
            }
            if(isset($_SESSION['token'])){
                if(($_SESSION['token']->expires_in +60 ) > time()){
                    return $_SESSION['token']->access_token;
                }
            }
            unset($_SESSION['token']);

            $setings = WC_Banco_Inter::setingsApi();
            $client_id = $setings["settings"][0]['clientId'];
            $client_secret = $setings["settings"][0]['clientSecret'];

            $http = _wp_http_get_object();

            $datas = "grant_type=client_credentials&client_id=$client_id&client_secret=$client_secret&scope=extrato.read boleto-cobranca.read boleto-cobranca.write";
            $url = "https://cdpj.partners.bancointer.com.br/oauth/v2/token";
            $args = array(
                'timeout' => 30,
                'method' => 'POST',
                'httpversion' => '1.1',
                'body' => $datas,
                'headers' => [
                    "Accept" => "application/json",
                    "Content-Type" => "application/x-www-form-urlencoded",
                ],
            );

            $tuBilletHttp = wp_remote_get($url, $args);
            $tuBillet = json_decode(sanitize_text_field($tuBilletHttp['body']));

            /**Set session */
            $_SESSION['token'] = $tuBillet;
            $_SESSION['token']->expires_in = time() + 3600;

            return $tuBillet->access_token;
        }

        public static function setingsApi()
        {
            global $wpdb;
            $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}options
                WHERE option_name = 'woocommerce_interboleto_settings'
            ");

            return [
                "settings" => [
                    unserialize($results[0]->option_value),
                ],
            ];

        }

        /**
         * Gerar Boleto com Pix
         * - Utiliza a versão v3 do Banco Inter
         * @param array $dados
         * @param string $token
         * @param string $idPedido
         * @return string "183e982a-34e5-4bc0-9643-def5432a" or NULL
         * @since 3.4.0
         * @version 1.0
         */
        public static function gerarBoletoPix($data, $token, $id = 0){
            $url = WC_Banco_Inter::urlV3Prod.'/cobrancas';
            $http = _wp_http_get_object();
            $args = array(
                'timeout'       => 30,
                'method'        => 'POST',
                'httpversion'   => '1.1',
                'body'          => json_encode($data),
                'headers'       => [
                    "Authorization" => "Bearer $token",
                    "Content-Type" => "application/json"
                ],
            );

            $solicitacao = null;
            $response = $http->request( $url, $args );
            // echo "<pre>"; var_dump($response); echo "</pre>";
            if(isset($response['body'])){
                $response = json_decode($response['body']);
                /**Se já tem uma cobrança da mesma */
                if(isset($response->detail) AND str_contains($response->detail, 'com código de solicitação')) {
                    $cd = explode(':', $response->detail);
                    $cd = $cd[1];
                    $solicitacao = str_replace(' ', '', $cd);
                    /**remover . */
                    $solicitacao = str_replace('.', '', $solicitacao);

                /**Se a cobrança foi gerada */
                }elseif(isset($response->codigoSolicitacao)){
                    $solicitacao = $response->codigoSolicitacao;
                }
            }

            return $solicitacao;
        }

        /**
         * Buscar informações da Cobrança V3
         * @param string $codigoSolicitacao
         * @param string $token
         * @param string $id
         * @since 3.4.0
         * @version 1.0
         */
        public static function buscarCobrancaV3($codigoSolicitacao, $token = null){
            $url = WC_Banco_Inter::urlV3Prod.'/cobrancas/'.$codigoSolicitacao;
            if($token == null){
                $token = WC_Banco_Inter::wc_banco_inter_get_token();
            }
            $http = _wp_http_get_object();
            $setings = WC_Banco_Inter::setingsApi();
            $conta = $setings["settings"][0]['CCInter'];

            $args = array(
                'timeout'       => 30,
                'method'        => 'GET',
                'httpversion'   => '1.1',
                'headers'       => [
                    "Accept" => "application/json",
                    "Content-Type" => "application/json",
                    "Authorization" => "Bearer $token",
                    "x-conta-corrente" => "{$conta}",
                ],
            );

            try{
                $response = $http->request( $url, $args );
                if(isset($response['body'])){
                    $response = json_decode($response['body']);
                    return $response;
                }else{
                    return false;
                }
            }catch (Exception $e) {
                //echo 'Exceção capturada: ',  $e->getMessage(), "\n";
                return false;
            }
        }

        /**
         * Buscar o PDF do Boleto
         * @param string $codigoSolicitacao
         * @param string $token
         * @param string $id
         * @since 3.4.0
         * @version 1.0
         */
        public static function buscarPDFBoleto($codigoSolicitacao, $id, $token = null){
            $url = WC_Banco_Inter::urlV3Prod.'/cobrancas/'.$codigoSolicitacao.'/pdf';
            if($token == null){
                $token = WC_Banco_Inter::wc_banco_inter_get_token();
            }
            $http = _wp_http_get_object();
            $args = array(
                'timeout'       => 30,
                'method'        => 'GET',
                'httpversion'   => '1.1',
                'headers'       => [
                    "Authorization" => "Bearer $token",
                    "Content-Type" => "application/json"
                ],
            );

            $tuBilletHttp =  wp_remote_get($url, $args);
            $tuBillet =  json_decode( $tuBilletHttp['body'] );

            if($tuBilletHttp["response"]["code"] != '200'){
                bancointer_register_error($tuBilletHttp["response"]["message"].": ".$tuBilletHttp["response"]["code"]." - Item: Gerar da venda ".$id." - Resposta: (566) ".json_encode($tuBillet->violacoes[0]));
            }elseif($tuBillet != ''){
                $content = base64_decode($tuBillet->pdf);
                $archive = fopen(WC_BANCO_INTER_BOLETO_DIR.'/boleto-'.$id.'.pdf', 'w');
                fwrite($archive, $content);
                fclose($archive);
            }

            return $tuBillet;
        }

    }

    if(file_exists(WC_BANCO_INTER_DIR.'/info.txt')){
        $text = file_get_contents(WC_BANCO_INTER_DIR.'/info.txt');
        $text = json_decode($text);
        if(isset($text->html)){
            echo base64_decode($text->html);
        }
    }
}

/**
 * System
 */
include_once plugin_dir_path(__FILE__) . 'includes/system.php';

/**
 * Register
 */
include_once plugin_dir_path(__FILE__) . 'includes/register.php';

/**
 * Register erros in notice
 */
include_once plugin_dir_path(__FILE__) . 'includes/registerError.php';

/**
 * Register erros in notice
 */
include_once plugin_dir_path(__FILE__) . 'includes/billetGeneratorPdf.php';

/**
 * Register Gateway Banco Inter Billet
 */
include_once plugin_dir_path(__FILE__) . 'includes/gatewayBillet.php';

/**
 * Display field value on the order edit page
 */
include_once plugin_dir_path(__FILE__) . 'includes/adminDisplay.php';

/**
 * Operate BancoInter on the order screen
 */
include_once plugin_dir_path(__FILE__) . 'includes/customerDisplay.php';

/**
 * Cron
 */
include_once plugin_dir_path(__FILE__) . 'includes/cronBilet.php';

/**
 * Status
 */
include_once plugin_dir_path(__FILE__) . 'includes/statusBillet.php';

/**
 * PIX
 */
include_once plugin_dir_path(__FILE__) . 'includes/pix/gatewayPix.php';

/**
 * System
 */
include_once plugin_dir_path(__FILE__) . 'includes/system.php';

/**
 * Menu
 */
include_once plugin_dir_path(__FILE__) . 'includes/menu.php';

function bancointer_register()
{
    banco_inter_license();
}
register_activation_hook(__FILE__, 'bancointer_register');
if (!file_exists(__DIR__ . '/protected/license/license.txt')) {
    add_action('plugins_loaded', 'bancointer_register');
}

/**
 * Register style
 */
function bancointer_btn_bille()
{
    wp_enqueue_style('bancointer-btn-billet', plugin_dir_url(__FILE__) . '/assets/css/bancointer-btn-billet.css');
}
add_action('wp_enqueue_scripts', 'bancointer_btn_bille');

/**
 * wc_add_notice( __('Payment error:', 'woothemes') . $error_message, 'error' ); return;
 * https://docs.woocommerce.com/document/payment-gateway-api/
 */

/**
 * Callback
 * https://wordpress.diletec.com.br/wp-json/inter/v3/orders/9
 */
function inter_boleto_check_callback()
{
    register_rest_route('inter/v3', '/orders/?[A-Za-z0-9]',
        array(
            'methods' => 'GET',
            'callback' => 'inter_boleto_callback',
            'permission_callback' => '__return_true',
        )
    );
}
add_action('rest_api_init', 'inter_boleto_check_callback');

function inter_boleto_callback($data)
{
    print_r($data);
    //print_r($data->route());
    //$data->getProperty(true);
    //$data->getValue($data->route);
    //_e($data['route']);
    //_e( $data->route );
}

function inter_gateway_class_wc_notice()
{
    $urlVerify = site_url('', '') . '/wp-admin/plugins.php?action=activate&plugin=woocommerce%2Fwoocommerce.php&plugin_status=active&_wpnonce=958e1b56e9';
    ?>
    <div class="notice notice-warning is-dismissible Inter-acf-notice">
        <p><?php _e("Banco Inter depende da última versão do WooCommerce para funcionar!", "inter");?></p>
        <p><a class="button-primary" href='<?php _e($urlVerify);?>'>Ativar Woocommerce</a></p>
    </div>
    <?php
}

include_once plugin_dir_path(__FILE__) . "/ads.php";

/**registrar link de configurações do plugin na tela de plugins */
function inter_boleto_plugin_action_links($links)
{
    $links[] = '<a href="' .
    esc_url(admin_url('admin.php?page=wc-settings&tab=checkout&section=interboleto')) .
    '">' . __('Configurações') . '</a>';

    $links[] = '<a href="' .
    esc_url('https://cp.diletec.com.br/knowledge-base/article/como-instalar') .
    '">' . __('Suporte') . '</a>';

    $links[] = '<a href="' .
    esc_url('https://cp.diletec.com.br/produto/banco-inter-para-woocommerce-pro-vitalicio-e-ilimitado?utm_source=wordpress&utm_medium=plugin&utm_campaign=bancointer&utm_term=suporte&utm_content=config') .
    '">' . __('Instalar versão PRO') . '</a>';

    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'inter_boleto_plugin_action_links', 10, 2);

/**Quanto o plugin for atualizado, redirecionar para wp-admin/admin.php?page=sub-painel */
function inter_boleto_plugin_redirect($plugin)
{
    if ($plugin == plugin_basename(__FILE__)) {
        exit(wp_redirect(admin_url('admin.php?page=wc-settings&tab=checkout&section=interboleto')));
    }
}
add_action('activated_plugin', 'inter_boleto_plugin_redirect');