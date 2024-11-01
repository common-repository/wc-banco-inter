<?php

add_action ('admin_notices', 'inter_gateway_class_wc_notice_pro');

function inter_gateway_class_wc_notice_pro()
{
    if(isset($_GET['interads']) && $_GET['interads'] == 'false'){
        /**Registrar cookie */
        setcookie('interads', 'false', time() + (3600 * 1), "/");
        return;
    }
    /**Verificar cookie */
    if(isset($_COOKIE['interads']) && $_COOKIE['interads'] == 'false'){
        return;
    }
    /** Buscar o valor total das vendas */
    global $wpdb;

    $total = $wpdb->get_results("SELECT SUM(m.meta_value) AS total
        FROM {$wpdb->prefix}posts AS p, {$wpdb->prefix}postmeta AS m
        WHERE ((p.post_type = 'shop_order' AND p.ID = m.post_id)
        AND m.meta_key = '_order_total') AND p.post_status IN ('wc-completed', 'wc-processing')
        ORDER BY m.post_id ASC
    ");
    /** Economia de 12% do $total tratado para Real Brasileiro */
    // $economia = $total[0]->total * 0.12;
    // $economia = '<b>'.number_format($economia, 2, ',', '.')."</b>";

    /**Valor total das vendas x100 */
    $total = $total[0]->total;
    $economia = '<b>'.number_format($total * 99, 2, ',', '.')."</b>";
    $melhoria10 = '<b>'.number_format($total * 10, 2, ',', '.')."</b>";

	$dateFim = new DateTime('2024-09-30');
	$date = new DateTime();
	$date->getTimestamp();

    $current_user = wp_get_current_user();
    $user_info = get_userdata($current_user->ID);
    $user_email = $user_info->user_email;
    $urlVerify = 'https://cp.diletec.com.br/produto/banco-inter-para-woocommerce-pro-vitalicio-e-ilimitado?utm_source=plugin&utm_medium=banner&utm_campaign=pro&ra=13&email='.$user_email;
    if($date->getTimestamp() <= $dateFim->getTimestamp()){

    ?>
    <div class="updated notice-warning is-dismissible Inter-acf-notice">
        <p style="font-weight: 700; font-size: 18px;"><?php  _e( "Aviso! - O Banco Inter vai descontinuar a API v2 de boleto! Resolva usando a Versão PRO", "inter" ); ?></p>
        <div id="inter-pro-promo-countdown" style="color: red; font-size: 14px;">Promção válida até </div>
        <p style="font-size: 16px;"> <?php _e( "Para continuar vendendo com o Banco Inter, considere utilizar uma das versões PRO deste Plugin, pois as versões PRO estão recebendo o Modelo Boleto Pix V3.", "inter" ); ?></p>
        <p><a class="button button-primary" href='<?php _e($urlVerify); ?>'>Ativar a Versão PRO</a>
        <a class="button button-warning" href="https://wa.me/5531993320829">Fale com o time</a>
        <a style="display: none;" class="button button-warning" href="<?php _e($urlVerify); ?>">Conhecer o Plugin</a>
        <a class="button btn-light" href="/wp-admin/index.php?interads=false">Desativar Ads</a>
        <a style="display: none;" class="button button-primary" href="https://cp.diletec.com.br/authentication/register?ra=13&email=<?php _e($user_email);?>">Torne-se um Afiliado Diletec e Ganhe até 50% pelas indicações</a>
        <a style="display: none;" class="button btn-primary" href='https://www.diletec.com.br/whatsapp-api-para-wordpress-e-woocommerce/?utm_source=plugin&utm_medium=banner&utm_campaign=pro'>Plugin de WhastApp</a>
        <a style="display: none;" class="button btn-warning" href='https://lp.diletec.com.br/sistema-de-whatsapp?utm_source=plugin&utm_medium=banner&utm_campaign=pro'>Gestão de WhatsApp</a>
        <a style="display: none;" class="button btn-warning" href='https://cp.diletec.com.br/knowledge-base/article/o-que-tem-na-versao-pro?utm_source=plugin&utm_medium=banner&utm_campaign=pro'>Banco Inter PRO</a>
	    </p>
    </div>
    <?php
    }else{
    ?>
    <div class="notice notice-warning is-dismissible Inter-acf-notice">
        <p style="font-weight: 700; font-size: 18px;"><?php  _e( "Seu Plugin do Banco Inter não está fazendo a baixa automática!", "inter" ); ?></p>
        <div id="inter-pro-promo-countdown" style="display:none;">Promção válida até </div>
        <p style="font-size: 16px;"> <?php _e( "Ative a baixa automática do Plugin e tenha total comodidade com o poder da integração de ponta a ponta. E ainda libere o uso do HPOS!", "inter" ); ?></p>
        <p><a class="button button-primary" href='<?php _e($urlVerify); ?>'>Ativar Versão PRO</a>
        <a class="button button-warning" href="https://wa.me/5531993320829">Suporte</a>
        <a style="display: none;" class="button button-primary" href='https://cp.diletec.com.br/ecommerce?utm_source=plugin+free&utm_medium=banner&utm_campaign=pro&utm_id=inter'>O Plugin que você procura está aqui</a>
        <a style="" class="button btn-light" href="/wp-admin/index.php?interads=false">Remover aviso</a>
        <a style="display: none;" class="button button-primary" href='https://cp.diletec.com.br/produto/banco-inter-para-woocommerce-pro-vitalicio-e-ilimitado?utm_source=plugin+free&utm_medium=banner&utm_campaign=pro&utm_id=inter'>Versão PRO Vitalícia</a>
        </p>
    </div>
    <?php
    }
}

function inter_pro_promo_countdown(){
    $plugin_dir = plugin_dir_url( __FILE__ );
    wp_enqueue_script( 'inter-pro-promo-countdown', "{$plugin_dir}assets/js/countdown.js", [], '1.0.7', true );
}
add_action( 'admin_enqueue_scripts', 'inter_pro_promo_countdown' );