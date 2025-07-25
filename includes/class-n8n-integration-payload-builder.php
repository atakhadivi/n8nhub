<?php

/**
 * The payload builder functionality of the plugin.
 *
 * Builds and enhances payloads for n8n webhooks
 *
 * @since      1.0.0
 */
class N8N_Integration_Payload_Builder {

    /**
     * Build a post payload with enhanced data.
     *
     * @since    1.0.0
     * @param    int       $post_id    The post ID.
     * @param    bool      $include_meta    Whether to include post meta data.
     * @param    bool      $include_taxonomies    Whether to include taxonomy data.
     * @param    bool      $include_author    Whether to include author data.
     * @return   array                The post data payload.
     */
    public static function build_post_payload($post_id, $include_meta = true, $include_taxonomies = true, $include_author = true) {
        $post = get_post($post_id);
        
        if (!$post) {
            return array();
        }
        
        // Basic post data
        $post_data = array(
            'id' => $post_id,
            'title' => $post->post_title,
            'content' => $post->post_content,
            'excerpt' => $post->post_excerpt,
            'status' => $post->post_status,
            'type' => $post->post_type,
            'date' => $post->post_date,
            'modified' => $post->post_modified,
            'url' => get_permalink($post_id),
            'slug' => $post->post_name,
            'guid' => $post->guid,
            'comment_status' => $post->comment_status,
            'ping_status' => $post->ping_status,
            'comment_count' => $post->comment_count,
        );
        
        // Add post meta
        if ($include_meta) {
            $post_meta = get_post_meta($post_id);
            if (!empty($post_meta)) {
                $post_data['meta'] = array();
                foreach ($post_meta as $meta_key => $meta_values) {
                    $post_data['meta'][$meta_key] = count($meta_values) === 1 ? $meta_values[0] : $meta_values;
                }
            }
        }
        
        // Add taxonomies (categories, tags, etc.)
        if ($include_taxonomies) {
            $taxonomies = get_object_taxonomies($post->post_type);
            if (!empty($taxonomies)) {
                $post_data['taxonomies'] = array();
                
                foreach ($taxonomies as $taxonomy) {
                    $terms = get_the_terms($post_id, $taxonomy);
                    if (!empty($terms) && !is_wp_error($terms)) {
                        $post_data['taxonomies'][$taxonomy] = array();
                        foreach ($terms as $term) {
                            $post_data['taxonomies'][$taxonomy][] = array(
                                'id' => $term->term_id,
                                'name' => $term->name,
                                'slug' => $term->slug,
                                'description' => $term->description,
                            );
                        }
                    }
                }
                
                // Add specific arrays for common taxonomies for convenience
                if (isset($post_data['taxonomies']['category'])) {
                    $post_data['categories'] = $post_data['taxonomies']['category'];
                }
                
                if (isset($post_data['taxonomies']['post_tag'])) {
                    $post_data['tags'] = $post_data['taxonomies']['post_tag'];
                }
            }
        }
        
        // Add author data
        if ($include_author && $post->post_author) {
            $author = get_userdata($post->post_author);
            if ($author) {
                $post_data['author'] = array(
                    'id' => $author->ID,
                    'username' => $author->user_login,
                    'email' => $author->user_email,
                    'display_name' => $author->display_name,
                    'first_name' => $author->first_name,
                    'last_name' => $author->last_name,
                    'url' => $author->user_url,
                    'registered_date' => $author->user_registered,
                    'description' => $author->description,
                    'roles' => $author->roles,
                );
            }
        } else {
            $post_data['author_id'] = $post->post_author;
        }
        
        // Add featured image if exists
        if (has_post_thumbnail($post_id)) {
            $thumbnail_id = get_post_thumbnail_id($post_id);
            $thumbnail = wp_get_attachment_image_src($thumbnail_id, 'full');
            
            if ($thumbnail) {
                $post_data['featured_image'] = array(
                    'id' => $thumbnail_id,
                    'url' => $thumbnail[0],
                    'width' => $thumbnail[1],
                    'height' => $thumbnail[2],
                    'alt' => get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true),
                );
            }
        }
        
        return $post_data;
    }
    
    /**
     * Build a user payload with enhanced data.
     *
     * @since    1.0.0
     * @param    int       $user_id    The user ID.
     * @param    bool      $include_meta    Whether to include user meta data.
     * @return   array                The user data payload.
     */
    public static function build_user_payload($user_id, $include_meta = true) {
        $user = get_userdata($user_id);
        
        if (!$user) {
            return array();
        }
        
        // Basic user data
        $user_data = array(
            'id' => $user->ID,
            'username' => $user->user_login,
            'email' => $user->user_email,
            'display_name' => $user->display_name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'url' => $user->user_url,
            'registered_date' => $user->user_registered,
            'description' => $user->description,
            'roles' => $user->roles,
        );
        
        // Add user meta
        if ($include_meta) {
            $user_meta = get_user_meta($user_id);
            if (!empty($user_meta)) {
                $user_data['meta'] = array();
                foreach ($user_meta as $meta_key => $meta_values) {
                    // Skip sensitive data
                    if (in_array($meta_key, array('user_pass', 'session_tokens', 'wp_capabilities'))) {
                        continue;
                    }
                    $user_data['meta'][$meta_key] = count($meta_values) === 1 ? $meta_values[0] : $meta_values;
                }
            }
        }
        
        return $user_data;
    }
    
    /**
     * Build a comment payload with enhanced data.
     *
     * @since    1.0.0
     * @param    int       $comment_id    The comment ID.
     * @param    bool      $include_meta    Whether to include comment meta data.
     * @return   array                The comment data payload.
     */
    public static function build_comment_payload($comment_id, $include_meta = true) {
        $comment = get_comment($comment_id);
        
        if (!$comment) {
            return array();
        }
        
        // Get the post title
        $post_title = get_the_title($comment->comment_post_ID);
        
        // Basic comment data
        $comment_data = array(
            'id' => $comment->comment_ID,
            'post_id' => $comment->comment_post_ID,
            'post_title' => $post_title,
            'author' => $comment->comment_author,
            'author_email' => $comment->comment_author_email,
            'author_url' => $comment->comment_author_url,
            'author_ip' => $comment->comment_author_IP,
            'content' => $comment->comment_content,
            'status' => $comment->comment_approved,
            'type' => $comment->comment_type,
            'parent' => $comment->comment_parent,
            'user_id' => $comment->user_id,
            'date' => $comment->comment_date,
            'date_gmt' => $comment->comment_date_gmt,
            'permalink' => get_comment_link($comment_id),
        );
        
        // Add comment meta
        if ($include_meta) {
            $comment_meta = get_comment_meta($comment_id);
            if (!empty($comment_meta)) {
                $comment_data['meta'] = array();
                foreach ($comment_meta as $meta_key => $meta_values) {
                    $comment_data['meta'][$meta_key] = count($meta_values) === 1 ? $meta_values[0] : $meta_values;
                }
            }
        }
        
        return $comment_data;
    }
    
    /**
     * Build a WooCommerce order payload with enhanced data.
     *
     * @since    1.0.0
     * @param    int       $order_id    The order ID.
     * @return   array                The order data payload.
     */
    public static function build_woocommerce_order_payload($order_id) {
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            return array();
        }
        
        $order = wc_get_order($order_id);
        
        if (!$order) {
            return array();
        }
        
        // Basic order data
        $order_data = array(
            'id' => $order->get_id(),
            'order_number' => $order->get_order_number(),
            'created_at' => $order->get_date_created() ? $order->get_date_created()->date('Y-m-d H:i:s') : '',
            'updated_at' => $order->get_date_modified() ? $order->get_date_modified()->date('Y-m-d H:i:s') : '',
            'completed_at' => $order->get_date_completed() ? $order->get_date_completed()->date('Y-m-d H:i:s') : '',
            'status' => $order->get_status(),
            'currency' => $order->get_currency(),
            'total' => $order->get_total(),
            'subtotal' => $order->get_subtotal(),
            'total_tax' => $order->get_total_tax(),
            'total_discount' => $order->get_total_discount(),
            'shipping_total' => $order->get_shipping_total(),
            'shipping_tax' => $order->get_shipping_tax(),
            'cart_tax' => $order->get_cart_tax(),
            'payment_method' => $order->get_payment_method(),
            'payment_method_title' => $order->get_payment_method_title(),
            'transaction_id' => $order->get_transaction_id(),
            'customer_ip_address' => $order->get_customer_ip_address(),
            'customer_user_agent' => $order->get_customer_user_agent(),
            'customer_note' => $order->get_customer_note(),
            'billing' => array(
                'first_name' => $order->get_billing_first_name(),
                'last_name' => $order->get_billing_last_name(),
                'company' => $order->get_billing_company(),
                'address_1' => $order->get_billing_address_1(),
                'address_2' => $order->get_billing_address_2(),
                'city' => $order->get_billing_city(),
                'state' => $order->get_billing_state(),
                'postcode' => $order->get_billing_postcode(),
                'country' => $order->get_billing_country(),
                'email' => $order->get_billing_email(),
                'phone' => $order->get_billing_phone(),
            ),
            'shipping' => array(
                'first_name' => $order->get_shipping_first_name(),
                'last_name' => $order->get_shipping_last_name(),
                'company' => $order->get_shipping_company(),
                'address_1' => $order->get_shipping_address_1(),
                'address_2' => $order->get_shipping_address_2(),
                'city' => $order->get_shipping_city(),
                'state' => $order->get_shipping_state(),
                'postcode' => $order->get_shipping_postcode(),
                'country' => $order->get_shipping_country(),
            ),
        );
        
        // Add customer data
        $customer_id = $order->get_customer_id();
        if ($customer_id) {
            $customer = new WC_Customer($customer_id);
            $order_data['customer'] = array(
                'id' => $customer_id,
                'email' => $customer->get_email(),
                'first_name' => $customer->get_first_name(),
                'last_name' => $customer->get_last_name(),
                'username' => $customer->get_username(),
                'date_created' => $customer->get_date_created() ? $customer->get_date_created()->date('Y-m-d H:i:s') : '',
                'date_modified' => $customer->get_date_modified() ? $customer->get_date_modified()->date('Y-m-d H:i:s') : '',
                'role' => $customer->get_role(),
                'is_paying_customer' => $customer->get_is_paying_customer(),
                'orders_count' => $customer->get_order_count(),
                'total_spent' => $customer->get_total_spent(),
                'avatar_url' => $customer->get_avatar_url(),
            );
        }
        
        // Add line items
        $order_data['line_items'] = array();
        foreach ($order->get_items() as $item_id => $item) {
            $product = $item->get_product();
            $product_data = array(
                'id' => $item->get_id(),
                'name' => $item->get_name(),
                'product_id' => $item->get_product_id(),
                'variation_id' => $item->get_variation_id(),
                'quantity' => $item->get_quantity(),
                'tax_class' => $item->get_tax_class(),
                'subtotal' => $item->get_subtotal(),
                'subtotal_tax' => $item->get_subtotal_tax(),
                'total' => $item->get_total(),
                'total_tax' => $item->get_total_tax(),
                'taxes' => $item->get_taxes(),
            );
            
            // Add product data if available
            if ($product) {
                $product_data['sku'] = $product->get_sku();
                $product_data['price'] = $product->get_price();
                $product_data['regular_price'] = $product->get_regular_price();
                $product_data['sale_price'] = $product->get_sale_price();
                $product_data['permalink'] = $product->get_permalink();
                $product_data['stock_quantity'] = $product->get_stock_quantity();
                $product_data['stock_status'] = $product->get_stock_status();
                $product_data['weight'] = $product->get_weight();
                $product_data['dimensions'] = array(
                    'length' => $product->get_length(),
                    'width' => $product->get_width(),
                    'height' => $product->get_height(),
                );
                $product_data['categories'] = array();
                $categories = get_the_terms($product->get_id(), 'product_cat');
                if (!empty($categories) && !is_wp_error($categories)) {
                    foreach ($categories as $category) {
                        $product_data['categories'][] = array(
                            'id' => $category->term_id,
                            'name' => $category->name,
                            'slug' => $category->slug,
                        );
                    }
                }
            }
            
            $order_data['line_items'][] = $product_data;
        }
        
        // Add shipping lines
        $order_data['shipping_lines'] = array();
        foreach ($order->get_shipping_methods() as $shipping_id => $shipping) {
            $order_data['shipping_lines'][] = array(
                'id' => $shipping_id,
                'method_id' => $shipping->get_method_id(),
                'method_title' => $shipping->get_method_title(),
                'total' => $shipping->get_total(),
                'total_tax' => $shipping->get_total_tax(),
                'taxes' => $shipping->get_taxes(),
            );
        }
        
        // Add tax lines
        $order_data['tax_lines'] = array();
        foreach ($order->get_tax_totals() as $tax_code => $tax) {
            $order_data['tax_lines'][] = array(
                'id' => $tax->id,
                'rate_id' => $tax->rate_id,
                'code' => $tax_code,
                'title' => $tax->label,
                'total' => $tax->amount,
                'compound' => $tax->is_compound,
            );
        }
        
        // Add fee lines
        $order_data['fee_lines'] = array();
        foreach ($order->get_fees() as $fee_id => $fee) {
            $order_data['fee_lines'][] = array(
                'id' => $fee_id,
                'name' => $fee->get_name(),
                'tax_class' => $fee->get_tax_class(),
                'tax_status' => $fee->get_tax_status(),
                'total' => $fee->get_total(),
                'total_tax' => $fee->get_total_tax(),
                'taxes' => $fee->get_taxes(),
            );
        }
        
        // Add coupon lines
        $order_data['coupon_lines'] = array();
        foreach ($order->get_coupon_codes() as $coupon_code) {
            $coupon = new WC_Coupon($coupon_code);
            $order_data['coupon_lines'][] = array(
                'code' => $coupon_code,
                'discount' => $order->get_discount_total(),
                'discount_tax' => $order->get_discount_tax(),
            );
        }
        
        // Add order notes
        $order_data['notes'] = array();
        $notes = wc_get_order_notes(array('order_id' => $order_id));
        foreach ($notes as $note) {
            $order_data['notes'][] = array(
                'id' => $note->id,
                'author' => $note->added_by,
                'date' => $note->date_created->date('Y-m-d H:i:s'),
                'content' => $note->content,
                'is_customer_note' => $note->customer_note,
            );
        }
        
        // Add order meta
        $order_data['meta_data'] = array();
        foreach ($order->get_meta_data() as $meta) {
            $order_data['meta_data'][] = array(
                'id' => $meta->id,
                'key' => $meta->key,
                'value' => $meta->value,
            );
        }
        
        return $order_data;
    }
    
    /**
     * Build a test payload for a specific trigger.
     *
     * @since    1.0.0
     * @param    string    $trigger    The trigger type.
     * @param    array     $test_data  The test data provided by the user.
     * @return   array                 The enhanced test data payload.
     */
    public static function build_test_payload($trigger, $test_data) {
        // If test data is not an array, initialize it
        if (!is_array($test_data)) {
            $test_data = array();
        }
        
        // If test data already contains an ID, use it to fetch real data
        if (isset($test_data['id']) && !empty($test_data['id'])) {
            $id = absint($test_data['id']);
            switch ($trigger) {
                case 'post_save':
                    $post = get_post($id);
                    if ($post) {
                        $enhanced_data = self::build_post_payload($id);
                        // Preserve is_update flag if it exists
                        if (isset($test_data['is_update'])) {
                            $enhanced_data['is_update'] = (bool) $test_data['is_update'];
                        }
                        return $enhanced_data;
                    }
                    break;
                    
                case 'user_register':
                    if (get_userdata($id)) {
                        return self::build_user_payload($id);
                    }
                    break;
                    
                case 'comment_post':
                    $comment = get_comment($id);
                    if ($comment) {
                        $enhanced_data = self::build_comment_payload($id);
                        // Preserve comment_approved flag if it exists
                        if (isset($test_data['comment_approved'])) {
                            $enhanced_data['comment_approved'] = $test_data['comment_approved'];
                        }
                        return $enhanced_data;
                    }
                    break;
                    
                case 'woocommerce_new_order':
                    if (class_exists('WooCommerce')) {
                        $order = wc_get_order($id);
                        if ($order) {
                            return self::build_woocommerce_order_payload($id);
                        }
                    }
                    break;
            }
        }
        
        // If no ID or couldn't fetch real data, return the test data as is
        return $test_data;
    }
}