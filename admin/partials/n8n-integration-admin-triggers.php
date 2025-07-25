<?php

/**
 * Provide a admin area view for the plugin's triggers configuration
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @since      1.0.0
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Get current settings
$n8n_url = get_option('n8n_integration_url', '');
$enabled_triggers = get_option('n8n_integration_enabled_triggers', array());
$webhook_urls = get_option('n8n_integration_webhook_urls', array());

// Convert old format webhook URLs to new format if needed
foreach ($webhook_urls as $trigger_id => $webhook_data) {
    if (is_string($webhook_data)) {
        $webhook_urls[$trigger_id] = array(
            'url' => $webhook_data,
            'name' => '',
            'description' => ''
        );
    }
}

// Define available triggers
$available_triggers = array(
    'post_save' => array(
        'name' => __('Post Save', 'n8n-integration'),
        'description' => __('Triggered when a post is created or updated', 'n8n-integration'),
        'webhook_data' => isset($webhook_urls['post_save']) ? $webhook_urls['post_save'] : array('url' => '', 'name' => '', 'description' => ''),
        'enabled' => in_array('post_save', $enabled_triggers),
    ),
    'user_register' => array(
        'name' => __('User Register', 'n8n-integration'),
        'description' => __('Triggered when a new user is registered', 'n8n-integration'),
        'webhook_data' => isset($webhook_urls['user_register']) ? $webhook_urls['user_register'] : array('url' => '', 'name' => '', 'description' => ''),
        'enabled' => in_array('user_register', $enabled_triggers),
    ),
    'comment_post' => array(
        'name' => __('Comment Post', 'n8n-integration'),
        'description' => __('Triggered when a new comment is posted', 'n8n-integration'),
        'webhook_data' => isset($webhook_urls['comment_post']) ? $webhook_urls['comment_post'] : array('url' => '', 'name' => '', 'description' => ''),
        'enabled' => in_array('comment_post', $enabled_triggers),
    ),
);

// Add custom post type triggers
// Get all registered post types except built-in ones
// We'll still keep the default 'post_save' trigger for backward compatibility
$post_types = get_post_types(array('_builtin' => false), 'objects');

foreach ($post_types as $post_type) {
    $trigger_id = 'post_save_' . $post_type->name;
    $available_triggers[$trigger_id] = array(
        'name' => sprintf(__('%s Save', 'n8n-integration'), $post_type->labels->singular_name),
        'description' => sprintf(__('Triggered when a %s is created or updated', 'n8n-integration'), strtolower($post_type->labels->singular_name)),
        'webhook_data' => isset($webhook_urls[$trigger_id]) ? $webhook_urls[$trigger_id] : array('url' => '', 'name' => '', 'description' => ''),
        'enabled' => in_array($trigger_id, $enabled_triggers),
        'post_type' => $post_type->name,
    );
}

// Add WooCommerce trigger if WooCommerce is active
if (class_exists('WooCommerce')) {
    $available_triggers['woocommerce_new_order'] = array(
        'name' => __('WooCommerce New Order', 'n8n-wordpress-integration'),
        'description' => __('Triggered when a new WooCommerce order is created', 'n8n-wordpress-integration'),
        'webhook_data' => isset($webhook_urls['woocommerce_new_order']) ? $webhook_urls['woocommerce_new_order'] : array('url' => '', 'name' => '', 'description' => ''),
        'enabled' => in_array('woocommerce_new_order', $enabled_triggers),
    );
}

?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?> - <?php _e('Triggers', 'n8n-wordpress-integration'); ?></h1>
    
    <div class="notice notice-info">
        <p><?php _e('Configure which WordPress events should trigger n8n workflows.', 'n8n-wordpress-integration'); ?></p>
    </div>
    
    <?php if (empty($n8n_url)) : ?>
        <div class="notice notice-warning">
            <p><?php _e('Please configure your n8n URL in the Settings tab before setting up triggers.', 'n8n-wordpress-integration'); ?></p>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <h2><?php _e('Available Triggers', 'n8n-wordpress-integration'); ?></h2>
        
        <form id="n8n-integration-triggers-form">
            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php _e('Enable', 'n8n-wordpress-integration'); ?></th>
                        <th><?php _e('Trigger', 'n8n-wordpress-integration'); ?></th>
                        <th><?php _e('Description', 'n8n-wordpress-integration'); ?></th>
                        <th><?php _e('n8n Webhook URL', 'n8n-wordpress-integration'); ?></th>
                        <th><?php _e('Webhook Name', 'n8n-wordpress-integration'); ?></th>
                        <th><?php _e('Webhook Description', 'n8n-wordpress-integration'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($available_triggers as $trigger_id => $trigger) : ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="enabled_triggers[]" id="trigger-<?php echo esc_attr($trigger_id); ?>" value="<?php echo esc_attr($trigger_id); ?>" <?php checked($trigger['enabled']); ?>>
                            </td>
                            <td>
                                <label for="trigger-<?php echo esc_attr($trigger_id); ?>"><?php echo esc_html($trigger['name']); ?></label>
                            </td>
                            <td>
                                <?php echo esc_html($trigger['description']); ?>
                            </td>
                            <td>
                                <input type="url" name="webhook_urls[<?php echo esc_attr($trigger_id); ?>][url]" value="<?php echo esc_attr($trigger['webhook_data']['url']); ?>" class="regular-text" placeholder="<?php echo esc_attr(sprintf(__('e.g., %s', 'n8n-wordpress-integration'), $n8n_url . '/webhook/123456')); ?>">
                            </td>
                            <td>
                                <input type="text" name="webhook_urls[<?php echo esc_attr($trigger_id); ?>][name]" value="<?php echo esc_attr($trigger['webhook_data']['name']); ?>" class="regular-text" placeholder="<?php _e('Webhook name (optional)', 'n8n-wordpress-integration'); ?>">
                            </td>
                            <td>
                                <input type="text" name="webhook_urls[<?php echo esc_attr($trigger_id); ?>][description]" value="<?php echo esc_attr($trigger['webhook_data']['description']); ?>" class="regular-text" placeholder="<?php _e('Webhook description (optional)', 'n8n-wordpress-integration'); ?>">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <p class="description"><?php _e('For each enabled trigger, enter the webhook URL from your n8n workflow that should be called when the event occurs.', 'n8n-wordpress-integration'); ?></p>
            
            <p class="submit">
                <button type="submit" id="save-triggers" class="button button-primary"><?php _e('Save Triggers', 'n8n-wordpress-integration'); ?></button>
            </p>
        </form>
    </div>
    
    <div class="card">
        <h2><?php _e('How to Use Triggers', 'n8n-wordpress-integration'); ?></h2>
        
        <ol>
            <li><?php _e('Create a new workflow in n8n.', 'n8n-wordpress-integration'); ?></li>
            <li><?php _e('Add a "Webhook" node as a trigger.', 'n8n-wordpress-integration'); ?></li>
            <li><?php _e('Configure the webhook to receive HTTP POST requests.', 'n8n-wordpress-integration'); ?></li>
            <li><?php _e('Copy the webhook URL from n8n.', 'n8n-wordpress-integration'); ?></li>
            <li><?php _e('Paste the URL in the corresponding field above and enable the trigger.', 'n8n-wordpress-integration'); ?></li>
            <li><?php _e('Save the triggers configuration.', 'n8n-wordpress-integration'); ?></li>
            <li><?php _e('Test your workflow by triggering the event in WordPress.', 'n8n-wordpress-integration'); ?></li>
        </ol>
        
        <p><?php _e('When the selected WordPress events occur, data will be sent to your n8n workflow via the webhook URL.', 'n8n-wordpress-integration'); ?></p>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        // Save triggers
        $('#n8n-integration-triggers-form').on('submit', function(e) {
            e.preventDefault();
            
            var formData = $(this).serializeArray();
            var data = {
                enabled_triggers: [],
                webhook_urls: {}
            };
            
            // Process form data
            $.each(formData, function(i, field) {
                if (field.name === 'enabled_triggers[]') {
                    data.enabled_triggers.push(field.value);
                } else if (field.name.startsWith('webhook_urls[')) {
                    // Extract trigger ID and field type (url, name, description)
                    var matches = field.name.match(/webhook_urls\[(.*?)\]\[(.*?)\]/);
                    if (matches && matches[1] && matches[2]) {
                        var triggerId = matches[1];
                        var fieldType = matches[2];
                        
                        // Initialize webhook data object if it doesn't exist
                        if (!data.webhook_urls[triggerId]) {
                            data.webhook_urls[triggerId] = {
                                url: '',
                                name: '',
                                description: ''
                            };
                        }
                        
                        // Set the appropriate field
                        data.webhook_urls[triggerId][fieldType] = field.value;
                    }
                }
            });
            
            $.ajax({
                url: n8n_integration_admin.rest_url + 'settings',
                method: 'POST',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', n8n_integration_admin.rest_nonce);
                },
                data: data,
                success: function(response) {
                    if (response.success) {
                        alert('<?php _e("Triggers saved successfully.", "n8n-wordpress-integration"); ?>');
                    } else {
                        alert('<?php _e("Failed to save triggers.", "n8n-wordpress-integration"); ?>');
                    }
                },
                error: function() {
                    alert('<?php _e("Failed to save triggers.", "n8n-wordpress-integration"); ?>');
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
    
    .widefat {
        margin-top: 10px;
        margin-bottom: 20px;
        table-layout: fixed;
        width: 100%;
    }
    
    .widefat th:nth-child(1) {
        width: 5%;
    }
    
    .widefat th:nth-child(2) {
        width: 10%;
    }
    
    .widefat th:nth-child(3) {
        width: 15%;
    }
    
    .widefat th:nth-child(4) {
        width: 30%;
    }
    
    .widefat th:nth-child(5) {
        width: 20%;
    }
    
    .widefat th:nth-child(6) {
        width: 20%;
    }
    
    .widefat input[type="url"],
    .widefat input[type="text"] {
        width: 100%;
    }
</style>