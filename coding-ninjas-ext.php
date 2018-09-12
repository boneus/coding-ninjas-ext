<?php 
/*
Plugin Name: Coding Ninjas Tasks Extension
Description: Extension plugin for freelancers tasks. Uses post_title for name and thumbnail for avatar.
Author: BoneS
Author URI: 
Plugin URI: 
Version: 1.0
Text Domain: cne
*/

add_action( 'plugins_loaded', 'cne_plugin_init' );
function cne_plugin_init() {

	if ( class_exists( '\codingninjas\App' ) ) {

		require_once "app/App.php";
		\codingninjasext\App::run(__FILE__);
	}
	else {

		add_action( 'admin_init', 'cne_plugin_deactivate' );
		add_action( 'admin_notices', 'cne_missing_basic_plugin_notice' );
	}
}

function cne_plugin_deactivate() {

	deactivate_plugins( plugin_basename( __FILE__ ) );
}

function cne_missing_basic_plugin_notice() {

	echo '<div class="error"><p>Activation failed: Coding Ninjas basic plugin is not activated.</p></div>';
}