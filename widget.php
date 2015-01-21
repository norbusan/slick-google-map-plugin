<?php
/*
Copyright (C) 2011-08/2014  Alexander Zagniotov
Copyright (C) 2015 Norbert Preining

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
if ( !function_exists( 'add_action' ) ) {
	echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
	exit;
}

class SimpleGoogleMap_Widget extends WP_Widget {

    function __construct() {
        parent::__construct(
            'simplegooglemap',
            __('Simple Google Map', SGMP_NAME),
            array( 'classname' => 'simplegooglemap_widget', 'description' => __("A simple and intuitive, yet elegant fully documented Google map plugin that installs as a widget and a short code. The plugin is packed with useful features. Widget and shortcode enabled. Offers extensive configuration options for marker, controls, size, KML files, location by latitude/longitude, location by address, info window, directions, traffic/bike lanes and more.", SGMP_NAME)),
            array( 'width' => 570)
        );
    }

	public function widget( $args, $instance ) {

		if (is_admin() || is_feed()) {
			return;
		}

        wp_enqueue_script('sgmp-google-map-jsapi');
        wp_enqueue_script('sgmp-google-map-orchestrator-framework');

		extract($args);
		$map_data_properties = array();
		$not_map_data_properties = array("title", "width", "height", "mapalign", "directionhint",
				"latitude", "longitude", "addresscontent", "addmarkerlisthidden", "addmarkermashuphidden", "enablegeolocationmarkerhidden", "addmarkerinput",
				"showmarker", "animation", "infobubblecontent", "markerdirections", "locationaddmarkerinput", "bubbletextaddmarkerinput");

		$json_default_values = sgmp_fetch_json_data_file(SGMP_JSON_DATA_DEFAULT_WIDGET_PARAM_VALUES);

		foreach ($instance as $key => $value) {
				$value = trim($value);
				$value = (!isset($value) || empty($value)) ? (isset($json_default_values[$key]) ? $json_default_values[$key] : esc_attr(strip_tags($value))) : esc_attr(strip_tags($value));
				$instance[$key] = $value;

				if (!in_array($key, $not_map_data_properties)) {
					$key = str_replace("hidden", "", $key);
					$key = str_replace("_", "", $key);
					$map_data_properties[$key] = $value;
				}
		}
		extract( $instance );
		echo $before_widget;

		if ( isset($title)) {
			echo $before_title .$title . $after_title;
		}

		$addmarkermashuphidden = isset($addmarkermashuphidden) ? $addmarkermashuphidden : "false";
        $enablegeolocationmarkerhidden = isset($enablegeolocationmarkerhidden) ? $enablegeolocationmarkerhidden : "false";
        $enablemarkerclusteringhidden = isset($enablemarkerclusteringhidden) ? $enablemarkerclusteringhidden : "false";
		if ($addmarkermashuphidden == "true") {
            $json_data_arr = make_marker_geo_mashup_2();
            $addmarkerlisthidden = $json_data_arr["data"];
            $map_data_properties['debug'] = $json_data_arr["debug"];
		} else if ($addmarkermashuphidden == "false") {
            $latitude = isset($latitude) && trim($latitude) != "" ? $latitude : "";
            $longitude = isset($longitude) && trim($longitude) != "" ? $longitude : "";
            $addresscontent = isset($addresscontent) && trim($addresscontent) != "" ? $addresscontent : "";
			$addmarkerlisthidden = update_markerlist_from_legacy_locations($latitude, $longitude, $addresscontent, $addmarkerlisthidden);
			$addmarkerlisthidden = htmlspecialchars($addmarkerlisthidden);
		}
		$bad_entities = array("&quot;", "&#039;");
		$addmarkerlisthidden = str_replace($bad_entities, "", $addmarkerlisthidden);
		$addmarkerlisthidden = sgmp_parse_wiki_style_links($addmarkerlisthidden);

        if ($addmarkermashuphidden == 'false' && trim($addmarkerlisthidden) != "") {
            $cached_marker_data_json = get_option(SGMP_MAP_CACHE_WIDGET_PREFIX.$this->id);
            if (isset($cached_marker_data_json) && trim($cached_marker_data_json) != "") {
                $addmarkerlisthidden = $cached_marker_data_json;
                $cache_time = get_option(SGMP_MAP_CACHE_WIDGET_TIME_PREFIX.$this->id);
                $map_data_properties['debug'] = array("widget_id" => $this->id, "state" => "cached", "since" => $cache_time);
            } else {
                $addmarkerlisthidden = sgmp_do_serverside_address_validation_2($addmarkerlisthidden);
                $map_data_properties['debug'] = array("widget_id" => $this->id, "state" => "fresh", "since" => time());
                update_option(SGMP_MAP_CACHE_WIDGET_PREFIX.$this->id, $addmarkerlisthidden);
                update_option(SGMP_MAP_CACHE_WIDGET_TIME_PREFIX.$this->id, time());
            }
        }

		$id = md5(time().' '.rand());
		$map_data_properties['id'] = $id;
        $map_data_properties['markerlist'] = $addmarkerlisthidden;
		$map_data_properties['addmarkermashup'] = $addmarkermashuphidden;
		$map_data_properties['enablegeolocationmarker'] = $enablegeolocationmarkerhidden;
		$map_data_properties['enablemarkerclustering'] = $enablemarkerclusteringhidden;
		$map_data_properties['kml'] = sgmp_clean_kml($map_data_properties['kml']);
		$map_data_properties['panoramiouid'] = sgmp_clean_panoramiouid($map_data_properties['panoramiouid']);

		//When widget was saved and untouched for a long time, the new added config options were not initialized
		$map_data_properties['distanceunits'] = isset($map_data_properties['distanceunits']) ? $map_data_properties['distanceunits'] : "miles";
        $map_data_properties['scrollwheelcontrol'] = isset($map_data_properties['scrollwheelcontrol']) ? $map_data_properties['scrollwheelcontrol'] : "false";
        $map_data_properties['enablemarkerclustering'] = isset($map_data_properties['enablemarkerclustering']) ? $map_data_properties['enablemarkerclustering'] : "false";
		$map_data_properties['tiltfourtyfive'] = isset($map_data_properties['tiltfourtyfive']) ? $map_data_properties['tiltfourtyfive'] : "false";
		$map_data_properties['draggable'] = isset($map_data_properties['draggable']) ? $map_data_properties['draggable'] : "true";
		$map_data_properties['styles'] = isset($map_data_properties['styles']) ? sgmp_clean_styles($map_data_properties['styles']) : "";
        $map_data_properties['styles'] = trim($map_data_properties['styles']) != "" ? base64_encode($map_data_properties['styles']) : $map_data_properties['styles'];
		$poweredby = isset($poweredby) ? $poweredby : "false";
		$language = isset($language) ? $language : "en";

		echo sgmp_draw_map_placeholder($id, $width, $height, $mapalign, $directionhint, $poweredby);
		sgmp_set_google_map_language($language);
		sgmp_map_data_injector(json_encode($map_data_properties), $id);

        echo $after_widget;
	}

    public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;
		foreach ($new_instance as $key => $val) {
			$instance[$key] = strip_tags($new_instance[$key]);
		}

        update_option(SGMP_MAP_CACHE_WIDGET_PREFIX.$this->id, "");
        update_option(SGMP_MAP_CACHE_WIDGET_TIME_PREFIX.$this->id, "");


		return $instance;
	}

	public function form( $instance ) {

		$settings = array();

		$json_html_elems = sgmp_fetch_json_data_file(SGMP_JSON_DATA_HTML_ELEMENTS_FORM_PARAMS);
		$json_default_values = sgmp_fetch_json_data_file(SGMP_JSON_DATA_DEFAULT_WIDGET_PARAM_VALUES);

		if (is_array($json_html_elems)) {

			$legacy_params = array("latitude" => "", "longitude" => "", "addresscontent" => "", "addmarkerlisthidden" => "");

			foreach ($json_html_elems as $data_chunk) {
				$id = $data_chunk['dbParameterId'];
				$value = isset($instance[$id]) && trim($instance[$id]) != "" ? trim($instance[$id]) : "";
				$value = (!isset($value) || empty($value)) ? (isset($json_default_values[$id]) ? $json_default_values[$id] : esc_attr(strip_tags($value))) : esc_attr(strip_tags($value));

				if (array_key_exists($id, $legacy_params)) {
					$legacy_params[$id] = $value;
				}

				if ($id == "addmarkerlisthidden") {
					extract($legacy_params);
					$addmarkerlisthidden = update_markerlist_from_legacy_locations($latitude, $longitude, $addresscontent, $addmarkerlisthidden);
					$value = $addmarkerlisthidden;
				}

				$data_chunk['dbParameterValue'] = $value;
				$data_chunk['dbParameterId'] = $this->get_field_id($id);
				$data_chunk['dbParameterName'] = $this->get_field_name($id);
				sgmp_set_values_for_html_rendering($settings, $data_chunk);
			}
		}

		$template_values = sgmp_build_template_values($settings);
        $template_values['SHORTCODEBUILDER_FORM_TITLE'] = sgmp_render_template_with_values($template_values, SGMP_HTML_TEMPLATE_WIDGET_FORM_TITLE);
        $template_values['SHORTCODEBUILDER_HTML_FORM'] = "";

		$tokens_with_values = array();
		$tokens_with_values['WIDGET_ID_TOKEN'] = $this->id;
		$tokens_with_values['MAP_CONFIGURATION_FORM_TEMPLATE_TOKEN'] = sgmp_render_template_with_values($template_values, SGMP_HTML_TEMPLATE_MAP_CONFIGURATION_FORM);

		echo sgmp_render_template_with_values($tokens_with_values, SGMP_HTML_TEMPLATE_WIDGET);
	}
}
?>
