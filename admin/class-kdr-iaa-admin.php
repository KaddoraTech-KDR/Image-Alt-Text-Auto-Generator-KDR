<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class KDR_IAA_Admin {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    }

   public function register_menu() {

    // Top-level menu (Settings)
    add_menu_page(
        'Alt Text Generator KDR',
        'Alt Text Generator KDR',
        'manage_options',
        'kdr-iaa-settings',
        [ $this, 'render_settings_page' ],
        'dashicons-format-image',
        58
    );

    // Submenu: Settings (same page)
    add_submenu_page(
        'kdr-iaa-settings',
        'Settings',
        'Settings',
        'manage_options',
        'kdr-iaa-settings',
        [ $this, 'render_settings_page' ]
    );

    // Media menu: Bulk generator
    add_media_page(
        'Alt Text Generator KDR',
        'Alt Text Generator KDR',
        'manage_options',
        'kdr-iaa-bulk',
        [ $this, 'render_bulk_page' ]
    );
}


    public function render_bulk_page() {
        include KDR_IAA_PATH . 'admin/views/bulk-page.php';
    }

    public function render_settings_page() {
        include KDR_IAA_PATH . 'admin/views/settings-page.php';
    }

    public function enqueue_assets( $hook ) {

        if ( strpos( $hook, 'kdr-iaa' ) === false ) return;

        wp_enqueue_style(
            'kdr-iaa-admin',
            KDR_IAA_URL . 'assets/admin.css',
            [],
            KDR_IAA_VERSION
        );

        wp_enqueue_script(
            'kdr-iaa-admin',
            KDR_IAA_URL . 'assets/admin.js',
            ['jquery'],
            KDR_IAA_VERSION,
            true
        );
    }
}
