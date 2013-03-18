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

/**
 * ATTENTION: I have changed some things in this class.
 *  Updating the code to a newer version from the source may lead to unexpected results.
 */
/* Finally, A light, permissions-checking logging class. 
 *
 * 
 * Author	: Kenneth Katzgrau < katzgrau@gmail.com >
 * Date	: July 26, 2008
 * Comments	: Originally written for use with wpSearch
 * Website	: http://codefury.net
 * Version	: 1.0
 *
 * Usage: 
 * 		$log = new KLogger ( "log.txt" , KLogger::INFO );
 * 		$log->LogInfo("Returned a million search results");	//Prints to the log file
 * 		$log->LogFATAL("Oh dear.");				//Prints to the log file
 * 		$log->LogDebug("x = 5");					//Prints nothing due to priority setting
 */

class KLogger
{

    const ALL = 0; // Most Verbose
    const DEBUG_VERBOSE = 100;
    const DEBUG_DETAILED = 200;
    const DEBUG = 300; // ... 
    const INFO = 400; // ...
    const WARNING = 500; // ...
    const ERROR = 600; // ...
    const FATAL = 700; // Least Verbose
    const OFF = 1000; // Nothing at all.
    const LOG_OPEN = 1;
    const OPEN_FAILED = 2;
    const LOG_CLOSED = 3;
    const EMPTY_STR = "<NO MESSAGE>";

    /* Public members: Not so much of an example of encapsulation, but that's okay. */

    public $Log_Status = KLogger::LOG_CLOSED;
    public $DateFormat = "Y-m-d G:i:s";
    public $MessageQueue;
    public $includeIP = true;
    public $includeHostname = true;
    public $includeFileAndLine = true;
    private $log_file;
    private $priority = KLogger::INFO;
    private $file_handle;

    public function __construct($filepath, $priority, $disablePhpProtection = false)
    {
        $filepath = trim($filepath);
        if ($priority == KLogger::OFF)
            return;
        if (!$disablePhpProtection && !endsWith($filepath, ".php"))
        {
            $filepath .= '.php';
        }
        $this->log_file = $filepath;
        $this->MessageQueue = array();
        $this->priority = $priority;

        $fileExists = file_exists($this->log_file);
//        if ($fileExists)
//        {
//            if (!is_writable($this->log_file))
//            {
//                $this->Log_Status = KLogger::OPEN_FAILED;
//                $this->MessageQueue[] = "The file exists, but could not be opened for writing. Check that appropriate permissions have been set.";
//                return;
//            }
//        }

        if ($this->file_handle = fopen($this->log_file, "a"))
        {
            if (!$fileExists && !$disablePhpProtection)
            {
                fwrite($this->file_handle, "<?php die(); ?>\n\n#Log File Start\n");
                //In some linux configurations, the log file is owned by the http user (eg. www-data)
                //included in a user (eg. also www-data group). This will allow to any user in the
                //same group to edit the log file, and disallowing reading for public.
                chmod($this->log_file, 0760);
            }
            $this->Log_Status = KLogger::LOG_OPEN;
            $this->MessageQueue[] = "The log file was opened successfully.";
        } else
        {
            $this->Log_Status = KLogger::OPEN_FAILED;
            $this->MessageQueue[] = "The file could not be opened. Check permissions.";
        }

        return;
    }

    public function __destruct()
    {
        if ($this->file_handle)
            fclose($this->file_handle);
    }

    public function logInfo($str, array $stacktrace = null)
    {
        $this->log(KLogger::INFO, $str, $stacktrace ? $stacktrace : debug_backtrace());
    }

    public function logDebug($str, array $stacktrace = null)
    {
        $this->log(KLogger::DEBUG, $str, $stacktrace ? $stacktrace : debug_backtrace());
    }

    public function logWarning($str, array $stacktrace = null)
    {
        $this->log(KLogger::WARNING, $str, $stacktrace ? $stacktrace : debug_backtrace());
    }

    public function logError($str, array $stacktrace = null)
    {
        $this->log(KLogger::ERROR, $str, $stacktrace ? $stacktrace : debug_backtrace());
    }

    public function logFatal($str, array $stacktrace = null)
    {
        $this->log(KLogger::FATAL, $str, $stacktrace ? $stacktrace : debug_backtrace());
    }

    public function log($priority, $messageStr, array $stacktrace = null)
    {
        if ($this->includeFileAndLine)
        {
            if ($stacktrace === null)
                $stacktrace = debug_backtrace();
            $caller = array_shift($stacktrace);
            $this->_Log($priority, $messageStr, $caller['file'], $caller['line']);
        } else
        {
            $this->_Log($priority, $messageStr);
        }
    }

    public function aLog($priority, $messageStr, $file = null, $line = null)
    {
        if ($file === null && $this->includeFileAndLine)
        {
            $stacktrace = debug_backtrace();
            $caller = array_shift($stacktrace);
            $this->_Log($priority, $messageStr, $caller['file'], $caller['line']);
        } else
        {
            $this->_Log($priority, $messageStr, $file, $line);
        }
    }
    public function rawLog($priority, $str, $includeTimeline = false){
        if ($this->priority <= $priority)
        {
            $status = $includeTimeline ? $this->_getTimeLine($priority) : '';
            $this->_writeFreeFormLine("$status $str\n");
        }
    }

    private function _Log($priority, $str, $file = null, $line = null)
    {
        if(empty($str)){
            $str = self::EMPTY_STR;
        }
        $fileLine = '';
        if ($this->includeFileAndLine && !empty($file))
            $fileLine = (!empty($line)) ? (" In $file on line $line.") : (" In $file, line unknown.");
        if ($this->priority <= $priority)
        {
            $status = $this->_getTimeLine($priority);
            $this->_writeFreeFormLine("{$status} {$str}{$fileLine}\n");
        }
    }

    private function _writeFreeFormLine($line)
    {
        if ($this->Log_Status == KLogger::LOG_OPEN && $this->priority != KLogger::OFF)
        {
            if (fwrite($this->file_handle, $line) === false)
            {
                $this->MessageQueue[] = "The file could not be written to. Check that appropriate permissions have been set.";
            }
        }
    }

    private function _getTimeLine($level)
    {
        $time = date($this->DateFormat);
        switch ($level)
        {
            case self::INFO:
                $level = 'INFO';
                break;
            case self::WARNING:
                $level = 'WARNING';
                break;
            case self::DEBUG:
                $level = 'DEBUG';
                break;
            case self::ERROR:
                $level = 'ERROR';
                break;
            case self::FATAL:
                $level = 'FATAL';
                break;
            case self::DEBUG_VERBOSE:
                $level = 'DEBUG-VERBOSE';
                break;
            case self::DEBUG_DETAILED:
                $level = 'DEBUG-DETAILED';
                break;
            default:
                $level = 'LOG';
        }

        if ($this->includeIP)
        {
            $ip = ip_address();
            if ($this->includeHostname)
                $ip .= ' ' . gethostbyaddr($ip);
            return "$time - $ip - $level  -->";
        }

        return "$time - $level   -->";
    }

}
