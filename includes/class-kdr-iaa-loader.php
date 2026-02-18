<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class KDR_IAA_Loader {

    public function init() {

        require_once KDR_IAA_PATH . 'includes/class-kdr-iaa-settings.php';
        require_once KDR_IAA_PATH . 'includes/class-kdr-iaa-generator.php';
        require_once KDR_IAA_PATH . 'includes/class-kdr-iaa-hooks.php';
        require_once KDR_IAA_PATH . 'includes/class-kdr-iaa-bulk.php';

        if ( is_admin() ) {
            require_once KDR_IAA_PATH . 'admin/class-kdr-iaa-admin.php';
            new KDR_IAA_Admin();
        }

        new KDR_IAA_Hooks();
    }
}
