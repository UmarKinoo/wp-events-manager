<?php
/**
 * Registers and handles custom meta boxes for the Event post type.
 *
 * @package WP_Events_Manager
 */

defined( 'ABSPATH' ) || exit;

class Event_Meta_Boxes {

	/**
	 * Hook everything up.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'register' ) );
		add_action( 'save_post_event', array( $this, 'save' ), 10, 2 );
	}

	/**
	 * Register the meta box.
	 */
	public function register() {
		add_meta_box(
			'event_details',
			__( 'Event Details', 'wp-events-manager' ),
			array( $this, 'render' ),
			'event',
			'normal',
			'high'
		);
	}

	/**
	 * Render the meta box HTML.
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function render( $post ) {
		// Nonce field for security verification on save.
		wp_nonce_field( 'wpem_save_event_details', 'wpem_event_nonce' );

		$date     = get_post_meta( $post->ID, '_event_date', true );
		$end_date = get_post_meta( $post->ID, '_event_end_date', true );
		$location = get_post_meta( $post->ID, '_event_location', true );
		$capacity = get_post_meta( $post->ID, '_event_capacity', true );
		?>
		<table class="form-table">
			<tr>
				<th>
					<label for="event_date"><?php esc_html_e( 'Start Date', 'wp-events-manager' ); ?></label>
				</th>
				<td>
					<input
						type="date"
						id="event_date"
						name="event_date"
						value="<?php echo esc_attr( $date ); ?>"
						class="regular-text"
					/>
				</td>
			</tr>
			<tr>
				<th>
					<label for="event_end_date"><?php esc_html_e( 'End Date', 'wp-events-manager' ); ?></label>
				</th>
				<td>
					<input
						type="date"
						id="event_end_date"
						name="event_end_date"
						value="<?php echo esc_attr( $end_date ); ?>"
						class="regular-text"
					/>
				</td>
			</tr>
			<tr>
				<th>
					<label for="event_location"><?php esc_html_e( 'Location', 'wp-events-manager' ); ?></label>
				</th>
				<td>
					<input
						type="text"
						id="event_location"
						name="event_location"
						value="<?php echo esc_attr( $location ); ?>"
						class="regular-text"
						placeholder="<?php esc_attr_e( 'e.g. Ebène CyberCity, Mauritius', 'wp-events-manager' ); ?>"
					/>
				</td>
			</tr>
			<tr>
				<th>
					<label for="event_capacity"><?php esc_html_e( 'Capacity', 'wp-events-manager' ); ?></label>
				</th>
				<td>
					<input
						type="number"
						id="event_capacity"
						name="event_capacity"
						value="<?php echo esc_attr( $capacity ); ?>"
						class="small-text"
						min="1"
					/>
					<p class="description"><?php esc_html_e( 'Maximum number of attendees. Leave blank for unlimited.', 'wp-events-manager' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Save meta box data.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public function save( $post_id, $post ) {
		// Verify nonce.
		if ( ! isset( $_POST['wpem_event_nonce'] ) || ! wp_verify_nonce( $_POST['wpem_event_nonce'], 'wpem_save_event_details' ) ) {
			return;
		}

		// Don't save on autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check user has permission to edit this post.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save start date.
		if ( isset( $_POST['event_date'] ) ) {
			update_post_meta(
				$post_id,
				'_event_date',
				sanitize_text_field( $_POST['event_date'] )
			);
		}

		// Save end date.
		if ( isset( $_POST['event_end_date'] ) ) {
			update_post_meta(
				$post_id,
				'_event_end_date',
				sanitize_text_field( $_POST['event_end_date'] )
			);
		}

		// Save location.
		if ( isset( $_POST['event_location'] ) ) {
			update_post_meta(
				$post_id,
				'_event_location',
				sanitize_text_field( $_POST['event_location'] )
			);
		}

		// Save capacity.
		if ( isset( $_POST['event_capacity'] ) ) {
			update_post_meta(
				$post_id,
				'_event_capacity',
				absint( $_POST['event_capacity'] )
			);
		}
	}
}