<?php
/**
 * Template part for displaying the header fields like search or facets if FacetWP is active.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Listable
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

//do not show the navigation fields on the front page
if ( ! is_page_template( 'page-templates/front_page.php' ) ) {

	$search_form_class = 'search-form  js-search-form';

	if ( listable_using_facetwp() ) {
		global $post;

		$facets = listable_get_facets_by_area( 'navigation_bar' );

		if ( ! empty( $facets ) ) {

			$search_form_class .= '  show-on-mobile';

			?>

			<div class="header-facet-wrapper">
				<?php

				listable_display_facets( $facets );

				/**
				 * These header fields will be visible almost everywhere, but for example, they cannot filter facets
				 * on a blog post. So we need to redirect the search from every non-listing archive to the listings
				 * archive pages
				 */
				if ( ! is_tax( 'job_listing_category' ) && ! empty( $post ) && ! has_shortcode($post->post_content, 'jobs' ) ) {
					?>
					<div class="hide">
						<script>
							(function ($) {
								$(document).ready(function () {
									//prevent the facets from disappearing
									FWP.loading_handler = function () {}
								});

								$(document).on('keyup', '.header-facet-wrapper input[type="text"]', function (e) {
									if (e.which === 13) {
										//if the user presses ENTER/RETURN in a text field then redirect
										facetwp_redirect_to_listings();
										return false;
									}
								});

								$(document).on('change', '.header-facet-wrapper select, .header-facet-wrapper input[type="checkbox"]', function (ev, el) {
									if ($(this).val() !== '') {
										facetwp_redirect_to_listings();
									}
								});
							})(jQuery);

							facetwp_redirect_to_listings = function () {
								jQuery('body').css('opacity', '0');
								//wait a little bit
								setTimeout(function () {
									//if the user presses ENTER/RETURN in a text field then redirect
									FWP.parse_facets();
									FWP.set_hash();

									var query_string = FWP.build_query_string();
									if ('' != query_string) {
										query_string = '?' + query_string;
									}
									window.location.href = '<?php echo listable_get_listings_page_url(); ?>' + query_string;
									return false;
								}, 700);
							}
						</script>
						<?php echo facetwp_display( 'template', 'listings' ); ?>
					</div>
				<?php } ?>
				<button class="search-submit" name="submit" id="searchsubmit" onclick="FWP.refresh(); <?php echo ( ! empty( $post ) && ! has_shortcode( $post->post_content, 'jobs' ) && ! is_tax( 'job_listing_category' ) ) ? 'facetwp_redirect_to_listings();' : ''; ?>">
					<?php get_template_part( 'assets/svg/search-icon-svg' ); ?>
				</button>
			</div>
		<?php }
	} ?>

	<form class="<?php echo $search_form_class; ?>" method="get"
	      action="<?php echo get_post_type_archive_link( 'job_listing' ); ?>" role="search">
		<?php
		$has_search_menu = false;
		if ( has_nav_menu( 'search_suggestions' ) ) {
			$has_search_menu = true;
		}

		do_action( 'listable_header_search_hidden_fields' ); ?>

		<div class="search-field-wrapper<?php if ( $has_search_menu ) {
			echo '  has--menu';
		} ?>">
			<label for="search_keywords_placeholder"><?php esc_html_e( 'Keywords', 'listable' ); ?></label>
			<input class="search-field  js-search-mobile-field  js-search-suggestions-field" type="text"
                   name="search_keywords" id="search_keywords_placeholder"
			       placeholder="<?php esc_html_e( 'What are you looking for?', 'listable' ); ?>" autocomplete="off"
			       value="<?php the_search_query(); ?>"/>

			<?php wp_nav_menu( array(
				'container'      => false,
				'theme_location' => 'search_suggestions',
				'menu_class'     => 'search-suggestions-menu',
				'fallback_cb'    => false,
			) ); ?>
		</div>

		<span class="search-trigger--mobile  js-search-trigger-mobile">
            <?php get_template_part( 'assets/svg/search-icon-mobile-svg' ); ?>
            <?php get_template_part( 'assets/svg/close-icon-svg' ); ?>
        </span>

		<button class="search-submit  js-search-mobile-submit" name="submit" id="searchsubmit">
			<?php get_template_part( 'assets/svg/search-icon-svg' ); ?>
		</button>

	</form>

<?php }
