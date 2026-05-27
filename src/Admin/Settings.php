<?php
declare(strict_types=1);

namespace SGMP\Admin;

use SGMP\Options;

final class Settings {

	private const PAGE_SLUG  = 'slick-google-map';
	private const GROUP_SLUG = 'sgmp_options_group';

	public function register(): void {
		add_action( 'admin_menu', $this->add_page( ... ) );
		add_action( 'admin_init', $this->register_settings( ... ) );
		add_action( 'admin_notices', $this->maybe_show_missing_key_notice( ... ) );
	}

	public function add_page(): void {
		add_options_page(
			__( 'Slick Google Map', 'slick-google-map' ),
			__( 'Slick Google Map', 'slick-google-map' ),
			'manage_options',
			self::PAGE_SLUG,
			$this->render_page( ... )
		);
	}

	public function register_settings(): void {
		register_setting(
			self::GROUP_SLUG,
			Options::OPTION_KEY,
			[
				'type'              => 'array',
				'sanitize_callback' => [ Options::class, 'sanitize' ],
				'default'           => Options::defaults(),
			]
		);

		add_settings_section( 'sgmp_section_provider', __( 'Map provider', 'slick-google-map' ), '__return_false', self::PAGE_SLUG );
		add_settings_section( 'sgmp_section_defaults', __( 'Defaults', 'slick-google-map' ), '__return_false', self::PAGE_SLUG );
		add_settings_section( 'sgmp_section_mashup',   __( 'Geo-mashup', 'slick-google-map' ),  '__return_false', self::PAGE_SLUG );

		$this->field( 'provider',       __( 'Default provider', 'slick-google-map' ),       'sgmp_section_provider', 'render_provider' );
		$this->field( 'google_api_key', __( 'Google Maps API key', 'slick-google-map' ),    'sgmp_section_provider', 'render_text', [ 'autocomplete' => 'off' ] );
		$this->field( 'google_map_id',  __( 'Google Map ID (recommended)', 'slick-google-map' ),'sgmp_section_provider', 'render_text' );

		$this->field( 'default_lat',    __( 'Default latitude', 'slick-google-map' ),       'sgmp_section_defaults', 'render_text' );
		$this->field( 'default_lng',    __( 'Default longitude', 'slick-google-map' ),      'sgmp_section_defaults', 'render_text' );
		$this->field( 'default_zoom',   __( 'Default zoom (0-22)', 'slick-google-map' ),    'sgmp_section_defaults', 'render_number', [ 'min' => 0, 'max' => 22 ] );
		$this->field( 'default_height', __( 'Default height (e.g. 400px)', 'slick-google-map' ), 'sgmp_section_defaults', 'render_text' );

		$this->field( 'lat_meta_key',   __( 'Latitude meta key', 'slick-google-map' ),      'sgmp_section_mashup', 'render_text' );
		$this->field( 'lng_meta_key',   __( 'Longitude meta key', 'slick-google-map' ),     'sgmp_section_mashup', 'render_text' );
		$this->field( 'mashup_ttl',     __( 'Cache TTL (seconds)', 'slick-google-map' ),    'sgmp_section_mashup', 'render_number', [ 'min' => 0, 'max' => 86400 ] );
	}

	private function field( string $key, string $label, string $section, string $renderer, array $extra = [] ): void {
		add_settings_field(
			"sgmp_field_{$key}",
			$label,
			[ $this, $renderer ],
			self::PAGE_SLUG,
			$section,
			array_merge( [ 'label_for' => "sgmp_field_{$key}", 'key' => $key ], $extra )
		);
	}

	public function render_text( array $args ): void {
		$opts  = Options::get();
		$value = (string) ( $opts[ $args['key'] ] ?? '' );
		printf(
			'<input type="text" id="%1$s" name="%2$s[%3$s]" value="%4$s" class="regular-text" autocomplete="%5$s" />',
			esc_attr( $args['label_for'] ),
			esc_attr( Options::OPTION_KEY ),
			esc_attr( $args['key'] ),
			esc_attr( $value ),
			esc_attr( $args['autocomplete'] ?? 'on' )
		);
	}

	public function render_number( array $args ): void {
		$opts  = Options::get();
		$value = (string) ( $opts[ $args['key'] ] ?? '' );
		printf(
			'<input type="number" id="%1$s" name="%2$s[%3$s]" value="%4$s" min="%5$s" max="%6$s" />',
			esc_attr( $args['label_for'] ),
			esc_attr( Options::OPTION_KEY ),
			esc_attr( $args['key'] ),
			esc_attr( $value ),
			esc_attr( (string) ( $args['min'] ?? 0 ) ),
			esc_attr( (string) ( $args['max'] ?? 100 ) )
		);
	}

	public function render_provider( array $args ): void {
		$opts    = Options::get();
		$current = (string) $opts['provider'];
		$choices = [
			Options::PROVIDER_LEAFLET => __( 'Leaflet / OpenStreetMap (no key required)', 'slick-google-map' ),
			Options::PROVIDER_GOOGLE  => __( 'Google Maps (API key required)', 'slick-google-map' ),
		];
		echo '<select id="' . esc_attr( $args['label_for'] ) . '" name="' . esc_attr( Options::OPTION_KEY ) . '[provider]">';
		foreach ( $choices as $value => $label ) {
			printf(
				'<option value="%s"%s>%s</option>',
				esc_attr( $value ),
				selected( $current, $value, false ),
				esc_html( $label )
			);
		}
		echo '</select>';
	}

	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Slick Google Map', 'slick-google-map' ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( self::GROUP_SLUG );
				do_settings_sections( self::PAGE_SLUG );
				submit_button();
				?>
			</form>
			<h2><?php echo esc_html__( 'Usage', 'slick-google-map' ); ?></h2>
			<p><?php echo esc_html__( 'Shortcode:', 'slick-google-map' ); ?>
				<code>[slick_map lat="48.2082" lng="16.3738" zoom="13" height="400px"]</code></p>
			<p><?php echo esc_html__( 'Geo-mashup:', 'slick-google-map' ); ?>
				<code>[slick_map mashup="post,page"]</code></p>
		</div>
		<?php
	}

	public function maybe_show_missing_key_notice(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$opts = Options::get();
		if ( $opts['provider'] !== Options::PROVIDER_GOOGLE ) {
			return;
		}

		$settings_url = admin_url( 'options-general.php?page=' . self::PAGE_SLUG );

		if ( $opts['google_api_key'] === '' ) {
			printf(
				'<div class="notice notice-warning"><p>%s</p></div>',
				wp_kses(
					sprintf(
						/* translators: %s: settings page URL */
						__( 'Slick Google Map: Google provider is selected but no API key is set. <a href="%s">Add a key</a>.', 'slick-google-map' ),
						esc_url( $settings_url )
					),
					[ 'a' => [ 'href' => [] ] ]
				)
			);
			return;
		}

		if ( $opts['google_map_id'] === '' ) {
			printf(
				'<div class="notice notice-info is-dismissible"><p>%s</p></div>',
				wp_kses(
					sprintf(
						/* translators: %s: settings page URL */
						__( 'Slick Google Map: no Google Map ID is set. Maps will still work, but pages will log a deprecation warning for <code>google.maps.Marker</code>. Create a Map ID in the Google Cloud Console (Maps Platform → Map Management) and paste it into <a href="%s">the settings</a> to use the modern Advanced Marker.', 'slick-google-map' ),
						esc_url( $settings_url )
					),
					[ 'a' => [ 'href' => [] ], 'code' => [] ]
				)
			);
		}
	}
}
