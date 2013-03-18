<?php

namespace VIRUS\webservice;

if (!defined("VIRUS"))
{//prevent script direct accessF
    header('HTTP/1.1 404 Not Found');
    header("X-Powered-By: ");
    echo "<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">\n<html><head>\n<title>404 Not Found</title>\n</head>
          <body>\n<h1>Not Found</h1>\n<p>The requested URL " . $_SERVER['REQUEST_URI'] . " was not found on this server.</p>\n
          <hr>\n" . $_SERVER['SERVER_SIGNATURE'] . "\n</body></html>\n";
    die();
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
    const PARAM_DELIMITERS = ':=';

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

    private static function convert_type($var)
    {
        if (is_numeric($var))
        {
            if ((float) $var != (int) $var)
            {
                return (float) $var;
            } else
            {
                return (int) $var;
            }
        }

        if ($var == "true")
            return true;
        if ($var == "false")
            return false;

        return $var;
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
                $paramArr = self::_splitParamKeyVal(self::PARAM_DELIMITERS, $param);
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
            CoreVIRUS::logWarning("Unknown/Unsupported HTTP request method: \"$method\". Falling back to default method (" . DEFAULT_HTTP_METHOD . ').');
            $method = DEFAULT_HTTP_METHOD;
        }
        $this->method = $method;
        //handle acceptType
        $httpAccept = '';
        if (isset($_SERVER['HTTP_ACCEPT']))
            $httpAccept = strtolower(trim($_SERVER['HTTP_ACCEPT']));
        $resultTypes = explode(':', self::ACCEPT_TYPES);
        $this->resultType = null;
        foreach ($resultTypes as $at)
        {
            if (strpos($httpAccept, $at))
            {
                $this->resultType = $at;
                break;
            }
        }
        if ($this->resultType === null)
        {
            CoreVIRUS::logWarning("Unsupported HTTP accept types: \"$httpAccept\". The answer will be delivered in the default  '" . self::DEFAULT_ACCEPT_TYPE . '\' format.');
            $this->resultType = self::DEFAULT_ACCEPT_TYPE;
        }
        //handle contentType
        $httpContentType = isset($_SERVER['CONTENT_TYPE']) ? strtolower(trim($_SERVER['CONTENT_TYPE'])) : self::DEFAULT_CONTENT_TYPE;
        $contentTypes = explode(':', self::CONTENT_TYPES);
        $this->contentType = null;
        foreach ($contentTypes as $ct)
        {
            if (strpos($httpContentType, $ct))
            {
                $this->contentType = $ct;
                break;
            }
        }
        if ($this->contentType === null)
        {
            CoreVIRUS::logWarning("Unknown/Unsupported HTTP request contentType: \"$this->contentType\". Falling back to default http mode (" . self::DEFAULT_CONTENT_TYPE . ').');
            $this->contentType = self::DEFAULT_CONTENT_TYPE;
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
        $res = json_decode($this->content, true);
        if (!is_array($res))
        {
            CoreVIRUS::logWarning("Unable to convert the request content-type to a proper json array.");
            $res = array();
        }
        return $res;
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

    public function getSegmentAsInt($indexOrKey, $default = false, $maxValue = PHP_INT_MAX)
    {
        if (empty($this->segments[$indexOrKey]))
            return $default;
        $str = $this->segments[$indexOrKey];
        return is_numeric($str) ? min(intval($str, 10), $maxValue) : $default;
    }

    public function getSegmentAsPositiveInt($indexOrKey, $default = false, $maxValue = PHP_INT_MAX)
    {
        if (empty($this->segments[$indexOrKey]))
            return $default;
        $val = intval($this->segments[$indexOrKey]);
        return $val > 0 ? min($val, $maxValue) : $default;
    }

    public function getRawSegment($indexOrKey, $default = false)
    {
        return !empty($this->rawParameters[$indexOrKey]) ? $this->rawParameters[$indexOrKey] : $default;
    }

    public function getRawSegmentAsInt($indexOrKey, $default = false, $maxValue = PHP_INT_MAX)
    {
        if (empty($this->rawParameters[$indexOrKey]))
            return $default;
        $str = $this->rawParameters[$indexOrKey];
        return is_numeric($str) ? min(intval($str, 10), $maxValue) : $default;
    }

    public function getRawSegmentAsPositiveInt($indexOrKey, $default = false, $maxValue = PHP_INT_MAX)
    {
        if (empty($this->rawParameters[$indexOrKey]))
            return $default;
        $val = intval($this->rawParameters[$indexOrKey]);
        return $val > 0 ? min($val, $maxValue) : $default;
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

    private $jsonArrayCache = null;

    /**
     * Obtain the value of the POST paramenter with the key $key, returning a default value if not found.
     * The data is obtained from the content of the HTTP request. 
     * 
     * This function can handle xml and json content types. In case of XML content, it searches recursively through the
     *  XML structure until it finds the required element (<$key>value</$key>) or the end of the structure. Incase of
     *  json content, it searches for the value with a max depth of 2. In both cases, if the key is repeated, only the
     *  first found value is returned.
     * 
     * The returned value is converted to a proper data type if possible.
     * @param string $key the key to obtain from a 
     * @param mixed $default optional return value if the key is not found. Default is null.
     * @return string|boolean|int|float|mixed
     */
    public function getPostParameter($key, $default = null)
    {
        
        switch ($this->getContentType())
        {
            
            case 'json':
                //obtain and cache the result in a property for performance on multiple calls of getPostParameter()
                $arr = $this->jsonArrayCache !== null ? $this->jsonArrayCache : ($this->jsonArrayCache = $this->getContentAsJsonArray());
                if (array_key_exists($key, $arr))
                {
                    return $arr[$key];
                } else
                {
                    while (list($k, $v) = each($array))
                    {
                        if (is_array($v))
                        {
                            $result = array_key_exists($key, $v) ? $v[$key] : $default;
                            return $this->convert_type($result);
                        }
                        break; //we only wanna check the first array elem.
                    }
                    return $this->convert_type($default);
                }
                break;
            default: //xml
                return $this->convert_type($this->getContentFirstXmlTag($key, $default));
        }
    }

    public function getDebugString()
    {
        return $this->method . ' ' . URI::getInstance()->getURIString() . " ContentType: $this->resultType ResultType: $this->resultType";
    }

}
