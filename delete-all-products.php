<?php



$products = get_posts( array(
    'post_type'      => 'product',
    'posts_per_page' => -1,
    'post_status'    => 'any',
    'fields'         => 'ids',
) );

if ( !empty( $products ) ) {
    foreach ( $products as $product_id ) {
        wp_delete_post( $product_id, true );
    }
}
$attributes = wc_get_attribute_taxonomies();

if ( !empty( $attributes ) ) {
    foreach ( $attributes as $attribute ) {
        wp_delete_term( $attribute->attribute_id, 'pa_' . $attribute->attribute_name );
    }
}

