<?php

namespace VIRUS\webservice\services;

use VIRUS\webservice\WebserviceRequest;
use VIRUS\webservice\CoreVIRUS;
use VIRUS\webservice\ErrorWebserviceResponse;
use VIRUS\webservice\WebserviceResponse;

if(!defined("VIRUS")){
    die("You are not allowed here!");    
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

