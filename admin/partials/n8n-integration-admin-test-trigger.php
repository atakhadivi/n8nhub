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

// Load the payload builder
require_once plugin_dir_path(dirname(dirname(__FILE__))) . 'includes/class-n8n-integration-payload-builder.php';

// Get the available triggers
$available_triggers = array(
    'post_save' => array(
        'name' => __('Post Save', 'n8n-wordpress-integration'),
        'description' => __('Triggered when a post is created or updated', 'n8n-wordpress-integration')
    ),
    'user_register' => array(
        'name' => __('User Register', 'n8n-wordpress-integration'),
        'description' => __('Triggered when a new user is registered', 'n8n-wordpress-integration')
    ),
    'comment_post' => array(
        'name' => __('Comment Post', 'n8n-wordpress-integration'),
        'description' => __('Triggered when a new comment is posted', 'n8n-wordpress-integration')
    )
);

// Add WooCommerce trigger if WooCommerce is active
if (class_exists('WooCommerce')) {
    $available_triggers['woocommerce_new_order'] = array(
        'name' => __('WooCommerce New Order', 'n8n-wordpress-integration'),
        'description' => __('Triggered when a new WooCommerce order is created', 'n8n-wordpress-integration')
    );
}

// Get a recent post for testing if available
$recent_posts = get_posts(array('numberposts' => 1));
$post_id = !empty($recent_posts) ? $recent_posts[0]->ID : 1;

// Get a recent user for testing if available
$recent_users = get_users(array('number' => 1, 'orderby' => 'ID', 'order' => 'DESC'));
$user_id = !empty($recent_users) ? $recent_users[0]->ID : 1;

// Get a recent comment for testing if available
$recent_comments = get_comments(array('number' => 1));
$comment_id = !empty($recent_comments) ? $recent_comments[0]->comment_ID : 1;

// Get a recent order for testing if available
$order_id = 1;
if (class_exists('WooCommerce')) {
    $recent_orders = wc_get_orders(array('limit' => 1));
    $order_id = !empty($recent_orders) ? $recent_orders[0]->get_id() : 1;
}

// Generate test data using payload builder
foreach ($available_triggers as $trigger_id => &$trigger) {
    switch ($trigger_id) {
        case 'post_save':
            $trigger['test_data'] = N8N_Integration_Payload_Builder::build_post_payload($post_id);
            $trigger['test_data']['is_update'] = true;
            break;
        case 'user_register':
            $trigger['test_data'] = N8N_Integration_Payload_Builder::build_user_payload($user_id);
            break;
        case 'comment_post':
            $trigger['test_data'] = N8N_Integration_Payload_Builder::build_comment_payload($comment_id);
            break;
        case 'woocommerce_new_order':
            $trigger['test_data'] = N8N_Integration_Payload_Builder::build_woocommerce_order_payload($order_id);
            break;
    }
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
                    trigger: triggerId,
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