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

require_once 'webserviceresponse.class.php';

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
        $this->outputResource['output'] = $errorArray;
    }

}

