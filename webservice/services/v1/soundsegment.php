<?php

namespace VIRUS\webservice\services;

if (!defined("VIRUS"))
{
    die("You are not allowed here!");
}

use VIRUS\webservice\CoreVIRUS;
use VIRUS\webservice\WebserviceRequest;
use VIRUS\webservice\WebserviceResponse;
use VIRUS\webservice\WebserviceCollection;
use VIRUS\webservice\models\VideoModel;
use VIRUS\webservice\OkWebserviceResponse;
use VIRUS\webservice\models\SoundSegmentModel as SoundSegmentModel;

class SoundSegmentService extends WebserviceService
{

    /**
     *
     * @var VideoModel 
     */
//    private $soundsegment;
    
    

    public function __construct($serviceName)
    {
        parent::__construct($serviceName);
    }

    public function beforeRequest(WebserviceRequest $request)
    {

//        $this->soundsegment = new SoundSegmentModel();
    }

    public function get(WebserviceRequest $request)
    {
        
        //"limit" and "page" parameters are used to prevent overload of the webservice.
        //$limit parameter reduces the output collection to a number of $limit entries by page
        $limit = $request->getSegmentAsPositiveInt('limit', 100, API_MAX_LIMIT);
        //$offsetPage parameter represents an indexed page composed a collection of size $limit.
        $offsetPage = $request->getSegmentAsPositiveInt('page', 1);
        
        //output variable must be a VIRUS\webservice\WebserviceResponse object.
        $output = null;
        //Checking if the first segment, after the service segment is an integer
        // if its an integer, it means we are selecting a specific entry of the service
        $idSegment = $request->getRawSegmentAsInt(1, false);
        if ($idSegment === false)
        {
            $resultArr = SoundSegmentModel::get($limit, $offsetPage);
            //var_export($resultArr);
            $resultResource = new WebserviceCollection($this->getServiceName(), $resultArr, null, $limit, $offsetPage);
            $output = new OkWebserviceResponse($request->getAcceptType(), HTML_200_OK, array($resultResource));
        } else
        {
            
            //are we selecting the related collection to this entry?
            switch ($request->getRawSegment(2, null))
            {
                case 'similar':
                
                    $resultArr = SoundSegmentModel::getMostSimilar($idSegment, $limit, $offsetPage); //fetch result
//                    $total = null; //fetch total here
                    $resultRes = new WebserviceCollection($this->getServiceName(), $resultArr);
                    $output = new OkWebserviceResponse($request->getAcceptType(), 200, array($resultRes));
                    break;
                default:
                    $resultArr = SoundSegmentModel::getSingle($idSegment);
                    $resultRes = new WebserviceCollection($this->getServiceName(), $resultArr);
                    $output = new OkWebserviceResponse($request->getAcceptType(), 200, array($resultRes));
            }
        }
        return $output;
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
