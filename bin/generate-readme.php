<?php
/**
 * Convert WordPress.org readme.txt -> GitHub README.md.
 *
 * readme.txt is the single source of truth; this script regenerates
 * README.md whenever readme.txt changes. Run via `composer readme`.
 */

declare(strict_types=1);

$root = dirname( __DIR__ );
$src  = $root . '/readme.txt';
$dst  = $root . '/README.md';

$text = file_get_contents( $src );
if ( $text === false ) {
	fwrite( STDERR, "Cannot read $src\n" );
	exit( 1 );
}

$lines       = preg_split( '/\r\n|\r|\n/', $text );
$title       = null;
$meta        = [];
$tagline     = null;
$body_lines  = [];
$in_meta     = false;

foreach ( $lines as $line ) {
	if ( preg_match( '/^===\s*(.+?)\s*===$/', $line, $m ) ) {
		$title   = $m[1];
		$in_meta = true;
		continue;
	}
	if ( preg_match( '/^==\s*(.+?)\s*==$/', $line, $m ) ) {
		$in_meta      = false;
		$body_lines[] = '## ' . $m[1];
		continue;
	}
	if ( preg_match( '/^=\s*(.+?)\s*=$/', $line, $m ) ) {
		$body_lines[] = '### ' . $m[1];
		continue;
	}

	if ( $in_meta ) {
		if ( trim( $line ) === '' ) {
			$in_meta = false;
			continue;
		}
		if ( preg_match( '/^([A-Za-z][^:]*):\s*(.+)$/', $line, $m ) ) {
			$meta[ trim( $m[1] ) ] = trim( $m[2] );
			continue;
		}
		if ( $tagline === null ) {
			$tagline = trim( $line );
		}
		continue;
	}

	$body_lines[] = $line;
}

$body = implode( "\n", $body_lines );

// Turn "## Screenshots" block (numbered list) into inline image embeds.
$body = preg_replace_callback(
	'/## Screenshots\n+(.+?)(?=\n## |\z)/s',
	static function ( array $m ): string {
		$entries = preg_split( '/\r\n|\r|\n/', $m[1] );
		$images  = [];
		foreach ( $entries as $entry ) {
			if ( preg_match( '/^\s*(\d+)\.\s*(.+?)\s*$/', $entry, $em ) ) {
				$images[] = sprintf(
					"![%s](.wordpress-org/screenshot-%d.png)\n*%s*",
					addcslashes( $em[2], ']' ),
					(int) $em[1],
					$em[2]
				);
			}
		}
		return "## Screenshots\n\n" . implode( "\n\n", $images ) . "\n";
	},
	$body
);

// Assemble the final document.
$out   = [];
$out[] = '# ' . ( $title ?? 'Plugin' );
$out[] = '';

$php_min = $meta['Requires PHP']      ?? '';
$wp_min  = $meta['Requires at least'] ?? '';
$license = $meta['License']           ?? 'GPLv2 or later';

$badges = [];
if ( $php_min !== '' ) {
	$badges[] = sprintf( '![PHP](https://img.shields.io/badge/PHP-%s%%2B-777BB4?logo=php&logoColor=white)', rawurlencode( $php_min ) );
}
if ( $wp_min !== '' ) {
	$badges[] = sprintf( '![WordPress](https://img.shields.io/badge/WordPress-%s%%2B-21759B?logo=wordpress&logoColor=white)', rawurlencode( $wp_min ) );
}
$badges[] = sprintf( '![License](https://img.shields.io/badge/License-%s-blue.svg)', rawurlencode( $license ) );

$out[] = implode( ' ', $badges );
$out[] = '';

if ( $tagline !== null && $tagline !== '' ) {
	$out[] = '> ' . $tagline;
	$out[] = '';
}

$out[] = trim( $body );
$out[] = '';
$out[] = '## Development';
$out[] = '';
$out[] = '```bash';
$out[] = 'composer install   # install dev dependencies';
$out[] = 'composer test      # run the PHPUnit suite';
$out[] = 'composer readme    # regenerate this README.md from readme.txt';
$out[] = '```';
$out[] = '';
$out[] = 'Source layout under `src/`:';
$out[] = '';
$out[] = '- `Plugin.php` – entry point, wires sub-modules';
$out[] = '- `Options.php` – settings DTO with `sanitize()` as the trust boundary';
$out[] = '- `Admin/Settings.php` – Settings API page';
$out[] = '- `Admin/ClassicEditor.php` – "Slick Map" media button + dialog';
$out[] = '- `Frontend/Shortcode.php` – `[slick_map]` renderer';
$out[] = '- `Frontend/LegacyShortcode.php` – `[google-map-v3]` back-compat alias';
$out[] = '- `Frontend/Assets.php` – script/style registration, Google bootstrap';
$out[] = '- `Geo/PostQuery.php` – geo-mashup query (prepared SQL, transient-cached)';
$out[] = '- `Block/Block.php` – server-rendered Gutenberg block';
$out[] = '';
$out[] = '_This file is generated from `readme.txt`. Edit that file, then run `composer readme`._';
$out[] = '';

file_put_contents( $dst, implode( "\n", $out ) );
fwrite( STDOUT, "Wrote $dst\n" );
