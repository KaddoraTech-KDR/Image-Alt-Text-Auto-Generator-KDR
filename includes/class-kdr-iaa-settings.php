<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class KDR_IAA_Settings {

    const OPTION_KEY = 'kdr_iaa_settings';

    /**
     * Default settings for the plugin.
     */
    public static function defaults() : array {
        return [
            'enable_upload'      => 1,          // auto-generate on upload
            'case_mode'          => 'sentence',  // sentence | title
            'remove_prefixes'    => 1,          // IMG_, DSC_, etc.
            'skip_numeric'       => 1,          // skip if filename is only numbers
            'overwrite_existing' => 0,          // do NOT overwrite alt by default
            'batch_size'         => 50,         // bulk batch size
        ];
    }

    /**
     * Get all settings merged with defaults.
     */
    public static function get_all() : array {
        $saved = get_option( self::OPTION_KEY, [] );
        if ( ! is_array( $saved ) ) $saved = [];
        return array_merge( self::defaults(), $saved );
    }

    /**
     * Get a single setting value.
     */
    public static function get( string $key, $default = null ) {
        $all = self::get_all();
        return array_key_exists( $key, $all ) ? $all[ $key ] : $default;
    }

    /**
     * Update settings (expects raw input array), sanitizes and saves.
     */
    public static function update( array $raw ) : array {
        $clean = self::sanitize( $raw );
        update_option( self::OPTION_KEY, $clean );
        return $clean;
    }

    /**
     * Sanitize raw settings array.
     */
    public static function sanitize( array $raw ) : array {
        $defaults = self::defaults();

        $clean = [];

        $clean['enable_upload'] = isset( $raw['enable_upload'] ) ? 1 : 0;

        $case_mode = isset( $raw['case_mode'] ) ? sanitize_text_field( $raw['case_mode'] ) : $defaults['case_mode'];
        $clean['case_mode'] = in_array( $case_mode, ['sentence','title'], true ) ? $case_mode : $defaults['case_mode'];

        $clean['remove_prefixes'] = isset( $raw['remove_prefixes'] ) ? 1 : 0;
        $clean['skip_numeric']    = isset( $raw['skip_numeric'] ) ? 1 : 0;
        $clean['overwrite_existing'] = isset( $raw['overwrite_existing'] ) ? 1 : 0;

        $batch_size = isset( $raw['batch_size'] ) ? absint( $raw['batch_size'] ) : $defaults['batch_size'];
        if ( $batch_size < 10 ) $batch_size = 10;
        if ( $batch_size > 200 ) $batch_size = 200;
        $clean['batch_size'] = $batch_size;

        return array_merge( $defaults, $clean );
    }
}
