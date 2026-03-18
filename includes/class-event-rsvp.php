<?php
/**
 * Handles RSVP functionality for events.
 *
 * @package WP_Events_Manager
 */

defined( 'ABSPATH' ) || exit;

class Event_RSVP {

	/**
	 * Hook everything up.
	 */
	public function __construct() {
		add_action( 'wpem_single_event_rsvp', array( $this, 'render_rsvp_box' ) );
		add_action( 'admin_post_wpem_rsvp', array( $this, 'handle_rsvp' ) );
		add_action( 'admin_post_nopriv_wpem_rsvp', array( $this, 'handle_rsvp_not_logged_in' ) );
		add_action( 'add_meta_boxes', array( $this, 'register_rsvp_meta_box' ) );
	}

	/**
	 * Check if the current user has RSVPed for an event.
	 *
	 * @param int $event_id Event post ID.
	 * @return bool
	 */
	public function user_has_rsvped( $event_id ) {
		if ( ! is_user_logged_in() ) {
			return false;
		}
		return get_user_meta( get_current_user_id(), 'wpem_rsvp_' . $event_id, true ) === 'yes';
	}

	/**
	 * Get the total RSVP count for an event.
	 *
	 * @param int $event_id Event post ID.
	 * @return int
	 */
	public function get_rsvp_count( $event_id ) {
		global $wpdb;
		$count = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value = 'yes'",
			'wpem_rsvp_' . $event_id
		) );
		return (int) $count;
	}

	/**
	 * Render the RSVP box on the single event page.
	 *
	 * @param int $event_id Event post ID.
	 */
	public function render_rsvp_box( $event_id ) {
		$capacity      = get_post_meta( $event_id, '_event_capacity', true );
		$rsvp_count    = $this->get_rsvp_count( $event_id );
		$user_rsvped   = $this->user_has_rsvped( $event_id );
		$is_full       = $capacity && $rsvp_count >= (int) $capacity;
		$action        = $user_rsvped ? 'cancel' : 'confirm';
		?>
		<div class="wpem-rsvp-box">
			<h3><?php esc_html_e( 'RSVP for this Event', 'wp-events-manager' ); ?></h3>

			<?php if ( $capacity ) : ?>
				<p class="wpem-capacity-info">
					<?php
					printf(
						esc_html__( '%1$d of %2$d spots taken', 'wp-events-manager' ),
						$rsvp_count,
						(int) $capacity
					);
					?>
				</p>
			<?php endif; ?>

			<?php if ( ! is_user_logged_in() ) : ?>
				<p><?php esc_html_e( 'You must be logged in to RSVP.', 'wp-events-manager' ); ?></p>
				<a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>" class="wpem-rsvp-btn">
					<?php esc_html_e( 'Log in to RSVP', 'wp-events-manager' ); ?>
				</a>

			<?php elseif ( $user_rsvped ) : ?>
				<p class="wpem-rsvp-confirmed">
					✓ <?php esc_html_e( 'You are attending this event.', 'wp-events-manager' ); ?>
				</p>
				<form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'wpem_rsvp_' . $event_id, 'wpem_rsvp_nonce' ); ?>
					<input type="hidden" name="action" value="wpem_rsvp" />
					<input type="hidden" name="event_id" value="<?php echo esc_attr( $event_id ); ?>" />
					<input type="hidden" name="rsvp_action" value="cancel" />
					<button type="submit" class="wpem-rsvp-btn wpem-rsvp-cancel">
						<?php esc_html_e( 'Cancel RSVP', 'wp-events-manager' ); ?>
					</button>
				</form>

			<?php elseif ( $is_full ) : ?>
				<p><?php esc_html_e( 'Sorry, this event is full.', 'wp-events-manager' ); ?></p>

			<?php else : ?>
				<form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'wpem_rsvp_' . $event_id, 'wpem_rsvp_nonce' ); ?>
					<input type="hidden" name="action" value="wpem_rsvp" />
					<input type="hidden" name="event_id" value="<?php echo esc_attr( $event_id ); ?>" />
					<input type="hidden" name="rsvp_action" value="confirm" />
					<button type="submit" class="wpem-rsvp-btn">
						<?php esc_html_e( 'RSVP Now', 'wp-events-manager' ); ?>
					</button>
				</form>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Handle the RSVP form submission.
	 */
	public function handle_rsvp() {
		$event_id = isset( $_POST['event_id'] ) ? absint( $_POST['event_id'] ) : 0;

		// Verify nonce.
		if ( ! isset( $_POST['wpem_rsvp_nonce'] ) || ! wp_verify_nonce( $_POST['wpem_rsvp_nonce'], 'wpem_rsvp_' . $event_id ) ) {
			wp_die( esc_html__( 'Security check failed.', 'wp-events-manager' ) );
		}

		// Must be logged in.
		if ( ! is_user_logged_in() ) {
			wp_redirect( wp_login_url( get_permalink( $event_id ) ) );
			exit;
		}

		if ( ! $event_id ) {
			wp_redirect( home_url() );
			exit;
		}

		$user_id      = get_current_user_id();
		$rsvp_action  = isset( $_POST['rsvp_action'] ) ? sanitize_text_field( $_POST['rsvp_action'] ) : 'confirm';
		$capacity     = get_post_meta( $event_id, '_event_capacity', true );
		$rsvp_count   = $this->get_rsvp_count( $event_id );

		if ( 'confirm' === $rsvp_action ) {
			// Check capacity before confirming.
			if ( $capacity && $rsvp_count >= (int) $capacity ) {
				wp_redirect( add_query_arg( 'rsvp', 'full', get_permalink( $event_id ) ) );
				exit;
			}

			update_user_meta( $user_id, 'wpem_rsvp_' . $event_id, 'yes' );

			// Send confirmation email.
			do_action( 'wpem_rsvp_confirmed', $user_id, $event_id );

		} elseif ( 'cancel' === $rsvp_action ) {
			delete_user_meta( $user_id, 'wpem_rsvp_' . $event_id );

			// Send cancellation email.
			do_action( 'wpem_rsvp_cancelled', $user_id, $event_id );
		}

		wp_redirect( add_query_arg( 'rsvp', $rsvp_action === 'confirm' ? 'confirmed' : 'cancelled', get_permalink( $event_id ) ) );
		exit;
	}

	/**
	 * Redirect non-logged-in users to login page.
	 */
	public function handle_rsvp_not_logged_in() {
		$event_id = isset( $_POST['event_id'] ) ? absint( $_POST['event_id'] ) : 0;
		wp_redirect( wp_login_url( get_permalink( $event_id ) ) );
		exit;
	}
	/**
	 * Register RSVP meta box on the event edit screen.
	 */
	public function register_rsvp_meta_box() {
		add_meta_box(
			'wpem_rsvp_attendees',
			__( 'RSVP Attendees', 'wp-events-manager' ),
			array( $this, 'render_rsvp_meta_box' ),
			'event',
			'normal',
			'default'
		);
	}

	/**
	 * Render the RSVP attendees meta box.
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function render_rsvp_meta_box( $post ) {
		$capacity   = get_post_meta( $post->ID, '_event_capacity', true );
		$rsvp_count = $this->get_rsvp_count( $post->ID );

		$users = get_users( array(
			'meta_key'   => 'wpem_rsvp_' . $post->ID,
			'meta_value' => 'yes',
		) );
		?>
		<p>
			<strong><?php esc_html_e( 'Total RSVPs:', 'wp-events-manager' ); ?></strong>
			<?php echo esc_html( $rsvp_count ); ?>
			<?php if ( $capacity ) : ?>
				/ <?php echo esc_html( $capacity ); ?> <?php esc_html_e( 'capacity', 'wp-events-manager' ); ?>
			<?php endif; ?>
		</p>

		<?php if ( ! empty( $users ) ) : ?>
			<table class="widefat striped" style="margin-top:12px;">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Name', 'wp-events-manager' ); ?></th>
						<th><?php esc_html_e( 'Email', 'wp-events-manager' ); ?></th>
						<th><?php esc_html_e( 'Username', 'wp-events-manager' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $users as $user ) : ?>
						<tr>
							<td><?php echo esc_html( $user->display_name ); ?></td>
							<td><?php echo esc_html( $user->user_email ); ?></td>
							<td><?php echo esc_html( $user->user_login ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php else : ?>
			<p style="color:#666;"><?php esc_html_e( 'No attendees yet.', 'wp-events-manager' ); ?></p>
		<?php endif;
	}
}