<?php
defined( 'ABSPATH' ) || exit;

get_header();
?>

<?php while ( have_posts() ) : the_post(); ?>
	<article id="event-<?php the_ID(); ?>" <?php post_class( 'event-single' ); ?>>
		<header class="event-header">
			<?php the_title( '<h1 class="event-title">', '</h1>' ); ?>
		</header>
		<div class="event-content">
			<?php the_content(); ?>
		</div>
	</article>
<?php endwhile; ?>

<?php
get_footer();
