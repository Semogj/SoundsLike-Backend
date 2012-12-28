<?php

namespace VIRUS\webservice\services;

if(!defined("VIRUS")){
    die("You are not allowed here!");    
}

use VIRUS\webservice\CoreVIRUS;
use VIRUS\webservice\WebserviceRequest;
use VIRUS\webservice\WebserviceResponse;

class VideoService extends WebserviceService
{

    private $videoModel;

    public function __construct()
    {
        parent::__construct();
    }

    public function beforeRequest(WebserviceRequest $request)
    {
        $this->videoModel = CoreVIRUS::loadModel('video');
    }

    public function get(WebserviceRequest $request)
    {
        echo "hi!";
        die();
    }

    public function post(WebserviceRequest $request)
    {
        
    }

    public function put(WebserviceRequest $request)
    {
        
    }

    public function delete(WebserviceRequest $request)
    {
        
    }

    public function afterRequest(WebserviceRequest $request)
    {
        
    }

}
