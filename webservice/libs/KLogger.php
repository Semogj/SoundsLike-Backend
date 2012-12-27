<?php

/* Finally, A light, permissions-checking logging class. 
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

    const DEBUG = 1; // Most Verbose
    const INFO = 2; // ...
    const WARN = 3; // ...
    const ERROR = 4; // ...
    const FATAL = 5; // Least Verbose
    const OFF = 6; // Nothing at all.
    const LOG_OPEN = 1;
    const OPEN_FAILED = 2;
    const LOG_CLOSED = 3;

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

    public function LogInfo($line, array $stacktrace = null)
    {
        $this->_Log($line, KLogger::INFO, $stacktrace  ? $stacktrace : debug_backtrace());
    }

    public function LogDebug($line, array $stacktrace = null)
    {
        $this->_Log($line, KLogger::DEBUG, $stacktrace  ? $stacktrace : debug_backtrace());
    }

    public function LogWarn($line, array $stacktrace = null)
    {
        $this->_Log($line, KLogger::WARN, $stacktrace  ? $stacktrace : debug_backtrace());
    }

    public function LogError($line, array $stacktrace = null)
    {
        $this->_Log($line, KLogger::ERROR, $stacktrace  ? $stacktrace : debug_backtrace());
    }

    public function LogFatal($line, array $stacktrace = null)
    {
        $this->_Log($line, KLogger::FATAL, $stacktrace  ? $stacktrace : debug_backtrace());
    }

    public function Log($line, $priority, array $stacktrace = null)
    {
        $this->_Log($line, $priority, $stacktrace  ? $stacktrace : debug_backtrace());
    }

    private function _Log($line, $priority, array $stacktrace = null)
    {
        $fileLine = '';
        if ($this->includeFileAndLine && $stacktrace != null)
        {
            $caller = array_shift($stacktrace);
            $fileLine = ' In ' . $caller['file'] . ' on line ' . $caller['line'];
        }
        if ($this->priority <= $priority)
        {
            $status = $this->getTimeLine($priority);
            $this->WriteFreeFormLine("$status $line$fileLine\n");
        }
    }

    public function WriteFreeFormLine($line)
    {
        if ($this->Log_Status == KLogger::LOG_OPEN && $this->priority != KLogger::OFF)
        {
            if (fwrite($this->file_handle, $line) === false)
            {
                $this->MessageQueue[] = "The file could not be written to. Check that appropriate permissions have been set.";
            }
        }
    }

    private function getTimeLine($level)
    {
        $time = date($this->DateFormat);
        switch ($level)
        {
            case KLogger::INFO:
                $level = 'INFO';
                break;
            case KLogger::WARN:
                $level = 'WARN';
                break;
            case KLogger::DEBUG:
                $level = 'DEBUG';
                break;
            case KLogger::ERROR:
                $level = 'ERROR';
                break;
            case KLogger::FATAL:
                $level = 'FATAL';
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
