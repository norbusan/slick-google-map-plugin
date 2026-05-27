<?php
declare(strict_types=1);

namespace SGMP\Tests\Unit;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
use SGMP\Options;

final class OptionsTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Functions\stubs( [
			'sanitize_text_field' => static fn( $v ) => is_string( $v ) ? trim( strip_tags( $v ) ) : '',
			'sanitize_key'        => static fn( $v ) => is_string( $v ) ? strtolower( preg_replace( '/[^a-z0-9_\-]/i', '', $v ) ) : '',
		] );
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	public function testDefaultsAreReturnedForEmptyInput(): void {
		$out = Options::sanitize( [] );
		$this->assertSame( Options::defaults(), $out );
	}

	public function testNonArrayInputFallsBackToDefaults(): void {
		$this->assertSame( Options::defaults(), Options::sanitize( null ) );
		$this->assertSame( Options::defaults(), Options::sanitize( 'malicious' ) );
		$this->assertSame( Options::defaults(), Options::sanitize( 42 ) );
	}

	public function testProviderIsConstrainedToAllowList(): void {
		$out = Options::sanitize( [ 'provider' => 'evil-provider' ] );
		$this->assertSame( Options::defaults()['provider'], $out['provider'] );

		$out = Options::sanitize( [ 'provider' => Options::PROVIDER_GOOGLE ] );
		$this->assertSame( Options::PROVIDER_GOOGLE, $out['provider'] );
	}

	public function testApiKeyIsSanitized(): void {
		$out = Options::sanitize( [ 'google_api_key' => "  AIza<script>alert(1)</script>BAD  " ] );
		$this->assertStringNotContainsString( '<script>', $out['google_api_key'] );
		$this->assertSame( 'AIzaalert(1)BAD', $out['google_api_key'] );
	}

	public function testZoomIsClamped(): void {
		$this->assertSame( 22, Options::sanitize( [ 'default_zoom' => 9999 ] )['default_zoom'] );
		$this->assertSame( 0,  Options::sanitize( [ 'default_zoom' => -5 ] )['default_zoom'] );
		$this->assertSame( 7,  Options::sanitize( [ 'default_zoom' => '7' ] )['default_zoom'] );
	}

	public function testHeightMustMatchUnitPattern(): void {
		$d = Options::defaults()['default_height'];
		$this->assertSame( '500px', Options::sanitize( [ 'default_height' => '500px' ] )['default_height'] );
		$this->assertSame( '50%',   Options::sanitize( [ 'default_height' => '50%' ] )['default_height'] );
		$this->assertSame( $d,      Options::sanitize( [ 'default_height' => 'javascript:alert(1)' ] )['default_height'] );
		$this->assertSame( $d,      Options::sanitize( [ 'default_height' => '500' ] )['default_height'] );
	}

	public function testLatLngMustBeNumeric(): void {
		$out = Options::sanitize( [ 'default_lat' => 'DROP TABLE', 'default_lng' => '<x>' ] );
		$this->assertSame( Options::defaults()['default_lat'], $out['default_lat'] );
		$this->assertSame( Options::defaults()['default_lng'], $out['default_lng'] );

		$out = Options::sanitize( [ 'default_lat' => '48.5', 'default_lng' => '-2.25' ] );
		$this->assertSame( '48.5',  $out['default_lat'] );
		$this->assertSame( '-2.25', $out['default_lng'] );
	}

	public function testMetaKeysAreSanitizedToSafeIdentifiers(): void {
		$out = Options::sanitize( [ 'lat_meta_key' => "lat; DROP TABLE wp_posts;--" ] );
		$this->assertMatchesRegularExpression( '/^[a-z0-9_\-]+$/i', $out['lat_meta_key'] );
		$this->assertStringNotContainsString( ' ', $out['lat_meta_key'] );
		$this->assertStringNotContainsString( ';', $out['lat_meta_key'] );
	}

	public function testTtlIsClamped(): void {
		$this->assertSame( 86400, Options::sanitize( [ 'mashup_ttl' => 999999 ] )['mashup_ttl'] );
		$this->assertSame( 0,     Options::sanitize( [ 'mashup_ttl' => -1 ] )['mashup_ttl'] );
	}
}
