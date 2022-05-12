<?php

add_action( 'wp_enqueue_scripts', 'enqueue_parent_styles' );

function enqueue_parent_styles() {

    wp_enqueue_style( 'open-shop', get_template_directory_uri().'/style.css' );

}



 function barprice_product_items(){
    wp_enqueue_style( 'bootstrap-custom', 'https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css' );
    global $product;
    $share_from_total = get_field('product_share_count',$product->ID);
    $regular_price = round($product->regular_price);
    $stock_left = $product->get_stock_quantity();
    $stock_percentage = 100 - number_format(($share_from_total - $stock_left) * 100 / $share_from_total, 2);
    echo '
    <div class="progress">
        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="'.($share_from_total-$stock_left).'" aria-valuemin="0" aria-valuemax="'.$share_from_total.'" style="width: '.$stock_percentage.'%"></div>
    </div>
    <div class="p stock-left-value">'.(($stock_left).__(" remains","open-shop-child")).'</div>
    ';
 }
 add_action( 'woocommerce_after_shop_loop_item_title', 'barprice_product_items', 20 );