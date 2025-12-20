<?php
/**
 * Cron/Scheduled Sync functionality.
 *
 * @package Import_Luma_Events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Import_Luma_Events_Cron Class.
 */
class Import_Luma_Events_Cron {

	/**
	 * The cron hook name.
	 *
	 * @var string
	 */
	const CRON_HOOK = 'ile_sync_events';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( self::CRON_HOOK, array( $this, 'run_sync' ) );
		add_action( 'update_option_ile_luma_options', array( $this, 'reschedule_on_settings_change' ), 10, 2 );
	}

	/**
	 * Schedule the sync cron job.
	 */
	public function schedule_sync() {
		$options = get_option( 'ile_luma_options' );

		// Check if auto sync is enabled.
		if ( empty( $options['enable_auto_sync'] ) ) {
			$this->clear_scheduled_sync();
			return;
		}

		// Get frequency.
		$frequency = isset( $options['sync_frequency'] ) ? $options['sync_frequency'] : 'hourly';

		// Check if there's already a scheduled event.
		$next_scheduled = wp_next_scheduled( self::CRON_HOOK );

		// If already scheduled, check if the frequency matches.
		if ( $next_scheduled ) {
			// Get the current schedule data.
			$cron_array = _get_cron_array();
			if ( isset( $cron_array[ $next_scheduled ][ self::CRON_HOOK ] ) ) {
				$current_schedule = reset( $cron_array[ $next_scheduled ][ self::CRON_HOOK ] );

				// If the frequency matches, don't reschedule - leave it alone.
				if ( isset( $current_schedule['schedule'] ) && $current_schedule['schedule'] === $frequency ) {
					return;
				}
			}
		}

		// Clear existing schedule (only if we need to reschedule).
		$this->clear_scheduled_sync();

		// Get all schedules to calculate proper next run time.
		$schedules = wp_get_schedules();

		// Calculate next run time based on frequency.
		// Use current timestamp (UTC) plus a small offset to ensure it runs soon.
		$current_timestamp = time();

		// For the first schedule, run it very soon (in 1 minute).
		// WordPress cron will then handle the recurring interval.
		$first_run = $current_timestamp + 60;

		// Schedule the event.
		$scheduled = wp_schedule_event( $first_run, $frequency, self::CRON_HOOK );

		// Debug log if scheduling failed.
		if ( $scheduled === false ) {
			error_log( 'ILE: Failed to schedule cron event. Frequency: ' . $frequency . ', Timestamp: ' . $first_run );
		}
	}

	/**
	 * Clear scheduled sync.
	 */
	public function clear_scheduled_sync() {
		$timestamp = wp_next_scheduled( self::CRON_HOOK );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, self::CRON_HOOK );
		}
	}

	/**
	 * Reschedule when settings change.
	 *
	 * @param array $old_value Old settings value.
	 * @param array $new_value New settings value.
	 */
	public function reschedule_on_settings_change( $old_value, $new_value ) {
		$this->schedule_sync();
	}

	/**
	 * Run the sync process.
	 */
	public function run_sync() {
		$options = get_option( 'ile_luma_options' );

		// Check if auto sync is enabled.
		if ( empty( $options['enable_auto_sync'] ) ) {
			return;
		}

		$calendar_id = isset( $options['luma_calendar_id'] ) ? $options['luma_calendar_id'] : '';

		if ( empty( $calendar_id ) ) {
			return;
		}

		$import_manager = new Import_Luma_Events_Import_Manager();
		$import_options = array(
			'post_status'     => 'publish',
			'update_existing' => true,
		);

		$result = $import_manager->import_calendar_events( $calendar_id, $import_options );

		// Log the result.
		if ( $result['success'] ) {
			update_option( 'ile_last_sync', array(
				'time'    => current_time( 'mysql' ),
				'status'  => 'success',
				'created' => $result['created'],
				'updated' => $result['updated'],
				'skipped' => $result['skipped'],
			) );
		} else {
			update_option( 'ile_last_sync', array(
				'time'   => current_time( 'mysql' ),
				'status' => 'error',
				'error'  => isset( $result['error'] ) ? $result['error'] : 'Unknown error',
			) );
		}
	}
}
