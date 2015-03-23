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

if ( !function_exists('sgmp_shortcode_googlemap_handler') ):
    function sgmp_shortcode_googlemap_handler($attr, $content = null, $code = null) {

        if (is_admin() || is_feed()) {
            return;
        }

        wp_enqueue_script('sgmp-google-map-jsapi');
        wp_enqueue_script('sgmp-google-map-orchestrator-framework');

        $shortcode_attribs = shortcode_atts(array(
            'shortcodeid' => -1,
            'latitude' => 0,
            'longitude' => 0,
            'zoom' => '7',
            'width' => 400,
            'height' => 400,
            'maptype' => 'ROADMAP',
            'maptypecontrol' => 'true',
            'pancontrol' => 'true',
            'addresscontent' => '',
            'zoomcontrol' => 'true',
            'scalecontrol' => 'true',
            'streetviewcontrol' => 'true',
            'scrollwheelcontrol' => 'false',
            'showbike' => 'false',
            'styles' => '',
            'enablemarkerclustering' => 'false',
            'bubbleautopan' => 'false',
            'distanceunits' => 'miles',
            'showtraffic' => 'false',
            'showpanoramio' => 'false',
            'addmarkerlist' => '',
            'kml' => '',
            'directionhint' => 'false',
            'mapalign' => 'center',
            'panoramiouid' => '',
            'enablegeolocationmarker' => 'false',
            'addmarkermashup' => 'false',
            'language' => 'default',
            'poweredby' => 'false',
            'draggable' => 'true',
            'tiltfourtyfive' => 'false',
            'addmarkermashupbubble' => 'false'), $attr);

        // TODO seems that this should NOT be used anymore!!!
        // core.trac.wordpress.org/ticket/22400
        // http://wordpress.stackexchange.com/questions/99603/what-does-extract-shortcode-atts-array-do
        extract($shortcode_attribs);

        $id = md5(time().' '.rand());

        $map_data_properties = array();
        $addmarkerlist = strip_tags($addmarkerlist);

        if ($addmarkermashup == 'true') {
            $json_data_arr = make_marker_geo_mashup_2();
            $addmarkerlist = $json_data_arr["data"];
            $map_data_properties["debug"] = $json_data_arr["debug"];
        } else if ($addmarkermashup == 'false') {
            $addmarkerlist = update_markerlist_from_legacy_locations($latitude, $longitude, $addresscontent, $addmarkerlist);
            $addmarkerlist = htmlspecialchars($addmarkerlist);
        }

        $bad_entities = array("&quot;", "&#039;");
        $addmarkerlist = str_replace($bad_entities, "", $addmarkerlist);
        $addmarkerlist = sgmp_parse_wiki_style_links($addmarkerlist);

        $not_map_data_properties = array("title", "latitude", "longitude", "addresscontent", "addmarkerlist", "showmarker", 
            "animation", "infobubblecontent", "markerdirections", "locationaddmarkerinput", "bubbletextaddmarkerinput");

        foreach ($shortcode_attribs as $key => $value) {
            $value = trim($value);
            $value = (!isset($value) || empty($value)) ? '' : esc_attr(strip_tags($value));

            if (!in_array($key, $not_map_data_properties)) {
                $key = str_replace("hidden", "", $key);
                $key = str_replace("_", "", $key);
                $map_data_properties[$key] = $value;
            }
        }

        if ($addmarkermashup == 'false' && trim($addmarkerlist) != "") {
               
            $post_type = "custom";
            $post_id = -1;

            global $post;
            if (isset($post)) {
                if (is_object($post)) {
                    $post_type = isset($post->post_type) ? $post->post_type : $post_type;
                    $post_id = isset($post->ID) ? $post->ID : $post_id;
                } else if (is_array($post) && !empty($post)) {
                    $post_type = isset($post['post_type']) ? $post['post_type'] : $post_type;
                    $post_id = isset($post['ID']) ? $post['ID'] : $post_id;
                }
            }

            if (is_numeric($shortcodeid) && $shortcodeid == -1) {
                $shortcodeid =  md5($addmarkerlist);
            }

            $json_data_arr = sgmp_get_post_page_cached_markerlist($shortcodeid, $post_id, $post_type, $addmarkerlist);
            $addmarkerlist = $json_data_arr["data"];
            $map_data_properties['debug'] = $json_data_arr["debug"];
        }

        $map_data_properties['id'] = $id;
        $map_data_properties['markerlist'] = $addmarkerlist;
        $map_data_properties['kml'] = sgmp_clean_kml($map_data_properties['kml']);
        $map_data_properties['panoramiouid'] = sgmp_clean_panoramiouid($map_data_properties['panoramiouid']);

        sgmp_set_google_map_language($language);

// That outputs things like the following code
//
// <object id='for-mapid-fcf6faf4bdf2c64b1c09b4c8b0d8726e' name='for-mapid-fcf6faf4bdf2c64b1c09b4c8b0d8726e' class='sgmp-data-placeholder sgmp-json-string-placeholder'><param id='json-string-for-mapid-fcf6faf4bdf2c64b1c09b4c8b0d8726e' name='json-string-for-mapid-fcf6faf4bdf2c64b1c09b4c8b0d8726e' value='{"shortcodeid":"13caa3c929","zoom":"12","width":"100%","height":"350","maptype":"roadmap","maptypecontrol":"true","pancontrol":"true","zoomcontrol":"true","scalecontrol":"true","streetviewcontrol":"true","scrollwheelcontrol":"false","showbike":"false","styles":"","enablemarkerclustering":"false","bubbleautopan":"true","distanceunits":"miles","showtraffic":"false","showpanoramio":"false","kml":"","directionhint":"false","mapalign":"center","panoramiouid":"","enablegeolocationmarker":"false","addmarkermashup":"false","language":"default","poweredby":"false","draggable":"true","tiltfourtyfive":"false","addmarkermashupbubble":"false","debug":{"shortcodeid":"13caa3c929","post_id":25,"post_type":"post","state":"cached","since":"1424934050"},"id":"fcf6faf4bdf2c64b1c09b4c8b0d8726e","markerlist":"\u77f3\u5ddd\u770c\u91d1\u6ca2\u5e02{}1-default.png{}Kenrokuen at preining{}36.56132540000001, 136.65620509999997"}' /></object>
        sgmp_map_data_injector(json_encode($map_data_properties), $id);

        return sgmp_draw_map_placeholder($id, $width, $height, $mapalign, $directionhint, $poweredby);
    }
endif;


# vim:set expandtab tabstop=4 shiftwidth=4 autoindent smartindent: #
?>
