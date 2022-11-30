<?php


if (!defined('ABSPATH')) {
    exit;
}


function fsww_update_251_add_column_action_performed_by() {
    global $wpdb;

    if((int)get_option('fsww_db_version', '250') < 251) {

        $table_name = $wpdb->prefix . 'fswcwallet_transaction';
        if ( $table_name === $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) && !$wpdb->get_var( "SHOW COLUMNS FROM `{$table_name}` LIKE 'action_performed_by';" ) ) {
            $wpdb->query("ALTER TABLE {$wpdb->prefix}fswcwallet_transaction ADD action_performed_by INT(11) NULL");
        }

        if(!add_option('fsww_db_version', '251')){
            update_option('fsww_db_version', '251');
        }

        //error_log("db updated 251");
    }
}

function fsww_update_252_add_column_transaction_description() {
    global $wpdb;

    if((int)get_option('fsww_db_version', '251') < 252) {

        $table_name = $wpdb->prefix . 'fswcwallet_transaction';
        if ( $table_name === $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) && !$wpdb->get_var( "SHOW COLUMNS FROM `{$table_name}` LIKE 'transaction_description';" ) ) {
            $wpdb->query("ALTER TABLE {$wpdb->prefix}fswcwallet_transaction ADD transaction_description TEXT NULL");
        }

        if(!add_option('fsww_db_version', '252')){
            update_option('fsww_db_version', '252');
        }

        //error_log("db updated 252");
    }
}


fsww_update_251_add_column_action_performed_by();
fsww_update_252_add_column_transaction_description();
