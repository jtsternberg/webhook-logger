<?php

function stringify_output( $data, $title, $args ) {
	if ( is_string( $data ) ) {
		$message = $data;
	} else {
		$message = '';
		foreach ( $data as $key => $value ) {
			if ( ! empty( $args['format'] ) && 'var_dump' === $args['format'] ) {
				ob_start();
				var_dump( $value );
				// grab the data from the output buffer and add it to our $content variable
				$value = '<strong><code>' . $key . ':</code></strong>' . ob_get_clean();
			} else {
				$value = '<xmp>' . $key . ': ' . print_r( $value, true ) . '</xmp>';
			}
			$message .= $value;
		}
	}

	WpDie::output( $message, $title, $args );
}

function convert_to_bytes( $toBytes ) {
	if ( is_numeric( $toBytes ) ) {
		// Not much we can do here.
		return intval( $toBytes );
	}

	$rev   = strrev( $toBytes );
	$bytes = floatval( $toBytes );

	$sizes = [
		'B',
		'K',
		'M',
		'G',
		'T',
		'P',
	];

	$factor = 0;
	foreach ( $sizes as $size ) {
		if ( 0 === strpos( $rev, $size ) ) {
			$factor = array_search( $size, $sizes );
		}
	}

	if ( $factor > 0 ) {
		$bytes = $bytes * pow( 1024, $factor );
	}

	return $bytes;
}

function compare_filesize( $bytes, $compare = '' ) {
	$pre = $compare;
	if ( ! is_numeric( $bytes ) ) {
		if ( ! file_exists( $bytes ) ) {
			return null;
		}

		$bytes = filesize( $bytes );
	}

	$compare = convert_to_bytes( $compare );

	if ( $compare > $bytes ) {
		return -1;
	}

	if ( $compare < $bytes ) {
		return $bytes - $compare;
	}

	return 0;
}

function get_file_line_count( $fileName ) {
	$linecount = 0;
	$handle    = fopen( $fileName, 'r' );
	while ( ! feof( $handle ) ) {
		$line = fgets( $handle );
		$linecount++;
	}

	fclose( $handle );

	return $linecount;
}

function purge_file_contents( $fileName, $allowed = 0 ) {
	$fileSize     = filesize( $fileName );
	$allowedBytes = convert_to_bytes( $allowed );
	$lineCount    = get_file_line_count( $fileName );
	$percent      = ( $allowedBytes / $fileSize );
	$allowedLines = floor( $percent * $lineCount );
	$toRemove     = $lineCount - $allowedLines;

	if ( ( $fileSize - $allowedBytes ) < 1000 ) {
		// We're close enough.
		return;
	}

	$newFile = new SplFileObject( $fileName . '-temp', 'w' );

	foreach ( new LimitIterator( new SplFileObject( $fileName ), $toRemove ) as $line ) {
		$newFile->fwrite( $line );
	}

	unlink( $fileName );
	rename( $fileName . '-temp', $fileName );
}

/**
 * Send a JSON response back to an Ajax request.
 *
 * @param mixed $response    Variable (usually an array or object) to encode as JSON,
 *                           then print and die.
 * @param int   $status_code The HTTP status code to output.
 */
function wp_send_json( $response, $status_code = null ) {
	@header( 'Content-Type: application/json; charset=UTF-8' );
	if ( null !== $status_code ) {
		WpDie::statusHeader( $status_code );
	}

	echo json_encode( $response );
	die;
}
