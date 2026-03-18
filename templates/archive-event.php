<?php
defined( 'ABSPATH' ) || exit;

get_header();
?>

<?php if ( have_posts() ) : ?>
	<header class="events-archive-header">
		<h1 class="events-archive-title"><?php post_type_archive_title(); ?></h1>
	</header>
	<div class="events-archive-list">
		<?php while ( have_posts() ) : the_post(); ?>
			<article id="event-<?php the_ID(); ?>" <?php post_class( 'event-card' ); ?>>
				<?php the_title( '<h2 class="event-title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' ); ?>
				<div class="event-excerpt"><?php the_excerpt(); ?></div>
			</article>
		<?php endwhile; ?>
	</div>
	<?php the_posts_pagination(); ?>
<?php else : ?>
	<p><?php esc_html_e( 'No events found.', 'wp-events-manager' ); ?></p>
<?php endif; ?>

<?php
get_footer();
