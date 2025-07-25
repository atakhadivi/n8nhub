<?php

/**
 * Provide a admin area view for the plugin's logs
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @since      1.0.0
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Get logs from database
// In a real implementation, you would store logs in a custom table or option
// For this example, we'll just show a placeholder

?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?> - <?php _e('Logs', 'n8n-wordpress-integration'); ?></h1>
    
    <div class="notice notice-info">
        <p><?php _e('View logs of n8n integration activities.', 'n8n-wordpress-integration'); ?></p>
    </div>
    
    <div class="card">
        <h2><?php _e('Integration Logs', 'n8n-wordpress-integration'); ?></h2>
        
        <div class="tablenav top">
            <div class="alignleft actions">
                <form id="n8n-integration-logs-filter">
                    <select name="log_type">
                        <option value=""><?php _e('All Types', 'n8n-wordpress-integration'); ?></option>
                        <option value="trigger"><?php _e('Triggers', 'n8n-wordpress-integration'); ?></option>
                        <option value="action"><?php _e('Actions', 'n8n-wordpress-integration'); ?></option>
                        <option value="error"><?php _e('Errors', 'n8n-wordpress-integration'); ?></option>
                    </select>
                    <select name="log_status">
                        <option value=""><?php _e('All Statuses', 'n8n-wordpress-integration'); ?></option>
                        <option value="success"><?php _e('Success', 'n8n-wordpress-integration'); ?></option>
                        <option value="error"><?php _e('Error', 'n8n-wordpress-integration'); ?></option>
                    </select>
                    <input type="submit" class="button" value="<?php _e('Filter', 'n8n-wordpress-integration'); ?>">
                </form>
            </div>
            <div class="alignright">
                <button id="clear-logs" class="button"><?php _e('Clear Logs', 'n8n-wordpress-integration'); ?></button>
            </div>
        </div>
        
        <table class="widefat logs-table">
            <thead>
                <tr>
                    <th><?php _e('Time', 'n8n-wordpress-integration'); ?></th>
                    <th><?php _e('Type', 'n8n-wordpress-integration'); ?></th>
                    <th><?php _e('Event', 'n8n-wordpress-integration'); ?></th>
                    <th><?php _e('Status', 'n8n-wordpress-integration'); ?></th>
                    <th><?php _e('Message', 'n8n-wordpress-integration'); ?></th>
                    <th><?php _e('Details', 'n8n-wordpress-integration'); ?></th>
                </tr>
            </thead>
            <tbody id="logs-table-body">
                <tr>
                    <td colspan="6" class="no-logs-message">
                        <?php _e('No logs available yet. Logs will appear here when n8n integration activities occur.', 'n8n-wordpress-integration'); ?>
                    </td>
                </tr>
                <!-- Logs will be populated here via JavaScript -->
            </tbody>
        </table>
        
        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <span class="displaying-num">0 <?php _e('items', 'n8n-wordpress-integration'); ?></span>
                <span class="pagination-links">
                    <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>
                    <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>
                    <span class="paging-input">
                        <label for="current-page-selector" class="screen-reader-text"><?php _e('Current Page', 'n8n-wordpress-integration'); ?></label>
                        <input class="current-page" id="current-page-selector" type="text" name="paged" value="1" size="1" aria-describedby="table-paging">
                        <span class="tablenav-paging-text"> <?php _e('of', 'n8n-wordpress-integration'); ?> <span class="total-pages">1</span></span>
                    </span>
                    <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>
                    <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>
                </span>
            </div>
        </div>
    </div>
    
    <div class="card">
        <h2><?php _e('Log Settings', 'n8n-wordpress-integration'); ?></h2>
        
        <form id="n8n-integration-log-settings-form">
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="log_level"><?php _e('Log Level', 'n8n-wordpress-integration'); ?></label>
                        </th>
                        <td>
                            <select name="log_level" id="log_level">
                                <option value="error"><?php _e('Error Only', 'n8n-wordpress-integration'); ?></option>
                                <option value="info" selected><?php _e('Info (Recommended)', 'n8n-wordpress-integration'); ?></option>
                                <option value="debug"><?php _e('Debug (Verbose)', 'n8n-wordpress-integration'); ?></option>
                            </select>
                            <p class="description"><?php _e('Select the level of detail for logging.', 'n8n-wordpress-integration'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="log_retention"><?php _e('Log Retention', 'n8n-wordpress-integration'); ?></label>
                        </th>
                        <td>
                            <select name="log_retention" id="log_retention">
                                <option value="7"><?php _e('7 days', 'n8n-wordpress-integration'); ?></option>
                                <option value="30" selected><?php _e('30 days', 'n8n-wordpress-integration'); ?></option>
                                <option value="90"><?php _e('90 days', 'n8n-wordpress-integration'); ?></option>
                                <option value="0"><?php _e('Keep indefinitely', 'n8n-wordpress-integration'); ?></option>
                            </select>
                            <p class="description"><?php _e('How long to keep logs before automatically deleting them.', 'n8n-wordpress-integration'); ?></p>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <p class="submit">
                <button type="submit" id="save-log-settings" class="button button-primary"><?php _e('Save Log Settings', 'n8n-wordpress-integration'); ?></button>
            </p>
        </form>
    </div>
    
    <!-- Log Details Modal -->
    <div id="log-details-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2><?php _e('Log Details', 'n8n-wordpress-integration'); ?></h2>
            <div id="log-details-content">
                <!-- Log details will be populated here via JavaScript -->
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        // Sample log data for demonstration
        // In a real implementation, this would be fetched from the server
        var sampleLogs = [
            {
                id: 1,
                time: '<?php echo date('Y-m-d H:i:s'); ?>',
                type: 'trigger',
                event: 'post_save',
                status: 'success',
                message: 'Post #123 saved successfully',
                details: {
                    post_id: 123,
                    post_title: 'Sample Post',
                    webhook_url: 'https://n8n.example.com/webhook/123',
                    response_code: 200,
                    response_body: '{"success":true}'
                }
            },
            {
                id: 2,
                time: '<?php echo date('Y-m-d H:i:s', strtotime('-1 hour')); ?>',
                type: 'action',
                event: 'create_post',
                status: 'success',
                message: 'Post #124 created successfully',
                details: {
                    post_id: 124,
                    post_title: 'Created from n8n',
                    request_body: '{"action":"create_post","title":"Created from n8n","content":"This post was created from n8n."}',
                    response_body: '{"success":true,"post_id":124}'
                }
            },
            {
                id: 3,
                time: '<?php echo date('Y-m-d H:i:s', strtotime('-2 hours')); ?>',
                type: 'trigger',
                event: 'user_register',
                status: 'error',
                message: 'Failed to send user data to n8n',
                details: {
                    user_id: 5,
                    webhook_url: 'https://n8n.example.com/webhook/456',
                    error: 'Connection refused',
                    request_body: '{"trigger":"user_register","data":{"id":5,"username":"newuser"}}'
                }
            }
        ];
        
        // Function to render logs
        function renderLogs(logs) {
            if (logs.length === 0) {
                $('#logs-table-body').html('<tr><td colspan="6" class="no-logs-message"><?php _e("No logs found matching your criteria.", "n8n-wordpress-integration"); ?></td></tr>');
                return;
            }
            
            var html = '';
            
            logs.forEach(function(log) {
                var statusClass = log.status === 'success' ? 'success' : 'error';
                
                html += '<tr>';
                html += '<td>' + log.time + '</td>';
                html += '<td>' + log.type + '</td>';
                html += '<td>' + log.event + '</td>';
                html += '<td><span class="status-' + statusClass + '">' + log.status + '</span></td>';
                html += '<td>' + log.message + '</td>';
                html += '<td><button class="button view-details" data-log-id="' + log.id + '"><?php _e("View", "n8n-wordpress-integration"); ?></button></td>';
                html += '</tr>';
            });
            
            $('#logs-table-body').html(html);
            $('.displaying-num').text(logs.length + ' <?php _e("items", "n8n-wordpress-integration"); ?>');
        }
        
        // Initial render
        renderLogs(sampleLogs);
        
        // Filter logs
        $('#n8n-integration-logs-filter').on('submit', function(e) {
            e.preventDefault();
            
            var logType = $('select[name="log_type"]').val();
            var logStatus = $('select[name="log_status"]').val();
            
            var filteredLogs = sampleLogs.filter(function(log) {
                if (logType && log.type !== logType) return false;
                if (logStatus && log.status !== logStatus) return false;
                return true;
            });
            
            renderLogs(filteredLogs);
        });
        
        // Clear logs
        $('#clear-logs').on('click', function() {
            if (confirm('<?php _e("Are you sure you want to clear all logs? This action cannot be undone.", "n8n-wordpress-integration"); ?>')) {
                // In a real implementation, this would send an AJAX request to clear logs
                sampleLogs = [];
                renderLogs(sampleLogs);
                alert('<?php _e("Logs cleared successfully.", "n8n-wordpress-integration"); ?>');
            }
        });
        
        // Save log settings
        $('#n8n-integration-log-settings-form').on('submit', function(e) {
            e.preventDefault();
            
            var logLevel = $('#log_level').val();
            var logRetention = $('#log_retention').val();
            
            // In a real implementation, this would send an AJAX request to save settings
            alert('<?php _e("Log settings saved successfully.", "n8n-wordpress-integration"); ?>');
        });
        
        // View log details
        $(document).on('click', '.view-details', function() {
            var logId = $(this).data('log-id');
            var log = sampleLogs.find(function(log) { return log.id === logId; });
            
            if (log) {
                var detailsHtml = '<table class="widefat">';
                
                // Add basic log info
                detailsHtml += '<tr><th><?php _e("ID", "n8n-wordpress-integration"); ?></th><td>' + log.id + '</td></tr>';
                detailsHtml += '<tr><th><?php _e("Time", "n8n-wordpress-integration"); ?></th><td>' + log.time + '</td></tr>';
                detailsHtml += '<tr><th><?php _e("Type", "n8n-wordpress-integration"); ?></th><td>' + log.type + '</td></tr>';
                detailsHtml += '<tr><th><?php _e("Event", "n8n-wordpress-integration"); ?></th><td>' + log.event + '</td></tr>';
                detailsHtml += '<tr><th><?php _e("Status", "n8n-wordpress-integration"); ?></th><td>' + log.status + '</td></tr>';
                detailsHtml += '<tr><th><?php _e("Message", "n8n-wordpress-integration"); ?></th><td>' + log.message + '</td></tr>';
                
                // Add detailed info
                if (log.details) {
                    Object.keys(log.details).forEach(function(key) {
                        var value = log.details[key];
                        
                        // Format JSON strings
                        if (typeof value === 'string' && (value.startsWith('{') || value.startsWith('['))) {
                            try {
                                var jsonObj = JSON.parse(value);
                                value = '<pre>' + JSON.stringify(jsonObj, null, 2) + '</pre>';
                            } catch (e) {
                                // Not valid JSON, leave as is
                            }
                        }
                        
                        detailsHtml += '<tr><th>' + key.replace(/_/g, ' ') + '</th><td>' + value + '</td></tr>';
                    });
                }
                
                detailsHtml += '</table>';
                
                $('#log-details-content').html(detailsHtml);
                $('#log-details-modal').show();
            }
        });
        
        // Close modal
        $('.close').on('click', function() {
            $('#log-details-modal').hide();
        });
        
        // Close modal when clicking outside of it
        $(window).on('click', function(event) {
            if ($(event.target).is('#log-details-modal')) {
                $('#log-details-modal').hide();
            }
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
    }
    
    .logs-table th {
        font-weight: 600;
    }
    
    .no-logs-message {
        text-align: center;
        padding: 20px;
        color: #666;
    }
    
    .status-success {
        color: #46b450;
        font-weight: 600;
    }
    
    .status-error {
        color: #dc3232;
        font-weight: 600;
    }
    
    /* Modal styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.4);
    }
    
    .modal-content {
        background-color: #fefefe;
        margin: 5% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        max-width: 800px;
        border-radius: 4px;
    }
    
    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }
    
    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
    }
    
    #log-details-content pre {
        background: #f6f7f7;
        padding: 10px;
        overflow: auto;
        max-height: 300px;
        margin: 0;
    }
</style>