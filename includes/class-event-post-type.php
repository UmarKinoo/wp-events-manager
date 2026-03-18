<?php
/**
 * Registers the Event custom post type and Event Type taxonomy.
 *
 * @package WP_Events_Manager
 */

defined( 'ABSPATH' ) || exit;

class Event_Post_Type {

	/**
	 * Hook everything up.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register' ) );
		add_action( 'init', array( $this, 'register_taxonomy' ) );
		add_filter( 'manage_event_posts_columns', array( $this, 'admin_columns' ) );
		add_action( 'manage_event_posts_custom_column', array( $this, 'admin_column_content' ), 10, 2 );
		add_filter( 'template_include', array( $this, 'load_templates' ) );
	}

	/**
	 * Register the Event custom post type.
	 */
	public function register() {
		$labels = array(
			'name'               => __( 'Events', 'wp-events-manager' ),
			'singular_name'      => __( 'Event', 'wp-events-manager' ),
			'add_new'            => __( 'Add New', 'wp-events-manager' ),
			'add_new_item'       => __( 'Add New Event', 'wp-events-manager' ),
			'edit_item'          => __( 'Edit Event', 'wp-events-manager' ),
			'new_item'           => __( 'New Event', 'wp-events-manager' ),
			'view_item'          => __( 'View Event', 'wp-events-manager' ),
			'search_items'       => __( 'Search Events', 'wp-events-manager' ),
			'not_found'          => __( 'No events found', 'wp-events-manager' ),
			'not_found_in_trash' => __( 'No events found in trash', 'wp-events-manager' ),
		);

		$args = array(
			'labels'          => $labels,
			'public'          => true,
			'has_archive'     => true,
			'menu_icon'       => 'dashicons-calendar-alt',
			'supports'        => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'rewrite'         => array( 'slug' => 'events' ),
			'capability_type' => 'post',
			'map_meta_cap'    => true,
			'show_in_rest'    => true,
		);

		register_post_type( 'event', $args );
	}

	/**
	 * Register the Event Type taxonomy.
	 */
	public function register_taxonomy() {
		$labels = array(
			'name'              => __( 'Event Types', 'wp-events-manager' ),
			'singular_name'     => __( 'Event Type', 'wp-events-manager' ),
			'search_items'      => __( 'Search Event Types', 'wp-events-manager' ),
			'all_items'         => __( 'All Event Types', 'wp-events-manager' ),
			'edit_item'         => __( 'Edit Event Type', 'wp-events-manager' ),
			'update_item'       => __( 'Update Event Type', 'wp-events-manager' ),
			'add_new_item'      => __( 'Add New Event Type', 'wp-events-manager' ),
			'new_item_name'     => __( 'New Event Type Name', 'wp-events-manager' ),
			'menu_name'         => __( 'Event Types', 'wp-events-manager' ),
		);

		$args = array(
			'labels'       => $labels,
			'hierarchical' => true,
			'public'       => true,
			'rewrite'      => array( 'slug' => 'event-type' ),
			'show_in_rest' => true,
		);

		register_taxonomy( 'event_type', 'event', $args );
	}

	/**
	 * Add custom columns to the admin Events list.
	 *
	 * @param array $columns Existing columns.
	 * @return array Modified columns.
	 */
	public function admin_columns( $columns ) {
		$new = array();
		foreach ( $columns as $key => $value ) {
			$new[ $key ] = $value;
			if ( 'title' === $key ) {
				$new['event_date']     = __( 'Date', 'wp-events-manager' );
				$new['event_location'] = __( 'Location', 'wp-events-manager' );
				$new['event_type']     = __( 'Event Type', 'wp-events-manager' );
			}
		}
		return $new;
	}

	/**
	 * Populate custom admin column content.
	 *
	 * @param string $column  Column slug.
	 * @param int    $post_id Post ID.
	 */
	public function admin_column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'event_date':
				$date = get_post_meta( $post_id, '_event_date', true );
				echo $date ? esc_html( date( 'M j, Y', strtotime( $date ) ) ) : '—';
				break;
			case 'event_location':
				$location = get_post_meta( $post_id, '_event_location', true );
				echo $location ? esc_html( $location ) : '—';
				break;
			case 'event_type':
				$terms = get_the_terms( $post_id, 'event_type' );
				if ( $terms && ! is_wp_error( $terms ) ) {
					$names = wp_list_pluck( $terms, 'name' );
					echo esc_html( implode( ', ', $names ) );
				} else {
					echo '—';
				}
				break;
		}
	}

	/**
	 * Load custom templates for event single and archive views.
	 *
	 * @param string $template Current template path.
	 * @return string Modified template path.
	 */
	public function load_templates( $template ) {
		if ( is_singular( 'event' ) ) {
			$custom = WP_EVENTS_MANAGER_PLUGIN_DIR . 'templates/single-event.php';
			if ( file_exists( $custom ) ) {
				return $custom;
			}
		}

		if ( is_post_type_archive( 'event' ) ) {
			$custom = WP_EVENTS_MANAGER_PLUGIN_DIR . 'templates/archive-event.php';
			if ( file_exists( $custom ) ) {
				return $custom;
			}
		}

		return $template;
	}
}