<?php

namespace VIRUS\webservice;

if (!defined("VIRUS"))
{//prevent script direct accessF
    header('HTTP/1.1 404 Not Found');
    header("X-Powered-By: ");
    echo "<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">\n<html><head>\n<title>404 Not Found</title>\n</head>
          <body>\n<h1>Not Found</h1>\n<p>The requested URL " . $_SERVER['REQUEST_URI'] . " was not found on this server.</p>\n
          <hr>\n" . $_SERVER['SERVER_SIGNATURE'] . "\n</body></html>\n";
    die();
}

/**
 * Class for global database and logger access.
 * (I could do it simply by global variable, but I dislike that way!)
 *
 * @author semogj
 */
class CoreVIRUS
{

    const LOG_DEBUG_VERBOSE = 100;// Most Verbose
    const LOG_DEBUG_DETAILED = 200;
    const LOG_DEBUG = 300; 
    const LOG_INFO = 400; // ...
    const LOG_WARNING = 500; // ...
    const LOG_ERROR = 600; // ...
    const LOG_FATAL = 700; // Least Verbose 

    /**
     *
     * @static \KLogger $logger 
     */

    private static $dbArray = array(),
            $logger = null;
    private static $controller = null;
    private static $loadedModels = array();
    private static $config = array();

    /**
     * Returns a registered PDO database connector.
     * @param string $dbIdentifier the database indentifier. "DEFAULT" if not specified.
     * @return \PDO the db
     */
    public static function getDb($dbIdentifier = "DEFAULT")
    {
        if (key_exists($dbIdentifier, self::$dbArray))
        {
            return self::$dbArray[$dbIdentifier];
        }
        return null;
    }

    /**
     * Registers a PDO database connector for use in the app.
     * NOTE: this is not meant to be invoked by the developper!
     * 
     * @param \PDO $db the database connector object.
     * @param type $dbIdentifier an idenfifier for later access. If not specified it will be tied to "DEFAULT".
     */
    public static function registerDB(\PDO $db, $dbIdentifier = "DEFAULT")
    {
        self::$dbArray[$dbIdentifier] = $db;
    }
    public static function registerConfig(array $config){
        self::$config = $config;
    }
    
    public static function getConfig($key = null, $default = null){
        return $key === null ? self::$config : arrayFetchValue(self::$config, $key, $default);
    }

    /**
     * Register an logger instance.
     * NOTE: this is not meant to be invoked by the developper!
     * @param \KLogger $logger
     */
    public static function setLogger($logger)
    {
        self::$logger = $logger;
    }

    /**
     * Returns an logger instance.
     * @return \KLogger the logger instance.
     */
    public static function getLogger()
    {
        return self::$logger;
    }
    /**
     * Alternative log method to log() with file and line parameters.
     * The entry is prefixed with a timeline and suffixed with the error location.
     * If the file parameter is null or empty, this function is equivalent to the normal log() method;
     * @param int $level the logger level. Please use CoreVIRUS::LOG_* constants.
     * @param string $message the message to be logged.
     * @param string $file the file name or path where the error did happen.
     * @param int|string $line the file line where the error did happen.
     */
    public static function aLog($level, $message, $file = null, $line = null)
    {
        self::$logger->aLog($level, $message, $file, $line);
    }

    /**
     * Logs an error.
     * The entry is prefixed with a timeline and suffixed with the error location.
     * @param int $level the logger level. Please use CoreVIRUS::LOG_* constants.
     * @param string $message the message to be logged.
     * @param array $stacktrace if you want to report the offending line from the stacktrace, use debug_backtrace() array.
     */
    public static function log($level, $message, array $stacktrace = null)
    {
        self::$logger->log($level, $message, $stacktrace ? $stacktrace : debug_backtrace());
    }
    /**
     * Logs an entry to the log file without any prefix or sufix.
     * @param int $level 
     * @param string $rawMessage
     * @param boolean $includeTimeline true if you want to be prefixed with the default timeline.
     */
    public static function logRaw($level, $rawMessage, $includeTimeline = false)
    {
        self::$logger->rawLog($level, $rawMessage, $includeTimeline);
    }

    public static function logInfo($line, array $stacktrace = null)
    {
        self::$logger->log(self::LOG_INFO, $line, $stacktrace ? $stacktrace : debug_backtrace());
    }

    public static function logDebug($line, array $stacktrace = null)
    {
        self::$logger->log(self::LOG_DEBUG, $line, $stacktrace ? $stacktrace : debug_backtrace());
    }

    public static function logWarning($line, array $stacktrace = null)
    {
        self::$logger->log(self::LOG_WARNING, $line, $stacktrace ? $stacktrace : debug_backtrace());
    }

    public static function logError($line, array $stacktrace = null)
    {
        self::$logger->log(self::LOG_ERROR, $line, $stacktrace ? $stacktrace : debug_backtrace());
    }

    public static function logFatal($line, array $stacktrace = null)
    {
        self::$logger->log(self::LOG_FATAL, $line, $stacktrace ? $stacktrace : debug_backtrace());
    }

    /**
     * @deprecated since version 0.9 The framework now uses the PHP autoloading feature for namespaced classes.
     * @param string $model
     */
    public static function requireModel($model)
    {
        if (!loadModel($model))
        {
            self::displayErrorResponse(500, "Internal Server Error", "");
        }
    }

    /**
     * 
     * @param string $model
     * @return VIRUS\webservice\models\Model|boolean
     * @deprecated since version 0.9 The framework now uses the PHP autoloading feature for namespaced classes.
     */
    public static function loadModel($model)
    {
        $model = strtolower(trim($model));
        $className = MODELS_NAMESPACE . '\\' . ucfirst($model . "model");

        if (array_key_exists($className, self::$loadedModels))
        {
            return self::$loadedModels[$className];
        }

        $path = ROOT_DIRECTORY . MODELS_FOLDER . "{$model}model.php";
        if (!includeSafe($path))
        {
            self::getLogger()->logError("Unable to include model '$model' (path: '$path').", debug_backtrace());
            false;
        }

        if (class_exists($className) && ($modelObj = new $className) instanceof models\Model)
        {
            self::getLogger()->logDebug("Model $model loaded with success from $path.", debug_backtrace());
            return self::$loadedModels[$className] = $modelObj;
        }
        self::getLogger()->logError("The model file was included but we were unable to load the model class '$className'.", debug_backtrace());
        return false;
    }

    /**
     * Register the framework autoloader.
     * NOTE: this is not meant to be invoked by the developper!
     */
    public static function registerAutoloader()
    {
        spl_autoload_register(function ($class) {

                    $class = parseClassname($class, true);
                    switch ($class->namespace)
                    {
                        case SERVICES_NAMESPACE:
                            return includeSafe(ROOT_DIRECTORY . SERVICES_FOLTER . strtolower($class->classname) . '.php');
                        case MODELS_NAMESPACE:
                            return includeSafe(ROOT_DIRECTORY . MODELS_FOLDER . strtolower($class->classname) . '.php');
                        case CONTROLLERS_NAMESPACE:
                            return includeSafe(ROOT_DIRECTORY . CONTROLLERS_FOLDER . strtolower($class->classname) . '.php');
                        default:
                            return false;
                    }
                });
    }

    public static function registerErrorHandler()
    {
        set_error_handler(
                function ($errno, $errstr, $errfile, $errline) {
                    switch ($errno)
                    {
                        case E_USER_ERROR: case E_ERROR: case E_RECOVERABLE_ERROR: //lets dream for the day where we can catch an E_ERROR!
                            CoreVIRUS::aLog("Fatal Error: [$errno] $errstr", $errfile, $errline);
                            CoreVIRUS::displayErrorResponse(HTML_500_INTERNAL_SERVER_ERROR, HTML_500_DEFAULT_TITLE, HTML_500_DEFAULT_MESSAGE);
                            die(1);
                            break;

                        case E_WARNING: case E_USER_WARNING: case E_CORE_WARNING: case E_STRICT: case E_DEPRECATED: case E_USER_DEPRECATED:
                            CoreVIRUS::aLog(CoreVIRUS::LOG_WARNING,"Warning: [$errno] $errstr", $errfile, $errline);
                            break;
                        case E_NOTICE: E_USER_NOTICE:
                            CoreVIRUS::aLog(CoreVIRUS::LOG_WARNING,"Notice: [$errno] $errstr", $errfile, $errline);
                            break;

                        default:
                            CoreVIRUS::aLog(CoreVIRUS::LOG_ERROR, "Unknown Error: [$errno] $errstr", $errfile, $errline);
                            break;
                    }

                    /* Don't execute PHP internal error handler */
                    return true;
                }
        );
    }
    

    /**
     * Verifies and try to load a service from the service folder.
     * NOTE: this is not meant to be invoked by the developper!
     * @param String $service
     * @param int|String $version api version
     * @return \VIRUS\webservice\services\WebserviceService|boolean
     */
    public static function loadService($service, $version = 1)
    {
        $service = strtolower(trim($service));
        $className = SERVICES_NAMESPACE . '\\' . ucfirst($service . "Service");

        $filename = ROOT_DIRECTORY . SERVICES_FOLTER . "/v{$version}/{$service}.php";
        //is it already loaded?
        if (class_exists($className))
        {
            self::getLogger()->logWarning("Attempt to load an already loaded service ($service)!", debug_backtrace());
            return new $className;
        }

        if (!includeSafe($filename))
        {
            self::getLogger()->logError("Unable to include service '$service' source file (path: '$filename').", debug_backtrace());
            false;
        }
        if (class_exists($className) && ($serviceObj = new $className($service)) instanceof services\WebserviceService)
        {
            self::getLogger()->logDebug("Service '$service' loaded with success from file $filename.", debug_backtrace());
            return $serviceObj;
        }
        self::getLogger()->logError("The service file was included but we were unable to load the service class '$className'.", debug_backtrace());
        return false;
    }

    public static function requireView($view, array $data)
    {
        if (!loadView($view, $data))
        {
            self::displayErrorResponse(500, "Internal Server Error", "");
        }
    }

    /**
     * Allows the inclusion of a view file from the directory in the constant VIEWS_FOLDER.
     * Use it if you want to include any view.
     * 
     * @param type $view the view name, including the folder. If trying to load the view file "myserviceview.php"
     * you must use only the "myservice" in this variable. The name must not start with a slash (/)!
     * @param array $data an array of data to be delivered to the view.
     * @return boolean false if the view file cannot be included.
     */
    public static function loadView($view, $data)
    {
        $view = strtolower(trim($view));
        $filename = ROOT_DIRECTORY . VIEWS_FOLDER . "{$view}view.php";
        $result = false;
        if (is_file($filename) && is_readable($filename))
        {
            self::getLogger()->logDebug("View '$view' loaded with success from file $filename.", debug_backtrace());
            $data = new ViewData($data);
            $result = include $filename;
        }
        if (!$result)
        {
            self::getLogger()->logError("Unable to include view '$view' (path: '$filename').", debug_backtrace());
        }
        return $result;
    }

    /**
     * Outputs to the client an simple error response and terminates the execution of the request!
     * @param int $httpStatus An integer representing the HTTP status code to be returned.
     * @param string $title The error subject.
     * @param string $msg The error description.
     * @param string $debug An optional debug message to be displayed if DEBUG is enabled.
     * @param boolean $die DEFAULT=TRUE. Use FALSE to prevent the code execution to be terminated.
     */
    public static function displayErrorResponse($httpStatus, $title, $msg, $debug = '', $die = true)
    {

        $getContentType = function() {
                    $httpAccept = '';
                    if (isset($_SERVER['HTTP_ACCEPT']))
                        $httpAccept = strtolower(trim($_SERVER['HTTP_ACCEPT']));
                    if (strpos($httpAccept, 'json'))
                    {
                        $httpAccept = 'json';
                    } else
                    {
                        $httpAccept = 'xml';
                    }
                    return $httpAccept;
                };

        \http_response_code($httpStatus);
//        self::getLogger()->LogDebug("http accept: {$_SERVER['HTTP_ACCEPT']}");
        $type = $getContentType();
        header('Content-type: ' . ($type === 'json' ? 'application/json' : 'text/xml, charset=utf-8'));
        $status = getStatusCode($httpStatus);
        if ($type === 'json')
        {
            $outputArr = array('error' => array('code' => $httpStatus, 'title' => $title,
                    'description' => $msg));
            if (APP_DEBUG === true && !empty($debug))
                $outputArr['error']['debug'] = $debug;
            echo json_encode(array(RESPONSE_APP_NAME => $outputArr));
        } else
        {
            echo '<', RESPONSE_APP_NAME, " status=\"$httpStatus\" code=\"$status\">
                <error>
                <code>$httpStatus</code>
                <title>$title</title>
                <description>$msg</description>",
            APP_DEBUG === true && !empty($debug) ? "<debug>$debug</debug>" : '',
            '</error></', RESPONSE_APP_NAME, '>';
        }
        if ($die)
            die(); //Die demon!
    }

    public static function setController($controller)
    {
        self::$controller = $controller;
    }

    public static function getController()
    {
        return self::$controller;
    }

}
