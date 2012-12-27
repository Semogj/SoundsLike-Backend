<?php

namespace VIRUS\webservice;

require_once 'databasemodel.php';
require_once 'uri.php';
require_once 'utils.php';
require_once 'Slim/Slim.php';
require_once 'KLogger.php';

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
        if (class_exists($className) && class_implements(MODEL_PARENT_CLASS))
        {
            self::getLogger()->LogDebug("Model $model loaded with success from $path.", debug_backtrace());
            return self::$loadedModels[$className] = new $className;
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
        if (class_exists($className) && class_implements(SERVICE_PARENT_CLASS))
        {
            self::getLogger()->LogDebug("Service '$service' loaded with success from file $filename.", debug_backtrace());
            return new $className;
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
        if($die)
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

class WebserviceRequest
{

    private $resource, $method, $resultType, $segments, $rawParameters, $content, $contentType;

    const AVAILABLE_HTTP_METHODS = 'GET:POST:PUT:DELETE';
    const ACCEPT_TYPES = 'xml:json';
    const CONTENT_TYPES = 'xml:json';
    const DEFAULT_CONTENT_TYPE = 'xml';
    const DEFAULT_ACCEPT_TYPE = 'xml';
    const DEFAULT_HTTP_METHOD = 'GET';

    private static function _splitParamKeyVal($delimitersChars, $string)
    {
        $charArr = str_split($delimitersChars);
        $nChars = count($charArr);
        if ($nChars == 0)
        {
            return array($string);
        }
        $strLen = strlen($string);
        $j = 0;
        for ($i = 0; $i < $strLen; $i++)
        {
            for ($j = 0; $j < $nChars; $j++)
            {
                if ($charArr[$j] === $string[$i])
                {
                    return array(substr($string, 0, $i), $i + 1 < $strLen ? substr($string, $i + 1) : '');
                }
            }
        }
        return array($string);
    }

    public function __construct($resource, array $segments)
    {
        $this->resource = $resource;
        $this->rawParameters[0] = $resource;
        $this->segments[0] = $resource;
        $segmentIndex = 1;
        //handle parameters
        foreach ($segments as $segment)
        {
            //lets search for ; separators
            $moreSegments = explode(';', $segment);
            foreach ($moreSegments as $param)
            {
                if (empty($param))
                {
                    continue;
                }
                $this->rawParameters[$segmentIndex] = $param;
                //both : and = are parameters key-value separators, e.g. /key=value/x:1;limit=2;
                $paramArr = self::_splitParamKeyVal(':=', $param);
                if (count($paramArr) == 2)
                {
                    $val = is_numeric($paramArr[1]) ? intval($paramArr[1], 10) : trim(urldecode($paramArr[1]));
                    $this->segments[$segmentIndex] = $val;
                    if (!empty($paramArr[0]))
                    {
                        $this->segments[$paramArr[0]] = $val;
                    }
                } else
                {
                    $this->segments[$segmentIndex] = trim(urldecode($param));
                }
                $segmentIndex++;
            }
        }

        $method = strtoupper($_SERVER['REQUEST_METHOD']);
        if (!in_array($method, explode(':', self::AVAILABLE_HTTP_METHODS)))
        {
            $method = DEFAULT_HTTP_METHOD;
        }
        $this->method = $method;
        //handle acceptType
        $httpAccept = '';
        if (isset($_SERVER['HTTP_ACCEPT']))
            $httpAccept = strtolower(trim($_SERVER['HTTP_ACCEPT']));
        $resultTypes = explode(':', self::ACCEPT_TYPES);
        $this->resultType = self::DEFAULT_ACCEPT_TYPE;
        foreach ($resultTypes as $at)
        {
            if (strpos($httpAccept, $at))
            {
                $this->resultType = $at;
                break;
            }
        }
        //handle contentType
        $httpContentType = isset($_SERVER['CONTENT_TYPE']) ? strtolower(trim($_SERVER['CONTENT_TYPE'])) : self::DEFAULT_CONTENT_TYPE;
        $contentTypes = explode(':', self::CONTENT_TYPES);
        $this->contentType = self::DEFAULT_CONTENT_TYPE;
        foreach ($contentTypes as $ct)
        {
            if (strpos($httpContentType, $ct))
            {
                $this->contentType = $ct;
                break;
            }
        }
        //get request content. Normally empty for GET and DELETE http methods
        $content = @file_get_contents('php://input');
        $this->content = $content === null ? '' : $content;
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getContentFirstXmlTag($key, $defaultNotFound = false)
    {
        $getXmlFirstTag = function(SimpleXMLElement $elem, $key) use (&$getXmlFirstTag) {
                    if (strcmp($elem->getName(), $key))
                    {
                        return $elem->asXML();
                    } else
                    {
                        foreach ($elem->children() as $child)
                        {
                            $result = $getXmlFirstTag($child, $key, $defaultNotFound);
                            if ($result !== false)
                            {
                                return $result;
                            }
                        }
                    }
                    return false;
                };
        libxml_use_internal_errors(false);
        $xml = simplexml_load_string($this->content);
        if (!$xml)
            return $defaultNotFound;
        $result = $this->_getXmlFirstTag($xml, $key);
        return $result === false ? $defaultNotFound : $result;
    }

    /**
     *
     * @return array the array from a json_decode call 
     */
    public function getContentAsJsonArray()
    {
        return json_decode($this->content);
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getContentType()
    {
        return $this->contentType;
    }

    public function getSegment($indexOrKey, $default = false)
    {
        return !empty($this->segments[$indexOrKey]) ? $this->segments[$indexOrKey] : $default;
    }

    public function getSegmentAsInt($indexOrKey, $default = false)
    {
        if (empty($this->segments[$indexOrKey]))
            return $default;
        $str = $this->segments[$indexOrKey];
        return is_numeric($str) ? intval($str, 10) : $default;
    }

    public function getSegmentAsPositiveInt($indexOrKey, $default = false)
    {
        if (empty($this->segments[$indexOrKey]))
            return $default;
        $val = intval($this->segments[$indexOrKey]);
        return $val > 0 ? $val : $default;
    }

    public function getRawSegment($indexOrKey, $default = false)
    {
        return !empty($this->rawParameters[$indexOrKey]) ? $this->rawParameters[$indexOrKey] : $default;
    }

    public function getRawSegmentAsInt($indexOrKey, $default = false)
    {
        if (empty($this->rawParameters[$indexOrKey]))
            return $default;
        $str = $this->rawParameters[$indexOrKey];
        return is_numeric($str) ? intval($str, 10) : $default;
    }

    public function getRawSegmentAsPositiveInt($indexOrKey, $default = false)
    {
        if (empty($this->rawParameters[$indexOrKey]))
            return $default;
        $val = intval($this->rawParameters[$indexOrKey]);
        return $val > 0 ? $val : $default;
    }

    public function getSegments()
    {
        return $this->segments;
    }

    public function getRawParameters()
    {
        return $this->rawParameters;
    }

    public function getAcceptType($ignoreGetParam = false, $getParamKey = 'rtype')
    {
        return !$ignoreGetParam ? $this->getSegment($getParamKey, $this->resultType) : $this->resultType;
    }

    public function getPostParameter($key, $default = null)
    {

        switch ($this->getContentType())
        {
            case 'json':
                $arr = $this->getContentAsJsonArray();
                if (array_key_exists($key, $arr))
                {
                    return $arr[$key];
                } else
                {
                    while (list($k, $v) = each($array))
                    {
                        if (is_array($v))
                        {
                            return array_key_exists($key, $v) ? $v[$key] : $default;
                        }
                        break; //we only wanna check the first array elem.
                    }
                    return $default;
                }
                break;
            default: //xml
                return $this->getContentFirstXmlTag($key, $default);
        }
    }

    public function getDebugString()
    {
        return $this->method . ' ' . URI::getInstance()->getURIString() . " ContentType: $this->resultType ResultType: $this->resultType";
    }

}

class WebserviceResponse
{

    static $ERR_INVALID_RESOURCE = array('code' => 404, 'title' => 'Invalid Resource', 'description' => 'This resource does not exist');
    static $ERR_INVALID_METHOD = array('code' => 405, 'title' => 'Invalid Method', 'description' => 'No method with that name in this package');
    static $ERR_AUTHENTICATION_FAILED = array('code' => 511, 'error_title' => 'Authentication Failed', 'description' => 'You do not have permissions to access the service');
    static $ERR_INVALID_FORMAT = array('code' => 400, 'title' => 'Invalid Format', 'error_description' => 'The service doesn\'t exist in that format');
    static $ERR_INVALID_PARAMETERS = array('code' => 400, 'title' => 'Invalid Parameters', 'description' => 'Your request is missing a required parameter');
    static $ERR_OPERATION_FAILED = array('code' => 500, 'title' => 'Operation Failed', 'description' => 'Something else went kaputs');
    static $ERR_SERVICE_OFFLINE = array('code' => 503, 'title' => 'Service Offline', 'description' => 'This service is temporarily offline. Try again later');

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
    const HTML_511_NETWORK_AUTHENTICATION_REQUIRED = 511;

    protected $outputResource;

    public function __construct($status = 200, $resultType = 'xml', array $outputArray = array())
    {
        $this->outputResource = $outputArray;
        $this->outputResource['status'] = $status;
        $this->outputResource['resultType'] = $resultType;
    }

    public function getOutputArray()
    {
        return $this->outputResource;
    }

    public function getStatus()
    {
        return $this->outputResource['status'];
    }

    public function getResultType()
    {
        return $this->outputResource['resultType'];
    }

}

class ErrorWebserviceResponse extends WebserviceResponse
{

    public function __construct(array $errorArray, $altDescription = false, array $errorMessages = array(), $resultType = 'xml')
    {
        parent::__construct($errorArray['code'], $resultType);
        if (!empty($altDescription))
            $errorArray['description'] = $altDescription;
        if (!empty($errorMessages))
        {
            $tmpArr = array();
            foreach ($errorMessages as $msg)
            {
                $tmpArr['message'] = $msg;
            }
            $errorArray['messages'] = $tmpArr;
        }
        $this->outputResource['error'] = $errorArray;
    }
}

class OkWebserviceResponse extends WebserviceResponse
{

    public function __construct($resultType = 'xml', $status = WebserviceResponse::HTML_200_OK, array $outputArray = array())
    {
        parent::__construct($status, $resultType, $status, $outputArray);
    }

}

class WebserviceCollection
{

    public $resulArray, $count, $total, $perPage, $page, $totalPages, $resourceTag;

    public function __construct($resourceTag, $resultArray, $total = null, $perPage = null, $page = null)
    {
        $this->resourceTag = $resourceTag;
        $this->resulArray = $resultArray;
        $this->count = count($resultArray);
        $this->total = $total;
        $this->perPage = $perPage;
        $this->page = $page;
        if ($total !== null && $perPage !== null)
        {
            $tpages = $perPage != 0 ? $total / $perPage : 0;
            $this->totalPages = is_float($tpages) ? intval($tpages, 10) + 1 : $tpages;
        } else
        {
            $this->totalPages = null;
        }
    }

}

namespace VIRUS\webservice\controllers;

abstract class Controller
{

    abstract function __construct();

    abstract function _remap($resource, array $segments);

    abstract function _default();
}

namespace VIRUS\webservice;

class ViewData
{

    private $array;

    public function __construct(array $data = array())
    {
        $this->array = $data;
    }

    public function get($key, $default = null)
    {
        return isset($this->array[$key]) ? $this->array[$key] : $default;
    }

//    public function getInt($key, $default){
//        
//    }
//    public function getPositiveInt($key, $default){
//        
//    }
//    public function getFloat($key, $default){
//        
//    }
//    public function getString($key, $default){
//        
//    }
//    public function __get($name)
//    {
//        echo "Getting '$name'\n";
//        if (array_key_exists($name, $this->data))
//        {
//            return $this->data[$name];
//        }
//
//        $trace = debug_backtrace();
//        trigger_error(
//                'Undefined property via __get(): ' . $name .
//                ' in ' . $trace[0]['file'] .
//                ' on line ' . $trace[0]['line'], E_USER_NOTICE);
//        return null;
//    }
//
//    public function __call($name, $arguments)
//    {
//        // Note: value of $name is case sensitive.
//        echo "Calling object method '$name' "
//        . implode(', ', $arguments) . "\n";
//    }
}

namespace VIRUS\webservice\services;

use VIRUS\webservice\WebserviceRequest;

abstract class WebserviceService
{

    /**
     *
     * @var \KLogger
     */
    protected $logger;

    public function __construct()
    {
        parent::__construct();
        $this->logger = \VIRUS\webservice\CoreVIRUS::getLogger();
    }

    /**
     * @return WebserviceResponse 
     */
    public function processRequest(WebserviceRequest $request)
    {
        $logger = CoreVIRUS::getLogger();
        $requestMethod = $request->getMethod();
        try
        {
            if (method_exists($this, $requestMethod))
            {
                $this->beforeRequest($request);
                $result = $this->{$requestMethod}($request);
                $this->afterRequest($request);
                if (empty($result) || !($result instanceof WebserviceResponse))
                {
                    $logger->LogError("Unexpected result when processing the method '$requestMethod' of resource  '{$request->getResource()}'.");
                    return new ErrorWebserviceResponse(WebserviceResponse::$ERR_OPERATION_FAILED, 'The service returned an unexpected result.');
                }
                return $result;
            } else
            {

                $logger->LogInfo("Call to invalid webservice method '$requestMethod' of resource '{$request->getResource()}'.");
                return new ErrorWebserviceResponse(WebserviceResponse::$ERR_INVALID_METHOD, "Invalid resource method $requestMethod.");
            }
        } catch (Exception $ex)
        {
            $logger->LogError("Exception raised when processing the method '$requestMethod' of resource '{$request->getResource()}'.
                    Exception message: {$ex->getMessage()} at line {$ex->getLine()} of file {$ex->getFile()}.");
            return new ErrorWebserviceResponse(WebserviceResponse::$ERR_OPERATION_FAILED, 'The service went kaputs while processing your request...');
        }
    }

    abstract function beforeRequest(WebserviceRequest $request);

    /**
     * @return WebserviceResponse 
     */
    abstract function get(WebserviceRequest $request);

    /**
     * @return WebserviceResponse 
     */
    abstract function post(WebserviceRequest $request);

    /**
     * @return WebserviceResponse 
     */
    abstract function put(WebserviceRequest $request);

    /**
     * @return WebserviceResponse 
     */
    abstract function delete(WebserviceRequest $request);

    abstract function afterRequest(WebserviceRequest $request);
}

namespace VIRUS\webservice;
