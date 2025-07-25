<?php

/**
 * Provide a admin area view for the plugin's actions configuration
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @since      1.0.0
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Get the site URL for the webhook endpoint
$webhook_url = rest_url('n8n-integration/v1/webhook');
$api_key = get_option('n8n_integration_api_key', '');

?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?> - <?php _e('Actions', 'n8n-wordpress-integration'); ?></h1>
    
    <div class="notice notice-info">
        <p><?php _e('Learn how to perform actions in WordPress from your n8n workflows.', 'n8n-wordpress-integration'); ?></p>
    </div>
    
    <div class="card">
        <h2><?php _e('Webhook Endpoint', 'n8n-wordpress-integration'); ?></h2>
        
        <p><?php _e('Use the following webhook URL in your n8n workflows to send data to WordPress:', 'n8n-wordpress-integration'); ?></p>
        
        <div class="webhook-url-container">
            <code id="webhook-url"><?php echo esc_url($webhook_url); ?></code>
            <button type="button" id="copy-webhook-url" class="button button-secondary"><?php _e('Copy', 'n8n-wordpress-integration'); ?></button>
        </div>
        
        <p class="description"><?php _e('Remember to include the API key in the request header as X-N8N-API-KEY.', 'n8n-wordpress-integration'); ?></p>
        
        <div class="api-key-container">
            <p><strong><?php _e('API Key:', 'n8n-wordpress-integration'); ?></strong></p>
            <code id="api-key"><?php echo esc_html($api_key); ?></code>
            <button type="button" id="copy-api-key" class="button button-secondary"><?php _e('Copy', 'n8n-wordpress-integration'); ?></button>
        </div>
    </div>
    
    <div class="card">
        <h2><?php _e('Available Actions', 'n8n-wordpress-integration'); ?></h2>
        
        <p><?php _e('You can use the webhook URL with the following actions:', 'n8n-wordpress-integration'); ?></p>
        
        <table class="widefat">
            <thead>
                <tr>
                    <th><?php _e('Action', 'n8n-wordpress-integration'); ?></th>
                    <th><?php _e('Description', 'n8n-wordpress-integration'); ?></th>
                    <th><?php _e('Required Parameters', 'n8n-wordpress-integration'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>create_post</code></td>
                    <td><?php _e('Create a new post or custom post type', 'n8n-wordpress-integration'); ?></td>
                    <td><code>title</code>, <code>content</code></td>
                </tr>
                <tr>
                    <td><code>update_post</code></td>
                    <td><?php _e('Update an existing post', 'n8n-wordpress-integration'); ?></td>
                    <td><code>post_id</code></td>
                </tr>
                <tr>
                    <td><code>delete_post</code></td>
                    <td><?php _e('Delete a post', 'n8n-wordpress-integration'); ?></td>
                    <td><code>post_id</code></td>
                </tr>
                <tr>
                    <td><code>create_user</code></td>
                    <td><?php _e('Create a new user', 'n8n-wordpress-integration'); ?></td>
                    <td><code>username</code>, <code>email</code>, <code>password</code></td>
                </tr>
                <tr>
                    <td><code>update_user</code></td>
                    <td><?php _e('Update an existing user', 'n8n-wordpress-integration'); ?></td>
                    <td><code>user_id</code></td>
                </tr>
                <tr>
                    <td><code>custom_action</code></td>
                    <td><?php _e('Execute a custom action', 'n8n-wordpress-integration'); ?></td>
                    <td><code>custom_action_type</code></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="card">
        <h2><?php _e('How to Use Actions', 'n8n-wordpress-integration'); ?></h2>
        
        <h3><?php _e('Example: Creating a Post from n8n', 'n8n-wordpress-integration'); ?></h3>
        
        <ol>
            <li><?php _e('In your n8n workflow, add an "HTTP Request" node.', 'n8n-wordpress-integration'); ?></li>
            <li><?php _e('Set the request method to POST.', 'n8n-wordpress-integration'); ?></li>
            <li><?php _e('Set the URL to your webhook endpoint:', 'n8n-wordpress-integration'); ?> <code><?php echo esc_url($webhook_url); ?></code></li>
            <li><?php _e('Add a header with key "X-N8N-API-KEY" and value of your API key.', 'n8n-wordpress-integration'); ?></li>
            <li><?php _e('Set the "Content-Type" header to "application/json".', 'n8n-wordpress-integration'); ?></li>
            <li><?php _e('In the request body, include the action and required parameters:', 'n8n-wordpress-integration'); ?></li>
        </ol>
        
        <div class="code-example">
            <pre>{
  "action": "create_post",
  "title": "Post Title",
  "content": "Post content goes here.",
  "status": "publish",
  "post_type": "post",
  "meta": {
    "custom_field": "custom value"
  },
  "categories": [1, 2],
  "tags": ["tag1", "tag2"]
}</pre>
        </div>
        
        <h3><?php _e('Example: Updating a User from n8n', 'n8n-wordpress-integration'); ?></h3>
        
        <div class="code-example">
            <pre>{
  "action": "update_user",
  "user_id": 1,
  "email": "newemail@example.com",
  "first_name": "New First Name",
  "last_name": "New Last Name",
  "meta": {
    "phone": "123-456-7890"
  }
}</pre>
        </div>
        
        <h3><?php _e('Example Response', 'n8n-wordpress-integration'); ?></h3>
        
        <div class="code-example">
            <pre>{
  "success": true,
  "message": "Post created successfully",
  "post_id": 123
}</pre>
        </div>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        // Copy webhook URL
        $('#copy-webhook-url').on('click', function() {
            var webhookUrl = document.getElementById('webhook-url');
            var range = document.createRange();
            range.selectNode(webhookUrl);
            window.getSelection().removeAllRanges();
            window.getSelection().addRange(range);
            document.execCommand('copy');
            window.getSelection().removeAllRanges();
            
            $(this).text('<?php _e("Copied!", "n8n-wordpress-integration"); ?>');
            setTimeout(function() {
                $('#copy-webhook-url').text('<?php _e("Copy", "n8n-wordpress-integration"); ?>');
            }, 2000);
        });
        
        // Copy API key
        $('#copy-api-key').on('click', function() {
            var apiKey = document.getElementById('api-key');
            var range = document.createRange();
            range.selectNode(apiKey);
            window.getSelection().removeAllRanges();
            window.getSelection().addRange(range);
            document.execCommand('copy');
            window.getSelection().removeAllRanges();
            
            $(this).text('<?php _e("Copied!", "n8n-wordpress-integration"); ?>');
            setTimeout(function() {
                $('#copy-api-key').text('<?php _e("Copy", "n8n-wordpress-integration"); ?>');
            }, 2000);
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
    
    .webhook-url-container,
    .api-key-container {
        display: flex;
        align-items: center;
        margin: 10px 0;
    }
    
    .webhook-url-container code,
    .api-key-container code {
        flex: 1;
        padding: 10px;
        background: #f6f7f7;
        border-radius: 4px;
        margin-right: 10px;
        word-break: break-all;
    }
    
    .widefat {
        margin-top: 10px;
        margin-bottom: 20px;
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
</style>