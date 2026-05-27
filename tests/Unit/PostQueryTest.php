<?php
declare(strict_types=1);

namespace SGMP\Tests\Unit;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Mockery;
use PHPUnit\Framework\TestCase;
use SGMP\Geo\PostQuery;
use SGMP\Options;

final class PostQueryTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Functions\when( 'get_option' )->justReturn( Options::defaults() );
		Functions\when( 'sanitize_key' )->alias(
			static fn( $v ) => is_string( $v ) ? strtolower( preg_replace( '/[^a-z0-9_\-]/i', '', $v ) ) : ''
		);
		Functions\when( 'get_transient' )->justReturn( false );
		Functions\when( 'set_transient' )->justReturn( true );
		Functions\when( 'get_permalink' )->alias( static fn( int $id ) => "https://example.test/?p={$id}" );
		Functions\when( 'get_post_meta' )->justReturn( '' );
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		Mockery::close();
		global $wpdb;
		$wpdb = null;
		parent::tearDown();
	}

	public function testEmptyPostTypesReturnsEmptyArray(): void {
		$this->assertSame( [], ( new PostQuery() )->fetch( [] ) );
		$this->assertSame( [], ( new PostQuery() )->fetch( [ '', '   ' ] ) );
	}

	public function testFetchUsesPreparedStatementAndFiltersOutOfRangeCoords(): void {
		global $wpdb;
		$wpdb = Mockery::mock();
		$wpdb->posts    = 'wp_posts';
		$wpdb->postmeta = 'wp_postmeta';

		$captured_sql = null;
		$captured_args = null;

		$wpdb->shouldReceive( 'prepare' )
			->once()
			->andReturnUsing( static function ( string $sql, array $args ) use ( &$captured_sql, &$captured_args ) {
				$captured_sql  = $sql;
				$captured_args = $args;
				return 'PREPARED_SQL';
			} );

		$wpdb->shouldReceive( 'get_results' )
			->once()
			->with( 'PREPARED_SQL', \ARRAY_A )
			->andReturn( [
				[ 'ID' => '1', 'post_title' => 'Vienna', 'lat' => '48.2', 'lng' => '16.4' ],
				[ 'ID' => '2', 'post_title' => 'Bad',    'lat' => '999',  'lng' => '0'    ],
				[ 'ID' => '3', 'post_title' => 'NaN',    'lat' => 'x',    'lng' => 'y'    ],
				[ 'ID' => '4', 'post_title' => 'Paris',  'lat' => '48.85','lng' => '2.35' ],
			] );

		$markers = ( new PostQuery() )->fetch( [ 'post', 'page' ] );

		$this->assertCount( 2, $markers, 'Out-of-range and non-numeric rows must be dropped' );
		$this->assertSame( 'Vienna', $markers[0]['title'] );
		$this->assertSame( 'Paris',  $markers[1]['title'] );
		$this->assertSame( 48.2, $markers[0]['lat'] );
		$this->assertSame( 'https://example.test/?p=1', $markers[0]['url'] );

		// Args passed to prepare(): [ lat_key, lng_key, ...post_types ]
		$this->assertSame( [ 'lat', 'lng', 'post', 'page' ], $captured_args );
		$this->assertStringContainsString( '%s,%s', $captured_sql, 'Placeholders must be used for post types' );
		$this->assertStringNotContainsString( "'post'", $captured_sql, 'Raw values must not be interpolated' );
	}

	public function testMaliciousPostTypesAreSanitizedBeforeReachingPrepare(): void {
		global $wpdb;
		$wpdb = Mockery::mock();
		$wpdb->posts    = 'wp_posts';
		$wpdb->postmeta = 'wp_postmeta';

		$captured = null;
		$wpdb->shouldReceive( 'prepare' )
			->andReturnUsing( static function ( string $sql, array $args ) use ( &$captured ) {
				$captured = $args;
				return 'SQL';
			} );
		$wpdb->shouldReceive( 'get_results' )->andReturn( [] );

		( new PostQuery() )->fetch( [ 'post; DROP TABLE wp_users--', 'page' ] );

		// sanitize_key strips `; ` and other unsafe chars before values reach prepare().
		$post_type_args = array_slice( $captured, 2 );
		foreach ( $post_type_args as $arg ) {
			$this->assertMatchesRegularExpression( '/^[a-z0-9_\-]+$/', $arg );
		}
	}
}
