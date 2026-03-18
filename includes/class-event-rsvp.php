<?php
defined( 'ABSPATH' ) || exit;

class Event_RSVP {

	public function __construct() {
	}

	public function has_availability( $event_id ) {
		return true;
	}

	public function register_attendee( $event_id, $user_id = 0 ) {
		return false;
	}
}
