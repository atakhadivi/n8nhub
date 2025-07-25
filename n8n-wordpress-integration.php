<?php
/**
 * Plugin Name: n8n WordPress Integration
 * Plugin URI: https://github.com/yourusername/n8n-wordpress-integration
 * Description: Integrates WordPress with n8n workflow automation platform
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: n8n-wordpress-integration
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('N8N_INTEGRATION_VERSION', '1.0.0');
define('N8N_INTEGRATION_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('N8N_INTEGRATION_PLUGIN_URL', plugin_dir_url(__FILE__));
define('N8N_INTEGRATION_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * The core plugin class
 */
require_once N8N_INTEGRATION_PLUGIN_DIR . 'includes/class-n8n-integration.php';

/**
 * Begins execution of the plugin.
 */
function run_n8n_integration() {
    $plugin = new N8N_Integration();
    $plugin->run();
}
run_n8n_integration();