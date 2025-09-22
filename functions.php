<?php
// Add to cart handler with images
add_action('wp_ajax_custom_add_to_cart', 'custom_add_to_cart');
add_action('wp_ajax_nopriv_custom_add_to_cart', 'custom_add_to_cart');

function custom_add_to_cart() {
    if (!isset($_POST['product_id'])) {
        wp_send_json_error(['message' => 'Invalid request.']);
    }

    $product_id  = intval($_POST['product_id']);
    $condition   = sanitize_text_field($_POST['condition'] ?? '');
    $carrier     = sanitize_text_field($_POST['carrier'] ?? '');
    $storage     = sanitize_text_field($_POST['storage'] ?? '');
    $price       = floatval($_POST['price'] ?? 0);
    $accessories = array_map('sanitize_text_field', $_POST['accessories'] ?? []);

    // Handle uploaded images
    $uploaded_images = [];
    if (!empty($_FILES['phone_images'])) {
        $files = $_FILES['phone_images'];

        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        foreach ($files['name'] as $index => $name) {
            if ($files['error'][$index] === 0) {
                $file_array = [
                    'name'     => $files['name'][$index],
                    'type'     => $files['type'][$index],
                    'tmp_name' => $files['tmp_name'][$index],
                    'error'    => $files['error'][$index],
                    'size'     => $files['size'][$index],
                ];

                // Upload to WP Media Library
                $upload = wp_handle_sideload($file_array, ['test_form' => false]);

                if (!isset($upload['error']) && isset($upload['file'])) {
                    $attachment = [
                        'post_mime_type' => $upload['type'],
                        'post_title'     => sanitize_file_name($name),
                        'post_content'   => '',
                        'post_status'    => 'inherit'
                    ];

                    $attach_id = wp_insert_attachment($attachment, $upload['file']);
                    $attach_data = wp_generate_attachment_metadata($attach_id, $upload['file']);
                    wp_update_attachment_metadata($attach_id, $attach_data);

                    $uploaded_images[] = $attach_id;
                } else {
                    error_log("❌ Image upload error: " . $upload['error']);
                }
            }
        }
    }

    $cart_item_data = [
        'condition'    => $condition,
        'carrier'      => $carrier,
        'storage'      => $storage,
        'accessories'  => $accessories,
        'custom_price' => $price,
        'images'       => $uploaded_images
    ];

    try {
        $added = WC()->cart->add_to_cart($product_id, 1, 0, [], $cart_item_data);

        if ($added) {
            wp_send_json_success([
                'message' => 'Product added to cart.',
                'data'    => $cart_item_data
            ]);
        } else {
            wp_send_json_error(['message' => 'Could not add product to cart.']);
        }
    } catch (Exception $e) {
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}

// Show custom meta in cart, including images
add_filter('woocommerce_get_item_data', function($item_data, $cart_item){
    if(isset($cart_item['condition'])){
        $item_data[] = ['name'=>'Condition','value'=>$cart_item['condition']];
    }
    if(isset($cart_item['carrier'])){
        $item_data[] = ['name'=>'Carrier','value'=>$cart_item['carrier']];
    }
    if(isset($cart_item['storage'])){
        $item_data[] = ['name'=>'Storage','value'=>$cart_item['storage']];
    }
    if(!empty($cart_item['accessories'])){
        $item_data[] = ['name'=>'Accessories','value'=>implode(", ", $cart_item['accessories'])];
    }

    if(!empty($cart_item['images'])){
        $image_names = [];
        foreach($cart_item['images'] as $img_id){
            $title = get_the_title($img_id);
            $short_title = (strlen($title) > 15) ? substr($title,0,15).'...' : $title;
            $image_names[] = $short_title;
        }

        $names_html = '<div class="cart-uploaded-image-names" style="display:flex;flex-direction:column;gap:4px;">';
        foreach($image_names as $name){
            $names_html .= '<span style="background:#f9f9f9;color:#333;padding:4px 6px;border-radius:4px;font-size:13px;box-shadow:0 1px 2px rgba(0,0,0,0.1);margin-right: 10px; box-shadow: 1px 1px 5px #1111111f">'
                         . esc_html($name) .
                          '</span>';
        }
        $names_html .= '</div>';
        $item_data[] = ['name'=>'Uploaded Images','value'=>$names_html];
    }

    return $item_data;
}, 10, 2);






// Override price
add_action('woocommerce_before_calculate_totals', function($cart){
    foreach($cart->get_cart() as $cart_item){
        if(isset($cart_item['custom_price'])){
            $cart_item['data']->set_price($cart_item['custom_price']);
        }
    }
});

// Save images to order item meta
add_action('woocommerce_checkout_create_order_line_item', function($item, $cart_item_key, $values, $order){
    if(isset($values['images'])){
        $item->add_meta_data('_uploaded_images', $values['images']);
    }
}, 10, 4);