<?php

namespace wpmvc\debug\tabs;

/**
 * Database tab — connection summary and the list of executed queries with
 * timing, from `$wpdb->queries` (requires the `SAVEQUERIES` constant).
 */
class Database_Tab extends Tab {

    /**
     * Queries slower than this (in seconds) are counted/marked as slow.
     */
    const SLOW_THRESHOLD = 0.1;

    public function get_id() : string {
        return 'database';
    }

    public function get_label() : string {
        return 'Database';
    }

    public function get_icon() : string {
        return '<path d="M4.318 2.687C5.234 2.271 6.536 2 8 2s2.766.27 3.682.687C12.644 3.125 13 3.627 13 4c0 .374-.356.875-1.318 1.313C10.766 5.729 9.464 6 8 6s-2.766-.27-3.682-.687C3.356 4.875 3 4.373 3 4c0-.374.356-.875 1.318-1.313ZM13 5.698V7c0 .374-.356.875-1.318 1.313C10.766 8.729 9.464 9 8 9s-2.766-.27-3.682-.687C3.356 7.875 3 7.373 3 7V5.698c.271.202.58.378.904.525C4.978 6.711 6.427 7 8 7s3.022-.289 4.096-.777A4.92 4.92 0 0 0 13 5.698ZM14 4c0-1.007-.875-1.755-1.904-2.223C11.022 1.289 9.573 1 8 1s-3.022.289-4.096.777C2.875 2.245 2 2.993 2 4v9c0 1.007.875 1.755 1.904 2.223C4.978 15.71 6.427 16 8 16s3.022-.289 4.096-.777C13.125 14.755 14 14.007 14 13V4Zm-1 4.698V10c0 .374-.356.875-1.318 1.313C10.766 11.729 9.464 12 8 12s-2.766-.27-3.682-.687C3.356 10.875 3 10.373 3 10V8.698c.271.202.58.378.904.525C4.978 9.71 6.427 10 8 10s3.022-.289 4.096-.777A4.92 4.92 0 0 0 13 8.698Zm0 3V13c0 .374-.356.875-1.318 1.313C10.766 14.729 9.464 15 8 15s-2.766-.27-3.682-.687C3.356 13.875 3 13.373 3 13v-1.302c.271.202.58.378.904.525C4.978 12.71 6.427 13 8 13s3.022-.289 4.096-.777c.324-.147.633-.323.904-.525Z"/>';
    }

    public function get_badge() {
        global $wpdb;

        return (int) $wpdb->num_queries;
    }

    public function get_data() : array {
        global $wpdb;

        $enabled = defined( 'SAVEQUERIES' ) && SAVEQUERIES;
        $queries = array();
        $total   = 0.0;
        $slow    = 0;

        if ( $enabled && ! empty( $wpdb->queries ) ) {
            foreach ( $wpdb->queries as $query ) {
                $sql    = trim( (string) $query[0] );
                $time   = (float) $query[1];
                $chain  = array_map( 'trim', explode( ',', (string) $query[2] ) );
                $caller = end( $chain );

                $type = strtoupper( (string) strtok( $sql, " \t\r\n(" ) );

                if ( ! in_array( $type, array( 'SELECT', 'INSERT', 'UPDATE', 'DELETE' ), true ) ) {
                    $type = 'OTHER';
                }

                $total += $time;

                if ( $time > self::SLOW_THRESHOLD ) {
                    $slow++;
                }

                $queries[] = array(
                    'sql'    => $sql,
                    'type'   => $type,
                    'time'   => $time,
                    'slow'   => $time > self::SLOW_THRESHOLD,
                    'caller' => $caller,
                    'chain'  => $chain,
                );
            }
        }

        return array(
            'enabled' => $enabled,
            'queries' => $queries,
            'stats'   => array(
                array( 'label' => 'Queries', 'meta' => 'Executed', 'value' => (int) $wpdb->num_queries ),
                array( 'label' => 'Total Time', 'meta' => 'Query execution time', 'value' => number_format( $total * 1000, 2 ) . ' ms' ),
                array( 'label' => 'Slow Queries', 'meta' => '> ' . ( self::SLOW_THRESHOLD * 1000 ) . ' ms', 'value' => $slow ),
                array( 'label' => 'MySQL Server', 'meta' => DB_HOST, 'value' => $wpdb->db_version() ),
            ),
        );
    }

}
