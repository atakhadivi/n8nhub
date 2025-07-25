<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @since      1.0.0
 */
class N8N_Integration {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      N8N_Integration_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if (defined('N8N_INTEGRATION_VERSION')) {
            $this->version = N8N_INTEGRATION_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'n8n-wordpress-integration';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_api_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - N8N_Integration_Loader. Orchestrates the hooks of the plugin.
     * - N8N_Integration_i18n. Defines internationalization functionality.
     * - N8N_Integration_Admin. Defines all hooks for the admin area.
     * - N8N_Integration_Public. Defines all hooks for the public side of the site.
     * - N8N_Integration_API. Defines all hooks for the API functionality.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once N8N_INTEGRATION_PLUGIN_DIR . 'includes/class-n8n-integration-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once N8N_INTEGRATION_PLUGIN_DIR . 'includes/class-n8n-integration-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once N8N_INTEGRATION_PLUGIN_DIR . 'admin/class-n8n-integration-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once N8N_INTEGRATION_PLUGIN_DIR . 'public/class-n8n-integration-public.php';

        /**
         * The class responsible for defining all actions related to the API functionality.
         */
        require_once N8N_INTEGRATION_PLUGIN_DIR . 'includes/class-n8n-integration-api.php';

        $this->loader = new N8N_Integration_Loader();

    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the N8N_Integration_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {

        $plugin_i18n = new N8N_Integration_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');

    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {

        $plugin_admin = new N8N_Integration_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        
        // Add menu item
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
        
        // Add Settings link to the plugin
        $this->loader->add_filter('plugin_action_links_' . N8N_INTEGRATION_PLUGIN_BASENAME, $plugin_admin, 'add_action_links');

    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {

        $plugin_public = new N8N_Integration_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

    }

    /**
     * Register all of the hooks related to the API functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_api_hooks() {

        $plugin_api = new N8N_Integration_API($this->get_plugin_name(), $this->get_version());

        // Register REST API endpoints
        $this->loader->add_action('rest_api_init', $plugin_api, 'register_routes');
        
        // Register WordPress hooks for n8n triggers
        $this->loader->add_action('save_post', $plugin_api, 'trigger_post_save', 10, 3);
        $this->loader->add_action('user_register', $plugin_api, 'trigger_user_register', 10, 1);
        $this->loader->add_action('comment_post', $plugin_api, 'trigger_comment_post', 10, 3);
        $this->loader->add_action('woocommerce_new_order', $plugin_api, 'trigger_woocommerce_new_order', 10, 1);

    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    N8N_Integration_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }

}