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
		add_action( 'pre_get_posts', array( $this, 'filter_events_query' ) );
		add_shortcode( 'events_list', array( $this, 'render_shortcode' ) );
		add_action( 'save_post_event', array( $this, 'clear_event_cache' ) );
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
	
	/**
	 * Modify the main query to handle filtering and search on the events archive.
	 *
	 * @param WP_Query $query The current query object.
	 */
	public function filter_events_query( $query ) {
		if ( ! is_admin() && $query->is_main_query() && is_post_type_archive( 'event' ) ) {

			// Filter by event type taxonomy.
			if ( ! empty( $_GET['event_type'] ) ) {
				$query->set( 'tax_query', array(
					array(
						'taxonomy' => 'event_type',
						'field'    => 'slug',
						'terms'    => sanitize_text_field( $_GET['event_type'] ),
					),
				) );
			}

			// Filter by keyword search.
			if ( ! empty( $_GET['event_search'] ) ) {
				$query->set( 's', sanitize_text_field( $_GET['event_search'] ) );
			}

			// Filter by date range.
			if ( ! empty( $_GET['date_from'] ) || ! empty( $_GET['date_to'] ) ) {
				$meta_query = array( 'relation' => 'AND' );

				if ( ! empty( $_GET['date_from'] ) ) {
					$meta_query[] = array(
						'key'     => '_event_date',
						'value'   => sanitize_text_field( $_GET['date_from'] ),
						'compare' => '>=',
						'type'    => 'DATE',
					);
				}

				if ( ! empty( $_GET['date_to'] ) ) {
					$meta_query[] = array(
						'key'     => '_event_date',
						'value'   => sanitize_text_field( $_GET['date_to'] ),
						'compare' => '<=',
						'type'    => 'DATE',
					);
				}

				$query->set( 'meta_query', $meta_query );
			}

			// Default order by event date ascending.
			$query->set( 'meta_key', '_event_date' );
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'order', 'ASC' );
		}
	}

	/**
	 * Render the [events_list] shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'count'   => 6,
				'type'    => '',
				'orderby' => 'meta_value',
				'order'   => 'ASC',
			),
			$atts,
			'events_list'
		);

		$args = array(
			'post_type'      => 'event',
			'posts_per_page' => absint( $atts['count'] ),
			'meta_key'       => '_event_date',
			'orderby'        => sanitize_text_field( $atts['orderby'] ),
			'order'          => sanitize_text_field( $atts['order'] ),
			'post_status'    => 'publish',
		);

		if ( ! empty( $atts['type'] ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'event_type',
					'field'    => 'slug',
					'terms'    => sanitize_text_field( $atts['type'] ),
				),
			);
		}

		$cache_key = 'wpem_shortcode_' . md5( serialize( $args ) );
		$events    = $this->get_cached_events( $args, $cache_key );

		ob_start();

		wp_enqueue_style(
			'wpem-events',
			WP_EVENTS_MANAGER_PLUGIN_URL . 'assets/css/events.css',
			array(),
			WP_EVENTS_MANAGER_VERSION
		);

		if ( $events->have_posts() ) : ?>
			<div class="wpem-events-grid">
				<?php while ( $events->have_posts() ) : $events->the_post(); ?>
					<?php
					$date     = get_post_meta( get_the_ID(), '_event_date', true );
					$location = get_post_meta( get_the_ID(), '_event_location', true );
					$terms    = get_the_terms( get_the_ID(), 'event_type' );
					?>
					<div class="wpem-event-card">
						<div class="wpem-event-card-body">
							<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

							<?php if ( $date ) : ?>
								<p class="wpem-event-meta">
									📅 <?php echo esc_html( date( 'M j, Y', strtotime( $date ) ) ); ?>
								</p>
							<?php endif; ?>

							<?php if ( $location ) : ?>
								<p class="wpem-event-meta">
									📍 <?php echo esc_html( $location ); ?>
								</p>
							<?php endif; ?>

							<?php if ( $terms && ! is_wp_error( $terms ) ) : ?>
								<?php foreach ( $terms as $term ) : ?>
									<span class="wpem-event-type-badge"><?php echo esc_html( $term->name ); ?></span>
								<?php endforeach; ?>
							<?php endif; ?>

							<br>
							<a href="<?php the_permalink(); ?>" class="wpem-read-more">
								<?php esc_html_e( 'View Event', 'wp-events-manager' ); ?>
							</a>
						</div>
					</div>
				<?php endwhile; ?>
			</div>
			<?php wp_reset_postdata(); ?>
		<?php else : ?>
			<p class="wpem-no-events"><?php esc_html_e( 'No events found.', 'wp-events-manager' ); ?></p>
		<?php endif;

		return ob_get_clean();
	}
	
	/**
	 * Get cached events or run fresh query.
	 *
	 * @param array $args WP_Query args.
	 * @param string $cache_key Unique cache key.
	 * @return WP_Query
	 */
	public function get_cached_events( $args, $cache_key ) {
		$cached = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$query = new WP_Query( $args );
		set_transient( $cache_key, $query, HOUR_IN_SECONDS );

		return $query;
	}

	/**
	 * Clear event transient cache when an event is saved.
	 *
	 * @param int $post_id Post ID.
	 */
	public function clear_event_cache( $post_id ) {
		global $wpdb;
		$wpdb->query(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wpem_%'"
		);
	}
	
}