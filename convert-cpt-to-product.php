<?php 

function convert_cpt_to_woocommerce_products() {
    $args = array(
        'post_type'      => 'katalog-aut',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    );

    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        echo "No posts found in katalog-aut.";
        return;
    }

    while ($query->have_posts()) {
        $query->the_post();
        $post_id = get_the_ID();
        
        $marka_terms = wp_get_post_terms($post_id, 'marka', array('fields' => 'names'));
        
        $product_name = get_post_meta($post_id, 'nazwa_auta', true);
        $product_price = get_post_meta($post_id, 'cena', true);
        $product_thumbnail = get_post_meta($post_id, 'zdjecie_glowne', true);
        error_log('product_thumbnail: ' . print_r($product_thumbnail,true));
        $gallery_images = get_post_meta($post_id, 'galeria', false);
        error_log('gallery_images: ' . print_r($gallery_images,true));
        
        $product_price = (!empty($product_price)) ? $product_price : 0;
        
        $product_thumbnail_id = (!empty($product_thumbnail)) ? $product_thumbnail : null;
        
        $gallery_image_ids = (!empty($gallery_images)) ? $gallery_images : array();
        
        $product_data = array(
            'post_title'   => !empty($product_name) ? $product_name : get_the_title(),
            'post_content' => get_the_content(),
            'post_status'  => 'publish',
            'post_type'    => 'product',
        );

        $new_product_id = wp_insert_post($product_data);

        if (!$new_product_id) {
            echo "Failed to insert product for post ID: $post_id";
            continue;
        }

        if (!empty($marka_terms)) {
            wp_set_object_terms($new_product_id, $marka_terms, 'product_brand');
        }

        wp_set_object_terms($new_product_id, 'simple', 'product_type');

        update_post_meta($new_product_id, '_price', $product_price);
        update_post_meta($new_product_id, '_regular_price', $product_price);
        update_post_meta($new_product_id, '_stock', 1);
        update_post_meta($new_product_id, '_manage_stock', 'yes');
        update_post_meta($new_product_id, '_stock_status', 'instock');


        if ($product_thumbnail_id) {
            set_post_thumbnail($new_product_id, $product_thumbnail_id);
        }

        if (!empty($gallery_image_ids)) {
            update_post_meta($new_product_id, '_product_image_gallery', implode(',', $gallery_image_ids));
        }

        $attributes = array(
            'model'        => get_post_meta($post_id, 'model', true),
            'oszczednosc'  => get_post_meta($post_id, 'oszczednosc', true),
            'moc'          => get_post_meta($post_id, 'moc', true),
            'pojemnosc'    => get_post_meta($post_id, 'pojemnosc', true),
            'przebieg'     => get_post_meta($post_id, 'przebieg', true),
            'rocznik'      => get_post_meta($post_id, 'rocznik', true),
            'naped'        => get_post_meta($post_id, 'naped', true),
            'kolor'        => get_post_meta($post_id, 'kolor', true),
            'typ_podwozia' => get_post_meta($post_id, 'typ_podwozia', true),
            'uszkodzenia'  => get_post_meta($post_id, 'uszkodzenia', true),
            'stan'         => get_post_meta($post_id, 'stan', true),
        );

        $product_attributes = array();
        foreach ($attributes as $key => $value) {
            if (!empty($value)) {
                $attr_name = ucfirst(str_replace('_', ' ', $key));
                $product_attributes["pa_{$key}"] = array(
                    'name'         => wc_clean($attr_name),
                    'value'        => wc_clean($value),
                    'position'     => 0,
                    'is_visible'   => 1,
                    'is_variation' => 0,
                    'is_taxonomy'  => 0,
                );
            }
        }

        update_post_meta($new_product_id, '_product_attributes', $product_attributes);

        echo "Product created: " . get_the_title() . " (ID: $new_product_id)<br>";
    }

    wp_reset_postdata();
}

convert_cpt_to_woocommerce_products();
