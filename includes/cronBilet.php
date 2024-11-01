<?php

/**
 * https://developer.wordpress.org/plugins/cron/scheduling-wp-cron-events/
 * https://developer.wordpress.org/reference/functions/wp_next_scheduled/
*/
function banco_inter_cron_schedule( $schedules ) {
    $schedules['banco_inter_billet_update'] = array(
        'interval' => 3600,
        'display'  => __( 'A cada hora' ),
    );
    return $schedules;
}
add_filter( 'cron_schedules', 'banco_inter_cron_schedule' );

if ( ! wp_next_scheduled( 'banco_inter_cron_hook' ) ) {
    wp_schedule_event( time(), 'banco_inter_billet_update', 'banco_inter_cron_hook' );
}

add_action('banco_inter_cron_hook', 'banco_inter_billet_cron');

function banco_inter_billet_cron(){
    WC_Banco_Inter::reject();
    if(!file_exists(plugin_dir_path( __DIR__ ).'tmp/cron.txt')) {
        $errorsNotice = fopen(plugin_dir_path( __DIR__ ).'tmp/cron.txt', 'a');
        fclose($errorsNotice);
    }

	/**https://stackoverflow.com/questions/45848249/woocommerce-get-all-orders-for-a-product */
    global $wpdb;
    $results = $wpdb->get_results("
        SELECT * FROM {$wpdb->prefix}posts AS wp INNER JOIN
        {$wpdb->prefix}postmeta AS wpm ON (wp.id = wpm.post_id)

        WHERE
        wp.post_type = 'shop_order'
        AND wp.post_status = 'wc-on-hold'
        AND wpm.meta_key = 'nossonumero'
        ORDER BY RAND() LIMIT 3
    ");

    foreach($results as $value){
        if($value->meta_value != ''){
            $numero = $value->meta_value;
            $bancoInter = WC_Banco_Inter::wc_banco_inter_get_pedidos_boletos($numero, $value->ID);
            $content = "[".date("Y-m-d H:i:s")."] - " .'CRON: ID: '.$value->ID.' Numero: '.$numero.' Status: '.$bancoInter   .PHP_EOL;
            $archive = fopen(plugin_dir_path( __DIR__ ).'tmp/cron.txt', 'a');

            fwrite($archive, $content);
            fclose($archive);
        }
    }

}
