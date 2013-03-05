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
use VIRUS\webservice\models\UserModel;
use VIRUS\webservice\OkWebserviceResponse;
use VIRUS\webservice\models\SoundSegmentModel;
use VIRUS\webservice\models\SoundTagModel;

class UserService extends WebserviceService
{

    /**
     *
     * @var VideoModel 
     */
//    private $videoModel;

    public function __construct($serviceName)
    {
        parent::__construct($serviceName);
    }

    public function beforeRequest(WebserviceRequest $request)
    {

//        $this->videoModel = new \VIRUS\webservice\models\VideoModel();
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
        $idUserSegment = $request->getRawSegmentAsInt(1, false);
        if ($idUserSegment === false)
        {
            $resultArr = UserModel::get($limit, $offsetPage);
            //var_export($resultArr);
            $output = new WebserviceCollection($this->getServiceName(), $resultArr, null, $limit, $offsetPage);
        } else
        {

            //are we selecting the related collection to this entry?
            switch ($request->getRawSegment(2, null))
            {
                case 'tags': case 'soundtags': case 'tag': case 'soundtag':
                    $resultArr = SoundTagModel::getUserTags($idUserSegment, $limit, $offsetPage);
                    $output = new WebserviceCollection('soundtag', $resultArr, null, $limit, $offsetPage);
                    break;
                default:
                    $resultArr = UserModel::getSingle($idUserSegment);
                    $output = new WebserviceCollection($this->getServiceName(), $resultArr);
            }
        }
        return new OkWebserviceResponse($request->getAcceptType(), 200, array($output));
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
