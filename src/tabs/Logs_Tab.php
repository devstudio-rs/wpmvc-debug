<?php

namespace wpmvc\debug\tabs;

use wpmvc\base\App;
use wpmvc\components\Logger;

/**
 * Logs tab — two sub-tabs: the WordPress debug log (WP_DEBUG_LOG) and the
 * WPMVC logger component's files (when a logger component is configured).
 *
 * The debugger only reads existing log files; it never writes or triggers
 * logging, and never instantiates the logger to read its configuration.
 */
class Logs_Tab extends Tab {

    /** Read at most this many bytes from the tail of a log file. */
    const MAX_BYTES = 131072;

    /** Show at most this many (most recent) entries per source. */
    const MAX_ENTRIES = 300;

    /** Memoized data (get_badge + get_data share one build per render). */
    private $data;

    public function get_id() : string {
        return 'logs';
    }

    public function get_label() : string {
        return 'Logs';
    }

    public function get_icon() : string {
        return '<path d="M5 4a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1H5zm-.5 2.5A.5.5 0 0 1 5 6h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5zM5 8a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1H5zm0 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1H5z"/><path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2zm10-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1z"/>';
    }

    public function get_badge() {
        $data = $this->get_data();

        return count( $data['wordpress']['entries'] ) + $data['wpmvc']['count'];
    }

    public function register_ajax() {
        add_action( 'wp_ajax_wpmvc_debug_clear_log', array( $this, 'ajax_clear_log' ) );
    }

    /**
     * AJAX: clear a log ('wordpress' or 'logger').
     *
     * @return void
     */
    public function ajax_clear_log() {
        $this->verify_ajax();

        $target = isset( $_POST['target'] ) ? sanitize_key( wp_unslash( $_POST['target'] ) ) : '';

        if ( ! in_array( $target, array( 'wordpress', 'logger' ), true ) ) {
            wp_send_json_error( array( 'message' => 'Unknown target' ), 400 );
        }

        wp_send_json_success( array( 'cleared' => $this->clear( $target ) ) );
    }

    public function get_data() : array {
        if ( null === $this->data ) {
            $this->data = array(
                'wordpress' => $this->wordpress_data(),
                'wpmvc'     => $this->wpmvc_data(),
            );
        }

        return $this->data;
    }

    /**
     * WordPress debug log: resolve the WP_DEBUG_LOG path, parse the tail.
     *
     * @return array
     */
    private function wordpress_data() : array {
        $path    = $this->wordpress_log_path();
        $entries = ( $path && is_readable( $path ) ) ? $this->parse_wordpress( $this->tail( $path ) ) : array();

        return array(
            'enabled' => (bool) ( defined( 'WP_DEBUG_LOG' ) ? WP_DEBUG_LOG : false ),
            'path'    => $path,
            'entries' => $entries,
            'levels'  => $this->count_levels( $entries ),
        );
    }

    /**
     * Resolve the WordPress debug log path from WP_DEBUG_LOG, or null when
     * file logging is off.
     *
     * @return string|null
     */
    private function wordpress_log_path() {
        $constant = defined( 'WP_DEBUG_LOG' ) ? WP_DEBUG_LOG : false;

        if ( is_string( $constant ) ) {
            return $constant;
        }

        if ( true === $constant ) {
            return WP_CONTENT_DIR . '/debug.log';
        }

        return null;
    }

    /**
     * Resolved log directories of every configured logger component,
     * derived from declared config without instantiating the logger.
     *
     * @return string[]
     */
    private function logger_directories() : array {
        $directories = array();

        foreach ( App::instances() as $app ) {
            $configs = $app->get_component_configs();

            if ( empty( $configs['logger']['class'] ) ) {
                continue;
            }

            $class = $configs['logger']['class'];

            if ( ! is_a( $class, Logger::class, true ) ) {
                continue;
            }

            $defaults  = get_class_vars( $class );
            $directory = isset( $configs['logger']['directory'] ) ? $configs['logger']['directory'] : ( $defaults['directory'] ?? '@upload.basedir/logs' );

            $directories[] = $app->get_alias( $directory );
        }

        return array_values( array_unique( $directories ) );
    }

    /**
     * Empty a log ('wordpress' or 'logger'). Admins-only / nonce checks are
     * the caller's responsibility (see Debug::ajax_clear_log()).
     *
     * @param string $target
     * @return bool True when at least one file was cleared.
     */
    public function clear( string $target ) : bool {
        if ( 'wordpress' === $target ) {
            $path = $this->wordpress_log_path();

            return $path && is_writable( $path ) && false !== file_put_contents( $path, '' );
        }

        if ( 'logger' === $target ) {
            $cleared = false;

            foreach ( $this->logger_directories() as $directory ) {
                foreach ( (array) glob( trailingslashit( $directory ) . '*.log' ) as $file ) {
                    if ( is_writable( $file ) && false !== file_put_contents( $file, '' ) ) {
                        $cleared = true;
                    }
                }
            }

            return $cleared;
        }

        return false;
    }

    /**
     * WPMVC logger: find apps declaring a logger component, resolve each
     * log directory from the declared config (without instantiating), and
     * parse every *.log file found.
     *
     * @return array
     */
    private function wpmvc_data() : array {
        $sources = $this->logger_directories();
        $entries = array();

        foreach ( $sources as $directory ) {
            if ( ! is_dir( $directory ) ) {
                continue;
            }

            foreach ( (array) glob( trailingslashit( $directory ) . '*.log' ) as $file ) {
                if ( ! is_readable( $file ) ) {
                    continue;
                }

                $group = pathinfo( $file, PATHINFO_FILENAME );

                foreach ( $this->parse_wpmvc( $this->tail( $file ) ) as $entry ) {
                    $entry['group'] = $group;
                    $entries[]      = $entry;
                }
            }
        }

        // Newest first, capped.
        $entries = array_reverse( $entries );
        $count   = count( $entries );
        $entries = array_slice( $entries, 0, self::MAX_ENTRIES );

        return array(
            'available' => ! empty( $sources ),
            'sources'   => array_values( array_unique( $sources ) ),
            'entries'   => $entries,
            'count'     => $count,
            'levels'    => $this->count_levels( $entries ),
        );
    }

    /**
     * Parse the WPMVC logger format: `[Y-m-d H:i:s] type: message`.
     *
     * @param string $content
     * @return array
     */
    private function parse_wpmvc( string $content ) : array {
        $entries = array();

        foreach ( preg_split( '/\r\n|\r|\n/', $content ) as $line ) {
            if ( '' === trim( $line ) ) {
                continue;
            }

            if ( preg_match( '/^\[([^\]]+)\]\s+(\w+):\s*(.*)$/s', $line, $m ) ) {
                $entries[] = array(
                    'time'    => $m[1],
                    'level'   => strtolower( $m[2] ),
                    'message' => $m[3],
                );

                continue;
            }

            // Unparseable line — keep it so nothing is silently dropped.
            $entries[] = array( 'time' => '', 'level' => 'log', 'message' => $line );
        }

        return $entries;
    }

    /**
     * Parse the WordPress debug log. Entries start with `[date …]`; lines
     * that don't are continuations (stack traces) of the previous entry.
     *
     * @param string $content
     * @return array
     */
    private function parse_wordpress( string $content ) : array {
        $entries = array();

        foreach ( preg_split( '/\r\n|\r|\n/', $content ) as $line ) {
            if ( preg_match( '/^\[(\d{2}-\w{3}-\d{4}[^\]]*)\]\s*(.*)$/', $line, $m ) ) {
                $message = $m[2];
                $level   = 'log';

                if ( preg_match( '/^PHP (Warning|Notice|Deprecated|Fatal error|Parse error|Recoverable fatal error):/', $message, $lm ) ) {
                    $level   = strtolower( str_replace( array( ' error', ' ' ), array( '', '-' ), $lm[1] ) );
                    $message = trim( substr( $message, strlen( $lm[0] ) ) );
                }

                $entries[] = array(
                    'time'    => $m[1],
                    'level'   => $level,
                    'message' => $message,
                );

                continue;
            }

            if ( '' === trim( $line ) ) {
                continue;
            }

            // Continuation line (e.g. a stack-trace row) — append to the last entry.
            if ( $entries ) {
                $entries[ count( $entries ) - 1 ]['message'] .= "\n" . $line;
            }
        }

        $entries = array_reverse( $entries );

        return array_slice( $entries, 0, self::MAX_ENTRIES );
    }

    /**
     * Count entries per level, for the summary chips.
     *
     * @param array $entries
     * @return array<string, int>
     */
    private function count_levels( array $entries ) : array {
        $levels = array();

        foreach ( $entries as $entry ) {
            $level            = $entry['level'];
            $levels[ $level ] = ( $levels[ $level ] ?? 0 ) + 1;
        }

        return $levels;
    }

    /**
     * Read at most MAX_BYTES from the end of a file, dropping a partial
     * first line so parsing starts on an entry boundary.
     *
     * @param string $path
     * @return string
     */
    private function tail( string $path ) : string {
        $size = filesize( $path );
        $fh   = fopen( $path, 'rb' );

        if ( ! $fh ) {
            return '';
        }

        if ( $size > self::MAX_BYTES ) {
            fseek( $fh, -self::MAX_BYTES, SEEK_END );
            fgets( $fh ); // discard the partial line
        }

        $content = stream_get_contents( $fh );
        fclose( $fh );

        return (string) $content;
    }

}
