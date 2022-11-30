<?php
class Transactions_History_Endpoint {


	public static $endpoint = 'transactions-history';

	
	public function __construct() {
		// Actions used to insert a new endpoint in the WordPress.
		add_action('init', array($this, 'add_endpoints'));
		add_filter('query_vars', array($this, 'add_query_vars'));

		// Change the My Accout page title.
		add_filter('the_title', array($this, 'endpoint_title'));

		// Insering your new tab/page into the My Account page.
		add_filter('woocommerce_account_menu_items', array($this, 'new_menu_items'));
		add_action('woocommerce_account_' . self::$endpoint . '_endpoint', array($this, 'endpoint_content'));
	}

    
	public function add_endpoints() {
		add_rewrite_endpoint(self::$endpoint, EP_ROOT | EP_PAGES);
	}

	public function add_query_vars($vars) {
		$vars[] = self::$endpoint;

		return $vars;
	}

	public function endpoint_title($title) {
		global $wp_query;

		$is_endpoint = isset($wp_query->query_vars[self::$endpoint]);

		if ($is_endpoint && ! is_admin() && is_main_query() && in_the_loop() && is_account_page()) {
			// New page title.
			$title = __('Transactions History', 'fsww');

			remove_filter('the_title', array($this, 'endpoint_title'));
		}

		return $title;
	}

	
	public function new_menu_items($items) {
		// Remove the logout menu item.
		$logout = $items['customer-logout'];
		unset($items['customer-logout']);

		// Insert your custom endpoint.
		$items[self::$endpoint] = __('Transactions History', 'fsww');

		// Insert back the logout item.
		$items['customer-logout'] = $logout;

		return $items;
	}

	/**
	 * Endpoint HTML content.
	 */
	public function endpoint_content() {
		
        require_once(dirname(__FILE__) . '/../transactions_table_html.php');

        echo "<div class=\"fsww-balance-tr\"><strong>" . __("Your current balance is: ", "fsww") . "</strong>" . do_shortcode("[fsww_balance]") . "</div><br>";
        echo '<a href="?print=transactions" target="_blank">' . __('Show printable view', 'fsww') . '</a>';

        transactions_table_html(get_current_user_id(), true);
        
	}

	/**
	 * Plugin install action.
	 * Flush rewrite rules to make our custom endpoint available.
	 */
	public static function install() {
		flush_rewrite_rules();
	}
}
