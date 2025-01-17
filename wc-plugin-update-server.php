<?php
/**
 * Plugin Name:     WooCommerce and Plugin Update Server integration
 * Plugin URI:      http://rwsite.ru
 * Description:
 * Version:         1.0.4
 * Author:          Aleksey Tikhomirov
 * Author URI:      http://rwsite.ru
 * Text Domain:     wc-pus
 * Domain Path:     /languages
 *
 * Requires at least: 5.6
 * Requires PHP: 7.0
 * WC requires at least: 6.0
 * WC tested up to: 6.6.0
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 3, as published by the Free Software Foundation. You may NOT assume
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @author          Aleksey <support@rwsite.ru>
 * @copyright       Copyright (c) Aleksey Tikhomirov
 * @license         http://www.gnu.org/licenses/gpl-3.0.html
 *
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('Lic_Manager_Plugin')):
class Lic_Manager_Plugin {

    private static $instance;

    /**
     * @return LicOrder
     */
    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    private function __construct()
    {
        $this->file = __FILE__;
        $this->get_plugin_data();
        $this->includes();
        $this->load_textdomain();
    }

    /**
     * Setup plugin data
     */
    public function get_plugin_data()
    {
        $this->plugin_data = get_file_data($this->file, [
            'version'     => 'Version',
            'author'      => 'Author',
            'name'        => 'Plugin Name',
            'locale'      => 'Text Domain',
            'description' => 'Description',
            'plugin_url'  => 'Plugin URI'
        ]);
        $this->version          = $this->plugin_data["version"];
        $this->key              = $this->plugin_data["locale"];
        $this->locale           = $this->plugin_data["locale"];
        $this->name             = $this->plugin_data["name"];

        return $this->plugin_data;
    }

    /**
     * Include necessary files
     */
    private function includes() {
        // Get out if WC is not active
        if (!function_exists('WC') || !class_exists('WPPUS_License_Server')) {
            add_action( 'admin_notices', function(){ ?>
                <div class="notice notice-error is-dismissible"><p>
                        <?php echo __('Woocommerce or WPPUS_License_Server is not activated. To work this plugin, you need to install and activate WooCommerce and WPPUS_License_Server plugins.', 'wc-pus'); ?>
                </div>
            <?php });
            return;
        }

        require_once __DIR__ .  '/includes/LicProduct.php';
        require_once __DIR__ . '/includes/LicOrder.php';
        require_once __DIR__ . '/includes/Software_Licence_Manager_integration.php';
        require_once __DIR__ . '/includes/LicOrderMetaBox.php';

        if (is_admin()) {
            // new Software_Licence_Manager_integration();
        }

        (new LicOrderMetaBox())->add_actions();
        (new LicProduct())->add_actions();
        (new LicOrder())->add_actions();
    }

    /**
     * Internationalization
     */
    public function load_textdomain() {
        // Load the default language files
        load_plugin_textdomain('wc-pus', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
    }

    public static function activation() {
        // nothing
    }
    public static function uninstall() {
        // nothing
    }
}

register_activation_hook(__FILE__, ['Lic_Manager_Plugin', 'activation']);
register_uninstall_hook(__FILE__,  ['Lic_Manager_Plugin', 'uninstall']);

add_action('plugins_loaded', ['Lic_Manager_Plugin','getInstance'], 20);
endif;