<?php

namespace VIRUS\webservice\controllers;

use VIRUS\webservice\CoreVIRUS;
use VIRUS\webservice\WebserviceRequest;
use VIRUS\webservice\WebserviceResponse;
use VIRUS\webservice\WebserviceErrorResponse;

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
        parent::__construct();
    }

    public function _remap($resource, array $segments = array())
    {
        parent::_remap($resource, $segments);
    }

    public function _beforeResponse(WebserviceResponse $response)
    {
        parent::_beforeResponse($response);
    }


}

