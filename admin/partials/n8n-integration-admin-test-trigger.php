<?php

/**
 * Provide a admin area view for the plugin's test trigger functionality
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @since      1.0.0
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Get the available triggers
$available_triggers = array(
    'post_save' => array(
        'name' => __('Post Save', 'n8n-wordpress-integration'),
        'description' => __('Triggered when a post is created or updated', 'n8n-wordpress-integration'),
        'test_data' => array(
            'post_id' => 1,
            'title' => 'Test Post',
            'content' => 'This is a test post content.',
            'excerpt' => 'Test excerpt',
            'status' => 'publish',
            'type' => 'post',
            'author' => 1,
            'date' => current_time('mysql'),
            'modified' => current_time('mysql'),
            'url' => get_site_url() . '/?p=1',
            'is_update' => true,
            'meta' => array(
                'test_meta_key' => 'test_meta_value'
            ),
            'categories' => array(
                array(
                    'id' => 1,
                    'name' => 'Uncategorized',
                    'slug' => 'uncategorized'
                )
            ),
            'tags' => array()
        )
    ),
    'user_register' => array(
        'name' => __('User Register', 'n8n-wordpress-integration'),
        'description' => __('Triggered when a new user is registered', 'n8n-wordpress-integration'),
        'test_data' => array(
            'user_id' => 1,
            'username' => 'testuser',
            'email' => 'test@example.com',
            'first_name' => 'Test',
            'last_name' => 'User',
            'role' => 'subscriber',
            'registered_date' => current_time('mysql'),
            'meta' => array(
                'test_meta_key' => 'test_meta_value'
            )
        )
    ),
    'comment_post' => array(
        'name' => __('Comment Post', 'n8n-wordpress-integration'),
        'description' => __('Triggered when a new comment is posted', 'n8n-wordpress-integration'),
        'test_data' => array(
            'comment_id' => 1,
            'comment_post_id' => 1,
            'comment_author' => 'Test Commenter',
            'comment_author_email' => 'commenter@example.com',
            'comment_author_url' => 'https://example.com',
            'comment_content' => 'This is a test comment.',
            'comment_type' => 'comment',
            'comment_parent' => 0,
            'user_id' => 0,
            'comment_date' => current_time('mysql'),
            'comment_approved' => 1,
            'post_title' => 'Test Post'
        )
    )
);

// Add WooCommerce trigger if WooCommerce is active
if (class_exists('WooCommerce')) {
    $available_triggers['woocommerce_new_order'] = array(
        'name' => __('WooCommerce New Order', 'n8n-wordpress-integration'),
        'description' => __('Triggered when a new WooCommerce order is created', 'n8n-wordpress-integration'),
        'test_data' => array(
            'order_id' => 1,
            'order_number' => '1',
            'order_status' => 'processing',
            'order_total' => '99.99',
            'order_currency' => 'USD',
            'order_date' => current_time('mysql'),
            'payment_method' => 'cod',
            'payment_method_title' => 'Cash on Delivery',
            'customer' => array(
                'id' => 1,
                'first_name' => 'Test',
                'last_name' => 'Customer',
                'email' => 'customer@example.com',
                'phone' => '123-456-7890'
            ),
            'billing' => array(
                'first_name' => 'Test',
                'last_name' => 'Customer',
                'company' => 'Test Company',
                'address_1' => '123 Test St',
                'address_2' => 'Apt 4',
                'city' => 'Test City',
                'state' => 'TS',
                'postcode' => '12345',
                'country' => 'US',
                'email' => 'customer@example.com',
                'phone' => '123-456-7890'
            ),
            'shipping' => array(
                'first_name' => 'Test',
                'last_name' => 'Customer',
                'company' => 'Test Company',
                'address_1' => '123 Test St',
                'address_2' => 'Apt 4',
                'city' => 'Test City',
                'state' => 'TS',
                'postcode' => '12345',
                'country' => 'US'
            ),
            'line_items' => array(
                array(
                    'product_id' => 1,
                    'name' => 'Test Product',
                    'quantity' => 2,
                    'subtotal' => '79.98',
                    'total' => '79.98',
                    'price' => '39.99'
                )
            ),
            'shipping_lines' => array(
                array(
                    'method_id' => 'flat_rate',
                    'method_title' => 'Flat Rate',
                    'total' => '10.00'
                )
            ),
            'tax_lines' => array(
                array(
                    'rate_id' => 1,
                    'label' => 'Tax',
                    'compound' => false,
                    'tax_total' => '10.01'
                )
            )
        )
    );
}

// Get enabled triggers and webhook URLs
$enabled_triggers = get_option('n8n_integration_enabled_triggers', array());
$webhook_urls = get_option('n8n_integration_webhook_urls', array());

?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?> - <?php _e('Test Triggers', 'n8n-wordpress-integration'); ?></h1>
    
    <div class="notice notice-info">
        <p><?php _e('Use this page to test your n8n webhook triggers without having to perform the actual WordPress actions.', 'n8n-wordpress-integration'); ?></p>
    </div>
    
    <div class="card">
        <h2><?php _e('Test Trigger', 'n8n-wordpress-integration'); ?></h2>
        
        <form id="n8n-integration-test-trigger-form">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="trigger-type"><?php _e('Select Trigger', 'n8n-wordpress-integration'); ?></label>
                    </th>
                    <td>
                        <select id="trigger-type" name="trigger_type">
                            <?php foreach ($available_triggers as $trigger_id => $trigger) : ?>
                                <option value="<?php echo esc_attr($trigger_id); ?>" <?php echo !in_array($trigger_id, $enabled_triggers) ? 'disabled' : ''; ?>>
                                    <?php echo esc_html($trigger['name']); ?> <?php echo !in_array($trigger_id, $enabled_triggers) ? '(' . __('Not Enabled', 'n8n-wordpress-integration') . ')' : ''; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php _e('Select the trigger you want to test. Disabled options are triggers that are not currently enabled in your settings.', 'n8n-wordpress-integration'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="test-data"><?php _e('Test Data', 'n8n-wordpress-integration'); ?></label>
                    </th>
                    <td>
                        <textarea id="test-data" name="test_data" rows="10" class="large-text code"></textarea>
                        <p class="description"><?php _e('This is the data that will be sent to your n8n webhook. You can modify it if needed.', 'n8n-wordpress-integration'); ?></p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="submit" id="test-trigger" class="button button-primary"><?php _e('Send Test Trigger', 'n8n-wordpress-integration'); ?></button>
                <span class="spinner" style="float: none; margin-top: 0;"></span>
            </p>
        </form>
        
        <div id="test-result" style="display: none;">
            <h3><?php _e('Test Result', 'n8n-wordpress-integration'); ?></h3>
            <div id="test-result-content" class="code-example">
                <pre></pre>
            </div>
        </div>
    </div>
    
    <div class="card">
        <h2><?php _e('How to Use Test Triggers', 'n8n-wordpress-integration'); ?></h2>
        
        <ol>
            <li><?php _e('Make sure you have set up and enabled the trigger you want to test in the Triggers page.', 'n8n-wordpress-integration'); ?></li>
            <li><?php _e('Select the trigger type from the dropdown above.', 'n8n-wordpress-integration'); ?></li>
            <li><?php _e('Review and modify the test data if needed.', 'n8n-wordpress-integration'); ?></li>
            <li><?php _e('Click "Send Test Trigger" to send the data to your n8n webhook.', 'n8n-wordpress-integration'); ?></li>
            <li><?php _e('Check the result to see if the webhook was successfully delivered.', 'n8n-wordpress-integration'); ?></li>
        </ol>
        
        <p><?php _e('This is useful for testing your n8n workflows without having to perform the actual WordPress actions like creating posts or users.', 'n8n-wordpress-integration'); ?></p>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        // Update test data when trigger type changes
        $('#trigger-type').on('change', function() {
            var triggerId = $(this).val();
            var triggerData = <?php echo json_encode($available_triggers); ?>[triggerId];
            
            if (triggerData && triggerData.test_data) {
                $('#test-data').val(JSON.stringify(triggerData.test_data, null, 2));
            } else {
                $('#test-data').val('');
            }
        }).trigger('change');
        
        // Handle form submission
        $('#n8n-integration-test-trigger-form').on('submit', function(e) {
            e.preventDefault();
            
            var triggerId = $('#trigger-type').val();
            var testData = $('#test-data').val();
            
            // Show spinner
            $('#test-trigger').prop('disabled', true);
            $('#n8n-integration-test-trigger-form .spinner').addClass('is-active');
            
            // Hide previous results
            $('#test-result').hide();
            
            // Send test trigger
            $.ajax({
                url: n8n_integration_admin.ajax_url,
                method: 'POST',
                data: {
                    action: 'n8n_integration_test_trigger',
                    nonce: n8n_integration_admin.nonce,
                    trigger_id: triggerId,
                    test_data: testData
                },
                success: function(response) {
                    // Show result
                    $('#test-result').show();
                    $('#test-result-content pre').text(JSON.stringify(response, null, 2));
                    
                    // Hide spinner
                    $('#test-trigger').prop('disabled', false);
                    $('#n8n-integration-test-trigger-form .spinner').removeClass('is-active');
                },
                error: function(xhr, status, error) {
                    // Show error
                    $('#test-result').show();
                    $('#test-result-content pre').text('Error: ' + error);
                    
                    // Hide spinner
                    $('#test-trigger').prop('disabled', false);
                    $('#n8n-integration-test-trigger-form .spinner').removeClass('is-active');
                }
            });
        });
    });
</script>

<style type="text/css">
    .card {
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        margin-top: 20px;
        padding: 20px;
    }
    
    .code-example {
        background: #f6f7f7;
        border-radius: 4px;
        padding: 15px;
        margin: 15px 0;
        overflow-x: auto;
    }
    
    .code-example pre {
        margin: 0;
        white-space: pre-wrap;
    }
    
    #test-result-content {
        max-height: 400px;
        overflow-y: auto;
    }
</style>