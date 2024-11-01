<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/** Cria os diretórios e registra o define */
if ( ! defined( 'WC_BANCO_INTER_DIR' ) ) {
    define( 'WC_BANCO_INTER_DIR', WP_CONTENT_DIR . '/banco-inter/' );
}
if(!is_dir(WC_BANCO_INTER_DIR)){
    mkdir(WC_BANCO_INTER_DIR, 0755, true);
}

/**errors */
if ( ! defined( 'WC_BANCO_INTER_ERRORS_DIR' ) ) {
    define( 'WC_BANCO_INTER_ERRORS_DIR', WC_BANCO_INTER_DIR . 'errors/' );
}
if(!is_dir(WC_BANCO_INTER_ERRORS_DIR)){
    mkdir(WC_BANCO_INTER_ERRORS_DIR, 0755, true);
    $file = fopen(WC_BANCO_INTER_ERRORS_DIR . 'errors.log', 'a');
    fclose($file);
}
/**errors File */
if ( ! defined( 'WC_BANCO_INTER_ERRORS_FILE' ) ) {
    define( 'WC_BANCO_INTER_ERRORS_FILE', WC_BANCO_INTER_ERRORS_DIR . 'errors.log' );
}

/**File errorsNotice.log in WC_BANCO_INTER_ERRORS_DIR */
if ( ! defined( 'WC_BANCO_INTER_ERRORS_NOTICE_FILE' ) ) {
    define( 'WC_BANCO_INTER_ERRORS_NOTICE_FILE', WC_BANCO_INTER_ERRORS_DIR . 'errorsNotice.log' );
}

/**Cron */
if ( ! defined( 'WC_BANCO_INTER_CRON_DIR' ) ) {
    define( 'WC_BANCO_INTER_CRON_DIR', WC_BANCO_INTER_DIR . 'cron/' );
}
if(!is_dir(WC_BANCO_INTER_CRON_DIR)){
    mkdir(WC_BANCO_INTER_CRON_DIR, 0755, true);
    $file = fopen(WC_BANCO_INTER_CRON_DIR . 'cron.txt', 'a');
    fclose($file);
}
/**CRON File */
if ( ! defined( 'WC_BANCO_INTER_CRON_FILE' ) ) {
    define( 'WC_BANCO_INTER_CRON_FILE', WC_BANCO_INTER_CRON_DIR . 'cron.txt' );
}

/** Boleto */
if ( ! defined( 'WC_BANCO_INTER_BOLETO_DIR' ) ) {
    define( 'WC_BANCO_INTER_BOLETO_DIR', WC_BANCO_INTER_DIR . 'boleto/' );
}
if(!is_dir(WC_BANCO_INTER_BOLETO_DIR)){
    mkdir(WC_BANCO_INTER_BOLETO_DIR, 0755, true);
}

/**DIR protected > license */
if ( ! defined( 'WC_BANCO_INTER_PROTECTED_DIR' ) ) {
    define( 'WC_BANCO_INTER_PROTECTED_DIR', WC_BANCO_INTER_DIR . 'protected/' );
}
if(!is_dir(WC_BANCO_INTER_PROTECTED_DIR)){
    mkdir(WC_BANCO_INTER_PROTECTED_DIR, 0755, true);
}
/**DIR license */
if ( ! defined( 'WC_BANCO_INTER_LICENSE_DIR' ) ) {
    define( 'WC_BANCO_INTER_LICENSE_DIR', WC_BANCO_INTER_PROTECTED_DIR . 'license/' );
}
if(!is_dir(WC_BANCO_INTER_LICENSE_DIR)){
    mkdir(WC_BANCO_INTER_LICENSE_DIR, 0755, true);
    $file = fopen(WC_BANCO_INTER_LICENSE_DIR . 'license.txt', 'a');
    fclose($file);
    $file = fopen(WC_BANCO_INTER_LICENSE_DIR . 'validate.txt', 'a');
    fclose($file);
}
/**FILE license */
if ( ! defined( 'WC_BANCO_INTER_LICENSE_FILE' ) ) {
    define( 'WC_BANCO_INTER_LICENSE_FILE', WC_BANCO_INTER_LICENSE_DIR . 'license.txt' );
}
/**FILE validate */
if ( ! defined( 'WC_BANCO_INTER_VALIDATE_FILE' ) ) {
    define( 'WC_BANCO_INTER_VALIDATE_FILE', WC_BANCO_INTER_LICENSE_DIR . 'validate.txt' );
}

$upload_dir = wp_upload_dir();
$target_dir = $upload_dir['basedir'] . '/diletec/';
if(!is_dir($target_dir)){
    mkdir($target_dir, 0755, true);
}