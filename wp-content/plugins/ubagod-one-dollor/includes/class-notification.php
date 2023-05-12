<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

if (!defined('UBA_ONE_DOLLOR_RUN')) exit;
// require UBA_ONE_DOLLOR_DIR.'lib/phpmailer/Exception.php';
// require UBA_ONE_DOLLOR_DIR.'lib/phpmailer/PHPMailer.php';
// require UBA_ONE_DOLLOR_DIR.'lib/phpmailer/SMTP.php';


class OD_NOTIFICATION{

    protected $product_users = array();
    
    public function __construct()
    {
        add_filter( 'manage_product_posts_columns', array($this, 'set_custom_edit_product_columns') );
        add_action( 'manage_product_posts_custom_column' , array($this, 'custom_product_column'));
        
        wp_enqueue_script( 'ob_backend', UBA_ONE_DOLLOR_ASSET_DIR.'js/od_backend.js',array(), '1.0.0', true );
        wp_localize_script( 'ob_backend', 'my_ajax_object', array( 
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce(date('ymd'))
            ) 
        );

        add_action( 'wp_ajax_send_notification',  array($this, 'send_notification') );
        add_action( 'wp_ajax_nopriv_send_notification', array($this, 'send_notification') );
    }



    // Add the custom columns to the product post type:
    public function set_custom_edit_product_columns($columns) {
        $columns['do_lottery'] = __( 'Do Lottery', 'ubagod-one-dollor' );
        return $columns;
    }

    // Add the data to the custom columns for the product post type:
    public function custom_product_column( $column ) {
        wp_enqueue_style( 'bootstrap-custom', 'https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css' );
        global $post;
        switch ( $column ) {
            case 'do_lottery' :
                echo "
                <form id='lottery_actions_".$post->ID."' onsubmit='return false;'>
                    <select style='width:100%' name='action'>
                        <option value='send_notification_for_do_lottery'>Notification for lottery</option>
                    </select>
                    <a href='#' class='btn btn-primary w-100 mt-1' onclick='do_lottery(".$post->ID.")'>Run</a>
                <form>
                ";
        }

    }

    // get users that buy the product
    public function get_users_product($product_id = ''){
        if(!$product_id) return false;
        
        $product_obj = new WC_Product($product_id);
        $pdetail['product_detail'] = $product_obj->get_data();
        
        global $wpdb;
        $statuses = array_map( 'esc_sql', wc_get_is_paid_statuses() );

        $customer_emails = $wpdb->get_col("
            SELECT pm.meta_value FROM {$wpdb->posts} AS p
            INNER JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id
            INNER JOIN {$wpdb->prefix}woocommerce_order_items AS i ON p.ID = i.order_id
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS im ON i.order_item_id = im.order_item_id
            WHERE p.post_status IN ( 'wc-" . implode( "','wc-", $statuses ) . "' )
            AND pm.meta_key IN ( '_billing_email' )
            AND im.meta_key IN ( '_product_id', '_variation_id' )
            AND im.meta_value = $product_id
        ");

        $pdetail['user_details'] = $customer_emails;

        return $pdetail;
    }

    public function send_notification(){
        if(!wp_verify_nonce($_REQUEST['nonce'],date('ymd'))){
            wp_send_json_success(array('error' => 'nonce'));
            exit;
        }
        if(!$_REQUEST['product_id']){
            wp_send_json_success(array('error' => 'pid'));
            exit;
        }


        date_default_timezone_set('UTC');
        $UTCTime = date('Y-m-d H:i',strtotime('+1 hour'));
        date_default_timezone_set('Turkey');
        $IstanbulTime = date('Y-m-d H:i',strtotime('+1 hour'));
        date_default_timezone_set('Iran');
        $TehranTime = date('Y-m-d H:i',strtotime('+1 hour'));
        
        $users_product = $this->get_users_product($_REQUEST['product_id']);
        add_image_size('one_md_img', 450, 450, true );

        $replacement = [];
        $replacement['product_img']['value'] = isset($users_product['product_detail']['image_id']) ? wp_get_attachment_image_src( get_post_thumbnail_id( $users_product['product_detail']['image_id'] ), 'single-post-thumbnail' ) : '#';
        $replacement['product_name']['value'] = isset($users_product['product_detail']['name']) ?  $users_product['product_detail']['name'] : '-';
        $replacement['lottery_time']['value'] = "<p>TR/Istanbul: ".$IstanbulTime."</p>"."<p>UTC: ".$UTCTime."</p>"."<p>IR/Tehran: ".$TehranTime."</p>";
        $replacement['product_link']['value'] = get_permalink($users_product['product_detail']['id']);
        $replacement['product_img']['value'] = get_the_post_thumbnail( ($users_product['product_detail']['id']), 'one_md_img' );
        
        $replacement['unsubscribe_link']['value'] = '#';
        
        $mail_template = $this->get_mail_template("do_lottery",$replacement);
        $mail_title = "Don't miss this Lottery";

        $dest = "parhamin2010@gmail.com";
        $subject = "Test Email";
        $body = $this->get_mail_template('do_lottery',$replacement);
        $headers = "Content-Type: text/html; charset=UTF-8\r\n";

        foreach($users_product['user_details'] as $user_email){
            $users[] = wp_mail($user_email, $subject, $body, $headers);
        }
        
        if (!empty($users)) {
            wp_send_json_success(array('error' => 'email sent!', 'data' => $users));
        } else {
            wp_send_json_success(array('error' => 'email failed!', 'data' => null));
        }
        exit;
    }

    public function get_mail_template($tmpl = '', $replacement = array()){
        if(!$tmpl) return '';
        $html =  file_get_contents(UBA_ONE_DOLLOR_DIR.'templates/emails/'.$tmpl.'.html');
        foreach($replacement as $key=>$replace){
            $html = str_replace('###'.$key.'###',$replace['value'],$html);
        }

        return $html;
    }

    
    
}
