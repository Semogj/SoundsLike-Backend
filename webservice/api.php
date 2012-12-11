<?php

namespace VIRUS\webservice;

$config = array(
    'dbDriver' => 'mysql',
    'dbHost' => 'localhost',
    'dbUser' => 'root',
    'dbPassword' => 'root123!',
    'dbName' => 'virus_as',
    'logFile' => 'logs/logFile.txt'
);

require_once 'libs/Slim/Slim.php';
require_once 'libs/KLogger.php';
require_once 'libs/virus.php';
require_once 'libs/modelscore.php';

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
const HTML_502_SERVICE_UNAVAILABLE = 502;
const API_DEFAULT_RESULT_LIMIT = 100; //results by page
const API_DEFAULT_RESULT_PAGE = 1; //default result page
const LOG_LEVEL = \KLogger::DEBUG;

define('ROOT_DIRECTORY', dirname(__FILE__) . '/');

function includeSafe($filename)
{
    $result = false;
    if (is_file($filename))
    {
        //for capturing the warning output, case cannot be included
        ob_start();
        $result = include $filename;
        ob_clean();
    }
    return $result;
}

function errorDisplayBasicResponse($httpStatus, $title, $msg, $debug = '')
{

    \http_response_code($httpStatus);
    header('Content-type: text/xml');

    echo '<', RESPONSE_APP_NAME, "><error>
        <code>$httpStatus</code>
        <title>$title</title>
        <description>$msg</description>",
    !empty($debug) ? "<debug>$debug</debug>" : '',
    '</error></', RESPONSE_APP_NAME, '>';
    die(); //Die demon!
}

//ob_start();
$logger = new \KLogger(ROOT_DIRECTORY . $config['logFile'], LOG_LEVEL);
//ob_clean();
if ($logger->Log_Status != \KLogger::LOG_OPEN)
{
    errorDisplayBasicResponse(HTML_500_INTERNAL_SERVER_ERROR, 'Internal Server Error', 'Shit just happened!', "Unable to open log file!");
}

/**
 * 
 * @global \KLogger $logger
 * @return \KLogger
 */
function getLogger()
{
    global $logger;
    return $logger;
}

if (version_compare(PHP_VERSION, MINIMUM_PHP_VERSION, '<'))
{
    errorDisplayBasicResponse(HTML_500_INTERNAL_SERVER_ERROR, 'Internal Server Error', 'Shit just happened!', "Server PHP 5.3.0 version or higher is required!");
}


$db = null;
try
{
    $db = new \PDO("{$config['dbDriver']}:host={$config['dbHost']};dbname={$config['dbName']}", $config['dbUser'], $config['dbPassword']);
} catch (\PDOException $ex)
{
    $logger->LogFatal("Database connection error:");
    errorDisplayBasicResponse(HTML_502_SERVICE_UNAVAILABLE, 'Database connection error', 'It seems that the database is busy for us! Try again later.');
}
\Slim\Slim::registerAutoloader();

VIRUS::registerDB($db);

$webservice = new \Slim\Slim();
includeSafe("services/video.php");


$webservice->get('/api/v:version/:service', function($version, $service) use ($webservice, $logger) {
            if (includeSafe("services/$service.php"))
                $logger->LogWarn("The requested service $service was not found!");
            $function = "services\{$service}Webservice";
            if (function_exists($function))
            {
                call_user_func($function, array($webservice, "/api/$service/"));
            }else{
                $logger->LogError("The requested service $service file was found but the $function function is missing!");
            }

            $webservice->pass();
        });

//            echo "hi $name!";
//        });
//$webservice->run();
?>