<?php

namespace VIRUS\webservice\services;

use VIRUS\webservice\WebserviceRequest;
use VIRUS\webservice\CoreVIRUS;
use VIRUS\webservice\ErrorWebserviceResponse;
use VIRUS\webservice\WebserviceResponse;

if (!defined("VIRUS"))
{//prevent script direct accessF
    header('HTTP/1.1 404 Not Found');
    header("X-Powered-By: ");
    echo "<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">\n<html><head>\n<title>404 Not Found</title>\n</head>
          <body>\n<h1>Not Found</h1>\n<p>The requested URL " . $_SERVER['REQUEST_URI'] . " was not found on this server.</p>\n
          <hr>\n" . $_SERVER['SERVER_SIGNATURE'] . "\n</body></html>\n";
    die();
}

abstract class WebserviceService
{

    /**
     *
     * @var \KLogger
     */
    protected $logger;
    private $serviceName;
    
    /**
     * If you want to use a fixed name, just pass a string when overriding this constructor!
     * @param string $serviceName
     */
    public function __construct($serviceName)
    {
        $this->logger = \VIRUS\webservice\CoreVIRUS::getLogger();
        $logger = CoreVIRUS::getLogger();
        $this->serviceName = $serviceName;
    }
    
    public function getServiceName(){
        return $this->serviceName;
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
                    $logger->logError("Unexpected result when processing the method '$requestMethod' of resource  '{$request->getResource()}'.");
                    return new ErrorWebserviceResponse(WebserviceResponse::$ERR_OPERATION_FAILED, 'The service returned an unexpected result.');
                }
                return $result;
            } else
            {

                $logger->logInfo("Call to invalid webservice method '$requestMethod' of resource '{$request->getResource()}'.");
                return new ErrorWebserviceResponse(WebserviceResponse::$ERR_INVALID_METHOD, "Invalid resource method $requestMethod.");
            }
        } catch (Exception $ex)
        {
            $logger->logError("Exception raised when processing the method '$requestMethod' of resource '{$request->getResource()}'.
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

/**
 GET SKELETON:

 public function get(WebserviceRequest $request)
    {

        //"limit" and "page" parameters are used to prevent overload of the webservice.
        //$limit parameter reduces the output collection to a number of $limit entries by page
        $limit = $request->getSegmentAsPositiveInt('limit', 100, API_MAX_LIMIT);
        //$offsetPage parameter represents an indexed page composed a collection of size $limit.
        $offsetPage = $request->getSegmentAsPositiveInt('page', 1);
                
        //output variable must be a VIRUS\webservice\WebserviceResponse object.
        $output = null;
        //Checking if the first segment, after the service segment is an integer
        // if its an integer, it means we are selecting a specific entry of the service
        $idSegment = $request->getRawSegmentAsInt(1, false);
        if ($idSegment === false)
        {
            $resultArr = array(); //fetch result here
            $resultResource = new WebserviceCollection($this->getServiceName(), $resultArr, null, $limit, $offsetPage);
            $output = new OkWebserviceResponse($request->getAcceptType(), 200, array($resultResource));
        } else
        {
            //are we selecting the related collection to this entry?
            switch ($request->getRawSegment(2, null))
            {
                case 'servi':
                
                    $resultArr = array(); //fetch result
                    $total = null; //fetch total here
                    $resultRes = new ResultResource('article', $request, $total, $limit, $offsetPage);
                    $output = new OkWebserviceResponse($request->getAcceptType(), 200, array($resultRes));
                    break;
                default:
                    $resultArr = $this->newsModel->getSingleNewsItemById($idSegment);
                    $resultRes = new ResultResource('article', $resultArr);
                    $output = new OkWebserviceResponse($request->getAcceptType(), 200, array($resultRes));
            }
        }
        return $output;
    } 
 */