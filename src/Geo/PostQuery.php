<?php
declare(strict_types=1);

namespace SGMP\Geo;

use SGMP\Options;

final class PostQuery {

	/**
	 * Fetch published posts of the given types that carry lat/lng meta values.
	 *
	 * @param list<string> $post_types
	 * @return list<array{lat:float,lng:float,title:string,url:string}>
	 */
	public function fetch( array $post_types ): array {
		$post_types = array_values( array_filter( array_map( 'sanitize_key', $post_types ) ) );
		if ( $post_types === [] ) {
			return [];
		}

		$opts     = Options::get();
		$lat_key  = (string) $opts['lat_meta_key'];
		$lng_key  = (string) $opts['lng_meta_key'];
		$ttl      = (int) $opts['mashup_ttl'];

		$cache_key = 'sgmp_mashup_' . md5( implode( ',', $post_types ) . "|{$lat_key}|{$lng_key}" );
		if ( $ttl > 0 ) {
			$cached = get_transient( $cache_key );
			if ( is_array( $cached ) ) {
				return $cached;
			}
		}

		global $wpdb;

		$type_placeholders = implode( ',', array_fill( 0, count( $post_types ), '%s' ) );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- placeholders generated above.
		$sql = $wpdb->prepare(
			"SELECT p.ID, p.post_title,
			        lat_meta.meta_value AS lat,
			        lng_meta.meta_value AS lng
			   FROM {$wpdb->posts} AS p
			   INNER JOIN {$wpdb->postmeta} AS lat_meta
			           ON lat_meta.post_id = p.ID AND lat_meta.meta_key = %s
			   INNER JOIN {$wpdb->postmeta} AS lng_meta
			           ON lng_meta.post_id = p.ID AND lng_meta.meta_key = %s
			  WHERE p.post_status = 'publish'
			    AND p.post_type IN ($type_placeholders)
			  LIMIT 1000",
			array_merge( [ $lat_key, $lng_key ], $post_types )
		);

		$rows = $wpdb->get_results( $sql, ARRAY_A ) ?: [];

		$markers = [];
		foreach ( $rows as $row ) {
			if ( ! is_numeric( $row['lat'] ) || ! is_numeric( $row['lng'] ) ) {
				continue;
			}
			$lat = (float) $row['lat'];
			$lng = (float) $row['lng'];
			if ( $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180 ) {
				continue;
			}
			$post_id   = (int) $row['ID'];
			$icon      = (string) get_post_meta( $post_id, 'marker_icon', true );
			$markers[] = [
				'lat'   => $lat,
				'lng'   => $lng,
				'title' => (string) $row['post_title'],
				'url'   => (string) get_permalink( $post_id ),
				'icon'  => $icon,
			];
		}

		if ( $ttl > 0 ) {
			set_transient( $cache_key, $markers, $ttl );
		}
		return $markers;
	}
}
