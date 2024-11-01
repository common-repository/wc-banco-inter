<?php
/**
 * Register a custom page.
 */

 add_action( 'admin_menu', 'wc_banco_inter_admin_menu' );

 function wc_banco_inter_admin_menu() {
    /** Add awaiting-mod count-1 */
    $awaiting_mod = '1';
    $awaiting_mod = $awaiting_mod ? "<span class='awaiting-mod count-1'><span class='pending-count'>$awaiting_mod</span></span>" : '';

    add_menu_page('Banco Inter','Banco inter '.$awaiting_mod,'manage_options','wc-banco-inter','wc_banco_inter_sub_registro','dashicons-bank',7);
    add_submenu_page('wc-banco-inter','WhatsApp','WhatsApp','manage_options','https://www.diletec.com.br/whatsapp-api-para-wordpress-e-woocommerce/?utm_source=plugin&utm_medium=link-menu&utm_campaign=pro');
    add_submenu_page('wc-banco-inter','Painel','Painel '.$awaiting_mod,'manage_options','sub-painel','wc_banco_inter_sub_pro');
    add_submenu_page('wc-banco-inter','Boletos','Boletos','manage_options','sub-boletos','wc_banco_inter_sub_pro');
    add_submenu_page('wc-banco-inter','Gerar Boletos','Gerar Boletos','manage_options','sub-gerar-boletos','wc_banco_inter_sub_pro');
    add_submenu_page('wc-banco-inter','Configurações','Configurações','manage_options', site_url('', '').'/wp-admin/admin.php?page=wc-settings&tab=checkout&section=interboleto');
    add_submenu_page('wc-banco-inter','Status','Status','manage_options','sub-status','wc_banco_inter_sub_pro');
    add_submenu_page('wc-banco-inter','Log','Log','manage_options','sub-log','wc_banco_inter_sub_pro');
    add_submenu_page('wc-banco-inter','Registro','Registro','manage_options','sub-registro','wc_banco_inter_sub_registro');
    add_submenu_page('wc-banco-inter','Suporte','Suporte','manage_options','https://cp.diletec.com.br/knowledge-base/category/banco-inter-para-woocommerce');
    unset($GLOBALS['submenu']['wc-banco-inter'][0]);
 }

 include_once(plugin_dir_path(__DIR__)."/admin/register.php");
 include_once(plugin_dir_path(__DIR__)."/admin/proVersion.php");