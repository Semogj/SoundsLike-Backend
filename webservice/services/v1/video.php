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
use VIRUS\webservice\models\SoundSegmentModel;
use VIRUS\webservice\models\SoundTagModel;

class VideoService extends WebserviceService
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
        $idVideoSegment = $request->getRawSegmentAsInt(1, false);
        if ($idVideoSegment === false)
        {
            $resultArr = VideoModel::get($limit, $offsetPage);
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
                    {//we have a soundSegment id url-segment
                        switch ($request->getRawSegment(4, null))
                        { 
                            case 'similar':
                                $resultArr = SoundSegmentModel::getMostSimilarInVideo($idAudioSegment, $idVideoSegment, $limit, $offsetPage);
                                $output = new WebserviceCollection('soundsegment', $resultArr, null, $limit, $offsetPage);
                                break;
                            case 'soundtags': case 'soundtag': case 'tag': case 'tags':
//                                die($idAudioSegment);
                                switch ($request->getSegment(5, null))
                                {
                                    case 'user':
                                        $userId = $request->getSegment(6, 0);
                                        $resultArr = SoundTagModel::getUserTagsInAudioSegment($userId, $idAudioSegment, $limit, $offsetPage); //fetch result
                                        $output = new WebserviceCollection('soundtag', $resultArr);
                                        break;
                                    default:
                                        CoreVIRUS::logDebug("idAudioSegment = " . $idAudioSegment);
                                        $resultArr = SoundTagModel::getAudioSegmentWeightedTags($idAudioSegment, $limit, $offsetPage); //fetch result
                                        CoreVIRUS::logDebug(print_r($resultArr,true));
                                        $output = new WebserviceCollection('soundtag', $resultArr);
                                }
                                break;
                            default:
                                $resultArr = SoundSegmentModel::getSingle($idAudioSegment);
                                $output = new WebserviceCollection('soundsegment', $resultArr);
                        }
                    }
                    break;
                default:
                    $resultArr = VideoModel::getSingle($idVideoSegment);
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
