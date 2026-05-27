( function ( wp ) {
	'use strict';

	var el                = wp.element.createElement;
	var registerBlockType = wp.blocks.registerBlockType;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var useBlockProps     = wp.blockEditor.useBlockProps;
	var PanelBody         = wp.components.PanelBody;
	var TextControl       = wp.components.TextControl;
	var SelectControl     = wp.components.SelectControl;
	var Button            = wp.components.Button;
	var __                = wp.i18n.__;

	function MarkerRow( props ) {
		var m = props.marker;
		var update = function ( field ) {
			return function ( value ) {
				var next = Object.assign( {}, m );
				next[ field ] = value;
				props.onChange( next );
			};
		};
		return el( 'div',
			{ style: { padding: '0.5em', border: '1px solid #ddd', marginBottom: '0.5em', borderRadius: '4px' } },
			el( TextControl, { label: __( 'Address', 'slick-google-map' ), value: m.address || '', onChange: update( 'address' ) } ),
			el( 'div', { style: { display: 'flex', gap: '0.5em' } },
				el( TextControl, { label: __( 'Lat', 'slick-google-map' ), value: m.lat || '', onChange: update( 'lat' ) } ),
				el( TextControl, { label: __( 'Lng', 'slick-google-map' ), value: m.lng || '', onChange: update( 'lng' ) } )
			),
			el( TextControl, { label: __( 'Title', 'slick-google-map' ), value: m.title || '', onChange: update( 'title' ) } ),
			el( TextControl, {
				label: __( 'Icon (URL or built-in name)', 'slick-google-map' ),
				help: __( 'e.g. https://… or one of: default, restaurant, lodging, cafe, bar, museum, airport, rail, shop, camera, mountain, castle, religious', 'slick-google-map' ),
				value: m.icon || '',
				onChange: update( 'icon' )
			} ),
			el( TextControl, { label: __( 'Link URL (optional)', 'slick-google-map' ), value: m.url || '', onChange: update( 'url' ) } ),
			el( Button, {
				variant: 'link',
				isDestructive: true,
				onClick: props.onRemove
			}, __( 'Remove marker', 'slick-google-map' ) )
		);
	}

	function Edit( props ) {
		var a = props.attributes;
		var setA = props.setAttributes;
		var markers = Array.isArray( a.markers ) ? a.markers : [];

		var setMarker = function ( i, m ) {
			var next = markers.slice();
			next[ i ] = m;
			setA( { markers: next } );
		};
		var removeMarker = function ( i ) {
			var next = markers.slice();
			next.splice( i, 1 );
			setA( { markers: next } );
		};
		var addMarker = function () {
			setA( { markers: markers.concat( [ { address: '', lat: '', lng: '', title: '', icon: '', url: '' } ] ) } );
		};

		var mapPanel = el( PanelBody, { title: __( 'Map', 'slick-google-map' ), initialOpen: true },
			el( SelectControl, {
				label: __( 'Provider', 'slick-google-map' ),
				value: a.provider || '',
				options: [
					{ label: __( 'Default (from settings)', 'slick-google-map' ), value: '' },
					{ label: 'Leaflet / OSM', value: 'leaflet' },
					{ label: 'Google Maps',   value: 'google' }
				],
				onChange: function ( v ) { setA( { provider: v } ); }
			} ),
			el( TextControl, { label: __( 'Map centre — Latitude', 'slick-google-map' ), value: a.lat,  onChange: function ( v ) { setA( { lat: v } ); } } ),
			el( TextControl, { label: __( 'Map centre — Longitude', 'slick-google-map' ), value: a.lng, onChange: function ( v ) { setA( { lng: v } ); } } ),
			el( TextControl, {
				label: __( 'Zoom (0-22)', 'slick-google-map' ), type: 'number',
				value: a.zoom, onChange: function ( v ) { setA( { zoom: parseInt( v, 10 ) || 0 } ); }
			} ),
			el( TextControl, { label: __( 'Height (e.g. 400px)', 'slick-google-map' ), value: a.height, onChange: function ( v ) { setA( { height: v } ); } } )
		);

		var markersPanel = el( PanelBody, { title: __( 'Markers', 'slick-google-map' ), initialOpen: true },
			markers.length === 0
				? el( 'p', {}, __( 'No markers yet.', 'slick-google-map' ) )
				: markers.map( function ( m, i ) {
					return el( MarkerRow, {
						key: i,
						marker: m,
						onChange: function ( next ) { setMarker( i, next ); },
						onRemove: function () { removeMarker( i ); }
					} );
				} ),
			el( Button, { variant: 'secondary', onClick: addMarker }, __( '+ Add marker', 'slick-google-map' ) )
		);

		var layersPanel = el( PanelBody, { title: __( 'Layers & geo-mashup', 'slick-google-map' ), initialOpen: false },
			el( TextControl, {
				label: __( 'Geo-mashup post types (comma-separated)', 'slick-google-map' ),
				value: a.mashup, onChange: function ( v ) { setA( { mashup: v } ); }
			} ),
			el( TextControl, {
				label: __( 'KML overlay URL', 'slick-google-map' ),
				help: __( 'Works with both providers.', 'slick-google-map' ),
				value: a.kml, onChange: function ( v ) { setA( { kml: v } ); }
			} ),
			el( TextControl, {
				label: __( 'GPX overlay URL', 'slick-google-map' ),
				help: __( 'Leaflet provider only.', 'slick-google-map' ),
				value: a.gpx, onChange: function ( v ) { setA( { gpx: v } ); }
			} )
		);

		var controls = el( InspectorControls, {}, mapPanel, markersPanel, layersPanel );

		var summary = markers.length
			? markers.length + ' ' + __( 'marker(s)', 'slick-google-map' )
			: ( a.mashup ? __( 'Mashup: ', 'slick-google-map' ) + a.mashup : ( a.lat + ', ' + a.lng + ' @ zoom ' + a.zoom ) );

		var preview = el( 'div', useBlockProps( {
			style: { border: '1px dashed #999', padding: '1em', textAlign: 'center', background: '#f6f7f7' }
		} ),
			el( 'strong', {}, __( 'Slick Google Map', 'slick-google-map' ) ),
			el( 'div', {}, summary )
		);

		return [ controls, preview ];
	}

	registerBlockType( 'sgmp/map', { edit: Edit, save: function () { return null; } } );
} )( window.wp );
