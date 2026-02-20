<?php
if (!defined('ABSPATH'))
    exit;

class KDR_IAA_Admin
{

    public function __construct()
    {
        add_action('admin_menu', [$this, 'kdr_iaa_register_menu']);
        add_action('admin_enqueue_scripts', [$this, 'kdr_iaa_enqueue_assets']);

        // Register bulk AJAX handlers (admin side)
        if (class_exists('KDR_IAA_Bulk')) {
            KDR_IAA_Bulk::register_ajax();
        }
    }

    /**
     * Menus:
     * - Top-level plugin menu for Settings
     * - Media page for Bulk generator
     */
    public function kdr_iaa_register_menu()
    {

        // Top-level plugin menu (Settings)
        add_menu_page(
            'Alt Text Generator KDR Settings',
            'Alt Text Generator KDR Settings',
            'manage_options',
            'kdr-iaa-settings',
            [$this, 'kdr_iaa_render_settings_page'],
            'dashicons-format-image',
            58
        );

        // Submenu: Settings
        add_submenu_page(
            'kdr-iaa-settings',
            'Alt Text Generator KDR Settings',
            'Settings',
            'manage_options',
            'kdr-iaa-settings',
            [$this, 'kdr_iaa_render_settings_page']
        );

        // Media menu: Bulk generator (tool)
        add_media_page(
            'Alt Text Generator KDR',
            'Alt Text Generator KDR',
            'manage_options',
            'kdr-iaa-bulk',
            [$this, 'kdr_iaa_render_bulk_page']
        );
    }

    public function kdr_iaa_render_bulk_page()
    {
        include KDR_IAA_PATH . 'admin/views/bulk-page.php';
    }

    public function kdr_iaa_render_settings_page()
    {
        include KDR_IAA_PATH . 'admin/views/settings-page.php';
    }

    /**
     * Load JS/CSS only on our plugin pages.
     */
    public function kdr_iaa_enqueue_assets($hook)
    {

        // Allowed hooks:
        // - media_page_kdr-iaa-bulk
        // - toplevel_page_kdr-iaa-settings
        if ($hook !== 'media_page_kdr-iaa-bulk' && $hook !== 'toplevel_page_kdr-iaa-settings') {
            return;
        }

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

        // Provide AJAX + nonce to JS
        $nonce = wp_create_nonce(KDR_IAA_Bulk::NONCE_ACTION);

        wp_localize_script(
            'kdr-iaa-admin',
            'kdr_iaa_ajax',
            [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => $nonce,
            ]
        );
    }
}
