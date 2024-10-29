<?php
/*
* Plugin Name: ACF - URL Field add-on
* Description: This plugin is an add-on for ACF. It allows you to choose URL between posts link and external link
* Author:      Johary Ranarimanana
* Plugin URI: http://netapsys.fr
* Version:     1.0
* Text Domain: acf-url
* Domain Path: /lang/
*/

//init
add_action( 'admin_init', 'acf_url_object_admin_init' );
function acf_url_object_admin_init() {
  wp_enqueue_script( 'livequery', plugin_dir_url( __FILE__ ) . 'js/jquery.livequery.js' );
  wp_enqueue_script( 'json2' );
  wp_enqueue_script( 'acf_url_field', plugin_dir_url( __FILE__ ) . 'js/acf_url.js' );
}  

add_action( 'init', 'acf_url_object_init' );
function acf_url_object_init() {
  include 'field.php';
}

//plugin register traduction
add_action( 'plugins_loaded', 'acf_url_object_plugins_loaded' );
function acf_url_object_plugins_loaded() {
  //localisation
  load_plugin_textdomain( 'acf-url', false, dirname( plugin_basename( __FILE__ ) ).'/languages/' );
}

?>