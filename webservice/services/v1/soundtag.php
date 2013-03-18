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
use VIRUS\webservice\models\SoundTagModel;
use VIRUS\webservice\WebserviceOkResponse;
use VIRUS\webservice\models\SoundSegmentModel;
use VIRUS\webservice\WebserviceErrorResponse;

class SoundTagService extends WebserviceService
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
            $resultArr = SoundTagModel::get($limit, $offsetPage);
            //var_export($resultArr);
            $output = new WebserviceCollection($this->getServiceName(), $resultArr, null, $limit, $offsetPage);
        } else
        {

            //are we selecting the related collection to this entry?
            switch ($request->getRawSegment(2, null))
            {
                case 'soundsegment':
                    $idAudioSegment = $request->getRawSegmentAsInt(3, false);
                    if ($idAudioSegment === false)
                    {
                        $resultArr = SoundSegmentModel::getFiltered(SoundSegmentModel::filter()->byVideoId($idVideoSegment), $limit, $offsetPage);
                        $output = new WebserviceCollection('soundsegment', $resultArr, null, $limit, $offsetPage);
                    } else
                    {//we have a id segment
                        switch ($request->getRawSegment(4, null))
                        {
                            case 'similar':
                                $resultArr = SoundSegmentModel::getMostSimilarInVideo($idAudioSegment, $idVideoSegment, $limit, $offsetPage);
                                $output = new WebserviceCollection('soundsegment', $resultArr, null, $limit, $offsetPage);
                                break;
                            default:
                                $resultArr = SoundSegmentModel::getSingle($idAudioSegment);
                                $output = new WebserviceCollection('soundsegment', $resultArr);
                        }
                    }
                    break;
                default:
                    $resultArr = SoundTagModel::getSingle($idUserSegment);
                    $output = new WebserviceCollection($this->getServiceName(), $resultArr);
            }
        }
        return new WebserviceOkResponse($request->getAcceptType(), 200, array($output));
    }

    public function post(WebserviceRequest $request)
    {
        $userId = intval($request->getPostParameter('uid'), 10);
        $segmentId = intval($request->getPostParameter('sid'), 10);
        $tagName = $request->getPostParameter('tags');
        $type = $request->getPostParameter('type');
        $confidence = $request->getPostParameter('confidence');

        $errorArr = array();

        
        if ($userId <= 0)
        {
            $errorArr[] = 'Invalid user Id.';
        } else
        {
            $user = UserModel::getSingle($userId);
            if (empty($user))
            {
                $errorArr[] = 'The specified user id does not exist.';
            }
        }
        if ($segmentId <= 0)
        {
            $errorArr[] = 'Invalid sound segment Id.';
        } else
        {
            
            $segment = SoundSegmentModel::getSingle($segmentId);
            if (empty($segment))
            {
                $errorArr[] = 'The specified sound segment id does not exist.';
            }
        }
        //is a valid tag string (only a-z A-Z, spaces and commas (,) are accepted)
        
        if (preg_match("/[\w\,\s]+/i", $tagName))
        {
            $tagName = preg_split("/\,/", $tagName);
            if (array_values_empty($tagName))
            {
                $errorArr[] = 'The tagName parameter is invalid. Please verify if tags only use alphabeth letters (a-z A-Z) spaces and commas for separating multiple tags.';
            }else{
                $tagName = array_map('trim',$tagName);
            }
        } else
        {
            $errorArr[] = 'The tagName parameter is invalid. Please verify if tags only use alphabeth letters (a-z A-Z) spaces and commas for separating multiple tags.';
        }
        if(!empty($errorArr)){
            return WebserviceErrorResponse::getErrorResponse(WebserviceErrorResponse::ERR_INVALID_FORMAT, $request->getAcceptType(), null, $errorArr);
        }else{
            $result = SoundTagModel::createEntry($userId, $segmentId, $tagName, $type, $confidence);
            if(!$result){
                return WebserviceErrorResponse::getErrorResponse(WebserviceErrorResponse::ERR_CONFLICT, $request->getAcceptType());
            }else{
                return new WebserviceOkResponse($request->getAcceptType(),HTML_201_CREATED,array('insertId' => $result));
            }
        }
        
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
