<?php
/**
 * CBR Setting 
 *
 * @class   CBR_Admin_Notice
 * @package WooCommerce/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CBR_Admin_Notice class
 *
 * @since 1.0.0
 */
class CBR_Admin_Notice {
	
	/**
	 * Get the class instance
	 *
	 * @since  1.0.0
	 * @return CBR_Admin_Notice
	*/
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Instance of this class.
	 *
	 * @since 1.0.0
	 * @var object Class Instance
	*/
	private static $instance;
	
	/*
	* construct function
	*
	* @since 1.0.0
	*/
	function __construct() {
		$this->init();
    }

	/*
	* init function
	*
	* @since 1.0.0
	*/
    function init() {
        
		//callback for notices hook in admin
		add_action( 'admin_notices', array( $this, 'cbr_pro_admin_notice' ) );
		add_action('admin_init', array( $this, 'cbr_pro_plugin_notice_ignore' ) );

		add_action('cbr_settings_admin_notice', array( $this, 'cbr_settings_admin_notice' ) );
		
		//add_action( 'admin_notices', array( $this, 'admin_notice_after_update' ) );		
		//add_action('admin_init', array( $this, 'cbr_plugin_notice_ignore' ) );
		
    }
	
	/*
	* Display admin notice on plugin install or update
	*
	* @since 1.0.0
	*/
	public function admin_notice_after_update(){ 		
		
		if ( get_option('cbr_review_notice_ignore') ) return;
		
		$dismissable_url = esc_url(  add_query_arg( 'cbr-review-ignore-notice', 'true' ) );
		?>		
		<style>		
		.wp-core-ui .notice.cbr-dismissable-notice{
			position: relative;
			padding-right: 38px;
		}
		.wp-core-ui .notice.cbr-dismissable-notice a.notice-dismiss{
			padding: 9px;
			text-decoration: none;
		} 
		.wp-core-ui .button-primary.btn_review_notice {
			background: transparent;
			color: #03aa6f;
			border-color: #03aa6f;
			text-transform: uppercase;
			padding: 0 11px;
			font-size: 12px;
			height: 30px;
			line-height: 28px;
			margin: 5px 0 15px;
		}
		</style>	
		<div class="notice updated notice-success cbr-dismissable-notice">
			<a href="<?php echo $dismissable_url; ?>" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></a>
			<p>Hey, I noticed you are using the Country Based Restrictions Plugin - that’s awesome!</br>Could you please do me a BIG favor and give it a 5-star rating on WordPress to help us spread the word and boost our motivation?</p>
			<p>Eran Shor</br>Founder of zorem</p>
			<a class="button-primary btn_review_notice" target="blank" href="https://wordpress.org/support/plugin/woo-product-country-base-restrictions/reviews/#new-post">Ok, you deserve it</a>
			<a class="button-primary btn_review_notice" href="<?php echo $dismissable_url; ?>">Nope, maybe later</a>
			<a class="button-primary btn_review_notice" href="<?php echo $dismissable_url; ?>">I already did</a>
		</div>
	<?php 		
	}
	
	/*
	* Hide admin notice on dismiss of ignore-notice
	*
	* @since 1.0.0
	*/
	public function cbr_plugin_notice_ignore(){
		if (isset($_GET['cbr-review-ignore-notice'])) {
			update_option( 'cbr_review_notice_ignore', 'true' );
		}
	}

	public function cbr_settings_admin_notice() {
		$date_now = gmdate( 'Y-m-d' );
		if ( $date_now > '2022-06-30' ) {
			return;
		}
		include 'views/admin_message_panel.php';
	}

	
	/**
	 * CBR pro admin notice
	 *
	 * @since 1.0.0
	 */
	function cbr_pro_admin_notice() {
		if ( class_exists( 'Country_Based_Restrictions_PRO_Add_on' ) ) return;
		
		if ( get_option('Cbr_Pro_Plugin_Promotion_Notice') ) return;
		
		$message = __('Upgrade to Country Based Restrictions Pro and save time by applying bulk country restrictions by the product categories, tags, attributes and shipping classes. Enable and disable payment gateways by country and more... Use code <strong>CBRPRO20</strong> to get  20% off (valid by March 31st)', 'woo-product-country-base-restrictions');
		echo '<div class="updated notice"><h3 style="margin-bottom: 0;">Country Based Restriction Pro</h3><p>'. __( $message ) .' 
		<span style="display: block; margin: 0.5em 0.5em 0 0; clear: both;">
			<a class ="button-secondary" href="https://www.zorem.com/products/country-based-restriction-pro/" target="_blank">Upgrade to Pro</a>
			<a class ="button-secondary" href="' . esc_url(  add_query_arg( 'cbr-pro-plugin-ignore-notice', 'true' ) ) . '" class="dismiss-notice" target="_parent">Dismiss this notice</a></span>
		</p></div>';
	}
	
	/**
	 * CBR pro admin notice ignore
	 *
	 * @since 1.0.0
	 */
	function cbr_pro_plugin_notice_ignore(){
		
		if (isset($_GET['cbr-pro-plugin-ignore-notice'])) {
			update_option( 'Cbr_Pro_Plugin_Promotion_Notice', 'true' );
		}
	}
    
	
}

