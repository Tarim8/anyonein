DESCRIPTION
===========

  Anyonein is designed to report how long ago various sensors were last
activated.

  Intended for use in communal spaces, like Hackspaces, where it's handy for
people to be able to see if any one is in, or when they were last in.

  Anyonein simply logs the time a sensor was last activated through an HTTP
interface.  It doesn't do anything fancy like processing sensor data which
should be done locally by the sensor and its controller.





USAGE
=====

  The Anyonein program can be accessed with various query string parameters,
post parameters or cookie parameters:

    ?format=html
  Output in html format.  Sensors are listed on separate lines with the times
stated as now or as seconds, minutes, hours or days ago.  Values will always be
two or more so units are always plural.  If the sensors are shown then the page
will be set to refresh every two minutes.

    ?format=json
  Output as a json object.  Sensors will be listed as name, value pairs where
value is the time in seconds since the sensor was last activated.  Any errors
will be reported as "error" and "code" (the HTTP return code).  A set sensor
message will be reported as "ok".

    ?format=jsonp&callback=CALLBACK
  Output as jsonp using `CALLBACK` as the function to call.

    ?sensor=SENSOR
  Mark `SENSOR` as just activated.  This must be authorised (come from the right
IP address, right domain or contain the right `ID` password).

    ?sensor=SENSOR&never
  Mark `SENSOR` as though it has never been activated.

    ?id=PASSWORD
  Use `PASSWORD` to authorise this request.

    ?show=display
  Return the sensor data.

    ?show=update
  Return the sensor data and activate the default sensor.


  If no parameters are given then Anyonein will default to `?show=display`
unless the call is authorised (eg from the right IP address) in which case it
will display an HTML yes/no form asking whether you are in the space.
Answering yes will default to `?show=update`, answering no will default to
`?show=display`.


Example Usage
-------------

    http://example.com/anyonein/
  will display the current state of the sensors and refresh the page every two
minutes.

    http://example.com/anyonein/?sensor=light&format=json&show=display
  will update the light sensor and return the sensor status as a json object.






INSTALLATION
============

  Anyonein programs are PHP files which have been tested with PHP version 5.4.

* `.htaccess`
* `anyonein.css`
* `index.php`
* `local-example.php`

  These should be installed in a web accessible directory; the
`local-example.php` file copied to `local.php` and edited accordingly.

  If the files are installed in a directory `/srv/space/htdocs/anyonein` where
`/srv/space/htdocs` serves the domain `space.example.com` then Anyonein is
accessed through `http://space.example.com/anyonein/`.





CONFIGURATION
=============

  `local.php` is a configuration file which should contain some or all of the
following:

Location Definitions
--------------------

    define( 'LOCATION', 'Our Hackspace' );               // name of location
    define( 'FILE_PREFIX', '/var/tmp/anyonein-' );       // last seen files
    define( 'FILE_SUFFIX', '.time' );

  The last seen files should be stored in a temporary directory which is write
accessible to the web server process and should, ideally, remain across
reboots.  For most Linux system policies `/var/tmp` is suitable.


Sensor Definitions
------------------

  The program can handle any number of sensors wich are defined in the
following way.  The first one defined is the sensor which is updated when a
show=update parameter is set.

    new Sensor( SENSOR, DESCRIPTION, MAX_LAST_SEEN );

  `SENSOR` is the name of the sensor which is set by the `?sensor=SENSOR`
parameter.

  `DESCRIPTION` is a text description which  can contain strings of the form
"{past tense|present tense}" which gets displayed according to whether the
sensor is just activated or has been activated in the past.  It also contains
strings of the form "%D" which are replaced by a phrase describing how long ago
the sensor was activated.

  `MAX_LAST_SEEN` is the amount of time before the sensor is ignored as never
having been activated.  (Optional, defaults to 28 days.)


Example Sensor Definitions
--------------------------

    new Sensor( 'computer', 'A computer {was|is} in use {%D ago|now}' );
    new Sensor( 'movement', 'Someone {|is} mov{ed|ing} {%D ago|now}' );
    new Sensor( 'light', 'A light {was|is} on {%D ago|now}' );
    new Sensor( 'test', 'Something {detected %D ago|is on now}' );


Authorisation
-------------

  Any program trying to update a sensor must have at least one valid form of
authorisation.  Authorisations are defined in the following ways.  It is
permissable to have several of the same `AUTH_TYPE` with different `AUTH_VALUEs`.

    new Auth( AUTH_TYPE, AUTH_VALUE );

`AUTH_TYPE` can be one of:

    'ip'
  Which is the internet facing IP address (ie the IP address the web server
sees) of a device attempting an update.

    'id'
  An id which may be passed as a parameter.  Note that, from a security point
of view, this should be passed as POST data or a set cookie over https.

    'domain'
  If the internet facing IP address can be changed dynamically beyond your
control then you can use a dynamic dns service to track this and specify the
dynamic domain name.


Example Authorisations
----------------------

    new Auth( 'ip', '203.0.113.0' );
    new Auth( 'id', 'password' );
    new Auth( 'domain', 'dynamic.example.com' );

