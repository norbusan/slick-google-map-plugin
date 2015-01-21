<?php
/*
Plugin Name: Simple Google Map Plugin
Plugin URI: https://github.com/norbusan/simple-google-map-plugin
Description: A simple and intuitive, yet elegant and fully documented Google map plugin that installs as a widget and a short code. The plugin is packed with useful features. Widget and shortcode enabled. Offers extensive configuration options for markers, over 250 custom marker icons, marker Geo mashup, controls, size, KML files, location by latitude/longitude, location by address, info window, directions, traffic/bike lanes and more. 
Version: 0.0.1
Author: Norbert Preining
Author URI: http://www.preining.info/
License: GPLv2

Copyright (C) 2011-09/2014  Alexander Zagniotov
Copyright (C) 2015 Norbert Preining

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

if ( !function_exists( 'add_action' ) ) {
	echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
	exit;
}

if ( is_admin() ) {
	$sgmp_transient = get_transient( 'sgmp_update_routine' );
	if ( $sgmp_transient === FALSE ) {
		set_transient( 'sgmp_update_routine', 'execute only once a week', 60*60*24*7 );
		//info: options to hide notices
		$sgmp_defaults = array(
			'admin_notice' => 'show',
			'plugin_notice' => 'show',
			'metabox_notice' => 'show'
		);
		add_option('sgmp_options', $sgmp_defaults );

		//info: copy map icons to wp-content/uploads
		require_once(ABSPATH . 'wp-admin' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'file.php');
		WP_Filesystem();
		$cmpg_upload_dir = wp_upload_dir();
		define ("CMPG_PLUGIN_ICONS_DIR", $cmpg_upload_dir['basedir'] . DIRECTORY_SEPARATOR . "leaflet-maps-marker-icons");
		define ("CMPG_PLUGIN_DIR", plugin_dir_path(__FILE__));
		$target = CMPG_PLUGIN_ICONS_DIR;
		
		if ( !file_exists(CMPG_PLUGIN_ICONS_DIR . DIRECTORY_SEPARATOR . '1-default.png') )
		{
			wp_mkdir_p( $target );
			$source = CMPG_PLUGIN_DIR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'markers' . DIRECTORY_SEPARATOR . 'zip';
			copy_dir($source, $target, $skip_list = array() );
			$zipfile = CMPG_PLUGIN_ICONS_DIR . DIRECTORY_SEPARATOR . 'sgmp-markers.zip';
			unzip_file( $zipfile, $target );
			//info: fallback for hosts where copying zipfile to LEAFLET_PLUGIN_ICON_DIR doesnt work
			if ( !file_exists(CMPG_PLUGIN_ICONS_DIR . DIRECTORY_SEPARATOR . '1-default.png') ) {
				if (class_exists('ZipArchive')) {
					$zip = new ZipArchive;
					$res = $zip->open( CMPG_PLUGIN_DIR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'markers' . DIRECTORY_SEPARATOR . 'zip' . DIRECTORY_SEPARATOR . 'sgmp-markers.zip');
					if ($res === TRUE) {
						$zip->extractTo(CMPG_PLUGIN_ICONS_DIR);
						$zip->close();
					}
				}
			}
		}

	}
}

if ( !function_exists('sgmp_define_constants') ):
	function sgmp_define_constants() {
		define('SGMP_PLUGIN_BOOTSTRAP', __FILE__ );
		define('SGMP_PLUGIN_DIR', dirname(SGMP_PLUGIN_BOOTSTRAP));
		define('SGMP_PLUGIN_URI', plugin_dir_url(SGMP_PLUGIN_BOOTSTRAP));

		$json_constants_string = file_get_contents(SGMP_PLUGIN_DIR."/data/plugin.constants.json");
		$json_constants = json_decode($json_constants_string, true);
		$json_constants = $json_constants[0];

		if (is_array($json_constants)) {
			foreach ($json_constants as $constant_key => $constant_value) {
				$constant_value = str_replace("SGMP_PLUGIN_DIR", SGMP_PLUGIN_DIR, $constant_value);
				$constant_value = str_replace("SGMP_PLUGIN_URI", SGMP_PLUGIN_URI, $constant_value);
				define($constant_key, $constant_value);
			}
		}
	}
endif;

if ( !function_exists('sgmp_require_dependancies') ):
	function sgmp_require_dependancies() {
		require_once (SGMP_PLUGIN_DIR . '/functions.php');
		require_once (SGMP_PLUGIN_DIR . '/widget.php');
		require_once (SGMP_PLUGIN_DIR . '/shortcode.php');
		require_once (SGMP_PLUGIN_DIR . '/metabox.php');
		require_once (SGMP_PLUGIN_DIR . '/admin-menu.php');
        require_once (SGMP_PLUGIN_DIR . '/admin-bar-menu.php');
		require_once (SGMP_PLUGIN_DIR . '/head.php');
	}
endif;

if ( !function_exists('sgmp_register_hooks') ):
	function sgmp_register_hooks() {
		register_activation_hook( SGMP_PLUGIN_BOOTSTRAP, 'sgmp_on_activate_hook');
	}
endif;

if ( !function_exists('sgmp_add_actions') ):
	function sgmp_add_actions() {
		//http://scribu.net/wordpress/optimal-script-loading.html
		add_action('init', 'sgmp_google_map_register_scripts');
		add_action('init', 'sgmp_load_plugin_textdomain');
		add_action('admin_notices', 'sgmp_show_message');
		add_action('admin_init', 'sgmp_google_map_admin_add_style');
		add_action('admin_init', 'sgmp_google_map_admin_add_script');
		add_action('admin_footer', 'sgmp_google_map_init_global_admin_html_object');
		add_action('admin_menu', 'sgmp_google_map_plugin_menu');

        if ( is_admin() ) {
            $setting_plugin_menu_bar_menu = get_option(SGMP_DB_SETTINGS_PLUGIN_ADMIN_BAR_MENU);
            if (!isset($setting_plugin_menu_bar_menu) || (isset($setting_plugin_menu_bar_menu) && $setting_plugin_menu_bar_menu != "false")) {
                add_action('admin_bar_menu', 'sgmp_admin_bar_menu', 99999);
            }
        }

		add_action('widgets_init', create_function('', 'return register_widget("SimpleGoogleMap_Widget");'));
		add_action('wp_head', 'sgmp_google_map_deregister_scripts', 200);
		add_action('wp_head', 'sgmp_generate_global_options');

        if ( is_admin() ) {
			global $wp_version;
            $setting_tiny_mce_button = get_option(SGMP_DB_SETTINGS_TINYMCE_BUTTON);
            if (!isset($setting_tiny_mce_button) || (isset($setting_tiny_mce_button) && $setting_tiny_mce_button != "false")) {
                if (sgmp_should_load_admin_scripts()) {
                    if (version_compare($wp_version,"3.9","<")){
						add_action('init', 'sgmp_register_mce');
					}
                    add_action('wp_ajax_sgmp_mce_ajax_action', 'sgmp_mce_ajax_action_callback');
                }
            }
        }

        add_action('wp_ajax_nopriv_sgmp_ajax_cache_map_action', 'sgmp_ajax_cache_map_action_callback');
        add_action('wp_ajax_sgmp_ajax_cache_map_action', 'sgmp_ajax_cache_map_action_callback');
        add_action('wp_ajax_sgmp_insert_shortcode_to_post_action', 'sgmp_insert_shortcode_to_post_action_callback');

        add_action('save_post', 'sgmp_save_post_hook' );
        add_action('save_page', 'sgmp_save_page_hook' );

        add_action('publish_post', 'sgmp_publish_post_hook' );
        add_action('publish_page', 'sgmp_publish_page_hook' );

        add_action('deleted_post', 'sgmp_deleted_post_hook' );
        add_action('deleted_page', 'sgmp_deleted_page_hook' );

        add_action('publish_to_draft', 'sgmp_publish_to_draft_hook' );
	}
endif;

if ( !function_exists('sgmp_add_shortcode_support') ):
	function sgmp_add_shortcode_support() {
		add_shortcode('google-map-v3', 'sgmp_shortcode_googlemap_handler');
	}
endif;

if ( !function_exists('sgmp_add_filters') ):
	function sgmp_add_filters() {
		add_filter( 'widget_text', 'do_shortcode');
        	add_filter( 'plugin_action_links', 'sgmp_plugin_action_links', 10, 2 );
	}
endif;

global $sgmp_global_map_language;
$sgmp_global_map_language = "en";

/* BOOTSTRAPPING STARTS */
sgmp_define_constants();
sgmp_require_dependancies();
sgmp_add_actions();
sgmp_register_hooks();
sgmp_add_shortcode_support();
sgmp_add_filters();
/* BOOTSTRAPPING ENDS */

?>
