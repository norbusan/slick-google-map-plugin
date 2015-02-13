<?php
if(defined('WP_UNINSTALL_PLUGIN')) {
	global $wpdb;
	if (is_multisite()) {
		$blogs = $wpdb->get_results("SELECT `blog_id` FROM {$wpdb->blogs}", ARRAY_A);
		$options_table = $wpdb->options;
			$wpdb->query( "DELETE FROM ".$options_table." WHERE option_name LIKE '".SGMP_ALL_MAP_CACHED_CONSTANTS_PREFIX."%';" );
	
			delete_option('sgmp_options');
			delete_option('widget_slickgooglemap');
			delete_option('sgmp_persisted_shortcodes');
			delete_transient('sgmp_update_routine');
			delete_transient('sgmp_layers_markers_export');

			//legacy
			delete_option(SGMP_DB_PUBLISHED_POST_MARKERS);
			delete_option(SGMP_DB_POST_COUNT);
			delete_option(SGMP_DB_PUBLISHED_POST_IDS);
			delete_option(SGMP_DB_PUBLISHED_PAGE_IDS);
			delete_option(SGMP_DB_SETTINGS_SHOULD_BASE_OBJECT_RENDER);
			delete_option(SGMP_DB_SETTINGS_WAS_BASE_OBJECT_RENDERED);
			delete_option(SGMP_DB_PURGE_GEOMASHUP_CACHE);
			delete_option(SGMP_DB_GEOMASHUP_CONTENT);
		if ($blogs) {
			foreach($blogs as $blog) {
				switch_to_blog($blog['blog_id']);
					$options_table = $wpdb->options;
					$wpdb->query( "DELETE FROM ".$options_table." WHERE option_name LIKE '".SGMP_ALL_MAP_CACHED_CONSTANTS_PREFIX."%';" );
			
					delete_option('sgmp_options');
					delete_option('widget_slickgooglemap');
					delete_option('sgmp_persisted_shortcodes');
					delete_transient('sgmp_update_routine');
					delete_transient('sgmp_layers_markers_export');

					//legacy
					delete_option(SGMP_DB_PUBLISHED_POST_MARKERS);
					delete_option(SGMP_DB_POST_COUNT);
					delete_option(SGMP_DB_PUBLISHED_POST_IDS);
					delete_option(SGMP_DB_PUBLISHED_PAGE_IDS);
					delete_option(SGMP_DB_SETTINGS_SHOULD_BASE_OBJECT_RENDER);
					delete_option(SGMP_DB_SETTINGS_WAS_BASE_OBJECT_RENDERED);
					delete_option(SGMP_DB_PURGE_GEOMASHUP_CACHE);
					delete_option(SGMP_DB_GEOMASHUP_CONTENT);
					restore_current_blog();
				}
		}
	} else {
		$options_table = $wpdb->options;
		$wpdb->query( "DELETE FROM ".$options_table." WHERE option_name LIKE '".SGMP_ALL_MAP_CACHED_CONSTANTS_PREFIX."%';" );

		delete_option('sgmp_options');
		delete_option('widget_slickgooglemap');
		delete_option('sgmp_persisted_shortcodes');
		delete_transient('sgmp_update_routine');
		delete_transient('sgmp_layers_markers_export');

		//legacy
		delete_option(SGMP_DB_PUBLISHED_POST_MARKERS);
		delete_option(SGMP_DB_POST_COUNT);
		delete_option(SGMP_DB_PUBLISHED_POST_IDS);
		delete_option(SGMP_DB_PUBLISHED_PAGE_IDS);
		delete_option(SGMP_DB_SETTINGS_SHOULD_BASE_OBJECT_RENDER);
		delete_option(SGMP_DB_SETTINGS_WAS_BASE_OBJECT_RENDERED);
		delete_option(SGMP_DB_PURGE_GEOMASHUP_CACHE);
		delete_option(SGMP_DB_GEOMASHUP_CONTENT);
	}
}
?>
