<?php

// This is a template configuration file for local.php

define( 'LOCATION', 'Our Hackspace' );
define( 'FILE_PREFIX', '/var/tmp/anyonein-' );
define( 'FILE_SUFFIX', '.time' );
define( 'MIN_LAST_SEEN', 2 * MINUTES );                 // less than this is treated as now
define( 'MAX_LAST_SEEN', 28 * DAYS );                   // ignore if older than this
define( 'REFRESH_TIMEOUT', MIN_LAST_SEEN );             // refresh browser page
define( 'LOG_FILE', '/home/hackspace/logs/anyonein.log' );

// Sensor names and last seen past/present descriptions
new Sensor( 'computer', 'A computer {was|is} in use {%D ago|now}' );
new Sensor( 'movement', 'Someone {moved %D ago|is moving}', 10 * MINUTES );

// Authorisation to update a sensor
new Auth( 'ip', '203.0.113.0' );
new Auth( 'id', 'password' );
new Auth( 'domain', 'dynamic.example.com' );

?>
