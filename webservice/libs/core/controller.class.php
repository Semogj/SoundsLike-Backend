<?php

namespace VIRUS\webservice\controllers;

use VIRUS\webservice\CoreVIRUS;
use VIRUS\webservice\WebserviceResponse;
use VIRUS\webservice\WebserviceErrorResponse;
use VIRUS\webservice\WebserviceRequest;

if (!defined("VIRUS"))
{//prevent script direct accessF
    header('HTTP/1.1 404 Not Found');
    header("X-Powered-By: ");
    echo "<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">\n<html><head>\n<title>404 Not Found</title>\n</head>
          <body>\n<h1>Not Found</h1>\n<p>The requested URL " . $_SERVER['REQUEST_URI'] . " was not found on this server.</p>\n
          <hr>\n" . $_SERVER['SERVER_SIGNATURE'] . "\n</body></html>\n";
    die();
}

abstract class Controller
{

    public function __construct()
    {
        
    }

    public function _remap($resource, array $segments)
    {
        $request = new WebserviceRequest($resource, $segments);
        CoreVIRUS::logRaw(CoreVirus::LOG_DEBUG, "Request: {$request->getDebugString()}.");
        CoreVIRUS::logRaw(CoreVirus::LOG_DEBUG_DETAILED, 'Segments: ' . print_r($request->getSegments(), true) . '.');
        $response = null;
        $service = CoreVIRUS::loadService($resource, 1);
        if (!$service)
        {
            $errorMsg = "Invalid service resource '$resource'. These aren't the droids you are looking for!";
            CoreVIRUS::logInfo("Invalid service request 'apiv1/$resource'");
            $response = WebserviceErrorResponse::getErrorResponse(WebserviceErrorResponse::ERR_INVALID_RESOURCE, $request->getAcceptType(), $errorMsg);
        } else
        {
            CoreVIRUS::logDebug('Calling processRequest() of service class ' . get_class($request) . '.');
            $response = $service->processRequest($request);
        }

        $this->_beforeResponse($response);
        ob_start();
        CoreVIRUS::loadView('webservice/webservice', array('response' => $response, 'logger' => CoreVIRUS::getLogger()));
        ob_end_flush();
    }

    public function _beforeResponse(WebserviceResponse $response)
    {
        
    }

}

