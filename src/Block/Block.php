<?php
declare(strict_types=1);

namespace SGMP\Block;

use SGMP\Frontend\Shortcode;

final class Block {

	public function register(): void {
		add_action( 'init', $this->register_block( ... ) );
	}

	public function register_block(): void {
		register_block_type(
			SGMP_PLUGIN_DIR . 'assets/block',
			[
				'render_callback' => $this->render( ... ),
			]
		);
	}

	public function render( array $attributes ): string {
		$atts = [
			'lat'      => isset( $attributes['lat'] ) ? (string) $attributes['lat'] : '',
			'lng'      => isset( $attributes['lng'] ) ? (string) $attributes['lng'] : '',
			'zoom'     => isset( $attributes['zoom'] ) ? (string) (int) $attributes['zoom'] : '',
			'height'   => isset( $attributes['height'] ) ? (string) $attributes['height'] : '',
			'title'    => isset( $attributes['title'] ) ? (string) $attributes['title'] : '',
			'provider' => isset( $attributes['provider'] ) ? (string) $attributes['provider'] : '',
			'mashup'   => isset( $attributes['mashup'] ) ? (string) $attributes['mashup'] : '',
			'kml'      => isset( $attributes['kml'] ) ? (string) $attributes['kml'] : '',
			'gpx'      => isset( $attributes['gpx'] ) ? (string) $attributes['gpx'] : '',
		];
		$atts = array_filter( $atts, static fn( $v ) => $v !== '' );

		$markers = [];
		if ( isset( $attributes['markers'] ) && is_array( $attributes['markers'] ) ) {
			foreach ( $attributes['markers'] as $m ) {
				if ( ! is_array( $m ) ) {
					continue;
				}
				$entry = [];
				foreach ( [ 'address', 'lat', 'lng', 'title', 'icon', 'url' ] as $k ) {
					if ( isset( $m[ $k ] ) && (string) $m[ $k ] !== '' ) {
						$entry[ $k ] = (string) $m[ $k ];
					}
				}
				if ( $entry !== [] ) {
					$markers[] = $entry;
				}
			}
		}

		return ( new Shortcode() )->render_with_markers( $atts, $markers );
	}
}
