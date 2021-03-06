<?php

namespace VIRUS\webservice\services;

if (!defined("VIRUS"))
{
    die("You are not allowed here!");
}

use VIRUS\webservice\CoreVIRUS;
use VIRUS\webservice\WebserviceRequest;
use VIRUS\webservice\WebserviceResponse;
use VIRUS\webservice\WebserviceErrorResponse;
use VIRUS\webservice\WebserviceCollection;
use VIRUS\webservice\models\VideoModel;
use VIRUS\webservice\WebserviceOkResponse;
use VIRUS\webservice\models\SoundSegmentModel;
use VIRUS\webservice\models\SoundTagModel;

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
        $resultResource = null;
        if ($idSegment === false)
        {
            $resultArr = SoundSegmentModel::get($limit, $offsetPage);
            //var_export($resultArr);
            $resultResource = new WebserviceCollection($this->getServiceName(), $resultArr, null, $limit, $offsetPage);
        } else
        {

            //are we selecting the related collection to this entry?
            switch ($request->getRawSegment(2, null))
            {
                case 'similar':
                    $resultArr = SoundSegmentModel::getMostSimilar($idSegment, $limit, $offsetPage); //fetch result
                    $resultResource = new WebserviceCollection($this->getServiceName(), $resultArr);
                    break;
                case 'similarsoundtag' : case 'similarsoundtags': case 'similartag': case 'similartags':
                    $similarLimit = $request->getSegmentAsInt('similarLimit', 10);
                    $includeCurrent = $request->getSegment('includeCurrent', false) ? true : false;
                    $userId = $request->getSegmentAsInt('user', false);
                    $resultArr = SoundSegmentModel::getTagsOfMostSimilar($idSegment, $similarLimit, $includeCurrent, $userId, $limit, $offsetPage); //fetch result
                    $resultResource = new WebserviceCollection('soundtag', $resultArr);
                    break;
                case 'soundtags': case 'soundtag': case 'tag': case 'tags':
                    switch ($request->getSegment(3, null))
                    {
                        case 'user':
                            $userId = $request->getSegment(4, 0);
                            $resultArr = SoundTagModel::getUserTagsInAudioSegment($userId, $idSegment, $limit, $offsetPage); //fetch result
                            $resultResource = new WebserviceCollection('soundtag', $resultArr);
                            break;
                        default:
                            $resultArr = SoundTagModel::getAudioSegmentWeightedTags($idSegment, $limit, $offsetPage); //fetch result
                            $resultResource = new WebserviceCollection('soundtag', $resultArr);
                    }
                    break;
                case 'spectrogram': {
                        $sound = SoundSegmentModel::getSingle($idSegment);
                        $sound = $sound[0];
                        if (empty($sound['spectrogram']))
                        {
                            CoreVIRUS::logDebug("Spectrogram requested for soundsegment '$idSegment': No cache.");

                            $video = VideoModel::getSingle($sound['videoId']);
                            $video = $video[0];
                            //check for the wav format being available
                            $formats = explode(',', $video['availableFormats']);
                            if (!in_array('wav', $formats))
                            {
                                //TODO: log here!;
                                CoreVIRUS::logDebug("!!!!!!!!!!!");
                                return WebserviceErrorResponse::getErrorResponse(WebserviceErrorResponse::ERR_OPERATION_FAILED, $request->getAcceptType(), 'The requested resource does not support spectrogram. Only resources with wav format supports this feature.');
                            }
                            $videoFilePath = ROOT_DIRECTORY . CoreVIRUS::getConfig('resourcesPath'). $video['resourcesPath'];
                            if (!is_readable($videoFilePath . '.wav'))
                            {
                                CoreVIRUS::logFatal("The file $videoFilePath.wav  doesn't exist! Cannot use it for the creation of the spectrogram.");
                                return WebserviceErrorResponse::getErrorResponse(WebserviceErrorResponse::ERR_OPERATION_FAILED);
                            }
                            //FIXME: test with floats
                            $start = intval($sound['start'], 10);
                            $duration = intval($sound['end'], 10) - $start;
                            $outputFile = $videoFilePath . '-spectrogram.png';
//                            $command = "sox";
                            //sox "/home/semogj/Dropbox/www/VIRUS-AudioEvents-Webservice/webservice/resources/BacktotheFuture/BacktotheFuture.wav"
                            // -n trim 5344 4 spectrogram -x 800 -y 200 -l -r 
                            // -o /home/semogj/Dropbox/www/VIRUS-AudioEvents-Webservice/webservice/resources/BacktotheFuture/BacktotheFuture-spectrogram.png
                            $command = "sox \"$videoFilePath.wav\" -n trim $start $duration spectrogram -x 800 -y 200 -l -r -o \"$outputFile\"";
//                            $output = shell_exec($command, $output);
                            exec($command, $output);
                            
                            if (!file_exists($outputFile))
                            {
                                CoreVIRUS::logError("Cannot find spectrogram file '$outputFile' after sox command exec.");
                                return WebserviceErrorResponse::getErrorResponse(WebserviceErrorResponse::ERR_OPERATION_FAILED);
                            }
                            $sound['spectrogram'] = file_get_contents($outputFile);
                            SoundSegmentModel::updateSpectrogram($idSegment, $sound['spectrogram']);
                        }else{
                            CoreVIRUS::logDebug("Spectrogram requested for soundsegment '$idSegment': Found a cached spectogram.");
                        }
                        $resultResource = new WebserviceCollection($this->getServiceName(), $sound);
                        $output = new WebserviceOkResponse($request->getAcceptType(), 200, array($resultResource));
                    }
                default:
                    $resultArr = SoundSegmentModel::getSingle($idSegment);
                    $resultResource = new WebserviceCollection($this->getServiceName(), $resultArr);
                    $output = new WebserviceOkResponse($request->getAcceptType(), 200, array($resultResource));
            }
        }
        return new WebserviceOkResponse($request->getAcceptType(), HTML_200_OK, array($resultResource));
        ;
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
