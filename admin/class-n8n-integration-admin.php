<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 */
class N8N_Integration_Admin {

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
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, N8N_INTEGRATION_PLUGIN_URL . 'admin/css/n8n-integration-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, N8N_INTEGRATION_PLUGIN_URL . 'admin/js/n8n-integration-admin.js', array('jquery'), $this->version, false);
        
        // Localize the script with new data
        wp_localize_script($this->plugin_name, 'n8n_integration_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('n8n_integration_admin_nonce'),
            'rest_url' => rest_url('n8n-integration/v1/'),
            'rest_nonce' => wp_create_nonce('wp_rest'),
        ));
    }

    /**
     * Add menu item for the plugin.
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {
        add_menu_page(
            __('n8n Integration', 'n8n-wordpress-integration'),
            __('n8n Integration', 'n8n-wordpress-integration'),
            'manage_options',
            $this->plugin_name,
            array($this, 'display_plugin_admin_page'),
            'dashicons-rest-api',
            100
        );
        
        add_submenu_page(
            $this->plugin_name,
            __('Settings', 'n8n-wordpress-integration'),
            __('Settings', 'n8n-wordpress-integration'),
            'manage_options',
            $this->plugin_name,
            array($this, 'display_plugin_admin_page')
        );
        
        add_submenu_page(
            $this->plugin_name,
            __('Triggers', 'n8n-wordpress-integration'),
            __('Triggers', 'n8n-wordpress-integration'),
            'manage_options',
            $this->plugin_name . '-triggers',
            array($this, 'display_plugin_triggers_page')
        );
        
        add_submenu_page(
            $this->plugin_name,
            __('Actions', 'n8n-wordpress-integration'),
            __('Actions', 'n8n-wordpress-integration'),
            'manage_options',
            $this->plugin_name . '-actions',
            array($this, 'display_plugin_actions_page')
        );
        
        add_submenu_page(
            $this->plugin_name,
            __('Logs', 'n8n-wordpress-integration'),
            __('Logs', 'n8n-wordpress-integration'),
            'manage_options',
            $this->plugin_name . '-logs',
            array($this, 'display_plugin_logs_page')
        );
    }

    /**
     * Add action links to the plugin page.
     *
     * @since    1.0.0
     * @param    array    $links    The existing action links.
     * @return   array              The modified action links.
     */
    public function add_action_links($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=' . $this->plugin_name) . '">' . __('Settings', 'n8n-wordpress-integration') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Render the settings page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_page() {
        include_once N8N_INTEGRATION_PLUGIN_DIR . 'admin/partials/n8n-integration-admin-display.php';
    }

    /**
     * Render the triggers page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_plugin_triggers_page() {
        include_once N8N_INTEGRATION_PLUGIN_DIR . 'admin/partials/n8n-integration-admin-triggers.php';
    }

    /**
     * Render the actions page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_plugin_actions_page() {
        include_once N8N_INTEGRATION_PLUGIN_DIR . 'admin/partials/n8n-integration-admin-actions.php';
    }

    /**
     * Render the logs page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_plugin_logs_page() {
        include_once N8N_INTEGRATION_PLUGIN_DIR . 'admin/partials/n8n-integration-admin-logs.php';
    }

}