<?php

namespace VIRUS\webservice;

if (!defined("VIRUS"))
{//prevent script direct access
    header('HTTP/1.1 404 Not Found');
    header("X-Powered-By: ");
    echo "<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">\n<html><head>\n<title>404 Not Found</title>\n</head>
          <body>\n<h1>Not Found</h1>\n<p>The requested URL " . $_SERVER['REQUEST_URI'] . " was not found on this server.</p>\n
          <hr>\n" . $_SERVER['SERVER_SIGNATURE'] . "\n</body></html>\n";
    die();
}

require_once 'webserviceresponse.class.php';

class WebserviceErrorResponse extends WebserviceResponse
{

    public function __construct($status = 500, $resultType = 'xml', $errorTitle = '', $errorMessage = '', $errorDetails = '')
    {
        parent::__construct($status, $resultType);
        $errorArr = array('title' => $errorTitle, 'description' => $errorMessage, 'details' => $errorDetails);
        $this->setOutputArray($errorArr);
    }

    public static function getErrorResponse($constOrStatusCode, $resultType = 'xml', $message = null, array $details = array() , $title = null)
    {

        switch ($constOrStatusCode)
        {
            case self::ERR_INVALID_RESOURCE: case 404: //Not Found
                $constOrStatusCode = 404;
                if (!empty($title))
                    $title = 'Invalid Resource';
                if (!empty($message))
                    $message = 'This resource does not exist';
                break;
            case self::ERR_INVALID_METHOD:
                $constOrStatusCode = 405;
            case 405: //405 Method Not Allowed
                if (!empty($title))
                    $title = 'Invalid Method';
                if (!empty($message))
                    $message = 'No method with that name in this service';
                break;
            case self::ERR_AUTHENTICATION_FAILED:
                $constOrStatusCode = 511;
            case 401: case 402: case 403: //Unauthorized, Payment Required, Forbidden
                if (!empty($title))
                    $title = 'Authentication Failed';
                if (!empty($message))
                    $message = 'You do not have permissions to access the service';
                break;
            case self::ERR_INVALID_FORMAT:
                $constOrStatusCode = 400;
            case 400: //Bad Request
                if (!empty($title))
                    $title = 'Invalid Format';
                if (!empty($message))
                    $message = 'The service doesn\'t exist in that format';
                break;
            case self::ERR_INVALID_PARAMETERS:
                $constOrStatusCode = 400;
                if (!empty($title))
                    $title = 'Invalid Parameter';
                if (!empty($message))
                    $message = 'Your request is missing a required parameter';
                break;
            case self::ERR_SERVICE_OFFLINE:
                $constOrStatusCode = 503;
                if (!empty($title))
                    $title = 'Invalid Resource';
                if (!empty($message))
                    $message = 'This service is temporarily offline. Try again later';
                break;
            case self::ERR_IM_A_TEAPOT:
                $constOrStatusCode = 418;
            case 418: //I'm a teapot
                if (!empty($title))
                    $title = 'I\'m a teapot';
                if (!empty($message))
                    $message = 'I\'m a little teapot,
                                    Short and stout,
                                    Here is my handle, (one hand on hip)
                                    Here is my spout, (other arm out with elbow and wrist bent)
                                    When I get all steamed up,
                                    Hear me shout,
                                    Tip me over and pour me out! (lean over toward spout)';
                break;
            case self::ERR_TOO_MANY_REQUESTS:
                $constOrStatusCode = 429;
            case 429: //Too many requests
                if (!empty($title))
                    $title = 'Too Many Requests';
                if (!empty($message))
                    $message = 'You have reached your request quota per client. Try again later.';
                break;
            case self::ERR_NOT_IMPLEMENTED: 
                $constOrStatusCode = 501;
            case 501: // Not Implemented
                if (!empty($title))
                    $title = 'Not Implemented';
                if (!empty($message))
                    $message = 'Ther service/method you were trying to reach is not implemented here.';
                break;
            case self::ERR_OPERATION_FAILED: case 500: default: //Internal Server Error
                $constOrStatusCode = 500;
                if (!empty($title))
                    $title = 'Operation Failed';
                if (!empty($message))
                    $message = 'Something went wrong with the server.';
                break;
        }
        return new WebserviceErrorResponse($constOrStatusCode,$resultType,$details, $title, $message);
    }

    const ERR_INVALID_RESOURCE = 0x12321;
    const ERR_INVALID_METHOD = 0x23432;
    const ERR_AUTHENTICATION_FAILED = 0x34543;
    const ERR_INVALID_FORMAT = 0x45654;
    const ERR_INVALID_PARAMETERS = 0x56765;
    const ERR_OPERATION_FAILED = 0x67876;
    const ERR_SERVICE_OFFLINE = 0x78987;
    const ERR_NOT_IMPLEMENTED = 0x89098;
    const ERR_TOO_MANY_REQUESTS = 0x90109;
    const ERR_IM_A_TEAPOT = 0x91224;
    const ERR_METHOD_NOT_ALLOWED = 0x42524;

}

