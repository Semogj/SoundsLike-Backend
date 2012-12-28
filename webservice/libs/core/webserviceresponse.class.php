<?php
namespace VIRUS\webservice;

if(!defined("VIRUS")){
    die("You are not allowed here!");    
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

