<?php
declare(strict_types=1);

namespace SGMP\Geo;

use SGMP\Options;

/**
 * Address -> {lat, lng} resolver.
 *
 * Uses Google Geocoding API when an API key is configured, otherwise falls
 * back to Nominatim/OpenStreetMap. Every lookup is cached in a transient so
 * each unique address is geocoded at most once per cache window — visitors
 * never trigger a live geocode.
 */
final class Geocoder {

	private const CACHE_PREFIX = 'sgmp_geo_';
	private const CACHE_TTL    = 30 * DAY_IN_SECONDS;
	private const NEG_TTL      = DAY_IN_SECONDS;
	private const TIMEOUT      = 5;

	/**
	 * @return array{lat:float,lng:float}|null
	 */
	public function geocode( string $address ): ?array {
		$address = trim( $address );
		if ( $address === '' ) {
			return null;
		}

		$opts     = Options::get();
		$provider = $opts['google_api_key'] !== '' ? 'google' : 'nominatim';
		$key      = self::CACHE_PREFIX . md5( $provider . '|' . $address );

		$cached = get_transient( $key );
		if ( is_array( $cached ) ) {
			return isset( $cached['__neg'] ) ? null : $cached;
		}

		$result = $provider === 'google'
			? $this->geocode_google( $address, $opts['google_api_key'] )
			: $this->geocode_nominatim( $address );

		if ( $result === null ) {
			set_transient( $key, [ '__neg' => true ], self::NEG_TTL );
		} else {
			set_transient( $key, $result, self::CACHE_TTL );
		}
		return $result;
	}

	/**
	 * @return array{lat:float,lng:float}|null
	 */
	private function geocode_google( string $address, string $api_key ): ?array {
		$url = add_query_arg(
			[ 'address' => $address, 'key' => $api_key ],
			'https://maps.googleapis.com/maps/api/geocode/json'
		);
		$response = wp_remote_get( $url, [ 'timeout' => self::TIMEOUT ] );
		if ( is_wp_error( $response ) || (int) wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return null;
		}
		$body = json_decode( (string) wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $body ) || ( $body['status'] ?? '' ) !== 'OK' ) {
			return null;
		}
		$loc = $body['results'][0]['geometry']['location'] ?? null;
		if ( ! is_array( $loc ) || ! isset( $loc['lat'], $loc['lng'] ) ) {
			return null;
		}
		return [ 'lat' => (float) $loc['lat'], 'lng' => (float) $loc['lng'] ];
	}

	/**
	 * @return array{lat:float,lng:float}|null
	 */
	private function geocode_nominatim( string $address ): ?array {
		$url = add_query_arg(
			[ 'q' => $address, 'format' => 'json', 'limit' => 1 ],
			'https://nominatim.openstreetmap.org/search'
		);
		$response = wp_remote_get( $url, [
			'timeout'    => self::TIMEOUT,
			'user-agent' => 'WordPress Slick Google Map plugin (' . home_url() . ')',
		] );
		if ( is_wp_error( $response ) || (int) wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return null;
		}
		$body = json_decode( (string) wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $body ) || ! isset( $body[0]['lat'], $body[0]['lon'] ) ) {
			return null;
		}
		return [ 'lat' => (float) $body[0]['lat'], 'lng' => (float) $body[0]['lon'] ];
	}
}
