<?php
/**
 * Handles email notifications for events and RSVPs.
 *
 * @package WP_Events_Manager
 */

defined( 'ABSPATH' ) || exit;

class Event_Notifications {

	/**
	 * Hook everything up.
	 */
	public function __construct() {
		// Event published/updated notifications.
		add_action( 'transition_post_status', array( $this, 'on_event_published' ), 10, 3 );
		add_action( 'post_updated', array( $this, 'on_event_updated' ), 10, 3 );

		// RSVP notifications.
		add_action( 'wpem_rsvp_confirmed', array( $this, 'send_rsvp_confirmation' ), 10, 2 );
		add_action( 'wpem_rsvp_cancelled', array( $this, 'send_rsvp_cancellation' ), 10, 2 );
	}

	/**
	 * Send notification when an event is published.
	 *
	 * @param string  $new_status New post status.
	 * @param string  $old_status Old post status.
	 * @param WP_Post $post       Post object.
	 */
	public function on_event_published( $new_status, $old_status, $post ) {
		if ( 'event' !== $post->post_type ) {
			return;
		}

		if ( 'publish' === $new_status && 'publish' !== $old_status ) {
			$this->notify_admin_new_event( $post );
		}
	}

	/**
	 * Send notification when a published event is updated.
	 *
	 * @param int     $post_id     Post ID.
	 * @param WP_Post $post_after  Post after update.
	 * @param WP_Post $post_before Post before update.
	 */
	public function on_event_updated( $post_id, $post_after, $post_before ) {
		if ( 'event' !== $post_after->post_type ) {
			return;
		}

		if ( 'publish' === $post_after->post_status && $post_after->post_modified !== $post_before->post_modified ) {
			$this->notify_rsvp_users_of_update( $post_after );
		}
	}

	/**
	 * Notify admin when a new event is published.
	 *
	 * @param WP_Post $post Post object.
	 */
	private function notify_admin_new_event( $post ) {
		$admin_email = get_option( 'admin_email' );
		$subject     = sprintf(
			/* translators: %s: event title */
			__( 'New Event Published: %s', 'wp-events-manager' ),
			$post->post_title
		);

		$date     = get_post_meta( $post->ID, '_event_date', true );
		$location = get_post_meta( $post->ID, '_event_location', true );

		$message  = sprintf( __( 'A new event has been published on your site.', 'wp-events-manager' ) ) . "\n\n";
		$message .= sprintf( __( 'Event: %s', 'wp-events-manager' ), $post->post_title ) . "\n";
		$message .= $date ? sprintf( __( 'Date: %s', 'wp-events-manager' ), date( 'F j, Y', strtotime( $date ) ) ) . "\n" : '';
		$message .= $location ? sprintf( __( 'Location: %s', 'wp-events-manager' ), $location ) . "\n" : '';
		$message .= sprintf( __( 'View Event: %s', 'wp-events-manager' ), get_permalink( $post->ID ) ) . "\n";

		wp_mail( $admin_email, $subject, $message );
	}

	/**
	 * Notify all RSVPed users when an event is updated.
	 *
	 * @param WP_Post $post Post object.
	 */
	private function notify_rsvp_users_of_update( $post ) {
		$users = get_users( array(
			'meta_key'   => 'wpem_rsvp_' . $post->ID,
			'meta_value' => 'yes',
		) );

		if ( empty( $users ) ) {
			return;
		}

		$subject  = sprintf(
			/* translators: %s: event title */
			__( 'Event Updated: %s', 'wp-events-manager' ),
			$post->post_title
		);

		$date     = get_post_meta( $post->ID, '_event_date', true );
		$location = get_post_meta( $post->ID, '_event_location', true );

		foreach ( $users as $user ) {
			$message  = sprintf( __( 'Hi %s,', 'wp-events-manager' ), $user->display_name ) . "\n\n";
			$message .= sprintf( __( 'An event you RSVPed for has been updated.', 'wp-events-manager' ) ) . "\n\n";
			$message .= sprintf( __( 'Event: %s', 'wp-events-manager' ), $post->post_title ) . "\n";
			$message .= $date ? sprintf( __( 'Date: %s', 'wp-events-manager' ), date( 'F j, Y', strtotime( $date ) ) ) . "\n" : '';
			$message .= $location ? sprintf( __( 'Location: %s', 'wp-events-manager' ), $location ) . "\n" : '';
			$message .= sprintf( __( 'View Event: %s', 'wp-events-manager' ), get_permalink( $post->ID ) ) . "\n";

			wp_mail( $user->user_email, $subject, $message );
		}
	}

	/**
	 * Send RSVP confirmation email to user.
	 *
	 * @param int $user_id  User ID.
	 * @param int $event_id Event post ID.
	 */
	public function send_rsvp_confirmation( $user_id, $event_id ) {
		$user     = get_userdata( $user_id );
		$event    = get_post( $event_id );
		$date     = get_post_meta( $event_id, '_event_date', true );
		$location = get_post_meta( $event_id, '_event_location', true );

		$subject  = sprintf(
			/* translators: %s: event title */
			__( 'RSVP Confirmed: %s', 'wp-events-manager' ),
			$event->post_title
		);

		$message  = sprintf( __( 'Hi %s,', 'wp-events-manager' ), $user->display_name ) . "\n\n";
		$message .= __( 'Your RSVP has been confirmed for the following event:', 'wp-events-manager' ) . "\n\n";
		$message .= sprintf( __( 'Event: %s', 'wp-events-manager' ), $event->post_title ) . "\n";
		$message .= $date ? sprintf( __( 'Date: %s', 'wp-events-manager' ), date( 'F j, Y', strtotime( $date ) ) ) . "\n" : '';
		$message .= $location ? sprintf( __( 'Location: %s', 'wp-events-manager' ), $location ) . "\n" : '';
		$message .= sprintf( __( 'View Event: %s', 'wp-events-manager' ), get_permalink( $event_id ) ) . "\n\n";
		$message .= __( 'We look forward to seeing you there!', 'wp-events-manager' ) . "\n";

		wp_mail( $user->user_email, $subject, $message );
	}

	/**
	 * Send RSVP cancellation email to user.
	 *
	 * @param int $user_id  User ID.
	 * @param int $event_id Event post ID.
	 */
	public function send_rsvp_cancellation( $user_id, $event_id ) {
		$user  = get_userdata( $user_id );
		$event = get_post( $event_id );

		$subject = sprintf(
			/* translators: %s: event title */
			__( 'RSVP Cancelled: %s', 'wp-events-manager' ),
			$event->post_title
		);

		$message  = sprintf( __( 'Hi %s,', 'wp-events-manager' ), $user->display_name ) . "\n\n";
		$message .= sprintf( __( 'Your RSVP for "%s" has been cancelled.', 'wp-events-manager' ), $event->post_title ) . "\n\n";
		$message .= sprintf( __( 'If this was a mistake, you can re-register here: %s', 'wp-events-manager' ), get_permalink( $event_id ) ) . "\n";

		wp_mail( $user->user_email, $subject, $message );
	}
}