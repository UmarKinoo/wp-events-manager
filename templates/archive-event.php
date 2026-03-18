<?php
/**
 * Archive template for Event post type.
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

<div class="wpem-events-archive">

    <h1><?php esc_html_e( 'Events', 'wp-events-manager' ); ?></h1>

    <!-- Filter form -->
    <form class="wpem-filter-form" method="GET" action="<?php echo esc_url( get_post_type_archive_link( 'event' ) ); ?>">
        <select name="event_type">
            <option value=""><?php esc_html_e( 'All Event Types', 'wp-events-manager' ); ?></option>
            <?php
            $types = get_terms( array( 'taxonomy' => 'event_type', 'hide_empty' => false ) );
            foreach ( $types as $type ) :
            ?>
                <option value="<?php echo esc_attr( $type->slug ); ?>" <?php selected( isset( $_GET['event_type'] ) ? $_GET['event_type'] : '', $type->slug ); ?>>
                    <?php echo esc_html( $type->name ); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input
            type="date"
            name="date_from"
            value="<?php echo isset( $_GET['date_from'] ) ? esc_attr( $_GET['date_from'] ) : ''; ?>"
            placeholder="<?php esc_attr_e( 'From date', 'wp-events-manager' ); ?>"
        />

        <input
            type="date"
            name="date_to"
            value="<?php echo isset( $_GET['date_to'] ) ? esc_attr( $_GET['date_to'] ) : ''; ?>"
            placeholder="<?php esc_attr_e( 'To date', 'wp-events-manager' ); ?>"
        />

        <input
            type="text"
            name="event_search"
            value="<?php echo isset( $_GET['event_search'] ) ? esc_attr( $_GET['event_search'] ) : ''; ?>"
            placeholder="<?php esc_attr_e( 'Search events...', 'wp-events-manager' ); ?>"
        />

        <button type="submit"><?php esc_html_e( 'Filter', 'wp-events-manager' ); ?></button>

        <?php if ( isset( $_GET['event_type'] ) || isset( $_GET['date_from'] ) || isset( $_GET['date_to'] ) || isset( $_GET['event_search'] ) ) : ?>
            <a href="<?php echo esc_url( get_post_type_archive_link( 'event' ) ); ?>"><?php esc_html_e( 'Clear filters', 'wp-events-manager' ); ?></a>
        <?php endif; ?>
    </form>

    <?php if ( have_posts() ) : ?>
        <div class="wpem-events-grid">
            <?php while ( have_posts() ) : the_post(); ?>
                <?php
                $date     = get_post_meta( get_the_ID(), '_event_date', true );
                $location = get_post_meta( get_the_ID(), '_event_location', true );
                $terms    = get_the_terms( get_the_ID(), 'event_type' );
                ?>
                <div class="wpem-event-card">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <a href="<?php the_permalink(); ?>">
                            <?php the_post_thumbnail( 'medium', array( 'style' => 'width:100%;height:180px;object-fit:cover;display:block;' ) ); ?>
                        </a>
                    <?php endif; ?>
                    <div class="wpem-event-card-body">
                        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

                        <?php if ( $date ) : ?>
                            <p class="wpem-event-meta">
                                <span>📅</span> <?php echo esc_html( date( 'M j, Y', strtotime( $date ) ) ); ?>
                            </p>
                        <?php endif; ?>

                        <?php if ( $location ) : ?>
                            <p class="wpem-event-meta">
                                <span>📍</span> <?php echo esc_html( $location ); ?>
                            </p>
                        <?php endif; ?>

                        <?php if ( $terms && ! is_wp_error( $terms ) ) : ?>
                            <?php foreach ( $terms as $term ) : ?>
                                <span class="wpem-event-type-badge"><?php echo esc_html( $term->name ); ?></span>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <br>
                        <a href="<?php the_permalink(); ?>" class="wpem-read-more">
                            <?php esc_html_e( 'View Event', 'wp-events-manager' ); ?>
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <div style="margin-top:32px;">
            <?php the_posts_pagination(); ?>
        </div>

    <?php else : ?>
        <div class="wpem-no-events">
            <p><?php esc_html_e( 'No events found.', 'wp-events-manager' ); ?></p>
        </div>
    <?php endif; ?>

</div>

<?php get_footer(); ?>