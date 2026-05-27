<?php
declare(strict_types=1);

namespace SGMP\Frontend;

use SGMP\Options;

final class Assets {

	public const HANDLE_JS    = 'sgmp-frontend';
	public const HANDLE_CSS   = 'sgmp-frontend';
	public const HANDLE_LEAFLET_JS  = 'sgmp-leaflet';
	public const HANDLE_LEAFLET_CSS = 'sgmp-leaflet';
	public const HANDLE_OMNIVORE_JS = 'sgmp-leaflet-omnivore';

	public function register(): void {
		add_action( 'wp_enqueue_scripts', $this->register_assets( ... ) );
	}

	public function register_assets(): void {
		wp_register_style(
			self::HANDLE_LEAFLET_CSS,
			'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
			[],
			'1.9.4'
		);
		wp_register_script(
			self::HANDLE_LEAFLET_JS,
			'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
			[],
			'1.9.4',
			true
		);
		wp_register_script(
			self::HANDLE_OMNIVORE_JS,
			'https://unpkg.com/leaflet-omnivore@0.3.4/leaflet-omnivore.min.js',
			[ self::HANDLE_LEAFLET_JS ],
			'0.3.4',
			true
		);

		wp_register_style(
			self::HANDLE_CSS,
			SGMP_PLUGIN_URL . 'assets/css/frontend.css',
			[],
			SGMP_VERSION
		);

		wp_register_script(
			self::HANDLE_JS,
			SGMP_PLUGIN_URL . 'assets/js/frontend.js',
			[],
			SGMP_VERSION,
			true
		);

		$opts = Options::get();
		wp_localize_script(
			self::HANDLE_JS,
			'SGMP_CONFIG',
			[
				'googleMapId' => $opts['google_map_id'],
				'hasGoogleKey' => $opts['google_api_key'] !== '',
			]
		);

		if ( $opts['google_api_key'] !== '' ) {
			wp_add_inline_script(
				self::HANDLE_JS,
				self::google_bootstrap( $opts['google_api_key'] ),
				'before'
			);
		}
	}

	/**
	 * Returns Google's recommended inline bootstrap loader, parameterised with the user's key.
	 *
	 * After this runs, google.maps.importLibrary("maps") returns a Promise.
	 * The script tag for the actual API is appended lazily on first call.
	 *
	 * @see https://developers.google.com/maps/documentation/javascript/load-maps-js-api#dynamic-library-import
	 */
	private static function google_bootstrap( string $api_key ): string {
		$params = wp_json_encode( [ 'key' => $api_key, 'v' => 'weekly' ] );
		return '(g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e.toString();d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})(' . $params . ');';
	}

	public static function enqueue_for_provider( string $provider, bool $needs_overlay = false ): void {
		wp_enqueue_style( self::HANDLE_CSS );
		wp_enqueue_script( self::HANDLE_JS );

		if ( $provider === Options::PROVIDER_LEAFLET ) {
			wp_enqueue_style( self::HANDLE_LEAFLET_CSS );
			wp_enqueue_script( self::HANDLE_LEAFLET_JS );
			if ( $needs_overlay ) {
				wp_enqueue_script( self::HANDLE_OMNIVORE_JS );
			}
		}
		// Google provider needs nothing extra — the inline bootstrap is already
		// attached to HANDLE_JS, and importLibrary lazily fetches the API.
	}
}
