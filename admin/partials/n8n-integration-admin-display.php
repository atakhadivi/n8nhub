<?php

/**
 * Provide a admin area view for the plugin
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
$api_key = get_option('n8n_integration_api_key', '');
$n8n_api_key = get_option('n8n_integration_n8n_api_key', '');
$debug_mode = get_option('n8n_integration_debug_mode', false);

// Generate a new API key if none exists
if (empty($api_key)) {
    $api_key = wp_generate_password(32, false);
    update_option('n8n_integration_api_key', $api_key);
}

// Get the site URL for the webhook endpoint
$webhook_url = rest_url('n8n-integration/v1/webhook');

?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="notice notice-info">
        <p><?php _e('n8n Integration allows you to connect your WordPress site with n8n workflow automation platform.', 'n8n-wordpress-integration'); ?></p>
    </div>
    
    <div class="card">
        <h2><?php _e('Connection Settings', 'n8n-wordpress-integration'); ?></h2>
        
        <form id="n8n-integration-settings-form">
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="n8n_url"><?php _e('n8n URL', 'n8n-wordpress-integration'); ?></label>
                        </th>
                        <td>
                            <input name="n8n_url" type="url" id="n8n_url" value="<?php echo esc_attr($n8n_url); ?>" class="regular-text">
                            <p class="description"><?php _e('Enter the URL of your n8n instance (e.g., https://your-n8n-instance.com).', 'n8n-wordpress-integration'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="api_key"><?php _e('WordPress API Key', 'n8n-wordpress-integration'); ?></label>
                        </th>
                        <td>
                            <input name="api_key" type="text" id="api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text" readonly>
                            <button type="button" id="generate-api-key" class="button button-secondary"><?php _e('Generate New Key', 'n8n-wordpress-integration'); ?></button>
                            <p class="description"><?php _e('This API key is used to authenticate requests from n8n to WordPress.', 'n8n-wordpress-integration'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="n8n_api_key"><?php _e('n8n API Key', 'n8n-wordpress-integration'); ?></label>
                        </th>
                        <td>
                            <input name="n8n_api_key" type="text" id="n8n_api_key" value="<?php echo esc_attr($n8n_api_key); ?>" class="regular-text">
                            <p class="description"><?php _e('Enter your n8n API key to enable WordPress to communicate with n8n. This is required for bidirectional communication.', 'n8n-wordpress-integration'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="debug_mode"><?php _e('Debug Mode', 'n8n-wordpress-integration'); ?></label>
                        </th>
                        <td>
                            <label for="debug_mode">
                                <input name="debug_mode" type="checkbox" id="debug_mode" value="1" <?php checked($debug_mode); ?>>
                                <?php _e('Enable webhook logging', 'n8n-wordpress-integration'); ?>
                            </label>
                            <p class="description"><?php _e('When enabled, all webhook requests and responses will be logged for debugging purposes. This can be useful for troubleshooting but may impact performance.', 'n8n-wordpress-integration'); ?></p>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <p class="submit">
                <button type="submit" id="save-settings" class="button button-primary"><?php _e('Save Settings', 'n8n-wordpress-integration'); ?></button>
                <button type="button" id="test-connection" class="button button-secondary"><?php _e('Test Connection', 'n8n-wordpress-integration'); ?></button>
            </p>
        </form>
    </div>
    
    <div class="card">
        <h2><?php _e('Webhook Information', 'n8n-wordpress-integration'); ?></h2>
        
        <p><?php _e('Use the following webhook URL in your n8n workflows to send data to WordPress:', 'n8n-wordpress-integration'); ?></p>
        
        <div class="webhook-url-container">
            <code id="webhook-url"><?php echo esc_url($webhook_url); ?></code>
            <button type="button" id="copy-webhook-url" class="button button-secondary"><?php _e('Copy', 'n8n-wordpress-integration'); ?></button>
        </div>
        
        <p class="description"><?php _e('Remember to include the API key in the request header as X-N8N-API-KEY.', 'n8n-wordpress-integration'); ?></p>
    </div>
    
    <div class="card">
        <h2><?php _e('Documentation', 'n8n-wordpress-integration'); ?></h2>
        
        <h3><?php _e('Available Actions (n8n → WordPress)', 'n8n-wordpress-integration'); ?></h3>
        
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
        
        <h3><?php _e('Available Triggers (WordPress → n8n)', 'n8n-wordpress-integration'); ?></h3>
        
        <p><?php _e('The following WordPress events can trigger n8n workflows:', 'n8n-wordpress-integration'); ?></p>
        
        <table class="widefat">
            <thead>
                <tr>
                    <th><?php _e('Trigger', 'n8n-wordpress-integration'); ?></th>
                    <th><?php _e('Description', 'n8n-wordpress-integration'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>post_save</code></td>
                    <td><?php _e('Triggered when a post is created or updated', 'n8n-wordpress-integration'); ?></td>
                </tr>
                <tr>
                    <td><code>user_register</code></td>
                    <td><?php _e('Triggered when a new user is registered', 'n8n-wordpress-integration'); ?></td>
                </tr>
                <tr>
                    <td><code>comment_post</code></td>
                    <td><?php _e('Triggered when a new comment is posted', 'n8n-wordpress-integration'); ?></td>
                </tr>
                <tr>
                    <td><code>woocommerce_new_order</code></td>
                    <td><?php _e('Triggered when a new WooCommerce order is created', 'n8n-wordpress-integration'); ?></td>
                </tr>
            </tbody>
        </table>
        
        <p><?php _e('Configure these triggers in the Triggers tab.', 'n8n-wordpress-integration'); ?></p>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        // Generate new API key
        $('#generate-api-key').on('click', function() {
            if (confirm('<?php _e("Are you sure you want to generate a new API key? This will invalidate the existing key.", "n8n-wordpress-integration"); ?>')) {
                $.ajax({
                    url: n8n_integration_admin.rest_url + 'settings',
                    method: 'POST',
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', n8n_integration_admin.rest_nonce);
                    },
                    data: {
                        api_key: generateRandomKey(32)
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('<?php _e("Failed to generate new API key.", "n8n-wordpress-integration"); ?>');
                        }
                    },
                    error: function() {
                        alert('<?php _e("Failed to generate new API key.", "n8n-wordpress-integration"); ?>');
                    }
                });
            }
        });
        
        // Generate random key
        function generateRandomKey(length) {
            var result = '';
            var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            var charactersLength = characters.length;
            for (var i = 0; i < length; i++) {
                result += characters.charAt(Math.floor(Math.random() * charactersLength));
            }
            return result;
        }
        
        // Save settings
        $('#n8n-integration-settings-form').on('submit', function(e) {
            e.preventDefault();
            
            var n8n_url = $('#n8n_url').val();
            var api_key = $('#api_key').val();
            var n8n_api_key = $('#n8n_api_key').val();
            var debug_mode = $('#debug_mode').is(':checked') ? 1 : 0;
            
            $.ajax({
                url: n8n_integration_admin.rest_url + 'settings',
                method: 'POST',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', n8n_integration_admin.rest_nonce);
                },
                data: {
                    n8n_url: n8n_url,
                    api_key: api_key,
                    n8n_api_key: n8n_api_key,
                    debug_mode: debug_mode
                },
                success: function(response) {
                    if (response.success) {
                        alert('<?php _e("Settings saved successfully.", "n8n-wordpress-integration"); ?>');
                    } else {
                        alert('<?php _e("Failed to save settings.", "n8n-wordpress-integration"); ?>');
                    }
                },
                error: function() {
                    alert('<?php _e("Failed to save settings.", "n8n-wordpress-integration"); ?>');
                }
            });
        });
        
        // Test connection
        $('#test-connection').on('click', function() {
            var n8n_url = $('#n8n_url').val();
            
            if (!n8n_url) {
                alert('<?php _e("Please enter the n8n URL first.", "n8n-wordpress-integration"); ?>');
                return;
            }
            
            $(this).prop('disabled', true).text('<?php _e("Testing...", "n8n-wordpress-integration"); ?>');
            
            $.ajax({
                url: n8n_integration_admin.rest_url + 'test-connection',
                method: 'POST',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', n8n_integration_admin.rest_nonce);
                },
                data: {
                    n8n_url: n8n_url
                },
                success: function(response) {
                    if (response.success) {
                        alert('<?php _e("Connection successful! Your n8n instance is reachable.", "n8n-wordpress-integration"); ?>');
                    } else {
                        alert('<?php _e("Connection failed: ", "n8n-wordpress-integration"); ?>' + response.message);
                    }
                    $('#test-connection').prop('disabled', false).text('<?php _e("Test Connection", "n8n-wordpress-integration"); ?>');
                },
                error: function() {
                    alert('<?php _e("Connection failed. Please check the URL and make sure your n8n instance is running.", "n8n-wordpress-integration"); ?>');
                    $('#test-connection').prop('disabled', false).text('<?php _e("Test Connection", "n8n-wordpress-integration"); ?>');
                }
            });
        });
        
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
    
    .webhook-url-container {
        display: flex;
        align-items: center;
        margin: 10px 0;
    }
    
    .webhook-url-container code {
        flex: 1;
        padding: 10px;
        background: #f6f7f7;
        border-radius: 4px;
        margin-right: 10px;
    }
    
    .widefat {
        margin-top: 10px;
        margin-bottom: 20px;
    }
    
    #workflow-execution-results {
        margin-top: 15px;
        padding: 15px;
        background-color: #f8f9fa;
        border-left: 4px solid #007cba;
        display: none;
    }
    
    .workflow-status {
        margin-top: 10px;
        padding: 10px;
        border-radius: 4px;
    }
    
    .workflow-status.success {
        background-color: #d4edda;
        border-color: #c3e6cb;
        color: #155724;
    }
    
    .workflow-status.error {
        background-color: #f8d7da;
        border-color: #f5c6cb;
        color: #721c24;
    }
    
    .workflow-status.pending {
        background-color: #fff3cd;
        border-color: #ffeeba;
        color: #856404;
    }
    
    #workflow-list {
        margin-top: 15px;
        max-height: 300px;
        overflow-y: auto;
    }
</style>

<div class="card">
    <h2><?php _e('Execute n8n Workflows', 'n8n-wordpress-integration'); ?></h2>
    
    <p><?php _e('Execute n8n workflows directly from WordPress. This requires a valid n8n URL and API key.', 'n8n-wordpress-integration'); ?></p>
    
    <div id="workflow-list-container">
        <button type="button" id="load-workflows" class="button button-secondary"><?php _e('Load Workflows', 'n8n-wordpress-integration'); ?></button>
        <div id="workflow-list"></div>
    </div>
    
    <form id="execute-workflow-form" style="display: none;">
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="workflow_id"><?php _e('Workflow', 'n8n-wordpress-integration'); ?></label>
                    </th>
                    <td>
                        <select name="workflow_id" id="workflow_id" class="regular-text"></select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="workflow_data"><?php _e('Input Data (JSON)', 'n8n-wordpress-integration'); ?></label>
                    </th>
                    <td>
                        <textarea name="workflow_data" id="workflow_data" class="large-text code" rows="5">{}</textarea>
                        <p class="description"><?php _e('Enter the input data for the workflow in JSON format.', 'n8n-wordpress-integration'); ?></p>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <p class="submit">
            <button type="submit" id="execute-workflow" class="button button-primary"><?php _e('Execute Workflow', 'n8n-wordpress-integration'); ?></button>
        </p>
    </form>
    
    <div id="workflow-execution-results"></div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        // Load workflows
        $('#load-workflows').on('click', function() {
            var button = $(this);
            button.prop('disabled', true).text('<?php _e("Loading...", "n8n-wordpress-integration"); ?>');
            
            $.ajax({
                url: n8n_integration_admin.rest_url + 'workflows',
                method: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', n8n_integration_admin.rest_nonce);
                },
                success: function(response) {
                    button.prop('disabled', false).text('<?php _e("Refresh Workflows", "n8n-wordpress-integration"); ?>');
                    
                    if (response.success && response.workflows) {
                        var workflowSelect = $('#workflow_id');
                        workflowSelect.empty();
                        
                        $.each(response.workflows, function(index, workflow) {
                            workflowSelect.append($('<option>', {
                                value: workflow.id,
                                text: workflow.name
                            }));
                        });
                        
                        $('#execute-workflow-form').show();
                    } else {
                        $('#workflow-list').html('<div class="notice notice-error inline"><p>' + (response.message || '<?php _e("Failed to load workflows", "n8n-wordpress-integration"); ?>') + '</p></div>');
                    }
                },
                error: function(xhr) {
                    button.prop('disabled', false).text('<?php _e("Load Workflows", "n8n-wordpress-integration"); ?>');
                    $('#workflow-list').html('<div class="notice notice-error inline"><p><?php _e("Failed to load workflows. Please check your n8n URL and API key.", "n8n-wordpress-integration"); ?></p></div>');
                }
            });
        });
        
        // Execute workflow
        $('#execute-workflow-form').on('submit', function(e) {
            e.preventDefault();
            
            var workflowId = $('#workflow_id').val();
            var workflowData = $('#workflow_data').val();
            
            if (!workflowId) {
                alert('<?php _e("Please select a workflow", "n8n-wordpress-integration"); ?>');
                return;
            }
            
            try {
                // Parse JSON to validate
                var dataObj = JSON.parse(workflowData);
            } catch (error) {
                alert('<?php _e("Invalid JSON data", "n8n-wordpress-integration"); ?>');
                return;
            }
            
            $('#execute-workflow').prop('disabled', true).text('<?php _e("Executing...", "n8n-wordpress-integration"); ?>');
            
            $.ajax({
                url: n8n_integration_admin.rest_url + 'execute-workflow',
                method: 'POST',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', n8n_integration_admin.rest_nonce);
                },
                data: JSON.stringify({
                    workflow_id: workflowId,
                    data: dataObj
                }),
                contentType: 'application/json',
                success: function(response) {
                    $('#execute-workflow').prop('disabled', false).text('<?php _e("Execute Workflow", "n8n-wordpress-integration"); ?>');
                    
                    if (response.success) {
                        var executionId = response.execution_id;
                        
                        $('#workflow-execution-results').html(
                            '<h3><?php _e("Execution Results", "n8n-wordpress-integration"); ?></h3>' +
                            '<div class="workflow-status pending">' +
                            '<?php _e("Workflow execution started. Execution ID:", "n8n-wordpress-integration"); ?> ' + executionId +
                            '</div>'
                        ).show();
                        
                        // Poll for execution status if we have an execution ID
                        if (executionId) {
                            checkWorkflowStatus(executionId);
                        }
                    } else {
                        $('#workflow-execution-results').html(
                            '<h3><?php _e("Execution Results", "n8n-wordpress-integration"); ?></h3>' +
                            '<div class="workflow-status error">' +
                            '<?php _e("Failed to execute workflow:", "n8n-wordpress-integration"); ?> ' + (response.message || '<?php _e("Unknown error", "n8n-wordpress-integration"); ?>') +
                            '</div>'
                        ).show();
                    }
                },
                error: function(xhr) {
                    $('#execute-workflow').prop('disabled', false).text('<?php _e("Execute Workflow", "n8n-wordpress-integration"); ?>');
                    
                    $('#workflow-execution-results').html(
                        '<h3><?php _e("Execution Results", "n8n-wordpress-integration"); ?></h3>' +
                        '<div class="workflow-status error">' +
                        '<?php _e("Failed to execute workflow. Please check your n8n URL and API key.", "n8n-wordpress-integration"); ?>' +
                        '</div>'
                    ).show();
                }
            });
        });
        
        // Function to check workflow execution status
        function checkWorkflowStatus(executionId) {
            $.ajax({
                url: n8n_integration_admin.rest_url + 'workflow-status/' + executionId,
                method: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', n8n_integration_admin.rest_nonce);
                },
                success: function(response) {
                    if (response.success && response.status) {
                        var status = response.status;
                        var statusClass = 'pending';
                        var statusText = '<?php _e("Pending", "n8n-wordpress-integration"); ?>';
                        
                        if (status.finished) {
                            statusClass = status.data && status.data.resultData && status.data.resultData.error ? 'error' : 'success';
                            statusText = statusClass === 'success' ? '<?php _e("Completed", "n8n-wordpress-integration"); ?>' : '<?php _e("Failed", "n8n-wordpress-integration"); ?>';
                        }
                        
                        $('#workflow-execution-results .workflow-status')
                            .removeClass('pending success error')
                            .addClass(statusClass)
                            .html(
                                '<?php _e("Workflow execution", "n8n-wordpress-integration"); ?>: <strong>' + statusText + '</strong><br>' +
                                '<?php _e("Execution ID:", "n8n-wordpress-integration"); ?> ' + executionId + '<br><br>' +
                                '<pre>' + JSON.stringify(status, null, 2) + '</pre>'
                            );
                        
                        // If not finished, poll again after 2 seconds
                        if (!status.finished) {
                            setTimeout(function() {
                                checkWorkflowStatus(executionId);
                            }, 2000);
                        }
                    } else {
                        $('#workflow-execution-results .workflow-status')
                            .removeClass('pending success error')
                            .addClass('error')
                            .html(
                                '<?php _e("Failed to get workflow status:", "n8n-wordpress-integration"); ?> ' + 
                                (response.message || '<?php _e("Unknown error", "n8n-wordpress-integration"); ?>')
                            );
                    }
                },
                error: function(xhr) {
                    $('#workflow-execution-results .workflow-status')
                        .removeClass('pending success error')
                        .addClass('error')
                        .html('<?php _e("Failed to get workflow status. Please check your n8n URL and API key.", "n8n-wordpress-integration"); ?>');
                }
            });
        }
    });
</script>