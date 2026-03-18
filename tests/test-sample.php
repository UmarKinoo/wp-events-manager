<?php
/**
 * Unit tests for WP Events Manager plugin.
 *
 * @package WP_Events_Manager
 */

class Test_WP_Events_Manager extends WP_UnitTestCase {

	/**
	 * Test that the Event custom post type is registered.
	 */
	public function test_event_post_type_is_registered() {
		$this->assertTrue( post_type_exists( 'event' ) );
	}

	/**
	 * Test that the Event Type taxonomy is registered.
	 */
	public function test_event_type_taxonomy_is_registered() {
		$this->assertTrue( taxonomy_exists( 'event_type' ) );
	}

	/**
	 * Test that event meta saves correctly.
	 */
	public function test_event_meta_saves_correctly() {
		$post_id = $this->factory->post->create( array(
			'post_type'   => 'event',
			'post_status' => 'publish',
			'post_title'  => 'Test Event',
		) );

		update_post_meta( $post_id, '_event_date', '2025-06-15' );
		update_post_meta( $post_id, '_event_location', 'Port Louis, Mauritius' );
		update_post_meta( $post_id, '_event_capacity', 50 );

		$this->assertEquals( '2025-06-15', get_post_meta( $post_id, '_event_date', true ) );
		$this->assertEquals( 'Port Louis, Mauritius', get_post_meta( $post_id, '_event_location', true ) );
		$this->assertEquals( 50, (int) get_post_meta( $post_id, '_event_capacity', true ) );
	}

	/**
	 * Test that RSVP stores correctly in user meta.
	 */
	public function test_rsvp_stores_correctly() {
		$user_id = $this->factory->user->create( array(
			'role' => 'subscriber',
		) );

		$post_id = $this->factory->post->create( array(
			'post_type'   => 'event',
			'post_status' => 'publish',
		) );

		update_user_meta( $user_id, 'wpem_rsvp_' . $post_id, 'yes' );

		$this->assertEquals( 'yes', get_user_meta( $user_id, 'wpem_rsvp_' . $post_id, true ) );
	}

	/**
	 * Test that RSVP can be cancelled.
	 */
	public function test_rsvp_can_be_cancelled() {
		$user_id = $this->factory->user->create();
		$post_id = $this->factory->post->create( array( 'post_type' => 'event' ) );

		update_user_meta( $user_id, 'wpem_rsvp_' . $post_id, 'yes' );
		delete_user_meta( $user_id, 'wpem_rsvp_' . $post_id );

		$this->assertEmpty( get_user_meta( $user_id, 'wpem_rsvp_' . $post_id, true ) );
	}

	/**
	 * Test that the shortcode renders output.
	 */
	public function test_shortcode_renders_output() {
		$post_id = $this->factory->post->create( array(
			'post_type'   => 'event',
			'post_status' => 'publish',
			'post_title'  => 'Shortcode Test Event',
		) );

		update_post_meta( $post_id, '_event_date', '2025-06-15' );

		// Flush the cache so WP_Query picks up the new post.
		wp_cache_flush();

		$output = do_shortcode( '[events_list count="1"]' );

		$this->assertNotEmpty( $output );
		$this->assertStringContainsString( 'wpem-event-card', $output );
	}

	/**
	 * Test that event post type supports REST API.
	 */
	public function test_event_post_type_supports_rest() {
		$post_type = get_post_type_object( 'event' );
		$this->assertTrue( $post_type->show_in_rest );
	}
}