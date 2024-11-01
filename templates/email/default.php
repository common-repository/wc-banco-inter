<?php
/**
 * Default email instructions.
 *
 * @author  daniel.souza@diletec.com.br
 * @package Wc-Banco-Inter/Templates
 * @version 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

_e( 'Payment', 'banco-inter' );

_e( "\n\n" );

if ( 2 == $type ) {

	_e( 'Please use the link below to view your Banking Ticket, you can print and pay in your internet banking or in a lottery retailer:', 'banco-inter' );

	_e( "\n" );

	_e( esc_url( $link ) );

	_e( "\n" );

	_e( 'After we receive the ticket payment confirmation, your order will be processed.', 'banco-inter' );

} elseif ( 3 == $type ) {

	_e( 'Please use the link below to make the payment in your bankline:', 'banco-inter' );

	_e( "\n" );

	_e( esc_url( $link ) );

	_e( "\n" );

	_e( 'After we receive the confirmation from the bank, your order will be processed.', 'banco-inter' );

} else {

	_e( sprintf( __( 'You just made the payment in %s using the %s.', 'banco-inter' ), $installments . 'x', $method ) );

	_e( "\n" );

	_e( 'As soon as the credit card operator confirm the payment, your order will be processed.', 'banco-inter' );

}

_e( "\n\n****************************************************\n\n" );
