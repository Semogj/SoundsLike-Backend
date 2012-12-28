<?php

namespace VIRUS\webservice;

if (!defined("VIRUS"))
{
    die("You are not allowed here!");
}

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * This class is used to filter and segment the URI.
 * This is an adapted version of the CodeIgniter's Core URI class.
 *
 * @author semogj
 */
class URI
{

    private static $instance;

    /**
     * 
     * @return URI
     */
    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new URI();
        return self::$instance;
    }

    private $uriString;
    private $segments;

    protected function __construct()
    {
        $this->uriString = self::_fetch_uri_string();
        $this->segments = self::_explode_segments($this->uriString);
    }

    const PERMITTED_URI_CHARS = 'a-z 0-9~%.:_\-;={}';
    const URI_PROTOCOL = 'AUTO';

    public function getURIString()
    {
        return $this->uriString;
    }

    public function getSegmentArray($leftSegmentsToExclude = 0)
    {
        if ($leftSegmentsToExclude == 0)
            return $this->segments;
        return array_slice($this->segments, $leftSegmentsToExclude);
    }

    public function getSegment($index, $default = FALSE)
    {
        return isset($this->segments[$index]) ? $this->segments[$index] : $default;
    }

    public function getSegmentCount()
    {
        return count($this->segments);
    }

    private function _remap($service, $params = array(), $version = '1')
    {
        $request = new WebserviceRequest($service, $params);
        $response = null;
        //var_dump($resource);
        if (file_exists(ROOT_DIRECTORY . "models/apiv1/{$resource}model.php"))
        {
            $this->load->model('apiv1/' . $resource . 'model', $resource . 'Model');
            $response = $this->{$resource . 'Model'}->processRequest($request);
        } else
        {
            $errorMsg = "These aren't the droids you are looking for! Invalid webservice resource '$resource'.";
            log_message('info', $errorMsg);
            $response = new ErrorWebserviceResponse(WebserviceResponse::$ERR_INVALID_RESOURCE, $errorMsg);
        }
        $this->load->view('APIv1/apiv1result_view', array('response' => $response));
    }

    private static function _fetch_uri_string()
    {
        $getUriString = function ($str) {
                    // Filter out control characters
                    $str = remove_invisible_characters($str, FALSE);

                    // If the URI contains only a slash we'll kill it
                    return ($str == '/') ? '' : $str;
                };
        if (strtoupper(self::URI_PROTOCOL) == 'AUTO')
        {
            // Is the request coming from the command line?
            if (php_sapi_name() == 'cli' or defined('STDIN'))
            {
                return $getUriString(self::_parse_cli_args());
            }

            // Let's try the REQUEST_URI first, this will work in most situations
            if ($uri = self::_detect_uri())
            {
                return $getUriString($uri);
            }

            // Is there a PATH_INFO variable?
            // Note: some servers seem to have trouble with getenv() so we'll test it two ways
            $path = (isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : @getenv('PATH_INFO');
            if (trim($path, '/') != '' && $path != "/" . SELF)
            {
                return $getUriString($path);
            }

            // No PATH_INFO?... What about QUERY_STRING?
            $path = (isset($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'] : @getenv('QUERY_STRING');
            if (trim($path, '/') != '')
            {
                return $getUriString($path);
            }

            // As a last ditch effort lets try using the $_GET array
            if (is_array($_GET) && count($_GET) == 1 && trim(key($_GET), '/') != '')
            {
                return $getUriString(key($_GET));
            }

            // We've exhausted all our options...
            return '';
        }

        $uri = strtoupper(self::URI_PROTOCOL);

        if ($uri == 'REQUEST_URI')
        {
            return $getUriString(self::_detect_uri());
        } elseif ($uri == 'CLI')
        {
            return $getUriString(self::_parse_cli_args());
        }

        $path = (isset($_SERVER[$uri])) ? $_SERVER[$uri] : @getenv($uri);
        return $getUriString($path);
    }

    /**
     * Explode the URI Segments.
     *
     * @access	private
     * @return	void
     */
    private static function _explode_segments($uriString)
    {
        $segments = null;
        foreach (explode("/", preg_replace("|/*(.+?)/*$|", "\\1", $uriString)) as $val)
        {
            // Filter segments for security
            $val = trim(self::_filter_uri($val));

            if ($val != '')
            {
                $segments[] = $val;
            }
        }
        return $segments;
    }

    /**
     * Filter segments for malicious characters
     *
     * @access	private
     * @param	string
     * @return	string
     */
    private static function _filter_uri($str)
    {
        if ($str != '')
        {
            // preg_quote() in PHP 5.3 escapes -, so the str_replace() and addition of - to preg_quote() is to maintain backwards
            // compatibility as many are unaware of how characters in the permitted_uri_chars will be parsed as a regex pattern
            if (!preg_match("|^[" . str_replace(array('\\-', '\-'), '-', preg_quote(self::PERMITTED_URI_CHARS, '-')) . "]+$|i", $str))
            {
                throw new \Exception("The URI you submitted has disallowed characters.", HTML_400_BAD_REQUEST);
            }
        }

        // Convert programatic characters to entities
        $bad = array('$', '(', ')', '%28', '%29');
        $good = array('&#36;', '&#40;', '&#41;', '&#40;', '&#41;');

        return str_replace($bad, $good, $str);
    }

    /**
     * Parse cli arguments
     *
     * Take each command line argument and assume it is a URI segment.
     *
     * @access	private
     * @return	string
     */
    private static function _parse_cli_args()
    {
        $args = array_slice($_SERVER['argv'], 1);

        return $args ? '/' . implode('/', $args) : '';
    }

    /**
     * Detects the URI
     *
     * This function will detect the URI automatically and fix the query string
     * if necessary.
     *
     * @access	private
     * @return	string
     */
    private static function _detect_uri()
    {
        if (!isset($_SERVER['REQUEST_URI']) OR !isset($_SERVER['SCRIPT_NAME']))
        {
            return '';
        }

        $uri = $_SERVER['REQUEST_URI'];
        if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0)
        {
            $uri = substr($uri, strlen($_SERVER['SCRIPT_NAME']));
        } elseif (strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) === 0)
        {
            $uri = substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
        }

        // This section ensures that even on servers that require the URI to be in the query string (Nginx) a correct
        // URI is found, and also fixes the QUERY_STRING server var and $_GET array.
        if (strncmp($uri, '?/', 2) === 0)
        {
            $uri = substr($uri, 2);
        }
        $parts = preg_split('#\?#i', $uri, 2);
        $uri = $parts[0];
        if (isset($parts[1]))
        {
            $_SERVER['QUERY_STRING'] = $parts[1];
            parse_str($_SERVER['QUERY_STRING'], $_GET);
        } else
        {
            $_SERVER['QUERY_STRING'] = '';
            $_GET = array();
        }

        if ($uri == '/' || empty($uri))
        {
            return '/';
        }

        $uri = parse_url($uri, PHP_URL_PATH);

        // Do some final cleaning of the URI and return it
        return str_replace(array('//', '../'), '/', trim($uri, '/'));
    }

}

?>
