<?php

namespace wpmvc\debug\tabs;

/**
 * Scheduled Jobs tab — lists the WP-Cron schedule (`_get_cron_array()`) with
 * per-event actions to run a job immediately or delete it.
 *
 * Reading the schedule is side-effect free; the run/delete actions are
 * admin-only and nonce-checked (see Debug::ajax_job()).
 */
class Scheduled_Jobs_Tab extends Tab {

    public function get_id() : string {
        return 'scheduled';
    }

    public function get_label() : string {
        return 'Scheduled Jobs';
    }

    public function get_icon() : string {
        return '<path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"/><path d="M11.854 7.646a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L8.5 10.293l2.646-2.647a.5.5 0 0 1 .708 0z"/>';
    }

    public function get_badge() {
        return count( $this->events() );
    }

    public function register_ajax() {
        add_action( 'wp_ajax_wpmvc_debug_job', array( $this, 'ajax_job' ) );
    }

    /**
     * AJAX: run or delete a scheduled cron job.
     *
     * @return void
     */
    public function ajax_job() {
        $this->verify_ajax();

        $op        = isset( $_POST['op'] ) ? sanitize_key( wp_unslash( $_POST['op'] ) ) : '';
        $timestamp = isset( $_POST['timestamp'] ) ? (int) $_POST['timestamp'] : 0;
        $hook      = isset( $_POST['hook'] ) ? sanitize_text_field( wp_unslash( $_POST['hook'] ) ) : '';
        $key       = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';

        if ( 'run' === $op ) {
            wp_send_json_success( array( 'ok' => $this->run( $timestamp, $hook, $key ) ) );
        }

        if ( 'delete' === $op ) {
            wp_send_json_success( array( 'ok' => $this->delete( $timestamp, $hook, $key ) ) );
        }

        wp_send_json_error( array( 'message' => 'Unknown op' ), 400 );
    }

    public function get_data() : array {
        $events = $this->events();
        $now    = time();

        $hooks      = array();
        $schedules  = array();
        $next_run   = null;

        foreach ( $events as $event ) {
            $hooks[ $event['hook'] ] = true;

            if ( $event['schedule'] ) {
                $schedules[ $event['schedule'] ] = true;
            }

            if ( ! $event['due'] && ( null === $next_run || $event['timestamp'] < $next_run ) ) {
                $next_run = $event['timestamp'];
            }
        }

        $due = array_filter( $events, static function ( $event ) {
            return $event['due'];
        } );

        return array(
            'events'  => $events,
            'cron_disabled' => defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON,
            'stats'   => array(
                array( 'label' => 'Total Jobs', 'meta' => 'All scheduled tasks', 'value' => count( $events ) ),
                array( 'label' => 'Due Now', 'meta' => 'Ready to run', 'value' => count( $due ) ),
                array( 'label' => 'Next Run', 'meta' => 'Soonest upcoming', 'value' => null !== $next_run ? human_time_diff( $now, $next_run ) : '—' ),
                array( 'label' => 'Hooks', 'meta' => 'Unique hook names', 'value' => count( $hooks ) ),
                array( 'label' => 'Recurrence', 'meta' => 'Distinct intervals', 'value' => count( $schedules ) ),
            ),
        );
    }

    /**
     * Flatten `_get_cron_array()` into a sorted list of events.
     *
     * @return array
     */
    private function events() : array {
        if ( ! function_exists( '_get_cron_array' ) ) {
            return array();
        }

        $crons     = _get_cron_array();
        $schedules = wp_get_schedules();
        $now       = time();
        $events    = array();

        foreach ( (array) $crons as $timestamp => $hooks ) {
            foreach ( $hooks as $hook => $keyed ) {
                foreach ( $keyed as $key => $event ) {
                    $schedule = isset( $event['schedule'] ) ? $event['schedule'] : false;

                    $events[] = array(
                        'timestamp'  => (int) $timestamp,
                        'hook'       => $hook,
                        'key'        => $key,
                        'args'       => isset( $event['args'] ) ? $event['args'] : array(),
                        'schedule'   => $schedule,
                        'recurrence' => $schedule ? ( $schedules[ $schedule ]['display'] ?? $schedule ) : 'One-off',
                        'due'        => $timestamp <= $now,
                    );
                }
            }
        }

        usort( $events, static function ( $a, $b ) {
            return $a['timestamp'] <=> $b['timestamp'];
        } );

        return $events;
    }

    /**
     * Run a job now (executes its hook callbacks). Consumes a one-off event.
     *
     * @param int    $timestamp
     * @param string $hook
     * @param string $key
     * @return bool
     */
    public function run( int $timestamp, string $hook, string $key ) : bool {
        $event = $this->find( $timestamp, $hook, $key );

        if ( null === $event ) {
            return false;
        }

        do_action_ref_array( $hook, $event['args'] );

        // A one-off event is consumed by running it.
        if ( false === $event['schedule'] ) {
            wp_unschedule_event( $timestamp, $hook, $event['args'] );
        }

        return true;
    }

    /**
     * Delete a scheduled event.
     *
     * @param int    $timestamp
     * @param string $hook
     * @param string $key
     * @return bool
     */
    public function delete( int $timestamp, string $hook, string $key ) : bool {
        $event = $this->find( $timestamp, $hook, $key );

        if ( null === $event ) {
            return false;
        }

        return false !== wp_unschedule_event( $timestamp, $hook, $event['args'] );
    }

    /**
     * Look up an event's raw data by its identity, straight from the cron
     * array — args are never taken from the client.
     *
     * @param int    $timestamp
     * @param string $hook
     * @param string $key
     * @return array|null
     */
    private function find( int $timestamp, string $hook, string $key ) {
        $crons = _get_cron_array();

        if ( isset( $crons[ $timestamp ][ $hook ][ $key ] ) ) {
            $event = $crons[ $timestamp ][ $hook ][ $key ];

            return array(
                'args'     => isset( $event['args'] ) ? $event['args'] : array(),
                'schedule' => isset( $event['schedule'] ) ? $event['schedule'] : false,
            );
        }

        return null;
    }

}
