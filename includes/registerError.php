<?php

/**
 * Register erros
 */
function bancointer_register_error($error){
    /**Creat archive of error */
    $content = "[".date("Y-m-d H:i:s")."] - " . $error.PHP_EOL;
    $archive = fopen(__DIR__.'/../tmp/errors.log', 'a');
    fwrite($archive, $content);
    fclose($archive);
    if(!file_exists(__DIR__.'/../tmp/errorsNotice.log')) {
        $errorsNotice = fopen(__DIR__.'/../tmp/errorsNotice.log', 'a');
        fclose($errorsNotice);
    }
}

/**
 * Register erros in notice
 */
function bancointer_acf_notice() {
    $urlVerify = site_url( '', '' ).'/wp-admin/admin.php?page=wc-settings&tab=checkout&section=interboleto';
    ?>
    <div class="notice notice-warning is-dismissible interboleto-acf-notice">
        <p><?php  _e( "Please, verify if your data's API! <a href=\"".$urlVerify."\">Check my information - Banco Inter</a>", "interboleto" ); ?></p>
    </div>
    <?php
}
if(file_exists(__DIR__.'/../tmp/errorsNotice.log')) {
    //add_action ('admin_notices', 'bancointer_acf_notice');
    /**Delete archive of error */
    if(isset($_GET['section']) AND $_GET['section'] == 'interboleto'){
        unlink(__DIR__.'/../tmp/errorsNotice.log');
    }
}


function bancointer_acf_notice_dainamic($alertsBancoInter) {
    if($alertsBancoInter === null){ return false;}
    if(isset($_POST['woocommerce_interboleto_enabled'])){ return false;}
    //https://developer.wordpress.org/reference/hooks/admin_notices/
    // $htm = _e('<div class="notice notice-error is-dismissible interboleto-acf-notice-dainamic"><p>');
    // $htm .= is_string($alertsBancoInter);
    // $htm .= _e('</p></div>');
    // _e($htm);
    _e( sprintf( __( '<div class="notice notice-error is-dismissible interboleto-acf-notice-dainamic"><p>'.$alertsBancoInter.'</p></div>', 'bancointer') ) );
    // print_r($htm);

}

