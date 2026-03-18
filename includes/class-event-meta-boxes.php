<?php
defined( 'ABSPATH' ) || exit;

class Event_Meta_Boxes {

	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_event', array( $this, 'save_meta' ), 10, 2 );
	}

	public function add_meta_boxes() {
	}

	public function save_meta( $post_id, $post ) {
	}
}
