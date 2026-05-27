<?php
declare(strict_types=1);

namespace SGMP\Frontend;

/**
 * Compatibility shim for the historical `[google-map-v3 ...]` shortcode shipped
 * by very old versions of this plugin.
 *
 * Only the attributes that still map to a real feature in the modern renderer
 * are honoured. Everything else (Panoramio, marker clustering, the various
 * *control booleans, language hints, etc.) is silently dropped so old posts
 * keep rendering instead of breaking.
 */
final class LegacyShortcode {

	public const TAG = 'google-map-v3';

	private const TRUTHY = [ 'true', 'yes', '1', 'on' ];
	private const SEP    = '{}';

	public function register(): void {
		add_shortcode( self::TAG, $this->render( ... ) );
	}

	public function render( mixed $atts, ?string $content = null ): string {
		$atts        = is_array( $atts ) ? $atts : [];
		$new_atts    = self::translate( $atts );
		$new_markers = self::parse_addmarkerlist( isset( $atts['addmarkerlist'] ) ? (string) $atts['addmarkerlist'] : '' );

		return ( new Shortcode() )->render_with_markers( $new_atts, $new_markers );
	}

	/**
	 * Map old attribute names to the modern [slick_map] vocabulary.
	 *
	 * @param array<string,mixed> $atts
	 * @return array<string,string>
	 */
	public static function translate( array $atts ): array {
		$out = [];

		foreach ( [ 'lat', 'lng', 'zoom', 'kml', 'gpx', 'title', 'provider', 'maptype', 'address', 'icon' ] as $passthrough ) {
			if ( isset( $atts[ $passthrough ] ) && $atts[ $passthrough ] !== '' ) {
				$out[ $passthrough ] = (string) $atts[ $passthrough ];
			}
		}

		// Old plugin used `latitude` / `longitude` as full names; map to short.
		if ( ! isset( $out['lat'] ) && isset( $atts['latitude'] ) && (string) $atts['latitude'] !== '' && (string) $atts['latitude'] !== '0' ) {
			$out['lat'] = (string) $atts['latitude'];
		}
		if ( ! isset( $out['lng'] ) && isset( $atts['longitude'] ) && (string) $atts['longitude'] !== '' && (string) $atts['longitude'] !== '0' ) {
			$out['lng'] = (string) $atts['longitude'];
		}

		// Old plugin used `addresscontent` for the centre / fallback address.
		if ( ! isset( $out['address'] ) && isset( $atts['addresscontent'] ) && (string) $atts['addresscontent'] !== '' ) {
			$out['address'] = (string) $atts['addresscontent'];
		}

		if ( isset( $atts['width'] ) && $atts['width'] !== '' ) {
			$out['width'] = self::with_unit( (string) $atts['width'], 'px' );
		}
		if ( isset( $atts['height'] ) && $atts['height'] !== '' ) {
			$out['height'] = self::with_unit( (string) $atts['height'], 'px' );
		}

		if ( isset( $atts['addmarkermashup'] ) && self::is_truthy( (string) $atts['addmarkermashup'] ) ) {
			$out['mashup'] = 'post,page';
		}

		if ( ! isset( $out['provider'] ) ) {
			$out['provider'] = 'google';
		}

		return $out;
	}

	/**
	 * Parse the old `addmarkerlist="addr{}icon{}desc|addr{}icon{}desc|..."` format.
	 *
	 * Each marker becomes ['address' => ..., 'title' => ..., 'icon' => ...] or
	 * ['lat' => ..., 'lng' => ..., ...] when the address parses cleanly as a
	 * comma-separated coordinate pair. Legacy icon names like `1-default.png`
	 * are dropped — only absolute URLs make it through Shortcode::sanitize_icon().
	 *
	 * @return list<array<string,mixed>>
	 */
	public static function parse_addmarkerlist( string $list ): array {
		$list = trim( $list );
		if ( $list === '' ) {
			return [];
		}

		$out = [];
		foreach ( explode( '|', $list ) as $entry ) {
			$entry = trim( $entry );
			if ( $entry === '' ) {
				continue;
			}

			$parts = explode( self::SEP, $entry );
			$addr  = trim( $parts[0] ?? '' );
			$icon  = trim( $parts[1] ?? '' );
			$desc  = trim( $parts[2] ?? '' );

			if ( $addr === '' ) {
				continue;
			}

			$marker = [];

			$coords = preg_split( '/[,;]/', $addr );
			if ( is_array( $coords ) && count( $coords ) === 2
				&& is_numeric( trim( $coords[0] ) )
				&& is_numeric( trim( $coords[1] ) ) ) {
				$marker['lat'] = (float) trim( $coords[0] );
				$marker['lng'] = (float) trim( $coords[1] );
			} else {
				$marker['address'] = $addr;
			}

			if ( $desc !== '' ) {
				$marker['title'] = $desc;
			}
			if ( $icon !== '' ) {
				$marker['icon'] = $icon; // Shortcode::sanitize_icon drops non-URL values.
			}

			$out[] = $marker;
		}
		return $out;
	}

	private static function with_unit( string $value, string $default_unit ): string {
		$value = trim( $value );
		return preg_match( '/^\d+$/', $value ) === 1 ? $value . $default_unit : $value;
	}

	private static function is_truthy( string $value ): bool {
		return in_array( strtolower( trim( $value ) ), self::TRUTHY, true );
	}
}
