<?php

// Let members know if someone is in the Hackspace
//
// Copyright Tarim July 2011
// To be released under an open source license when we've decided what
//
// If you are on BV Studios wireless then set a cookie called LOCATION
// If you're in the Hackspace (as opposed to somewhere else in BV) then
// set LOCATION to TAG (through a form) and touch the TAGFILE
//
// set the following to allow GET to override cookies
// php_value request_order "CPG"


define( 'BVSTUDIOSIP', '203.0.113.0' );	        // Dummy IP address
define( 'REFRESHTIMEOUT', 2 * 60 );		// refresh page every 2 minutes
define( 'COOKIETIMEOUT', 2 * REFRESHTIMEOUT );	// cookie last twice as long

define( 'LOCATION', 'G11' );			// cookie name
define( 'TAG', 'iwozere' );			// cookie value
define( 'TAGFILE', '/var/tmp/bristolhackspace.tag' );  // server tagfile

define( 'MINUTES', 60 );
define( 'HOURS', MINUTES * 60 );
define( 'DAYS', HOURS * 24 );

// HTML header
define( 'HEADER', '
<html>
  <head>
    <title>
      Anyone in? - Bristol Hackspace
    </title>
    <style type="text/css">
      form {
	display: inline;
      }
      .body {
	text-align: center;
      }
      input {
	width: 6em;
      }
    </style>
  </head>
  <body>
    <div class="body">
');

// HTML form
define( 'FORM', '
      <div class="title" >
	You appear to be in BV Studios.  Are you in '. LOCATION . '?
      </div>
      <div class="form" >
	<form method="post" url="' . $_SERVER['SCRIPT_NAME'] . '" >
	  <input type="hidden" name="' . LOCATION . '" value="' . TAG . '" />
	  <input type="submit" value="yes" />
	</form>
	<form method="post" url="' . $_SERVER['SCRIPT_NAME'] . '" >
	  <input type="hidden" name="' . LOCATION . '" value="0" />
	  <input type="submit" value="no" />
	</form>
      </div>
' );

// HTML footer
define( 'FOOTER', '
    </div>
  </body>
</html>
');


// Are we in BV Studios?
if( strcmp( $_SERVER['REMOTE_ADDR'], BVSTUDIOSIP ) == 0 ) {

    // have we got a cookie or submitted a form?
    if( is_array( $_REQUEST ) && array_key_exists( LOCATION, $_REQUEST ) ) {
	// set a cookie
	setcookie( LOCATION, $_REQUEST[LOCATION], time() + COOKIETIMEOUT );

	// only touch TAGFILE if LOCATION is TAG (we're in G11 not just BV)
	if( strcmp( $_REQUEST[LOCATION], TAG ) == 0 ) {
	    touch( TAGFILE );
	    $message = "	You are in the Hackspace.";
	}

    } else {	// no cookie or submitted form

	// send a form to be filled in by the user
	$message = FORM;
    }
}


if( is_null( $message ) ) {
    // Find the time the TAGFILE was last updated
    if( $result = @stat( TAGFILE ) ) {
	$last = time() - $result['mtime'];

	if( $last <= 2 * REFRESHTIMEOUT ) {
	    $message = "	Someone is in.";
	} else {
	    if( $last < 2 * HOURS ) {
		$last = floor( $last / MINUTES );
		$units = 'minutes';
	    } elseif( 	$last < 2 * DAYS ) {
		$last = floor( $last / HOURS );
		$units = 'hours';
	    } else {
		$last = floor( $last / DAYS );
		$units = 'days';
	    }
	    $message = "	Someone was in $last $units ago.";
	}
    } else {
	$message = "	I can't tell when anyone was last in.";
    }
}


// Refresh the page every so often
header( 'Refresh: ' . REFRESHTIMEOUT );

// Tell them all the details
echo HEADER, $message, FOOTER;

?>
