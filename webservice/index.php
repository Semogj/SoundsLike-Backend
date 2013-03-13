<?php

namespace VIRUS\webservice;

/**
 * TODO list:
 *  - Namespaced logging implementation (a simplification of java logging framework).
 *  - Logger handlers
 *  - Limitation of logger entries
 *  - File loggers organization
 *  - Services alias
 */
$config = array(
    'dbDriver' => 'mysql',
    'dbHost' => 'localhost',
    'dbUser' => 'root',
    'dbPassword' => 'root123!',
    'dbName' => 'virus_as',
    'logFile' => 'logs/logFile.txt',
    'debug' => true, //for error_reporting();  WARNING: use false in a production environment!
    'loglevel' => 0, //for log file: ALL = 0, DEBUG = 100, INFO = 200, WARNING = 300, ERROR = 400, FATAL = 500
    'defaultTimezone' => 'Europe/Lisbon'
);

//constant to prevent direct script access
define('VIRUS', 1);
define('VERSION', '0.9');

require_once 'libs/core.php';

//The app name to be returned in the server responses.
//e.g. <app_name><cats><cat>...</cat><cat>...</cat><cat>...</cat></cats></app_name>
define('RESPONSE_APP_NAME', 'virus');

//The minimum php version to be supported by this script. 
//NOTE: This script is not supported by PHP versions bellow 5.3, but you can enforce higher versions.
define('MINIMUM_PHP_VERSION', "5.3.0");


//HTML status codes
define('HTML_200_OK', 200);
define('HTML_201_CREATED', 201);
define('HTML_202_ACCEPTED', 202);
define('HTML_400_BAD_REQUEST', 400);
define('HTML_401_UNAUTHORIZED', 401);
define('HTML_403_FORBIDDEN', 403);
define('HTML_404_NOT_FOUND', 404);
define('HTML_405_METHOD_NOT_ALLOWED', 405);
define('HTML_406_NOT_ACCEPTABLE', 406);
define('HTML_500_INTERNAL_SERVER_ERROR', 500);
define('HTML_501_NOT_IMPLEMENTED', 501);
define('HTML_502_SERVICE_UNAVAILABLE', 503);


define('HTML_500_DEFAULT_TITLE', 'Internal Server Error');
define('HTML_500_DEFAULT_MESSAGE', 'Something went wrong with the server.');

//The default limit and page values for most requests that returns paginated collection
// of results, if these are not specified on the request URI.
define('API_DEFAULT_RESULT_LIMIT', 100);
define('API_DEFAULT_RESULT_PAGE', 1);
//The maximum limit value. This prevents a request from overloading the server.
define("API_MAX_LIMIT", 10000);
//The available formats for returning data, separated by ':'. This framework only has built in suport to xml and json.
define("API_RESPONSE_TYPES", "xml:json");
//The default return format.
define("API_DEFAULT_RESPONSE_TYPE", "xml");

//if you want to change the location of some components, here is the spot!

const CONTROLLERS_FOLDER = 'controllers/';
const CONTROLLERS_NAMESPACE = 'VIRUS\\webservice\\controllers';
const MODELS_FOLDER = 'models/';
const MODELS_NAMESPACE = 'VIRUS\\webservice\\models';
const SERVICES_FOLTER = 'services/';
const SERVICES_NAMESPACE = 'VIRUS\\webservice\\services';
const VIEWS_FOLDER = 'views/';

define('LOG_LEVEL', $config['loglevel']);
define('ROOT_DIRECTORY', dirname(__FILE__) . '/');
// The name of THIS file

define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
date_default_timezone_set(isset($config['defaultTimezone']) ? $config['defaultTimezone'] : 'Europe/Lisbon');

//-1 = show all errors
//0 = disable reporting
error_reporting(isset($config['debug']) && $config['debug'] === true || $config['debug'] === 1 ? -1 : 0);

define('APP_DEBUG', $config['debug']);

//hide some server info
header("X-Powered-By: Y0URMaM.NET");

function includeSafe($filename)
{
    $result = false;
    if (is_file($filename) && is_readable($filename))
    {
        //for capturing the warning output, case cannot be included
        $result = include_once $filename;
    }
    return $result;
}

//Register an autoloader. Now we are able to load and use namespaced classes without the need of importing them.
CoreVIRUS::registerAutoloader();
//Register the error handler
CoreVIRUS::registerErrorHandler();

function showErrorResponse($httpStatus, $title, $msg, $debug = '', $die = true)
{
    CoreVIRUS::displayErrorResponse($httpStatus, $title, $msg, $debug, $die);
}

ob_start();
$logger = new \KLogger(ROOT_DIRECTORY . $config['logFile'], LOG_LEVEL);
ob_clean();
if ($logger->Log_Status != \KLogger::LOG_OPEN)
{
    showErrorResponse(HTML_500_INTERNAL_SERVER_ERROR, 'Internal Server Error', 'Shit just happened!', "Unable to open log file!");
}
CoreVIRUS::setLogger($logger);

CoreVIRUS::rawLog(CoreVIRUS::LOG_DEBUG, '#----- Core files and Logger loaded. -----#', true);

if (version_compare(PHP_VERSION, MINIMUM_PHP_VERSION, '<='))
{
    $logger->logFatal('Server PHP 5.3.0 version or higher is required, found version ' . PHP_VERSION . '!');
    showErrorResponse(HTML_500_INTERNAL_SERVER_ERROR, 'Internal Server Error', 'Shit just happened!', "Server PHP 5.3.0 version or higher is required!");
}

try
{
    $db = new \PDO("{$config['dbDriver']}:host={$config['dbHost']};dbname={$config['dbName']}", $config['dbUser'], $config['dbPassword']);
    $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    CoreVIRUS::registerDB($db);
} catch (\PDOException $e)
{
    CoreVIRUS::logFatal("Database connection error: " . $e);
    showErrorResponse(HTML_502_SERVICE_UNAVAILABLE, 'Database connection error', 'It seems that the database is busy for us (or just anti-social)! Try again later.');
}
CoreVIRUS::logDebug("Database connection established and registered.");

$uri = null;
try
{
    $uri = URI::getInstance();
} catch (\Exception $e)
{
    CoreVIRUS::logWarning("Error found while loading URI class: " . $e);
    showErrorResponse(HTML_400_BAD_REQUEST, 'Bad Request', 'The request has invalid characters');
}
CoreVIRUS::logDebug("URI class loaded.");


//The first segment should be the controller
$controllerSegment = $uri->getSegment(0);

if (!$controllerSegment)
{
    CoreVIRUS::logWarning("Undefined controller");
    showErrorResponse(HTML_404_NOT_FOUND, 'API Not Found', 'Please specify a valid API and Service!');
}

if (!includeSafe(CONTROLLERS_FOLDER . $controllerSegment . '.php'))
{
    CoreVIRUS::logWarning("Unknown controller specified: $controllerSegment");
    showErrorResponse(HTML_404_NOT_FOUND, 'API Not Found', 'Please specify a valid API and Service!');
} else
{
    $path = CONTROLLERS_FOLDER . $controllerSegment . '.php';
    CoreVIRUS::logDebug("Loaded controller file $path.");
}
$controllerSegment = CONTROLLERS_NAMESPACE . '\\' . ucfirst($controllerSegment);
if (!class_exists($controllerSegment))
{
    CoreVIRUS::logFatal("Found the controller file, but we were unable to load the requested controller '$controllerSegment' class.");
    showErrorResponse(HTML_404_NOT_FOUND, 'API Not Found', 'Please specify a valid API and Service!');
}
$controller = new $controllerSegment;
if (!($controller instanceof controllers\Controller))
{
    CoreVIRUS::logFatal("The requested controller class '$controllerSegment' doesn't implement the Controller interface.");
    showErrorResponse(HTML_404_NOT_FOUND, 'API Not Found', 'Please specify a valid API and Service!');
}
CoreVIRUS::setController($controller);

try
{
    $resource = $uri->getSegment(1, NULL);
    if ($resource === NULL)
        $controller->_default();
    else
        $controller->_remap($resource, $uri->getSegmentArray(2));
} catch (PDOException $ex)
{
    $error = 'Fatal error: ' . $ex;
    CoreVIRUS::logFatal($error);
    showErrorResponse(HTML_500_INTERNAL_SERVER_ERROR, 'Database Error', 'Something went wrong with the connection to the database.', $error);
} catch (\Exception $ex)
{
    $error = 'Fatal error: ' . $ex;
    CoreVIRUS::logFatal($error);
    showErrorResponse(HTML_500_INTERNAL_SERVER_ERROR, 'Server General Error', 'Something went wrong with the server.', $error);
}
?>