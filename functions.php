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
 
if ( !function_exists('sgmp_draw_map_placeholder') ):
    function sgmp_draw_map_placeholder($id, $width, $height, $align, $hint, $poweredby) {


        $widthunits = "px";
        $heightunits = "px";

        $width = strtolower($width);
        $height = strtolower($height);
        $directionswidth = $width;

        if (strpos($width, "%") !== false) {
            $widthunits = "%";
            $width = substr($width, 0, -1);
            $directionswidth = $width;
        }

        if (strpos($width, "px") !== false) {
            $width = substr($width, 0, -1);
            $directionswidth = ($width - 10);
        }

        if (strpos($height, "%") !== false) {
            $height = substr($height, 0, -1);
        }

        if (strpos($height, "px") !== false) {
            $height = substr($height, 0, -1);
        }

        $toploading = ceil($height / 2) - 50;

        $map_marker_directions_hint_template = "";

        if ($hint == "true") {
            $tokens_with_values = array();
            $tokens_with_values['MARKER_DIRECTIONS_HINT_WIDTH_TOKEN'] = $width.$widthunits;
            $tokens_with_values['LABEL_DIRECTIONS_HINT'] = __('Click on map markers to get directions',SGMP_NAME);
            $map_marker_directions_hint_template = sgmp_render_template_with_values($tokens_with_values, SGMP_HTML_TEMPLATE_MAP_MARKER_DIRECTION_HINT);
        }

        $map_poweredby_notice_template = "";
        if ($poweredby == "true") {
            $tokens_with_values = array();
            $tokens_with_values['MARKER_DIRECTIONS_HINT_WIDTH_TOKEN'] = $width.$widthunits;
            $map_poweredby_notice_template = sgmp_render_template_with_values($tokens_with_values, SGMP_HTML_TEMPLATE_MAP_POWEREDBY_NOTICE);
        }

        $tokens_with_values = array();
        $tokens_with_values['MAP_PLACEHOLDER_ID_TOKEN'] = $id;
        $tokens_with_values['MAP_PLACEHOLDER_WIDTH_TOKEN'] = $width.$widthunits;
        $tokens_with_values['MAP_PLACEHOLDER_HEIGHT_TOKEN'] = $height.$heightunits;
        $tokens_with_values['LOADING_INDICATOR_TOP_POS_TOKEN'] = $toploading;
        $tokens_with_values['MAP_ALIGN_TOKEN'] = $align;
        $tokens_with_values['MARKER_DIRECTIONS_HINT_TOKEN'] = $map_marker_directions_hint_template;
        $tokens_with_values['MAP_POWEREDBY_NOTICE_TOKEN'] = $map_poweredby_notice_template;
        $tokens_with_values['IMAGES_DIRECTORY_URI'] = SGMP_PLUGIN_IMAGES;
        $tokens_with_values['DIRECTIONS_WIDTH_TOKEN'] = $directionswidth.$widthunits;
        $tokens_with_values['LABEL_GET_DIRECTIONS'] = __('Get Directions',SGMP_NAME);
        $tokens_with_values['LABEL_PRINT_DIRECTIONS'] = __('Print Directions',SGMP_NAME);
        $tokens_with_values['LABEL_ADDITIONAL_OPTIONS'] = __('Additional options',SGMP_NAME);
        $tokens_with_values['LABEL_AVOID_TOLLS'] = __('Avoid tolls',SGMP_NAME);
        $tokens_with_values['LABEL_AVOID_HIGHWAYS'] = __('Avoid highways',SGMP_NAME);
        $tokens_with_values['LABEL_KM'] = __('KM',SGMP_NAME);
        $tokens_with_values['LABEL_MILES'] = __('Miles',SGMP_NAME);

        return sgmp_render_template_with_values($tokens_with_values, SGMP_HTML_TEMPLATE_MAP_PLACEHOLDER_AND_DIRECTIONS);
    }
endif;


if ( !function_exists('sgmp_render_template_with_values') ):
    function sgmp_render_template_with_values($tokens_with_values, $template_name) {
        $template = file_get_contents(SGMP_PLUGIN_HTML."/".$template_name);
        return sgmp_replace_template_tokens($tokens_with_values, $template);
    }
endif;


if ( !function_exists('sgmp_fetch_json_data_file') ):
    function sgmp_fetch_json_data_file($filename) {

        $json_html_string = file_get_contents(SGMP_PLUGIN_DATA_DIR."/".$filename);
        $json_html = json_decode($json_html_string, true);
        if (sizeof($json_html) == 1) {
            $json_html = $json_html[0];
        }
        return $json_html;
    }
endif;


if ( !function_exists('sgmp_parse_wiki_style_links') ):
    function sgmp_parse_wiki_style_links($text) {

        $pattern = "/\#[^\#]*\#/";
        preg_match_all($pattern, $text, $wikilinks);

        if (isset($wikilinks[0])) {
            foreach ($wikilinks[0] as $wikilink)  {
                $text = str_replace($wikilink, "[TOKEN]", $text);
                $wikilink = preg_replace("/(\#)|(\#)/", "", $wikilink);
                $url_data = preg_split("/[\s,]+/", $wikilink, 2);
                $href = trim($url_data[0]);
                $linkName = "Click Here";
                if (isset($url_data[1])) {
                    $linkName = trim($url_data[1]);
                }

                $anchor = "<a target='_blank' href='".$href."'>".$linkName."</a>";
                $text = str_replace("[TOKEN]", $anchor, $text);
            }
        }
        return $text;
    }
endif;


if ( !function_exists('sgmp_should_load_admin_scripts') ):
    function sgmp_should_load_admin_scripts()  {
        global $pagenow;

        $admin_pages = array('sgmp-documentation', 'sgmp-shortcodebuilder', 'sgmp-settings', 'sgmp-saved-shortcodes');
        $plugin_admin_page = isset($_REQUEST['page']) && trim($_REQUEST['page']) != "" ? $_REQUEST['page'] : "";
        $is_plugin_menu_page = in_array($plugin_admin_page, $admin_pages);

        $action_type = isset($_REQUEST['action']) && trim($_REQUEST['action']) != "" ? $_REQUEST['action'] : "";
        $is_post_edit_mode = ($action_type == "edit" && $pagenow == "post.php");

        $is_post_create_mode = ($pagenow == "post-new.php");

        $is_widgets_page = ($pagenow == "widgets.php");

        // Either we are viewing plugin's admin pages or we are creating new post or any other type
        return ($is_plugin_menu_page || $is_post_create_mode || $is_post_edit_mode || $is_widgets_page);
    }
endif;


if ( !function_exists('sgmp_load_plugin_textdomain') ):
    function sgmp_load_plugin_textdomain() {
        load_plugin_textdomain(SGMP_NAME, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
    }
endif;

global $wp_version;
if (version_compare($wp_version,"3.9","<")){
    if ( !function_exists('sgmp_register_mce') ):
        function sgmp_register_mce() {
            if ( current_user_can('edit_posts') &&  current_user_can('edit_pages') ) {
                add_filter('mce_external_plugins', 'sgmp_load_button_js_into_mce_editor');
                add_filter('mce_buttons', 'sgmp_load_button_into_mce_editor');
            }
        }
    endif;

    if ( !function_exists('sgmp_load_button_js_into_mce_editor') ):
        function sgmp_load_button_js_into_mce_editor($plugin_array) {
            $plugin_array['shortcode'] = SGMP_PLUGIN_JS.'/sgmp.mce.js';
            return $plugin_array;
        }
    endif;
    
    if ( !function_exists('sgmp_load_button_into_mce_editor') ):
        function sgmp_load_button_into_mce_editor($buttons) {
            array_push($buttons, "shortcode");
            return $buttons;
        }
    endif;
}

if ( !function_exists('sgmp_ajax_cache_map_action_callback') ):
    function sgmp_ajax_cache_map_action_callback() {

        check_ajax_referer( SGMP_AJAX_CACHE_MAP_ACTION, SGMP_TIMESTAMP );

        if (isset($_POST['data']))  {
            if (isset($_POST['geoMashup']))  {
                $isGeoMashup = $_POST['geoMashup'] === "true";
                if ($isGeoMashup) {
                    update_option(SGMP_DB_GEOMASHUP_DATA_CACHE, $_POST['data']);
                    update_option(SGMP_DB_GEOMASHUP_DATA_CACHE_TIME, time());
                    echo "OK";
                }  else {

                    if (isset($_POST['postType']))  {
                        $post_db_cache_key = SGMP_MAP_CACHE_POST_PREFIX.$_POST['postId']."_".$_POST['shortcodeId'];
                        $post_db_cache_time_key = SGMP_MAP_CACHE_POST_TIME_PREFIX.$_POST['postId']."_".$_POST['shortcodeId'];

                        $page_db_cache_key = SGMP_MAP_CACHE_PAGE_PREFIX.$_POST['postId']."_".$_POST['shortcodeId'];
                        $page_db_cache_time_key = SGMP_MAP_CACHE_PAGE_TIME_PREFIX.$_POST['postId']."_".$_POST['shortcodeId'];

                        $customtype_db_cache_key = SGMP_MAP_CACHE_CUSTOM_TYPE_PREFIX.$_POST['postId']."_".$_POST['shortcodeId'];
                        $customtype_db_cache_time_key = SGMP_MAP_CACHE_CUSTOM_TYPE_TIME_PREFIX.$_POST['postId']."_".$_POST['shortcodeId'];

                        if ($_POST['postType'] == "post") {
                            update_option($post_db_cache_key, $_POST['data']);
                            update_option($post_db_cache_time_key, time());
                            echo "OK_POST";
                        } else if ($_POST['postType'] == "page") {
                            update_option($page_db_cache_key, $_POST['data']);
                            update_option($page_db_cache_time_key, time());
                            echo "OK_PAGE";
                        } else {
                            update_option($customtype_db_cache_key, $_POST['data']);
                            update_option($customtype_db_cache_time_key, time());
                            echo "OK_CUSTOM";
                        }
                    } else if (isset($_POST['widgetId']))  {
                        update_option(SGMP_MAP_CACHE_WIDGET_PREFIX.$_POST['widgetId'], $_POST['data']);
                        update_option(SGMP_MAP_CACHE_WIDGET_TIME_PREFIX.$_POST['widgetId'], time());
                        echo "OK_WIDGET";
                    }
                }
            }
        }
        exit();
    }
endif;

if ( !function_exists('sgmp_mce_ajax_action_callback') ):
    function sgmp_mce_ajax_action_callback() {

        if (isset($_POST['title']))  {
            $persisted_shortcodes_json = get_option(SGMP_PERSISTED_SHORTCODES);
            if (isset($persisted_shortcodes_json) && trim($persisted_shortcodes_json) != "") {
                $persisted_shortcodes = json_decode($persisted_shortcodes_json, true);
                if (is_array($persisted_shortcodes)) {
                    if (isset($persisted_shortcodes[$_POST['title']])) {
                        unset($persisted_shortcodes[$_POST['title']]);
                        if (empty($persisted_shortcodes)) {
                            //$persisted_shortcodes[] = array("title" => "None saved", "code" => "");
                        }
                        update_option(SGMP_PERSISTED_SHORTCODES, json_encode($persisted_shortcodes));
                        echo "OK";
                    }
                }
            }
        }
        exit();
    }
endif;

if ( !function_exists('sgmp_show_message') ):

function sgmp_show_message($message, $errormsg = false)
{
    if (!isset($message) || $message == '') {
        return;
    }
    if ($errormsg == true) {
        echo '<div id="message" class="error"><p><strong>'.$message.'</strong></p></div>';
    } else {
        echo '<div id="message" class="updated fade"><p><strong>'.$message.'</strong></p></div>';
    }
}
endif;



if ( !function_exists('sgmp_map_data_injector') ):
    function sgmp_map_data_injector($map_json, $id) {
        echo sgmp_map_data_hook_function( $map_json, $id );
    }
endif;


if ( !function_exists('sgmp_map_data_hook_function') ):
    function sgmp_map_data_hook_function( $map_json, $id) {
        global $sgmp_global_map_language;
        $naughty_stuff = array("'", "\r\n", "\n", "\r");
        $map_json = str_replace($naughty_stuff, "", $map_json);
        $objectid = 'for-mapid-'.$id;
        $paramid = 'json-string-'.$objectid;
        return "<object id='".$objectid."' name='".$objectid."' class='sgmp-data-placeholder sgmp-json-string-placeholder'><param id='".$paramid."' name='".$paramid."' value='".$map_json."' /></object>".PHP_EOL."<script type='text/javascript'>SGMPGlobal.language = '".$sgmp_global_map_language."';</script>".PHP_EOL;
    }
endif;



if ( !function_exists('sgmp_set_google_map_language') ):
    function sgmp_set_google_map_language($user_selected_language)  {

        global $sgmp_global_map_language;

        $db_saved_language = get_option(SGMP_DB_SELECTED_LANGUAGE);

        if (!isset($db_saved_language) || $db_saved_language == '') {
            if ($user_selected_language != 'default') {
                update_option(SGMP_DB_SELECTED_LANGUAGE, $user_selected_language);
                $sgmp_global_map_language = $user_selected_language;

            } else {
                if (!is_admin()) {
                    $sgmp_global_map_language = "en";
                }
            }
        } else if (isset($db_saved_language) && $db_saved_language != '') {

            if ($user_selected_language != 'default') {
                update_option(SGMP_DB_SELECTED_LANGUAGE, $user_selected_language);
                $sgmp_global_map_language = $user_selected_language;

            } else {
                $sgmp_global_map_language = $db_saved_language;
            }
        }
    }
endif;


if ( !function_exists('trim_marker_value') ):
    function trim_marker_value(&$value)
    {
        $value = trim($value);
    }
endif;

if ( !function_exists('trim_to_lower_value') ):
    function trim_to_lower_value(&$value)
    {
        $value = trim(strtolower($value));
    }
endif;


if ( !function_exists('update_markerlist_from_legacy_locations') ):
    function update_markerlist_from_legacy_locations($latitude, $longitude, $addresscontent, $hiddenmarkers)  {

        $legacyLoc = isset($addresscontent) ? $addresscontent : "";

        if (isset($latitude) && isset($longitude)) {
            if ($latitude != "0" && $longitude != "0" && $latitude != 0 && $longitude != 0) {
                $legacyLoc = $latitude.",".$longitude;
            }
        }

        if (isset($hiddenmarkers) && $hiddenmarkers != "") {

            $hiddenmarkers_arr = explode("|", $hiddenmarkers);
            $filtered = array();
            foreach($hiddenmarkers_arr as $marker) {
                if (strpos(trim($marker), SGMP_SEP) === false) {
                    $filtered[] = trim($marker.SGMP_SEP."1-default.png");
                } else {
                    $filtered[] = trim($marker);
                }
            }

            $hiddenmarkers = implode("|", $filtered);
        }

        if (trim($legacyLoc) != "")  {
            $hiddenmarkers = $legacyLoc.SGMP_SEP."1-default.png".(isset($hiddenmarkers) && $hiddenmarkers != "" ? "|".$hiddenmarkers : "");
        }

        $hiddenmarkers_arr = explode("|", $hiddenmarkers );
        array_walk($hiddenmarkers_arr, 'trim_marker_value');
        $hiddenmarkers_arr = array_unique($hiddenmarkers_arr);
        return implode("|", $hiddenmarkers_arr);
    }
endif;



if ( !function_exists('sgmp_clean_kml') ):
    function sgmp_clean_kml($kml) {
        $result = '';
        if (isset($kml) && $kml != "") {

            $lowerkml = strtolower(trim($kml));
            $pos = strpos($lowerkml, "http");

            if ($pos !== false && $pos == "0") {
                $kml = strip_tags($kml);
                $kml = str_replace("&#038;", "&", $kml);
                $kml = str_replace("&amp;", "&", $kml);
                $result = trim($kml);
            }
        }
        return $result;
    }
endif;

if ( !function_exists('sgmp_clean_styles') ):
    function sgmp_clean_styles($styles) {
        $styles = trim($styles);
        $styles = preg_replace('/\s+/', '', $styles);
        return str_replace(array("'", '"', "&quot;", "&#39;", "&#8217;"), '"', $styles);
    }
endif;


if ( !function_exists('sgmp_clean_panoramiouid') ):
    function sgmp_clean_panoramiouid($userId) {

        if (isset($userId) && $userId != "") {
            $userId = strtolower(trim($userId));
            $userId = strip_tags($userId);
        }

        return $userId;
    }
endif;



if ( !function_exists('sgmp_create_html_select') ):
    function sgmp_create_html_select($attr) {
        return "<select role='".$attr['role']."' id='".$attr['id']."' style='' class='shortcodeitem' name='".$attr['name']."'>".
                sgmp_create_html_select_options($attr['options'], $attr['value'])."</select>";
    }
endif;


if ( !function_exists('sgmp_create_html_select_options') ):
    function sgmp_create_html_select_options( $options, $so ){
        $r = '';
        foreach ($options as $label => $value){
            $r .= '<option value="'.$value.'"';
            if($value == $so){
                $r .= ' selected="selected"';
            }
            $r .= '>&nbsp;'.$label.'&nbsp;</option>';
        }
        return $r;
    }
endif;


if ( !function_exists('sgmp_create_html_input') ):
    function sgmp_create_html_input($attr) {
        $type = 'text';

        if (isset($attr['type'])) {
            $type = $attr['type'];
        }

        if (strpos($attr['class'], "notshortcodeitem") === false) {
            $attr['class'] = $attr['class']." shortcodeitem";
        }

        return sprintf('<input type="%s" id="%s" name="%s" value="%s" role="%s" class="%s" style="%s" />',
                $type,
                $attr['id'],
                $attr['name'],
                $attr['value'],
                $attr['role'],
                $attr['class'],
                $attr['style']
        );
    }
endif;

if ( !function_exists('sgmp_create_html_list') ):
    function sgmp_create_html_list($attr) {
        return sprintf('<ul class="%s" id="%s" name="%s" style="%s"></ul>', $attr['class'], $attr['id'], $attr['name'], $attr['style']);
    }
endif;


if ( !function_exists('sgmp_create_html_textarea') ):
    function sgmp_create_html_textarea($attr) {
        if (strpos($attr['class'], "notshortcodeitem") === false) {
            $attr['class'] = $attr['class']." shortcodeitem";
        }

        return sprintf('<textarea id="%s" name="%s" role="%s" class="%s" style="%s">%s</textarea>',
            $attr['id'],
            $attr['name'],
            $attr['role'],
            $attr['class'],
            $attr['style'],
            $attr['value']
        );
    }
endif;

if ( !function_exists('sgmp_create_html_label') ):
    function sgmp_create_html_label($attr) {
         return "<label for=".$attr['for'].">".$attr['value']."</label>";
    }
endif;


if ( !function_exists('sgmp_create_html_geobubble') ):
        function sgmp_create_html_geobubble($attr) {
                $falseselected = "checked";
                $trueselected = "";

                if ($attr['value'] == "true") {
                    $falseselected = "";
                    $trueselected = "checked";
                }

                $elem = "<p class='geo-mashup-marker-options'>When Geo mashup marker clicked, info bubble should contain:</p>";
                $elem .= "<input type='radio' class='".$attr['class']."' id='".$attr['id']."-false' role='".$attr['name']."' name='".$attr['name']."' ".$falseselected." value='false' />&nbsp;";
                $elem .= "<label for='".$attr['id']."-false'> - marker location (address or lat/long, whichever was set in the original map)</label><br />";
                $elem .= "<input type='radio' class='".$attr['class']."' id='".$attr['id']."-true' role='".$attr['name']."' name='".$attr['name']."' ".$trueselected." value='true' />&nbsp;";
                $elem .= "<label for='".$attr['id']."-true'> - linked title to the original post/page and the latter's excerpt</label>";
                return $elem;
        }
endif;



if ( !function_exists('sgmp_create_html_custom') ):
    function sgmp_create_html_custom($attr) {
        $markerDir = SGMP_PLUGIN_IMAGES_DIR . "/markers/";
        $items = "<div id='".$attr['id']."' class='".$attr['class']."' style='margin-bottom: 15px; padding-bottom: 10px; padding-top: 10px; padding-left: 30px; height: 200px; overflow: auto; border-radius: 4px 4px 4px 4px; border: 1px solid #C9C9C9;'>";
        if (is_readable($markerDir)) {

            if ($dir = opendir($markerDir)) {

                $files = array();
                while ($files[] = readdir($dir));
                sort($files);
                closedir($dir);

                $extensions = array("png", "jpg", "gif", "jpeg");

                foreach ($files as $file) {
                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

                    if (!in_array($ext, $extensions)) {
                        continue;
                    }

                    if (strrpos($file, "shadow") === false) {
                        $attr['class'] = "";
                        $attr['style'] = "";
                        $sel = "";
                        $iconId = "";
                        $radioId = "";
                        $src = SGMP_PLUGIN_IMAGES."/markers/".$file;
                        if ($file == "1-default.png") {
                            $attr['class'] = "selected-marker-image nomarker";
                            $attr['style'] = "cursor: default; ";
                            $sel = "checked='checked'";
                            $iconId = "default-marker-icon";
                            $radioId = $iconId."-radio";
                        } else if ($file == "2-default.png" || $file == "3-default.png") {
                            $attr['class'] = "nomarker";
                        }

                        $items .= "<div style='float: left; text-align: center; margin-right: 8px;'><a href='javascript:void(0);'><img id='".$iconId."' style='".$attr['style']."' class='".$attr['class']."' src='".$src."' border='0' /></a><br /><input ".$sel." type='radio' id='".$radioId."' value='".$file."' style='' name='custom-icons-radio' /></div>";

                    }
                }
            }
        }

        return $items."</div>";
    }
endif;


if ( !function_exists('sgmp_replace_template_tokens') ):
    function sgmp_replace_template_tokens($token_values, $template)  {
        foreach ($token_values as $key => $value) {
            $template = str_replace($key, $value, $template);
        }
        return $template;
    }
endif;


if ( !function_exists('sgmp_build_template_values') ):
    function sgmp_build_template_values($settings) {

        $template_values = array();

        foreach($settings as $setting) {
            $function_type = $setting['type'];
            $token = $setting['token'];
            $token_prefix = (isset($setting['token_prefix']) ? $setting['token_prefix'] : '');

            $function_name =  "sgmp_create_html_".$function_type;
            $html_template_token_name = strtoupper((isset($token_prefix) && $token_prefix != '' ) ? $token_prefix : $function_type)."_".strtoupper($token);
            $template_values[$html_template_token_name] = "COULD NOT RENDER HTML";
            if (function_exists($function_name)) {
                $template_values[$html_template_token_name] = $function_name($setting['attr']);
            }
        }
        return $template_values;
    }
endif;


if ( !function_exists('sgmp_set_values_for_html_rendering') ):
    function sgmp_set_values_for_html_rendering(&$settings, $params) {

        $html_element_select_options = array();
        $html_element_select_options['miles_km'] = array("Miles" => "miles", "KM" => "km");
        $html_element_select_options['show_hide'] = array("Show" => "true", "Hide" => "false");
        $html_element_select_options['enable_disable_xor'] = array("Enable" => "false", "Disable" => "true");
        $html_element_select_options['enable_disable'] = array("Enable" => "true", "Disable" => "false");
        $html_element_select_options['map_types'] = array("Roadmap"=>"roadmap", "Satellite"=>"satellite", "Hybrid"=>"hybrid", "Terrain" => "terrain", "OpenStreet"=>"OSM");
        $html_element_select_options['animation_types'] = array("Drop"=>"DROP", "Bounce"=>"BOUNCE");
        $html_element_select_options['map_aligns'] = array("Center"=>"center", "Right"=>"right", "Left" => "left");
        $html_element_select_options['languages'] = array("Default" => "default", "Arabic" => "ar", "Basque" => "eu", "Bulgarian" => "bg", "Bengali" => "bn", "Catalan" => "ca", "Czech" => "cs", "Danish" => "da", "English" => "en", "German" => "de", "Greek" => "el", "Spanish" => "es", "Farsi" => "fa", "Finnish" => "fi", "Filipino" => "fil", "French" => "fr", "Galician" => "gl", "Gujarati" => "gu", "Hindi" => "hi", "Croatian" => "hr", "Hungarian" => "hu", "Indonesian" => "id", "Italian" => "it", "Hebrew" => "iw", "Japanese" => "ja", "Kannada" => "kn", "Korean" => "ko", "Lithuanian" => "lt", "Latvian" => "lv", "Malayalam" => "ml", "Marathi" => "mr", "Dutch" => "nl", "Norwegian" => "no", "Oriya" => "or", "Polish" => "pl", "Portuguese" => "pt", "Romanian" => "ro", "Russian" => "ru", "Slovak" => "sk", "Slovenian" => "sl", "Serbian" => "sr", "Swedish" => "sv", "Tagalog" => "tl", "Tamil" => "ta", "Telugu" => "te", "Thai" => "th", "Turkish" => "tr", "Ukrainian" => "uk", "Vietnamese" => "vi", "Chinese (simpl)" => "zh-CN", "Chinese (tradi)" => "zh-TW");


        if (isset($params['htmlLabelValue']) && trim($params['htmlLabelValue']) != "") {
            $settings[] = array("type" => "label", "token" => $params['templateTokenNameSuffix'], 
                "attr" => array("for" => $params['dbParameterId'], "value" => $params['htmlLabelValue'])); 
        }

        $settings[] = array(
                    "type" => (isset($params['backendFunctionNameSuffix']) ? $params['backendFunctionNameSuffix'] : ''),
                    "token" => (isset($params['templateTokenNameSuffix']) ? $params['templateTokenNameSuffix'] : ''),
                    "token_prefix" => (isset($params['templateTokenNamePrefix']) ? $params['templateTokenNamePrefix'] : ''),
                    "attr"=> array(
                        "role" => (isset($params['templateTokenNameSuffix']) ? $params['templateTokenNameSuffix'] : ''),
                        "id" => (isset($params['dbParameterId']) ? $params['dbParameterId'] : ''),
                        "name" => (isset($params['dbParameterName']) ? $params['dbParameterName'] : ''),
                        "type" => (isset($params['htmlInputElementType']) ? $params['htmlInputElementType'] : ''),
                        "value" => (isset($params['dbParameterValue']) ? $params['dbParameterValue'] : ""),
                        "class" => (isset($params['cssClasses']) ? $params['cssClasses'] : ""),
                        "style" => (isset($params['inlineCss']) ? $params['inlineCss'] : ""),
                        "options" => (isset($params['htmlSelectOptionsKey']) ? $html_element_select_options[$params['htmlSelectOptionsKey']] : array())
                    )
                );
    }
endif;



if ( !function_exists('sgmp_google_map_deregister_scripts') ):
function sgmp_google_map_deregister_scripts() {
    $handle = '';
    global $wp_scripts;

    if (isset($wp_scripts->registered) && is_array($wp_scripts->registered)) {
        foreach ( $wp_scripts->registered as $script) {

            if (strpos($script->src, 'http://maps.googleapis.com/maps/api/js') !== false && $script->handle != 'sgmp-google-map-api') {

                if (!isset($script->handle) || $script->handle == '') {
                    $script->handle = 'remove-google-map-duplicate';
                }

                unset($script->src);
                $handle = $script->handle;

                if ($handle != '') {
                    $wp_scripts->remove( $handle );
                    $handle = '';
                    break;
                }
            }
        }
    }
}
endif;

if ( !function_exists('sgmp_plugin_action_links') ):
    function sgmp_plugin_action_links($links, $file) {
        $plugin =  plugin_basename(SGMP_PLUGIN_BOOTSTRAP);
        if ($file == $plugin) {
            $settings_link = sprintf( '<a href="admin.php?page=sgmp-settings">%s</a>', __('Settings',SGMP_NAME) );
            array_unshift($links, $settings_link);
        }
        return $links;
    }
endif;


if ( !function_exists('extract_published_content_containing_shortcode') ):
    function extract_published_content_containing_shortcode($content_type)  {

        $total_published = "custom";
        if ($content_type == "page" || $content_type == "post") {
            $count_posts = wp_count_posts($content_type);
            $total_published = $count_posts->publish;
        }

        // To avoid cases where plugin's like Ultimate Category Excluder messes around with the main query by using filter 'pre_get_posts' to exclude posts
        global $wpdb;
        $table = $wpdb->posts;

        // LIMIT 1000 should be more than enough, really who has a blog with 1000+ published content these days?
        $query = "SELECT * FROM $table WHERE $table.post_type IN ('".$content_type."') AND $table.post_status = 'publish' LIMIT 1000";
        $posts = $wpdb->get_results($query);

        $extracted = array();
        $pattern = "/\[google-map-v3[^\]]*\]/";
        $addresses = array();
        $per_published_content_type_address_counter = 0;
        foreach($posts as $post)  {

            $content_id = $content_type . "_" . $post->ID;

            preg_match_all($pattern, $post->post_content, $matches);
            if (is_array($matches[0]) && count($matches[0]) > 0) {

                $matches_shortcodes_per_post = $matches[0];
                foreach($matches_shortcodes_per_post as $matched_shortcode)  {
                    $washed_marker_csv = str_replace(array("\r\n", "\r", "\n"), " ", $matched_shortcode);

                    $pattern = "/addmarkerlist=\"(.*?)\"/";
                    preg_match_all($pattern, $washed_marker_csv, $address_matches);

                    if (is_array($address_matches) && is_array($address_matches[1]) && !empty($address_matches[1])) {
                        $addresss_segments = explode(SGMP_SEP, $address_matches[1][0]);
                        $address_as_string = $addresss_segments[0];
                        if (isset($address_as_string) && trim($address_as_string) != "") {
                            if (!isset($addresses[$content_id]) || !is_array($addresses[$content_id])) {
                                $addresses[$content_id] = array();
                            }
                            $addresses[$content_id][] = $address_as_string;
                        }
                    }
                }
                $per_published_content_type_address_counter = $per_published_content_type_address_counter + count($addresses[$content_id]);
                $extracted[$post->ID] = $post;
            }
        }

        $function_used = "SQL query";
        return array("extracted" => $extracted, "query" => array("wp_count_posts" => $total_published, $function_used => count($posts), $content_type."s_with_shortcodes" => count($extracted), $content_type."_addresses_total" => $per_published_content_type_address_counter, "addresses" => $addresses));
    }
endif;


if ( !function_exists('sgmp_on_activate_hook') ):
    function sgmp_on_activate_hook()  {
        sgmp_clear_cached_map_data(SGMP_ALL_MAP_CACHED_CONSTANTS_PREFIX);

        update_option(SGMP_DB_GEOMASHUP_DATA_CACHE, "");
        update_option(SGMP_DB_GEOMASHUP_DATA_CACHE_TIME, "");
        update_option(SGMP_INITIAL_WARNING, 1);
    }
endif;

if ( !function_exists('sgmp_clear_cached_map_data') ):
    function sgmp_clear_cached_map_data($prefix_constant)  {
        // Remove cache of posts, pages and widgets
        global $wpdb;
        $options_table = $wpdb->options;
        $wpdb->query( "DELETE FROM ".$options_table." WHERE option_name LIKE '".$prefix_constant."%';" );
    }
endif;


if ( !function_exists('process_collection_of_contents') ):
    function process_collection_of_contents($published_content_list)  {

        $db_markers = array();
        foreach($published_content_list as $post) {

            $post_content = $post->post_content;
            $extracted = extract_locations_from_post_content($post_content);

            $bad_entities = array("&quot;", "&#039;", "'", "\"");
            if (count($extracted) > 0) {

                $marker = array();
                $post_title = $post->post_title;
                $post_title = strip_tags($post_title);
                $post_title = str_replace($bad_entities, "", $post_title);
                $post_title = preg_replace("/\r\n|\n\r|\n/", " ", $post_title);
                $marker[$post->ID]['markers'] = $extracted;
                $marker[$post->ID]['title'] = $post_title;
                $marker[$post->ID]['permalink'] = get_permalink($post->ID);
                $marker[$post->ID]['excerpt'] = '';

                $clean = "";
                if (isset($post->post_excerpt) && trim($post->post_excerpt) != '') {
                    $clean = clean_excerpt($post->post_excerpt);
                } else {
                    $clean = clean_excerpt($post_content);
                }
                if ( trim($clean) != '' ) {
                    $excerpt = mb_substr($clean, 0, 175);
                    $marker[$post->ID]['excerpt'] = $excerpt."..";
                }
                $db_markers[] = $marker[$post->ID];
            }
        }
        return $db_markers;

    }
endif;



if ( !function_exists('clean_excerpt') ):
    function clean_excerpt($content)  {

        if (!isset($content) || $content == "") {
            return $content;
        }
        $bad_entities = array("&quot;", "&#039;", "'", "\"");
        $content = strip_tags($content);
        $content = preg_replace ("/<[^>]*>/", "", $content);
        $content = preg_replace ("/\[[^\]]*\]/", "", $content);
        $content = preg_replace("/\r\n|\n\r|\n/", " ", $content);
        $content = str_replace($bad_entities, "", $content);
        return trim($content);
    }
endif;


if ( !function_exists('extract_locations_from_post_content') ):
    function extract_locations_from_post_content($post_content)  {

        $arr = array();
        if (isset($post_content) && $post_content != '') {

            if (strpos($post_content, "addresscontent") !== false) {
                $pattern = "/addresscontent=\"(.*?)\"/";
                $found = find_for_regex($pattern, $post_content); 

                if (count($found) > 0) {
                    $arr = array_merge($arr, $found);
                }
            }

            if (strpos($post_content, "addmarkerlist") !== false) {

                $pattern = "/addmarkerlist=\"(.*?)\"/";
                $washed_post_content = str_replace(array("\r\n", "\r", "\n"), " ", $post_content);
                $found = find_for_regex($pattern, $washed_post_content);

                if (count($found) > 0) {
                    $arr = array_merge($arr, $found);
                }
            }

            if (strpos($post_content, "latitude") !== false) {

                $pattern = "/latitude=\"(.*?)\"(\s{0,})longitude=\"(.*?)\"/";

                preg_match_all($pattern, $post_content, $matches);

                if (is_array($matches)) {

                    if (isset($matches[1]) && is_array($matches[1]) &&
                        isset($matches[3]) && is_array($matches[3])) {

                        for ($idx = 0; $idx < sizeof($matches[1]); $idx++) {

                            if (isset($matches[1][$idx]) && isset($matches[3][$idx])) {
                                $lat = $matches[1][$idx];
                                $lng = $matches[3][$idx];

                                if (trim($lat) != "0" && trim($lng) != "0") {
                                    $coord = trim($lat).",".trim($lng);
                                    $arr[$coord] = $coord;
                                }
                            }
                        }
                    }
                }
            }

            $arr = array_unique($arr);
        }

        return $arr;
    }

endif;


if ( !function_exists('find_for_regex') ):

    function find_for_regex($pattern, $post_content)  {
        $arr = array();
        preg_match_all($pattern, $post_content, $matches);

        if (is_array($matches)) {
            if (isset($matches[1]) && is_array($matches[1])) {

                foreach($matches[1] as $key => $value) {
                    if (isset($value) && trim($value) != "") {

                        if (strpos($value, "|") !== false) {
                            $value_arr = explode("|", $value);
                            foreach ($value_arr as $value) {
                                $arr[$value] = $value;
                            }
                        } else {
                            $arr[$value] = $value;
                        }
                    }
                }
            }
        }

        return $arr;
    }
endif;

if ( !function_exists('sgmp_save_post_hook') ):
    function sgmp_save_post_hook($postID)  {
        sgmp_clear_cached_map_data(SGMP_MAP_CACHE_POST_PREFIX.$postID);
        sgmp_clear_cached_map_data(SGMP_MAP_CACHE_POST_TIME_PREFIX.$postID);

        sgmp_clear_cached_map_data(SGMP_MAP_CACHE_CUSTOM_TYPE_PREFIX.$postID);
        sgmp_clear_cached_map_data(SGMP_MAP_CACHE_CUSTOM_TYPE_TIME_PREFIX.$postID);

        update_option(SGMP_DB_GEOMASHUP_DATA_CACHE, "");
        update_option(SGMP_DB_GEOMASHUP_DATA_CACHE_TIME, "");
    }
endif;

if ( !function_exists('sgmp_save_page_hook') ):
    function sgmp_save_page_hook($pageID)  {
        sgmp_clear_cached_map_data(SGMP_MAP_CACHE_PAGE_PREFIX.$pageID);
        sgmp_clear_cached_map_data(SGMP_MAP_CACHE_PAGE_TIME_PREFIX.$pageID);

        sgmp_clear_cached_map_data(SGMP_MAP_CACHE_CUSTOM_TYPE_PREFIX.$pageID);
        sgmp_clear_cached_map_data(SGMP_MAP_CACHE_CUSTOM_TYPE_TIME_PREFIX.$pageID);

        update_option(SGMP_DB_GEOMASHUP_DATA_CACHE, "");
        update_option(SGMP_DB_GEOMASHUP_DATA_CACHE_TIME, "");
    }
endif;

if ( !function_exists('sgmp_publish_post_hook') ):
    function sgmp_publish_post_hook($postID)  {
        sgmp_clear_cached_map_data(SGMP_MAP_CACHE_POST_PREFIX.$postID);
        sgmp_clear_cached_map_data(SGMP_MAP_CACHE_POST_TIME_PREFIX.$postID);

        sgmp_clear_cached_map_data(SGMP_MAP_CACHE_CUSTOM_TYPE_PREFIX.$postID);
        sgmp_clear_cached_map_data(SGMP_MAP_CACHE_CUSTOM_TYPE_TIME_PREFIX.$postID);

        update_option(SGMP_DB_GEOMASHUP_DATA_CACHE, "");
        update_option(SGMP_DB_GEOMASHUP_DATA_CACHE_TIME, "");
    }
endif;

if ( !function_exists('sgmp_publish_page_hook') ):
    function sgmp_publish_page_hook($pageID)  {
        sgmp_clear_cached_map_data(SGMP_MAP_CACHE_PAGE_PREFIX.$pageID);
        sgmp_clear_cached_map_data(SGMP_MAP_CACHE_PAGE_TIME_PREFIX.$pageID);

        sgmp_clear_cached_map_data(SGMP_MAP_CACHE_CUSTOM_TYPE_PREFIX.$pageID);
        sgmp_clear_cached_map_data(SGMP_MAP_CACHE_CUSTOM_TYPE_TIME_PREFIX.$pageID);

        update_option(SGMP_DB_GEOMASHUP_DATA_CACHE, "");
        update_option(SGMP_DB_GEOMASHUP_DATA_CACHE_TIME, "");
    }
endif;

if ( !function_exists('sgmp_deleted_post_hook') ):
    function sgmp_deleted_post_hook($postID)  {
        sgmp_clear_cached_map_data(SGMP_MAP_CACHE_POST_PREFIX.$postID);
        sgmp_clear_cached_map_data(SGMP_MAP_CACHE_POST_TIME_PREFIX.$postID);

        sgmp_clear_cached_map_data(SGMP_MAP_CACHE_CUSTOM_TYPE_PREFIX.$postID);
        sgmp_clear_cached_map_data(SGMP_MAP_CACHE_CUSTOM_TYPE_TIME_PREFIX.$postID);

        update_option(SGMP_DB_GEOMASHUP_DATA_CACHE, "");
        update_option(SGMP_DB_GEOMASHUP_DATA_CACHE_TIME, "");
    }
endif;

if ( !function_exists('sgmp_deleted_page_hook') ):
    function sgmp_deleted_page_hook($pageID)  {
        sgmp_clear_cached_map_data(SGMP_MAP_CACHE_PAGE_PREFIX.$pageID);
        sgmp_clear_cached_map_data(SGMP_MAP_CACHE_PAGE_TIME_PREFIX.$pageID);

        sgmp_clear_cached_map_data(SGMP_MAP_CACHE_CUSTOM_TYPE_PREFIX.$pageID);
        sgmp_clear_cached_map_data(SGMP_MAP_CACHE_CUSTOM_TYPE_TIME_PREFIX.$pageID);

        update_option(SGMP_DB_GEOMASHUP_DATA_CACHE, "");
        update_option(SGMP_DB_GEOMASHUP_DATA_CACHE_TIME, "");
    }
endif;

if ( !function_exists('sgmp_publish_to_draft_hook') ):
    function sgmp_publish_to_draft_hook($obj)  {
        if (isset($obj)) {
            $post_page_type = $obj->post_type;
            $post_page_id = $obj->ID;
            $post_db_cache_key = SGMP_MAP_CACHE_POST_PREFIX.$post_page_id;
            $post_db_cache_time_key = SGMP_MAP_CACHE_POST_TIME_PREFIX.$post_page_id;

            $page_db_cache_key = SGMP_MAP_CACHE_PAGE_PREFIX.$post_page_id;
            $page_db_cache_time_key = SGMP_MAP_CACHE_PAGE_TIME_PREFIX.$post_page_id;

            $customtype_db_cache_key = SGMP_MAP_CACHE_CUSTOM_TYPE_PREFIX.$post_page_id;
            $customtype_db_cache_time_key = SGMP_MAP_CACHE_CUSTOM_TYPE_TIME_PREFIX.$post_page_id;

            if ($post_page_type == "post") {
                sgmp_clear_cached_map_data($post_db_cache_key);
                sgmp_clear_cached_map_data($post_db_cache_time_key);
            } else if ($post_page_type == "page") {
                sgmp_clear_cached_map_data($page_db_cache_key);
                sgmp_clear_cached_map_data($page_db_cache_time_key);
            } else {
                sgmp_clear_cached_map_data($customtype_db_cache_key);
                sgmp_clear_cached_map_data($customtype_db_cache_time_key);
            }
            update_option(SGMP_DB_GEOMASHUP_DATA_CACHE, "");
            update_option(SGMP_DB_GEOMASHUP_DATA_CACHE_TIME, "");
        }
    }
endif;

if ( !function_exists('sgmp_get_post_page_cached_markerlist') ):
    function sgmp_get_post_page_cached_markerlist($shortcodeid, $post_page_id, $post_page_type, $markerlist)  {

        $post_db_cache_key = SGMP_MAP_CACHE_POST_PREFIX.$post_page_id."_".$shortcodeid;
        $post_db_cache_time_key = SGMP_MAP_CACHE_POST_TIME_PREFIX.$post_page_id."_".$shortcodeid;

        $page_db_cache_key = SGMP_MAP_CACHE_PAGE_PREFIX.$post_page_id."_".$shortcodeid;
        $page_db_cache_time_key = SGMP_MAP_CACHE_PAGE_TIME_PREFIX.$post_page_id."_".$shortcodeid;

        $customtype_db_cache_key = SGMP_MAP_CACHE_CUSTOM_TYPE_PREFIX.$post_page_id."_".$shortcodeid;
        $customtype_db_cache_time_key = SGMP_MAP_CACHE_CUSTOM_TYPE_TIME_PREFIX.$post_page_id."_".$shortcodeid;

        $cached_validated_addresses = "";
        $cached_map_data_time = "";
        if ($post_page_type == "post") {
            $cached_validated_addresses = get_option($post_db_cache_key);
            $cached_map_data_time = get_option($post_db_cache_time_key);
        } else if ($post_page_type == "page") {
            $cached_validated_addresses = get_option($page_db_cache_key);
            $cached_map_data_time = get_option($page_db_cache_time_key);
        } else {
            $cached_validated_addresses = get_option($customtype_db_cache_key);
            $cached_map_data_time = get_option($customtype_db_cache_time_key);
        }

        if (isset($cached_validated_addresses) && trim($cached_validated_addresses) != "") {
            return array("data" => $cached_validated_addresses, "debug" => array("shortcodeid" => $shortcodeid, "post_id"=>$post_page_id, "post_type"=>$post_page_type, "state" => "cached", "since" => $cached_map_data_time));
        }

        $validated_addresses = sgmp_do_serverside_address_validation_2($markerlist);
        if ($post_page_type == "post") {
            update_option($post_db_cache_key, $validated_addresses);
            update_option($post_db_cache_time_key, time());
        } else if ($post_page_type == "page") {
            update_option($page_db_cache_key, $validated_addresses);
            update_option($page_db_cache_time_key, time());
        }

        return array("data" => $validated_addresses, "debug" => array("shortcodeid" => $shortcodeid, "post_id"=>$post_page_id, "post_type"=>$post_page_type, "state" => "fresh", "since" => time()));
    }
endif;


if ( !function_exists('make_marker_geo_mashup_2') ):

    function make_marker_geo_mashup_2()   {

        $cached_geomashup_json = get_option(SGMP_DB_GEOMASHUP_DATA_CACHE);
        if (isset($cached_geomashup_json) && trim($cached_geomashup_json) != "") {
            $cached_geomashup_json = str_replace("\\\"", "\"", $cached_geomashup_json);
            $cache_time = get_option(SGMP_DB_GEOMASHUP_DATA_CACHE_TIME);
            return array("data" => $cached_geomashup_json, "debug" => array("state" => "cached", "since" => $cache_time));
        }

        $query_debug_data = array();
        $post_data = extract_published_content_containing_shortcode("post");
        $query_debug_data["post"] = $post_data["query"];

        $page_data = extract_published_content_containing_shortcode("page");
        $query_debug_data["page"] = $page_data["query"];

        $custom_data = array();
        $custom_data["extracted"] = array();
        $custom_data["query"] = array();
        $custom_post_types = get_option(SGMP_DB_SETTINGS_CUSTOM_POST_TYPES);
        if (isset($custom_post_types) && trim($custom_post_types) != "") {
            $custom_post_types_arr = explode(",", $custom_post_types);
            array_walk($custom_post_types_arr, 'trim_to_lower_value');
            $custom_post_types = implode("','", $custom_post_types_arr);
            $custom_data = extract_published_content_containing_shortcode($custom_post_types);
            $query_debug_data["custom"] = $custom_data["query"];
        }

        $extracted_published_markers =  array_merge(process_collection_of_contents($post_data["extracted"]),
                                                    process_collection_of_contents($page_data["extracted"]),
                                                    process_collection_of_contents($custom_data["extracted"]));

        if (is_array($extracted_published_markers) && count($extracted_published_markers) > 0) {

            $filtered = array();
            $duplicates = array();
            foreach($extracted_published_markers as $post_data) {

                $title = $post_data['title'];
                $permalink = $post_data['permalink'];
                $markers = $post_data['markers'];
                $excerpt = $post_data['excerpt'];

                $bad_entities = array("&quot;", "&#039;", "'", "\"");
                $bad_characters = array("\r\n", "\r", "\n", "<br>", "<BR>", "<br />", "<BR />");
                foreach($markers as $full_loc) {

                    $tobe_filtered_loc = $full_loc;
                    if (strpos($full_loc, SGMP_SEP) !== false) {
                        $loc_arr = explode(SGMP_SEP, $full_loc);
                        $tobe_filtered_loc = $loc_arr[0];
                    }
                    $tobe_filtered_loc = str_replace($bad_entities, "", str_replace($bad_characters, " ", $tobe_filtered_loc));
                    if (!isset($filtered[$tobe_filtered_loc])) {
                        $validated_addresses = sgmp_do_serverside_address_validation_2($full_loc);
                        $filtered[$tobe_filtered_loc]['validated_address_csv_data'] = $validated_addresses;
                        $filtered[$tobe_filtered_loc]['permalink'] = $permalink;

                        if (isset($title) &&  trim($title) != "")  {
                            $title = str_replace($bad_entities, "", $title);
                            $title = trim($title);
                        }
                        $filtered[$tobe_filtered_loc]['title'] = $title;
                        if (isset($excerpt) &&  trim($excerpt) != "") {
                            $excerpt = str_replace($bad_entities, "", str_replace($bad_characters, " ", $excerpt));
                            $excerpt = trim($excerpt);
                        }

                        $filtered[$tobe_filtered_loc]['excerpt'] = $excerpt;
                    } else {
                        if (isset($duplicates[$tobe_filtered_loc]) && is_numeric($duplicates[$tobe_filtered_loc])) {
                            $duplicates[$tobe_filtered_loc]++;
                        } else {
                            $duplicates[$tobe_filtered_loc] = 1;
                        }
                    }
                }
            }

            $debug_data = array("since" => time(), "query" => $query_debug_data, "duplicate_addresses_extracted" => $duplicates);
            $filtered["live_debug"] = $debug_data;
            $geomashup_json = json_encode($filtered);
            update_option(SGMP_DB_GEOMASHUP_DATA_CACHE, $geomashup_json);
            update_option(SGMP_DB_GEOMASHUP_DATA_CACHE_TIME, time());

            $debug_data["state"] = "fresh";
            return array("data" => $geomashup_json, "debug" => $debug_data);
        }
    }
endif;


if ( !function_exists('sgmp_do_serverside_address_validation_2') ):
    function sgmp_do_serverside_address_validation_2($markers_data) {

        $bad_entities = array("&quot;", "&#039;", "'", "\"");
        $bad_characters = array("\r\n", "\r", "\n", "<br>", "<BR>", "<br />", "<BR />");
        $markers_data = str_replace($bad_entities, "", str_replace($bad_characters, " ", $markers_data));

        $splitted_marker_list = explode("|", $markers_data);
        $validated_addresses = array();
        foreach($splitted_marker_list as $marker_data_with_sgmp_sep) {

            $marker_data_segments = explode(SGMP_SEP, $marker_data_with_sgmp_sep);
            $address = $marker_data_segments[0];

            if (!isset($address) || trim($address) == "") {
                continue;
            }

            $icon = isset($marker_data_segments[1]) && trim($marker_data_segments[1]) != "" ? SGMP_SEP.$marker_data_segments[1] : SGMP_SEP."1-default.png";
            $description = isset($marker_data_segments[2]) && trim($marker_data_segments[2]) != "" ? SGMP_SEP.$marker_data_segments[2] : SGMP_SEP.SGMP_NO_BUBBLE_DESC;


            # we use the general DMS to decimal conversion routine
            # from below to check for format. If a positional format is
            # recognized, then we use the decimal form, otherwise we request
            # a revalidation
            # We do this only under the condition that the input only
            # contains exactly one comma "," which is used to separate lat/lon
            $latlon = preg_split("/[,;]/", $address);
            if (count($latlon) == 2) {
                $latdms = sgmp_convert_dms_to_decimal($latlon[0]);
                $londms = sgmp_convert_dms_to_decimal($latlon[1]);
            }
            # if we have been successful, use the decimal representation,
            # otherwise force a revalidation
            if (isset($latdms) && isset($londms) && trim($latdms) != "" && trim($londms) != "") {
                $validated_addresses[] = $address.$icon.$description.SGMP_SEP."$latdms,$londms";
            } else {
                $validated_addresses[] = $address.$icon.$description.SGMP_SEP.SGMP_GEO_VALIDATION_CLIENT_REVALIDATE;
            }

            # old code
            # if (preg_match('/[^0-9,\s{1,}\-\.+]/', $address) !== 0) {
            #     $validated_addresses[] = $address.$icon.$description.SGMP_SEP.SGMP_GEO_VALIDATION_CLIENT_REVALIDATE;
            # } else {
            #     $validated_addresses[] = $address.$icon.$description.SGMP_SEP.$address;
            # }
        }
        return implode("|", $validated_addresses);
    }
endif;

if ( !function_exists('sgmp_convert_dms_to_decimal') ):
    function sgmp_convert_dms_to_decimal($latlng) {
        /*
         * Convert DMS (degrees / minutes / seconds) to decimal degrees
         *
         * adapted from https://github.com/prairiewest/PHPconvertDMSToDecimal
         * Todd Trann
         * May 22, 2015
         *
         * MIT license
         */
        $valid = false;
        $decimal_degrees = 0;
        $degrees = 0; $minutes = 0; $seconds = 0; $direction = 1;

        // Determine if there are extra periods in the input string
        $num_periods = substr_count($latlng, '.');
        if ($num_periods > 1) {
            $temp = preg_replace('/\./', ' ', $latlng, $num_periods - 1); // replace all but last period with delimiter
            $temp = trim(preg_replace('/[a-zA-Z]/','',$temp)); // when counting chunks we only want numbers
            $chunk_count = count(explode(" ",$temp));
            if ($chunk_count > 2) {
                $latlng = preg_replace('/\./', ' ', $latlng, $num_periods - 1); // remove last period
            } else {
                $latlng = str_replace("."," ",$latlng); // remove all periods, not enough chunks left by keeping last one
            }
        }

        // Remove unneeded characters
        $latlng = trim($latlng);
        # NP changes to original version
        # - add case for °
        # - replace ° and º with spaces instead
        $latlng = str_replace("º"," ",$latlng);
        $latlng = str_replace("°"," ",$latlng);
        $latlng = str_replace("'","",$latlng);
        $latlng = str_replace("\"","",$latlng);
        $latlng = substr($latlng,0,1) . str_replace('-', ' ', substr($latlng,1)); // remove all but first dash

        if ($latlng != "") {
            // DMS with the direction at the start of the string
            if (preg_match("/^([nsewNSEW]?)\s*(\d{1,3})\s+(\d{1,3})\s+(\d+\.?\d*)$/",$latlng,$matches)) {
                $valid = true;
                $degrees = intval($matches[2]);
                $minutes = intval($matches[3]);
                $seconds = floatval($matches[4]);
                if (strtoupper($matches[1]) == "S" || strtoupper($matches[1]) == "W")
                    $direction = -1;
            }
            // DMS with the direction at the end of the string
            elseif (preg_match("/^(-?\d{1,3})\s+(\d{1,3})\s+(\d+(?:\.\d+)?)\s*([nsewNSEW]?)$/",$latlng,$matches)) {
                $valid = true;
                $degrees = intval($matches[1]);
                $minutes = intval($matches[2]);
                $seconds = floatval($matches[3]);
                if (strtoupper($matches[4]) == "S" || strtoupper($matches[4]) == "W" || $degrees < 0) {
                    $direction = -1;
                    $degrees = abs($degrees);
                }
            }
            if ($valid) {
                // A match was found, do the calculation
                $decimal_degrees = ($degrees + ($minutes / 60) + ($seconds / 3600)) * $direction;
            } else {
                // Decimal degrees with a direction at the start of the string
                if (preg_match("/^([nsewNSEW]?)\s*(\d+(?:\.\d+)?)$/",$latlng,$matches)) {
                    $valid = true;
                    if (strtoupper($matches[1]) == "S" || strtoupper($matches[1]) == "W")
                        $direction = -1;
                    $decimal_degrees = $matches[2] * $direction;
                }
                // Decimal degrees with a direction at the end of the string
                elseif (preg_match("/^(-?\d+(?:\.\d+)?)\s*([nsewNSEW]?)$/",$latlng,$matches)) {
                    $valid = true;
                    if (strtoupper($matches[2]) == "S" || strtoupper($matches[2]) == "W" || $degrees < 0) {
                        $direction = -1;
                        $degrees = abs($degrees);
                    }
                    $decimal_degrees = $matches[1] * $direction;
                }
            }
        }
        if ($valid) {
            return $decimal_degrees;
        } else {
            return "";
        }
    }
endif;

# vim:set expandtab tabstop=4 shiftwidth=4 autoindent smartindent: #
?>
