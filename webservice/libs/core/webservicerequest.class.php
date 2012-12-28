<?php

namespace VIRUS\webservice;

if(!defined("VIRUS")){
    die("You are not allowed here!");    
}

class WebserviceRequest
{

    private $resource, $method, $resultType, $segments, $rawParameters, $content, $contentType;

    const AVAILABLE_HTTP_METHODS = 'GET:POST:PUT:DELETE';
    const ACCEPT_TYPES = 'xml:json';
    const CONTENT_TYPES = 'xml:json';
    const DEFAULT_CONTENT_TYPE = 'xml';
    const DEFAULT_ACCEPT_TYPE = 'xml';
    const DEFAULT_HTTP_METHOD = 'GET';

    private static function _splitParamKeyVal($delimitersChars, $string)
    {
        $charArr = str_split($delimitersChars);
        $nChars = count($charArr);
        if ($nChars == 0)
        {
            return array($string);
        }
        $strLen = strlen($string);
        $j = 0;
        for ($i = 0; $i < $strLen; $i++)
        {
            for ($j = 0; $j < $nChars; $j++)
            {
                if ($charArr[$j] === $string[$i])
                {
                    return array(substr($string, 0, $i), $i + 1 < $strLen ? substr($string, $i + 1) : '');
                }
            }
        }
        return array($string);
    }

    public function __construct($resource, array $segments)
    {
        $this->resource = $resource;
        $this->rawParameters[0] = $resource;
        $this->segments[0] = $resource;
        $segmentIndex = 1;
        //handle parameters
        foreach ($segments as $segment)
        {
            //lets search for ; separators
            $moreSegments = explode(';', $segment);
            foreach ($moreSegments as $param)
            {
                if (empty($param))
                {
                    continue;
                }
                $this->rawParameters[$segmentIndex] = $param;
                //both : and = are parameters key-value separators, e.g. /key=value/x:1;limit=2;
                $paramArr = self::_splitParamKeyVal(':=', $param);
                if (count($paramArr) == 2)
                {
                    $val = is_numeric($paramArr[1]) ? intval($paramArr[1], 10) : trim(urldecode($paramArr[1]));
                    $this->segments[$segmentIndex] = $val;
                    if (!empty($paramArr[0]))
                    {
                        $this->segments[$paramArr[0]] = $val;
                    }
                } else
                {
                    $this->segments[$segmentIndex] = trim(urldecode($param));
                }
                $segmentIndex++;
            }
        }

        $method = strtoupper($_SERVER['REQUEST_METHOD']);
        if (!in_array($method, explode(':', self::AVAILABLE_HTTP_METHODS)))
        {
            $method = DEFAULT_HTTP_METHOD;
        }
        $this->method = $method;
        //handle acceptType
        $httpAccept = '';
        if (isset($_SERVER['HTTP_ACCEPT']))
            $httpAccept = strtolower(trim($_SERVER['HTTP_ACCEPT']));
        $resultTypes = explode(':', self::ACCEPT_TYPES);
        $this->resultType = self::DEFAULT_ACCEPT_TYPE;
        foreach ($resultTypes as $at)
        {
            if (strpos($httpAccept, $at))
            {
                $this->resultType = $at;
                break;
            }
        }
        //handle contentType
        $httpContentType = isset($_SERVER['CONTENT_TYPE']) ? strtolower(trim($_SERVER['CONTENT_TYPE'])) : self::DEFAULT_CONTENT_TYPE;
        $contentTypes = explode(':', self::CONTENT_TYPES);
        $this->contentType = self::DEFAULT_CONTENT_TYPE;
        foreach ($contentTypes as $ct)
        {
            if (strpos($httpContentType, $ct))
            {
                $this->contentType = $ct;
                break;
            }
        }
        //get request content. Normally empty for GET and DELETE http methods
        $content = @file_get_contents('php://input');
        $this->content = $content === null ? '' : $content;
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getContentFirstXmlTag($key, $defaultNotFound = false)
    {
        $getXmlFirstTag = function(SimpleXMLElement $elem, $key) use (&$getXmlFirstTag) {
                    if (strcmp($elem->getName(), $key))
                    {
                        return $elem->asXML();
                    } else
                    {
                        foreach ($elem->children() as $child)
                        {
                            $result = $getXmlFirstTag($child, $key, $defaultNotFound);
                            if ($result !== false)
                            {
                                return $result;
                            }
                        }
                    }
                    return false;
                };
        libxml_use_internal_errors(false);
        $xml = simplexml_load_string($this->content);
        if (!$xml)
            return $defaultNotFound;
        $result = $this->_getXmlFirstTag($xml, $key);
        return $result === false ? $defaultNotFound : $result;
    }

    /**
     *
     * @return array the array from a json_decode call 
     */
    public function getContentAsJsonArray()
    {
        return json_decode($this->content);
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getContentType()
    {
        return $this->contentType;
    }

    public function getSegment($indexOrKey, $default = false)
    {
        return !empty($this->segments[$indexOrKey]) ? $this->segments[$indexOrKey] : $default;
    }

    public function getSegmentAsInt($indexOrKey, $default = false)
    {
        if (empty($this->segments[$indexOrKey]))
            return $default;
        $str = $this->segments[$indexOrKey];
        return is_numeric($str) ? intval($str, 10) : $default;
    }

    public function getSegmentAsPositiveInt($indexOrKey, $default = false)
    {
        if (empty($this->segments[$indexOrKey]))
            return $default;
        $val = intval($this->segments[$indexOrKey]);
        return $val > 0 ? $val : $default;
    }

    public function getRawSegment($indexOrKey, $default = false)
    {
        return !empty($this->rawParameters[$indexOrKey]) ? $this->rawParameters[$indexOrKey] : $default;
    }

    public function getRawSegmentAsInt($indexOrKey, $default = false)
    {
        if (empty($this->rawParameters[$indexOrKey]))
            return $default;
        $str = $this->rawParameters[$indexOrKey];
        return is_numeric($str) ? intval($str, 10) : $default;
    }

    public function getRawSegmentAsPositiveInt($indexOrKey, $default = false)
    {
        if (empty($this->rawParameters[$indexOrKey]))
            return $default;
        $val = intval($this->rawParameters[$indexOrKey]);
        return $val > 0 ? $val : $default;
    }

    public function getSegments()
    {
        return $this->segments;
    }

    public function getRawParameters()
    {
        return $this->rawParameters;
    }

    public function getAcceptType($ignoreGetParam = false, $getParamKey = 'rtype')
    {
        return !$ignoreGetParam ? $this->getSegment($getParamKey, $this->resultType) : $this->resultType;
    }

    public function getPostParameter($key, $default = null)
    {

        switch ($this->getContentType())
        {
            case 'json':
                $arr = $this->getContentAsJsonArray();
                if (array_key_exists($key, $arr))
                {
                    return $arr[$key];
                } else
                {
                    while (list($k, $v) = each($array))
                    {
                        if (is_array($v))
                        {
                            return array_key_exists($key, $v) ? $v[$key] : $default;
                        }
                        break; //we only wanna check the first array elem.
                    }
                    return $default;
                }
                break;
            default: //xml
                return $this->getContentFirstXmlTag($key, $default);
        }
    }

    public function getDebugString()
    {
        return $this->method . ' ' . URI::getInstance()->getURIString() . " ContentType: $this->resultType ResultType: $this->resultType";
    }

}
