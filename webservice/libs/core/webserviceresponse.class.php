<?php

namespace VIRUS\webservice;

if (!defined("VIRUS"))
{
    die("You are not allowed here!");
}

class WebserviceResponse
{

    static $ERR_INVALID_RESOURCE = array('code'              => 404, 'title'             => 'Invalid Resource', 'description'       => 'This resource does not exist');
    static $ERR_INVALID_METHOD = array('code'                     => 405, 'title'                    => 'Invalid Method', 'description'              => 'No method with that name in this package');
    static $ERR_AUTHENTICATION_FAILED = array('code'              => 511, 'error_title'       => 'Authentication Failed', 'description'       => 'You do not have permissions to access the service');
    static $ERR_INVALID_FORMAT = array('code'                  => 400, 'title'                 => 'Invalid Format', 'error_description'     => 'The service doesn\'t exist in that format');
    static $ERR_INVALID_PARAMETERS = array('code'                => 400, 'title'               => 'Invalid Parameters', 'description'         => 'Your request is missing a required parameter');
    static $ERR_OPERATION_FAILED = array('code'               => 500, 'title'              => 'Operation Failed', 'description'        => 'Something else went kaputs');
    static $ERR_SERVICE_OFFLINE = array('code'        => 503, 'title'       => 'Service Offline', 'description' => 'This service is temporarily offline. Try again later');

    const HTML_200_OK                              = 200;
    const HTML_201_CREATED                         = 201;
    const HTML_202_ACCEPTED                        = 202;
    const HTML_400_BAD_REQUEST                     = 400;
    const HTML_401_UNAUTHORIZED                    = 401;
    const HTML_403_FORBIDDEN                       = 403;
    const HTML_404_NOT_FOUND                       = 404;
    const HTML_405_METHOD_NOT_ALLOWED              = 405;
    const HTML_406_NOT_ACCEPTABLE                  = 406;
    const HTML_500_INTERNAL_SERVER_ERROR           = 500;
    const HTML_501_NOT_IMPLEMENTED                 = 501;
    const HTML_502_SERVICE_UNAVAILABLE             = 503;
    const HTML_511_NETWORK_AUTHENTICATION_REQUIRED = 511;

    protected $outputResource;

    public function __construct($status = 200, $resultType = 'xml', array $outputArray = array())
    {
        $this->outputResource['output']     = $outputArray;
        $this->outputResource['status']     = $status;
        $this->outputResource['resultType'] = $resultType;
    }

    public function getOutputArray()
    {
        return $this->outputResource['output'];
    }

    public function getStatus()
    {
        return $this->outputResource['status'];
    }

    public function getResultType()
    {
        return $this->outputResource['resultType'];
    }

    const XML_LABEL_COUNT       = 1;
    const XML_LABEL_PAGE        = 2;
    const XML_LABEL_LIMIT     = 3;
    const XML_LABEL_TOTAL       = 4;
    const XML_LABEL_TOTALPAGES  = 5;
    const XML_LALEL_NODES_COUNT = 6;
    const XML_LABEL_INT_PREFIX  = 7;

    private static $labels = array(self::XML_LABEL_COUNT       => 'size',
        self::XML_LABEL_PAGE        => 'page', self::XML_LABEL_LIMIT     => 'perpage',
        self::XML_LABEL_TOTAL       => 'totalsize', self::XML_LABEL_TOTALPAGES  => 'totalpages',
        self::XML_LALEL_NODES_COUNT => 'size', self::XML_LABEL_INT_PREFIX  => 'value-'
    );

    public function getOutputArrayAsXML(array $xmlLabels = array(), $arr = null)
    {
        return $this->_getArrayAsXML(isset($arr) ? $arr : $this->outputResource['output'], null, $xmlLabels);
    }

    private function _getArrayAsXML(array $theArray, $previousKey = null, array $xmlLabels = array())
    {
        $labels = self::$labels;
//        echo '############################## <br />';
//        var_dump($theArray);
//        die();
        if (!empty($xmlLabels))
        {
            $labels = array_merge($labels, $xmlLabels);
        }
        $result = '';

        if (is_array($theArray))
        {
            foreach ($theArray as $key => $value)
            {
                if (is_object($value) && $value instanceof WebserviceCollection)
                {
                    $callObj = $this;
                    $callable = function($arr, $labels) use($callObj) {
                                return $callObj->getOutputArrayAsXML($labels, $arr);
                            };
                    $result .= $value->getResultXML($callable, $labels);
                } elseif (is_array($value))
                {
                    $key   = strtolower(is_numeric($key) && $previousKey != null ? $previousKey : $key);
                    $sufix = " {$labels[self::XML_LALEL_NODES_COUNT]}=\"" . count($value) . '"';
                    $value = self::_getArrayAsXML($value, $key, $labels);
                    $result .= "<{$key}{$sufix}>$value</$key>";
                } else
                {
                    $value = htmlspecialchars($value, null, 'UTF-8');
                    if(isset($key)){
                        if (is_numeric($key))
                            $result .= "<{$labels[self::XML_LABEL_INT_PREFIX]}{$key}>{$value}</{$labels[self::XML_LABEL_INT_PREFIX]}{$key}>";
                        else{
                            $key = strtolower($key);
                            $result .= "<$key>$value</$key>";
                        }
                    }else{
                        $result .= $value;
                    }
                }
            }
        } else
        {
            $result .= htmlspecialchars($theArray, null, 'UTF-8');
        }
        CoreVIRUS::getLogger()->LogDebug("$result");
        return $result;
    }

}

