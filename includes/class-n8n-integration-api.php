<?php

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * The API functionality of the plugin.
 *
 * Defines the REST API endpoints and WordPress hooks for n8n integration
 *
 * @since      1.0.0
 *
 * WordPress core functions and classes used in this file:
 * 
 * @see https://developer.wordpress.org/reference/functions/register_rest_route/
 * @see https://developer.wordpress.org/reference/functions/get_option/
 * @see https://developer.wordpress.org/reference/functions/update_option/
 * @see https://developer.wordpress.org/reference/functions/current_user_can/
 * @see https://developer.wordpress.org/reference/functions/sanitize_text_field/
 * @see https://developer.wordpress.org/reference/functions/wp_kses_post/
 * @see https://developer.wordpress.org/reference/functions/esc_url_raw/
 * @see https://developer.wordpress.org/reference/functions/wp_remote_post/
 * @see https://developer.wordpress.org/reference/functions/wp_remote_get/
 * @see https://developer.wordpress.org/reference/functions/is_wp_error/
 * @see https://developer.wordpress.org/reference/classes/wp_rest_response/
 * @see https://developer.wordpress.org/reference/classes/wp_error/
 */
class N8N_Integration_API {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

    }

    /**
     * Register the REST API routes.
     *
     * @since    1.0.0
     */
    public function register_routes() {
        // Register route for webhook actions (n8n -> WordPress)
        \register_rest_route('n8n-integration/v1', '/webhook', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_webhook'),
            'permission_callback' => array($this, 'check_webhook_permission'),
        ));

        // Register route for getting plugin settings
        \register_rest_route('n8n-integration/v1', '/settings', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_settings'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));

        // Register route for updating plugin settings
        \register_rest_route('n8n-integration/v1', '/settings', array(
            'methods' => 'POST',
            'callback' => array($this, 'update_settings'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));

        // Register route for testing n8n connection
        \register_rest_route('n8n-integration/v1', '/test-connection', array(
            'methods' => 'POST',
            'callback' => array($this, 'test_connection'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // Register route for getting n8n workflows
        \register_rest_route('n8n-integration/v1', '/workflows', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_workflows'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // Register route for executing n8n workflow
        \register_rest_route('n8n-integration/v1', '/execute-workflow', array(
            'methods' => 'POST',
            'callback' => array($this, 'execute_workflow'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
        
        // Register route for getting n8n workflow execution status
        \register_rest_route('n8n-integration/v1', '/workflow-status/(?P<execution_id>[\w-]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_workflow_status'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
    }

    /**
     * Check if the webhook request has valid authentication.
     *
     * @since    1.0.0
     * @param    \WP_REST_Request    $request    The request object.
     * @return   bool                           Whether the request has valid authentication.
     */
    public function check_webhook_permission($request) {
        // Get the API key from the plugin settings
        $api_key = \get_option('n8n_integration_api_key', '');
        
        // If no API key is set, deny access
        if (empty($api_key)) {
            return false;
        }
        
        // Check if the API key in the request header matches the stored API key
        $request_api_key = $request->get_header('X-N8N-API-KEY');
        
        return $api_key === $request_api_key;
    }

    /**
     * Check if the user has admin permissions.
     *
     * @since    1.0.0
     * @return   bool    Whether the user has admin permissions.
     */
    public function check_admin_permission() {
        return \current_user_can('manage_options');
    }

    /**
     * Handle incoming webhook from n8n.
     *
     * @since    1.0.0
     * @param    \WP_REST_Request    $request    The request object.
     * @return   \WP_REST_Response               The response object.
     */
    public function handle_webhook($request) {
        // Get the request parameters
        $params = $request->get_params();
        
        // Check if the action parameter is set
        if (!isset($params['action'])) {
            return new \WP_REST_Response(array(
                'success' => false,
                'message' => 'Missing action parameter',
            ), 400);
        }
        
        // Handle different actions
        switch ($params['action']) {
            case 'create_post':
                return $this->create_post($params);
                
            case 'update_post':
                return $this->update_post($params);
                
            case 'delete_post':
                return $this->delete_post($params);
                
            case 'create_user':
                return $this->create_user($params);
                
            case 'update_user':
                return $this->update_user($params);
                
            case 'custom_action':
                return $this->handle_custom_action($params);
                
            default:
                return new \WP_REST_Response(array(
                    'success' => false,
                    'message' => 'Invalid action',
                ), 400);
        }
    }

    /**
     * Create a new post.
     *
     * @since    1.0.0
     * @param    array    $params    The request parameters.
     * @return   \WP_REST_Response    The response object.
     */
    private function create_post($params) {
        // Check required parameters
        if (!isset($params['title']) || !isset($params['content'])) {
            return new \WP_REST_Response(array(
                'success' => false,
                'message' => 'Missing required parameters',
            ), 400);
        }
        
        // Set up the post data
        $post_data = array(
            'post_title'    => \sanitize_text_field($params['title']),
            'post_content'  => \wp_kses_post($params['content']),
            'post_status'   => isset($params['status']) ? \sanitize_text_field($params['status']) : 'draft',
            'post_author'   => isset($params['author_id']) ? intval($params['author_id']) : \get_current_user_id(),
            'post_type'     => isset($params['post_type']) ? \sanitize_text_field($params['post_type']) : 'post',
        );
        
        // Insert the post
        $post_id = \wp_insert_post($post_data);
        
        // Check if the post was created successfully
        if (\is_wp_error($post_id)) {
            return new \WP_REST_Response(array(
                'success' => false,
                'message' => $post_id->get_error_message(),
            ), 500);
        }
        
        // Set post meta if provided
        if (isset($params['meta']) && is_array($params['meta'])) {
            foreach ($params['meta'] as $meta_key => $meta_value) {
                \update_post_meta($post_id, \sanitize_text_field($meta_key), \sanitize_text_field($meta_value));
            }
        }
        
        // Set post categories if provided
        if (isset($params['categories']) && is_array($params['categories'])) {
            \wp_set_post_categories($post_id, array_map('intval', $params['categories']));
        }
        
        // Set post tags if provided
        if (isset($params['tags']) && is_array($params['tags'])) {
            \wp_set_post_tags($post_id, array_map('\sanitize_text_field', $params['tags']));
        }
        
        return new \WP_REST_Response(array(
            'success' => true,
            'message' => 'Post created successfully',
            'post_id' => $post_id,
        ), 201);
    }

    /**
     * Update an existing post.
     *
     * @since    1.0.0
     * @param    array    $params    The request parameters.
     * @return   WP_REST_Response    The response object.
     */
    private function update_post($params) {
        // Check required parameters
        if (!isset($params['post_id'])) {
            return new \WP_REST_Response(array(
                'success' => false,
                'message' => 'Missing post_id parameter',
            ), 400);
        }
        
        $post_id = intval($params['post_id']);
        
        // Check if the post exists
        $post = \get_post($post_id);
        if (!$post) {
            return new \WP_REST_Response(array(
                'success' => false,
                'message' => 'Post not found',
            ), 404);
        }
        
        // Set up the post data
        $post_data = array(
            'ID' => $post_id,
        );
        
        // Add optional parameters if provided
        if (isset($params['title'])) {
            $post_data['post_title'] = \sanitize_text_field($params['title']);
        }
        
        if (isset($params['content'])) {
            $post_data['post_content'] = \wp_kses_post($params['content']);
        }
        
        if (isset($params['status'])) {
            $post_data['post_status'] = \sanitize_text_field($params['status']);
        }
        
        if (isset($params['author_id'])) {
            $post_data['post_author'] = intval($params['author_id']);
        }
        
        // Update the post
        $result = \wp_update_post($post_data, true);
        
        // Check if the post was updated successfully
        if (\is_wp_error($result)) {
            return new \WP_REST_Response(array(
                'success' => false,
                'message' => $result->get_error_message(),
            ), 500);
        }
        
        // Update post meta if provided
        if (isset($params['meta']) && is_array($params['meta'])) {
            foreach ($params['meta'] as $meta_key => $meta_value) {
                \update_post_meta($post_id, \sanitize_text_field($meta_key), \sanitize_text_field($meta_value));
            }
        }
        
        // Update post categories if provided
        if (isset($params['categories']) && is_array($params['categories'])) {
            \wp_set_post_categories($post_id, array_map('intval', $params['categories']));
        }
        
        // Update post tags if provided
        if (isset($params['tags']) && is_array($params['tags'])) {
            \wp_set_post_tags($post_id, array_map('\sanitize_text_field', $params['tags']));
        }
        
        return new \WP_REST_Response(array(
            'success' => true,
            'message' => 'Post updated successfully',
            'post_id' => $post_id,
        ), 200);
    }

    /**
     * Delete a post.
     *
     * @since    1.0.0
     * @param    array    $params    The request parameters.
     * @return   WP_REST_Response    The response object.
     */
    private function delete_post($params) {
        // Check required parameters
        if (!isset($params['post_id'])) {
            return new \WP_REST_Response(array(
                'success' => false,
                'message' => 'Missing post_id parameter',
            ), 400);
        }
        
        $post_id = intval($params['post_id']);
        
        // Check if the post exists
        $post = \get_post($post_id);
        if (!$post) {
            return new \WP_REST_Response(array(
                'success' => false,
                'message' => 'Post not found',
            ), 404);
        }
        
        // Delete the post
        $force_delete = isset($params['force_delete']) ? (bool) $params['force_delete'] : false;
        $result = \wp_delete_post($post_id, $force_delete);
        
        // Check if the post was deleted successfully
        if (!$result) {
            return new \WP_REST_Response(array(
                'success' => false,
                'message' => 'Failed to delete post',
            ), 500);
        }
        
        return new \WP_REST_Response(array(
            'success' => true,
            'message' => 'Post deleted successfully',
        ), 200);
    }

    /**
     * Create a new user.
     *
     * @since    1.0.0
     * @param    array    $params    The request parameters.
     * @return   WP_REST_Response    The response object.
     */
    private function create_user($params) {
        // Check required parameters
        if (!isset($params['username']) || !isset($params['email']) || !isset($params['password'])) {
            return new \WP_REST_Response(array(
                'success' => false,
                'message' => 'Missing required parameters',
            ), 400);
        }
        
        // Set up the user data
        $user_data = array(
            'user_login' => \sanitize_user($params['username']),
            'user_email' => \sanitize_email($params['email']),
            'user_pass'  => $params['password'],
            'role'       => isset($params['role']) ? \sanitize_text_field($params['role']) : 'subscriber',
        );
        
        // Add optional parameters if provided
        if (isset($params['first_name'])) {
            $user_data['first_name'] = \sanitize_text_field($params['first_name']);
        }
        
        if (isset($params['last_name'])) {
            $user_data['last_name'] = \sanitize_text_field($params['last_name']);
        }
        
        if (isset($params['display_name'])) {
            $user_data['display_name'] = \sanitize_text_field($params['display_name']);
        }
        
        // Create the user
        $user_id = \wp_insert_user($user_data);
        
        // Check if the user was created successfully
        if (\is_wp_error($user_id)) {
            return new \WP_REST_Response(array(
                'success' => false,
                'message' => $user_id->get_error_message(),
            ), 500);
        }
        
        // Set user meta if provided
        if (isset($params['meta']) && is_array($params['meta'])) {
            foreach ($params['meta'] as $meta_key => $meta_value) {
                \update_user_meta($user_id, \sanitize_text_field($meta_key), \sanitize_text_field($meta_value));
            }
        }
        
        return new \WP_REST_Response(array(
            'success' => true,
            'message' => 'User created successfully',
            'user_id' => $user_id,
        ), 201);
    }

    /**
     * Update an existing user.
     *
     * @since    1.0.0
     * @param    array    $params    The request parameters.
     * @return   WP_REST_Response    The response object.
     */
    private function update_user($params) {
        // Check required parameters
        if (!isset($params['user_id'])) {
            return new \WP_REST_Response(array(
                'success' => false,
                'message' => 'Missing user_id parameter',
            ), 400);
        }
        
        $user_id = intval($params['user_id']);
        
        // Check if the user exists
        $user = \get_user_by('id', $user_id);
        if (!$user) {
            return new \WP_REST_Response(array(
                'success' => false,
                'message' => 'User not found',
            ), 404);
        }
        
        // Set up the user data
        $user_data = array(
            'ID' => $user_id,
        );
        
        // Add optional parameters if provided
        if (isset($params['email'])) {
            $user_data['user_email'] = \sanitize_email($params['email']);
        }
        
        if (isset($params['password'])) {
            $user_data['user_pass'] = $params['password'];
        }
        
        if (isset($params['role'])) {
            $user_data['role'] = \sanitize_text_field($params['role']);
        }
        
        if (isset($params['first_name'])) {
            $user_data['first_name'] = \sanitize_text_field($params['first_name']);
        }
        
        if (isset($params['last_name'])) {
            $user_data['last_name'] = \sanitize_text_field($params['last_name']);
        }
        
        if (isset($params['display_name'])) {
            $user_data['display_name'] = \sanitize_text_field($params['display_name']);
        }
        
        // Update the user
        $result = \wp_update_user($user_data);
        
        // Check if the user was updated successfully
        if (\is_wp_error($result)) {
            return new \WP_REST_Response(array(
                'success' => false,
                'message' => $result->get_error_message(),
            ), 500);
        }
        
        // Update user meta if provided
        if (isset($params['meta']) && is_array($params['meta'])) {
            foreach ($params['meta'] as $meta_key => $meta_value) {
                \update_user_meta($user_id, \sanitize_text_field($meta_key), \sanitize_text_field($meta_value));
            }
        }
        
        return new \WP_REST_Response(array(
            'success' => true,
            'message' => 'User updated successfully',
            'user_id' => $user_id,
        ), 200);
    }

    /**
     * Handle custom action.
     *
     * @since    1.0.0
     * @param    array    $params    The request parameters.
     * @return   WP_REST_Response    The response object.
     */
    private function handle_custom_action($params) {
        // Check required parameters
        if (!isset($params['custom_action_type'])) {
            return new \WP_REST_Response(array(
                'success' => false,
                'message' => 'Missing custom_action_type parameter',
            ), 400);
        }
        
        // Allow plugins to hook into custom actions
        $result = \apply_filters('n8n_integration_custom_action', array(
            'success' => false,
            'message' => 'No handler found for this custom action',
        ), $params);
        
        return new \WP_REST_Response($result, $result['success'] ? 200 : 400);
    }

    /**
     * Get plugin settings.
     *
     * @since    1.0.0
     * @return   \WP_REST_Response    The response object.
     */
    public function get_settings() {
        $webhook_urls = \get_option('n8n_integration_webhook_urls', array());
        
        // Convert old format to new format if needed
        foreach ($webhook_urls as $trigger => $webhook_data) {
            if (is_string($webhook_data)) {
                $webhook_urls[$trigger] = array(
                    'url' => $webhook_data,
                    'name' => '',
                    'description' => ''
                );
            }
        }
        
        $settings = array(
            'n8n_url' => \get_option('n8n_integration_url', ''),
            'api_key' => \get_option('n8n_integration_api_key', ''),
            'n8n_api_key' => \get_option('n8n_integration_n8n_api_key', ''),
            'debug_mode' => \get_option('n8n_integration_debug_mode', false),
            'enabled_triggers' => \get_option('n8n_integration_enabled_triggers', array()),
            'webhook_urls' => $webhook_urls,
        );
        
        return new \WP_REST_Response($settings, 200);
    }

    /**
     * Update plugin settings.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The request object.
     * @return   WP_REST_Response               The response object.
     */
    public function update_settings($request) {
        $params = $request->get_params();
        
        // Update n8n URL if provided
        if (isset($params['n8n_url'])) {
            \update_option('n8n_integration_url', \esc_url_raw($params['n8n_url']));
        }
        
        // Update API key if provided
        if (isset($params['api_key'])) {
            \update_option('n8n_integration_api_key', \sanitize_text_field($params['api_key']));
        }
        
        // Update n8n API key if provided
        if (isset($params['n8n_api_key'])) {
            \update_option('n8n_integration_n8n_api_key', \sanitize_text_field($params['n8n_api_key']));
        }
        
        // Update debug mode if provided
        if (isset($params['debug_mode'])) {
            \update_option('n8n_integration_debug_mode', (bool) $params['debug_mode']);
        }
        
        // Update enabled triggers if provided
        if (isset($params['enabled_triggers']) && is_array($params['enabled_triggers'])) {
            \update_option('n8n_integration_enabled_triggers', array_map('\sanitize_text_field', $params['enabled_triggers']));
        }
        
        // Update webhook URLs if provided
        if (isset($params['webhook_urls']) && is_array($params['webhook_urls'])) {
            $webhook_urls = \get_option('n8n_integration_webhook_urls', array());
            
            foreach ($params['webhook_urls'] as $trigger => $webhook_data) {
                $trigger = \sanitize_text_field($trigger);
                
                // If webhook_data is a string, it's just the URL (backward compatibility)
                if (is_string($webhook_data)) {
                    $webhook_urls[$trigger] = array(
                        'url' => \esc_url_raw($webhook_data),
                        'name' => '',
                        'description' => ''
                    );
                } else if (is_array($webhook_data)) {
                    // New format with name, description, and URL
                    $webhook_urls[$trigger] = array(
                        'url' => isset($webhook_data['url']) ? \esc_url_raw($webhook_data['url']) : '',
                        'name' => isset($webhook_data['name']) ? \sanitize_text_field($webhook_data['name']) : '',
                        'description' => isset($webhook_data['description']) ? \sanitize_textarea_field($webhook_data['description']) : ''
                    );
                }
            }
            
            \update_option('n8n_integration_webhook_urls', $webhook_urls);
        }
        
        return new \WP_REST_Response(array(
            'success' => true,
            'message' => 'Settings updated successfully',
        ), 200);
    }

    /**
     * Test connection to n8n.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The request object.
     * @return   WP_REST_Response               The response object.
     */
    public function test_connection($request) {
        $params = $request->get_params();
        
        // Get n8n URL from request or from settings
        $n8n_url = isset($params['n8n_url']) ? \esc_url_raw($params['n8n_url']) : \get_option('n8n_integration_url', '');
        
        // Check if n8n URL is set
        if (empty($n8n_url)) {
            return new \WP_REST_Response(array(
                'success' => false,
                'message' => 'n8n URL is not set',
            ), 400);
        }
        
        // Make a request to n8n health endpoint
        $response = \wp_remote_get($n8n_url . '/healthz');
        
        // Check if the request was successful
        if (\is_wp_error($response)) {
            return new \WP_REST_Response(array(
                'success' => false,
                'message' => 'Failed to connect to n8n: ' . $response->get_error_message(),
            ), 500);
        }
        
        // Check if the response code is 200
        if (\wp_remote_retrieve_response_code($response) !== 200) {
            return new \WP_REST_Response(array(
                'success' => false,
                'message' => 'Failed to connect to n8n: Invalid response code',
            ), 500);
        }
        
        return new \WP_REST_Response(array(
            'success' => true,
            'message' => 'Successfully connected to n8n',
        ), 200);
    }

    /**
     * Trigger post save event.
     *
     * @since    1.0.0
     * @param    int       $post_id    The post ID.
     * @param    WP_Post   $post       The post object.
     * @param    bool      $update     Whether this is an existing post being updated.
     */
    public function trigger_post_save($post_id, $post, $update) {
        // Skip auto-drafts and revisions
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id) || $post->post_status === 'auto-draft') {
            return;
        }
        
        // Check if this trigger is enabled
        $enabled_triggers = \get_option('n8n_integration_enabled_triggers', array());
        if (!in_array('post_save', $enabled_triggers)) {
            return;
        }
        
        // Get webhook URL data
        $webhook_urls = \get_option('n8n_integration_webhook_urls', array());
        $webhook_data = isset($webhook_urls['post_save']) ? $webhook_urls['post_save'] : '';
        
        // If no webhook URL is set, return
        if (empty($webhook_data) || (is_array($webhook_data) && empty($webhook_data['url']))) {
            return;
        }
        
        // Use the payload builder to create enhanced post data
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-n8n-integration-payload-builder.php';
        $post_data = N8N_Integration_Payload_Builder::build_post_payload($post_id);
        
        // Add trigger-specific data
        $post_data['trigger'] = 'post_save';
        $post_data['is_update'] = $update;
        
        // Send data to n8n webhook
        $this->send_webhook_data($webhook_data, $post_data);
    }

    /**
     * Trigger when a new user is registered.
     *
     * @since    1.0.0
     * @param    int    $user_id    The user ID.
     */
    public function trigger_user_register($user_id) {
        // Check if this trigger is enabled
        $enabled_triggers = \get_option('n8n_integration_enabled_triggers', array());
        if (!in_array('user_register', $enabled_triggers)) {
            return;
        }
        
        // Get webhook URL data
        $webhook_urls = \get_option('n8n_integration_webhook_urls', array());
        $webhook_data = isset($webhook_urls['user_register']) ? $webhook_urls['user_register'] : '';
        
        // If no webhook URL is set, return
        if (empty($webhook_data) || (is_array($webhook_data) && empty($webhook_data['url']))) {
            return;
        }
        
        // Get user data
        $user = \get_userdata($user_id);
        
        // Prepare user data
        $user_data = array(
            'id' => $user_id,
            'username' => $user->user_login,
            'email' => $user->user_email,
            'display_name' => $user->display_name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'roles' => $user->roles,
            'registered_date' => $user->user_registered,
        );
        
        // Add user meta
        $user_meta = \get_user_meta($user_id);
        if (!empty($user_meta)) {
            $user_data['meta'] = array();
            foreach ($user_meta as $meta_key => $meta_values) {
                // Skip sensitive data
                if (in_array($meta_key, array('user_pass', 'session_tokens', 'capabilities'))) {
                    continue;
                }
                $user_data['meta'][$meta_key] = count($meta_values) === 1 ? $meta_values[0] : $meta_values;
            }
        }
        
        // Send data to n8n webhook
        $this->send_webhook_data($webhook_data, array(
            'trigger' => 'user_register',
            'data' => $user_data,
        ));
    }

    /**
     * Trigger when a new comment is posted.
     *
     * @since    1.0.0
     * @param    int        $comment_id    The comment ID.
     * @param    int|string $comment_approved 1 if the comment is approved, 0 if not, 'spam' if spam.
     * @param    array      $comment_data    Comment data.
     */
    public function trigger_comment_post($comment_id, $comment_approved, $comment_data) {
        // Check if this trigger is enabled
        $enabled_triggers = \get_option('n8n_integration_enabled_triggers', array());
        if (!in_array('comment_post', $enabled_triggers)) {
            return;
        }
        
        // Get webhook URL data
        $webhook_urls = \get_option('n8n_integration_webhook_urls', array());
        $webhook_data = isset($webhook_urls['comment_post']) ? $webhook_urls['comment_post'] : '';
        
        // If no webhook URL is set, return
        if (empty($webhook_data) || (is_array($webhook_data) && empty($webhook_data['url']))) {
            return;
        }
        
        // Get comment data
        $comment = \get_comment($comment_id);
        
        // Prepare comment data
        $comment_data = array(
            'id' => $comment_id,
            'post_id' => $comment->comment_post_ID,
            'author' => $comment->comment_author,
            'author_email' => $comment->comment_author_email,
            'author_url' => $comment->comment_author_url,
            'author_ip' => $comment->comment_author_IP,
            'content' => $comment->comment_content,
            'approved' => $comment->comment_approved,
            'date' => $comment->comment_date,
            'user_id' => $comment->user_id,
            'post_title' => \get_the_title($comment->comment_post_ID),
            'post_url' => \get_permalink($comment->comment_post_ID),
        );
        
        // Add comment meta
        $comment_meta = \get_comment_meta($comment_id);
        if (!empty($comment_meta)) {
            $comment_data['meta'] = array();
            foreach ($comment_meta as $meta_key => $meta_values) {
                $comment_data['meta'][$meta_key] = count($meta_values) === 1 ? $meta_values[0] : $meta_values;
            }
        }
        
        // Send data to n8n webhook
        $this->send_webhook_data($webhook_data, array(
            'trigger' => 'comment_post',
            'data' => $comment_data,
        ));
    }

    /**
     * Trigger when a new WooCommerce order is created.
     *
     * @since    1.0.0
     * @param    int    $order_id    The order ID.
     */
    public function trigger_woocommerce_new_order($order_id) {
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            return;
        }
        
        // Check if this trigger is enabled
        $enabled_triggers = \get_option('n8n_integration_enabled_triggers', array());
        if (!in_array('woocommerce_new_order', $enabled_triggers)) {
            return;
        }
        
        // Get webhook URL data
        $webhook_urls = \get_option('n8n_integration_webhook_urls', array());
        $webhook_data = isset($webhook_urls['woocommerce_new_order']) ? $webhook_urls['woocommerce_new_order'] : '';
        
        // If no webhook URL is set, return
        if (empty($webhook_data) || (is_array($webhook_data) && empty($webhook_data['url']))) {
            return;
        }
        
        // Use the payload builder to create enhanced order data
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-n8n-integration-payload-builder.php';
        $order_data = N8N_Integration_Payload_Builder::build_woocommerce_order_payload($order_id);
        
        // Add trigger-specific data
        $order_data['trigger'] = 'woocommerce_new_order';
        
        // Send data to n8n webhook
        $this->send_webhook_data($webhook_data, $order_data);
    }
    }

    /**
     * Send data to n8n webhook.
     *
     * @since    1.0.0
     * @param    string|array    $webhook_url    The webhook URL or webhook data array.
     * @param    array           $data           The data to send.
     * @return   array                           Response data including success status and message.
     */
    private function send_webhook_data($webhook_url, $data) {
        // Handle both string URLs and webhook data arrays
        $url = '';
        $webhook_name = '';
        
        if (is_string($webhook_url)) {
            $url = $webhook_url;
        } elseif (is_array($webhook_url) && isset($webhook_url['url'])) {
            $url = $webhook_url['url'];
            $webhook_name = isset($webhook_url['name']) ? $webhook_url['name'] : '';
        }
        
        // If no valid URL, return error
        if (empty($url)) {
            $error_message = 'n8n Integration: No webhook URL provided';
            error_log($error_message);
            return array(
                'success' => false,
                'message' => $error_message
            );
        }
        
        // Add site information
        $data['site'] = array(
            'name' => \get_bloginfo('name'),
            'url' => \get_site_url(),
            'admin_email' => \get_bloginfo('admin_email'),
            'version' => \get_bloginfo('version'),
            'language' => \get_bloginfo('language'),
        );
        
        // Add timestamp
        $data['timestamp'] = \current_time('timestamp');
        
        // Add webhook name if available
        if (!empty($webhook_name)) {
            $data['webhook_name'] = $webhook_name;
        }
        
        // Send data to webhook URL
        $response = \wp_remote_post($url, array(
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode($data),
            'timeout' => 15,
        ));
        
        // Log the request
        $this->log_webhook_request($url, $data, $response);
        
        // Check if request was successful
        if (\is_wp_error($response)) {
            $error_message = 'n8n Integration: Failed to send webhook data - ' . $response->get_error_message();
            error_log($error_message);
            return array(
                'success' => false,
                'message' => $error_message
            );
        }
        
        return array(
            'success' => true,
            'message' => 'Webhook data sent successfully',
            'response' => $response
        );
    }

    /**
     * Log webhook request for debugging and monitoring.
     *
     * @since    1.0.0
     * @param    string    $url        The webhook URL.
     * @param    array     $data       The data sent.
     * @param    mixed     $response   The response from the webhook.
     */
    private function log_webhook_request($url, $data, $response) {
        // Check if logging is enabled
        $debug_mode = \get_option('n8n_integration_debug_mode', false);
        if (!$debug_mode) {
            return;
        }
        
        // Prepare log data
        $log_entry = array(
            'timestamp' => \current_time('mysql'),
            'url' => $url,
            'data' => $data,
            'success' => !\is_wp_error($response),
            'response' => \is_wp_error($response) ? $response->get_error_message() : \wp_remote_retrieve_response_code($response)
        );
        
        // Get existing logs
        $logs = \get_option('n8n_integration_webhook_logs', array());
        
        // Add new log entry (limit to 100 entries)
        array_unshift($logs, $log_entry);
        if (count($logs) > 100) {
            $logs = array_slice($logs, 0, 100);
        }
        
        // Save logs
        \update_option('n8n_integration_webhook_logs', $logs);
    }
    
    /**
     * Clear webhook logs.
     *
     * @since    1.0.0
     * @return   void
     */
    public function clear_logs() {
        // Check if user has admin permissions
        if (!\current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        
        // Clear logs
        \update_option('n8n_integration_webhook_logs', array());
        
        wp_send_json_success('Logs cleared successfully');
    }
    
    /**
     * Test trigger by sending sample data to the webhook URL.
     *
     * @since    1.0.0
     * @return   void
     */
    public function test_trigger() {
        // Check if user has admin permissions
        if (!\current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        
        // Check nonce
        if (!isset($_POST['nonce']) || !\wp_verify_nonce($_POST['nonce'], 'n8n_integration_admin_nonce')) {
            wp_send_json_error('Invalid nonce');
        }
        
        // Get trigger ID and test data
        $trigger_id = isset($_POST['trigger']) ? \sanitize_text_field($_POST['trigger']) : '';
        $test_data_json = isset($_POST['test_data']) ? \wp_unslash($_POST['test_data']) : '';
        
        // Check if trigger ID is valid
        if (empty($trigger_id)) {
            wp_send_json_error('Invalid trigger ID');
        }
        
        // Check if trigger is enabled
        $enabled_triggers = \get_option('n8n_integration_enabled_triggers', array());
        if (!in_array($trigger_id, $enabled_triggers)) {
            wp_send_json_error('Trigger is not enabled');
        }
        
        // Get webhook URL data
        $webhook_urls = \get_option('n8n_integration_webhook_urls', array());
        $webhook_data = isset($webhook_urls[$trigger_id]) ? $webhook_urls[$trigger_id] : '';
        
        // If no webhook URL is set, return error
        if (empty($webhook_data) || (is_array($webhook_data) && empty($webhook_data['url']))) {
            wp_send_json_error('No webhook URL is set for this trigger');
        }
        
        // Parse test data
        $test_data = json_decode($test_data_json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error('Invalid JSON in test data: ' . json_last_error_msg());
        }
        
        // Use the payload builder to enhance the test data
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-n8n-integration-payload-builder.php';
        $enhanced_data = N8N_Integration_Payload_Builder::build_test_payload($trigger_id, $test_data);
        
        // Send data to n8n webhook
        $result = $this->send_webhook_data($webhook_data, array(
            'trigger' => $trigger_id,
            'data' => $enhanced_data,
            'test' => true
        ));
        
        // Return result
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
    
    /**
     * Get n8n API URL.
     *
     * @since    1.0.0
     * @return   string    The n8n API URL.
     */
    private function get_n8n_api_url() {
        $n8n_url = \get_option('n8n_integration_url', '');
        
        // If URL doesn't end with a slash, add it
        if (!empty($n8n_url) && substr($n8n_url, -1) !== '/') {
            $n8n_url .= '/';
        }
        
        return $n8n_url . 'api/v1/';
    }

    /**
     * Get n8n API key.
     *
     * @since    1.0.0
     * @return   string    The n8n API key.
     */
    private function get_n8n_api_key() {
        return \get_option('n8n_integration_n8n_api_key', '');
    }

    /**
     * Make a request to the n8n API.
     *
     * @since    1.0.0
     * @param    string    $endpoint    The API endpoint.
     * @param    string    $method      The HTTP method (GET, POST, etc.).
     * @param    array     $data        The data to send (for POST, PUT, etc.).
     * @return   array|\WP_Error         The response or error.
     */
    private function request_n8n_api($endpoint, $method = 'GET', $data = null) {
        $api_url = $this->get_n8n_api_url();
        $api_key = $this->get_n8n_api_key();
        
        if (empty($api_url)) {
            return new \WP_Error('n8n_api_error', 'n8n URL is not configured');
        }
        
        $url = $api_url . $endpoint;
        
        $args = array(
            'method' => $method,
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'timeout' => 30,
        );
        
        // Add API key if available
        if (!empty($api_key)) {
            $args['headers']['X-N8N-API-KEY'] = $api_key;
        }
        
        // Add data for POST, PUT, etc.
        if ($data !== null && in_array($method, array('POST', 'PUT', 'PATCH'))) {
            $args['body'] = json_encode($data);
        }
        
        $response = \wp_remote_request($url, $args);
        
        if (\is_wp_error($response)) {
            error_log('n8n Integration: API request failed - ' . $response->get_error_message());
            return $response;
        }
        
        $response_code = \wp_remote_retrieve_response_code($response);
        $response_body = \wp_remote_retrieve_body($response);
        
        if ($response_code < 200 || $response_code >= 300) {
            error_log('n8n Integration: API request failed with code ' . $response_code . ' - ' . $response_body);
            return new \WP_Error('n8n_api_error', 'API request failed with code ' . $response_code, array(
                'status' => $response_code,
                'body' => $response_body,
            ));
        }
        
        return json_decode($response_body, true);
    }

    /**
     * Get n8n workflows.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The request object.
     * @return   WP_REST_Response               The response object.
     */
    public function get_workflows($request) {
        $response = $this->request_n8n_api('workflows');
        
        if (\is_wp_error($response)) {
            return new \WP_REST_Response(array(
                'success' => false,
                'message' => $response->get_error_message(),
            ), 500);
        }
        
        return new \WP_REST_Response(array(
            'success' => true,
            'workflows' => $response,
        ), 200);
    }

    /**
     * Execute n8n workflow.
     *
     * @since    1.0.0
     * @param    \WP_REST_Request    $request    The request object.
     * @return   \WP_REST_Response               The response object.
     */
    public function execute_workflow($request) {
        $params = $request->get_params();
        
        // Check required parameters
        if (!isset($params['workflow_id'])) {
            return new \WP_REST_Response(array(
                'success' => false,
                'message' => 'Missing workflow_id parameter',
            ), 400);
        }
        
        $workflow_id = \sanitize_text_field($params['workflow_id']);
        $data = isset($params['data']) ? $params['data'] : array();
        
        // Add WordPress site information
        $data['site'] = array(
            'name' => \get_bloginfo('name'),
            'url' => \get_site_url(),
            'admin_email' => \get_bloginfo('admin_email'),
            'version' => \get_bloginfo('version'),
            'language' => \get_bloginfo('language'),
        );
        
        // Execute workflow
        $response = $this->request_n8n_api('workflows/' . $workflow_id . '/execute', 'POST', $data);
        
        if (\is_wp_error($response)) {
            return new \WP_REST_Response(array(
                'success' => false,
                'message' => $response->get_error_message(),
            ), 500);
        }
        
        return new \WP_REST_Response(array(
            'success' => true,
            'execution_id' => isset($response['executionId']) ? $response['executionId'] : null,
            'data' => $response,
        ), 200);
    }

    /**
     * Get n8n workflow execution status.
     *
     * @since    1.0.0
     * @param    \WP_REST_Request    $request    The request object.
     * @return   \WP_REST_Response               The response object.
     */
    public function get_workflow_status($request) {
        $execution_id = $request['execution_id'];
        
        $response = $this->request_n8n_api('executions/' . $execution_id);
        
        if (\is_wp_error($response)) {
            return new \WP_REST_Response(array(
                'success' => false,
                'message' => $response->get_error_message(),
            ), 500);
        }
        
        return new \WP_REST_Response(array(
            'success' => true,
            'status' => $response,
        ), 200);
    }

}