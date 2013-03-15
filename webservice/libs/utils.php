<?php

if (!defined("VIRUS"))
{//prevent script direct access
    header('HTTP/1.1 404 Not Found');
    header("X-Powered-By: ");
    echo "<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">\n<html><head>\n<title>404 Not Found</title>\n</head>
          <body>\n<h1>Not Found</h1>\n<p>The requested URL " . $_SERVER['REQUEST_URI'] . " was not found on this server.</p>\n
          <hr>\n" . $_SERVER['SERVER_SIGNATURE'] . "\n</body></html>\n";
    die();
}

function getArrayAsXML(array $theArray, $previousKey = null)
{
    $result = '';
    if (is_array($theArray))
    {
        foreach ($theArray as $key => $value)
        {
            $sufix = '';
            $valueTmp = null;
            if (is_object($value) && $value instanceof WebserviceCollection)
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
    $text = null;
    switch ($code)
    {
        case 100: $text = '100 Continue';
            break;
        case 101: $text = '101 Switching Protocols';
            break;
        case 200: $text = '200 OK';
            break;
        case 201: $text = '201 Created';
            break;
        case 202: $text = '202 Accepted';
            break;
        case 203: $text = '203 Non-Authoritative Information';
            break;
        case 204: $text = '204 No Content';
            break;
        case 205: $text = '205 Reset Content';
            break;
        case 206: $text = '206 Partial Content';
            break;
        case 300: $text = '300 Multiple Choices';
            break;
        case 301: $text = '301 Moved Permanently';
            break;
        case 302: $text = '302 Moved Temporarily';
            break;
        case 303: $text = '303 See Other';
            break;
        case 304: $text = '304 Not Modified';
            break;
        case 305: $text = '305 Use Proxy';
            break;
        case 400: $text = '400 Bad Request';
            break;
        case 401: $text = '401 Unauthorized';
            break;
        case 402: $text = '402 Payment Required';
            break;
        case 403: $text = '403 Forbidden';
            break;
        case 404: $text = '404 Not Found';
            break;
        case 405: $text = '405 Method Not Allowed';
            break;
        case 406: $text = '406 Not Acceptable';
            break;
        case 407: $text = '407 Proxy Authentication Required';
            break;
        case 408: $text = '408 Request Time-out';
            break;
        case 409: $text = '409 Conflict';
            break;
        case 410: $text = '410 Gone';
            break;
        case 411: $text = '411 Length Required';
            break;
        case 412: $text = '412 Precondition Failed';
            break;
        case 413: $text = '413 Request Entity Too Large';
            break;
        case 414: $text = '414 Request-URI Too Large';
            break;
        case 415: $text = '415 Unsupported Media Type';
            break;
        case 500: $text = '500 Internal Server Error';
            break;
        case 501: $text = '501 Not Implemented';
            break;
        case 502: $text = '502 Bad Gateway';
            break;
        case 503: $text = '503 Service Unavailable';
            break;
        case 504: $text = '504 Gateway Time-out';
            break;
        case 505: $text = '505 HTTP Version not supported';
            break;
        case 511: $text = '511 Network Authentication Required';
            break;
        default:
            $text = "$code";
    }
    return $text;
}

/**
 * Remove Invisible Characters
 *
 * This prevents sandwiching null characters
 * between ascii characters, like Java\0script.
 *
 * @access	public
 * @param	string
 * @return	string
 */
if (!function_exists('remove_invisible_characters'))
{

    function remove_invisible_characters($str, $url_encoded = TRUE)
    {
        $non_displayables = array();

// every control character except newline (dec 10)
// carriage return (dec 13), and horizontal tab (dec 09)

        if ($url_encoded)
        {
            $non_displayables[] = '/%0[0-8bcef]/'; // url encoded 00-08, 11, 12, 14, 15
            $non_displayables[] = '/%1[0-9a-f]/'; // url encoded 16-31
        }

        $non_displayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S'; // 00-08, 11, 12, 14-31, 127

        do
        {
            $str = preg_replace($non_displayables, '', $str, -1, $count);
        } while ($count);

        return $str;
    }

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
                case 511: $text = 'Network Authentication Required';
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

/**
 * Fetch the IP Address
 * 
 * This is an adapted function from the core Input class of CodeIgniter PHP framework.
 * 
 * @param string|array $proxyIps  Reverse proxy ips: If your server is behind a 
 *  reverse proxy, you must whitelist the proxy IPaddresses from which CodeIgniter
 *  should trust the HTTP_X_FORWARDED_FOR header in order to properly identify the
 *  visitor's IP address. Comma-delimited, e.g. '10.0.1.200,10.0.1.201' or string array
 * 
 * @author ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license http://codeigniter.com/user_guide/license.html
 * @return string
 */
function ip_address($proxyIps = null)
{
    if (isset($GLOBALS['ip_address']))
    {
        return $GLOBALS['ip_address'];
    }
    $ip = null;
    if (!empty($proxyIps))
    {
        $proxyIps = is_array($proxyIps) ? $proxyIps : explode(',', str_replace(' ', '', $proxyIps));
        foreach (array('HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'HTTP_X_CLIENT_IP', 'HTTP_X_CLUSTER_CLIENT_IP') as $header)
        {
            if (($spoof = array_fetch_value($_SERVER, $header)) !== FALSE)
            {
// Some proxies typically list the whole chain of IP
// addresses through which the client has reached us.
// e.g. client_ip, proxy_ip1, proxy_ip2, etc.
                if (strpos($spoof, ',') !== FALSE)
                {
                    $spoof = explode(',', $spoof, 2);
                    $spoof = $spoof[0];
                }

                if (!valid_ip($spoof))
                {
                    $spoof = FALSE;
                } else
                {
                    break;
                }
            }
        }
        $ip = ($spoof !== FALSE && in_array($_SERVER['REMOTE_ADDR'], $proxyIps, TRUE)) ? $spoof : $_SERVER['REMOTE_ADDR'];
    } else
    {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    if (!valid_ip($ip))
    {
        $ip = '0.0.0.0';
    }
    $GLOBALS['ip_address'] = $ip;
    return $ip;
}

// --------------------------------------------------------------------

/**
 * Validate IP Address
 *
 * This is a function retrieved from the Input class of CodeIgniter PHP framework.
 * 
 * @author ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license http://codeigniter.com/user_guide/license.html
 * @param string $ip
 * @param string $which ipv4 or ipv6
 * @return boolean
 */
function valid_ip($ip, $which = '')
{

    $which = strtolower($which);

    // First check if filter_var is available
    if (is_callable('filter_var'))
    {
        switch ($which)
        {
            case 'ipv4':
                $flag = FILTER_FLAG_IPV4;
                break;
            case 'ipv6':
                $flag = FILTER_FLAG_IPV6;
                break;
            default:
                $flag = '';
                break;
        }

        return (bool) filter_var($ip, FILTER_VALIDATE_IP, $flag);
    }

    if ($which !== 'ipv6' && $which !== 'ipv4')
    {
        if (strpos($ip, ':') !== FALSE)
        {
            $which = 'ipv6';
        } elseif (strpos($ip, '.') !== FALSE)
        {
            $which = 'ipv4';
        } else
        {
            return FALSE;
        }
    }

    return ${'valid_' . $which}($ip);
}

function array_fetch_value(&$array, $index = '', $default = FALSE)
{
    arrayFetchValue($array, $index, $default);
}

function arrayFetchValue(&$array, $index = '', $default = FALSE)
{
    if (!isset($array[$index]))
    {
        return $default;
    }
    return $array[$index];
}

/**
 * Validate IPv4 Address
 *
 * Updated version suggested by Geert De Deckere
 *
 * @author ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license http://codeigniter.com/user_guide/license.html
 * @param	string
 * @return	bool
 */
function valid_ipv4($ip)
{
    $ip_segments = explode('.', $ip);

    // Always 4 segments needed
    if (count($ip_segments) !== 4)
    {
        return FALSE;
    }
    // IP can not start with 0
    if ($ip_segments[0][0] == '0')
    {
        return FALSE;
    }

    // Check each segment
    foreach ($ip_segments as $segment)
    {
        // IP segments must be digits and can not be
        // longer than 3 digits or greater then 255
        if ($segment == '' OR preg_match("/[^0-9]/", $segment) OR $segment > 255 OR strlen($segment) > 3)
        {
            return FALSE;
        }
    }

    return TRUE;
}

// --------------------------------------------------------------------

/**
 * Validate IPv6 Address
 *
 * @author ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license http://codeigniter.com/user_guide/license.html
 * @param	string
 * @return	bool
 */
function valid_ipv6($str)
{
// 8 groups, separated by :
// 0-ffff per group
// one set of consecutive 0 groups can be collapsed to ::

    $groups = 8;
    $collapsed = FALSE;

    $chunks = array_filter(
            preg_split('/(:{1,2})/', $str, NULL, PREG_SPLIT_DELIM_CAPTURE)
    );

    // Rule out easy nonsense
    if (current($chunks) == ':' OR end($chunks) == ':')
    {
        return FALSE;
    }

    // PHP supports IPv4-mapped IPv6 addresses, so we'll expect those as well
    if (strpos(end($chunks), '.') !== FALSE)
    {
        $ipv4 = array_pop($chunks);

        if (!$this->_valid_ipv4($ipv4))
        {
            return FALSE;
        }

        $groups--;
    }

    while ($seg = array_pop($chunks))
    {
        if ($seg[0] == ':')
        {
            if (--$groups == 0)
            {
                return FALSE; // too many groups
            }

            if (strlen($seg) > 2)
            {
                return FALSE; // long separator
            }

            if ($seg == '::')
            {
                if ($collapsed)
                {
                    return FALSE; // multiple collapsed
                }

                $collapsed = TRUE;
            }
        } elseif (preg_match("/[^0-9a-f]/i", $seg) OR strlen($seg) > 4)
        {
            return FALSE; // invalid segment
        }
    }

    return $collapsed OR $groups == 1;
}

/**
 * Tests if this string ends with the specified suffix.
 * @param type $haystack the string
 * @param type $needle the suffix
 * @param type $case case sensitive? DEFAULT=True
 * @return boolean true or false.
 */
function startsWith($haystack, $needle, $case = true)
{
    if ($case)
        return strpos($haystack, $needle, 0) === 0;

    return stripos($haystack, $needle, 0) === 0;
}

/**
 * Tests if this string starts with the specified prefix.
 * @param type $haystack the string
 * @param type $needle the prefix
 * @param type $case case sensitive? DEFAULT=True
 * @return boolean true or false.
 */
function endsWith($haystack, $needle, $case = true)
{
    $expectedPosition = strlen($haystack) - strlen($needle);

    if ($case)
        return strrpos($haystack, $needle, 0) === $expectedPosition;

    return strripos($haystack, $needle, 0) === $expectedPosition;
}

/**
 * Returns true if the current php version is compatible (bigger) with the $version parameter.
 * Verifies and caches the verification for quick access. 
 * @param string $version
 * @return boolean
 */
function isPHPVersion($version)
{
    $version = 'isPHPVersion' . $version;
    if (isset($GLOBALS[$version]))
        return $GLOBALS[$version];
    return $GLOBALS[$version] = version_compare(PHP_VERSION, $version, '<=');
}

/**
 * Check if the parsed value is a valid positive integer, and returns a default value if not.
 * The value 0 is counted as invalid. 
 * @param type $theValue
 * @param type $theDefault
 * @param type $base the integer base, default is base 10. 
 */
function validate_pos_int($theValue, $theDefault, $base = 10)
{
    $theValue = intval($theValue, $base);
    return $theValue > 0 ? $theValue : $theDefault;
}

/**
 * Checks if a string is a valid URL. Uses the RFC 3986 but does not validate relative paths.
 * 
 * @param string $theString The string to be validated.
 * @return boolean true or false. 
 */
function is_valid_url($theString)
{
    return preg_match(
            '/^
(# Scheme
 [a-z][a-z0-9+\-.]*:
 (# Authority & path
  \/\/
  ([a-z0-9\-._~%!$&\'()*+,;=]+@)?              # User
  ([a-z0-9\-._~%]+                            # Named host
  |\[[a-f0-9:.]+\]                            # IPv6 host
  |\[v[a-f0-9][a-z0-9\-._~%!$&\'()*+,;=:]+\])  # IPvFuture host
  (:[0-9]+)?                                  # Port
  (\/[a-z0-9\-._~%!$&\'()*+,;=:@]+)*\/?          # Path
 |# Path without authority
  (\/?[a-z0-9\-._~%!$&\'()*+,;=:@]+(\/[a-z0-9\-._~%!$&\'()*+,;=:@]+)*\/?)?
 )
)
# Query
(\?[a-z0-9\-._~%!$&\'()*+,;=:@\/?]*)?
# Fragment
(\#[a-z0-9\-._~%!$&\'()*+,;=:@\/?]*)?
$/ix', $theString);
}

if (!function_exists('singular'))
{

    /**
     * Singular
     * Takes a plural word and makes it singular
     * This is a function retrieved from the Inflector helper of CodeIgniter PHP framework.
     * 
     * @author ExpressionEngine Dev Team
     * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
     * @license http://codeigniter.com/user_guide/license.html
     * @param string $str
     * @return string
     */
    function singular($str)
    {
        $result = strval($str);

        $singular_rules = array(
            '/(matr)ices$/' => '\1ix',
            '/(vert|ind)ices$/' => '\1ex',
            '/^(ox)en/' => '\1',
            '/(alias)es$/' => '\1',
            '/([octop|vir])i$/' => '\1us',
            '/(cris|ax|test)es$/' => '\1is',
            '/(shoe)s$/' => '\1',
            '/(o)es$/' => '\1',
            '/(bus|campus)es$/' => '\1',
            '/([m|l])ice$/' => '\1ouse',
            '/(x|ch|ss|sh)es$/' => '\1',
            '/(m)ovies$/' => '\1\2ovie',
            '/(s)eries$/' => '\1\2eries',
            '/([^aeiouy]|qu)ies$/' => '\1y',
            '/([lr])ves$/' => '\1f',
            '/(tive)s$/' => '\1',
            '/(hive)s$/' => '\1',
            '/([^f])ves$/' => '\1fe',
            '/(^analy)ses$/' => '\1sis',
            '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/' => '\1\2sis',
            '/([ti])a$/' => '\1um',
            '/(p)eople$/' => '\1\2erson',
            '/(m)en$/' => '\1an',
            '/(s)tatuses$/' => '\1\2tatus',
            '/(c)hildren$/' => '\1\2hild',
            '/(n)ews$/' => '\1\2ews',
            '/([^u])s$/' => '\1',
        );

        foreach ($singular_rules as $rule => $replacement)
        {
            if (preg_match($rule, $result))
            {
                $result = preg_replace($rule, $replacement, $result);
                break;
            }
        }

        return $result;
    }

}


if (!function_exists('plural'))
{

    /**
     * Plural
     * Takes a singular word and makes it plural
     *
     * This is a function retrieved from the Inflector helper of CodeIgniter PHP framework.
     * 
     * @author ExpressionEngine Dev Team
     * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
     * @license http://codeigniter.com/user_guide/license.html
     * @param string $str The string to be "plural'ized"
     * @return string
     */
    function plural($str)
    {
        $result = strval($str);

        $plural_rules = array(
            '/^(ox)$/' => '\1\2en', // ox
            '/([m|l])ouse$/' => '\1ice', // mouse, louse
            '/(matr|vert|ind)ix|ex$/' => '\1ices', // matrix, vertex, index
            '/(x|ch|ss|sh)$/' => '\1es', // search, switch, fix, box, process, address
            '/([^aeiouy]|qu)y$/' => '\1ies', // query, ability, agency
            '/(hive)$/' => '\1s', // archive, hive
            '/(?:([^f])fe|([lr])f)$/' => '\1\2ves', // half, safe, wife
            '/sis$/' => 'ses', // basis, diagnosis
            '/([ti])um$/' => '\1a', // datum, medium
            '/(p)erson$/' => '\1eople', // person, salesperson
            '/(m)an$/' => '\1en', // man, woman, spokesman
            '/(c)hild$/' => '\1hildren', // child
            '/(buffal|tomat)o$/' => '\1\2oes', // buffalo, tomato
            '/(bu|campu)s$/' => '\1\2ses', // bus, campus
            '/(alias|status|virus)/' => '\1es', // alias
            '/(octop)us$/' => '\1i', // octopus
            '/(ax|cris|test)is$/' => '\1es', // axis, crisis
            '/s$/' => 's', // no change (compatibility)
            '/$/' => 's',
        );

        foreach ($plural_rules as $rule => $replacement)
        {
            if (preg_match($rule, $result))
            {
                $result = preg_replace($rule, $replacement, $result);
                break;
            }
        }

        return $result;
    }

}

/**
 * Returns the namespace part of a class name string that contains its namespace.
 * @param string $name the class name with (or without) a namepace.
 * @return string the namespace string, or empty if not found.
 */
function getClassNamespace($name)
{
    return substr($name, 0, strrpos($name, '\\'));
}

/**
 * Decomposes a classname into classname and namespace, returning an array or object with the two parts.
 * @param string $name the class name with (or without) a namepace.
 * @param boolean $asObj DEFAULT = FALSE return an object instead of an array. 
 * @return array|object the decomposed parts as array or object. The used keys are "classname" and "namespace".
 */
function parseClassname($name, $asObj = false)
{
    if ($asObj)
    {
        $ret = new stdClass;
        $ret->classname = substr(strrchr($name, '\\'), 1);
        $ret->namespace = substr($name, 0, strrpos($name, '\\'));
    } else
    {
        $ret = array(
            'classname' => substr(strrchr($name, '\\'), 1),
            'namespace' => substr($name, 0, strrpos($name, '\\'))
        );
    }
    return $ret;
}

/**
 * Returns the classname part of a class name string that contains its namespace.
 * @param string $name the class name with (or without) a namepace.
 * @return string the classname string, or empty if not found for some reason.
 */
function getClassName($name)
{
    return substr(strrchr($name, '\\'), 1);
}

function convert_type($var)
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

/**
 * Verifies if there are empty values in a array.
 * All values in the array are evaluated agains the empty() PHP function.
 * Returns false if the array is empty or null.
 * @param array $array
 * @return boolean
 */
function array_values_empty($array)
{
    if (empty($array))
        return false;
    while (list(, $value) = each($array))
    {
        if (empty($value))
        {
            return false;
        }
    }
    return true;
}