<?php
declare(strict_types=1);

namespace SGMP;

use SGMP\Admin\ClassicEditor;
use SGMP\Admin\Settings;
use SGMP\Block\Block;
use SGMP\Frontend\Assets;
use SGMP\Frontend\LegacyShortcode;
use SGMP\Frontend\Shortcode;

final class Plugin {

	public function boot(): void {
		load_plugin_textdomain(
			'slick-google-map',
			false,
			dirname( plugin_basename( SGMP_PLUGIN_FILE ) ) . '/languages'
		);

		( new Settings() )->register();
		( new ClassicEditor() )->register();
		( new Assets() )->register();
		( new Shortcode() )->register();
		( new LegacyShortcode() )->register();
		( new Block() )->register();
	}
}
