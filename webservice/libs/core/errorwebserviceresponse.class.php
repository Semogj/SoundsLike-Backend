<?php
namespace VIRUS\webservice;

if(!defined("VIRUS")){
    die("You are not allowed here!");    
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

