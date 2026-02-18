<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class KDR_IAA_Hooks {

    public function __construct() {
        add_action( 'add_attachment', [ $this, 'auto_generate_alt' ] );
    }

    public function auto_generate_alt( $attachment_id ) {

        if ( ! wp_attachment_is_image( $attachment_id ) ) {
            return;
        }

        $alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );

        if ( ! empty( $alt ) ) {
            return;
        }

        $file = get_attached_file( $attachment_id );
        if ( ! $file ) return;

        $filename = basename( $file );

        $generator = new KDR_IAA_Generator();
        $alt_text = $generator->generate_from_filename( $filename );

        if ( $alt_text ) {
            update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt_text );
        }
    }
}
