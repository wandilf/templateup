<?php
/**
 * In this file we will put every function or hook which is needed to provide Woocommerce compatibility
 */

/**
 * First remove the woocommerce style. We'll provide one.
 */
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

// replace the pagination with our own in the `woocommerce/loop/loop-end.php`
remove_action( 'woocommerce_after_shop_loop', 'woocommerce_pagination', 10 );

remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );

//mode the payment options on checkout after billing details
remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
add_action( 'woocommerce_checkout_after_customer_details', 'woocommerce_checkout_payment', 20 );

// remove the title from woocommerce_single_product_summary because we are calling it a few lines before
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);

// remove the breadcrumb from woocommerce_before_main_content because we are calling it after title
remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);

// remove rating from woocommerce_single_product_summary, it doesn't apply on our design.
// if you really need this, override this file with a child theme
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );

remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);

remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);
add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);

remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
add_action( 'woocommerce_shop_loop_item_title', 'listable_woocommerce_template_loop_product_title', 10 );

function listable_woocommerce_template_loop_product_title() {
	echo '<h2 class="card__title">' . get_the_title() . '</h2>';
}

add_action ( 'woocommerce_after_cart_table', 'listable_woocommerce_proceed_to_checkout');

remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
add_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display', 10 );

if ( ! function_exists( 'listable_woocommerce_proceed_to_checkout') ) :
	function listable_woocommerce_proceed_to_checkout() { ?>

		<div class="wc-proceed-to-checkout">

				<?php do_action( 'woocommerce_proceed_to_checkout' ); ?>

		</div>

	<?php }
endif;

remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 );
add_action( 'woocommerce_checkout_before_customer_details', 'woocommerce_checkout_login_form', 10 );

remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
add_action( 'woocommerce_checkout_before_customer_details', 'woocommerce_checkout_coupon_form', 10 );

function listable_checkout_place_order_button() {
	wc_get_template( 'checkout/place-order-button.php', array(
			'order_button_text'  => apply_filters( 'woocommerce_order_button_text', esc_html__( 'Place order', 'listable' ) )
	) );
}

add_action( 'woocommerce_checkout_shipping', 'listable_checkout_place_order_button', 20 );


if ( ! function_exists( 'woocommerce_template_loop_product_thumbnail' ) ) {
	function woocommerce_template_loop_product_thumbnail() {
		echo woocommerce_get_product_thumbnail();
	}
}
if ( ! function_exists( 'woocommerce_get_product_thumbnail' ) ) {
	function woocommerce_get_product_thumbnail( $size = 'shop_catalog', $placeholder_width = 0, $placeholder_height = 0  ) {
		global $product, $post;
		$output = '<div class="card__image  card__image--product"';

		if ( has_post_thumbnail() ) {
			$image = wp_get_attachment_image_src ( get_post_thumbnail_id( get_the_ID() ), $size, true );
			$output .= ' style="background-image: url('. $image[0] .')" ';
		}

		$output .= '>';
		$output .= '<span class="product__price">' . $product->get_price_html() . '</span>';

		$posttags = wp_get_post_terms( get_the_ID($product->get_id()) , 'product_tag' , 'fields=names' );
		if($posttags) $output .= '<span class="product__tag">' . $posttags[0] . '</span>';

		$output .= '</div><!-- card__image -->';
		return $output;
	}
}

function short_desc_products_widget() {
    wc_get_template( 'single-product/short-description.php' );
}

add_filter('woocommerce_widget_product_item_end', 'short_desc_products_widget');

function listable_change_product_price_display_widget ( $price ) {
     if ( is_page_template('page-templates/front_page.php')  || is_singular( 'job_listing' ) )
        return '<span>'. $price . '</span>';
    else
        return $price;
}

add_filter( 'woocommerce_get_price_html', 'listable_change_product_price_display_widget' );

function listable_cart_widget_content_wrapper( $product_name ) {
     if ( is_page_template('page-templates/front_page.php')  || is_singular( 'job_listing' ) )
        return '<div class="card__content">' . $product_name . '</div>';
     else {
         return $product_name;
     }
}

add_filter( 'woocommerce_cart_item_name', 'listable_cart_widget_content_wrapper', 10, 1 );

function listable_cart_widget_total() {
    echo '<p class="woocommerce-mini-cart__total total"><strong>' . esc_html__( 'Total', 'woocommerce' ) . '</strong>' . WC()->cart->get_cart_total() . '</p>';
}

add_action( 'woocommerce_widget_shopping_cart_before_buttons', 'listable_cart_widget_total');

remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
