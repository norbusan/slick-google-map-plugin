<?php
/**
 * Plugin Name:       Slick Google Map
 * Plugin URI:        https://wordpress.org/plugins/slick-google-map/
 * Description:       Embed Google Maps (with your own API key) or OpenStreetMap/Leaflet maps via shortcode or Gutenberg block. Optional geo-mashup of posts/pages with latitude/longitude custom fields.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Author:            SGMP Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       slick-google-map
 * Domain Path:       /languages
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SGMP_VERSION', '1.0.0' );
define( 'SGMP_PLUGIN_FILE', __FILE__ );
define( 'SGMP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SGMP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

spl_autoload_register( static function ( string $class ): void {
	if ( ! str_starts_with( $class, 'SGMP\\' ) ) {
		return;
	}
	$relative = substr( $class, strlen( 'SGMP\\' ) );
	$path     = SGMP_PLUGIN_DIR . 'src/' . str_replace( '\\', '/', $relative ) . '.php';
	if ( is_file( $path ) ) {
		require_once $path;
	}
} );

add_action( 'plugins_loaded', static function (): void {
	( new SGMP\Plugin() )->boot();
} );
