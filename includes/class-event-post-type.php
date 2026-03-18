<?php
defined( 'ABSPATH' ) || exit;

class Event_Post_Type {

	const POST_TYPE = 'event';

	public function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
	}

	public function register_post_type() {
	}
}
