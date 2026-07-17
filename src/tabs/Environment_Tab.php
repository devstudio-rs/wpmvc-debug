<?php

namespace wpmvc\debug\tabs;

use wpmvc\debug\Debug;

/**
 * Environment tab — server, PHP, WordPress and database configuration,
 * loaded PHP extensions, environment variables and notable paths.
 */
class Environment_Tab extends Tab {

    public function get_id() : string {
        return 'environment';
    }

    public function get_label() : string {
        return 'Environment';
    }

    public function get_icon() : string {
        return '<path fill-rule="evenodd" d="M11.5 2a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zM9.05 3a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0V3h9.05zM4.5 7a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zM2.05 8a2.5 2.5 0 0 1 4.9 0H16v1H6.95a2.5 2.5 0 0 1-4.9 0H0V8h2.05zm9.45 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zm-2.45 1a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0v-1h9.05z"/>';
    }

    public function get_data() : array {
        global $wpdb;

        $server = function ( $key ) {
            return isset( $_SERVER[ $key ] ) ? sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) ) : '—';
        };

        $extensions = get_loaded_extensions();
        natcasesort( $extensions );

        $env = array();

        foreach ( getenv() as $key => $value ) {
            // Never print credentials, even in a local tool.
            if ( preg_match( '/PASSWORD|SECRET|TOKEN|AUTH|_KEY/i', $key ) ) {
                $value = '••••••';
            }

            $env[ $key ] = $value;
        }

        ksort( $env );

        $uploads = wp_get_upload_dir();

        return array(
            'sections' => array(
                array(
                    'title' => 'Server',
                    'icon'  => '<path d="M5 0a.5.5 0 0 1 .5.5V2h1V.5a.5.5 0 0 1 1 0V2h1V.5a.5.5 0 0 1 1 0V2h1V.5a.5.5 0 0 1 1 0V2A2.5 2.5 0 0 1 14 4.5h1.5a.5.5 0 0 1 0 1H14v1h1.5a.5.5 0 0 1 0 1H14v1h1.5a.5.5 0 0 1 0 1H14v1h1.5a.5.5 0 0 1 0 1H14a2.5 2.5 0 0 1-2.5 2.5v1.5a.5.5 0 0 1-1 0V14h-1v1.5a.5.5 0 0 1-1 0V14h-1v1.5a.5.5 0 0 1-1 0V14h-1v1.5a.5.5 0 0 1-1 0V14A2.5 2.5 0 0 1 2 11.5H.5a.5.5 0 0 1 0-1H2v-1H.5a.5.5 0 0 1 0-1H2v-1H.5a.5.5 0 0 1 0-1H2v-1H.5a.5.5 0 0 1 0-1H2A2.5 2.5 0 0 1 4.5 2V.5A.5.5 0 0 1 5 0zm-.5 3A1.5 1.5 0 0 0 3 4.5v7A1.5 1.5 0 0 0 4.5 13h7a1.5 1.5 0 0 0 1.5-1.5v-7A1.5 1.5 0 0 0 11.5 3h-7zM5 6.5A1.5 1.5 0 0 1 6.5 5h3A1.5 1.5 0 0 1 11 6.5v3A1.5 1.5 0 0 1 9.5 11h-3A1.5 1.5 0 0 1 5 9.5v-3zM6.5 6a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3z"/>',
                    'items' => array(
                        array( 'label' => 'OS', 'value' => php_uname( 's' ) . ' ' . php_uname( 'r' ) ),
                        array( 'label' => 'Server Software', 'value' => $server( 'SERVER_SOFTWARE' ) ),
                        array( 'label' => 'Hostname', 'value' => (string) gethostname() ),
                        array( 'label' => 'IP Address', 'value' => $server( 'SERVER_ADDR' ) ),
                        array( 'label' => 'Document Root', 'value' => $server( 'DOCUMENT_ROOT' ) ),
                        array( 'label' => 'Protocol', 'value' => $server( 'SERVER_PROTOCOL' ) ),
                    ),
                ),
                array(
                    'title' => 'PHP',
                    'icon'  => '<path d="M10.478 1.647a.5.5 0 1 0-.956-.294l-4 13a.5.5 0 0 0 .956.294l4-13zM4.854 4.146a.5.5 0 0 1 0 .708L1.707 8l3.147 3.146a.5.5 0 0 1-.708.708l-3.5-3.5a.5.5 0 0 1 0-.708l3.5-3.5a.5.5 0 0 1 .708 0zm6.292 0a.5.5 0 0 0 0 .708L14.293 8l-3.147 3.146a.5.5 0 0 0 .708.708l3.5-3.5a.5.5 0 0 0 0-.708l-3.5-3.5a.5.5 0 0 0-.708 0z"/>',
                    'items' => array(
                        array( 'label' => 'Version', 'value' => PHP_VERSION ),
                        array( 'label' => 'SAPI', 'value' => php_sapi_name() ),
                        array( 'label' => 'Memory Limit', 'value' => ini_get( 'memory_limit' ) ),
                        array( 'label' => 'Max Execution Time', 'value' => ini_get( 'max_execution_time' ) ),
                        array( 'label' => 'Upload Max Filesize', 'value' => ini_get( 'upload_max_filesize' ) ),
                        array( 'label' => 'Post Max Size', 'value' => ini_get( 'post_max_size' ) ),
                        array( 'label' => 'Date/Time', 'value' => date_i18n( 'Y-m-d H:i:s' ) ),
                        array( 'label' => 'Timezone', 'value' => wp_timezone_string() ),
                    ),
                ),
                array(
                    'title' => 'WordPress',
                    'icon'  => '<path d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm7.5-6.923c-.67.204-1.335.82-1.887 1.855A7.97 7.97 0 0 0 5.145 4H7.5V1.077zM4.09 4a9.267 9.267 0 0 1 .64-1.539 6.7 6.7 0 0 1 .597-.933A7.025 7.025 0 0 0 2.255 4H4.09zm-.582 3.5c.03-.877.138-1.718.312-2.5H1.674a6.958 6.958 0 0 0-.656 2.5h2.49zM4.847 5a12.5 12.5 0 0 0-.338 2.5H7.5V5H4.847zM8.5 5v2.5h2.99a12.495 12.495 0 0 0-.337-2.5H8.5zM4.51 8.5a12.5 12.5 0 0 0 .337 2.5H7.5V8.5H4.51zm3.99 0V11h2.653c.187-.765.306-1.608.338-2.5H8.5zM5.145 12c.138.386.295.744.468 1.068.552 1.035 1.218 1.65 1.887 1.855V12H5.145zm.182 2.472a6.696 6.696 0 0 1-.597-.933A9.268 9.268 0 0 1 4.09 12H2.255a7.024 7.024 0 0 0 3.072 2.472zM3.82 11a13.652 13.652 0 0 1-.312-2.5h-2.49c.062.89.291 1.733.656 2.5H3.82zm6.853 3.472A7.024 7.024 0 0 0 13.745 12H11.91a9.27 9.27 0 0 1-.64 1.539 6.688 6.688 0 0 1-.597.933zM8.5 12v2.923c.67-.204 1.335-.82 1.887-1.855.173-.324.33-.682.468-1.068H8.5zm3.68-1h2.146c.365-.767.594-1.61.656-2.5h-2.49a13.65 13.65 0 0 1-.312 2.5zm2.802-3.5a6.959 6.959 0 0 0-.656-2.5H12.18c.174.782.282 1.623.312 2.5h2.49zM11.27 2.461c.247.464.462.98.64 1.539h1.835a7.024 7.024 0 0 0-3.072-2.472c.218.284.418.598.597.933zM10.855 4a7.966 7.966 0 0 0-.468-1.068C9.835 1.897 9.17 1.282 8.5 1.077V4h2.355z"/>',
                    'items' => array(
                        array( 'label' => 'Version', 'value' => get_bloginfo( 'version' ) ),
                        array( 'label' => 'Site URL', 'value' => site_url() ),
                        array( 'label' => 'Home URL', 'value' => home_url() ),
                        array( 'label' => 'Multisite', 'value' => is_multisite() ? 'Yes' : 'No', 'badge' => is_multisite() ? 'success' : 'danger' ),
                        array( 'label' => 'Language', 'value' => get_locale() ),
                        array( 'label' => 'Debug Mode', 'value' => WP_DEBUG ? 'Enabled' : 'Disabled', 'badge' => WP_DEBUG ? 'success' : 'danger' ),
                        array( 'label' => 'WP Memory Limit', 'value' => WP_MEMORY_LIMIT ),
                        array( 'label' => 'WP Max Upload', 'value' => size_format( wp_max_upload_size() ) ),
                    ),
                ),
                array(
                    'title' => 'Database',
                    'icon'  => '<path d="M4.318 2.687C5.234 2.271 6.536 2 8 2s2.766.27 3.682.687C12.644 3.125 13 3.627 13 4c0 .374-.356.875-1.318 1.313C10.766 5.729 9.464 6 8 6s-2.766-.27-3.682-.687C3.356 4.875 3 4.373 3 4c0-.374.356-.875 1.318-1.313ZM13 5.698V7c0 .374-.356.875-1.318 1.313C10.766 8.729 9.464 9 8 9s-2.766-.27-3.682-.687C3.356 7.875 3 7.373 3 7V5.698c.271.202.58.378.904.525C4.978 6.711 6.427 7 8 7s3.022-.289 4.096-.777A4.92 4.92 0 0 0 13 5.698ZM14 4c0-1.007-.875-1.755-1.904-2.223C11.022 1.289 9.573 1 8 1s-3.022.289-4.096.777C2.875 2.245 2 2.993 2 4v9c0 1.007.875 1.755 1.904 2.223C4.978 15.71 6.427 16 8 16s3.022-.289 4.096-.777C13.125 14.755 14 14.007 14 13V4Zm-1 4.698V10c0 .374-.356.875-1.318 1.313C10.766 11.729 9.464 12 8 12s-2.766-.27-3.682-.687C3.356 10.875 3 10.373 3 10V8.698c.271.202.58.378.904.525C4.978 9.71 6.427 10 8 10s3.022-.289 4.096-.777A4.92 4.92 0 0 0 13 8.698Zm0 3V13c0 .374-.356.875-1.318 1.313C10.766 14.729 9.464 15 8 15s-2.766-.27-3.682-.687C3.356 13.875 3 13.373 3 13v-1.302c.271.202.58.378.904.525C4.978 12.71 6.427 13 8 13s3.022-.289 4.096-.777c.324-.147.633-.323.904-.525Z"/>',
                    'items' => array(
                        array( 'label' => 'Driver', 'value' => 'MySQLi' ),
                        array( 'label' => 'Server', 'value' => DB_HOST ),
                        array( 'label' => 'Database', 'value' => DB_NAME ),
                        array( 'label' => 'User', 'value' => DB_USER ),
                        array( 'label' => 'Charset', 'value' => $wpdb->charset ),
                        array( 'label' => 'Collation', 'value' => $wpdb->collate ? $wpdb->collate : '—' ),
                        array( 'label' => 'Prefix', 'value' => $wpdb->prefix ),
                        array( 'label' => 'Version', 'value' => $wpdb->db_version() ),
                    ),
                ),
            ),
            'extensions' => array_values( $extensions ),
            'env'        => $env,
            'paths'      => array(
                'ABSPATH'         => ABSPATH,
                'WP_CONTENT_DIR'  => WP_CONTENT_DIR,
                'WP_PLUGIN_DIR'   => WP_PLUGIN_DIR,
                'Theme'           => get_template_directory(),
                'Uploads'         => $uploads['basedir'],
                'WPMVC'           => \wpmvc\App::$base_path,
                'WPMVC Debug'     => Debug::$root,
            ),
        );
    }

}
