/**
 * All of the JS for your admin-specific functionality should be
 * included in this file.
 */

(function($) {
    'use strict';

    /**
     * Initialize the admin functionality when the DOM is ready.
     */
    $(document).ready(function() {
        // Settings Page
        initSettingsPage();
        
        // Triggers Page
        initTriggersPage();
        
        // Actions Page
        initActionsPage();
        
        // Logs Page
        initLogsPage();
    });

    /**
     * Initialize the settings page functionality.
     */
    function initSettingsPage() {
        // Generate new API key
        $('#generate-api-key').on('click', function() {
            if (confirm(n8n_integration_admin.i18n.confirm_generate_key)) {
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
                            alert(n8n_integration_admin.i18n.failed_generate_key);
                        }
                    },
                    error: function() {
                        alert(n8n_integration_admin.i18n.failed_generate_key);
                    }
                });
            }
        });
        
        // Save settings
        $('#n8n-integration-settings-form').on('submit', function(e) {
            e.preventDefault();
            
            var n8n_url = $('#n8n_url').val();
            var api_key = $('#api_key').val();
            
            $.ajax({
                url: n8n_integration_admin.rest_url + 'settings',
                method: 'POST',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', n8n_integration_admin.rest_nonce);
                },
                data: {
                    n8n_url: n8n_url,
                    api_key: api_key
                },
                success: function(response) {
                    if (response.success) {
                        alert(n8n_integration_admin.i18n.settings_saved);
                    } else {
                        alert(n8n_integration_admin.i18n.failed_save_settings);
                    }
                },
                error: function() {
                    alert(n8n_integration_admin.i18n.failed_save_settings);
                }
            });
        });
        
        // Test connection
        $('#test-connection').on('click', function() {
            var n8n_url = $('#n8n_url').val();
            
            if (!n8n_url) {
                alert(n8n_integration_admin.i18n.enter_n8n_url);
                return;
            }
            
            $(this).prop('disabled', true).text(n8n_integration_admin.i18n.testing);
            
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
                        alert(n8n_integration_admin.i18n.connection_successful);
                    } else {
                        alert(n8n_integration_admin.i18n.connection_failed + response.message);
                    }
                    $('#test-connection').prop('disabled', false).text(n8n_integration_admin.i18n.test_connection);
                },
                error: function() {
                    alert(n8n_integration_admin.i18n.connection_failed_check);
                    $('#test-connection').prop('disabled', false).text(n8n_integration_admin.i18n.test_connection);
                }
            });
        });
        
        // Copy webhook URL
        $('#copy-webhook-url').on('click', function() {
            copyToClipboard('#webhook-url');
            $(this).text(n8n_integration_admin.i18n.copied);
            setTimeout(function() {
                $('#copy-webhook-url').text(n8n_integration_admin.i18n.copy);
            }, 2000);
        });
    }

    /**
     * Initialize the triggers page functionality.
     */
    function initTriggersPage() {
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
                    var trigger = field.name.match(/webhook_urls\[(.*?)\]/);
                    if (trigger && trigger[1]) {
                        data.webhook_urls[trigger[1]] = field.value;
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
                        alert(n8n_integration_admin.i18n.triggers_saved);
                    } else {
                        alert(n8n_integration_admin.i18n.failed_save_triggers);
                    }
                },
                error: function() {
                    alert(n8n_integration_admin.i18n.failed_save_triggers);
                }
            });
        });
    }

    /**
     * Initialize the actions page functionality.
     */
    function initActionsPage() {
        // Copy webhook URL
        $('#copy-webhook-url').on('click', function() {
            copyToClipboard('#webhook-url');
            $(this).text(n8n_integration_admin.i18n.copied);
            setTimeout(function() {
                $('#copy-webhook-url').text(n8n_integration_admin.i18n.copy);
            }, 2000);
        });
        
        // Copy API key
        $('#copy-api-key').on('click', function() {
            copyToClipboard('#api-key');
            $(this).text(n8n_integration_admin.i18n.copied);
            setTimeout(function() {
                $('#copy-api-key').text(n8n_integration_admin.i18n.copy);
            }, 2000);
        });
    }

    /**
     * Initialize the logs page functionality.
     */
    function initLogsPage() {
        // Filter logs
        $('#n8n-integration-logs-filter').on('submit', function(e) {
            e.preventDefault();
            
            var logType = $('select[name="log_type"]').val();
            var logStatus = $('select[name="log_status"]').val();
            
            // In a real implementation, this would send an AJAX request to filter logs
            // For now, we're using the sample logs in the PHP file
        });
        
        // Clear logs
        $('#clear-logs').on('click', function() {
            if (confirm(n8n_integration_admin.i18n.confirm_clear_logs)) {
                // In a real implementation, this would send an AJAX request to clear logs
                // For now, we're using the sample logs in the PHP file
            }
        });
        
        // Save log settings
        $('#n8n-integration-log-settings-form').on('submit', function(e) {
            e.preventDefault();
            
            var logLevel = $('#log_level').val();
            var logRetention = $('#log_retention').val();
            
            // In a real implementation, this would send an AJAX request to save settings
            alert(n8n_integration_admin.i18n.log_settings_saved);
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
    }

    /**
     * Generate a random key of specified length.
     *
     * @param {number} length The length of the key to generate.
     * @return {string} The generated key.
     */
    function generateRandomKey(length) {
        var result = '';
        var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var charactersLength = characters.length;
        for (var i = 0; i < length; i++) {
            result += characters.charAt(Math.floor(Math.random() * charactersLength));
        }
        return result;
    }

    /**
     * Copy text from an element to the clipboard.
     *
     * @param {string} selector The selector for the element containing the text to copy.
     */
    function copyToClipboard(selector) {
        var element = document.querySelector(selector);
        var range = document.createRange();
        range.selectNode(element);
        window.getSelection().removeAllRanges();
        window.getSelection().addRange(range);
        document.execCommand('copy');
        window.getSelection().removeAllRanges();
    }

})(jQuery);