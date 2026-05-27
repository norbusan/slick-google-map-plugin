(function () {
	'use strict';

	function ready(fn) {
		if (document.readyState !== 'loading') { fn(); }
		else { document.addEventListener('DOMContentLoaded', fn); }
	}

	function parseConfig(el) {
		try { return JSON.parse(el.getAttribute('data-sgmp') || '{}'); }
		catch (e) { return null; }
	}

	function escapeHtml(s) {
		return String(s).replace(/[&<>"']/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
		});
	}
	function escapeAttr(s) { return escapeHtml(s); }

	function renderLeaflet(el, cfg) {
		if (typeof L === 'undefined') { return; }
		var controls = cfg.controls || {};
		var mapOpts = {
			zoomControl:    controls.zoom !== false,
			scrollWheelZoom: controls.scrollwheel !== false,
			dragging:        controls.draggable !== false
		};
		var map = L.map(el, mapOpts).setView([cfg.center.lat, cfg.center.lng], cfg.zoom);
		L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			maxZoom: 19,
			attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
		}).addTo(map);

		var bounds = [];
		(cfg.markers || []).forEach(function (m) {
			var opts = {};
			if (m.icon) {
				opts.icon = L.icon({
					iconUrl: m.icon,
					iconSize: [32, 32],
					iconAnchor: [16, 32],
					popupAnchor: [0, -32]
				});
			}
			var marker = L.marker([m.lat, m.lng], opts).addTo(map);
			if (m.title) {
				var html = m.url
					? '<a href="' + escapeAttr(m.url) + '">' + escapeHtml(m.title) + '</a>'
					: escapeHtml(m.title);
				marker.bindPopup(html);
			}
			bounds.push([m.lat, m.lng]);
		});
		if (bounds.length > 1) {
			map.fitBounds(bounds, { padding: [20, 20] });
		}

		if ((cfg.kml || cfg.gpx) && typeof omnivore !== 'undefined') {
			var layer = cfg.kml ? omnivore.kml(cfg.kml) : omnivore.gpx(cfg.gpx);
			layer.on('ready', function () {
				try { map.fitBounds(layer.getBounds(), { padding: [20, 20] }); } catch (e) {}
			});
			layer.addTo(map);
		}
	}

	// Minimal KML → GeoJSON converter for the Google Data layer.
	// Supports Point, LineString, Polygon, and MultiGeometry inside Placemark.
	function parseCoordsText(node) {
		if (!node) { return []; }
		return node.textContent.trim().split(/\s+/).map(function (tuple) {
			var parts = tuple.split(',');
			var lon = parseFloat(parts[0]), lat = parseFloat(parts[1]);
			return [ lon, lat ];
		}).filter(function (c) { return !isNaN(c[0]) && !isNaN(c[1]); });
	}

	function parseKmlGeometryNode(node) {
		switch (node.tagName) {
			case 'Point': {
				var c = parseCoordsText(node.getElementsByTagName('coordinates')[0]);
				return c.length ? [ { type: 'Point', coordinates: c[0] } ] : [];
			}
			case 'LineString': {
				var c = parseCoordsText(node.getElementsByTagName('coordinates')[0]);
				return c.length ? [ { type: 'LineString', coordinates: c } ] : [];
			}
			case 'Polygon': {
				var rings = [];
				var outer = node.getElementsByTagName('outerBoundaryIs')[0];
				if (outer) {
					var co = parseCoordsText(outer.getElementsByTagName('coordinates')[0]);
					if (co.length) { rings.push(co); }
				}
				var inners = node.getElementsByTagName('innerBoundaryIs');
				for (var j = 0; j < inners.length; j++) {
					var ci = parseCoordsText(inners[j].getElementsByTagName('coordinates')[0]);
					if (ci.length) { rings.push(ci); }
				}
				return rings.length ? [ { type: 'Polygon', coordinates: rings } ] : [];
			}
			case 'MultiGeometry': {
				var inner = [];
				for (var k = 0; k < node.children.length; k++) {
					inner = inner.concat(parseKmlGeometryNode(node.children[k]));
				}
				return inner;
			}
		}
		return [];
	}

	function kmlDocToGeoJson(xmlDoc) {
		var features = [];
		var placemarks = xmlDoc.getElementsByTagName('Placemark');
		for (var i = 0; i < placemarks.length; i++) {
			var pm = placemarks[i];
			var props = {};
			var nameEl = pm.getElementsByTagName('name')[0];
			var descEl = pm.getElementsByTagName('description')[0];
			if (nameEl) { props.name = nameEl.textContent.trim(); }
			if (descEl) { props.description = descEl.textContent.trim(); }

			var geoms = [];
			for (var j = 0; j < pm.children.length; j++) {
				geoms = geoms.concat(parseKmlGeometryNode(pm.children[j]));
			}
			var geom = null;
			if (geoms.length === 1) { geom = geoms[0]; }
			else if (geoms.length > 1) { geom = { type: 'GeometryCollection', geometries: geoms }; }
			if (geom) { features.push({ type: 'Feature', properties: props, geometry: geom }); }
		}
		return { type: 'FeatureCollection', features: features };
	}

	// Loads a KML URL into map.data and resolves with the added Features so
	// the caller can fold the KML's bounds into a combined fit.
	function loadKmlAsData(map, url) {
		return fetch(url, { credentials: 'omit' })
			.then(function (r) {
				if (!r.ok) { throw new Error('SGMP: KML fetch failed with status ' + r.status); }
				return r.text();
			})
			.then(function (text) {
				var doc = new DOMParser().parseFromString(text, 'application/xml');
				if (doc.getElementsByTagName('parsererror').length) {
					throw new Error('SGMP: KML XML parse error');
				}
				var geojson = kmlDocToGeoJson(doc);
				return map.data.addGeoJson(geojson);
			});
	}

	async function renderGoogle(el, cfg) {
		if (typeof google === 'undefined' || !google.maps || typeof google.maps.importLibrary !== 'function') {
			if (window.console) {
				console.error('SGMP: Google bootstrap not present. Is a Google Maps API key configured in Settings → Slick Google Map?');
			}
			return;
		}

		try {
			var coreLib   = await google.maps.importLibrary('core');
			var mapsLib   = await google.maps.importLibrary('maps');
			var markerLib = await google.maps.importLibrary('marker');

			var mapId = (window.SGMP_CONFIG && SGMP_CONFIG.googleMapId) || null;
			var controls = cfg.controls || {};
			var layers   = cfg.layers   || {};
			var mapOpts = {
				center: { lat: cfg.center.lat, lng: cfg.center.lng },
				zoom: cfg.zoom,
				zoomControl:       controls.zoom !== false,
				mapTypeControl:    controls.mapType !== false,
				streetViewControl: controls.streetView !== false,
				scrollwheel:       controls.scrollwheel !== false,
				gestureHandling:   controls.draggable === false ? 'none' : 'auto',
				draggable:         controls.draggable !== false
			};
			if (mapId) { mapOpts.mapId = mapId; }
			if (cfg.maptype && mapsLib.MapTypeId && mapsLib.MapTypeId[cfg.maptype.toUpperCase()]) {
				mapOpts.mapTypeId = mapsLib.MapTypeId[cfg.maptype.toUpperCase()];
			}
			if (cfg.tilt) { mapOpts.tilt = cfg.tilt; }
			if (cfg.styles && cfg.styles.length && !mapId) {
				mapOpts.styles = cfg.styles;
			}

			var map = new mapsLib.Map(el, mapOpts);

			if (layers.bike    && mapsLib.BicyclingLayer) { new mapsLib.BicyclingLayer().setMap(map); }
			if (layers.traffic && mapsLib.TrafficLayer)   { new mapsLib.TrafficLayer().setMap(map); }

			var bounds = new coreLib.LatLngBounds();
			var count = 0;

			(cfg.markers || []).forEach(function (m) {
				var pos = { lat: m.lat, lng: m.lng };
				var marker;
				if (mapId && markerLib.AdvancedMarkerElement) {
					var content = null;
					if (m.icon) {
						content = document.createElement('img');
						content.src = m.icon;
						content.width = 32;
						content.height = 32;
						content.style.display = 'block';
					}
					marker = new markerLib.AdvancedMarkerElement({
						position: pos, map: map, title: m.title || '',
						content: content
					});
				} else {
					var legacyOpts = { position: pos, map: map, title: m.title || '' };
					if (m.icon) { legacyOpts.icon = m.icon; }
					marker = new markerLib.Marker(legacyOpts);
				}
				count++;
				bounds.extend(pos);
				if (m.title) {
					var html = m.url
						? '<a href="' + escapeAttr(m.url) + '">' + escapeHtml(m.title) + '</a>'
						: escapeHtml(m.title);
					var iw = new mapsLib.InfoWindow({ content: html });
					marker.addListener('click', function () { iw.open(map, marker); });
				}
			});

			if (cfg.kml) {
				loadKmlAsData(map, cfg.kml).then(function (features) {
					(features || []).forEach(function (feature) {
						feature.getGeometry().forEachLatLng(function (latLng) {
							bounds.extend(latLng);
						});
					});
					if (!bounds.isEmpty()) { map.fitBounds(bounds); }
				}).catch(function (err) {
					if (window.console) { console.error(err); }
					if (count > 1) { map.fitBounds(bounds); }
				});
			} else if (count > 1) {
				map.fitBounds(bounds);
			}

			if (cfg.gpx && window.console) {
				console.warn('SGMP: GPX overlays are not supported by the Google Maps provider. Use the Leaflet provider for GPX.');
			}
		} catch (err) {
			if (window.console) { console.error('SGMP: Google Maps load failed', err); }
		}
	}

	ready(function () {
		var nodes = document.querySelectorAll('.sgmp-map[data-sgmp]');
		nodes.forEach(function (el) {
			if (el.dataset.sgmpReady === '1') { return; }
			el.dataset.sgmpReady = '1';
			var cfg = parseConfig(el);
			if (!cfg) { return; }
			if (cfg.provider === 'google') {
				renderGoogle(el, cfg);
			} else {
				renderLeaflet(el, cfg);
			}
		});
	});
})();
