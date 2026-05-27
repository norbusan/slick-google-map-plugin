<?php
declare(strict_types=1);

namespace SGMP\Frontend;

use SGMP\Geo\Geocoder;
use SGMP\Geo\PostQuery;
use SGMP\Options;

final class Shortcode {

	public const TAG = 'slick_map';

	public function register(): void {
		add_shortcode( self::TAG, $this->render( ... ) );
	}

	public function render( mixed $atts, ?string $content = null ): string {
		$atts = shortcode_atts( self::default_atts(), is_array( $atts ) ? $atts : [], self::TAG );

		$markers = $this->collect_markers( $atts, (string) ( $content ?? '' ) );
		return $this->render_with_markers( $atts, $markers );
	}

	/** @return array<string,string> */
	private static function default_atts(): array {
		return [
			'lat'                => '',
			'lng'                => '',
			'address'            => '',
			'zoom'               => '',
			'height'             => '',
			'width'              => '100%',
			'title'              => '',
			'icon'               => '',
			'provider'           => '',
			'mashup'             => '',
			'kml'                => '',
			'gpx'                => '',
			'maptype'            => '',
			'zoomcontrol'        => '',
			'maptypecontrol'     => '',
			'streetviewcontrol'  => '',
			'scrollwheel'        => '',
			'draggable'          => '',
			'showbike'           => '',
			'showtraffic'        => '',
			'tilt'               => '',
			'styles'             => '',
		];
	}

	/**
	 * Programmatic entry point used by LegacyShortcode after it has already
	 * parsed the historical `addmarkerlist` format into the modern marker form.
	 *
	 * @param array<string,mixed>            $atts    Raw outer-shortcode attributes (will be normalised).
	 * @param list<array<string,mixed>>      $markers Pre-built marker list (each with lat/lng/address etc.).
	 */
	public function render_with_markers( array $atts, array $markers ): string {
		$opts = Options::get();

		$atts = array_merge(
			array_merge( self::default_atts(), [
				'lat'      => $opts['default_lat'],
				'lng'      => $opts['default_lng'],
				'zoom'     => (string) $opts['default_zoom'],
				'height'   => $opts['default_height'],
				'provider' => $opts['provider'],
			] ),
			array_filter( $atts, static fn( $v ) => $v !== '' && $v !== null )
		);

		$provider = in_array( $atts['provider'], Options::ALLOWED_PROVIDERS, true )
			? $atts['provider']
			: $opts['provider'];

		// Geocode any markers that arrived with an address but no coordinates.
		$markers = $this->resolve_marker_addresses( $markers );

		// Mashup replaces any pre-built marker list.
		if ( $atts['mashup'] !== '' ) {
			$post_types = array_filter( array_map( 'sanitize_key', explode( ',', (string) $atts['mashup'] ) ) );
			if ( $post_types !== [] ) {
				$markers = ( new PostQuery() )->fetch( $post_types );
			}
		}

		// Resolve the outer `address` attribute to a centre point.
		$center_lat = (float) $atts['lat'];
		$center_lng = (float) $atts['lng'];
		if ( $atts['address'] !== '' ) {
			$resolved = ( new Geocoder() )->geocode( (string) $atts['address'] );
			if ( $resolved !== null ) {
				$center_lat = $resolved['lat'];
				$center_lng = $resolved['lng'];
			}
		}

		// Back-compat for the simple form: a `title` on the outer shortcode
		// becomes a single marker at the centre (unless markers were already
		// collected from nested children / legacy addmarkerlist / mashup).
		if ( $markers === [] && $atts['title'] !== '' ) {
			$markers[] = [
				'lat'   => $center_lat,
				'lng'   => $center_lng,
				'title' => (string) $atts['title'],
				'url'   => '',
				'icon'  => $this->sanitize_icon( (string) $atts['icon'] ),
			];
		}

		// Normalise marker shapes for the JS payload.
		$markers = array_values( array_map( fn( array $m ) => $this->normalise_marker( $m ), $markers ) );

		$zoom   = max( 0, min( 22, (int) $atts['zoom'] ) );
		$height = preg_match( '/^\d+(px|em|rem|vh|%)$/', (string) $atts['height'] ) === 1
			? (string) $atts['height']
			: $opts['default_height'];
		$width  = preg_match( '/^\d+(px|em|rem|vw|%)$/', (string) $atts['width'] ) === 1
			? (string) $atts['width']
			: '100%';

		$kml = $this->sanitize_overlay_url( (string) $atts['kml'], [ 'kml' ] );
		$gpx = $this->sanitize_overlay_url( (string) $atts['gpx'], [ 'gpx' ] );

		Assets::enqueue_for_provider( $provider, $kml !== '' || $gpx !== '' );

		$maptype_allow = [ 'roadmap', 'satellite', 'hybrid', 'terrain' ];
		$maptype       = strtolower( trim( (string) $atts['maptype'] ) );
		$maptype       = in_array( $maptype, $maptype_allow, true ) ? $maptype : '';

		$payload = [
			'provider' => $provider,
			'center'   => [ 'lat' => $center_lat, 'lng' => $center_lng ],
			'zoom'     => $zoom,
			'markers'  => $markers,
			'kml'      => $kml,
			'gpx'      => $gpx,
			'maptype'  => $maptype,
			'controls' => [
				'zoom'        => self::tri_bool( $atts['zoomcontrol'], true ),
				'mapType'     => self::tri_bool( $atts['maptypecontrol'], true ),
				'streetView'  => self::tri_bool( $atts['streetviewcontrol'], true ),
				'scrollwheel' => self::tri_bool( $atts['scrollwheel'], true ),
				'draggable'   => self::tri_bool( $atts['draggable'], true ),
			],
			'layers'   => [
				'bike'    => self::tri_bool( $atts['showbike'], false ),
				'traffic' => self::tri_bool( $atts['showtraffic'], false ),
			],
			'tilt'     => self::sanitize_tilt( (string) $atts['tilt'] ),
			'styles'   => self::sanitize_styles_json( (string) $atts['styles'] ),
		];

		$id = 'sgmp-' . wp_generate_uuid4();

		return sprintf(
			'<div class="sgmp-map" id="%1$s" style="width:%2$s;height:%3$s;" data-sgmp="%4$s"></div>',
			esc_attr( $id ),
			esc_attr( $width ),
			esc_attr( $height ),
			esc_attr( wp_json_encode( $payload ) )
		);
	}

	/**
	 * Build a marker list from nested [marker ...] children. Idempotent — if
	 * the caller already supplied a structured marker list (LegacyShortcode
	 * does this), the nested-child parsing is still safe: parse_markers_from
	 * returns [] when no [marker ...] tag is present.
	 *
	 * @return list<array<string,mixed>>
	 */
	private function collect_markers( array $atts, string $content ): array {
		return $this->parse_markers_from( $content );
	}

	/**
	 * @return list<array<string,mixed>>
	 */
	private function parse_markers_from( string $content ): array {
		if ( $content === '' ) {
			return [];
		}
		if ( ! preg_match_all( '/\[marker\b([^\]]*)\]/i', $content, $matches ) ) {
			return [];
		}
		$out = [];
		foreach ( $matches[1] as $atts_string ) {
			$parsed = shortcode_parse_atts( '__sgmp ' . $atts_string );
			if ( ! is_array( $parsed ) ) {
				continue;
			}
			unset( $parsed[0] );
			$out[] = $parsed;
		}
		return $out;
	}

	/**
	 * Replace `address` with `lat`/`lng` for any marker that needs it.
	 *
	 * @param list<array<string,mixed>> $markers
	 * @return list<array<string,mixed>>
	 */
	private function resolve_marker_addresses( array $markers ): array {
		$geocoder = null;
		$out      = [];
		foreach ( $markers as $m ) {
			if ( ( ! isset( $m['lat'] ) || ! isset( $m['lng'] ) || $m['lat'] === '' || $m['lng'] === '' )
				&& isset( $m['address'] ) && $m['address'] !== '' ) {
				$geocoder ??= new Geocoder();
				$resolved   = $geocoder->geocode( (string) $m['address'] );
				if ( $resolved === null ) {
					continue;
				}
				$m['lat'] = $resolved['lat'];
				$m['lng'] = $resolved['lng'];
			}
			if ( isset( $m['lat'], $m['lng'] ) && is_numeric( $m['lat'] ) && is_numeric( $m['lng'] ) ) {
				$out[] = $m;
			}
		}
		return $out;
	}

	/**
	 * @param array<string,mixed> $m
	 * @return array{lat:float,lng:float,title:string,url:string,icon:string}
	 */
	private function normalise_marker( array $m ): array {
		$title = isset( $m['title'] ) ? (string) $m['title'] : '';
		return [
			'lat'   => $this->coerce_coord( $m['lat'] ?? 0 ),
			'lng'   => $this->coerce_coord( $m['lng'] ?? 0 ),
			'title' => self::expand_wiki_links( $title ),
			'url'   => isset( $m['url'] ) ? esc_url_raw( (string) $m['url'] ) : '',
			'icon'  => $this->sanitize_icon( isset( $m['icon'] ) ? (string) $m['icon'] : '' ),
		];
	}

	private function coerce_coord( mixed $v ): float {
		if ( is_numeric( $v ) ) {
			return (float) $v;
		}
		$dec = self::dms_to_decimal( (string) $v );
		return $dec ?? 0.0;
	}

	/**
	 * Convert [[url|text]] → <a href="url">text</a> and [[Foo]] → Foo.
	 * Anchors are limited to http(s) hrefs.
	 */
	public static function expand_wiki_links( string $text ): string {
		if ( $text === '' || ! str_contains( $text, '[[' ) ) {
			return $text;
		}
		return preg_replace_callback(
			'/\[\[([^\]\|]+?)(?:\|([^\]]+))?\]\]/',
			static function ( array $m ): string {
				$target = trim( $m[1] );
				$label  = trim( $m[2] ?? $target );
				if ( preg_match( '#^https?://#i', $target ) ) {
					return sprintf(
						'<a href="%s" rel="noopener noreferrer">%s</a>',
						esc_url( $target ),
						esc_html( $label )
					);
				}
				return esc_html( $label );
			},
			$text
		) ?? $text;
	}

	/**
	 * Parse a coordinate in DMS form (e.g. 48°12'29.5"N, 48 12 29.5 N, 48d12m29.5sN)
	 * to a decimal float. Returns null when the input isn't recognisable as DMS.
	 *
	 * Bare decimal numbers should be parsed with (float) directly; this method is
	 * only called as a fallback for non-numeric coordinate strings.
	 */
	public static function dms_to_decimal( string $value ): ?float {
		$value = trim( $value );
		if ( $value === '' ) {
			return null;
		}
		if ( is_numeric( $value ) ) {
			return (float) $value;
		}
		if ( ! preg_match(
			'/^([+-]?\d+(?:\.\d+)?)\s*[°d ]?\s*(\d+(?:\.\d+)?)?\s*[\'m ]?\s*(\d+(?:\.\d+)?)?\s*["s ]?\s*([NSEWnsew])?$/u',
			$value,
			$m
		) ) {
			return null;
		}
		$deg = isset( $m[1] ) && $m[1] !== '' ? (float) $m[1] : 0.0;
		$min = isset( $m[2] ) && $m[2] !== '' ? (float) $m[2] : 0.0;
		$sec = isset( $m[3] ) && $m[3] !== '' ? (float) $m[3] : 0.0;
		$hem = isset( $m[4] ) ? strtoupper( $m[4] ) : '';

		$dec  = abs( $deg ) + $min / 60 + $sec / 3600;
		if ( $deg < 0 || $hem === 'S' || $hem === 'W' ) {
			$dec = -$dec;
		}
		return $dec;
	}

	private static function tri_bool( mixed $v, bool $default ): bool {
		if ( $v === '' || $v === null ) {
			return $default;
		}
		if ( is_bool( $v ) ) {
			return $v;
		}
		$s = strtolower( trim( (string) $v ) );
		if ( in_array( $s, [ 'true', '1', 'yes', 'on' ], true ) )  { return true; }
		if ( in_array( $s, [ 'false', '0', 'no', 'off' ], true ) ) { return false; }
		return $default;
	}

	private static function sanitize_tilt( string $v ): int {
		$v = strtolower( trim( $v ) );
		if ( $v === '' ) {
			return 0;
		}
		if ( in_array( $v, [ 'true', '1', 'yes', '45' ], true ) ) {
			return 45;
		}
		return 0;
	}

	/** Accept either a JSON array of Google-style entries or '' (no custom styles). */
	private static function sanitize_styles_json( string $v ): array {
		$v = trim( $v );
		if ( $v === '' ) {
			return [];
		}
		$decoded = json_decode( $v, true );
		return is_array( $decoded ) ? $decoded : [];
	}

	/** Modern icon names shipped under assets/icons/. */
	private const ICON_NAMES = [
		'default', 'restaurant', 'lodging', 'cafe', 'bar', 'museum',
		'airport', 'rail', 'shop', 'camera', 'mountain', 'castle', 'religious',
	];

	/** Map of historical icon filenames to their nearest modern equivalent. */
	private const LEGACY_ICON_MAP = [
		'1-default.png'                                     => 'default',
		'2-default.png'                                     => 'default',
		'3-default.png'                                     => 'default',
		'4-default.png'                                     => 'default',
		'5-default.png'                                     => 'default',
		'6-default.png'                                     => 'default',
		'7-default.png'                                     => 'default',
		'restaurant.png'                                    => 'restaurant',
		'restaurant_chinese.png'                            => 'restaurant',
		'restaurant_greek.png'                              => 'restaurant',
		'restaurant_vegetarian.png'                         => 'restaurant',
		'fastfood.png'                                      => 'restaurant',
		'pizzaria.png'                                      => 'restaurant',
		'hotel_0star.png'                                   => 'lodging',
		'hostel_0star.png'                                  => 'lodging',
		'lodging_0star.png'                                 => 'lodging',
		'motel-2.png'                                       => 'lodging',
		'bed_breakfast1-2.png'                              => 'lodging',
		'cabin-2.png'                                       => 'lodging',
		'apartment-3.png'                                   => 'lodging',
		'condominium.png'                                   => 'lodging',
		'house.png'                                         => 'lodging',
		'home.png'                                          => 'lodging',
		'coffee.png'                                        => 'cafe',
		'cup.png'                                           => 'cafe',
		'bar.png'                                           => 'bar',
		'bar_coktail.png'                                   => 'bar',
		'beergarden.png'                                    => 'bar',
		'winebar.png'                                       => 'bar',
		'museum_industry.png'                               => 'museum',
		'museum_naval.png'                                  => 'museum',
		'museum_openair.png'                                => 'museum',
		'museum_science.png'                                => 'museum',
		'childmuseum01.png'                                 => 'museum',
		'airport.png'                                       => 'airport',
		'jetfighter.png'                                    => 'airport',
		'bomber-2.png'                                      => 'airport',
		'helicopter.png'                                    => 'airport',
		'train.png'                                         => 'rail',
		'tramway.png'                                       => 'rail',
		'underground.png'                                   => 'rail',
		'cablecar.png'                                      => 'rail',
		'busstop.png'                                       => 'rail',
		'shop.png'                                          => 'shop',
		'supermarket.png'                                   => 'shop',
		'gifts.png'                                         => 'shop',
		'bags.png'                                          => 'shop',
		'conveniencestore.png'                              => 'shop',
		'photo.png'                                         => 'camera',
		'photography.png'                                   => 'camera',
		'mountains.png'                                     => 'mountain',
		'mountain-pass-locator-diagonal-reverse-export.png' => 'mountain',
		'mountainbiking-3.png'                              => 'mountain',
		'hiking.png'                                        => 'mountain',
		'climbing.png'                                      => 'mountain',
		'castle-2.png'                                      => 'castle',
		'palace-2.png'                                      => 'castle',
		'lighthouse-2.png'                                  => 'castle',
		'chapel-2.png'                                      => 'religious',
		'synagogue-2.png'                                   => 'religious',
		'pagoda-2.png'                                      => 'religious',
		'jewishquarter.png'                                 => 'religious',
		'jewishgrave.png'                                   => 'religious',
		'catholicgrave.png'                                 => 'religious',
	];

	/**
	 * Resolve an icon attribute to a usable URL.
	 *
	 * Accepts three forms:
	 *   - an absolute `http(s)://` URL — passes through (http upgraded on SSL pages)
	 *   - a modern short name like "restaurant" or "lodging" — resolved against assets/icons/
	 *   - a legacy filename like "1-default.png" or "museum_naval.png" — aliased to the
	 *     nearest modern short name before resolution
	 *
	 * Returns '' (renders the provider's default pin) for anything else.
	 */
	private function sanitize_icon( string $value ): string {
		$value = trim( $value );
		if ( $value === '' ) {
			return '';
		}

		if ( preg_match( '#^https?://#i', $value ) ) {
			$clean = esc_url_raw( $value, [ 'http', 'https' ] );
			if ( $clean === '' ) {
				return '';
			}
			if ( is_ssl() && str_starts_with( $clean, 'http://' ) ) {
				$clean = 'https://' . substr( $clean, 7 );
			}
			return $clean;
		}

		$lower = strtolower( $value );
		if ( isset( self::LEGACY_ICON_MAP[ $lower ] ) ) {
			$lower = self::LEGACY_ICON_MAP[ $lower ];
		}
		if ( in_array( $lower, self::ICON_NAMES, true ) ) {
			return SGMP_PLUGIN_URL . 'assets/icons/' . $lower . '.svg';
		}

		return '';
	}

	/**
	 * Validate an overlay URL and ensure the path ends with one of the allowed extensions.
	 *
	 * @param list<string> $extensions
	 */
	private function sanitize_overlay_url( string $url, array $extensions ): string {
		$url = trim( $url );
		if ( $url === '' ) {
			return '';
		}
		$url = esc_url_raw( $url, [ 'http', 'https' ] );
		if ( $url === '' ) {
			return '';
		}
		$path = wp_parse_url( $url, PHP_URL_PATH );
		if ( ! is_string( $path ) ) {
			return '';
		}
		$ext = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
		if ( ! in_array( $ext, $extensions, true ) ) {
			return '';
		}

		if ( is_ssl() && str_starts_with( $url, 'http://' ) ) {
			$url = 'https://' . substr( $url, 7 );
		}

		return $url;
	}
}
