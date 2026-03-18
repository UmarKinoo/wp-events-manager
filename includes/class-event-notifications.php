<?php
defined( 'ABSPATH' ) || exit;

class Event_Notifications {

	public function __construct() {
	}

	public function send_notification( $event_id, $type, $args = array() ) {
		return false;
	}
}
