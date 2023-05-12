<?php
namespace UBAGOD_ONE_DOLLOR;

if (!defined('ABSPATH')) exit;
/*
  Plugin Name: UBAGOD One dollor
  Description: Custom codes for running the whole website
  Version: 1.0.0
  Author: UBAGOD
  Author URI: http://www.ubagod.com/
  Text Domain: ubagod-one-dollor
 */

 define("UBA_ONE_DOLLOR_RUN",true);
 define("UBA_ONE_DOLLOR_DIR",plugin_dir_url(__DIR__.'/ubagod-once-dollor'));
 define("UBA_ONE_DOLLOR_ASSET_DIR",plugin_dir_url(__DIR__.'/ubagod-once-dollor').'assets/');



 add_action('plugins_loaded',function(){
   require_once("includes/initializer.php");
 });
?>

