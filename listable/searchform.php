<?php
/**
 * The template that displays the search form.
 *
 * @package Listable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<form class="search-form" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" role="search">
	<?php if ( get_post_type() === 'job_listing' ) {
		echo '<input type="hidden" name="post_type" value="job_listing" />';
	} ?>
	<input class="search-field" type="text" name="s" id="s" placeholder="<?php esc_html_e( 'What are you looking for?', 'listable' ); ?>" autocomplete="off" value="<?php the_search_query(); ?>"/>
	<button class="search-submit" name="submit" id="searchsubmit">
		<?php get_template_part( 'assets/svg/search-icon-svg'); ?>
    </button>
</form>