<?php
/**
 * Order details table shown in emails.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-order-details.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 3.7.0
 */

_e( '<div style="margin-bottom: 40px;">
	<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: Helvetica, Roboto, Arial, sans-serif;" border="1">
		<thead>
			<tr>
				<th class="td" scope="col" style="text-align:left;">'. _e_e( 'Product', 'woocommerce' ).'</th>
				<th class="td" scope="col" style="text-align:left;">'. _e_e( 'Quantity', 'woocommerce' ).'</th>
				<th class="td" scope="col" style="text-align:left;">'. _e_e( 'Price', 'woocommerce' ).'</th>
			</tr>
		</thead>
		<tbody>
			'.
			wc_get_email_order_items( 
				$order,
				array(
					'show_sku'      => $sent_to_admin,
					'show_image'    => false,
					'image_size'    => array( 32, 32 ),
					'plain_text'    => $plain_text,
					'sent_to_admin' => $sent_to_admin,
				)
            )
        .'    
		</tbody>
		<tfoot>
			Ok, aqui vem as instruções
		</tfoot>
	</table>
</div>');

