<?php
declare(strict_types=1);

namespace SGMP;

final class Options {

	public const OPTION_KEY = 'sgmp_options';

	public const PROVIDER_GOOGLE   = 'google';
	public const PROVIDER_LEAFLET  = 'leaflet';

	public const ALLOWED_PROVIDERS = [ self::PROVIDER_GOOGLE, self::PROVIDER_LEAFLET ];

	public const LAT_META_KEYS = [ 'lat', 'latitude', 'geo_latitude' ];
	public const LNG_META_KEYS = [ 'lng', 'lon', 'longitude', 'geo_longitude' ];

	public static function defaults(): array {
		return [
			'provider'        => self::PROVIDER_LEAFLET,
			'google_api_key'  => '',
			'google_map_id'   => '',
			'default_zoom'    => 12,
			'default_height'  => '400px',
			'default_lat'     => '48.2082',
			'default_lng'     => '16.3738',
			'lat_meta_key'    => 'lat',
			'lng_meta_key'    => 'lng',
			'mashup_ttl'      => 600,
		];
	}

	public static function get(): array {
		$stored = get_option( self::OPTION_KEY, [] );
		return is_array( $stored ) ? array_merge( self::defaults(), $stored ) : self::defaults();
	}

	public static function sanitize( mixed $input ): array {
		$d   = self::defaults();
		$in  = is_array( $input ) ? $input : [];
		$out = $d;

		$provider = isset( $in['provider'] ) ? (string) $in['provider'] : $d['provider'];
		$out['provider'] = in_array( $provider, self::ALLOWED_PROVIDERS, true ) ? $provider : $d['provider'];

		$out['google_api_key'] = isset( $in['google_api_key'] ) ? sanitize_text_field( (string) $in['google_api_key'] ) : '';
		$out['google_map_id']  = isset( $in['google_map_id'] ) ? sanitize_text_field( (string) $in['google_map_id'] ) : '';

		$zoom = isset( $in['default_zoom'] ) ? (int) $in['default_zoom'] : $d['default_zoom'];
		$out['default_zoom'] = max( 0, min( 22, $zoom ) );

		$h = isset( $in['default_height'] ) ? trim( (string) $in['default_height'] ) : $d['default_height'];
		$out['default_height'] = preg_match( '/^\d+(px|em|rem|vh|%)$/', $h ) === 1 ? $h : $d['default_height'];

		foreach ( [ 'default_lat', 'default_lng' ] as $k ) {
			if ( isset( $in[ $k ] ) && is_numeric( $in[ $k ] ) ) {
				$out[ $k ] = (string) (float) $in[ $k ];
			}
		}

		foreach ( [ 'lat_meta_key', 'lng_meta_key' ] as $k ) {
			$v = isset( $in[ $k ] ) ? sanitize_key( (string) $in[ $k ] ) : '';
			$out[ $k ] = $v !== '' ? $v : $d[ $k ];
		}

		$ttl = isset( $in['mashup_ttl'] ) ? (int) $in['mashup_ttl'] : $d['mashup_ttl'];
		$out['mashup_ttl'] = max( 0, min( 86400, $ttl ) );

		return $out;
	}
}
