<?php
namespace VIRUS\webservice;

if(!defined("VIRUS")){
    die("You are not allowed here!");    
}

require_once 'webserviceresponse.class.php';

class OkWebserviceResponse extends WebserviceResponse
{

    public function __construct($resultType = 'xml', $status = WebserviceResponse::HTML_200_OK, array $outputArray = array())
    {
        parent::__construct($status, $resultType, $outputArray);
    }

}

