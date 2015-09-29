<?php
	class WC_Gateway_Dotpay extends WC_Payment_Gateway {

	    // special test customer ID for sandbox
	    const DOTPAY_PAYMENTS_TEST_CUSTOMER = '';
        const DOTPAY_PAYMENTS_TEST_CUSTOMER_PIN = '';
            

	    // Dotpay IP address
	    const DOTPAY_IP = '195.150.9.37';

	    // Dotpay URL
	    const DOTPAY_URL = 'https://ssl.dotpay.pl';

	    // Gateway name
	    const PAYMENT_METHOD = 'dotpay';

		/**
		* initialise gateway with custom settings
		*/
		public function __construct() {

			global $woocommerce;

			$this->id = self::PAYMENT_METHOD;
			$this->icon = WOOCOMMERCE_DOTPAY_PLUGIN_URL . 'resources/images/dotpay.png';
			$this->has_fields = false;
			$this->title = 'Dotpay';
			$this->description = __('Credit card payment via Dotpay', 'dotpay-payment-gateway');
			$this->init_form_fields();
			$this->init_settings();
			//Actions
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );
			add_action( 'woocommerce_api_' . strtolower( get_class( $this ) ), array( $this, 'check_dotpay_response_legacy' ) );
		} 

		public function init_form_fields() {
			$this->form_fields = array(
				'enabled' => array(
					'title' => __('Enable/Disable', 'woocommerce'),
					'type' => 'checkbox',
					'label' => __('Enable Dotpay Payment', 'dotpay-payment-gateway'),
					'default' => 'yes'
				),
				'dotpay_id' => array(
					'title' => __('Dotpay customer ID', 'dotpay-payment-gateway'),
					'type' => 'text',
					'default' => self::DOTPAY_PAYMENTS_TEST_CUSTOMER,
				),
				'dotpay_pin' => array(
					'title' => __('Dotpay customer PIN', 'dotpay-payment-gateway'),
					'type' => 'text',
					'default' => self::DOTPAY_PAYMENTS_TEST_CUSTOMER_PIN,
				),          
				'dotpay_test' => array(
					'title' => __('Testing environment (test_payment)', 'dotpay-payment-gateway'),
                                        'label' => __('Only payment simulation. Forced PLN.', 'dotpay-payment-gateway'),
					'type' => 'checkbox',
                                        'default' => 'yes'
				),     
				'dotpay_chk' => array(
					'title' => __('CHK Blockade', 'dotpay-payment-gateway'),
                                        'label' => __('Secure payment parameters (optional)', 'dotpay-payment-gateway'),
					'type' => 'checkbox',
                                        'default' => 'no'
				),                              
				'title' => array(
					'title' => __('Title', 'woocommerce'),
					'type' => 'text',
					'description' => __('This controls the title which user sees during checkout.', 'dotpay-payment-gateway'),
					'default' => 'Dotpay',
					'desc_tip' => true,
				),
				'description' => array(
					'title' => __('Customer Message', 'woocommerce'),
					'type' => 'textarea',
					'default' => '',
				)
			);
		}

		function process_payment( $order_id ) {
			global $woocommerce;
			
			$order = new WC_Order( $order_id );

			$order->reduce_order_stock();

			$woocommerce->cart->empty_cart();

			return array(
				'result' 	=> 'success',
				'redirect'	=> $order->get_checkout_payment_url( true )
			);
		}

		function receipt_page( $order ) {
			//echo '<p>' . __( 'Thank you - your order is now pending payment. You should be automatically redirected to Dotpay to make payment.', 'dotpay-payment-gateway' ) . '</p>';
			echo $this->generate_dotpay_form_legacy( $order );
		}

		function generate_dotpay_form_legacy( $order_id ) {
			$order = new WC_Order( $order_id );
                        
			$dotpay_url = self::DOTPAY_URL;
                        if ($this->get_option( 'dotpay_test' ) == "yes")
                            $dotpay_url .= "/test_payment/";
                        
                        $payment_currency = get_woocommerce_currency();
                        if ($this->get_option( 'dotpay_test' ) == "yes")
                            $payment_currency = "PLN";
                                                
                        $dotpay_id = $this->get_option( 'dotpay_id' );
                        $firstname = $order->billing_first_name;
                        $lastname = $order->billing_last_name;
                        $street = $order->billing_address_1;
                        $street_n1 = $order->billing_address_2;
                        $city = $order->billing_city;
                        $postcode = $order->billing_postcode;
                        $country = $order->billing_country;
                        $email = $order->billing_email;
                        $control = esc_attr( $order_id );
							$amount_tmp = str_replace(',', '.', $order->get_total());
                        $amount = round($amount_tmp,2);
                        $return_url = $this->get_return_url( $order );
                        $notify_url = str_replace( 'https:', 'http:', add_query_arg( 'wc-api', 'WC_Gateway_Dotpay', home_url( '/' )));
                        
                        $payment_type = 0;
                        $description = get_bloginfo('name').': Order id: '.esc_attr( $order_id );
                        $chk = $dotpay_id.$amount.$payment_currency.$description.$control.$this->get_option( 'dotpay_pin' );
                        $chk = rawurlencode($chk);
                        if ($this->get_option( 'dotpay_chk' ) == "yes")
                            $signature = hash('md5', $chk);
                        $api_version = "legacy";
			wc_enqueue_js( '
				$.blockUI({
						message: "' . esc_js( __( 'Thank you for your order. We are now redirecting you to Dotpay to make payment.', 'dotpay-payment-gateway' ) ) . '",
						baseZ: 99999,
						overlayCSS:
						{
							background: "#fff",
							opacity: 0.6
						},
						css: {
							padding:        "20px",
							zindex:         "9999999",
							textAlign:      "center",
							color:          "#555",
							border:         "3px solid #aaa",
							backgroundColor:"#fff",
							cursor:         "wait",
							lineHeight:		"24px",
						}
					});
				jQuery("#submit_dotpay_payment_form").click();
			' );

	        ob_start();
	            include(WOOCOMMERCE_DOTPAY_PLUGIN_DIR . '/frontend/templates/woocommerce-dotpay-payment-button.tpl.php');
	            $html = ob_get_contents();
	        ob_end_clean();

	        return $html;

		}

		function check_dotpay_response_legacy() {
			global $woocommerce;
                        $data = $_POST;
                        $order = new WC_Order($data['control']);
                        
                        if($_SERVER['REMOTE_ADDR'] <> self::DOTPAY_IP)
                                wp_redirect( $this->get_return_url( $order ) );

                        $data["dotpay_pin"] = $this->get_option('dotpay_pin');
                        if(!$this->check_urlc_legacy($data))
                                die('WooCommerce - Wrong MD5, check PIN!');                        

                        $totalAmount = $order->get_total();
                        $totalAmount = str_replace(',', '.', $totalAmount);
                        $totalAmount = round($totalAmount,2);
                        $dotpay_amount = round($data['orginal_amount'],2);
                        if ($totalAmount <> $dotpay_amount) 
                                die('WooCommerce - INCORRECT AMOUNT '.$totalAmount.' <> '.$dotpay_amount);                                        

                        $totalAmount = number_format($totalAmount, 2,'.', '');
						$totalAmount = str_replace(',', '', $totalAmount);
                        $totalAmount .= " ".get_woocommerce_currency();
                        $orginal_amount = trim($data['orginal_amount']);
                        if ($totalAmount <> $orginal_amount) 
                                die('WooCommerce - INCORRECT ORG. AMOUNT '.$totalAmount.' <> '.$orginal_amount);                        
			
                        
                        switch($data['t_status']) {
                                case 1:
                                        $order->update_status('pending');
                                        break;
                                case 2:
                                        $order->update_status('processing');
                                        break;
                                case 3:
                                        $order->update_status('failed');
                                        break;
                                case 4:
                                        $order->update_status('failed');
                                        break;
                                case 5:
                                        $order->update_status('on-hold');
                                        break;
                                default:
                                        die('WooCommerce - Wrong t_status - '.$data['t_status']);
                        }
                        die('OK');            
		}

                static public function check_urlc_legacy($data) 
                {
                    $signature =
                        $data['dotpay_pin'].":".
                        $data['id'].":".
                        $data['control'].":".
                        $data['t_id'].":".
                        $data['amount'].":". 
                        $data['email'].":".
                        $data['service'].":".  
                        $data['code'].":".
                        $data['username'].":".
                        $data['password'].":".
                        $data['t_status'];
                    $signature=hash('md5', $signature);
                    return ($data['md5'] == $signature);
                } 
	}