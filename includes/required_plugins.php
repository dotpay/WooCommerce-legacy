<?php
	require_once dirname( __FILE__ ) . '/class-tgm-plugin-activation.php';

	add_action( 'tgmpa_register', 'dotpay_payment_gateway_recommended_plugins' );

	function dotpay_payment_gateway_recommended_plugins() {
		$plugins = array(
			array(
				'name'		=> 'WooCommerce - excelling eCommerce',
				'slug'		=> 'woocommerce',
				'required'	=> true,
				'version'	=> '2.1.0',
			),
		);

		tgmpa( $plugins );
	}