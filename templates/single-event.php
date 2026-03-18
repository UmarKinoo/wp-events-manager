<?php
/**
 * Single event template.
 *
 * @package WP_Events_Manager
 */

get_header();

wp_enqueue_style(
    'wpem-events',
    WP_EVENTS_MANAGER_PLUGIN_URL . 'assets/css/events.css',
    array(),
    WP_EVENTS_MANAGER_VERSION
);
?>

<div class="wpem-single-event">

    <a href="<?php echo esc_url( get_post_type_archive_link( 'event' ) ); ?>" class="wpem-back-link">
        &larr; <?php esc_html_e( 'Back to Events', 'wp-events-manager' ); ?>
    </a>

    <?php while ( have_posts() ) : the_post(); ?>

        <h1><?php the_title(); ?></h1>

        <?php
        $date     = get_post_meta( get_the_ID(), '_event_date', true );
        $end_date = get_post_meta( get_the_ID(), '_event_end_date', true );
        $location = get_post_meta( get_the_ID(), '_event_location', true );
        $capacity = get_post_meta( get_the_ID(), '_event_capacity', true );
        $terms    = get_the_terms( get_the_ID(), 'event_type' );
        ?>

        <div class="wpem-event-details-box">
            <?php if ( $date ) : ?>
                <p>
                    <strong><?php esc_html_e( 'Start Date:', 'wp-events-manager' ); ?></strong>
                    <?php echo esc_html( date( 'F j, Y', strtotime( $date ) ) ); ?>
                </p>
            <?php endif; ?>

            <?php if ( $end_date ) : ?>
                <p>
                    <strong><?php esc_html_e( 'End Date:', 'wp-events-manager' ); ?></strong>
                    <?php echo esc_html( date( 'F j, Y', strtotime( $end_date ) ) ); ?>
                </p>
            <?php endif; ?>

            <?php if ( $location ) : ?>
                <p>
                    <strong><?php esc_html_e( 'Location:', 'wp-events-manager' ); ?></strong>
                    <?php echo esc_html( $location ); ?>
                </p>
            <?php endif; ?>

            <?php if ( $capacity ) : ?>
                <p>
                    <strong><?php esc_html_e( 'Capacity:', 'wp-events-manager' ); ?></strong>
                    <?php echo esc_html( $capacity ); ?> <?php esc_html_e( 'attendees', 'wp-events-manager' ); ?>
                </p>
            <?php endif; ?>

            <?php if ( $terms && ! is_wp_error( $terms ) ) : ?>
                <p>
                    <strong><?php esc_html_e( 'Type:', 'wp-events-manager' ); ?></strong>
                    <?php foreach ( $terms as $term ) : ?>
                        <span class="wpem-event-type-badge"><?php echo esc_html( $term->name ); ?></span>
                    <?php endforeach; ?>
                </p>
            <?php endif; ?>
        </div>

        <?php if ( has_post_thumbnail() ) : ?>
            <div style="margin-bottom:24px;">
                <?php the_post_thumbnail( 'large', array( 'style' => 'width:100%;height:auto;border-radius:8px;' ) ); ?>
            </div>
        <?php endif; ?>

        <div class="wpem-event-content">
            <?php the_content(); ?>
        </div>

        <!-- RSVP section — rendered by the RSVP class via do_action -->
        <?php do_action( 'wpem_single_event_rsvp', get_the_ID() ); ?>

    <?php endwhile; ?>

</div>

<?php get_footer(); ?>