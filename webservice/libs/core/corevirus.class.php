<?php

namespace VIRUS\webservice;

if(!defined("VIRUS")){
    die("You are not allowed here!");    
}

/**
 * Class for global database access,
 * (I could do it simply by global variable, but I dislike that way!)
 *
 * @author semogj
 */
class CoreVIRUS
{

    /**
     *
     * @static \KLogger $logger 
     */
    private static $dbArray = array(), $logger = null;
    private static $controller = null;
    private static $loadedModels = array();

    /**
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

    public static function registerDB(\PDO $db, $dbIdentifier = "DEFAULT")
    {
        self::$dbArray[$dbIdentifier] = $db;
    }

    public static function setLogger($logger)
    {
        self::$logger = $logger;
    }

    /**
     * 
     * @return \KLogger
     */
    public static function getLogger()
    {
        return self::$logger;
    }

    public static function requireModel($model)
    {
        if (!loadModel($model))
        {
            self::displayErrorResponse(500, "Internal Server Error", "");
        }
    }

    /**
     * 
     * @param String $model
     * @return VIRUS\webservice\models\Model|boolean
     */
    public static function loadModel($model)
    {
        $model = strtolower(trim($model));
        $className = MODELS_NAMESPACE . ucfirst($model . "model");

        if (array_key_exists($className, self::$loadedModels))
        {
            return self::$loadedModels[$className];
        }

        $path = ROOT_DIRECTORY . MODELS_FOLDER . "{$model}model.php";
        if (!includeSafe($path))
        {
            self::getLogger()->LogError("Unable to include model '$model' (path: '$path').", debug_backtrace());
            false;
        }

        if (class_exists($className) && ($modelObj = new $className) instanceof models\Model)
        {
            self::getLogger()->LogDebug("Model $model loaded with success from $path.", debug_backtrace());
            return self::$loadedModels[$className] = $modelObj;
        }
        self::getLogger()->LogError("The model file was included but we were unable to load the model class '$className'.", debug_backtrace());
        return false;
    }

    /**
     * 
     * @param String $service
     * @param int|String $version api version
     * @return \VIRUS\webservice\services\WebserviceService|boolean
     */
    public static function loadService($service, $version = 1)
    {
        $service = strtolower(trim($service));
        $className = SERVICES_NAMESPACE . ucfirst($service . "Service");

        $filename = ROOT_DIRECTORY . SERVICES_FOLTER . "/v{$version}/{$service}.php";
        //is it already loaded?
        if (class_exists($className))
        {
            self::getLogger()->LogWarn("Attempt to load an already loaded service ($service)!", debug_backtrace());
            return new $className;
        }

        if (!includeSafe($filename))
        {
            self::getLogger()->LogError("Unable to include service '$service' source file (path: '$filename').", debug_backtrace());
            false;
        }
        if (class_exists($className) && ($serviceObj = new $className($service)) instanceof services\WebserviceService)
        {
            self::getLogger()->LogDebug("Service '$service' loaded with success from file $filename.", debug_backtrace());
            return $serviceObj;
        }
        self::getLogger()->LogError("The service file was included but we were unable to load the service class '$className'.", debug_backtrace());
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
     * @param type $view the view name. If trying to load the view file "myserviceview.pgp"
     * you must use inly the "myservice" in this variable.
     * @param array $data an array of data to be delivered to the view.
     * @return boolean
     */
    public static function loadView($view, $data)
    {
        $view = strtolower(trim($view));
        $filename = ROOT_DIRECTORY . VIEWS_FOLDER . "{$view}view.php";
        $result = false;
        if (is_file($filename) && is_readable($filename))
        {
            self::getLogger()->LogDebug("View '$view' loaded with success from file $filename.", debug_backtrace());
            $data = new ViewData($data);
            $result = include $filename;
        }
        if (!$result)
        {
            self::getLogger()->LogError("Unable to include view '$view' (path: '$filename').", debug_backtrace());
        }
        return $result;
    }

    public static function displayErrorResponse($httpStatus, $title, $msg, $debug = '', $die = true)
    {
        \http_response_code($httpStatus);
        header('Content-type: text/xml');
        $status = getStatusCode($httpStatus);
        echo '<', RESPONSE_APP_NAME, " status=\"$httpStatus\" code=\"$status\">
        <error>
        <code>$httpStatus</code>
        <title>$title</title>
        <description>$msg</description>",
        !empty($debug) ? "<debug>$debug</debug>" : '',
        '</error></', RESPONSE_APP_NAME, '>';
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
