<?php

namespace VIRUS\webservice;

$config = array(
    'dbDriver' => 'mysql',
    'dbHost' => 'localhost',
    'dbUser' => 'root',
    'dbPassword' => 'root123!',
    'dbName' => 'virus_as',
    'logFile' => 'logs/logFile.txt',
    'debug' => true
);

//constant to prevent direct script access
define('VIRUS',1);

require_once 'libs/core.php';



const RESPONSE_APP_NAME = 'virus';
const MINIMUM_PHP_VERSION = "5.3.0";
const HTML_200_OK = 200;
const HTML_201_CREATED = 201;
const HTML_202_ACCEPTED = 202;
const HTML_400_BAD_REQUEST = 400;
const HTML_401_UNAUTHORIZED = 401;
const HTML_403_FORBIDDEN = 403;
const HTML_404_NOT_FOUND = 404;
const HTML_405_METHOD_NOT_ALLOWED = 405;
const HTML_406_NOT_ACCEPTABLE = 406;
const HTML_500_INTERNAL_SERVER_ERROR = 500;
const HTML_501_NOT_IMPLEMENTED = 501;
const HTML_502_SERVICE_UNAVAILABLE = 503;
const API_DEFAULT_RESULT_LIMIT = 100; //results by page
const API_DEFAULT_RESULT_PAGE = 1; //default result page
const LOG_LEVEL = \KLogger::DEBUG;

const CONTROLLERS_FOLDER = 'controllers/';
const CONTROLLERS_NAMESPACE = 'VIRUS\\webservice\\controllers\\';
const MODELS_FOLDER = 'models/';
const MODELS_NAMESPACE = 'VIRUS\\webservice\\models\\';
const SERVICES_FOLTER = 'services/';
const SERVICES_NAMESPACE = 'VIRUS\\webservice\\services\\';
const VIEWS_FOLDER = 'views/';


define('ROOT_DIRECTORY', dirname(__FILE__) . '/');


date_default_timezone_set('Europe/Lisbon');

//-1 = show all errors
//0 = disable reporting
error_reporting(isset($config['debug']) && $config['debug'] === true || $config['debug'] === 1 ? -1 : 0);
header("X-Powered-By: Y0URMaM.NET");


function includeSafe($filename)
{
    $result = false;
    if (is_file($filename) && is_readable($filename))
    {
        //for capturing the warning output, case cannot be included
        $result = include $filename;
    }
    return $result;
}

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

$logger->LogDebug("Core files and Logger loaded.");

if (version_compare(PHP_VERSION, MINIMUM_PHP_VERSION, '<='))
{
    $logger->LogFatal('Server PHP 5.3.0 version or higher is required, found version ' . PHP_VERSION . '!');
    showErrorResponse(HTML_500_INTERNAL_SERVER_ERROR, 'Internal Server Error', 'Shit just happened!', "Server PHP 5.3.0 version or higher is required!");
}

$db = null;
try
{
    $db = new \PDO("{$config['dbDriver']}:host={$config['dbHost']};dbname={$config['dbName']}", $config['dbUser'], $config['dbPassword']);
} catch (\PDOException $e)
{
    $logger->LogFatal("Database connection error: " . $e);
    showErrorResponse(HTML_502_SERVICE_UNAVAILABLE, 'Database connection error', 
            'It seems that the database is busy for us (or just anti-social)! Try again later.');
}

CoreVIRUS::registerDB($db);
$logger->LogDebug("Database connection established and registered.");

$uri = null;

try
{
    $uri = URI::getInstance();
} catch (Exception $e)
{
    $logger->LogWarn("Error found while loading URI class: " . $e);
    showErrorResponse(HTML_400_BAD_REQUEST, 'Bad Request', 'The request has invalid characters');
}
$logger->LogDebug("URI class loaded.");


//The first segment should be the controller
$controllerSegment = $uri->getSegment(0);

if (!$controllerSegment)
{
    $logger->LogWarn("Undefined controller");
    showErrorResponse(HTML_404_NOT_FOUND, 'API Not Found', 'Please specify a valid API and Service!');
}

if (!includeSafe(CONTROLLERS_FOLDER . $controllerSegment . '.php'))
{
    $logger->LogWarn("Unknown controller specified: $controllerSegment");
    showErrorResponse(HTML_404_NOT_FOUND, 'API Not Found', 'Please specify a valid API and Service!');
}else{
    $path = CONTROLLERS_FOLDER . $controllerSegment . '.php';
    $logger->LogDebug("Loaded controller file $path.");
}
$controllerSegment = CONTROLLERS_NAMESPACE . ucfirst($controllerSegment);
if (!class_exists($controllerSegment))
{
    $logger->LogFatal("Found the controller file, but we were unable to load the requested controller '$controllerSegment' class.");
    showErrorResponse(HTML_404_NOT_FOUND, 'API Not Found', 'Please specify a valid API and Service!');
}
$controller = new $controllerSegment;
if (!($controller instanceof controllers\Controller))
{
    $logger->LogFatal("The requested controller class '$controllerSegment' doesn't implement the Controller interface.");
    showErrorResponse(HTML_404_NOT_FOUND, 'API Not Found', 'Please specify a valid API and Service!');
}
CoreVIRUS::setController($controller);

$resource = $uri->getSegment(1, NULL);
if ($resource === NULL)
    $controller->_default();
else
    $controller->_remap($resource, $uri->getSegmentArray(2));




//
//$webservice = new \Slim\Slim();
//includeSafe("services/video.php");
//
//
////echo "hi";
//
//$webservice->get('/api/v:version/:service', function($version, $service) use ($webservice, $logger) {
//            if (includeSafe("services/$service.php"))
//                $logger->LogWarn("The requested service $service was not found!");
//            $function = "services\{$service}Webservice";
//            if (function_exists($function))
//            {
//                call_user_func($function, array($webservice, "/api/$service/"));
//            } else
//            {
//                $logger->LogError("The requested service $service file was found but the $function function is missing!");
//            }
//
//            $webservice->pass();
//        });
//            echo "hi $name!";
//        });
//$webservice->run();
?>