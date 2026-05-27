<?php
declare(strict_types=1);

namespace SGMP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SGMP\Frontend\LegacyShortcode;

final class LegacyShortcodeTest extends TestCase {

	public function testRealWorldExampleMapsCleanly(): void {
		$out = LegacyShortcode::translate( [
			'shortcodeid'                 => 'TO_BE_GENERATED',
			'width'                       => '100%',
			'height'                      => '350',
			'zoom'                        => '12',
			'maptype'                     => 'terrain',
			'mapalign'                    => 'center',
			'directionhint'               => 'false',
			'language'                    => 'default',
			'poweredby'                   => 'false',
			'maptypecontrol'              => 'true',
			'pancontrol'                  => 'true',
			'zoomcontrol'                 => 'true',
			'scalecontrol'                => 'true',
			'streetviewcontrol'           => 'true',
			'scrollwheelcontrol'          => 'false',
			'draggable'                   => 'true',
			'tiltfourtyfive'              => 'false',
			'enablegeolocationmarker'     => 'false',
			'enablemarkerclustering'      => 'false',
			'addmarkermashup'             => 'false',
			'addmarkermashupbubble'       => 'false',
			'kml'                         => 'http://www.preining.info/kml/2014.01.11-13.Yatsugadake.kml',
			'bubbleautopan'               => 'true',
			'distanceunits'               => 'km',
			'showbike'                    => 'false',
			'showtraffic'                 => 'false',
			'showpanoramio'               => 'false',
		] );

		$this->assertSame( '100%',    $out['width'] );
		$this->assertSame( '350px',   $out['height'], 'Bare numeric heights must get a px unit' );
		$this->assertSame( '12',      $out['zoom'] );
		$this->assertSame( 'terrain', $out['maptype'] );
		$this->assertSame( 'http://www.preining.info/kml/2014.01.11-13.Yatsugadake.kml', $out['kml'] );
		$this->assertSame( 'google',  $out['provider'], 'Legacy shortcode must default to Google' );

		$this->assertArrayNotHasKey( 'mashup', $out, 'addmarkermashup=false must not enable the mashup' );
		$this->assertArrayNotHasKey( 'scalecontrol', $out );
		$this->assertArrayNotHasKey( 'showpanoramio', $out );
		$this->assertArrayNotHasKey( 'shortcodeid', $out );
	}

	public function testAddmarkermashupTrueEnablesMashup(): void {
		$out = LegacyShortcode::translate( [ 'addmarkermashup' => 'true' ] );
		$this->assertSame( 'post,page', $out['mashup'] );
	}

	public function testDimensionsWithExplicitUnitsArePreserved(): void {
		$out = LegacyShortcode::translate( [ 'width' => '600px', 'height' => '50vh' ] );
		$this->assertSame( '600px', $out['width'] );
		$this->assertSame( '50vh',  $out['height'] );
	}

	public function testEmptyAttsStillSelectsGoogle(): void {
		$out = LegacyShortcode::translate( [] );
		$this->assertSame( [ 'provider' => 'google' ], $out );
	}

	public function testExplicitProviderOverridesDefault(): void {
		$out = LegacyShortcode::translate( [ 'provider' => 'leaflet' ] );
		$this->assertSame( 'leaflet', $out['provider'] );
	}

	public function testAddmarkerlistEmptyReturnsEmpty(): void {
		$this->assertSame( [], LegacyShortcode::parse_addmarkerlist( '' ) );
		$this->assertSame( [], LegacyShortcode::parse_addmarkerlist( '   ' ) );
	}

	public function testAddmarkerlistCoordOnlyEntry(): void {
		$markers = LegacyShortcode::parse_addmarkerlist( '36.56132540000001, 136.65620509999997{}1-default.png{}Kenrokuen' );
		$this->assertCount( 1, $markers );
		$this->assertSame( 36.56132540000001, $markers[0]['lat'] );
		$this->assertSame( 136.65620509999997, $markers[0]['lng'] );
		$this->assertSame( 'Kenrokuen', $markers[0]['title'] );
		// The legacy PNG name is preserved on the structured object so
		// Shortcode::sanitize_icon() can decide. Here we just check it's
		// captured; the downstream test asserts it's stripped.
		$this->assertSame( '1-default.png', $markers[0]['icon'] );
	}

	public function testAddmarkerlistAddressOnlyEntry(): void {
		$markers = LegacyShortcode::parse_addmarkerlist( '石川県金沢市{}1-default.png{}Kenrokuen at preining' );
		$this->assertCount( 1, $markers );
		$this->assertSame( '石川県金沢市', $markers[0]['address'] );
		$this->assertSame( 'Kenrokuen at preining', $markers[0]['title'] );
		$this->assertArrayNotHasKey( 'lat', $markers[0] );
	}

	public function testAddmarkerlistMultipleEntriesMixedKinds(): void {
		$markers = LegacyShortcode::parse_addmarkerlist(
			'48.2,16.3{}home.png{}Home|Vienna, Austria{}{}Capital|52.5;13.4{}{}'
		);
		$this->assertCount( 3, $markers );

		$this->assertSame( 48.2, $markers[0]['lat'] );
		$this->assertSame( 16.3, $markers[0]['lng'] );
		$this->assertSame( 'Home', $markers[0]['title'] );

		$this->assertSame( 'Vienna, Austria', $markers[1]['address'] );
		$this->assertSame( 'Capital', $markers[1]['title'] );
		$this->assertArrayNotHasKey( 'icon', $markers[1] );

		// Semicolon also accepted as lat/lng separator (matches the old plugin).
		$this->assertSame( 52.5, $markers[2]['lat'] );
		$this->assertSame( 13.4, $markers[2]['lng'] );
		$this->assertArrayNotHasKey( 'title', $markers[2] );
	}

	public function testAddmarkerlistDropsEmptyEntries(): void {
		$markers = LegacyShortcode::parse_addmarkerlist( '|48.2,16.3{}{}A||' );
		$this->assertCount( 1, $markers );
		$this->assertSame( 'A', $markers[0]['title'] );
	}
}
