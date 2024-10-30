<?php
/*
@package ct4woo
@internal  General configuration tasks
@since 0.1
@todo nothing
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'CLICTILL_DOMAIN', 'ct4woo' );

define( 'CLICTILL_PLUGIN_DIR', dirname( __FILE__ ) . DIRECTORY_SEPARATOR );

define( 'CLICTILL_LANG_DIR', dirname( plugin_basename( __FILE__ ) )  . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR );

define( 'CLICTILL_PATH', 'https://clic-till.com' );

add_filter( 'cron_schedules', 'clictill_cron_recurrence_interval' );
function clictill_cron_recurrence_interval( $schedules ) {
    $schedules['clictill_cron_every_minute'] = array(
  		'interval'  => 60,
  		'display' => __( 'Every minute', CLICTILL_DOMAIN )
    );
    $schedules['clictill_cron_every_hour'] = array(
  		'interval'  => 3600,
  		'display' => __( 'Once hourly', CLICTILL_DOMAIN )
    );
    $schedules['clictill_cron_every_three_hours'] = array(
  		'interval'  => 10800,
  		'display' => __( 'Every three hours', CLICTILL_DOMAIN )
    );
    $schedules['clictill_cron_every_five_hours'] = array(
  		'interval'  => 18000,
  		'display' => __( 'Every five hours', CLICTILL_DOMAIN )
    );
	return $schedules;
}
