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

if ( !function_exists('sgmp_render_shortcode_builder_form') ):
function sgmp_render_shortcode_builder_form() {

    $settings = array();
    $json_string = file_get_contents(SGMP_PLUGIN_DATA_DIR."/".SGMP_JSON_DATA_HTML_ELEMENTS_FORM_PARAMS);
    $parsed_json = json_decode($json_string, true);

    if (is_array($parsed_json)) {
        foreach ($parsed_json as $data_chunk) {
            sgmp_set_values_for_html_rendering($settings, $data_chunk);
        }
    }

    $template_values = sgmp_build_template_values($settings);
    $template_values['SHORTCODEBUILDER_FORM_TITLE'] = "";
    $template_values['SHORTCODEBUILDER_HTML_FORM'] = "";
    $map_configuration_template = sgmp_render_template_with_values($template_values, SGMP_HTML_TEMPLATE_MAP_CONFIGURATION_FORM);

    $tokens_with_values = array("MAP_CONFIGURATION_FORM_TOKEN" => $map_configuration_template);
    echo sgmp_render_template_with_values($tokens_with_values, SGMP_HTML_TEMPLATE_MAP_SHORTCODE_BUILDER_METABOX);
}
endif;

?>
