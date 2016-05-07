ANYONEIN
========

  Anyonein is designed to report how long ago different sensors were last
activated.



DESCRIPTION
-----------

  Intended for use in communal spaces, like Hackspaces, where it's handy for
people to be able to see if any one is in, or when they were last in.

  Anyonein simply logs the time a sensor was last activated through an HTTP
interface.  It doesn't do anything fancy like processing sensor data which
should be done locally by the sensor and its controller.



USAGE
-----

  The Anyonein program can be accessed with query string parameters, post
parameters or cookie parameters:

    ?format=html
  Output in html format.  Sensors are listed on separate lines with the times
stated as now or as seconds, minutes, hours or days ago.  Values will always be
two or more so units are always plural.  If the sensors are shown in a browser
then the page will be set to refresh.  The refresh period is set in the local
configuration file.

    ?format=json
  Output as a json object.  Sensors will be listed as part of the `sensors`
object and contain tags: `lastseen`, `minlastseen` and `maxlastseen`.  Messages
and errors will be reported as part of the `status` object which may be empty
or contain tags: `message`, `code` (the HTTP return code) and `error` (an
error message).

    ?format=jsonp&callback=CALLBACK
  Output as jsonp using `CALLBACK` as the function to call.

    ?sensor=SENSOR
  Mark `SENSOR` as just activated.  This must be authorised (come from the right
IP address, right domain or contain the right `id` password).

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


### Example Usage ###

    http://example.com/anyonein/
  will display the current state of the sensors and refresh the page every so
often.

    http://example.com/anyonein/?sensor=movement&format=json
  will update the movement sensor and return the sensor status as a json
object.  For example:

    {
        "status": {
            "message": "Updated sensor movement"
        },
        "sensors": {
            "computer": {
                "lastseen": 16332,
                "minlastseen": 120,
                "maxlastseen": 2419200
            },
            "movement": {
                "lastseen": 16098,
                "minlastseen": 600,
                "maxlastseen": 2419200
            },
        }
    }


It is important to include the trailing slash on the URL with some tools.



INSTALLATION
------------

### Server ###

  The Anyonein program is a PHP file, along with Apache style `.htaccess` and
css files,  available in the `php` directory.  These have been tested with PHP
versions 5.3 and 5.4.

* `.htaccess`
* `anyonein.css`
* `index.php`
* `local-example.php`

  These should be installed in a web accessible directory; the
`local-example.php` file copied to `local.php` and edited accordingly.



CONFIGURATION
-------------

  `local.php` is a configuration file which should contain some or all of the
following:

### Location Definitions ###

    define( 'LOCATION', 'Our Hackspace' );               // name of location
    define( 'FILE_PREFIX', '/var/tmp/anyonein-' );       // last seen files
    define( 'FILE_SUFFIX', '.time' );

  The last seen files should be stored in a temporary directory which is write
accessible to the web server process and should, ideally, remain across
reboots.  For most Linux system policies `/var/tmp` is suitable.


### Timing Definitions ###

    define( 'MIN_LAST_SEEN', 2 * MINUTES );              // less than this is treated as now
    define( 'MAX_LAST_SEEN', 28 * DAYS );                // ignore if older than this
    define( 'REFRESH_TIMEOUT', MIN_LAST_SEEN );          // refresh browser page

  Any time less than MIN_LAST_SEEN will be reported as "now" by the show
command.  Any time more than MAX_LAST_SEEN will be ignored for reporting
purposes.  REFRESH_TIMEOUT says how often the browser page will be refreshed.


### Sensor Definitions ###

  The program can handle any number of sensors wich are defined in the
following way.  The first one defined is the default sensor which is updated
when a show=update parameter is set.

    new Sensor( SENSOR, DESCRIPTION, MIN_LAST_SEEN, MAX_LAST_SEEN );

  `SENSOR` is the name of the sensor which is set by the `?sensor=SENSOR`
parameter.

  `DESCRIPTION` is a text description which  can contain strings of the form
"{past tense|present tense}" which is displayed according to whether the sensor
has been recently activated or longer than `MIN_LAST_SEEN` time ago.  It also
contains strings of the form "%D" which are replaced by a phrase describing how
long ago the sensor was activated.

  `MAX_LAST_SEEN`, `MAX_LAST_SEEN` can be omitted and default to the above
times.


### Example Sensor Definitions ###

    new Sensor( 'computer', 'A computer {was|is} in use {%D ago|now}' );
    new Sensor( 'movement', 'Someone {|is} mov{ed|ing} {%D ago|now}', 10 * MINUTES );


### Authorisation ###

  Any program trying to update a sensor must have at least one valid form of
authorisation.  Authorisations are defined in the following ways.  It is
permissable to have several of the same `AUTH_TYPE` with different `AUTH_VALUE`.

    new Auth( AUTH_TYPE, AUTH_VALUE );

`AUTH_TYPE` can be one of:

    'ip'
  Which is the internet facing IP address (ie the IP address the web server
sees) of a device attempting an update.

    'id'
  An id which may be passed as a parameter.  Note that, from a security point
of view, this should be passed as POST data or a cookie over https.

    'domain'
  If the internet facing IP address can change then you can use a dynamic dns
service to track this and specify the dynamic domain name.


### Example Authorisations ###

    new Auth( 'ip', '203.0.113.0' );
    new Auth( 'id', 'password' );
    new Auth( 'domain', 'dynamic.example.com' );



SOMEONEIN
---------

  These scripts can be run on a server or computer to activate the sensors
repeatedly while the computer is switched on.  They are found in the
`someonein` directory.


### Cron ###

    someonein/cron/someonein.crontab
  A crontab entry to update a sensor every 5 minutes.


### Windows ###

    someonein/windows/schedule.xml
  A scheduled task configuration for Windows 7 Task Manager.  Needs `wget.exe`
to be installed.

    someonein/windows/someonein.ps1
  A Power Shell script which doesn't need `wget.exe`.  However this does need
to be signed or something Windows mad like that.  If you work out how, feel
free to update these instructions.
