<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class KDR_IAA_Bulk {

    const NONCE_ACTION = 'kdr_iaa_bulk_nonce';

    /**
     * Register AJAX actions (called from admin class).
     */
    public static function register_ajax() : void {
        add_action( 'wp_ajax_kdr_iaa_scan_missing',  [ __CLASS__, 'ajax_scan_missing' ] );
        add_action( 'wp_ajax_kdr_iaa_process_batch', [ __CLASS__, 'ajax_process_batch' ] );
    }

    /**
     * Security checks for all AJAX requests.
     */
    private static function ensure_access() : void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized' ], 403 );
        }

        $nonce = isset($_POST['nonce']) ? sanitize_text_field( wp_unslash($_POST['nonce']) ) : '';
        if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
            wp_send_json_error( [ 'message' => 'Invalid nonce' ], 403 );
        }
    }

    /**
     * Count images with missing/empty alt text.
     */
    public static function ajax_scan_missing() : void {
        self::ensure_access();

        $count = self::count_missing_alt();

        wp_send_json_success( [
            'missing' => $count,
        ] );
    }

    /**
     * Process next batch of images missing alt text.
     *
     * Request:
     * - offset (int): optional (default 0)
     *
     * Response:
     * - processed, generated, skipped, errors
     * - next_offset
     * - done (bool)
     */
    public static function ajax_process_batch() : void {
        self::ensure_access();

        if ( ! class_exists('KDR_IAA_Settings') || ! class_exists('KDR_IAA_Generator') ) {
            wp_send_json_error( [ 'message' => 'Core classes missing' ], 500 );
        }

        $settings   = KDR_IAA_Settings::get_all();
        $batch_size = isset($settings['batch_size']) ? absint($settings['batch_size']) : 50;
        if ( $batch_size < 10 ) $batch_size = 10;
        if ( $batch_size > 200 ) $batch_size = 200;

        $offset = isset($_POST['offset']) ? absint($_POST['offset']) : 0;

        // Fetch next batch (IDs only)
        $ids = self::get_missing_alt_ids( $batch_size, $offset );

        $generator = new KDR_IAA_Generator();

        $processed = 0;
        $generated = 0;
        $skipped   = 0;
        $errors    = 0;

        foreach ( $ids as $attachment_id ) {
            $processed++;

            // Re-check if still image + missing alt (race-safe)
            if ( ! wp_attachment_is_image( $attachment_id ) ) {
                $skipped++;
                continue;
            }

            $existing_alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );

            if ( ! empty( $existing_alt ) && empty( $settings['overwrite_existing'] ) ) {
                $skipped++;
                continue;
            }

            $file = get_attached_file( $attachment_id );
            if ( ! $file ) {
                $errors++;
                continue;
            }

            $filename = basename( $file );

            $alt_text = $generator->generate_from_filename( $filename, $settings );

            if ( $alt_text === '' ) {
                // generator decided to skip (e.g., numeric-only)
                $skipped++;
                continue;
            }

            $ok = update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt_text );

            if ( $ok !== false ) {
                $generated++;
            } else {
                $errors++;
            }
        }

        $next_offset = $offset + count( $ids );

        // If we returned fewer than batch_size, we reached the end.
        $done = ( count( $ids ) < $batch_size );

        wp_send_json_success( [
            'processed'   => $processed,
            'generated'   => $generated,
            'skipped'     => $skipped,
            'errors'      => $errors,
            'next_offset' => $next_offset,
            'done'        => $done,
            'batch_size'  => $batch_size,
        ] );
    }

    /**
     * Query: count images where alt is missing OR empty string.
     */
    private static function count_missing_alt() : int {
        global $wpdb;

        // Count attachments that are images and either:
        // - no _wp_attachment_image_alt meta row exists, OR
        // - meta value is empty
        $sql = "
            SELECT COUNT(DISTINCT p.ID)
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm
                ON pm.post_id = p.ID AND pm.meta_key = '_wp_attachment_image_alt'
            WHERE p.post_type = 'attachment'
              AND p.post_mime_type LIKE 'image/%'
              AND (pm.meta_id IS NULL OR pm.meta_value = '')
        ";

        return (int) $wpdb->get_var( $sql );
    }

    /**
     * Get attachment IDs with missing/empty alt text (paged with limit + offset).
     */
    private static function get_missing_alt_ids( int $limit, int $offset ) : array {
        global $wpdb;

        $limit  = max( 1, (int) $limit );
        $offset = max( 0, (int) $offset );

        $sql = $wpdb->prepare("
            SELECT DISTINCT p.ID
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm
                ON pm.post_id = p.ID AND pm.meta_key = '_wp_attachment_image_alt'
            WHERE p.post_type = 'attachment'
              AND p.post_mime_type LIKE 'image/%'
              AND (pm.meta_id IS NULL OR pm.meta_value = '')
            ORDER BY p.ID ASC
            LIMIT %d OFFSET %d
        ", $limit, $offset );

        $rows = $wpdb->get_col( $sql );

        return array_map( 'intval', $rows );
    }
}
