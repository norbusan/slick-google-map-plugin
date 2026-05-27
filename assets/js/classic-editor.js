( function ( $ ) {
	'use strict';

	function applyDefaults() {
		var d = (window.SGMP_CE && window.SGMP_CE.defaults) || {};
		if ( $( '#sgmp-ce-lat' ).val() === '' )    { $( '#sgmp-ce-lat' ).val( d.lat || '' ); }
		if ( $( '#sgmp-ce-lng' ).val() === '' )    { $( '#sgmp-ce-lng' ).val( d.lng || '' ); }
		if ( $( '#sgmp-ce-zoom' ).val() === '' )   { $( '#sgmp-ce-zoom' ).val( d.zoom || '' ); }
		if ( $( '#sgmp-ce-height' ).val() === '' ) { $( '#sgmp-ce-height' ).val( d.height || '' ); }
	}

	function escAttr( s ) {
		return String( s ).replace( /"/g, '&quot;' );
	}

	function attrs( fields ) {
		var parts = [];
		Object.keys( fields ).forEach( function ( k ) {
			var v = ( fields[ k ] || '' ).toString().trim();
			if ( v !== '' ) {
				parts.push( k + '="' + escAttr( v ) + '"' );
			}
		} );
		return parts.length ? ' ' + parts.join( ' ' ) : '';
	}

	function buildShortcode() {
		var outer = {
			provider: $( '#sgmp-ce-provider' ).val(),
			lat:      $( '#sgmp-ce-lat' ).val(),
			lng:      $( '#sgmp-ce-lng' ).val(),
			zoom:     $( '#sgmp-ce-zoom' ).val(),
			height:   $( '#sgmp-ce-height' ).val(),
			mashup:   $( '#sgmp-ce-mashup' ).val(),
			kml:      $( '#sgmp-ce-kml' ).val(),
			gpx:      $( '#sgmp-ce-gpx' ).val()
		};

		var markers = [];
		$( '#sgmp-ce-markers .sgmp-ce-marker' ).each( function () {
			var $row = $( this );
			var m = {
				address: $row.find( '.sgmp-ce-m-address' ).val(),
				lat:     $row.find( '.sgmp-ce-m-lat' ).val(),
				lng:     $row.find( '.sgmp-ce-m-lng' ).val(),
				title:   $row.find( '.sgmp-ce-m-title' ).val(),
				icon:    $row.find( '.sgmp-ce-m-icon' ).val()
			};
			if ( ( m.address || '' ).trim() !== '' || ( m.lat || '' ).trim() !== '' ) {
				markers.push( m );
			}
		} );

		if ( markers.length === 0 ) {
			return '[slick_map' + attrs( outer ) + ']';
		}

		var inner = markers.map( function ( m ) {
			return '[marker' + attrs( m ) + ']';
		} ).join( '\n' );

		return '[slick_map' + attrs( outer ) + ']\n' + inner + '\n[/slick_map]';
	}

	function addMarkerRow() {
		var tpl = $( '#sgmp-ce-marker-template' ).html();
		var $row = $( tpl );
		$row.find( '.sgmp-ce-m-remove' ).on( 'click', function () { $row.remove(); } );
		$( '#sgmp-ce-markers' ).append( $row );
	}

	$( function () {
		$( document ).on( 'click', '.thickbox[href*="sgmp-ce-dialog"]', function () {
			setTimeout( applyDefaults, 50 );
		} );

		$( document ).on( 'click', '#sgmp-ce-add-marker', addMarkerRow );

		$( document ).on( 'click', '#sgmp-ce-insert', function () {
			var shortcode = buildShortcode();
			if ( window.wp && wp.media && wp.media.editor ) {
				wp.media.editor.insert( shortcode );
			} else if ( typeof window.send_to_editor === 'function' ) {
				window.send_to_editor( shortcode );
			}
			if ( window.tb_remove ) { window.tb_remove(); }
		} );
	} );
} )( jQuery );
