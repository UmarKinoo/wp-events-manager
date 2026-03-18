<?php
/**
 * Sample data installer for WP Events Manager.
 * Run once via WP-CLI: wp eval-file sample-data.php
 *
 * @package WP_Events_Manager
 */

$events = array(
    array(
        'title'    => 'Tech Conference 2025',
        'date'     => '2025-06-15',
        'end_date' => '2025-06-16',
        'location' => 'Ebène CyberCity, Mauritius',
        'capacity' => 100,
        'type'     => 'conference',
        'content'  => 'Annual technology conference bringing together developers, designers and entrepreneurs.',
    ),
    array(
        'title'    => 'WordPress Meetup',
        'date'     => '2025-07-20',
        'end_date' => '2025-07-20',
        'location' => 'Port Louis, Mauritius',
        'capacity' => 50,
        'type'     => 'meetup',
        'content'  => 'Monthly WordPress community meetup. All levels welcome.',
    ),
    array(
        'title'    => 'Web Dev Workshop',
        'date'     => '2025-08-10',
        'end_date' => '2025-08-10',
        'location' => 'Quatre Bornes, Mauritius',
        'capacity' => 30,
        'type'     => 'workshop',
        'content'  => 'Hands-on workshop covering modern front-end development techniques.',
    ),
);

foreach ( $events as $event ) {
    // Create or get the event type term.
    $term = term_exists( $event['type'], 'event_type' );
    if ( ! $term ) {
        $term = wp_insert_term( ucfirst( $event['type'] ), 'event_type' );
    }
    $term_id = is_array( $term ) ? $term['term_id'] : $term;

    // Create the event post.
    $post_id = wp_insert_post( array(
        'post_title'   => $event['title'],
        'post_content' => $event['content'],
        'post_status'  => 'publish',
        'post_type'    => 'event',
    ) );

    if ( $post_id && ! is_wp_error( $post_id ) ) {
        update_post_meta( $post_id, '_event_date', $event['date'] );
        update_post_meta( $post_id, '_event_end_date', $event['end_date'] );
        update_post_meta( $post_id, '_event_location', $event['location'] );
        update_post_meta( $post_id, '_event_capacity', $event['capacity'] );
        wp_set_object_terms( $post_id, $term_id, 'event_type' );

        echo "Created: " . $event['title'] . "\n";
    }
}

echo "Sample data installed successfully.\n";