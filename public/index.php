<?php

// ini_set( 'display_errors', 0 );
ini_set( 'log_errors', 1 );
ini_set( 'error_log', __DIR__.'/../debug.log' );

/*
|--------------------------------------------------------------------------
| Register The Composer Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader
| for our application. We just need to utilize it! We'll require it
| into the script here so that we do not have to worry about the
| loading of any our classes "manually". Feels great to relax.
|
*/

require __DIR__.'/../vendor/autoload.php';
require_once __DIR__ . '/../includes/functions.php';

init();

function init() {
	$requestType = empty( $_SERVER['REQUEST_METHOD'] ) ? 'GET' : $_SERVER['REQUEST_METHOD'];
	$args = [
		'format' => ! empty( $_GET['format'] ) && 'var_dump' === $_GET['format'] ? 'var_dump' : 'print_r',
	];
	$data = [];
	switch ( $requestType ) {
		case 'GET':
			$args['wp_die_handler'] = ! empty( $_GET['return'] ) ? 'wp_send_json' : 'stringify_output';
			$data[ $requestType ] = $_GET;
			break;

		default:
			$args['wp_die_handler'] = 'wp_send_json';
			$data[ $requestType ] = json_decode( file_get_contents('php://input'), true );
			$data['GET'] = $_GET;
			break;
	}

	if ( ! empty( $_GET['log'] ) ) {
		$requestsLog = __DIR__ . '/../requests.log';
		$diff = compare_filesize( $requestsLog, '1M', 1000 );
		if ( $diff > 1000 ) {
			purge_file_contents( $requestsLog, '1M' );
		} else {
		}

		$put = file_put_contents( $requestsLog, "\n[" . date('Y-m-d H:i:s') . '] '. print_r( $data, true ), FILE_APPEND );

		$data['logged_result'] = $put;
	}

	wp_die( $data, $requestType, $args );
}
