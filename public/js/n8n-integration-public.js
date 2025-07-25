/**
 * All of the JS for your public-facing functionality should be
 * included in this file.
 */

(function($) {
    'use strict';

    /**
     * Initialize the public functionality when the DOM is ready.
     */
    $(document).ready(function() {
        // This file is intentionally left mostly empty as the plugin primarily focuses on backend integration.
        // Add any public-facing JavaScript functionality here if needed for future development.
        
        // Example: Initialize custom forms that might trigger n8n workflows
        initCustomForms();
    });

    /**
     * Initialize custom forms that might trigger n8n workflows.
     */
    function initCustomForms() {
        // Example: Custom form submission that might trigger an n8n workflow
        $('.n8n-integration-form').on('submit', function(e) {
            e.preventDefault();
            
            var formData = $(this).serialize();
            
            $.ajax({
                url: n8n_integration_public.ajax_url,
                method: 'POST',
                data: {
                    action: 'n8n_integration_form_submit',
                    form_data: formData,
                    nonce: n8n_integration_public.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Handle successful form submission
                        if (response.message) {
                            alert(response.message);
                        }
                    } else {
                        // Handle failed form submission
                        if (response.message) {
                            alert(response.message);
                        } else {
                            alert('Form submission failed. Please try again.');
                        }
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        });
    }

})(jQuery);