<?php
/*
Plugin Name: Customer Info
Description: Display customer information 
Version: 1.0
Author: wedangsusu
Author URI: http://wedangsusu.com
Plugin URI: http://wordpress.com
License: Frontier
License URI: http://google.com

*/
if(!defined('LANDING_PATH')) define( 'LANDING_PATH', plugin_dir_path(__FILE__) );
if(!defined('LANDING_DIR')) define( 'LANDING_DIR', plugin_dir_url(__FILE__) );

require_once(LANDING_PATH . 'customer-info.php');

$customer = new customerInfo();
$customer->init();

?>