<?php
/**
 * Electro Child
 *
 * @package electro-child
 */

/**
 * Include all your custom code here
 */

add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );
function my_theme_enqueue_styles() {
	$parenthandle = 'parent-style'; // This is 'twentyfifteen-style' for the Twenty Fifteen theme.
	$theme        = wp_get_theme();
	wp_enqueue_style( $parenthandle,
		get_template_directory_uri() . '/style.css',
		array(),  // If the parent theme code has a dependency, copy it to here.
		$theme->parent()->get( 'Version' )
	);
	wp_enqueue_style( 'child-style',
		get_stylesheet_uri(),
		array( $parenthandle ),
		$theme->get( 'Version' ) // This only works if you have Version defined in the style header.
	);
}



add_action('electro_after_product_loop_rows','barprice_product_items');
function barprice_product_items(){
    // wp_enqueue_style( 'bootstrap-custom', 'https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css' );
    global $product;
    
    $share_from_total = get_field('product_share_count',$product->ID);
    $regular_price = round($product->regular_price);
    $stock_left = $product->get_stock_quantity();
    $stock_percentage = 100 - number_format(($share_from_total - $stock_left) * 100 / $share_from_total, 2);

    $products_html .= '
    <div class="progress">
        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="'.($share_from_total-$stock_left).'" aria-valuemin="0" aria-valuemax="'.$share_from_total.'" style="width: '.$stock_percentage.'%"></div>
    </div>
    <div class="p stock-left-value">'.(($stock_left).__(" remains","ubagod-one-dollor")).'</div>
    ';
    
    
    
    echo ($products_html);
 }
 add_action( 'woocommerce_after_shop_loop_item_title', 'barprice_product_items', 20 );


// var_dump(function_exists('product_loop_rows'));die;
// require_once 'inc/woocommerce/class-electro-wp-helper.php';
