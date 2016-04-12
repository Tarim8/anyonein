<?php

// Anyonein - Let people know if someone is in a location
//
// Copyright Tarim 2016
//
// Anyonein is free software: you can redistribute it and/or modify
// it under the terms of the GNU Lesser General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Anyonein is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with Anyonein.  If not, see <http://www.gnu.org/licenses/>.

// set the following to allow GET to override cookies
// php_value request_order "CPG"

define( 'MINUTES', 60 );
define( 'HOURS', MINUTES * 60 );
define( 'DAYS', HOURS * 24 );

define( 'REFRESH_TIMEOUT', 2 * MINUTES );               // refresh page every 2 minutes
define( 'COOKIE_TIMEOUT', 2 * REFRESH_TIMEOUT );        // cookie lasts twice as long
define( 'MAX_LAST_SEEN', 29 * DAYS );                   // ignore if older than this

define( 'MINUTES_NAME', ' minutes' );
define( 'HOURS_NAME', ' hours' );
define( 'DAYS_NAME', ' days' );

// Form button labels
define( 'YES', 'yes' );
define( 'NO', 'no' );

// Web page messages
define( 'ANYONE_IN_MSG', 'Anyone in? - ' );
define( 'LOCATION_QUERY_MSG', 'Are you in ' );

define( 'UPDATED_MSG', 'Updated sensor ' );
define( 'REMOVED_MSG', 'Removed sensor ' );

define( 'NO_SENSOR_MSG', 'No sensor ' );
define( 'NOT_IN_MSG', 'Not in ' );
define( 'UNKNOWN_AUTH_MSG', 'Unknown authorisation ' );
define( 'INVALID_FORMAT_MSG', 'Invalid format ' );
define( 'INVALID_SHOW_MSG', 'Invalid show value ' );

// Query string constants
define( 'FORMAT', 'format' );
define( 'SENSOR', 'sensor' );
define( 'NEVER', 'never' );

define( 'SHOW', 'show' );
define( 'UPDATE', 'update' );
define( 'DISPLAY', 'display' );
define( 'NONE', 'none' );

define( 'IP', 'ip' );
define( 'ID', 'id' );
define( 'DOMAIN', 'domain' );

// HTTP error codes
define( 'HTTP_OK', 200 );
define( 'HTTP_BAD_REQUEST', 400 );
define( 'HTTP_FORBIDDEN', 403 );
define( 'HTTP_NOT_FOUND', 404 );
define( 'HTTP_INTERNAL_SERVER', 500 );

define( 'OK_MESSAGE', 'ok' );
define( 'ERROR_MESSAGE', 'error' );
define( 'ERROR_CODE', 'code' );




//
// Local contains something like the following
//
include( 'local.php' );

// define( 'LOCATION', 'Our Hackspace' );               // name of location
// define( 'FILE_PREFIX', '/var/tmp/anyonein-' );       // last seen files
// define( 'FILE_SUFFIX', '.time' );
//
// // Sensor names and last seen past/present descriptions
// new Sensor( 'computer', 'A computer {was|is} in use {%D ago|now}' );
// new Sensor( 'movement', 'Someone {|is} mov{ed|ing} {%D ago|now}' );
// new Sensor( 'light', 'A light {was|is} on {%D ago|now}' );
// new Sensor( 'test', 'Something {detected %D ago|is on now}' );
//
// // Authorisation to update a sensor
// new Auth( 'ip', '203.0.113.0' );
// new Auth( 'id', 'password' );
// new Auth( 'domain', 'dynamic.example.com' );





//
// Page class - static class to process the output text
//
class Page {
    public static $format = 'html';     // html, json
    public static $bodyPrefix = '';     // prefix to $body
    public static $body = '';           // build up HTML/JSON body here
    public static $bodySuffix = '';     // suffix to $body
    public static $separator = '';      // for subsequent items in a JSON list


    //
    // Page - set output format to html or json
    //
    function Page( $format ) {
        if( Page::$body ) {           // if already had internal error (unlikely)
            $format = 'html';
        }

        switch( $format ) {
        case 'json':
            Page::$format = 'json';
            header( 'Content-Type: application/json' );

            Page::$bodyPrefix = "{\n";
            Page::$bodySuffix = "\n}\n";

            break;

        case 'jsonp':
            if( array_key_exists( 'callback', $_REQUEST ) ) {
                Page::$format = 'json';
                header( 'Content-Type: text/javascript' );

                Page::$bodyPrefix = "$_REQUEST[callback]( {\n";
                Page::$bodySuffix = "\n} );\n";

                break;
            }

        default:
            Page::addErr( INVALID_FORMAT_MSG . $format, HTTP_BAD_REQUEST );

        case 'html':
        case null:
            Page::$bodyPrefix = '
                <!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
                <html>
                  <head>
                    <title>' . ANYONE_IN_MSG . LOCATION . '</title>
                    <link rel="stylesheet" type="text/css" href="anyonein.css" />
                  </head>

                  <body>
                    <div class="body">
                ';

            Page::$bodySuffix = '
                    </div>
                  </body>
                </html>
                ';

            break;
        }
    }


    //
    // addMsg - to page body
    //
    static function addMsg( $line ) {
        Page::addHTML( $line );

        Page::addJSON( OK_MESSAGE, "\"$line\"" );
    }


    //
    // addErr - to page body
    //
    static function addErr( $line, $code ) {
        header( "HTTP/1.1 $code $line" );

        Page::addHTML( $line );

        Page::addJSON( ERROR_CODE, $code );
        Page::addJSON( ERROR_MESSAGE, "\"$line\"" );
    }


    //
    // addHTML - to page body
    //
    static function addHTML( $line ) {
        if( Page::$format === 'html' ) {
            Page::$body .= "<p class=\"message\">$line</p>\n";
        }
    }


    //
    // addJSON - to page body
    //
    static function addJSON( $item, $value ) {
        if( Page::$format === 'json' ) {
            Page::$body .= Page::$separator . "\"$item\": $value";
            Page::$separator = ",\n";
        }
    }


    //
    // addForm - displays the yes/no forms if we're in the location
    // so we can update the default sensor if we're in
    //
    static function addForm() {
        Page::$body .= '
            <p class="formtitle" >
              ' . LOCATION_QUERY_MSG . LOCATION . '?
            </p>
            <p class="form" >
        ';

        Page::addButton( YES, UPDATE );
        Page::addButton( NO, DISPLAY );

        Page::$body .= '
            </p>
        ';
    }


    //
    // addButton - displays a form button
    // Usually submit=yes, show=update or submit=no, show=display
    //
    static function addButton( $submit, $show ) {
        Page::$body .= '
            <form method="post" url="' . $_SERVER['SCRIPT_NAME'] . '" >
              <input type="hidden" name="' . SHOW . '" value="' . $show . '" />
              <input type="submit" value="' . $submit . '"/>
            </form>
        ';
    }


    //
    // output - the data
    //
    static function output() {
        echo Page::$bodyPrefix, Page::$body, Page::$bodySuffix;
    }
}





//
// Authorisation class - contains validations that allow sensor time updates
// $type can be:
//   'ip': request is from a specific ip address
//   'id': request has 'id=password' query string, post or cookie
//   'domain': remote ip address is this domain's ip address (for use with dynamic dns)
//
class Auth {
    public $type;                       // ip, id, domain
    public $value;

    public static $auths = array();     // authorisation instances

    //
    // Construct an authorisation
    //
    function Auth( $type, $value ) {
        $this->type = $type;
        $this->value = $value;

        Auth::$auths[] = $this;
    }


    //
    // isHere - returns true if we are in the location
    //
    static function isHere() {
        foreach( Auth::$auths as $auth ) {
            if( $auth->authorised() ) {
                return true;
            }
        }
        return false;
    }


    //
    // authorised - returns true if the client is authorised to update a sensor
    //
    function authorised() {
        switch( $this->type ) {
        case IP:
            return inet_ntop( inet_pton( $_SERVER['REMOTE_ADDR'] ) ) === $this->value;

        case ID:
            return  array_key_exists( ID, $_REQUEST ) &&
                    $_REQUEST[ID] === $this->value;

        case DOMAIN:
            return $_SERVER['REMOTE_ADDR'] === gethostbyname( $this->value );
        }

        Page::addErr( UNKNOWN_AUTH_MSG . $this->type, HTTP_INTERNAL_SERVER );
        return false;
    }
}





//
// Sensor class - individual sensors
//
class Sensor {
    public $name;               // sensor name
    public $description;        // description text for reporting last seen time
    public $timeFile;           // the file to keep track of last seen time
    public $maxLastSeen;        // if older than this last seen time - ignore it

    public static $sensors;     // array of all sensors


    //
    // Construct a new sensor instance.
    // Add it to the static sensors list.
    // The first constructed sensor becomes the default sensor for ?show=update.
    //
    function Sensor( $name, $description, $maxLastSeen = MAX_LAST_SEEN ) {
        $this->name = $name;
        $this->description = $description;
        $this->maxLastSeen = $maxLastSeen;
        $this->timeFile = FILE_PREFIX . $name . FILE_SUFFIX;

        if( !isset( Sensor::$sensors ) ) {
            define( 'DEFAULT_SENSOR', $name );
        }

        Sensor::$sensors[$name] = $this;
    }


    //
    // setSensor - activate (or remove) a sensor.
    //
    static function setSensor( $sensorName ) {
        if( array_key_exists( $sensorName, Sensor::$sensors ) ) {
            Sensor::$sensors[$sensorName]->recordTime( array_key_exists( NEVER, $_REQUEST ) );

        } elseif( $sensorName !== null ) {
            Page::addErr( NO_SENSOR_MSG . $sensorName, HTTP_NOT_FOUND );
        }
    }


    //
    // Update sensor file's last seen time (or delete it).
    //
    function recordTime( $delete = false ) {
        if( Auth::isHere() ) {
            if( $delete ) {
                @unlink( $this->timeFile );
                Page::addMsg( REMOVED_MSG . $this->name );

            } else {
                touch( $this->timeFile );
                Page::addMsg( UPDATED_MSG . $this->name );
            }

        } else {
            Page::addErr( NOT_IN_MSG . LOCATION, HTTP_FORBIDDEN );
        }
    }


    //
    // showAll - the sensors in the $sensors array.
    // Update the default sensor if $update === UPDATE.
    // Send form if show=null, format='html', sensor=null and isHere().
    // Set cookie and refresh header when we're called in a browser.
    //
    static function showAll( $show ) {
        $browser = Page::$format === 'html' && !array_key_exists( SENSOR, $_REQUEST ); 

        switch( $show ) {
        case UPDATE:
            Sensor::$sensors[DEFAULT_SENSOR]->recordTime();
            break;

        case DISPLAY:
            break;

        case null:
             if( $browser && Auth::isHere() ) {
                Page::addForm();
                return;
            }

            $show = DISPLAY;
            break;

        case NONE:
            return;

        default:
            Page::addErr( INVALID_SHOW_MSG . $show, HTTP_BAD_REQUEST );
            return;
        }

        foreach( Sensor::$sensors as $sensor ) {
            $sensor->show();
        }

        if( $browser ) {
            setcookie( SHOW, $show, time() + COOKIE_TIMEOUT );
            header( 'Refresh: ' . REFRESH_TIMEOUT );
        }
    }


    //
    // show - a sensor's last seen time.
    // Only show it in HTML if it's less than $maxLastSeen ago.
    // Split up according to nearest 2*units (so we can always use plurals).
    //
    function show() {
        if( $result = @stat( $this->timeFile ) ) {
            $lastSeen = time() - $result['mtime'];

            if( Page::$format === 'html' ) {
                if( $lastSeen < $this->maxLastSeen ) {
                    if( $lastSeen <= 2 * REFRESH_TIMEOUT ) {
                        $this->describe();

                    } elseif( $lastSeen < 2 * HOURS ) {
                        $this->describe( $lastSeen / MINUTES, MINUTES_NAME );

                    } elseif( $lastSeen < 2 * DAYS ) {
                        $this->describe( $lastSeen / HOURS, HOURS_NAME );

                    } else {
                        $this->describe( $lastSeen / DAYS, DAYS_NAME );
                    }
                }

            } else {
                Page::addJSON( $this->name, $lastSeen );
            }
        }
    }


    //
    // describe - the last seen time in past or present tense.
    // Description strings can contain elements like {past|present}
    // which will pick out the word "past" or "present" accordingly.
    // The element %D will be replaced with the last seen time.
    //
    function describe( $lastSeen = 0, $units = '' ) {
        $lastSeen = floor( $lastSeen );

        $desc = preg_replace(
                    '/{([^\|]*)\|([^}]*)}/',
                    $lastSeen ? '$1' : '$2',
                    $this->description );

        $desc = preg_replace( '/%D/', $lastSeen . $units, $desc );

        Page::addHTML( $desc );
    }
}



//
// Main program
//
    // Set output format
    new Page( @$_REQUEST[FORMAT] );

    // Update sensor
    Sensor::setSensor( @$_REQUEST[SENSOR] );

    // Show data
    Sensor::showAll( @$_REQUEST[SHOW] );

    // Display the results
    Page::output();
?>
