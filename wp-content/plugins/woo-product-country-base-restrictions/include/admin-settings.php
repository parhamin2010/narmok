<?php
/**
 * CBR Setting 
 *
 * @class   CBR_Admin_Settings
 * @package WooCommerce/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CBR_Admin_Settings class
 *
 * @since 1.0.0
 */
class CBR_Admin_Settings {
	
	/**
	 * Get the class instance
	 *
	 * @since  1.0.0
	 * @return CBR_Admin_Settings
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
        
		add_action('admin_menu', array( $this, 'register_woocommerce_menu' ), 99 );

		//ajax save admin api settings
		add_action( 'wp_ajax_cbr_setting_form_update', array( $this, 'cbr_setting_form_update_callback') );
		
		if( isset($_GET['page']) && $_GET['page'] == 'woocommerce-product-country-base-restrictions' ){
			// Hook for add admin body class in settings page
			add_filter( 'admin_body_class', array( $this, 'cbr_post_admin_body_class' ), 100 );
		}
			
    }
    
    /*
	* Admin Menu add function
	*
	* @since 1.0.0
	* WC sub menu 
	*/
	public function register_woocommerce_menu() {
		add_submenu_page( 'woocommerce', 'Country Restrictions', 'Country Restrictions', 'manage_options', 'woocommerce-product-country-base-restrictions', array( $this, 'woocommerce_product_country_restrictions_page_callback' ) );
	}
	
	/*
	* add class in body tag
	*
	* @since 1.0.0
	*/
	function cbr_post_admin_body_class($body_class) {
		
		$body_class .= ' woocommerce-country-based-restrictions';
 
    	return $body_class;
	}
	
	/*
	* settings form save for Setting tab
	*
	* @since 1.0.0
	*/
	function cbr_setting_form_update_callback(){			
		
		if ( ! empty( $_POST ) && check_admin_referer( 'cbr_setting_form_action', 'cbr_setting_form_nonce_field' ) ) {
			
			$data = $this->get_general_settings();						
			
			foreach( $data as $key => $val ){				
				if(isset($_POST[ $key ])){						
					update_option( $key, wc_clean($_POST[ $key ]) );
				}
			}
			
			$data2 = $this->get_visibility_message_settings();						
			
			foreach( $data2 as $key => $val ){				
				if(isset($_POST[ $key ])){						
					update_option( $key, wc_clean($_POST[ $key ]) );
				}
			}
			
			$data3 = $this->get_hide_completely_settings();						
			
			foreach( $data3 as $key => $val ){				
				if(isset($_POST[ $key ])){						
					update_option( $key, wc_clean($_POST[ $key ]) );
				}
			}
			
			do_action('save_cbr_pro_setting_option');
			
			update_option( 'product_visibility', sanitize_text_field($_POST[ 'product_visibility' ]) );
			
			if($_POST[ 'product_visibility' ] == 'hide_catalog_visibility'){
				update_option( 'wpcbr_hide_restricted_product_variation', sanitize_text_field($_POST[ 'wpcbr_hide_restricted_product_variation1' ]) );
				update_option( 'wpcbr_make_non_purchasable', sanitize_text_field($_POST[ 'wpcbr_make_non_purchasable' ]) );
				if( class_exists( 'Country_Based_Restrictions_PRO_Add_on' ) && $_POST[ 'wpcbr_make_non_purchasable' ] == '1' ){
					update_option( 'wpcbr_hide_product_price', sanitize_text_field($_POST[ 'wpcbr_hide_product_price1' ]) );
				}
			}
			if($_POST[ 'product_visibility' ] == 'show_catalog_visibility'){
				update_option( 'wpcbr_hide_restricted_product_variation', sanitize_text_field($_POST[ 'wpcbr_hide_restricted_product_variation2' ]) );
				
				if( class_exists( 'Country_Based_Restrictions_PRO_Add_on' ) ){
					update_option( 'wpcbr_hide_product_price', sanitize_text_field($_POST[ 'wpcbr_hide_product_price2' ]) );
				}
			}
			echo json_encode( array('success' => 'true') );die();
	
		}
	}
	
	/*
	* callback for CBR page
	*
	* @since 1.0.0
	*/
	public function woocommerce_product_country_restrictions_page_callback(){
		global $fzpcr;
		$tab = isset( $_GET['tab'] ) ? sanitize_text_field($_GET['tab']) : '';
		?>
		<div class="zorem-layout__header">
			<img class="zorem-layout__header-logo" src="<?php echo esc_url($fzpcr->plugin_dir_url() . 'assets/images/cbr-logo.png'); ?>">			
			<div class="woocommerce-layout__activity-panel">
				<div class="woocommerce-layout__activity-panel-tabs">
					<button type="button" id="activity-panel-tab-help" class="components-button woocommerce-layout__activity-panel-tab">
						<span class="dashicons dashicons-editor-help"></span>
						Help 
					</button>
				</div>
				<div class="woocommerce-layout__activity-panel-wrapper">
					<div class="woocommerce-layout__activity-panel-content" id="activity-panel-true">
						<div class="woocommerce-layout__activity-panel-header">
							<div class="woocommerce-layout__inbox-title">
								<p class="css-activity-panel-Text">Documentation</p>            
							</div>								
						</div>
						<div>
							<ul class="woocommerce-list woocommerce-quick-links__list">
								<li class="woocommerce-list__item has-action">
									<?php
									$support_link = 'https://wordpress.org/support/plugin/woo-product-country-base-restrictions/#new-topic-0' ;
									?>
									<a href="<?php echo esc_url( $support_link ); ?>" class="woocommerce-list__item-inner" target="_blank" >
										<div class="woocommerce-list__item-before">
											<img src="<?php echo esc_url($fzpcr->plugin_dir_url(__FILE__) . 'assets/images/get-support-icon.svg'); ?>">	
										</div>
										<div class="woocommerce-list__item-text">
											<span class="woocommerce-list__item-title">
												<div class="woocommerce-list-Text">Get Support</div>
											</span>
										</div>
										<div class="woocommerce-list__item-after">
											<span class="dashicons dashicons-arrow-right-alt2"></span>
										</div>
									</a>
								</li>            
								<li class="woocommerce-list__item has-action">
									<a href="https://www.zorem.com/docs/country-based-restrictions-for-woocommerce/?utm_source=wp-admin&utm_medium=CBRDOCU&utm_campaign=add-ons" class="woocommerce-list__item-inner" target="_blank">
										<div class="woocommerce-list__item-before">
											<img src="<?php echo esc_url($fzpcr->plugin_dir_url(__FILE__) . 'assets/images/documentation-icon.svg'); ?>">
										</div>
										<div class="woocommerce-list__item-text">
											<span class="woocommerce-list__item-title">
												<div class="woocommerce-list-Text">Documentation</div>
											</span>
										</div>
										<div class="woocommerce-list__item-after">
											<span class="dashicons dashicons-arrow-right-alt2"></span>
										</div>
									</a>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</div>	
		</div>
		<div class="woocommerce cbr_admin_layout">
			<div class="cbr_admin_content">
				<input id="tab1" type="radio" name="tabs" class="cbr_tab_input" data-tab="settings" data-label="<?php _e('Settings', 'woocommerce'); ?>" checked>
				<label for="tab1" class="cbr_tab_label first_label" ><?php _e('Settings', 'woocommerce'); ?></label>
				<input id="tab4" type="radio" name="tabs" class="cbr_tab_input" data-tab="go-pro" data-label="<?php _e('Go Pro', 'woo-product-country-base-restrictions'); ?>" <?php if($tab == 'go-pro'){ echo 'checked'; } ?>>
				<label for="tab4" class="cbr_tab_label" ><?php _e('Go Pro', 'woo-product-country-base-restrictions'); ?></label>
				<div class="menu_devider"></div>
				<?php require_once( 'views/cbr_setting_tab.php' ); ?>
				<?php require_once( 'views/cbr_addons_tab.php' ); ?>
			</div>
		</div>
		<div id="cbr-toast-example" aria-live="assertive" aria-atomic="true" aria-relevant="text" class="mdl-snackbar mdl-js-snackbar">
			<div class="mdl-snackbar__text"></div>
			<button type="button" class="mdl-snackbar__action"></button>
		</div>
	   <?php
	}

    /**
     * Get all the settings for this plugin for @see woocommerce_admin_fields() function.
     *
	 * @since 1.0.0
     * @return array Array of settings for @see woocommerce_admin_fields() function.
     */
	function get_general_settings() {
		
        $settings = array(
			'wpcbr_force_geo_location' => array(
				'title'		=> __( 'Force Geolocation', 'woo-product-country-base-restrictions' ),
				'type'		=> 'checkbox',
				'default'	=> 'no',
				'show'		=> true,
				'id'		=> 'wpcbr_force_geo_location',
				'class'		=> 'checkbox-left',
				'label'		=> 'Enable plugin',
				'tooltip'		=> __( "Enable this option to detect the customer country only by the WooCommerce geo-location and to ignore the customer shipping country (if logged in)", 'woo-product-country-base-restrictions' ),
			),
			'wpcbr_debug_mode' => array(
				'title'		=> __( 'Enable Debug Toolbar', 'woo-product-country-base-restrictions' ),
				'type'		=> 'checkbox',
				'default'	=> 'no',
				'show'		=> true,
				'id'		=> 'wpcbr_debug_mode',
				'class'		=> 'checkbox-left',
				'label'		=> 'Enable plugin',
				'tooltip'		=> __( "Enable this option to show detected geo-location country top of header in frontend.", 'woo-product-country-base-restrictions' ),
			),
        );
        return  $settings;
    }
	
	/**
     * Get all the settings for this plugin for @see woocommerce_admin_fields() function.
     *
	 * @since 1.0.0
     * @return array Array of settings for @see woocommerce_admin_fields() function.
     */
	function get_visibility_message_settings() {
		
        $settings = array(
			'wpcbr_default_message' => array(
				'title'		=> __( 'Product restriction message', 'woo-product-country-base-restrictions' ),
				'tooltip'	=> __( "This message show on product page when product is not purchasable. Default message : Sorry, this product is not available in your country.", 'woo-product-country-base-restrictions' ),
				'placeholder'	=> __( "Sorry, this product is not available to purchase in your country.", 'woo-product-country-base-restrictions' ),
				'type'		=> 'textarea',
				'show'		=> true,
				'id'		=> 'wpcbr_default_message',
				'class'		=> '',
			),
			'wpcbr_message_position' => array(
				'title'		=> __( 'Product restriction message position', 'woo-product-country-base-restrictions' ),
				'tooltip'		=> __( "Default : After add to cart. This message will show on product page when product is not purchasable.", 'woo-product-country-base-restrictions'),
				'desc_tip'	=> __( "Use the shortcode [cbr_message_position] in your product template.", 'woo-product-country-base-restrictions' ),
				'type'		=> 'dropdown',
				'show'		=> true,
				'id'		=> 'wpcbr_message_position',
				'class'		=> '',
				'default'	=> '33',
				'options'	=> array(
					'3'			=> __( 'Before title', 'woo-product-country-base-restrictions' ),
					'8'			=> __( 'After title', 'woo-product-country-base-restrictions' ),
					'13'		=> __( 'After price', 'woo-product-country-base-restrictions' ),
					'23'		=> __( 'After short description', 'woo-product-country-base-restrictions' ),
					'33'		=> __( 'After add to cart', 'woo-product-country-base-restrictions' ),
					'43'		=> __( 'After meta', 'woo-product-country-base-restrictions' ),
					'53'		=> __( 'After sharing', 'woo-product-country-base-restrictions' ),
					'custom_shortcode'		=> __( 'Use shortcode', 'woo-product-country-base-restrictions' ),
				)
			),
        );
		$settings = apply_filters( "cbr_general_setting_option_data_array", $settings );
        return  $settings;
    }
	
	/**
     * Get all the settings for this plugin for @see woocommerce_admin_fields() function.
     *
	 * @since 1.0.0
     * @return array Array of settings for @see woocommerce_admin_fields() function.
     */
	function get_hide_completely_settings() {
		$page_list = wp_list_pluck( get_pages(), 'post_title', 'ID' );
        $settings = array(
			'wpcbr_redirect_404_page' => array(
			  'type'		=> 'checkbox',
			  'title'		=> __( 'Redirect the 404 page', 'woo-product-country-base-restrictions' ),				
			  'show'		=> true,
			  'default'	=> 'no',
			  'class'     => 'pro-feature',
			  'id'		=> 'wpcbr_redirect_404_page',
			  'label'		=> 'Enable plugin',
			  'tooltip'     => __( 'Enable this option to redirect 404 error page to shop page.','woo-product-country-base-restrictions'),
		  ),
		  'wpcbr_choose_the_page_to_redirect' => array(
			  'type'		=> 'dropdown',
			  'title'		=> __( 'Choose the page to redirect', 'woo-product-country-base-restrictions' ),				
			  'show'		=> true,
			  'default'	=> '',
			  'class'     => 'pro-feature',
			  'id'		=> 'wpcbr_choose_the_page_to_redirect',
			  'label'		=> 'Enable plugin',
			  'options'	=> $page_list,
			  'tooltip'     => __( 'Choose the page for redirect 404 error page to selected page.','woo-product-country-base-restrictions'),
		  ),
        );
        return  $settings;
    }

	
	/**
     * Get all the settings for this plugin for @see woocommerce_admin_fields() function.
     *
	 * @since 1.0.0
     * @return array Array of settings for @see woocommerce_admin_fields() function.
     */
	function get_product_settings() {
        
		$settings = array(
			'wpcbr_hide_restricted_product_variation1' => array(
				'title'		=> __( 'Hide Product Variations', 'woo-product-country-base-restrictions' ),
				'type'		=> 'checkbox',
				'default'	=> 'no',
				'show'		=> true,
				'id'		=> 'wpcbr_hide_restricted_product_variation',
				'class'		=> '',
				'label'		=> 'Enable plugin',
				'tooltip'		=> __( "Enable this option to hide the restricted product variations form the product variations selection on variable product page.", 'woo-product-country-base-restrictions' ),
			),
			'wpcbr_make_non_purchasable' => array(
				'title'		=> __( 'Make non-purchasable', 'woo-product-country-base-restrictions' ),
				'type'		=> 'checkbox',
				'default'	=> 'no',
				'show'		=> true,
				'id'		=> 'wpcbr_make_non_purchasable',
				'class'		=> '',
				'label'		=> 'Enable plugin',
				'tooltip'		=> __( "Enable this option to make products non-purchasable (i.e. product can't be added to the cart).", 'woo-product-country-base-restrictions' ),
			),
        );
		$settings = apply_filters( "cbr_hide_catelog_option_data_array", $settings );
        return  $settings;
    }
	
	/**
     * Get all the settings for this plugin for @see woocommerce_admin_fields() function.
     *
	 * @since 1.0.0
     * @return array Array of settings for @see woocommerce_admin_fields() function.
     */
	function get_product_catelog_settings() {
        
		$settings = array(
		'wpcbr_hide_restricted_product_variation2' => array(
				'title'		=> __( 'Hide Product Variations', 'woo-product-country-base-restrictions' ),
				'type'		=> 'checkbox',
				'default'	=> 'no',
				'show'		=> true,
				'id'		=> 'wpcbr_hide_restricted_product_variation',
				'class'		=> '',
				'label'		=> 'Enable plugin',
				'tooltip'		=> __( "Enable this option to hide the restricted product variations form the product variations selection on variable product page.", 'woo-product-country-base-restrictions' ),
			),
        );
		$settings = apply_filters( "cbr_catelog_visible_option_data_array", $settings );
 
		return  $settings;
    }
	
	/**
     * Get all the settings for this plugin for @see woocommerce_admin_fields() function.
     *
	 * @since 1.0.0
     * @return array Array of settings for @see woocommerce_admin_fields() function.
     */
	function get_general_widget_option() {
        
		$settings = apply_filters( "cbr_general_widget_option_data_array", array() );
 
		return  $settings;
    }
	
	
	/*
	* get html of fields
	*
	* @since 1.0.0
	*/
	public function get_html_general_setting( $arrays ){
		
		$checked = '';
		?>
		<?php foreach( (array)$arrays as $id => $array ){
			if($array['show']){	
			?>
			<?php if($array['class'] == 'checkbox-left'){ ?>
			<tr valign="top" class="border_1 <?php echo $array['class'];?>">
				<?php 
				if(isset($array['id']) && get_option($array['id'])){
					$checked = 'checked';
				} else{
					$checked = '';
				} 
				?>					
				<th scope="row" class="titledesc" colspan="2">
					<label class="checkbx-label" for="<?php echo $id?>">
						<input type="hidden" name="<?php echo $id?>" value="0">
						<input type="checkbox" id="<?php echo $id?>" name="<?php echo $id?>" class="checkbox-input" <?php echo $checked?> value="1">
						<?php echo $array['title']?><?php if(isset($array['title_link'])){ echo $array['title_link']; } ?>
						<?php if( isset($array['tooltip']) ){?>
							<span class="woocommerce-help-tip tipTip" title="<?php echo $array['tooltip']?>"></span>
						<?php } ?>
					</label>
				</th>
			</tr>
			<?php } elseif( $array['type'] == 'textarea' ){ ?>
				<tr valign="top" class="border_1 <?php echo $array['class'];?>">
					<th scope="row" class="titledesc" >
						<label for="<?php echo $id?>">
							<?php echo $array['title']?><?php if(isset($array['title_link'])){ echo $array['title_link']; } ?>
							<?php if( isset($array['tooltip']) ){?>
								<span class="woocommerce-help-tip tipTip" title="<?php echo $array['tooltip']?>"></span>
							<?php } ?>
						</label>
					</th>
					<td class="forminp"  <?php if($array['type'] == 'desc'){ ?> colspan=2 <?php } ?>>
						<fieldset>
						<textarea rows="3" cols="20" class="input-text regular-input" type="textarea" name="<?php echo $id?>" id="<?php echo $id?>" style="" placeholder="<?php if(!empty($array['placeholder'])){echo $array['placeholder'];} ?>"><?php if(!empty(get_option($array['id']))){echo stripslashes(get_option($array['id'])); }?></textarea>
						</fieldset><p class="description"><?php echo (isset($array['desc_tip']))? $array['desc_tip']: ''?></p>
					</td>
				</tr>
			<?php }  elseif( isset( $array['type'] ) && $array['type'] == 'dropdown' ){?>
				<tr valign="top" class="border_1 <?php echo $array['class'];?>">
					<th scope="row" class="titledesc" >
						<label for="<?php echo $id?>">
							<?php echo $array['title']?><?php if(isset($array['title_link'])){ echo $array['title_link']; } ?>
							<?php if( isset($array['tooltip']) ){?>
								<span class="woocommerce-help-tip tipTip" title="<?php echo $array['tooltip']?>"></span>
							<?php } ?>
						</label>
					</th>
					<td class="forminp"  <?php if($array['type'] == 'desc'){ ?> colspan=2 <?php } ?>>
					<?php
						if( isset($array['multiple']) ){
							$multiple = 'multiple';
							$field_id = $array['multiple'];
						} else {
							$multiple = '';
							$field_id = $id;
						}
					?>
					<fieldset>
						<select class="select" id="<?php echo $field_id?>" name="<?php echo $id?>" <?php echo $multiple;?>>    <?php foreach((array)$array['options'] as $key => $val ){?>
								<?php
									$selected = '';
									if( isset($array['multiple']) ){
										if (in_array($key, (array)$this->data->$field_id ))$selected = 'selected';
									} else {
										if( get_option($array['id']) == (string)$key )$selected = 'selected';
									}
								
								?>
								<option value="<?php echo $key?>" <?php echo $selected?> ><?php echo $val?></option>
							<?php } ?><p class="description"><?php echo (isset($array['desc']))? $array['desc']: ''?></p>
						</select><p class="description"><?php echo (isset($array['desc_tip']))? $array['desc_tip']: ''?></p>
					</fieldset>
					</td>
				</tr>
			<?php } ?>
		<?php } } ?>
	<?php 
	}


    /*
	* get html of fields
	*
	* @since 1.0.0
	*/
	public function get_html( $arrays ){
		
		$checked = '';
		?>
		<table class="form-table">
			<tbody>
            	<?php foreach( (array)$arrays as $id => $array ){
					if($array['show']){	
					?>
                	<?php if($array['type'] == 'title'){ ?>
                		<tr valign="top titlerow">
                        	<th colspan="2"><h3><?php echo $array['title']?></h3></th>
                        </tr>    	
                    <?php continue;} ?>
				<tr valign="top" class="<?php echo $array['class'];?> border_1">
					<?php if($array['type'] != 'desc' && isset($array['title']) ){ ?>										
					<th scope="row" class="titledesc"  >
						<label for=""><?php echo $array['title']?><?php if(isset($array['title_link'])){ echo $array['title_link']; } ?>
							<?php if( isset($array['tooltip']) ){?>
                            	<span class="woocommerce-help-tip tipTip" title="<?php echo $array['tooltip']?>"></span>
                            <?php } ?>
                        </label>
					</th>
					<?php } ?>
					<td class="forminp"  <?php if($array['type'] == 'desc'){ ?> colspan=2 <?php } ?>>
                    	<?php if( $array['type'] == 'checkbox' ){								

								if(isset($array['id']) && get_option($array['id'])){
									$checked = 'checked';
								} else{
									$checked = '';
								} 
							
							if(isset($array['disabled']) && $array['disabled'] == true){
								$disabled = 'disabled';
								$checked = '';
							} else{
								$disabled = '';
							}							
							?>
						<?php if($array['class'] == 'toggle'){?>
							<input type="hidden" name="<?php echo $id?>" value="0"/>
							<input class="tgl tgl-flat-cev" id="<?php echo $id?>" name="<?php echo $id?>" type="checkbox" <?php echo $checked ?> value="1" <?php echo $disabled; ?>/>
							<label class="tgl-btn" for="<?php echo $id ?>"></label>
							<p class="description"><?php echo (isset($array['desc']))? $array['desc']: ''?></p>
						<?php } else { ?>
							<span class="checkbox">
							<label class="checkbx-label" for="<?php echo $id?>">
								<input type="hidden" name="<?php echo $id?>" value="0"/>
								<input type="checkbox" id="<?php echo $id?>" name="<?php echo $id?>" class="checkbox-input" <?php echo $checked ?> value="1" <?php echo $disabled; ?>/>
							</label><p class="description"><?php echo (isset($array['desc']))? $array['desc']: ''?></p>
						</span>
						<?php } ?>
						<?php } elseif( $array['type'] == 'textarea' ){ ?>
                                        <fieldset>
                                        <textarea rows="3" cols="20" class="input-text regular-input" type="textarea" name="<?php echo $id?>" id="<?php echo $id?>" style="" placeholder="<?php if(!empty($array['placeholder'])){echo $array['placeholder'];} ?>"><?php if(!empty(get_option($array['id']))){echo stripslashes(get_option($array['id'])); }?></textarea>
                                        </fieldset>
                        <?php } elseif( $array['type'] == 'color' ){ ?>
                                        <fieldset>
                                              <input name="<?php echo $id?>" type="text" id="<?php echo $id?>" value="<?php echo get_option($array['id'])?>">
                                            <span class="slider round"></span>
                                        </fieldset>
                        <?php }  elseif( isset( $array['type'] ) && $array['type'] == 'dropdown' ){?>
                        	<?php
								if( isset($array['multiple']) ){
									$multiple = 'multiple';
									$field_id = $array['multiple'];
								} else {
									$multiple = '';
									$field_id = $id;
								}
							?>
                        	<fieldset>
								<select class="select" id="<?php echo $field_id?>" name="<?php echo $id?>" <?php echo $multiple;?>>    <?php foreach((array)$array['options'] as $key => $val ){?>
                                    	<?php
											$selected = '';
											if( isset($array['multiple']) ){
												if (in_array($key, (array)$this->data->$field_id ))$selected = 'selected';
											} else {
												if( get_option($array['id']) == (string)$key )$selected = 'selected';
											}
                                        
										?>
										<option value="<?php echo $key?>" <?php echo $selected?> ><?php echo $val?></option>
                                    <?php } ?><p class="description"><?php echo (isset($array['desc']))? $array['desc']: ''?></p>
								</select><p class="description"><?php echo (isset($array['desc_tip']))? $array['desc_tip']: ''?></p>
							</fieldset>
                        <?php } elseif( $array['type'] == 'title' ){?>
						<?php }
						elseif( $array['type'] == 'label' ){ ?>
							<fieldset>
                               <label><?php echo $array['value']; ?></label>
                            </fieldset>
						<?php }
						elseif( $array['type'] == 'button' ){ ?>
							<fieldset>
								<button class="button-primary btn_green2 <?php echo $array['button_class'];?>" <?php if($array['disable']  == 1){ echo 'disabled'; }?>><?php echo $array['label'];?></button>
							</fieldset>
						<?php }
						elseif( $array['type'] == 'radio' ){ ?>
							<fieldset>
                            	<ul>
									<?php foreach((array)$array['options'] as $key => $val ){?>
									<li><label><input name="product_visibility" value="<?php echo $key; ?>" type="radio" style="" class="product_visibility" <?php if( get_option($array['id']) == $key ) { echo 'checked'; } ?> /><?php echo $val;?><br></label></li>
                                    <?php } ?>
                                 </ul>
							</fieldset>
						<?php }
						else { ?>
                                                    
                        	<fieldset>
                                <input class="input-text regular-input " type="text" name="<?php echo $id?>" id="<?php echo $id?>" style="" value="<?php echo get_option($array['id'])?>" placeholder="<?php if(!empty($array['placeholder'])){echo $array['placeholder'];} ?>">
                            </fieldset>
                        <?php } ?>
                        
					</td>
				</tr>
	<?php } } ?>
			</tbody>
		</table>
	<?php 
	}
}
