<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class KDR_IAA_Generator {

    public function generate_from_filename( $filename ) {

        $name = pathinfo( $filename, PATHINFO_FILENAME );

        $name = str_replace( ['-', '_'], ' ', $name );

        $name = preg_replace('/\s+/', ' ', $name);

        $name = trim( $name );

        $name = ucfirst( $name );

        return $name;
    }
}
