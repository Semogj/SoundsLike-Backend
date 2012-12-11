<?php

function getArrayAsXML(array $theArray, $previousKey = null)
{
    $result = '';
//    do_dump($theArray);
//    die();
    if (is_array($theArray))
    {
        foreach ($theArray as $key => $value)
        {
            $sufix = '';
            $valueTmp = null;
            if (is_object($value) && $value instanceof ResultResource)
            {
                $key = $value->resourceTag;
                $sufix = " count=\"$value->count\"";
                if ($value->page !== null)
                {
                    $sufix .= " page=\"$value->page\"";
                }
                if ($value->perPage !== null)
                {
                    $sufix .= " perPage=\"$value->perPage\"";
                }
                if ($value->total !== null)
                {
                    $sufix .= " total=\"$value->total\"";
                }
                if ($value->totalPages !== null)
                {
                    $sufix .= " totalPages=\"$value->totalPages\"";
                }
                $valueTmp = is_array($value->resulArray) ? getArrayAsXML($value->resulArray, $value->resourceTag) : $value = htmlspecialchars($value->resulArray, null, 'UTF-8');
                $key = plural($key);
                $result .= "<{$key}{$sufix}>$valueTmp</$key>";
            } elseif (is_array($value))
            {
                $key = is_numeric($key) && $previousKey != null ? $previousKey : $key;
                $sufix = ' nodesCount="' . count($value) . '"';
                $value = getArrayAsXML($value, $key);
                $result .= "<{$key}{$sufix}>$value</$key>";
            } else
            {
                $value = htmlspecialchars($value, null, 'UTF-8');
                if (is_numeric($key))
                    $result .= "<value_$key>$value</value_$key>";
                else
                    $result .= "<$key>$value</$key>";
            }
        }
    } else
    {
        $result .= htmlspecialchars($theArray, null, 'UTF-8');
    }
    return $result;
}

function getStatusCode($code)
{
    switch ($code)
    {
        case 200:
            return '200 OK';
        case 201:
            return '201 Created';
        case 202:
            return '202 Accepted';
        case 400:
            return '400 Bad Request';
        case 401:
            return '401 Unauthorized';
        case 403:
            return '403 Forbidden';
        case 404:
            return '404 Not Found';
        case 405:
            return '405 Method Not Allowed';
        case 406:
            return '406 Not Acceptable';
        case 500:
            return '500 Internal Server Error';
        case 501:
            return '501 Not Implemented';
        case 502:
            return '503 Service Unavailable';
        default:
            return "$code";
    }


    if (!function_exists('http_response_code'))
    {

        function http_response_code($code = NULL)
        {

            if ($code !== NULL)
            {

                switch ($code)
                {
                    case 100: $text = 'Continue';
                        break;
                    case 101: $text = 'Switching Protocols';
                        break;
                    case 200: $text = 'OK';
                        break;
                    case 201: $text = 'Created';
                        break;
                    case 202: $text = 'Accepted';
                        break;
                    case 203: $text = 'Non-Authoritative Information';
                        break;
                    case 204: $text = 'No Content';
                        break;
                    case 205: $text = 'Reset Content';
                        break;
                    case 206: $text = 'Partial Content';
                        break;
                    case 300: $text = 'Multiple Choices';
                        break;
                    case 301: $text = 'Moved Permanently';
                        break;
                    case 302: $text = 'Moved Temporarily';
                        break;
                    case 303: $text = 'See Other';
                        break;
                    case 304: $text = 'Not Modified';
                        break;
                    case 305: $text = 'Use Proxy';
                        break;
                    case 400: $text = 'Bad Request';
                        break;
                    case 401: $text = 'Unauthorized';
                        break;
                    case 402: $text = 'Payment Required';
                        break;
                    case 403: $text = 'Forbidden';
                        break;
                    case 404: $text = 'Not Found';
                        break;
                    case 405: $text = 'Method Not Allowed';
                        break;
                    case 406: $text = 'Not Acceptable';
                        break;
                    case 407: $text = 'Proxy Authentication Required';
                        break;
                    case 408: $text = 'Request Time-out';
                        break;
                    case 409: $text = 'Conflict';
                        break;
                    case 410: $text = 'Gone';
                        break;
                    case 411: $text = 'Length Required';
                        break;
                    case 412: $text = 'Precondition Failed';
                        break;
                    case 413: $text = 'Request Entity Too Large';
                        break;
                    case 414: $text = 'Request-URI Too Large';
                        break;
                    case 415: $text = 'Unsupported Media Type';
                        break;
                    case 500: $text = 'Internal Server Error';
                        break;
                    case 501: $text = 'Not Implemented';
                        break;
                    case 502: $text = 'Bad Gateway';
                        break;
                    case 503: $text = 'Service Unavailable';
                        break;
                    case 504: $text = 'Gateway Time-out';
                        break;
                    case 505: $text = 'HTTP Version not supported';
                        break;
                    default:
                        exit('Unknown http status code "' . htmlentities($code) . '"');
                        break;
                }

                $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');

                header($protocol . ' ' . $code . ' ' . $text);

                $GLOBALS['http_response_code'] = $code;
            } else
            {

                $code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);
            }

            return $code;
        }

    }
}
