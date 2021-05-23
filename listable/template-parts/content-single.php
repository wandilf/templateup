<?php
/**
 * Template part for displaying single posts.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Listable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$has_image = false; ?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
		<div class="entry-featured" <?php listable_single_post_style(); ?>></div>
		<div class="header-content">
			<div class="entry-meta">
				<?php
				listable_posted_on();

				$post_categories = wp_get_post_categories( $post->ID );
				if ( ! is_wp_error( $post_categories ) ) {
					foreach ( $post_categories as $c ) {
						$cat = get_category( $c );
						echo '<a class="category-link" href="' . esc_sql( get_category_link( $cat->cat_ID ) ) . '">' . $cat->name . '</a>';
					}
				} ?>

			</div><!-- .entry-meta -->
			<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

            <?php if ( has_excerpt() ) { ?>
			    <div class="entry-subtitle"><?php the_excerpt(); ?></div>
            <?php } ?>

			<?php if ( function_exists( 'sharing_display' ) ) : ?>
				<?php sharing_display( '', true ); ?>
			<?php endif; ?>
		</div>
	</header><!-- .entry-header -->

	<?php if ( is_active_sidebar( 'blog_sidebar' ) ) { ?>
		<div class="entry-content_wrapper">
	<?php } ?>

	<div class="entry-content">

		<?php
		the_content();

		wp_link_pages( array(
			'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'listable' ),
			'after'  => '</div>',
		) );
		?>

		<?php if ( is_active_sidebar( 'blog_sidebar' ) ) { ?>

            <aside>
                <?php the_post_navigation(); ?>

                <?php
                // If comments are open or we have at least one comment, load up the comment template.
                if ( comments_open() || get_comments_number() ) :
                    comments_template();
                endif;
                ?>
            </aside>

        <?php } ?>

	</div><!-- .entry-content -->

	<?php if ( is_active_sidebar( 'blog_sidebar' ) ) { ?>
            <div class="widget-area--post">
                <?php dynamic_sidebar( 'blog_sidebar' ); ?>
            </div>
        </div>
	<?php } ?>

</article><!-- #post-## -->
