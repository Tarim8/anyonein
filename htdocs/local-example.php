<?php

// This is a template configuration file for local.php

define( 'LOCATION', 'Our Hackspace' );
define( 'FILE_PREFIX', '/var/tmp/anyonein-' );
define( 'FILE_SUFFIX', '.time' );

// Sensor names and last seen past/present descriptions
new Sensor( 'computer', 'A computer {was|is} in use {%D ago|now}' );
new Sensor( 'movement', 'Someone {|is} mov{ed|ing} {%D ago|now}' );
new Sensor( 'light', 'A light {was|is} on {%D ago|now}' );
new Sensor( 'test', 'Something {detected %D ago|is on now}' );

// Authorisation to update a sensor
new Auth( 'ip', '203.0.113.0' );
new Auth( 'id', 'password' );
new Auth( 'domain', 'dynamic.example.com' );

?>
