<?php
declare(strict_types=1);

namespace SGMP\Tests\Unit;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
use SGMP\Frontend\Shortcode;
use SGMP\Options;

final class ShortcodeTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		Functions\when( 'get_option' )->justReturn( Options::defaults() );
		Functions\when( 'wp_generate_uuid4' )->justReturn( '11111111-1111-4111-8111-111111111111' );
		Functions\when( 'wp_json_encode' )->alias( static fn( $v ) => json_encode( $v ) );
		Functions\when( 'esc_attr' )->returnArg( 1 );
		Functions\when( 'wp_enqueue_style' )->justReturn( null );
		Functions\when( 'wp_enqueue_script' )->justReturn( null );
		Functions\when( 'wp_script_is' )->justReturn( false );
		Functions\when( 'shortcode_atts' )->alias(
			static fn( array $defaults, $atts ): array => array_merge( $defaults, is_array( $atts ) ? $atts : [] )
		);
		Functions\when( 'esc_url_raw' )->alias( static fn( $url ) => filter_var( $url, FILTER_VALIDATE_URL ) ?: '' );
		Functions\when( 'esc_url' )->alias( static fn( $url ) => filter_var( $url, FILTER_VALIDATE_URL ) ?: '' );
		Functions\when( 'esc_html' )->returnArg();
		Functions\when( 'wp_parse_url' )->alias( static fn( $url, $component ) => parse_url( $url, $component ) );
		Functions\when( 'is_ssl' )->justReturn( false );
		Functions\when( 'shortcode_parse_atts' )->alias( static function ( $text ) {
			if ( ! is_string( $text ) ) { return []; }
			$pattern = '/(\w+)\s*=\s*"([^"]*)"|(\w+)\s*=\s*\'([^\']*)\'|(\w+)\s*=\s*(\S+)/';
			$atts    = [];
			if ( preg_match_all( $pattern, $text, $matches, PREG_SET_ORDER ) ) {
				foreach ( $matches as $m ) {
					if ( $m[1] !== '' )      { $atts[ $m[1] ] = $m[2]; }
					elseif ( $m[3] !== '' )  { $atts[ $m[3] ] = $m[4]; }
					elseif ( $m[5] !== '' )  { $atts[ $m[5] ] = $m[6]; }
				}
			}
			return $atts;
		} );
		Functions\when( 'get_transient' )->justReturn( false );
		Functions\when( 'set_transient' )->justReturn( true );
		Functions\when( 'is_wp_error' )->justReturn( false );
		Functions\when( 'home_url' )->justReturn( 'https://example.test' );
		Functions\when( 'add_query_arg' )->alias(
			static fn( array $args, string $url ) => $url . '?' . http_build_query( $args )
		);
		Functions\when( 'wp_remote_retrieve_response_code' )->alias( static fn( $r ) => is_array( $r ) ? ( $r['response']['code'] ?? 0 ) : 0 );
		Functions\when( 'wp_remote_retrieve_body' )->alias( static fn( $r ) => is_array( $r ) ? ( $r['body'] ?? '' ) : '' );

		if ( ! defined( 'DAY_IN_SECONDS' ) )  { define( 'DAY_IN_SECONDS', 86400 ); }
		if ( ! defined( 'WEEK_IN_SECONDS' ) ) { define( 'WEEK_IN_SECONDS', 604800 ); }
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function testRenderProducesAttachedDataPayload(): void {
		$html = ( new Shortcode() )->render( [ 'lat' => '10', 'lng' => '20', 'zoom' => '5', 'title' => 'Pin' ] );

		$this->assertStringContainsString( 'class="sgmp-map"', $html );
		$this->assertStringContainsString( 'data-sgmp=', $html );
		$this->assertStringContainsString( '"lat":10', $html );
		$this->assertStringContainsString( '"lng":20', $html );
		$this->assertStringContainsString( '"zoom":5', $html );
	}

	public function testRenderClampsZoomToValidRange(): void {
		$html = ( new Shortcode() )->render( [ 'zoom' => '9999' ] );
		$this->assertStringContainsString( '"zoom":22', $html );

		$html = ( new Shortcode() )->render( [ 'zoom' => '-5' ] );
		$this->assertStringContainsString( '"zoom":0', $html );
	}

	public function testInvalidProviderFallsBackToDefault(): void {
		$html = ( new Shortcode() )->render( [ 'provider' => 'evil' ] );
		$this->assertStringContainsString( '"provider":"' . Options::defaults()['provider'] . '"', $html );
	}

	public function testMaliciousHeightIsRejected(): void {
		$html = ( new Shortcode() )->render( [ 'height' => 'javascript:alert(1)' ] );
		$this->assertStringNotContainsString( 'javascript:', $html );
		$this->assertStringContainsString( 'height:' . Options::defaults()['default_height'], $html );
	}

	public function testNoMarkerWhenLatLngZeroAndNoTitle(): void {
		$html = ( new Shortcode() )->render( [ 'lat' => '0', 'lng' => '0' ] );
		$this->assertStringContainsString( '"markers":[]', $html );
	}

	public function testLatLngWithoutTitleDoesNotEmitMarker(): void {
		// Map center moves but no pin is added — lets [slick_map kml="..."]
		// render the overlay without a stray hometown marker.
		$html = ( new Shortcode() )->render( [ 'lat' => '48.2', 'lng' => '16.3' ] );
		$this->assertStringContainsString( '"markers":[]', $html );
		$this->assertStringContainsString( '"center":{"lat":48.2,"lng":16.3}', $html );
	}

	public function testTitleEmitsMarkerAtSuppliedLatLng(): void {
		$html = ( new Shortcode() )->render( [ 'lat' => '48.2', 'lng' => '16.3', 'title' => 'Vienna' ] );
		$this->assertStringContainsString( '"title":"Vienna"', $html );
		$this->assertStringContainsString( '"lat":48.2,"lng":16.3', $html );
	}

	public function testValidKmlUrlIsAccepted(): void {
		$html = ( new Shortcode() )->render( [ 'kml' => 'https://example.com/track.kml' ] );
		$this->assertStringContainsString( '"kml":"https:\/\/example.com\/track.kml"', $html );
	}

	public function testKmlUrlWithWrongExtensionIsRejected(): void {
		$html = ( new Shortcode() )->render( [ 'kml' => 'https://evil.example.com/payload.html' ] );
		$this->assertStringContainsString( '"kml":""', $html );
	}

	public function testJavascriptSchemeIsRejectedForOverlays(): void {
		$html = ( new Shortcode() )->render( [
			'kml' => 'javascript:alert(1)',
			'gpx' => 'data:text/xml,<gpx/>',
		] );
		$this->assertStringContainsString( '"kml":""', $html );
		$this->assertStringContainsString( '"gpx":""', $html );
	}

	public function testValidGpxUrlIsAccepted(): void {
		$html = ( new Shortcode() )->render( [ 'gpx' => 'https://example.com/hike.gpx' ] );
		$this->assertStringContainsString( '"gpx":"https:\/\/example.com\/hike.gpx"', $html );
	}

	public function testHttpKmlIsUpgradedToHttpsWhenPageIsSsl(): void {
		Functions\when( 'is_ssl' )->justReturn( true );
		$html = ( new Shortcode() )->render( [ 'kml' => 'http://www.preining.info/kml/old.kml' ] );
		$this->assertStringContainsString( '"kml":"https:\/\/www.preining.info\/kml\/old.kml"', $html );
	}

	public function testHttpKmlIsPreservedWhenPageIsNotSsl(): void {
		Functions\when( 'is_ssl' )->justReturn( false );
		$html = ( new Shortcode() )->render( [ 'kml' => 'http://www.preining.info/kml/old.kml' ] );
		$this->assertStringContainsString( '"kml":"http:\/\/www.preining.info\/kml\/old.kml"', $html );
	}

	public function testNestedMarkerChildrenAreParsedFromContent(): void {
		$content = '[marker lat="48.2" lng="16.3" title="Vienna"][marker lat="52.5" lng="13.4" title="Berlin" icon="https://example.com/pin.png"]';
		$html    = ( new Shortcode() )->render( [], $content );

		$this->assertStringContainsString( '"title":"Vienna"', $html );
		$this->assertStringContainsString( '"title":"Berlin"', $html );
		$this->assertStringContainsString( '"icon":"https:\/\/example.com\/pin.png"', $html );
	}

	public function testNestedMarkerWithAddressIsGeocoded(): void {
		Functions\when( 'wp_remote_get' )->justReturn( [
			'response' => [ 'code' => 200 ],
			'body'     => json_encode( [ [ 'lat' => '50.1', 'lon' => '8.7' ] ] ),
		] );
		$html = ( new Shortcode() )->render( [], '[marker address="Frankfurt" title="FRA"]' );
		$this->assertStringContainsString( '"lat":50.1', $html );
		$this->assertStringContainsString( '"lng":8.7', $html );
		$this->assertStringContainsString( '"title":"FRA"', $html );
	}

	public function testOuterAddressBecomesMapCenter(): void {
		Functions\when( 'wp_remote_get' )->justReturn( [
			'response' => [ 'code' => 200 ],
			'body'     => json_encode( [ [ 'lat' => '48.2', 'lon' => '16.3' ] ] ),
		] );
		$html = ( new Shortcode() )->render( [ 'address' => 'Vienna' ] );
		$this->assertStringContainsString( '"center":{"lat":48.2,"lng":16.3}', $html );
		$this->assertStringContainsString( '"markers":[]', $html );
	}

	public function testLegacyIconFilenameResolvesToBundledSvg(): void {
		// "1-default.png" is the most-used historical filename; it must alias
		// to the modern `default` SVG so legacy posts render a real icon.
		$html = ( new Shortcode() )->render(
			[],
			'[marker lat="0.5" lng="1" title="X" icon="1-default.png"]'
		);
		$this->assertStringContainsString( 'assets\/icons\/default.svg', $html );
	}

	public function testModernShortIconNameResolvesToBundledSvg(): void {
		$html = ( new Shortcode() )->render(
			[],
			'[marker lat="0.5" lng="1" title="X" icon="restaurant"]'
		);
		$this->assertStringContainsString( 'assets\/icons\/restaurant.svg', $html );
	}

	public function testControlAttributesDefaultToEnabled(): void {
		$html = ( new Shortcode() )->render( [ 'lat' => '1', 'lng' => '2' ] );
		$this->assertStringContainsString( '"zoom":true', $html );
		$this->assertStringContainsString( '"mapType":true', $html );
		$this->assertStringContainsString( '"streetView":true', $html );
		$this->assertStringContainsString( '"scrollwheel":true', $html );
		$this->assertStringContainsString( '"draggable":true', $html );
	}

	public function testControlAttributesAcceptFalsey(): void {
		$html = ( new Shortcode() )->render( [
			'zoomcontrol'       => 'false',
			'maptypecontrol'    => 'no',
			'streetviewcontrol' => '0',
			'scrollwheel'       => 'off',
			'draggable'         => 'false',
		] );
		$this->assertStringContainsString( '"zoom":false', $html );
		$this->assertStringContainsString( '"mapType":false', $html );
		$this->assertStringContainsString( '"streetView":false', $html );
		$this->assertStringContainsString( '"scrollwheel":false', $html );
		$this->assertStringContainsString( '"draggable":false', $html );
	}

	public function testLayerAttributesDefaultOff(): void {
		$html = ( new Shortcode() )->render( [] );
		$this->assertStringContainsString( '"bike":false', $html );
		$this->assertStringContainsString( '"traffic":false', $html );
	}

	public function testLayerAttributesCanBeEnabled(): void {
		$html = ( new Shortcode() )->render( [ 'showbike' => 'true', 'showtraffic' => 'yes' ] );
		$this->assertStringContainsString( '"bike":true', $html );
		$this->assertStringContainsString( '"traffic":true', $html );
	}

	public function testTiltFortyFive(): void {
		$this->assertStringContainsString( '"tilt":45', ( new Shortcode() )->render( [ 'tilt' => '45' ] ) );
		$this->assertStringContainsString( '"tilt":45', ( new Shortcode() )->render( [ 'tilt' => 'true' ] ) );
		$this->assertStringContainsString( '"tilt":0',  ( new Shortcode() )->render( [] ) );
	}

	public function testStylesJsonIsForwarded(): void {
		$json = '[{"featureType":"poi","stylers":[{"visibility":"off"}]}]';
		$html = ( new Shortcode() )->render( [ 'styles' => $json ] );
		$this->assertStringContainsString( '"styles":[{"featureType":"poi"', $html );
	}

	public function testInvalidStylesJsonIsEmptyArray(): void {
		$html = ( new Shortcode() )->render( [ 'styles' => 'not json' ] );
		$this->assertStringContainsString( '"styles":[]', $html );
	}

	public function testWikiLinkExpansion(): void {
		$expanded = Shortcode::expand_wiki_links( '[[https://example.com|Example]] and [[Plain Page]]' );
		$this->assertStringContainsString( '<a href="https://example.com"', $expanded );
		$this->assertStringContainsString( '>Example</a>', $expanded );
		$this->assertStringContainsString( 'Plain Page', $expanded );
		$this->assertStringNotContainsString( '[[', $expanded );
	}

	public function testDmsToDecimal(): void {
		$this->assertEqualsWithDelta( 48.20826388, Shortcode::dms_to_decimal( '48°12\'29.75"N' ), 0.0001 );
		$this->assertEqualsWithDelta( -16.3738,    Shortcode::dms_to_decimal( '16°22\'25.68"W' ), 0.001 );
		$this->assertEqualsWithDelta( 48.5,        Shortcode::dms_to_decimal( '48.5' ),           0.0001 );
		$this->assertNull( Shortcode::dms_to_decimal( '' ) );
		$this->assertNull( Shortcode::dms_to_decimal( 'not a coord' ) );
	}

	public function testUnknownIconNameStillDropped(): void {
		// Random non-URL string that isn't a known modern or legacy name.
		$html = ( new Shortcode() )->render(
			[],
			'[marker lat="0.5" lng="1" title="X" icon="not-a-real-icon"]'
		);
		$this->assertStringContainsString( '"icon":""', $html );
	}
}
