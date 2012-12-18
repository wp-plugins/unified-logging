<?php
/*
Plugin Name: UnifiedLogging
Plugin URI: http://blog.unifiedlogging.com/getting-started-with-unified-logging-wordpress-plugin/
Description: The Unified Logging plugin for wordpress adds hooks to send error information to Unified Logging.  This plugin also logs these errors to the standard error_log.  To sign up go to <a href="https://portal.unifiedlogging.com/signup/">Unified Logging</a>
Author: Unified Logging
License: Ms-PL
Version: 1.0
Author URI: http://www.unifiedlogging.com
*/

/* Copyright 2012 Unified Logging
*
*	This file is part of Unified Logging
*
*   Unified Logging is a service which collects data from your internet 
*	connected application.  This plugin enables information to be sent 
*	to Unified Logging using your credentials retrieve from the profile
*	page on Unified Logging.  Your data is sent over ssl and the secret
*	key is used to create a hash to make sure the data is not tampered
*	with.
*
*  	 This program is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License, or
*    (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with this program; if not, write to the Free Software
*    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*
*/ 
////////////////////////SETUP//////////////////////////////////////////////////
define('UL_VERSION', '1.0.0' );
register_activation_hook( __FILE__, array( 'unifiedlogging', 'unifiedlogging_activate' ) );
register_deactivation_hook( __FILE__, array( 'unifiedlogging', 'unifiedlogging_deactivate' ) );
register_uninstall_hook( __FILE__, array( 'unifiedlogging', 'unifiedlogging_uninstall' ) );
add_action( 'admin_menu', array( 'unifiedlogging', 'unifiedlogging_setup' ) );
add_filter('plugin_action_links', array( 'unifiedlogging', 'unifiedlogging_action_links'), 10, 2);
add_action( 'init', array( 'unifiedlogging', 'unifiedlogging_include' ) );

class unifiedlogging
{
	function unifiedlogging_activate() 
	{
		$options = get_option('plugin_unifieidlogging_settings');
		
		if ( empty( $options ) )
		{
			if (defined('E_DEPRECATED'))
			{
				// set defaults
				$new_options = array(
					'UL_SUBMISSIONURL' => '',
					'UL_ACCESSKEY' => '',
					'UL_SECRETKEY' => '',
					'UL_ACTIVE' => true,
					'UL_LEVEL' => E_ALL & ~E_NOTICE & ~E_DEPRECATED,
					'UL_E_ERROR' => false,
					'UL_E_WARNING' => false,
					'UL_E_PARSE' => false,
					'UL_E_NOTICE' => true,
					'UL_E_CORE_ERROR' => false,
					'UL_E_CORE_WARNING' => false,
					'UL_E_COMPILE_ERROR' => false,
					'UL_E_COMPILE_WARNING' => false,
					'UL_E_USER_ERROR' => false,
					'UL_E_USER_WARNING' => false,
					'UL_E_USER_NOTICE' => false,
					'UL_E_STRICT' => false,
					'UL_E_RECOVERABLE_ERROR' => false,
					'UL_E_DEPRECATED' => true,
					'UL_E_USER_DEPRECATED' => false);
			
				add_option( 'plugin_unifieidlogging_settings', $new_options );
			}
			else
			{
				// set defaults
				$new_options = array(
					'UL_SUBMISSIONURL' => '',
					'UL_ACCESSKEY' => '',
					'UL_SECRETKEY' => '',
					'UL_ACTIVE' => true,
					'UL_LEVEL' => E_ALL & ~E_NOTICE & ~E_STRICT,
					'UL_E_ERROR' => false,
					'UL_E_WARNING' => false,
					'UL_E_PARSE' => false,
					'UL_E_NOTICE' => true,
					'UL_E_CORE_ERROR' => false,
					'UL_E_CORE_WARNING' => false,
					'UL_E_COMPILE_ERROR' => false,
					'UL_E_COMPILE_WARNING' => false,
					'UL_E_USER_ERROR' => false,
					'UL_E_USER_WARNING' => false,
					'UL_E_USER_NOTICE' => false,
					'UL_E_STRICT' => true,
					'UL_E_RECOVERABLE_ERROR' => false,
					'UL_E_DEPRECATED' => false,
					'UL_E_USER_DEPRECATED' => false);
			
				add_option( 'plugin_unifieidlogging_settings', $new_options );

			}
		}
		else
		{
			$options['UL_ACTIVE'] = true;
			update_option( 'plugin_unifieidlogging_settings', $options);
		}
		
		unifiedlogging::unifiedlogging_check_update();
	}
	
	static function unifiedlogging_check_update()
	{
		$current_version = get_option( 'unifiedlogging_version' );
		if ( empty( $current_version ) == false 
		    && $current_version != $unifiedlogging_version )
		{
			try
			{
				// Execute your upgrade logic here
				unifiedlogging::ul_debug( 'ran update' );
			}
			catch ( Exception $e ) {
				unifiedlogging::ul_debug( $e->getMessage() );
			}
		}
		
		update_option( 'unifiedlogging_version', UL_VERSION );
	}
	
	function unifiedlogging_deactivate() {
		try{
			$options = get_option('plugin_unifieidlogging_settings');
			$options['UL_ACTIVE'] = false;
			update_option( 'plugin_unifieidlogging_settings', $options);
			
			remove_action( 'init', array( 'unifiedlogging', 'unifiedlogging_include' ) );
		}
		catch ( Exception $e ) {
			unifiedlogging::ul_debug( $e->getMessage() );
		}

	}
	
	function unifiedlogging_uninstall() 
	{
		delete_option( 'plugin_unifieidlogging_settings' );
		delete_option( 'unifiedlogging_version' );
	}
	
	function unifiedlogging_setup() {
		if( function_exists('add_options_page') ) 
		{
			add_options_page(__('Unified Logging'),__('Unified Logging'),'manage_options','unifiedlogging',array( 'unifiedlogging', 'unifiedlogging_ui' ));
		}
	}
	
	
	function unifiedlogging_action_links($links, $file) 
	{
		try{
			static $this_plugin;
		
			if (!$this_plugin) {
				$this_plugin = plugin_basename(__FILE__);
			}
		
			if ($file == $this_plugin) {
				// The "page" query string value must be equal to the slug
				// of the Settings admin page we defined earlier, which in
				// this case equals "myplugin-settings".
				$settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/options-general.php?page=unifiedlogging">Settings</a>';
				array_unshift($links, $settings_link);
			}
		}
		catch ( Exception $e ) {
			unifiedlogging::ul_debug( $e->getMessage() );
		}
	
		return $links;
	}
	
	function unifiedlogging_include() 
	{
		$options = get_option('plugin_unifieidlogging_settings');
		
		if ( isset( $options['UL_ACTIVE'] )
			 && $options['UL_ACTIVE'] == true
			 && isset( $options['UL_SUBMISSIONURL'] ) 
			 && isset( $options['UL_ACCESSKEY'] ) 
			 && isset( $options['UL_SECRETKEY'] ) 
			 && isset( $options['UL_LEVEL'] ) ) 
		{
			unifiedlogging::ul_debug( 'registering ul error handler: ' . plugin_dir_path(__FILE__) . 'ul-error-handler.php' );
			include_once( plugin_dir_path(__FILE__) . 'ul-error-handler.php' );
		}
	}
	
	function unifiedlogging_ui() {
		include_once( 'unifiedlogging-ui.php' );
	}
	
	function get_submission_url() {
		$options = get_option('plugin_unifieidlogging_settings');
		return $options['UL_SUBMISSIONURL'];
	}
	
	function get_access_key() {
		$options = get_option('plugin_unifieidlogging_settings');
		return $options['UL_ACCESSKEY'];
	}
	
	function get_secret_key() {
		$options = get_option('plugin_unifieidlogging_settings');
		return $options['UL_SECRETKEY'];
	}
	
	function get_level() {
		$options = get_option('plugin_unifieidlogging_settings');
		return $options['UL_LEVEL'];
	}
	
	function get_updated_level( $new_options )
	{
		$base_level = E_ALL; 
			
		if ($new_options['UL_E_ERROR'] == true && defined('E_ERROR')){
			$base_level = $base_level & ~E_ERROR;
		}
		if ($new_options['UL_E_WARNING'] == true && defined('E_WARNING')){
			$base_level = $base_level & ~E_WARNING;
		}
		if ($new_options['UL_E_PARSE'] == true && defined('E_PARSE')){
			$base_level = $base_level & ~E_PARSE;
		}
		if ($new_options['UL_E_NOTICE'] == true && defined('E_NOTICE')){
			$base_level = $base_level & ~E_NOTICE;
		}
		if ($new_options['UL_E_CORE_ERROR'] == true && defined('E_CORE_ERROR')){
			$base_level = $base_level & ~E_CORE_ERROR;
		}
		if ($new_options['UL_E_CORE_WARNING'] == true && defined('E_CORE_WARNING')){
			$base_level = $base_level & ~E_CORE_WARNING;
		}
		if ($new_options['UL_E_COMPILE_ERROR'] == true && defined('E_COMPILE_ERROR')){
			$base_level = $base_level & ~E_COMPILE_ERROR;
		}
		if ($new_options['UL_E_COMPILE_WARNING'] == true && defined('E_COMPILE_WARNING')){
			$base_level = $base_level & ~E_COMPILE_WARNING;
		}
		if ($new_options['UL_E_USER_ERROR'] == true && defined('E_USER_ERROR')){
			$base_level = $base_level & ~E_USER_ERROR;
		}
		if ($new_options['UL_E_USER_WARNING'] == true && defined('E_USER_WARNING')){
			$base_level = $base_level & ~E_USER_WARNING;
		}
		if ($new_options['UL_E_USER_NOTICE'] == true && defined('E_USER_NOTICE')){
			$base_level = $base_level & ~E_USER_NOTICE;
		}
		if ($new_options['UL_E_STRICT'] == true && defined('E_STRICT')){
			$base_level = $base_level & ~E_STRICT;
		}
		if ($new_options['UL_E_RECOVERABLE_ERROR'] == true && defined('E_RECOVERABLE_ERROR')){
			$base_level = $base_level & ~E_RECOVERABLE_ERROR;
		}
		if ($new_options['UL_E_DEPRECATED'] == true && defined('E_DEPRECATED')){
			$base_level = $base_level & ~E_DEPRECATED;
		}
		if ($new_options['UL_E_USER_DEPRECATED'] == true && defined('E_USER_DEPRECATED')){
			$base_level = $base_level & ~E_USER_DEPRECATED;
		}
		
		return $base_level;
	}
	
	function get_error() {
		$options = get_option('plugin_unifieidlogging_settings');
		return $options['UL_E_ERROR'];
	}
	
	function get_warning() {
		$options = get_option('plugin_unifieidlogging_settings');
		return $options['UL_E_WARNING'];
	}
	
	function get_parse() {
		$options = get_option('plugin_unifieidlogging_settings');
		return $options['UL_E_PARSE'];
	}
	
	function get_notice() {
		$options = get_option('plugin_unifieidlogging_settings');
		return $options['UL_E_NOTICE'];
	}
	
	function get_core_error() {
		$options = get_option('plugin_unifieidlogging_settings');
		return $options['UL_E_CORE_ERROR'];
	}
	
	function get_core_warning() {
		$options = get_option('plugin_unifieidlogging_settings');
		return $options['UL_E_CORE_WARNING'];
	}
	
	function get_compile_error() {
		$options = get_option('plugin_unifieidlogging_settings');
		return $options['UL_E_COMPILE_ERROR'];
	}
	
	function get_compile_warning() {
		$options = get_option('plugin_unifieidlogging_settings');
		return $options['UL_E_COMPILE_WARNING'];
	}
	
	function get_user_error() {
		$options = get_option('plugin_unifieidlogging_settings');
		return $options['UL_E_USER_ERROR'];
	}
	
	function get_user_warning() {
		$options = get_option('plugin_unifieidlogging_settings');
		return $options['UL_E_USER_WARNING'];
	}
	
	function get_user_notice() {
		$options = get_option('plugin_unifieidlogging_settings');
		return $options['UL_E_USER_NOTICE'];
	}
	
	function get_strict() {
		$options = get_option('plugin_unifieidlogging_settings');
		return $options['UL_E_STRICT'];
	}
	
	function get_recoverable_error() {
		$options = get_option('plugin_unifieidlogging_settings');
		return $options['UL_E_RECOVERABLE_ERROR'];
	}
	
	function get_deprecated() {
		$options = get_option('plugin_unifieidlogging_settings');
		return $options['UL_E_DEPRECATED'];
	}
	
	function get_user_deprecated() {
		$options = get_option('plugin_unifieidlogging_settings');
		return $options['UL_E_USER_DEPRECATED'];
	}
	/////////////////////////////
	
	static function ul_debug( $log_message )
	{
		if ( WP_DEBUG === true)
		{
			if ( is_array( $log_message ) || is_object( $log_message ) )
			{
				error_log(print_r( $log_message, true ) );
			}
			else
			{
				error_log( $log_message );
			}
		}
	}
	
}
?>
