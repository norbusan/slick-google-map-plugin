# Parity with the legacy plugin

This document tracks every notable feature from the 2011–2015 plugin code
(the `[google-map-v3 ...]` family of shortcodes) and where it stands in
the rewrite. It exists so that "is feature X back yet?" has a single
authoritative answer.

The rewrite's source of truth for shortcode behaviour is
[`src/Frontend/Shortcode.php`](src/Frontend/Shortcode.php).
Legacy `[google-map-v3]` and the `addmarkerlist="..."` format are
handled by [`src/Frontend/LegacyShortcode.php`](src/Frontend/LegacyShortcode.php).

---

## ✅ Restored

| Legacy feature / attribute | Modern equivalent |
|---|---|
| `[google-map-v3 ...]` shortcode | Registered as a back-compat alias — old posts render without change. |
| `addmarkerlist="addr{}icon{}desc\|..."` packed format | Parsed into structured markers. |
| `addmarkermashup="true"` | Translated to `mashup="post,page"` on the modern shortcode. |
| `addmarkermashupbubble` (mashup post links in bubbles) | Mashup markers always link to their post permalink. |
| `addresscontent="..."` (centre by address) | `address="…"` outer attribute. Geocoded once, cached 30 days. |
| Address geocoding on markers | Google Geocoding (when key set) or Nominatim/OSM (fallback). Server-side, cached. |
| Custom marker icons (`addmarkerlist="…{}foo.png{}…"`) | ~50 legacy filenames aliased to a modern set of 13 bundled SVGs. |
| Custom icon URLs | `icon="https://…"` passes through; `http://` upgraded to `https://` on SSL pages. |
| `kml="…"` overlay | Native to both providers. Google path replaces the deprecated `KmlLayer` with a `fetch` + inline KML→GeoJSON parser → `google.maps.Data`. |
| `width`, `height`, `zoom` | Pass-through; bare integers get a `px` unit. |
| `maptype` (roadmap / satellite / hybrid / terrain) | Honoured on the Google provider via `mapTypeId`. |
| `maptypecontrol`, `zoomcontrol`, `streetviewcontrol`, `scrollwheelcontrol`, `draggable` | Honoured. `scrollwheelcontrol` → `scrollwheel`; tri-state booleans (`true`/`false`/`yes`/`no`/`1`/`0`). |
| `showbike`, `showtraffic` | Google `BicyclingLayer` / `TrafficLayer`. Ignored on Leaflet. |
| `tiltfourtyfive="true"` | `tilt="45"` (or `true`/`yes`). Google only. |
| `styles="…"` (Google styled maps JSON) | Honoured — but ignored when a Map ID is set (cloud styling wins, per Google's policy). |
| DMS coordinates in marker `lat`/`lng` | Parsed: `48°12'29.5"N`, `48 12 29.5 N`, signed decimal. |
| `[[url\|text]]` / `[[Foo]]` wiki-style links in titles | Expanded server-side. Bare `[[Foo]]` becomes plain text "Foo"; an `http(s)` URL form becomes a real link. |

---

## ✨ New (no legacy equivalent)

These didn't exist in the old plugin but were added during the rewrite.

| Feature | Why |
|---|---|
| Leaflet / OpenStreetMap provider | Lets users skip Google billing entirely. Switchable per-map via `provider="leaflet"` or globally via Settings → Slick Google Map. |
| Gutenberg block (`sgmp/map`) with multi-marker repeater | Replaces the old TinyMCE-only flow; usable in the modern block editor. |
| GPX overlays | Leaflet provider only — Google's API has no native GPX layer. |
| Geocoding cache (per-address transient) | Old plugin geocoded on every page view, burning quota. |
| AdvancedMarkerElement | When a Google **Map ID** is configured, modern Advanced Markers replace the deprecated `google.maps.Marker`. |
| `marker_icon` post meta | Per-post override for mashup markers. |
| HTTPS upgrade for overlay URLs | `http://` KML/GPX URLs from old posts are auto-upgraded on SSL pages to avoid mixed-content blocks. |
| Multi-marker repeater UI in Classic-Editor dialog | The old plugin had a separate marker-builder admin page; the new dialog inlines it. |

---

## 🟡 Missing but doable

Reasonable scope, real value — just not done yet.

| Legacy feature | Effort estimate | Notes |
|---|---|---|
| **Marker clustering** (`enablemarkerclustering="true"`) | ~80 lines + dependency | `@googlemaps/markerclusterer` (Apache-2) for Google, `Leaflet.markercluster` for Leaflet. Important once a mashup has >50 markers. |
| **Directions / routing** | ~150 lines | Click marker A then B to show a route via `google.maps.DirectionsService`. Old plugin's `directionsRenderer` UI flow. |
| **Geolocation marker** (`enablegeolocationmarker="true"`) | ~40 lines | Requires browser permission popup; niche use. |
| Marker animation (`animation="DROP"` / `BOUNCE`) | ~10 lines | `google.maps.Marker.setAnimation()` — doesn't translate cleanly to `AdvancedMarkerElement`. |
| `distanceunits="km"` / `"miles"` | Cosmetic | Only matters once directions are restored. |

---

## ⚠️ Deprecated by upstream — cannot be restored

Even if we wanted these back, the underlying API or service is gone.

| Legacy feature | What happened |
|---|---|
| `showpanoramio="true"`, `panoramiouid="..."` | Google **shut Panoramio down in November 2016**. No replacement API; the photo IDs are unreachable. |
| `pancontrol="true"` | Google **removed `panControl`** from the Maps JS API in 2017. There's no flag to set anymore — the control simply doesn't exist. |
| `google.maps.KmlLayer` (was used internally for `kml=`) | Deprecated as of v3.65 (April 30, 2026). Replaced internally with a `fetch` + KML→GeoJSON parser → `google.maps.Data`; old code path is gone. |
| Old loader `https://www.google.com/jsapi` | Retired by Google in 2017. The modern loader (`maps/api/js`) requires a user-supplied API key (since June 2018). |
| `language="default"` mid-map relayout | The API still accepts `&language=` at load time, but the dynamic relayout the old plugin performed (re-injecting the script tag) is incompatible with the modern loader. Vector cloud-styled maps localize automatically from the visitor's browser. |
| `bubbleautopan="true"` | Modern info windows pan automatically when opened off-screen; the option is redundant. |
| `directionhint="true"`, `poweredby="true"` (chrome captions around the map) | Aesthetic furniture from the 2014-era plugin templates. The modern map container has no surrounding chrome and themes style the wrapping `<div>` directly. |
| `tiltfourtyfive="true"` on raster maps | Tilt only works on vector maps. The flag is honoured (`tilt: 45`) but takes no effect until you use vector tiles (Map ID + Vector style). |

---

## 🗑️ Deliberately dropped

Decisions taken at rewrite time, with rationale.

| Legacy feature | Why dropped |
|---|---|
| Sidebar widget (`SlickGoogleMap_Widget`) | Classic Widgets is in long-term legacy mode in WordPress; block-based widgets supersede it. Use the Gutenberg block instead. |
| Saved shortcodes admin library | A whole admin UI for storing parameterised shortcodes in `wp_options`. Source of multiple unauthenticated-write vulnerabilities in the old plugin. The modern equivalent is "paste the shortcode into the post you want it in". |
| The 300+ marker PNG icon library | Bundle bloat (~1 MB) and unclear per-file licensing. Replaced with 13 bundled SVGs plus a legacy-filename alias map (~50 most-used names). |
| Internal map-data cache (`sgmp_cache_*` options) | Old plugin re-serialised every rendered map's JSON into `wp_options`. Performance gain was minimal in modern PHP; the cache was the largest single security surface. Replaced with HTTP-level browser caching and a transient on geo-mashup queries. |
| TinyMCE Quicktag button | Replaced by the `media_buttons` "Slick Map" button (works in Classic Editor) and the Gutenberg block. |
| `shortcodeid="..."` attribute | Just a cache key for the old options-table cache. Meaningless without that cache. |
| `mapalign="center"` | Themes can target `.sgmp-map` with CSS; no plugin-level alignment switch needed. |

---

## Migration cheat-sheet

| Old post contains | What happens now |
|---|---|
| `[google-map-v3 lat=... lng=... zoom=... maptype=...]` | Renders as Google map with `maptype` honoured. |
| `[google-map-v3 ... kml="http://..."]` | URL auto-upgraded to `https://` on SSL pages; KML rendered via the modern parser. |
| `[google-map-v3 ... addmarkerlist="addr{}1-default.png{}desc\|..."]` | Each entry becomes a modern marker; `1-default.png` aliased to bundled `default.svg`; `desc` becomes the title (wiki links expanded). |
| `[google-map-v3 ... addmarkermashup="true"]` | Translated to `mashup="post,page"` on the new shortcode. |
| `[google-map-v3 ... showpanoramio="true"]` | Silently ignored — Panoramio is shut down. |
| `[google-map-v3 ... pancontrol="true" enablemarkerclustering="true"]` | `pancontrol` silently ignored (Google removed it); `enablemarkerclustering` silently ignored (not yet re-implemented — see "Missing but doable"). |
