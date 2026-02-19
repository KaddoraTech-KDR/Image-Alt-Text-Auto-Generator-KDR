<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class KDR_IAA_Hooks {

    public function __construct() {
        add_action( 'add_attachment', [ $this, 'kdr_iaa_auto_generate_alt_on_upload' ] );
    }

    /**
     * Auto-generate alt text when an image is uploaded (if enabled).
     */
    public function kdr_iaa_auto_generate_alt_on_upload( $attachment_id ) {

        // Only images
        if ( ! wp_attachment_is_image( $attachment_id ) ) {
            return;
        }

        // Load settings
        if ( ! class_exists( 'KDR_IAA_Settings' ) ) {
            return;
        }

        $settings = KDR_IAA_Settings::get_all();

        // Feature toggle
        if ( empty( $settings['enable_upload'] ) ) {
            return;
        }

        $existing_alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );

        // Do not overwrite existing alt text unless explicitly enabled
        if ( ! empty( $existing_alt ) && empty( $settings['overwrite_existing'] ) ) {
            return;
        }

        // Get filename
        $file = get_attached_file( $attachment_id );
        if ( ! $file ) {
            return;
        }

        $filename = basename( $file );

        // Generate
        if ( ! class_exists( 'KDR_IAA_Generator' ) ) {
            return;
        }

        $generator = new KDR_IAA_Generator();
        $alt_text  = $generator->generate_from_filename( $filename, $settings );

        if ( $alt_text === '' ) {
            return;
        }

        update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt_text );
    }
}
