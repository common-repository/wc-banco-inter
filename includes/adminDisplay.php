<?php

/**
 * Display field value on the order edit page
 */
add_action( 'woocommerce_admin_order_data_after_billing_address', 'wc_banco_inter_checkout_field_display_admin_order_meta', 10, 1 );
function wc_banco_inter_checkout_field_display_admin_order_meta($order){
    $method = get_post_meta( $order->get_id(), '_payment_method', true );
    if($method != 'interboleto')
        return;

    $boleto = get_post_meta( $order->get_id(), 'boleto', true );
    $nossonumero = get_post_meta( $order->get_id(), 'nossonumero', true );

    _e( '<p><strong>'.__( 'Boleto Number' ).':</strong> ' . $boleto . '</p>' );
    _e( '<p><strong>'.__( 'Nosso número').':</strong> ' . $nossonumero . '</p>' );
}

