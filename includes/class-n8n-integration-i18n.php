<?php

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 */
class N8N_Integration_i18n {

    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain() {

        load_plugin_textdomain(
            'n8n-wordpress-integration',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );

    }

}