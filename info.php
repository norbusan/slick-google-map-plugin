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
$sgmp_admin_notice = isset($_GET['admin_notice']) ? $_GET['admin_notice'] : '';
$sgmp_plugin_notice = isset($_GET['plugin_notice']) ? $_GET['plugin_notice'] : '';
$sgmp_metabox_notice = isset($_GET['metabox_notice']) ? $_GET['metabox_notice'] : '';
$sgmp_options = get_option('sgmp_options');

if ($sgmp_admin_notice != NULL) {
	$sgmp_options['admin_notice'] = 'hide';
	update_option('sgmp_options',$sgmp_options);
	echo '<div class="updated" style="padding:10px;margin:10px 0;">"Simple Google Map Plugin" options have been updated!</div>';
	echo '<script type="text/javascript">jQuery(document).ready(function($) { $("#sgmp_admin_notice").hide(); });</script>';
}
if ($sgmp_plugin_notice != NULL) {
	$sgmp_options['plugin_notice'] = 'hide';
	update_option('sgmp_options',$sgmp_options);
	echo '<div class="updated" style="padding:10px;margin:10px 0;">"Simple Google Map Plugin" options have been updated!</div>';
}
if ($sgmp_metabox_notice != NULL) {
	$sgmp_options['metabox_notice'] = 'hide';
	update_option('sgmp_options',$sgmp_options);
	echo '<div class="updated" style="padding:10px;margin:10px 0;">"Simple Google Map Plugin" options have been updated!</div>';
}

?>
