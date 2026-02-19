<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class KDR_IAA_Generator {

    /**
     * Generate alt text from a filename using plugin settings.
     *
     * @param string $filename e.g. "red-shoes_men.jpg"
     * @param array|null $settings pass settings array or null to load from KDR_IAA_Settings
     * @return string Generated alt text or empty string if skipped
     */
    public function generate_from_filename( string $filename, ?array $settings = null ) : string {

        if ( $settings === null ) {
            $settings = class_exists('KDR_IAA_Settings') ? KDR_IAA_Settings::get_all() : [];
        }

        $defaults = [
            'case_mode'       => 'sentence',
            'remove_prefixes' => 1,
            'skip_numeric'    => 1,
        ];
        $settings = array_merge( $defaults, is_array($settings) ? $settings : [] );

        // 1) Remove extension
        $name = pathinfo( $filename, PATHINFO_FILENAME );
        $name = trim( (string) $name );

        if ( $name === '' ) return '';

        // 2) Replace separators with spaces
        $name = str_replace( ['-', '_'], ' ', $name );

        // 3) Optionally remove common camera prefixes
        if ( ! empty( $settings['remove_prefixes'] ) ) {
            // Examples: IMG_1234, DSC_0001, PXL_20240101, WP_2020, etc.
            $name = preg_replace( '/\b(img|dsc|pxl|wp|photo)\s*/i', '', $name );
        }

        // 4) Remove leftover non-letter/number characters except spaces
        $name = preg_replace( '/[^A-Za-z0-9 ]+/', ' ', $name );

        // 5) Normalize spaces
        $name = preg_replace( '/\s+/', ' ', $name );
        $name = trim( $name );

        if ( $name === '' ) return '';

        // 6) Optionally skip numeric-only filenames
        if ( ! empty( $settings['skip_numeric'] ) ) {
            // if it becomes only digits after cleanup, skip
            if ( preg_match( '/^\d+$/', $name ) ) {
                return '';
            }
        }

        // 7) Apply casing
        $case_mode = (string) $settings['case_mode'];

        if ( $case_mode === 'title' ) {
            // Title Case (simple approach)
            $name = ucwords( strtolower( $name ) );
        } else {
            // Sentence case: first letter uppercase, rest as-is lower-ish
            $name = strtolower( $name );
            $name = ucfirst( $name );
        }

        return $name;
    }
}
