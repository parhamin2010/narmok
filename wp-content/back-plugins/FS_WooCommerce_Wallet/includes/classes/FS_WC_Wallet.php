<?php


class FS_WC_Wallet {
    
    // Plugin main instance
    public static $_instance = null;
    
    // Array of notification and errors to dispaly
    public $notices = array();
	
	public static $remove_partial_payment = '1';
    
    
    function __construct() {
        
        // check if WooCommerce is intalled and active
        if(! in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            
            $this->notices[] = __('WooCommerce need to be installed and activated to be able to use "WooCommerce Wallet".', 'fsww');
            
        }
		
		if(isset($_COOKIE['fsww_remove_partial_payment']) && $_COOKIE['fsww_remove_partial_payment'] == '1') {
			
			self::$remove_partial_payment = '1';
			
		}
		
		if(isset($_COOKIE['fsww_remove_partial_payment']) && $_COOKIE['fsww_remove_partial_payment'] == '0') {
			
			self::$remove_partial_payment = '0';
			
		}
        
        
        // Add actions
        $this->add_actions();
        
        // Add filters
        $this->add_filters();
        
        // Add AJAX actions
        $this->plugin_ajax_actions();
        
        // Add shortcodes
        $this->add_shortcodes();
        
        // Add my account endpoints
        $this->add_endpoints();

        add_action('wp_loaded', array($this, 'wallet_topup'), 20);

        if(get_option('fsww_disable_payment_methods', 'off') == 'on') {
            add_filter('woocommerce_available_payment_gateways', array($this, 'disable_payment_methods'));
        }

        add_filter('woocommerce_is_purchasable', array($this, 'make_recharge_product_purchasable'), 10, 2);
        add_action('woocommerce_before_calculate_totals', array($this, 'set_recharge_product_price'));
        
    }

    /**
     * Topup
     */
    public function wallet_topup() {

        if (isset($_POST['fsww_balance_to_add']) && !empty($_POST['fsww_balance_to_add'])) {
            $is_valid = $this->validate_topup_amount($_POST['fsww_balance_to_add']);
            if ($is_valid['is_valid']) {
                add_filter('woocommerce_add_cart_item_data', array($this, 'add_fsww_product_price_to_cart_item_data'), 10, 2);
                $product = self::fsww_get_wallet_rechargeable_product();
                if ($product) {
                    wc()->cart->empty_cart();
                    wc()->cart->add_to_cart($product->get_id());
                    $redirect_url = apply_filters('fsww_redirect_to_checkout_after_added_amount', true) ? wc_get_checkout_url() : wc_get_cart_url();
                    wp_safe_redirect($redirect_url);
                    exit();
                }
            } else {
                wc_add_notice($is_valid['message'], 'error');
            }
        }

    }

    public static function fsww_get_wallet_rechargeable_product() {
        self::create_product_if_not_exist();
        return wc_get_product(apply_filters( 'fsww_rechargeable_product_id', get_option( '_fsww_recharge_product' ) ) );
    }

    /**
     * Make rechargeable product purchasable
     * @param boolean $is_purchasable
     * @param WC_Product object $product
     * @return boolean
     */
    public function make_recharge_product_purchasable($is_purchasable, $product) {
        $wallet_product = self::fsww_get_wallet_rechargeable_product();
        if ($wallet_product) {
            if ($wallet_product->get_id() == $product->get_id()) {
                $is_purchasable = true;
            }
        }
        return $is_purchasable;
    }

    /**
     * Set topup product price at run time
     * @param OBJECT $cart
     * @return NULL
     */
    public function set_recharge_product_price($cart) {
        $product = self::fsww_get_wallet_rechargeable_product();
        if (!$product && empty($cart->cart_contents)) {
            return;
        }
        foreach ($cart->cart_contents as $key => $value) {
            if (isset($value['recharge_amount']) && $value['recharge_amount'] && $product->get_id() == $value['product_id']) {
                $value['data']->set_price($value['recharge_amount']);
            }
        }
    }

    /**
     * WooCommerce add cart item data
     * @param array $cart_item_data
     * @param int $product_id
     * @return array
     */
    public function add_fsww_product_price_to_cart_item_data($cart_item_data, $product_id) {
        $product = wc_get_product($product_id);
        if (isset($_POST['fsww_balance_to_add']) && $product) {
            $recharge_amount = apply_filters('fsww_rechargeable_amount', round($_POST['fsww_balance_to_add'], 2));
            $cart_item_data['recharge_amount'] = $recharge_amount;
        }
        return $cart_item_data;
    }

    public function validate_topup_amount($amount) {

        $min = get_option('fsww_deposit_input_min', '0');
        $max = get_option('fsww_deposit_input_max', '1000');

        if($amount > $max) {
            return array(
                'is_valid' => false,
                'message' => 'Invalid amount!'
            );
        }

        if($amount < $min) {
            return array(
                'is_valid' => false,
                'message' => 'Invalid amount!'
            );
        }

        if($amount > 0) {
            return array(
                'is_valid' => true,
                'message' => ''
            );
        }

        return array(
            'is_valid' => false,
            'message' => __('Top up can\'t be a 0 or a negative value', 'fsww')
        );

    }

    /**
     * Create rechargeable product if not exist
     */
    public static function create_product_if_not_exist(){
        if ( !wc_get_product( get_option( '_fsww_recharge_product' ) ) ) {
            self::create_recharge_product();
        }
    }

    /**
     * create rechargeable product
     */
    private static function create_recharge_product() {
        $product_args = array(
            'post_title' => wc_clean( 'Wallet Topup' ),
            'post_status' => 'private',
            'post_type' => 'product',
            'post_excerpt' => '',
            'post_content' => stripslashes(html_entity_decode( 'Auto generated product for wallet recharge please do not delete or update.', ENT_QUOTES, 'UTF-8' ) ),
            'post_author' => 1
        );
        $product_id = wp_insert_post( $product_args );
        if ( ! is_wp_error( $product_id ) ) {
            $product = wc_get_product( $product_id );
            wp_set_object_terms( $product_id, 'simple', 'product_type' );
            update_post_meta( $product_id, '_stock_status', 'instock' );
            update_post_meta( $product_id, 'total_sales', '0' );
            update_post_meta( $product_id, '_downloadable', 'yes' );
            update_post_meta( $product_id, '_virtual', 'yes' );
            update_post_meta( $product_id, '_regular_price', '0' );
            update_post_meta( $product_id, '_sale_price', '' );
            update_post_meta( $product_id, '_purchase_note', '' );
            update_post_meta( $product_id, '_featured', 'no' );
            update_post_meta( $product_id, '_weight', '' );
            update_post_meta( $product_id, '_length', '' );
            update_post_meta( $product_id, '_width', '' );
            update_post_meta( $product_id, '_height', '' );
            update_post_meta( $product_id, '_sku', '' );
            update_post_meta( $product_id, '_product_attributes', array() );
            update_post_meta( $product_id, '_sale_price_dates_from', '' );
            update_post_meta( $product_id, '_sale_price_dates_to', '' );
            update_post_meta( $product_id, '_price', '' );
            update_post_meta( $product_id, '_sold_individually', 'yes' );
            update_post_meta( $product_id, '_manage_stock', 'no' );
            update_post_meta( $product_id, '_backorders', 'no' );
            update_post_meta( $product_id, '_stock', '' );
            if (version_compare(WC_VERSION, '3.0', '>=' ) ) {
                $product->set_reviews_allowed(false );
                $product->set_catalog_visibility( 'hidden' );
                $product->save();
            }

            update_option( '_fsww_recharge_product', $product_id );
        }
    }


    public function disable_payment_methods( $available_gateways ) {

        global $woocommerce;


        if ( !is_admin() ) {

            if(isset($available_gateways['fsww'])) {
                foreach ($available_gateways as $key => $value) {
                    if($key != 'fsww') {
                        unset($available_gateways[$key]);
                    }
                }
            }

        }


        return $available_gateways;

    }
	
	public function run_functions() {
		
		if(isset($_POST['fsww-send-money']) && $_POST['fsww-send-money'] == "fsww") {
			
			$this->process_send_money();
			
		}
		
		if(isset($_POST['fsww-request-withrawal']) && $_POST['fsww-request-withrawal'] == "fsww") {
			
			$this->process_withrawal_request();
			
		}
		
		if(isset($_POST['fsww_accept_donations']) && $_POST['fsww_accept_donations'] == "fsww") {
			
			$this->fsww_accept_donations();
			
		}
		
	}
	
	function add_custom_price($cart_object) {
		
		$amount = 0;
		
		if(isset($_COOKIE['fsww_duid']) && isset($_COOKIE['fsww_da'])) {
			
			$amount  = floatval($_COOKIE['fsww_da']);
			$user_id = sanitize_text_field($_COOKIE['fsww_duid']);
			
		} else if(isset($_GET['transaction'])) {
			
			$args = explode(';', base64_decode($_GET['transaction']));
			
			$amount  = floatval($args['1']);
			$user_id = sanitize_text_field($args['0']);
			
		}
		
		if($amount != 0) {
			foreach($cart_object->cart_contents as $key => $value) {
				
				$value['data']->set_price($amount);
				
			}
		}
		
	}
	
	
	public function fsww_accept_donations() {
		
		global $woocommerce;
		
		$amount  = 0;
		$user_id = 0;
		
		if(isset($_POST['amount']) && $_POST['amount'] > 0) {
			
			$amount = floatval($_POST['amount']);
			
		}
		
		if(isset($_POST['user_id']) && $_POST['user_id'] != '') {
			
			$user_id = sanitize_text_field($_POST['user_id']);
			
		}
		
		setcookie("fsww_duid", $user_id, time() + (3600), "/");
		setcookie("fsww_da", $amount, time() + (3600), "/");
		
		$woocommerce->cart->empty_cart();
		
		
		if((wc_get_product(get_option('fsww_product')) == null) || (get_option('fsww_product', '-1') == '-1')) {
			
			$this->create_product();
			$woocommerce->cart->add_to_cart(get_option('fsww_product'));
			
		}else {
			$woocommerce->cart->add_to_cart(get_option('fsww_product'));
		}
		
		
	}
	
	public function create_product() {
		
		if((wc_get_product(get_option('fsww_product')) == null) || (get_option('fsww_product', '-1') == '-1')) {
			
			$post_id = wp_insert_post(array(
				'post_title'   => 'Donation',
				'post_content' => '',
				'post_status'  => 'publish',
				'post_type'    => "product",
			));
			
			wp_set_object_terms($post_id, 'simple', 'product_type');
			
			update_post_meta($post_id, '_visibility', 'hidden');
			update_post_meta($post_id, '_stock_status', 'instock');
			update_post_meta($post_id, 'total_sales', '0');
			update_post_meta($post_id, '_downloadable', 'no');
			update_post_meta($post_id, '_virtual', 'yes');
			update_post_meta($post_id, '_regular_price', '0');
			update_post_meta($post_id, '_sale_price', '0');
			update_post_meta($post_id, '_purchase_note', '');
			update_post_meta($post_id, '_featured', 'no');
			update_post_meta($post_id, '_weight', '');
			update_post_meta($post_id, '_length', '');
			update_post_meta($post_id, '_width', '');
			update_post_meta($post_id, '_height', '');
			update_post_meta($post_id, '_sku', '');
			update_post_meta($post_id, '_product_attributes', array());
			update_post_meta($post_id, '_sale_price_dates_from', '');
			update_post_meta($post_id, '_sale_price_dates_to', '');
			update_post_meta($post_id, '_price', '0');
			update_post_meta($post_id, '_sold_individually', 'yes');
			update_post_meta($post_id, '_manage_stock', 'no');
			update_post_meta($post_id, '_backorders', 'no');
			update_post_meta($post_id, '_stock', '');
			
			$term = get_term_by('slug', 'fsww-hp', 'product_cat');
			
			wp_set_object_terms($post_id, $term->term_id, 'product_cat');
			
			update_option('fsww_product', $post_id);
			
		}
		
	}
	
	public function fsww_withdraw_callback() {
		
		global $wpdb;
		
		require_once(dirname(__FILE__) . '/Wallet.php');
		
		$request_id      = $_POST['request_id'];
		$status          = 'accepted';
		
		$amount      = $_POST['amount'];
		$user_id      = $_POST['user_id'];
		
		Wallet::withdraw_funds($user_id, $amount, 0, __('Withdrawal request', 'fsww'));
		
		$data = array(
			'status'           => $status
		);
		
		$where = array(
			'request_id'       => $request_id
		);
		
		$wpdb->update("{$wpdb->prefix}fswcwallet_withdrawal_requests", $data, $where);
		
		
		$link = admin_url('admin.php?page=fsww-wr');
		wp_redirect($link);
		die();
		
	}
	
	public function fsww_reject_withdraw_callback() {
		
		global $wpdb;
		
		$request_id      = $_POST['request_id'];
		$status          = 'rejected';
		
		$data = array(
			'status'           => $status
		);
		
		$where = array(
			'request_id'       => $request_id
		);
		
		$wpdb->update("{$wpdb->prefix}fswcwallet_withdrawal_requests", $data, $where);
		
		
		$link = admin_url('admin.php?page=fsww-wr');
		wp_redirect($link);
		die();
		
	}
	
	
	public function process_send_money() {
		
		require_once(dirname(__FILE__) . '/Wallet.php');
		
		$user        = get_user_by('email', $_POST['send_to']);
		$sender_id   = get_current_user_id();
		$sender   = get_user_by("id", $sender_id);

		$balance     = floatval(Wallet::get_balance(get_current_user_id()));
		$amount      = $_POST['amount'];
		$errors	     = array();
		
		
		if(!$user) {
			
			$errors[] = "email=1";
			
		}
		
		if($balance < $amount) {
			
			$errors[] = "amount=1";
		}
		
		if(!$errors) {
			
			$receiver_id = $user->ID;
			
			Wallet::withdraw_funds($sender_id, $amount, 0, __("Funds transfer to ", "fsww") . $user->user_login);
			Wallet::add_funds($receiver_id, $amount,0, __("Funds received from ", "fsww") . $sender->user_login);
			
			$errors[] = "success=1";
			
		}
		
		$url = strtok($_POST['fsww-rdr'], '?');
		wp_redirect($url . '/?' . implode("&", $errors));
		exit();
		
	}
	
	public function wc_run_functions() {
		
		if(isset($_POST['fsww_accept_donations']) && $_POST['fsww_accept_donations'] == "fsww") {
			
			global $woocommerce;
			
			$amount  = 0;
			$user_id = '';
			
			if(isset($_POST['amount']) && $_POST['amount'] > 0) {
				
				$amount = floatval($_POST['amount']);
				
			}
			
			if(isset($_POST['user_id']) && $_POST['user_id'] != '') {
				
				$user_id = sanitize_text_field($_POST['user_id']);
				
			}
			
			setcookie("fsww_duid", $user_id, time() + (3600), "/");
			setcookie("fsww_da", $amount, time() + (3600), "/");
			
			$args = array($user_id, $amount);
			
			wp_redirect($woocommerce->cart->get_checkout_url() . '/?transaction=' . base64_encode(implode(";", $args)));
			
			die();
			
		}
		
	}
	
	function process_withrawal_request() {
		
		global $wpdb;
		
		require_once(dirname(__FILE__) . '/Wallet.php');
		
		$balance      = floatval(Wallet::get_balance(get_current_user_id()));
		$amount 	  = floatval($_POST['amount']);
		
		
		
		if($_POST['method'] == "paypal") {
			
			$method = "PayPal";
			$adress = sanitize_text_field($_POST['paypal-address']);
			
		} else if($_POST['method'] == "bitcoin") {
			
			$method = "Bitcoin";
			$adress = sanitize_text_field($_POST['bitcoin-address']);
			
		} else if($_POST['method'] == "swift") {
			
			$method = "SWIFT";
			$adress    = json_encode($_POST["swift"]);
			
		} else if($_POST['method'] == "bank") {

			$method = "Bank Transfer";
			$adress    = json_encode($_POST["bank"]);

		} else if($_POST['method'] == "bank_turkey") {

			$method = "Bank Transfer (Turkey)";
			$adress    = json_encode($_POST["bank_turkey"]);

		}
		
		if($balance >= $amount) {
			
			$data = array(
				"user_id" 		 => get_current_user_id(),
				"amount" 		 => floatval($_POST['amount']),
				"status"  		 => "under_review",
				"payment_method" => $method,
				"address"        => $adress,
			);
			
			$wpdb->insert("{$wpdb->prefix}fswcwallet_withdrawal_requests", $data);
			
		}
		
		
	}

    function add_refund_request_button_order_page($order) {

        global $wpdb;

        $status = $wpdb->get_var("SELECT status FROM {$wpdb->prefix}fswcwallet_requests WHERE order_id={$order->get_id()}");

        if($status == 'requested') {

            echo '<a href="' . admin_url('admin-ajax.php?action=fsww_crr&order_id=' . $order->get_id()) . '" class="button">' . __('Cancel Refund Request', 'fsww')  . '</a>';

        } elseif($status == 'refunded') {

            echo '<button class="button" disabled>' . __('Already Refunded', 'fsww')   . '</button>';

        } elseif($status == 'rejected') {

            echo '<button class="button" disabled>' . __('Request Rejected', 'fsww')   . '</button>';

        }
        else {

            echo '<a href="' . admin_url('admin-ajax.php?action=fsww_rr&order_id=' . $order->get_id()) . '" class="button">' . __('Request Refund', 'fsww')  . '</a>';

        }

    }
    
    function add_refund_request_button($actions, $order) {
        
        global $wpdb;
        
        $status = $wpdb->get_var("SELECT status FROM {$wpdb->prefix}fswcwallet_requests WHERE order_id={$order->get_id()}");
		
        if($status == 'requested') {

            echo '<a href="' . admin_url('admin-ajax.php?action=fsww_crr&order_id=' . $order->get_id()) . '" class="button">' . __('Cancel Refund Request', 'fsww')  . '</a>';
                    
        } elseif($status == 'refunded') {
            
            echo '<button class="button" disabled>' . __('Already Refunded', 'fsww')   . '</button>';

        } elseif($status == 'rejected') {
            
            echo '<button class="button" disabled>' . __('Request Rejected', 'fsww')   . '</button>';
            
        }
        else {
            
            echo '<a href="' . admin_url('admin-ajax.php?action=fsww_rr&order_id=' . $order->get_id()) . '" class="button">' . __('Request Refund', 'fsww')  . '</a>';
            
        }
        
        
		return $actions;
        
	}
    
    function make_refund_request_callback() {
        
        global $wpdb;
        
        $order_id        = $_GET['order_id'];
        $request_date    = date('Y-m-d H:i:s');
        $status          = 'requested';
        
        $data = array(
            'order_id'         => $order_id,
            'request_date'     => $request_date,
            'status'           => $status
        );
        
        $wpdb->insert("{$wpdb->prefix}fswcwallet_requests", $data);


        $url = wp_get_referer() ? wp_get_referer() : get_permalink(get_option('woocommerce_myaccount_page_id'));

        do_action('fs_wc_wallet_after_refund_request');

        header("Location: $url");
        die();
        
    }

    public function cancel_refund_request_callback() {

        global $wpdb;

        $order_id        = $_GET['order_id'];

        $data = array(
            'order_id'         => $order_id,
        );

        $wpdb->delete("{$wpdb->prefix}fswcwallet_requests", $data);


        $url = wp_get_referer() ? wp_get_referer() : get_permalink(get_option('woocommerce_myaccount_page_id'));

        header("Location: $url");
        die();

    }
    
    
    // Redirect to chechout if its a wallet cretit product
    function redirect_to_checkout() {
        
        global $woocommerce;

		if(isset($_REQUEST['fsww_add_product'])) {
			
			$product_id = (int) apply_filters('woocommerce_add_to_cart_product_id', $_REQUEST['add-to-cart']);

			if(has_term('fsww-wallet-credit', 'product_cat', $product_id)){

				wc_clear_notices();
				return wc_get_cart_url();

			}

		}
	
	    return wc_get_cart_url();
	    
    }

    // Remove wallet credit items from the cart
    function clear_cart_items($cart_item_data) {
        
        global $woocommerce;
        
        foreach($woocommerce->cart->get_cart() as $cart_item_key => $cart_item){
            
            if(has_term('fsww-wallet-credit', 'product_cat', $cart_item['product_id'])){
                
                global $woocommerce;
                
                $woocommerce->cart->set_quantity($cart_item_key, 0);
                
            }
            
        }
        
        return $cart_item_data;
        
    }

    
    
    function custom_cart_button_text() {
        
        global $product;
				
        if(has_term('fsww-wallet-credit', 'product_cat', $product->get_id()))
            return __('Buy Now', 'woocommerce');

        /** default */
        return __('Add to cart', 'woocommerce');
        
    }
    
    
    // Hide credit products
	function sm_pre_get_posts( $query ) {
		
		if (!is_admin() && $query->is_search() ) {
			$query->set( 'post_type', array( 'product' ) );
			$tax_query = array(
				array(
					'taxonomy' => 'product_cat',
					'field'   => 'slug',
					'terms'   => 'fsww-wallet-credit', //slug name of category
					'operator' => 'NOT IN',
				),
			);
			$query->set( 'tax_query', $tax_query );
		}
		
	}
	
	function custom_pre_get_posts_query( $q ) {
		
		$tax_query = (array) $q->get( 'tax_query' );
		
		$tax_query[] = array(
			'taxonomy' => 'product_cat',
			'field' => 'slug',
			'terms' => array( 'fsww-wallet-credit' ), // Don't display products in the carton category on the shop page.
			'operator' => 'NOT IN'
		);
		
		$q->set( 'tax_query', $tax_query );
	}

	function exclude_product_category_in_tax_query( $tax_query, $query ) {
		if( is_admin() ) return $tax_query;
		
		// The taxonomy for Product Categories custom taxonomy
		$taxonomy = 'product_cat';
		
		$tax_query[] = array(
			'taxonomy' => $taxonomy,
			'field'    => 'slug', // Or 'slug' or 'name'
			'terms'    => array( 'fsww-wallet-credit' ),
			'operator' => 'NOT IN', // Excluded
			'include_children' => true // (default is true)
		);
		
		return $tax_query;
	}
	
	function exclude_product_categories_widget( $list_args ) {
		$category = get_term_by( 'slug', 'fsww-wallet-credit', 'product_cat' );
		$cat_id = $category->term_id;
		
		$categories = array($cat_id);
		
		if(isset( $list_args['include'])):
			$included_ids =  explode( ',', $list_args['include'] );
			$included_ids = array_unique( $included_ids );
			$included_ids = array_diff ( $included_ids, $categories );
			
			$list_args['include'] = implode( ',', $included_ids);
		else:
			$list_args['exclude'] = $categories;
		endif;
		
		return $list_args;
	}
    
    // AJAX Actions
    public function plugin_ajax_actions() {
        
        add_action('wp_ajax_fsww_rr', array($this, 'make_refund_request_callback'));
        add_action('wp_ajax_nopriv_fsww_rr', array($this, 'make_refund_request_callback'));

        add_action('wp_ajax_fsww_crr', array($this, 'cancel_refund_request_callback'));
        add_action('wp_ajax_nopriv_fsww_crr', array($this, 'cancel_refund_request_callback'));

        add_action('wp_ajax_fsww_user_tansactions', array($this, 'fsww_user_tansactions_callback'));
        add_action('wp_ajax_nopriv_fsww_user_tansactions', array($this, 'fsww_user_tansactions_callback'));


	    add_action('woocommerce_before_calculate_totals', array($this, 'add_custom_price'), 99, 1);
        
    }

    public function add_plugin_actions() {
        if(current_user_can( 'manage_woocommerce' )) {
            // Export

            add_action('wp_ajax_fsww_export', array($this, 'fsww_export_callback'));
            add_action('wp_ajax_nopriv_fsww_export', array($this, 'fsww_export_callback'));

            // Import

            add_action('wp_ajax_fsww_import', array($this, 'fsww_import_callback'));
            add_action('wp_ajax_nopriv_fsww_import', array($this, 'fsww_import_callback'));

            add_action('wp_ajax_fsww_add_funds', array($this, 'add_funds_callback'));
            add_action('wp_ajax_nopriv_fsww_add_funds', array($this, 'add_funds_callback'));

            add_action('wp_ajax_fsww_refund', array($this, 'fsww_refund_callback'));
            add_action('wp_ajax_nopriv_fsww_refund', array($this, 'fsww_refund_callback'));

            add_action('wp_ajax_fsww_reject', array($this, 'fsww_reject_callback'));
            add_action('wp_ajax_nopriv_fsww_reject', array($this, 'fsww_reject_callback'));

            // Withdraw

            add_action('wp_ajax_fsww_withdraw', array($this, 'fsww_withdraw_callback'));
            add_action('wp_ajax_nopriv_fsww_withdraw', array($this, 'fsww_withdraw_callback'));

            add_action('wp_ajax_fsww_reject_withdraw', array($this, 'fsww_reject_withdraw_callback'));
            add_action('wp_ajax_nopriv_fsww_reject_withdraw', array($this, 'fsww_reject_withdraw_callback'));

            add_action('wp_ajax_fsww_save_encryption_setting', array($this, 'save_encryption_setting'));
            add_action('wp_ajax_nopriv_fsww_save_encryption_setting', array($this, 'save_encryption_setting'));

            add_action('wp_ajax_fsww_i_add_funds', array($this, 'fsww_i_add_funds_request'));
            add_action('wp_ajax_nopriv_fsww_i_add_funds', array($this, 'fsww_i_add_funds_request'));

            add_action('wp_ajax_fsww_i_withdraw', array($this, 'fsww_i_withdraw_request'));
            add_action('wp_ajax_nopriv_fsww_i_withdraw', array($this, 'fsww_i_withdraw_request'));

            add_action('wp_ajax_fsww_i_lock', array($this, 'fsww_i_lock_request'));
            add_action('wp_ajax_nopriv_fsww_i_lock', array($this, 'fsww_i_lock_request'));

            add_action('wp_ajax_fsww_edit_wallet', array($this, 'fsww_edit_wallet_callback'));
            add_action('wp_ajax_nopriv_fsww_edit_wallet', array($this, 'fsww_edit_wallet_callback'));
        }
    }
    
    public function fsww_export_callback() {
	
	    header('Content-Type: application/csv');
	    header('Content-Disposition: attachement; filename="wallets__' . date("__d_m_Y__H_i_s"). '__' . '.csv";');
	    echo $this->generate_csv();
    	
        die();
    }
	
	/*****************/
	//   Generate license keys csv
	/*****************/
	public function generate_csv(){
		global $wpdb;

        require_once(dirname(__FILE__) . '/Wallet.php');
		
		$output = "sep=,\n";
		
		$head = '"user_id","balance","last_deposit","total_spent","status","lock_message","user_email","user_login"';

        $query = $wpdb->get_results("SELECT ID, user_email, user_login FROM {$wpdb->prefix}users ORDER BY ID ASC", ARRAY_A);

		if($query){

			$output .= $head . "\n";

			foreach($query as $row){

                $export['ID'] = $row['ID'];
                $export['balance'] = Wallet::get_balance($row['ID']);
                $export['last_deposit'] = "(" . Wallet::get_last_deposit($row['ID']) . ")";
                $export['total_spent'] = Wallet::get_total_spent($row['ID']);
                $export['status'] = Wallet::get_status($row['ID']);
                $export['lock_message'] = Wallet::get_lock_message($row['ID']);


                $user_details = $row['user_email'] . '","' . $row['user_login'] . '"' ."\n";
                $output .= '"'.implode('","',$export).'","' . $user_details;

                unset($row);
				
			}
		}
		
		return $output;
	}
	
	
	public function fsww_import_callback(){
		global $wpdb;
		
		if(isset($_FILES['fsww_source_file'])&& $_FILES['fsww_source_file']['size'] > 0){
			
			require_once(dirname(__FILE__) . '/Wallet.php');
			
			$tmp = wp_tempnam($_FILES['fsww_source_file']['name']);
			move_uploaded_file($_FILES['fsww_source_file']['tmp_name'], $tmp);
			
			$handle = fopen($tmp, 'r');
			$delimiter = $this->detectDelimiter($tmp);
			while (($data = fgetcsv($handle, 0, $delimiter)) !== FALSE) {
				$num = count($data);
				
				if(($num ==6 || $num == 8) && !in_array('user_id', $data)) {
					if(Wallet::wallet_exist($data[0])) {

						$_data = array(
							'balance'       => FS_WC_Wallet::encrypt_decrypt('encrypt', $data[1]),
							'last_deposit'  => substr($data[2], 1, -1),
							'total_spent'   => FS_WC_Wallet::encrypt_decrypt('encrypt', $data[3]),
							'status'        => $data[4],
							'lock_message'  => $data[5]
						);

						$where = array(
							'user_id' => $data[0]
						);

						$wpdb->update("{$wpdb->prefix}fswcwallet", $_data, $where);

					} else {
						$_data = array(
							'user_id'       => $data[0],
							'balance'       => FS_WC_Wallet::encrypt_decrypt('encrypt', $data[1]),
							'last_deposit'  => substr($data[2], 1, -1),
							'total_spent'   => FS_WC_Wallet::encrypt_decrypt('encrypt', $data[3]),
							'status'        => $data[4],
							'lock_message'  => $data[5]
						);

						$wpdb->insert("{$wpdb->prefix}fswcwallet", $_data);
					}
				}
			}
			fclose($handle);
			
		}
		
		$link = admin_url('admin.php?page=fsww-wallets');
		wp_redirect($link);
		die();
	}

	public function detectDelimiter($csvFile) {
		$delimiters = array(
			';' => 0,
			',' => 0,
			"\t" => 0,
			"|" => 0
		);

		$handle = fopen($csvFile, "r");
		$firstLine = fgets($handle);
		if($firstLine == "sep=,") $firstLine = fgets($handle);
		foreach ($delimiters as $delimiter => &$count) {
			$count = count(str_getcsv($firstLine, $delimiter));
		}

		return array_search(max($delimiters), $delimiters);
	}
    
    public function save_encryption_setting() {
        
        $key = $_POST['fsww_encryption_key'];
        $vi = $_POST['fsww_encryption_vi'];
        $disable = $_POST['fsww_disable_encryption'];

        if(!add_option('fsww_disable_encryption', $disable)){
            update_option('fsww_disable_encryption', $disable);
        }

        $this->set_encryption_key($key, $vi, 'update');

        $link = admin_url('admin.php?page=fsww-settings&tab=encryption');
        wp_redirect($link);
        die();
        
    }
    
    public function fsww_user_tansactions_callback() {
        
        require_once(dirname(__FILE__) . '/../transactions_table_html.php');

        transactions_table_html($_POST['user_id']);
        
        die();
        
    }
    
    // Refund
    public function fsww_refund_callback() {
    
        global $wpdb;


        
        require_once(dirname(__FILE__) . '/Wallet.php');
        
        $request_id      = $_POST['request_id'];
        $status          = 'refunded';
        
        $data = array(
            'status'           => $status
        );
        
        $where = array(
            'request_id'       => $request_id
        );
        
        $order_id = $wpdb->get_var("SELECT order_id FROM {$wpdb->prefix}fswcwallet_requests WHERE request_id={$request_id}");
        
        $order          = wc_get_order($order_id);
        $amount         = $order->get_total();
        
        $user           = $order->get_user_id();
        
		
		//Parial payment
        $fees         = $order->get_fees();
        $total_fees   = 0; 
        
        $transaction_found = false;
        
        foreach($fees as $fee) {
            
            if($fee['tax_class'] == 'fsww_pfw'  || $fee['name'] == __('Paid From Wallet', 'fsww')) {
             
                $total_fees += abs(floatval($fee['line_total']));
                $transaction_found = true;
                
            }
            
        }        
          
        if($transaction_found) {
            
			$amount         = $amount + $total_fees;
            
        }
		
		
		
		//End Parial payment
		
        
        Wallet::refund($user, $amount, $order_id, __('Refund', 'fsww'));
        
        $wpdb->update("{$wpdb->prefix}fswcwallet_requests", $data, $where);
        
        
        $link = admin_url('admin.php?page=fsww-rr&r');
        wp_redirect($link);
        die();
        
    }
    
    // Reject refund request
    public function fsww_reject_callback() {
            
        global $wpdb;
        
        $request_id      = $_POST['request_id'];
        $status          = 'rejected';
        
        $data = array(
            'status'           => $status
        );
        
        $where = array(
            'request_id'       => $request_id
        );
        
        $wpdb->update("{$wpdb->prefix}fswcwallet_requests", $data, $where);
        
        
        $link = admin_url('admin.php?page=fsww-rr');
        wp_redirect($link);
        die();
        
    }
    
    public function fsww_edit_wallet_callback() {
        
        global $wpdb;
        
        $user_id              = $_POST['user_id'];

        require_once(dirname(__FILE__) . '/Wallet.php');
        if(!Wallet::wallet_exist($user_id)) {

            Wallet::create_wallet($user_id, 0, 0, 'unlocked');

        }

        $current_balance = Wallet::get_balance($user_id);

        $balance              = FS_WC_Wallet::encrypt_decrypt('encrypt', $_POST['balance']);
        $total_spent          = FS_WC_Wallet::encrypt_decrypt('encrypt', $_POST['total_spent']);
        $status               = $_POST['status'];
        $lock_message         = $_POST['lock_message'];
        
        $last_deposit_month   = $_POST['last_deposit_month'];
        $last_deposit_day     = $_POST['last_deposit_day'];
        $last_deposit_year    = $_POST['last_deposit_year'];
        
        $data = array(
            'balance'       => $balance,
            'last_deposit'  => $last_deposit_year . '-' . $last_deposit_month . '-' . $last_deposit_day . ' ' . date('H:i:s'),
            'total_spent'   => $total_spent,
            'status'        => $status,
            'lock_message'  => $lock_message
        );
        
        $where = array(
            'user_id'       => $user_id
        );
        
        $wpdb->update("{$wpdb->prefix}fswcwallet", $data, $where);

        Wallet::add_transaction(0, $user_id, 'debits', $current_balance, __("Wallet manually modified", "fsww"));
        Wallet::add_transaction(0, $user_id, 'credits', $_POST['balance'], __("Wallet manually modified", "fsww"));

        //die('<pre>' . print_r($data, true) . '</pre>');
        
        $link = admin_url('admin.php?page=fsww-wallets');
        wp_redirect($link);
        die();
        
    }
    
    public function fsww_i_add_funds_request() {
        
        require_once(dirname(__FILE__) . '/Wallet.php');
        
        $user_id  = $_POST['user_id'];
        $amount   = $_POST['amount'];
        $message  = $_POST['message'];
        $notify   = $_POST['notify'];
        
        if($notify == 'on') {
            
            $message    = '<p>' . fsww_price($amount) . ' ' . __('Have been added to your account balance', 'fsww') . '</p><br>' . $message;
            $subject    = __('Funds Have been added to your account', 'fsww');
            $heading    = __('Funds Have been added to your account', 'fsww');
            
            $this->send_email($user_id, $message, $subject, $heading);
            
        }
       
        Wallet::add_funds($user_id, $amount, 0, (__("Admin added funds", "fsww") . ($message!=''?(', '.$message):'')));
        
        $link = admin_url('admin.php?page=fsww-wallets');
        wp_redirect($link);
        die();
        
    }
    
    public function fsww_i_withdraw_request() {
        
        require_once(dirname(__FILE__) . '/Wallet.php');
        
        $user_id  = $_POST['user_id'];
        $amount   = $_POST['amount'];
        $message  = $_POST['message'];
        $notify   = $_POST['notify'];
        
        if($notify == 'on') {
            
            $message    = '<p>' . fsww_price($amount) . ' ' . __('Have been withdrawed from your account balance', 'fsww') . '</p><br>' . $message;
            $subject    = __('Funds Have been withdrawed from your account', 'fsww');
            $heading    = __('Funds Have been withdrawed from your account', 'fsww');
            
            $this->send_email($user_id, $message, $subject, $heading);
            
        }
        
        Wallet::withdraw_funds($user_id, $amount, 0, (__("Admin withdraw funds", "fsww") . ($message!=''?(', '.$message):'')));
        
        $link = admin_url('admin.php?page=fsww-wallets');
        wp_redirect($link);
        die();
        
    }
    
    public function fsww_i_lock_request() {
        
        //die('<pre>' . print_r($_POST, true) . '</pre>');
        
        require_once(dirname(__FILE__) . '/Wallet.php');
        
        $user_id  = $_POST['user_id'];
        $action   = $_POST['fsww_action'];
        $message  = $_POST['message'];
        $notify   = $_POST['notify'];
        
        
        if($action == 'lock') {
            
            Wallet::lock_account($user_id, $message);
            
            if($notify == 'on') {
            
                $message    = '<p>'.  __('Your wallet balance have been locked, you can nolonger use it to pay for products.', 'fsww') . '</p><br>' . $message;
                $subject    = __('Your account have been locked', 'fsww');
                $heading    = __('Your account have been locked', 'fsww');

                $this->send_email($user_id, $message, $subject, $heading);

            }
            
        } elseif ($action == 'unlock') {
            
            Wallet::unlock_account($user_id);
            
            if($notify == 'on') {
            
                $message    = '<p>'.  __('Your wallet balance have been unlocked.', 'fsww') . '</p><br>' . $message;
                $subject    = __('Your account have been unlocked', 'fsww');
                $heading    = __('Your account have been unlocked', 'fsww');

                $this->send_email($user_id, $message, $subject, $heading);

            }
            
        }
        
        $link = admin_url('admin.php?page=fsww-wallets');
        wp_redirect($link);
        die();
        
    }
    
    
    // Send emails
    public function send_email($user_id, $message, $subject, $heading) {
        
        global $woocommerce;

        $username       = get_user_by('id', $user_id);
        
        $to = $username->user_email;

        if(!$to || '' == trim($to)) {
            
            return false;
            
        }

        //$headers = apply_filters('woocommerce_email_headers', '', 'rewards_message');
        $attachments = array();
    

        $mailer = $woocommerce->mailer();

        $message = $mailer->wrap_message($heading, $message);

        $headers = array();

        $mailer->send($to, $subject, $message, $headers, $attachments);

    }
    
    // Add actions
    public function add_actions() {
        
        add_action('wp_loaded', array($this, 'register_styles'));
        add_action('wp_loaded', array($this, 'register_scripts'));
	
	    add_action('wp_loaded', array($this, 'run_functions'));
	    add_action('wp_loaded', array($this, 'wc_run_functions'));
        
        add_action('init', array($this, 'plugin_init'));
        add_action('init', array($this, 'add_plugin_actions'));

        add_action('init', array($this, 'transactions_print_view'));

        
        add_action('admin_notices', array($this, 'admin_notices'));
        
        // Add Wallets sub menu
        add_action('admin_menu', array($this, 'add_menu_items'));
        
        // Hide credit products
	    add_action('pre_get_posts', array($this, 'sm_pre_get_posts'), 1, 1);
	    add_action('woocommerce_product_query', array($this, 'custom_pre_get_posts_query'), 1, 1);
	    add_filter('woocommerce_product_query_tax_query', array($this, 'exclude_product_category_in_tax_query'), 10, 2 );
	    add_filter('woocommerce_product_categories_widget_args', array($this, 'exclude_product_categories_widget'), 10, 1);
	
	
	    add_action('woocommerce_before_checkout_form', array($this, 'account_alerts'));
        
        add_action('admin_init', array($this, 'register_custom_setting'));

        $load_wallet_on = get_option('fsww_load_wallet_on', 'completed');

        if($load_wallet_on == 'processing' || $load_wallet_on == 'all') {
            add_action('woocommerce_order_status_processing',  array($this, 'add_credits_to_user_account'));
        }

        if($load_wallet_on == 'completed' || $load_wallet_on == 'all') {
            add_action('woocommerce_order_status_completed', array($this, 'add_credits_to_user_account'));
        }

        if(get_option('fsww_partial_payments', 'on') == 'on') {
            
			
			add_action('init', array($this, 'parial_payment_cookie'), 1);
			add_action('woocommerce_before_cart_table', array($this, 'partial_payment_add_remove'), 1);
			add_action('woocommerce_before_checkout_form', array($this, 'partial_payment_add_remove_checkout'), 1);
            add_action('woocommerce_cart_calculate_fees', array($this, 'custom_wc_add_fee'), 1);
            add_filter('woocommerce_cart_totals_get_fees_from_cart_taxes', array($this, 'woocommerce_cart_totals_get_fees_from_cart_taxes'), 10, 2);
			
			add_action('woocommerce_checkout_order_processed', array($this, 'partial_payment'), 1, 1);
            
        }
        
        add_action('wp_enqueue_scripts', array($this, 'additional_custom_styles'));
        		
        add_action('woocommerce_order_status_refunded', array($this, 'cancelled_order'), 10, 1);
        add_action('woocommerce_order_status_cancelled', array($this, 'cancelled_order'), 10, 1);

		add_action('category_add_form_fields', array($this, 'tutorialshares_taxonomy_add_new_meta_field'), 10, 2);
		
		add_action('woocommerce_product_options_general_product_data', array($this, 'woo_add_custom_general_fields') );
		
		add_action('woocommerce_variation_options', array($this, 'woo_add_custom_variable_fields'), 1, 3);

		// Save Fields
		add_action('woocommerce_process_product_meta', array($this, 'woo_add_custom_general_fields_save'), 1);
		add_action('woocommerce_process_product_meta_variable', array($this, 'woo_add_custom_variable_fields_save'), 1);
		
		add_action('woocommerce_ajax_save_product_variations', array($this, 'woo_add_custom_variable_fields_save_ajax'), 10, 2);
        
		add_action('woocommerce_order_status_completed', array($this, 'cashback'), 10, 2 );
		
		//add_action('woocommerce_refund_created', array($this, 'process_refunds'), 10, 2);

		
		
    }

    public function transactions_print_view() {

	    if(isset($_GET['print']) && $_GET['print'] == 'transactions') {

            if(is_admin() || is_ajax() || (defined( 'REST_REQUEST' ) && REST_REQUEST)) return false;

            require_once(dirname(__FILE__) . '/../transactions_table_html.php');


            ?>

            <style>

                .fsww_table {
                    width: 100%;
                 }

                table {
                    margin: 0 auto;
                    border-collapse: collapse;
                    width: 70%;
                    min-width: 360px;
                    max-width: 720px;
                }

                th, td {
                    text-align: left;
                    padding: 8px;
                }

                tr:nth-child(even) {background-color: #f2f2f2;}

                @media print {
                    table {
                        margin: auto;
                        width: 100%;
                        min-width: unset;
                        max-width: unset;
                    }
                }
            </style>

            <?php


            echo '<div class="fsww_table">';
                transactions_table_html(get_current_user_id(), true);
            echo '</div>';


            die();

        }

    }
	
	function partial_payment_add_remove_checkout() {
		
		global $woocommerce;
		
		$cart_page_url = wc_get_checkout_url();
		
		if(is_user_logged_in()) {
			
			$balance      = floatval(Wallet::get_balance(get_current_user_id()));
			$total        = $woocommerce->cart->subtotal;

            $no_recharge_products = true;

            $product = FS_WC_Wallet::fsww_get_wallet_rechargeable_product();
            foreach ($woocommerce->cart->get_cart() as $key => $value) {
                if ($product->get_id() == $value['product_id']) {
                    $no_recharge_products = false;
                }
            }
			
			if( ($balance < $total && $balance > 0 ) && $no_recharge_products ) {
			
				if(self::$remove_partial_payment == '0') {

					echo '<div class="woocommerce-message">';
					echo '	  <a href="' . $cart_page_url . '?fsww_remove_partial_payment=1" class="button">' . __('Remove', 'fsww') . '</a> ' . __('Would you like to remove wallet partial payment?', 'fsww');
					echo '</div>';

				}else if(self::$remove_partial_payment == '1') {

					echo '<div class="woocommerce-message">';
					echo '	  <a href="' . $cart_page_url . '?fsww_remove_partial_payment=0" class="button">' . __('Add', 'fsww') . '</a> ' . __('You have ', 'fsww') . fsww_price($balance) . __(' would you like to use them?', 'fsww');
					echo '</div>';

				}

			}
			
		}
	
	}
    
    function partial_payment_add_remove() {
		
		global $woocommerce;
		
		$cart_page_url = wc_get_cart_url();
		
		if(is_user_logged_in()) {
			
			$balance      = floatval(Wallet::get_balance(get_current_user_id()));
			$total        = $woocommerce->cart->subtotal;

            $no_recharge_products = true;

            $product = FS_WC_Wallet::fsww_get_wallet_rechargeable_product();
            foreach ($woocommerce->cart->get_cart() as $key => $value) {
                if ($product->get_id() == $value['product_id']) {
                    $no_recharge_products = false;
                }
            }

            if( ($balance < $total && $balance > 0 ) && $no_recharge_products ) {
			
				if(self::$remove_partial_payment == '0') {

					echo '<div class="woocommerce-message">';
					echo '	  <a href="' . $cart_page_url . '?fsww_remove_partial_payment=1" class="button">' . __('Remove', 'fsww') . '</a> ' . __('Would you like to remove wallet partial payment?', 'fsww');
					echo '</div>';

				}else if(self::$remove_partial_payment == '1') {

					echo '<div class="woocommerce-message">';
					echo '	  <a href="' . $cart_page_url . '?fsww_remove_partial_payment=0" class="button">' . __('Add', 'fsww') . '</a> ' . __('You have ', 'fsww') . fsww_price($balance) . __(' would you like to use them?', 'fsww');
					echo '</div>';

				}

			}
			
		}
	
	}
	
	function parial_payment_cookie() {
		
		if(isset($_GET['fsww_remove_partial_payment']) && $_GET['fsww_remove_partial_payment'] == 1) {
			
			self::$remove_partial_payment = '1';
			setcookie('fsww_remove_partial_payment', '1', time() + (3600), "/");
			
		}
		
		if(isset($_GET['fsww_remove_partial_payment']) && $_GET['fsww_remove_partial_payment'] == 0) {
			
			self::$remove_partial_payment = '0';
			setcookie('fsww_remove_partial_payment', '0', time() + (3600), "/");
			
		}
		
		
		
	}
	
	function process_refunds($refund_id, $refund) {
			
		if(!isset($_POST['fsww_refund_action'])) {
			
			require_once(dirname(__FILE__) . '/Wallet.php');
		
			$order = wc_get_order($refund['order_id']);

			Wallet::add_funds($order->get_user_id(), floatval($refund['amount']), $refund['order_id']);

		}
		
	}
	

	function woo_add_custom_general_fields() {

		global $woocommerce, $post;

		// Text Field creation*
		woocommerce_wp_text_input( 
			array( 
				'id'          => '_fsww_credit', 
				'label'       => __('Wallet Credit', 'fsww'), 
				'placeholder' => '',
				'desc_tip'    => 'true',
				'description' => __('Enter the wallet credit given after purchasing this product', 'fsww'),
				'value'	      => get_post_meta($post->ID, '_fsww_credit', true)	
			)
		);/**/
		
		woocommerce_wp_text_input( 
			array( 
				'id'          => '_fsww_cashback' . '', 
				'label'       => __('Wallet Cashback', 'fsww'), 
				'placeholder' => '',
				'desc_tip'    => 'true',
				'description' => __('Enter the wallet cashback amount given after purchasing this product<br><br>Enter an exact value, or a value ending with % to give a percentage or leave empty for no cashback', 'fsww'),
				'value'	      => get_post_meta($post->ID, '_fsww_cashback', true)	
			)
		);
	
	}
	
	function woo_add_custom_variable_fields($loop, $variation_data, $variation) {
		
		woocommerce_wp_text_input( 
			array( 
				'id'          => '_fsww_cashback['.$loop.']', 
				'label'       => __('Wallet Cashback', 'fsww'), 
				'placeholder' => '',
				'desc_tip'    => 'true',
				'description' => __('Enter the wallet cashback amount given after purchasing this product<br><br>Enter an exact value, or a value ending with % to give a percentage or leave empty for no cashback', 'fsww'),
				'value'	      => get_post_meta($variation->ID, '_fsww_cashback', true)
			)
		);
		
	}
	
	function woo_add_custom_general_fields_save($post_id){

		$product = wc_get_product($post_id);
		
		if(isset($_POST['_fsww_credit'])) {
			
			$fsww_credit = $_POST['_fsww_credit'];
			update_post_meta($post_id, '_fsww_credit', esc_html($fsww_credit));
			
		}

		if($product->is_type('simple')){
			
			if(isset($_POST['_fsww_cashback'])) {

				$fsww_cashback = $_POST['_fsww_cashback'];
				update_post_meta($post_id, '_fsww_cashback', esc_html($fsww_cashback));

			}
			
		}
		
	}
	
	function woo_add_custom_variable_fields_save($post_id){
		
		if(isset($_POST['variable_sku'])) {
			
			$variable_sku = $_POST['variable_sku'];
			$variable_post_id = $_POST['variable_post_id'];
			
			$variable_custom_field = $_POST['_fsww_cashback'];
			
			for($i = 0; $i < sizeof($variable_sku); $i++) {
				
				$variation_id = (int) $variable_post_id[$i];
				
				if(isset($variable_custom_field[$i])) {
					
					update_post_meta($variation_id, '_fsww_cashback', esc_html($variable_custom_field[$i]));
					
				}
				
			}
			
		}
		
	}
	
	function woo_add_custom_variable_fields_save_ajax($array = array(), $int = 0){
		
		if(isset($_POST['variable_sku'])) {
			
			$variable_sku = $_POST['variable_sku'];
			$variable_post_id = $_POST['variable_post_id'];
			
			$variable_custom_field = $_POST['_fsww_cashback'];
			
			for($i = 0; $i < sizeof($variable_sku); $i++) {
				
				$variation_id = (int) $variable_post_id[$i];
				
				if(isset($variable_custom_field[$i])) {
					
					update_post_meta($variation_id, '_fsww_cashback', esc_html($variable_custom_field[$i]));
					
				}
				
			}
			
		}
		
	}
	
    
    public function cancelled_order($order_id) {
	    global $woocommerce;
	
	    require_once(dirname(__FILE__) . '/Wallet.php');
	
	    $order = wc_get_order($order_id);
	    $user_id = $order->get_user_id();
	
	    if(add_post_meta($order_id, 'fswcw_refunded', 'true', true)) {
	    	
		    if ($order->get_payment_method() == 'fsww') {
			    Wallet::refund($user_id, $order->get_total(), $order_id, __('Order cancelled', 'fsww'));
		    }
	    
		    
		    $fees = $order->get_fees();
		    $total_fees = 0;
		    $transaction_found = false;
		
		    foreach ($fees as $fee) {
			
			    if ($fee['tax_class'] == 'fsww_pfw' || $fee['name'] == __('Paid From Wallet', 'fsww')) {
				
				    $total_fees += abs(floatval($fee['line_total']));
				    $transaction_found = true;
				
			    }
			
		    }
		
		    if ($transaction_found) {
			
			    Wallet::refund($user_id, $total_fees, $order_id, __('Order cancelled', 'fsww'));
			
		    }
		    
	    }
		
	}
	
    public function partial_payment($order_id) {
        
        global $woocommerce;
        
        require_once(dirname(__FILE__) . '/Wallet.php');
        
        $order        = wc_get_order($order_id);
        $fees         = $order->get_items( 'fee' );
        $user_id      = $order->get_user_id();
        $total_fees   = 0;
        
        $transaction_found = false;


        
        foreach($fees as $item_id => $fee) {

            if($fee['tax_class'] == 'fsww_pfw'  || $fee['name'] == __('Paid From Wallet', 'fsww')) {
             
                $total_fees += abs(floatval($fee['line_total']));
                $transaction_found = true;
                
            }
            
        }        
          
        if($transaction_found) {
            
            $balance      = floatval(Wallet::get_balance($user_id));
            $balance_new  = $balance - floatval($woocommerce->cart->total);

            Wallet::withdraw_funds($user_id, $total_fees, $order_id, __('Partial Payment', 'fsww'));
            Wallet::add_spending($user_id, $total_fees);
            
        }
        
        
       
        
        
        return true;
        
    }

    public function custom_wc_add_fee($cart) {
        
        require_once(dirname(__FILE__) . '/Gateway.php');
        
        //$WC_Gateway_FSWW = new WC_Gateway_FSWW('fsww');
        
        
        require_once(dirname(__FILE__) . '/Wallet.php');
        
        global $woocommerce;
        
        if(!is_user_logged_in()) {
            
            return false;
        
        }
        
        $status   = Wallet::lock_status(get_current_user_id());
        
        if($status['status'] == 'locked') {
                
            return false;
            
        }
        
        foreach($woocommerce->cart->get_cart() as $cart_item_key => $cart_item){
            
            if(has_term('fsww-wallet-credit', 'product_cat', $cart_item['product_id'])){
                
                return false;
                
            }
            
        }


        $parial_payment_amount = Wallet::get_balance(get_current_user_id());
        $total   = floatval($woocommerce->cart->subtotal);


        $fee = array(
            'id' => '_fsww_partial_payment',
            'name' => __('Paid From Wallet', 'fsww'),
            'amount' => (float) -1 * $parial_payment_amount,
            'taxable' => false,
            'tax_class' => 'fsww_pfw',
        );



        if(($total > $parial_payment_amount) && ($parial_payment_amount > 0)) {
                 
			if(self::$remove_partial_payment == '0') {

                wc()->cart->fees_api()->add_fee($fee);
				
			}
            
            
        }
        
        //unset($WC_Gateway_FSWW);
        
    }

    public function woocommerce_cart_totals_get_fees_from_cart_taxes($fee_taxes, $fee) {
        if ('_fsww_partial_payment' === $fee->object->id) {
            $fee_taxes = array();
        }
        return $fee_taxes;
    }
    
    public function add_credits_to_user_account($order_id){

        if(add_post_meta($order_id, 'fsww_loaded', 'true', true)) {

            require_once(dirname(__FILE__) . '/Wallet.php');

            $order = wc_get_order($order_id);

            if (count($order->get_items()) > 0) {

                foreach ($order->get_items() as $item) {

                    if (get_option('fsww_deposit_input', 'off') == 'on') {

                        $product_id = $item['product_id'];
                        $wallet_product = self::fsww_get_wallet_rechargeable_product();
                        if ($wallet_product) {
                            if ($wallet_product->get_id() == $product_id) {
                                $quantity = $item['quantity'];
                                $amount = $item->get_subtotal() * (int)$quantity;

                                Wallet::add_funds($order->get_user_id(), $amount, 0, __('Wallet top up', 'fsww'));
                            }
                        }

                    } else {

                        $credit_product = false;

                        $product_id = $item['product_id'];
                        $term_list = get_the_terms($product_id, 'product_cat');

                        //echo '<pre>' . print_r($term_list, true) . '</pre>';

                        foreach ($term_list as $term) {

                            if ($term->slug == 'fsww-wallet-credit') {

                                $credit_product = true;

                            }

                        }

                        if ($credit_product) {

                            $_product = wc_get_product($product_id);
                            $quantity = $item['quantity'];

                            $credit = get_post_meta($product_id, '_fsww_credit', true);
                            $amount = $_product->get_regular_price() * (int)$quantity;

                            if ($credit != "") {

                                /*WCB*/
                                $amount = get_post_meta($product_id, '_fsww_credit', true) * (int)$quantity;

                            }

                            Wallet::add_funds($order->get_user_id(), $amount, 0, __('Wallet top up', 'fsww'));

                        }
                    }
                }
            }

        }
    }
    
    
    // Add shortcode
    public function add_shortcodes() {
        
        add_shortcode('fsww_deposit', array($this, 'shortcode_make_deposit'));
        
        add_shortcode('fsww_balance', array($this, 'shortcode_user_balance'));
		
        add_shortcode('fsww_transactions_history', array($this, 'shortcode_transactions_history'));
	
	    add_shortcode('fsww_send_money', array($this, 'shortcode_send_money'));
        
    }
	
	public function shortcode_send_money($sc_args) {
		
		if(!is_user_logged_in()) {
			return false;
		}
		
		if(isset($_GET['email'])) {
			
			echo "<div class=\"woocommerce-Message woocommerce-Message--info woocommerce-info\">" . __("Invalid user email", "fsww") . "</div>";
			
		}
		
		if(isset($_GET['amount'])) {
			
			echo "<div class=\"woocommerce-Message woocommerce-Message--info woocommerce-info\">" . __("Insufficient funds", "fsww") . "</div>";
			
		}
		
		if(isset($_GET['success'])) {
			
			echo "<div class=\"woocommerce-Message woocommerce-Message--info woocommerce-info\">" . __("The amount have been sent.", "fsww") . "</div>";
			
		}
		
		
		
		$actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		
		$output = '
        
        <style>

            .fsww-meke-deposit-sc {
            
            }
            
            .fsww-select {
                display: block;
                
                margin-bottom: 16px !important;
                max-width: 320px !important;
                width: 100% !important;
            }
            
        </style>
        
        <div class="fsww-meke-deposit-sc">
            <h4>' . __('Send Money', 'fsww') . '</h4>

            <form id="send_money" method="post">
			
				<input type="hidden" value="fsww" name="fsww-send-money">
				<input type="hidden" value="' . $actual_link . '" name="fsww-rdr">
			
				<p class="form-row form-row-wide">
				
					<label for="amount" class="">' . __('Amount', 'fsww') . '</label>
					<input type="text" class="input-text" name="amount" id="amount" placeholder="0.00" value="" required>
					
				</p>
				
				<p class="form-row form-row-wide">
				
					<label for="send_to" class="">' . __('Receiver email address', 'fsww') . '</label>
					<input type="email" class="input-text" name="send_to" id="send_to" placeholder="" value="" required>
					
				</p>
               	

                <input type="submit" class="button" value="' . __('Send', 'fsww') . '">

            </form>
        </div>';
		
		return $output;
		
	}
    
    public function shortcode_user_balance($sc_args) {
        
        require_once(dirname(__FILE__) . '/Wallet.php');
        
        $output         = '';
        $user_id        = get_current_user_id();
        $balance        = fsww_price(Wallet::get_balance($user_id));
        //$username       = get_user_by('id', $user_id);
        //$username       = $username->user_login;
        
        $output         = /*$username . ' &ndash; ' . */$balance;
        
        return $output;
        
    }
	
	public function shortcode_transactions_history($sc_args) {

	    if(is_admin() || is_ajax() || (defined( 'REST_REQUEST' ) && REST_REQUEST)) return false;

		require_once(dirname(__FILE__) . '/../transactions_table_html.php');


        ob_start();
        transactions_table_html(get_current_user_id(), true);
        
        return ob_get_clean();
        
    }
    
    public function shortcode_make_deposit($sc_args) {

        if(!is_user_logged_in()) {
            return false;
        }

        $options = '';
        
        $args = array( 
            'post_type'        => 'product', 
            'posts_per_page'   => 1000, 
            'product_cat'      => 'fsww-wallet-credit',
            'fsww_sc'          => 'show',
            'orderby'          => 'meta_value_num',
            'meta_key'         => '_price',
            'order'            => 'asc'
        );
        
        $loop = new WP_Query($args);
        
        while($loop->have_posts()) {
            
            $loop->the_post(); 
            global $product; 
            
                $options .= '<option value="' . get_the_ID() . '">' . get_the_title() . '</option>';

         }
        
        wp_reset_query();
        
        $output = '
        
        <style>

            .fsww-meke-deposit-sc {
                
            }
            
            .fsww-input,
            .fsww-select {
                display: block;
                
                margin-bottom: 16px !important;
                max-width: 320px !important;
                width: 100% !important;
            }
            
        </style>
        
        <div class="fsww-meke-deposit-sc">
            <h4>' . __('How much would you like to deposit?', 'fsww') . '</h4>';


        $select = '
            <form id="add_to_cart" method="post">
                <input type="hidden" name="add-to-cart" value="">
                <input type="hidden" name="fsww_add_product" value="1">

                <select class="fsww-select" name="add-to-cart">
                    ' . $options . '
                </select>

                <input type="submit" class="button" value="' . __('Make Deposit', 'fsww') . '">

            </form>';


        $min = get_option('fsww_deposit_input_min', '0');
        $max = get_option('fsww_deposit_input_max', '1000');


        $input = ' <form method="post" action="">
                    <div class="woo-wallet-add-amount">
                        <label for="woo_wallet_balance_to_add">' . __( 'Enter amount', 'fsww' ) . '</label>
                 
                        <input type="number" step="0.01" value="' . $min . '" min="' . $min . '"  max="' . $max . '" name="fsww_balance_to_add" id="fsww_balance_to_add" class="input-text fsww-input" />
             
                        <input type="submit" name="fsww_add_to_wallet" class="button" value="' . __( 'Add', 'fsww' ) . '" />
                    </div>
                </form>';

        $end = '</div>';

        if(get_option('fsww_deposit_input', 'off') == 'on') {
            $output = $output . $input . $end;
        } else {
            $output = $output . $select . $end;
        }
        
        return $output;
        
    }
    
    public function register_custom_setting() {
        
        register_setting('fsww_general_options_group', 'fsww_refunds');
        register_setting('fsww_general_options_group', 'fsww_refund_rate');
        register_setting('fsww_general_options_group', 'fsww_partial_payments');

        register_setting('fsww_general_options_group', 'fsww_show_balance_in_menu');
        register_setting('fsww_general_options_group', 'fsww_show_balance_in_menu_right');

        register_setting('fsww_general_options_group', 'fsww_rows_per_page');
        register_setting('fsww_general_options_group', 'fsww_order_status');
	    
        register_setting('fsww_general_options_group', 'fsww_withdrawals');
        register_setting('fsww_general_options_group', 'fsww_withdrawals_paypal');
        register_setting('fsww_general_options_group', 'fsww_withdrawals_swift');
        register_setting('fsww_general_options_group', 'fsww_withdrawals_bitcoin');
        register_setting('fsww_general_options_group', 'fsww_withdrawals_bank_transfer');
        register_setting('fsww_general_options_group', 'fsww_withdrawals_bank_transfer_turkey');

        register_setting('fsww_general_options_group', 'fsww_transfers');
        register_setting('fsww_general_options_group', 'fsww_deposit');
        register_setting('fsww_general_options_group', 'fsww_transactions');

        register_setting('fsww_general_options_group', 'fsww_deposit_input');
        register_setting('fsww_general_options_group', 'fsww_deposit_input_min');
        register_setting('fsww_general_options_group', 'fsww_deposit_input_max');

        register_setting('fsww_general_options_group', 'fsww_disable_payment_methods');
        register_setting('fsww_general_options_group', 'fsww_show_all_users');

        register_setting('fsww_general_options_group', 'fsww_load_wallet_on');

        register_setting('fsww_general_options_group', 'fsww_refunds_button_position');


        register_setting('fsww_advanced_options_group', 'fsww_db_version');

    }
    
    public function account_alerts() { 
        
        require_once(dirname(__FILE__) . '/Wallet.php');
        
        $notices  = '<div class="woocommerce-info">';
        
        $status   = Wallet::lock_status(get_current_user_id());
        
        $show     = false;
        
        $fees     = WC()->cart->get_fees();
        
        $transaction_found = false;
        
        foreach($fees as $fee) {
            
            if($fee->tax_class == 'fsww_pfw' || $fee->name == __('Paid From Wallet', 'fsww')) {
             
                return true;
                
            }
            
        }    
        
        if($status['status'] == 'locked') {
                
            $notices .= __('Your wallet credit is locked for the following reason:', 'fsww') . '<br><b>' . $status['lock_message'] . '<br>' . __('If you think there is an error please contact us.', 'fsww') . '</b>';
            $show     = true;
            
        } 
        
        $notices .= '</div>';
        
        if($show) {
            
            echo $notices;    
            
        }
        
    }
    
    public function check_balance() {
        
        global $woocommerce;
        
        require_once(dirname(__FILE__) . '/Wallet.php');
        
        $balance  = floatval(Wallet::get_balance(get_current_user_id()));
        $total    = floatval($woocommerce->cart->total);
        
        if($balance >= $total) {
                
            return true;
            
        }
        
        return false;
        
    }
    
    // Add filters
    public function add_filters() {
        
        // Add payment gatway
		
		add_filter('woocommerce_payment_gateways', array($this, 'gateway'));
		   
        
        //add_filter('woocommerce_product_add_to_cart_text', array($this, 'custom_cart_button_text'));
        //add_filter('woocommerce_product_single_add_to_cart_text', array($this, 'custom_cart_button_text'));    // 2.1 +
        
        // Redirect to chechout if its a wallet cretit product
		if((get_option('woocommerce_cart_redirect_after_add') === 'yes') || (isset($_REQUEST['fsww_add_product']))) {
			
			add_filter('woocommerce_add_to_cart_redirect',  array($this, 'redirect_to_checkout'));
			
		}
        
        
        // Rempve wallet credit items from the cart
        //add_filter('woocommerce_add_cart_item_data',  array($this, 'clear_cart_items'));
        
        if(get_option('fsww_refunds', 'on') == 'on') {

            if(get_option('fsww_refunds_button_position', 'table') == 'table') {
                add_filter('woocommerce_my_account_my_orders_actions', array($this,'add_refund_request_button'), 100, 2);
            }

            if(get_option('fsww_refunds_button_position', 'table') == 'order_details') {
                add_filter('woocommerce_order_details_after_order_table', array($this, 'add_refund_request_button_order_page'), 100, 2);
            }

        }
        
        add_filter('widget_text','do_shortcode');
        
        if(get_option('fsww_show_balance_in_menu', 'on') == 'on') {
            
            add_filter('wp_nav_menu_items', array($this, 'in_menu_balance'), 10, 2);
            
        }
		
		add_filter('woocommerce_get_price_html', array($this, 'custom_price_message'), 10, 2);
		
		add_filter('woocommerce_available_variation', array($this, 'custom_price_message_variation'), 10, 3);
         
    }
	
	function cashback($order_status, $order_id) {
		
		$order = wc_get_order($order_id);
		
		//if($order->get_payment_method() != 'fsww') {
		
		
			require_once(dirname(__FILE__) . '/Wallet.php');

			$order = wc_get_order($order_id);

			if (count($order->get_items()) > 0){

				foreach ($order->get_items() as $item) {

					$product_id     = $item['product_id'];
					$variation_id   = $item['variation_id'];
					$subtotal       = $item['subtotal'];
					$amount         = '0';
					$cashback_value = '';

					
					/*//////////////*
					echo '<pre>'; 
					print_r($item);
					/**/
					if($variation_id == 0) {
						
						$cashback_value = get_post_meta($product_id, '_fsww_cashback', true);
						
					}else {
						
						$cashback_value = get_post_meta($variation_id, '_fsww_cashback', true);
						
					}

					if($cashback_value != '') { 

						if(strpos($cashback_value, '%') === false ) {

							$amount = $cashback_value;

						}else {

							$cashback_value = rtrim($cashback_value, "%");
							$amount         = ($subtotal)*($cashback_value/100);

						}

						Wallet::add_funds($order->get_user_id(), $amount, 0, __('Wallet Cashback', 'fsww'));

					}

				}
				//die();
			}

		//}

		return $order_status;
		
	}
	
	
	function custom_price_message_variation($data, $product, $variation) {
		
		$cashback = '';
		
		$cashback_value = get_post_meta($variation->get_ID(), '_fsww_cashback', true);
			
		if($cashback_value != '') { 

			if(strpos($cashback_value, '%') === false ) {

				$cashback_value = fsww_price($cashback_value);

			}

			$cashback = ' | ' . $cashback_value . __(' Cashback');

		}
		
		$data['price_html'] = $data['price_html'] . $cashback;
		return $data;
	}
	
	function custom_price_message($price) {
		
		global $product;
		
		$cashback = '';
		
		if($product != null) {
			
			if($product->is_type('simple')) {
			
				$cashback_value = get_post_meta($product->get_id(), '_fsww_cashback', true);

				if($cashback_value != '') { 

					if(strpos($cashback_value, '%') === false ) {

						$cashback_value = fsww_price($cashback_value);

					}

					$cashback = ' | ' . $cashback_value . __(' Cashback');

				}

			}
			
		}
	
		return $price . $cashback;
	
	}

    public function in_menu_balance($items, $args) {

        if(is_user_logged_in()) {

            if (strpos(strtolower($args->menu->slug), 'primary') !== false) {

                require_once(dirname(__FILE__) . '/Wallet.php');

                $output = '';
                $user_id = get_current_user_id();
                $balance = fsww_price(Wallet::get_balance($user_id));

                $output = __('Balance', 'fsww') . ': ' . $balance;

                $right = ( get_option('fsww_show_balance_in_menu_right', '') == 'on') ? 'fsww-right' : '';

                $base = get_permalink( get_option('woocommerce_myaccount_page_id') );
                $items .= '<li class="menu-item menu-item-type-custom menu-item-object-custom fsww-balance ' . $right . '"><a href="' . wc_get_endpoint_url('make-a-deposit','',$base) .'">' . $output . '</a></li>';

            }

        }
        
        return $items;
        
    }
    
    // Add funds callback
    public function add_funds_callback() {
	
	    require_once(dirname(__FILE__) . '/Wallet.php');
	
	    if(strpos($_POST['user_id'], '@') !== false) {
		    $user           = get_user_by('email', $_POST['user_id']);
	    } else {
		    $user           = get_user_by('login', $_POST['user_id']);
	    }
	
	    $user_id        = $user->ID;
	    
	    $amount         = $_POST['balance'];
	    $message        = $_POST['message'];
	    $status         = $_POST['status'];
	    
	    if($amount != 0) {
		    $message = '<p>' . fsww_price($amount) . ' ' . __('Have been added to your account balance', 'fsww') . '</p><br>' . $message;
		    $subject = __('Funds Have been added to your account', 'fsww');
		    $heading = __('Funds Have been added to your account', 'fsww');
		
		    $this->send_email($user_id, $message, $subject, $heading);
		
		    Wallet::add_funds($user_id, $amount, 0, __("Admin added funds", "fsww"));
	    }
	    
	    if($status == 'locked') {
		
		    Wallet::lock_account($user_id, $message);
		    
		
	    } elseif ($status == 'unlocked') {
		
		    Wallet::unlock_account($user_id);
		
	    }
	    
        $link = admin_url('admin.php?page=fsww-wallets');
        wp_redirect($link);
        die();
        
    }

    
    // create class instace
    public static function instance() {
        
        if(is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    
    }
    
    // Add WooCommerce Wallet payment gateway
    public function gateway($gateways) {
		
		global $pagenow;
		
		if ($pagenow == 'nav-menus.php') {
			return;
		}
        
        require_once(dirname(__FILE__) . '/Gateway.php');
        
        $gateways[] = 'WC_Gateway_FSWW';
        
        return $gateways;
    }
    
    //Register styles
    public function register_styles() {
        wp_enqueue_style('FSWW_style', plugins_url('/assets/styles/style.css', FSWW_FILE), array(),'1.1');
    }
    
    //Register scripts
    public function register_scripts() {
        wp_enqueue_script('FSWW_main', plugins_url('/assets/scripts/main.js', FSWW_FILE), array('jquery'), '2.0');

        $translation_array = array(
            'delete_this_item'     => __("Delete This item?", 'fsww')
        );
        wp_localize_script('FSWW_main', 'fsww', $translation_array);
    }
    
    // Frontend CSS
    public function additional_custom_styles() {
        wp_enqueue_style('FSWW_frontend', plugins_url('/assets/styles/frontend.css', FSWW_FILE), array(),'1.1');
    }
    
    //Add plugin menu
    public function add_menu_items() {
        
        add_menu_page(
            __('Wallets', 'fsww'), 
            __('Wallets', 'fsww'), 
            'manage_woocommerce', 
            'fsww-wallets', 
            array($this, 'wallets_page_callback'), 
            'dashicons-money',  
            55.5
        );
	

        add_submenu_page(
            'fsww-wallets',
            __('Add Funds To a User', 'fsww'),
            __('Add Funds To a User', 'fsww'),
            'manage_woocommerce',
            'fsww-add-funds',
            array($this, 'wallets_add_funds_page_callback')
        );

        
        if(get_option('fsww_refunds', 'on') == 'on') {
         
            add_submenu_page(
	            'fsww-wallets',
	            __('Refund Requests', 'fsww'),
	            __('Refund Requests', 'fsww'),
	            'manage_woocommerce',
	            'fsww-rr',
	            array($this, 'wallets_requests_page_callback')
	        );
            
        }
	
	    if(get_option('fsww_withdrawals', 'off') == 'on') {
		    add_submenu_page(
			    'fsww-wallets',
			    __('Withdrawal Requests', 'fsww'),
			    __('Withdrawal Requests', 'fsww'),
			    'manage_woocommerce',
			    'fsww-wr',
			    array($this, 'wallets_withdrawal_requests_page_callback')
		    );
	    }
	
	    
	    add_submenu_page(
            'fsww-wallets',
            __('Transactions', 'fsww'),
            __('Transactions', 'fsww'),
            'manage_woocommerce',
            'fsww-t',
            array($this, 'wallets_transactions_page_callback')
        );
	
	    add_submenu_page(
		    'fsww-wallets',
		    __('Import/Export' ,'fsww'),
		    __('Import/Export' ,'fsww'),
		    'manage_woocommerce',
		    'fsww-import-export',
		    array($this, 'wallets_import_export_page_callback')
	    );
	    
        
        
        add_submenu_page(
            'fsww-wallets',
            __('Settings' ,'fsww'),
            __('Settings' ,'fsww'),
            'manage_woocommerce',
            'fsww-settings',
            array($this, 'wallets_settings_page_callback')
        );
    }
	
	//page callback
	public function wallets_withdrawal_requests_page_callback() {
		
		require_once(dirname(__FILE__) . '/../pages/withdrawal_requests.php');
		
	}
    
    //page callback
    public function Wallets_page_callback() {
        
        require_once(dirname(__FILE__) . '/../pages/wallets.php');
        
    }
    
    //page callback
    public function wallets_settings_page_callback() {
        
        require_once(dirname(__FILE__) . '/../pages/settings.php');
        
    }
    
    //page callback
    public function wallets_import_export_page_callback() {
        
        require_once(dirname(__FILE__) . '/../pages/import-export.php');
        
    }
    
    //page callback
    public function wallets_requests_page_callback() {
        
        require_once(dirname(__FILE__) . '/../pages/requests.php');
        
    }
    
    //page callback
    public function wallets_add_funds_page_callback() {
        
        require_once(dirname(__FILE__) . '/../pages/add_funds.php');
        
    }
    
    //page callback
    public function wallets_transactions_page_callback() {
        
        require_once(dirname(__FILE__) . '/../pages/transactions.php');
        
    }
    
    // Add my account endpoints
    public function add_endpoints() {
        
        require_once(dirname(__FILE__) . '/Transactions_History_Endpoint.php');
        require_once(dirname(__FILE__) . '/Make_Deposit_Endpoint.php');
	    require_once(dirname(__FILE__) . '/Send_Money_Endpoint.php');
	    require_once(dirname(__FILE__) . '/Withdrawal_Requests_Endpoint.php');
        


	    if(get_option('fsww_transactions', 'on') == 'on') {
		    new Transactions_History_Endpoint();
	    }

	    if(get_option('fsww_deposit', 'on') == 'on') {
		    new Make_Deposit_Endpoint();
	    }

	    if(get_option('fsww_transfers', 'off') == 'on') {
		    new Send_Money_Endpoint();
	    }
	
	    if(get_option('fsww_withdrawals', 'off') == 'on') {
		    new Withdrawal_Requests_Endpoint();
	    }
        
    }
    
    // Dispaly notices
    public function admin_notices() {
        if(count($this->notices) > 0) {
            foreach($this->notices as $notice) {
                
                echo '<div class="updated"><p>' . $notice . '</p></div>';
                
            }
        }
    }
	
	
    
    public static function activation() {
        global $wpdb;
        
        $queries = array();
        
        $queries[] = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}fswcwallet ( 
                         user_id        INT(11)   NOT NULL, 
                         balance        TEXT      NOT NULL, 
                         last_deposit   DATETIME  NOT NULL, 
                         total_spent    TEXT      NOT NULL, 
                         status         TEXT      NOT NULL, 
                         lock_message   TEXT      NOT NULL, 
                         PRIMARY KEY (user_id)
                      );";
        
        
        $queries[] = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}fswcwallet_requests ( 
                         request_id      INT(11)        NOT NULL AUTO_INCREMENT, 
                         order_id        INT(11)        NOT NULL, 
                         status          VARCHAR(255)   NOT NULL, 
                         request_date    DATETIME       NOT NULL,
                         PRIMARY KEY (request_id),
                         UNIQUE (order_id)
                      );";
        
        $queries[] = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}fswcwallet_transaction ( 
                         transaction_id   INT(11)        NOT NULL AUTO_INCREMENT, 
                         order_id         INT(11)        NOT NULL, 
                         user_id          INT(11)        NOT NULL, 
                         type             VARCHAR(255)   NOT NULL, 
                         transaction_date DATETIME       NOT NULL,
                         amount            TEXT           NOT NULL,
                         PRIMARY KEY (transaction_id)
                      );";
	
	    $queries[] = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}fswcwallet_withdrawal_requests (
                         request_id      INT(11)        NOT NULL AUTO_INCREMENT,
                         user_id         INT(11)        NOT NULL,
                         amount          TEXT           NOT NULL,
                         payment_method  TEXT           NOT NULL,
                         address         TEXT           NOT NULL,
                         status          VARCHAR(255)   NOT NULL,
                         PRIMARY KEY (request_id)
                      );";
 
        foreach($queries as $query) {
            $wpdb->query($query);
        }
        
        FS_WC_Wallet::add_terms();

        self::create_product_if_not_exist();
        
        //file_put_contents(__DIR__.'/my_loggg.html', ob_get_contents());
                
    }
    
    public static function add_terms() {
        
        wp_insert_term(
            __('WooCommerce Wallet Credit', 'fsww'),
            'product_cat',
            array(
                'description'=> 'WooCommerce Wallet Credit Product',
                'slug' => 'fsww-wallet-credit'
            )
        );
        
    }
    
    public function plugin_init(){
        require_once(dirname(__FILE__) . "/../updater.php");

        load_plugin_textdomain('fsww', false, basename(dirname(FSWW_FILE)). '/languages/');
    }
    
    public static function set_encryption_key($key, $vi, $action = 'set') {
        
        $upload_directory = wp_upload_dir();
        $target_dir = $upload_directory['basedir'] . '/fsww_files/';

        if (!file_exists($target_dir)) {
            
            wp_mkdir_p($target_dir);

            $fp = fopen($target_dir . '.htaccess', 'w');
            fwrite($fp, 'deny from all');
            fclose($fp);

            $fp = fopen($target_dir . 'encryption_key.php', 'w');
            fwrite($fp, "<?php define(\"FSWW_ENCRYPTION_KEY\", \"" . $key . "\");\ndefine(\"FSWW_ENCRYPTION_VI\", \"" . $vi . "\");");
            fclose($fp);

            $fp = fopen($target_dir . 'index.php', 'w');
            fwrite($fp, '<?php');
            fclose($fp);
            
        }else if ($action = 'update'){
            
            $fp = fopen($target_dir . 'encryption_key.php', 'w');
            fwrite($fp, "<?php define(\"FSWW_ENCRYPTION_KEY\", \"" . $key . "\");\ndefine(\"FSWW_ENCRYPTION_VI\", \"" . $vi . "\");");
            fclose($fp);
            
        }
        
    }
    
    public static function encrypt_decrypt($action, $string) {

	    if(get_option('fsww_disable_encryption', 'off') == 'on') {
	        return $string;
        }

        $upload_directory = wp_upload_dir();
        $target_file = $upload_directory['basedir'] . '/fsww_files/encryption_key.php';

        if(!@include_once($target_file)) {
            FS_WC_Wallet::set_encryption_key('5RdRDCmG89DooltnMlUG', '2Ve2W2g9ANKpvQNXuP3w');
            @include_once($target_file);
        }

        if( ! defined("FSWW_ENCRYPTION_KEY")) {
            $secret_key = ENCRYPTION_KEY;
        } else {
            $secret_key = FSWW_ENCRYPTION_KEY;
        }

        if( ! defined("FSWW_ENCRYPTION_VI")) {
            $secret_iv = ENCRYPTION_VI;
        } else {
            $secret_iv  = FSWW_ENCRYPTION_VI;
        }

        if ( ( ! defined("FSWW_ENCRYPTION_KEY")) && ( ! defined("FSWW_ENCRYPTION_VI") ) ) {
            FS_WC_Wallet::set_encryption_key($secret_key, $secret_iv, 'update');
        }
        
        $output = false;

        if (!extension_loaded('openssl')) {
            return $string;
        }

        $encrypt_method = "AES-256-CBC";

        // hash
        $key = hash('sha256', $secret_key);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $secret_iv), 0, 16);

        if($action == 'encrypt') {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        } else if($action == 'decrypt'){
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }

        return $output;
        
    }
	
	public static function get_terms($taxo = 'shop_order_status', $args = array()) {
	
		if(version_compare(WOOCOMMERCE_VERSION, '2.2', '<')) {
		
			return get_terms( $taxo, $args );
			
		} else if(version_compare(WOOCOMMERCE_VERSION, '2.2', '>=')) {
		
			$s = wc_get_order_statuses();

			if(!empty($s)) {
			
				$i = 1; 
				
				foreach($s as $key => $val) {
			
					if(empty($key) || empty($val))
						continue;
						
					$status = new stdClass();
					$status->term_id = $i;
					$status->slug = $key;
					$status->name = $val;
					$statuses[$i] = $status; 

					$i++;
					
				}

				return $statuses; 
			}
			
		}
	}
    
}
