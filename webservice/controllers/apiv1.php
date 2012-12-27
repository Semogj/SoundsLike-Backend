<?php
namespace VIRUS\webservice\controllers;
use VIRUS\webservice\CoreVIRUS;
use VIRUS\webservice\WebserviceRequest;
use VIRUS\webservice\WebserviceResponse;
use VIRUS\webservice\ErrorWebserviceResponse;
use VIRUS\webservice\OkWebserviceResponse;

class Apiv1 extends Controller
{
    public function __construct()
    {
        
    }
    public function _remap($resource, array $params = array())
    {
        $logger = CoreVIRUS::getLogger();
        $request = new WebserviceRequest($resource, $params);
        $logger->LogDebug("Request: {$request->getDebugString()}.");
        $response = null;
        $service = CoreVIRUS::loadService($resource);
        if(!$service)
        {
            $errorMsg = "These aren't the droids you are looking for! Invalid webservice resource '$resource'";
            $logger->LogInfo("Invalid service request 'apiv1/$resource'");
            $response = new ErrorWebserviceResponse(WebserviceResponse::$ERR_INVALID_RESOURCE, $errorMsg);
            
        } else
        {
            $logger->LogDebug('Calling processRequest() of service class ' . get_class($request) . '.');
            $response = $service->processRequest($request);
        }
        ob_start();
        CoreVIRUS::loadView('webservice/webservice', array('response' => $response, 'logger' => $logger));
        ob_end_flush();
    }

    public function _default()
    {
        
    }

    

}

