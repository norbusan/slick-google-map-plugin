<?php
declare(strict_types=1);

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'sgmp_options' );

global $wpdb;
$like = $wpdb->esc_like( '_transient_sgmp_' ) . '%';
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
		$like,
		$wpdb->esc_like( '_transient_timeout_sgmp_' ) . '%'
	)
);
