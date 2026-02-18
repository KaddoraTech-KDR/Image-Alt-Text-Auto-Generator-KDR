<?php
/**
 * Plugin Name: Image Alt Text Auto Generator KDR
 * Description: Automatically generate missing image alt text from filenames and bulk update media library.
 * Version: 1.0.0
 * Author: Kaddora Tech
 * Text Domain: iaa-kdr
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'KDR_IAA_VERSION', '0.1.0' );
define( 'KDR_IAA_PATH', plugin_dir_path( __FILE__ ) );
define( 'KDR_IAA_URL', plugin_dir_url( __FILE__ ) );

/**
 * Load plugin
 */
require_once KDR_IAA_PATH . 'includes/class-kdr-iaa-loader.php';

function kdr_iaa_init_plugin() {
    $loader = new KDR_IAA_Loader();
    $loader->init();
}
add_action( 'plugins_loaded', 'kdr_iaa_init_plugin' );
