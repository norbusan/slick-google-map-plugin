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

if ( !function_exists('sgmp_admin_bar_menu') ):
    function sgmp_admin_bar_menu() {

        global $wp_admin_bar;
        if ( !is_super_admin() || !is_admin_bar_showing() )
            return;

        $wp_admin_bar->add_menu( array(
            'parent' => "new-content",
            'id' => "sgmp-admin-bar-menu-new-shortcode",
            'title' => "<span class='ab-icon'></span><span class='ab-label'>Shortcodes</span>",
            'href' => "admin.php?page=sgmp-shortcodebuilder",
            'meta' => FALSE
        ) );

        $root_id = "sgmp";
        $wp_admin_bar->add_menu( array(
            'id'   => $root_id,
            'meta' => array(),
            'title' => "<span class='ab-icon'></span><span class='ab-label'>Google Map</span>",
            'href' => FALSE ));

        $wp_admin_bar->add_menu( array(
            'parent' => $root_id,
            'id' => "sgmp-admin-bar-menu-documentation",
            'title' => "Documentation",
            'href' => "admin.php?page=sgmp-documentation",
            'meta' => FALSE
        ) );

        $wp_admin_bar->add_menu( array(
            'parent' => $root_id,
            'id' => "sgmp-admin-bar-menu-shortcode-builder",
            'title' => "Shortcode Builder",
            'href' => "admin.php?page=sgmp-shortcodebuilder",
            'meta' => FALSE
        ) );

        $wp_admin_bar->add_menu( array(
            'parent' => $root_id,
            'id' => "sgmp-admin-bar-menu-saved-shortcodes",
            'title' => "Saved Shortcodes",
            'href' => "admin.php?page=sgmp-saved-shortcodes",
            'meta' => FALSE
        ) );

        $wp_admin_bar->add_menu( array(
            'parent' => $root_id,
            'id' => "sgmp-admin-bar-menu-settings",
            'title' => "Settings",
            'href' => "admin.php?page=sgmp-settings",
            'meta' => FALSE
        ) );

    }
endif;

?>
