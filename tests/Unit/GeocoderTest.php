<?php
declare(strict_types=1);

namespace SGMP\Tests\Unit;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
use SGMP\Geo\Geocoder;
use SGMP\Options;

final class GeocoderTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		Functions\when( 'get_transient' )->justReturn( false );
		Functions\when( 'set_transient' )->justReturn( true );
		Functions\when( 'add_query_arg' )->alias(
			static fn( array $args, string $url ): string => $url . '?' . http_build_query( $args )
		);
		Functions\when( 'home_url' )->justReturn( 'https://example.test' );
		Functions\when( 'wp_remote_retrieve_response_code' )->alias(
			static fn( $r ) => is_array( $r ) ? ( $r['response']['code'] ?? 200 ) : 0
		);
		Functions\when( 'wp_remote_retrieve_body' )->alias(
			static fn( $r ) => is_array( $r ) ? ( $r['body'] ?? '' ) : ''
		);
		Functions\when( 'is_wp_error' )->justReturn( false );

		if ( ! defined( 'DAY_IN_SECONDS' ) )   { define( 'DAY_IN_SECONDS', 86400 ); }
		if ( ! defined( 'WEEK_IN_SECONDS' ) )  { define( 'WEEK_IN_SECONDS', 604800 ); }
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function testGoogleProviderUsedWhenKeyIsConfigured(): void {
		Functions\when( 'get_option' )->justReturn(
			array_merge( Options::defaults(), [ 'google_api_key' => 'KEY' ] )
		);

		$captured_url = null;
		Functions\when( 'wp_remote_get' )->alias( static function ( $url, $args ) use ( &$captured_url ) {
			$captured_url = $url;
			return [
				'response' => [ 'code' => 200 ],
				'body'     => json_encode( [
					'status'  => 'OK',
					'results' => [ [ 'geometry' => [ 'location' => [ 'lat' => 48.5, 'lng' => 16.4 ] ] ] ],
				] ),
			];
		} );

		$result = ( new Geocoder() )->geocode( 'Vienna, Austria' );

		$this->assertSame( [ 'lat' => 48.5, 'lng' => 16.4 ], $result );
		$this->assertStringStartsWith( 'https://maps.googleapis.com/maps/api/geocode/json', (string) $captured_url );
		$this->assertStringContainsString( 'key=KEY', (string) $captured_url );
	}

	public function testNominatimUsedWhenNoGoogleKey(): void {
		Functions\when( 'get_option' )->justReturn( Options::defaults() );

		$captured = [];
		Functions\when( 'wp_remote_get' )->alias( static function ( $url, $args ) use ( &$captured ) {
			$captured['url']        = $url;
			$captured['user_agent'] = $args['user-agent'] ?? null;
			return [
				'response' => [ 'code' => 200 ],
				'body'     => json_encode( [ [ 'lat' => '52.5', 'lon' => '13.4' ] ] ),
			];
		} );

		$result = ( new Geocoder() )->geocode( 'Berlin' );

		$this->assertSame( [ 'lat' => 52.5, 'lng' => 13.4 ], $result );
		$this->assertStringStartsWith( 'https://nominatim.openstreetmap.org/search', (string) $captured['url'] );
		$this->assertStringContainsString( 'WordPress Slick Google Map plugin', (string) $captured['user_agent'] );
	}

	public function testEmptyAddressReturnsNullWithoutHttpCall(): void {
		Functions\when( 'get_option' )->justReturn( Options::defaults() );
		Functions\when( 'wp_remote_get' )->alias( static function () {
			throw new \LogicException( 'wp_remote_get must not be called for an empty address' );
		} );

		$this->assertNull( ( new Geocoder() )->geocode( '   ' ) );
	}

	public function testGoogleNonOkResponseReturnsNull(): void {
		Functions\when( 'get_option' )->justReturn(
			array_merge( Options::defaults(), [ 'google_api_key' => 'KEY' ] )
		);
		Functions\when( 'wp_remote_get' )->justReturn( [
			'response' => [ 'code' => 200 ],
			'body'     => json_encode( [ 'status' => 'ZERO_RESULTS', 'results' => [] ] ),
		] );
		$this->assertNull( ( new Geocoder() )->geocode( 'Nowhereville' ) );
	}

	public function testCacheHitReturnsImmediatelyWithoutHttp(): void {
		Functions\when( 'get_option' )->justReturn( Options::defaults() );
		Functions\when( 'get_transient' )->justReturn( [ 'lat' => 1.0, 'lng' => 2.0 ] );
		Functions\when( 'wp_remote_get' )->alias( static function () {
			throw new \LogicException( 'cache hit must not call HTTP' );
		} );

		$this->assertSame( [ 'lat' => 1.0, 'lng' => 2.0 ], ( new Geocoder() )->geocode( 'cached' ) );
	}

	public function testNegativeCacheHitReturnsNull(): void {
		Functions\when( 'get_option' )->justReturn( Options::defaults() );
		Functions\when( 'get_transient' )->justReturn( [ '__neg' => true ] );
		$this->assertNull( ( new Geocoder() )->geocode( 'previously-failed' ) );
	}
}
