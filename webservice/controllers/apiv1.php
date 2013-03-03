<?php
namespace VIRUS\webservice\controllers;
use VIRUS\webservice\CoreVIRUS;
use VIRUS\webservice\WebserviceRequest;
use VIRUS\webservice\WebserviceResponse;
use VIRUS\webservice\ErrorWebserviceResponse;

if (!defined("VIRUS"))
{//prevent script direct access
    header('HTTP/1.1 404 Not Found');
    header("X-Powered-By: ");
    echo "<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">\n<html><head>\n<title>404 Not Found</title>\n</head>
          <body>\n<h1>Not Found</h1>\n<p>The requested URL " . $_SERVER['REQUEST_URI'] . " was not found on this server.</p>\n
          <hr>\n" . $_SERVER['SERVER_SIGNATURE'] . "\n</body></html>\n";
    die();
}

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
            $errorMsg = "These aren't the droids you are looking for! Invalid service resource '$resource'.";
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

