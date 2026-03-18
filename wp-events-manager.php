<?php
/**
 * Plugin Name:     WP Events Manager
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     A custom events plugin with RSVP, filtering, and REST API.
 * Author:          Umar Kinoo
 * Author URI:      YOUR SITE HERE
 * Text Domain:     wp-events-manager
 * Domain Path:     /languages
 * Version:         0.1.0
 */

defined( 'ABSPATH' ) || exit;

// Plugin constants.
define( 'WP_EVENTS_MANAGER_VERSION', '0.1.0' );
define( 'WP_EVENTS_MANAGER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_EVENTS_MANAGER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once WP_EVENTS_MANAGER_PLUGIN_DIR . 'includes/class-event-post-type.php';
require_once WP_EVENTS_MANAGER_PLUGIN_DIR . 'includes/class-event-meta-boxes.php';
require_once WP_EVENTS_MANAGER_PLUGIN_DIR . 'includes/class-event-notifications.php';
require_once WP_EVENTS_MANAGER_PLUGIN_DIR . 'includes/class-event-rsvp.php';

// Load plugin textdomain for translations.
add_action( 'init', function() {
	load_plugin_textdomain(
		'wp-events-manager',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
} );

function wp_events_manager_init() {
	new Event_Post_Type();
	new Event_Meta_Boxes();
	new Event_Notifications();
	new Event_RSVP();
}
add_action( 'plugins_loaded', 'wp_events_manager_init' );

// Flush rewrite rules on activation so /events/ URLs work immediately.
register_activation_hook( __FILE__, function() {
	$cpt = new Event_Post_Type();
	$cpt->register();
	flush_rewrite_rules();
} );

register_deactivation_hook( __FILE__, function() {
	flush_rewrite_rules();
} );
