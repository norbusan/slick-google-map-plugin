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

if ( !function_exists('sgmp_enqueue_head_scripts') ):
        function sgmp_enqueue_head_scripts()  {
				wp_enqueue_script( array ( 'jquery' ) );
        }
endif;


if ( !function_exists('sgmp_google_map_admin_add_style') ):
        function sgmp_google_map_admin_add_style()  {
            wp_enqueue_style('slick-google-map-style', SGMP_PLUGIN_CSS . '/sgmp.admin.css', false, SGMP_VERSION, "screen");
        }
endif;


if ( !function_exists('sgmp_google_map_admin_add_script') ):
		function sgmp_google_map_admin_add_script()  {

            if (sgmp_should_load_admin_scripts()) {
                $whitelist = array('localhost', '127.0.0.1');
                wp_enqueue_script('sgmp-jquery-tools-tooltip', SGMP_PLUGIN_JS .'/jquery.tools.tooltip.min.js', array('jquery'), '1.2.5.a', true);
                $minified = ".min";
                if (in_array($_SERVER['HTTP_HOST'], $whitelist)) {
                    $minified = "";
                }
                wp_enqueue_script('sgmp-jquery-tokeninput', SGMP_PLUGIN_JS. '/sgmp.tokeninput'.$minified.'.js', array('jquery'), SGMP_VERSION, true);
                /* temp fix - will probably be fixed with https://core.trac.wordpress.org/ticket/29520#comment:17
				wp_enqueue_script('slick-google-map-plugin', SGMP_PLUGIN_JS. '/sgmp.admin'.$minified.'.js', array('jquery', 'media', 'wp-ajax-response'), SGMP_VERSION, true);
				*/
				wp_enqueue_script('slick-google-map-plugin', SGMP_PLUGIN_JS. '/sgmp.admin'.$minified.'.js', array('jquery', 'wp-ajax-response'), SGMP_VERSION, true);
            }

            if (sgmp_should_find_posts_scripts()) {
                add_action('admin_footer', 'find_posts_div', 99);
            }
		}
endif;

if ( !function_exists('sgmp_insert_shortcode_to_post_action_callback') ):
    function sgmp_insert_shortcode_to_post_action_callback() {
        //check_ajax_referer( "sgmp_insert_shortcode_to_post_action", "_ajax_nonce");

        if (isset($_POST['postId']) && isset($_POST['shortcodeName']))  {
            $post = get_post($_POST['postId']);
            $post_content = $post->post_content;

            $persisted_shortcodes_json = get_option(SGMP_PERSISTED_SHORTCODES);
            if (isset($persisted_shortcodes_json) && trim($persisted_shortcodes_json) != "") {
                $persisted_shortcodes = json_decode($persisted_shortcodes_json, true);

                if (is_array($persisted_shortcodes) && !empty($persisted_shortcodes)) {

                    if (isset($persisted_shortcodes[$_POST['shortcodeName']])) {
                        $shortcode = $persisted_shortcodes[$_POST['shortcodeName']];

                        if (is_array($shortcode)) {
                            $shortcode_id = substr(md5(rand()), 0, 10);
                            $raw_code = $shortcode['code'];
                            $cleaned_code = str_replace("TO_BE_GENERATED", $shortcode_id, $raw_code);
                            $updated_post_attribs = array('ID' => $_POST['postId'], 'post_content' => $post_content."<br />".$cleaned_code);

                            wp_update_post( $updated_post_attribs );
                            echo isset($post->post_title) && trim($post->post_title) != "" ? $post->post_title : "Titleless";
                        }
                    }
                }
            }
        }
        die();
    }
endif;


if ( !function_exists('sgmp_should_find_posts_scripts') ):
    function sgmp_should_find_posts_scripts()  {
        $admin_pages = array('sgmp-saved-shortcodes');
        $plugin_admin_page = isset($_REQUEST['page']) && trim($_REQUEST['page']) != "" ? $_REQUEST['page'] : "";

        return in_array($plugin_admin_page, $admin_pages);
    }
endif;



if ( !function_exists('sgmp_google_map_tab_script') ):
    	function sgmp_google_map_tab_script()  {
            wp_enqueue_script('sgmp-jquery-tools-tabs', SGMP_PLUGIN_JS .'/jquery.tools.tabs.min.js', array('jquery'), '1.2.5', true);
        }
endif;


if ( !function_exists('sgmp_google_map_register_scripts') ):
		function sgmp_google_map_register_scripts()  {
			$whitelist = array('localhost', '127.0.0.1');
			$minified = ".min";
			if (in_array($_SERVER['HTTP_HOST'], $whitelist)) {
				$minified = "";
			}
			wp_register_script('sgmp-google-map-orchestrator-framework', SGMP_PLUGIN_JS. '/sgmp.framework'.$minified.'.js', array(), SGMP_VERSION, true);

			$api = SGMP_GOOGLE_API_URL;
			if (is_ssl()) {
				$api = SGMP_GOOGLE_API_URL_SSL;
			}
			wp_register_script('sgmp-google-map-jsapi', $api, array(), false, true);
		}
endif;


if ( !function_exists('sgmp_google_map_init_global_admin_html_object') ):
		function sgmp_google_map_init_global_admin_html_object()  {

			if (is_admin()) {
				echo "<object id='global-data-placeholder' class='sgmp-data-placeholder'>".PHP_EOL;
				echo "    <param id='sep' name='sep' value='".SGMP_SEP."' />".PHP_EOL;
				echo "    <param id='ajaxurl' name='ajaxurl' value='".admin_url('admin-ajax.php')."' />".PHP_EOL;
                echo "    <param id='version' name='version' value='".SGMP_VERSION."' />".PHP_EOL;
                $persisted_shortcodes_json = get_option(SGMP_PERSISTED_SHORTCODES);
                if (isset($persisted_shortcodes_json) && trim($persisted_shortcodes_json) != "" && is_array(json_decode($persisted_shortcodes_json, true))) {
                    echo "    <param id='shortcodes' name='shortcodes' value='".$persisted_shortcodes_json."' />".PHP_EOL;
                } else {
                    echo "    <param id='shortcodes' name='shortcodes' value='".json_encode(array())."' />".PHP_EOL;
                }
                echo "    <param id='assets' name='assets' value='".SGMP_PLUGIN_ASSETS_URI."' />".PHP_EOL;
				echo "    <param id='customMarkersUri' name='customMarkersUri' value='".SGMP_PLUGIN_IMAGES."/markers/' />".PHP_EOL;
				echo "    <param id='defaultLocationText' name='defaultLocationText' value='Enter marker destination address or latitude,longitude here (required)' />".PHP_EOL;
				echo "    <param id='defaultBubbleText' name='defaultBubbleText' value='Enter marker info bubble text here (optional)' />".PHP_EOL;
				echo "</object> ".PHP_EOL;
			}
		}
endif;

if ( !function_exists('sgmp_generate_global_options') ):
    function sgmp_generate_global_options()  {

        $tokens_with_values = array();
        $tokens_with_values['LABEL_KML'] = '[TITLE] [MSG] ([STATUS])';
        $tokens_with_values['LABEL_DOCINVALID_KML'] = __('The KML file is not a valid KML, KMZ or GeoRSS document.',SGMP_NAME);
        $tokens_with_values['LABEL_FETCHERROR_KML'] = __('The KML file could not be fetched.',SGMP_NAME);
        $tokens_with_values['LABEL_LIMITS_KML'] = __('The KML file exceeds the feature limits of KmlLayer.',SGMP_NAME);
        $tokens_with_values['LABEL_NOTFOUND_KML'] = __('The KML file could not be found. Most likely it is an invalid URL, or the document is not publicly available.',SGMP_NAME);
        $tokens_with_values['LABEL_REQUESTINVALID_KML'] = __('The KmlLayer is invalid.',SGMP_NAME);
        $tokens_with_values['LABEL_TIMEDOUT_KML'] = __('The KML file could not be loaded within a reasonable amount of time.',SGMP_NAME);
        $tokens_with_values['LABEL_TOOLARGE_KML'] = __('The KML file exceeds the file size limits of KmlLayer.',SGMP_NAME);
        $tokens_with_values['LABEL_UNKNOWN_KML'] = __('The KML file failed to load for an unknown reason.',SGMP_NAME);

        $tokens_with_values = array_map('sgmp_escape_json',$tokens_with_values);
        $global_error_messages_json_template = sgmp_render_template_with_values($tokens_with_values, SGMP_HTML_TEMPLATE_GLOBAL_ERROR_MESSAGES);

        $tokens_with_values = array();
        $tokens_with_values['LABEL_STREETVIEW'] = __('Street View',SGMP_NAME);
        $tokens_with_values['LABEL_ADDRESS'] = __('Address',SGMP_NAME);
        $tokens_with_values['LABEL_DIRECTIONS'] = __('Directions',SGMP_NAME);
        $tokens_with_values['LABEL_TOHERE'] = __('To here',SGMP_NAME);
        $tokens_with_values['LABEL_FROMHERE'] = __('From here',SGMP_NAME);

        $tokens_with_values = array_map('sgmp_escape_json',$tokens_with_values);
        $info_bubble_translated_template = sgmp_render_template_with_values($tokens_with_values, SGMP_HTML_TEMPLATE_INFO_BUBBLE);

        global $sgmp_global_map_language;
        $sgmp_global_map_language = (isset($sgmp_global_map_language) && $sgmp_global_map_language != '') ? $sgmp_global_map_language : "en";

        $errorArray = json_decode($global_error_messages_json_template, true);
        $translationArray = json_decode($info_bubble_translated_template, true);

        $properties = array();
        $properties['ajaxurl'] = admin_url('admin-ajax.php');
        $properties['noBubbleDescriptionProvided'] = SGMP_NO_BUBBLE_DESC;
        $properties['geoValidationClientRevalidate'] = SGMP_GEO_VALIDATION_CLIENT_REVALIDATE;
        $properties['cssHref'] = SGMP_PLUGIN_URI."style.css?ver=".SGMP_VERSION;
        $properties['language'] = $sgmp_global_map_language;
        $properties['customMarkersUri'] = SGMP_PLUGIN_IMAGES."/markers/";
        foreach($errorArray as $name => $value) {
            $properties[$name] = $value;
        }
        foreach($translationArray as $name => $value) {
            $properties[$name] = $value;
        }
        $setting_map_should_fill_viewport = get_option(SGMP_DB_SETTINGS_MAP_SHOULD_FILL_VIEWPORT);
        if (isset($setting_map_should_fill_viewport) && $setting_map_should_fill_viewport == "true") {
            $properties['mapFillViewport'] = "true";
        } else {
            $properties['mapFillViewport'] = "false";
        }
        $properties[SGMP_TIMESTAMP] = wp_create_nonce(SGMP_AJAX_CACHE_MAP_ACTION);
        $properties['ajaxCacheMapAction'] = SGMP_AJAX_CACHE_MAP_ACTION;
        $properties['sep'] = SGMP_SEP;

        echo "<script type='text/javascript'>".PHP_EOL;
        echo "/* <![CDATA[ */".PHP_EOL;
        echo "// Slick Google Map plugin v".SGMP_VERSION.PHP_EOL;
        echo "var SGMPGlobal = ".json_encode($properties).PHP_EOL;
        echo "/* ]]> */".PHP_EOL;
        echo "</script>".PHP_EOL;
    }
endif;

if ( !function_exists('sgmp_escape_json') ):
function sgmp_escape_json( $encode ) {
    return str_replace( array("'",'"','&') , array('\u0027','\u0022','\u0026') , $encode );
}
endif;

?>
