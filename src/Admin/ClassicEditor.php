<?php
declare(strict_types=1);

namespace SGMP\Admin;

use SGMP\Options;

/**
 * Adds an "Insert Slick Map" media button above the Classic Editor.
 *
 * Opens a Thickbox dialog with a small form and inserts a [slick_map] shortcode.
 */
final class ClassicEditor {

	private const HANDLE = 'sgmp-classic-editor';

	public function register(): void {
		add_action( 'media_buttons', $this->render_button( ... ), 11 );
		add_action( 'admin_enqueue_scripts', $this->enqueue( ... ) );
		add_action( 'admin_footer', $this->render_dialog( ... ) );
	}

	public function enqueue( string $hook ): void {
		if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}
		wp_enqueue_style( 'thickbox' );
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_script(
			self::HANDLE,
			SGMP_PLUGIN_URL . 'assets/js/classic-editor.js',
			[ 'jquery', 'thickbox' ],
			SGMP_VERSION,
			true
		);
		$opts = Options::get();
		wp_localize_script(
			self::HANDLE,
			'SGMP_CE',
			[
				'defaults' => [
					'lat'    => $opts['default_lat'],
					'lng'    => $opts['default_lng'],
					'zoom'   => (int) $opts['default_zoom'],
					'height' => $opts['default_height'],
				],
			]
		);
	}

	public function render_button(): void {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen !== null && ! in_array( $screen->base, [ 'post' ], true ) ) {
			return;
		}
		printf(
			'<a href="#TB_inline?width=500&inlineId=sgmp-ce-dialog" class="button thickbox" title="%1$s"><span class="dashicons dashicons-location-alt" style="vertical-align:text-top;"></span> %2$s</a>',
			esc_attr__( 'Insert a Slick Map shortcode', 'slick-google-map' ),
			esc_html__( 'Slick Map', 'slick-google-map' )
		);
	}

	public function render_dialog(): void {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen === null || $screen->base !== 'post' ) {
			return;
		}
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}
		?>
		<div id="sgmp-ce-dialog" style="display:none;">
			<div style="padding:1em;">
				<h2 style="margin-top:0;"><?php echo esc_html__( 'Insert Slick Map', 'slick-google-map' ); ?></h2>
				<table class="form-table"><tbody>
					<tr><th><label for="sgmp-ce-provider"><?php echo esc_html__( 'Provider', 'slick-google-map' ); ?></label></th>
						<td><select id="sgmp-ce-provider">
							<option value=""><?php echo esc_html__( 'Default (from settings)', 'slick-google-map' ); ?></option>
							<option value="leaflet">Leaflet / OSM</option>
							<option value="google">Google Maps</option>
						</select></td></tr>
					<tr><th><label for="sgmp-ce-lat"><?php echo esc_html__( 'Latitude', 'slick-google-map' ); ?></label></th>
						<td><input type="text" id="sgmp-ce-lat" class="regular-text" /></td></tr>
					<tr><th><label for="sgmp-ce-lng"><?php echo esc_html__( 'Longitude', 'slick-google-map' ); ?></label></th>
						<td><input type="text" id="sgmp-ce-lng" class="regular-text" /></td></tr>
					<tr><th><label for="sgmp-ce-zoom"><?php echo esc_html__( 'Zoom (0-22)', 'slick-google-map' ); ?></label></th>
						<td><input type="number" id="sgmp-ce-zoom" min="0" max="22" /></td></tr>
					<tr><th><label for="sgmp-ce-height"><?php echo esc_html__( 'Height', 'slick-google-map' ); ?></label></th>
						<td><input type="text" id="sgmp-ce-height" class="regular-text" /></td></tr>
					<tr><th><label for="sgmp-ce-mashup"><?php echo esc_html__( 'Mashup post types', 'slick-google-map' ); ?></label></th>
						<td><input type="text" id="sgmp-ce-mashup" class="regular-text" placeholder="post,page" /></td></tr>
					<tr><th><label for="sgmp-ce-kml"><?php echo esc_html__( 'KML URL', 'slick-google-map' ); ?></label></th>
						<td><input type="url" id="sgmp-ce-kml" class="regular-text" /></td></tr>
					<tr><th><label for="sgmp-ce-gpx"><?php echo esc_html__( 'GPX URL', 'slick-google-map' ); ?></label></th>
						<td><input type="url" id="sgmp-ce-gpx" class="regular-text" /></td></tr>
				</tbody></table>

				<h3><?php echo esc_html__( 'Markers', 'slick-google-map' ); ?></h3>
				<div id="sgmp-ce-markers"></div>
				<p>
					<button type="button" class="button" id="sgmp-ce-add-marker"><?php echo esc_html__( '+ Add marker', 'slick-google-map' ); ?></button>
				</p>

				<p>
					<button type="button" class="button button-primary" id="sgmp-ce-insert">
						<?php echo esc_html__( 'Insert shortcode', 'slick-google-map' ); ?>
					</button>
				</p>

				<script type="text/template" id="sgmp-ce-marker-template">
					<div class="sgmp-ce-marker" style="border:1px solid #ddd;padding:0.5em;margin-bottom:0.5em;border-radius:4px;">
						<p><label><?php echo esc_html__( 'Address', 'slick-google-map' ); ?>
							<input type="text" class="sgmp-ce-m-address regular-text" /></label></p>
						<p style="display:flex;gap:0.5em;">
							<label style="flex:1;"><?php echo esc_html__( 'Lat', 'slick-google-map' ); ?>
								<input type="text" class="sgmp-ce-m-lat regular-text" /></label>
							<label style="flex:1;"><?php echo esc_html__( 'Lng', 'slick-google-map' ); ?>
								<input type="text" class="sgmp-ce-m-lng regular-text" /></label>
						</p>
						<p><label><?php echo esc_html__( 'Title', 'slick-google-map' ); ?>
							<input type="text" class="sgmp-ce-m-title regular-text" /></label></p>
						<p><label><?php echo esc_html__( 'Icon (URL or built-in name)', 'slick-google-map' ); ?>
							<input type="text" class="sgmp-ce-m-icon regular-text" placeholder="restaurant, lodging, … or https://…" /></label></p>
						<p><button type="button" class="button-link sgmp-ce-m-remove"><?php echo esc_html__( 'Remove marker', 'slick-google-map' ); ?></button></p>
					</div>
				</script>
			</div>
		</div>
		<?php
	}
}
