<?php

namespace VIRUS\webservice\models;

if (!defined("VIRUS"))
{//prevent script direct accessF
    header('HTTP/1.1 404 Not Found');
    header("X-Powered-By: ");
    echo "<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">\n<html><head>\n<title>404 Not Found</title>\n</head>
          <body>\n<h1>Not Found</h1>\n<p>The requested URL " . $_SERVER['REQUEST_URI'] . " was not found on this server.</p>\n
          <hr>\n" . $_SERVER['SERVER_SIGNATURE'] . "\n</body></html>\n";
    die();
}

/**
 * Description of ModelFilter
 *
 * @author semogj
 */
abstract class ModelFilter
{

    private $conditions; //conditions query for prepared statements
    private $varArray; //conditions values for prepared statements
    //control variables
    private $AND_flag, $OR_FLAG;
    private $counter;

    const statementFieldIdentifier = ':';

    protected function __construct()
    {
        $this->conditions = '';
        $this->AND_flag = true;
        $this->OR_FLAG = false;
        $this->varArray = array();
        $this->counter = 0;
    }

    public function or_()
    {
        if (!empty($this->conditions))
        {
            if (!$this->AND_flag)
                $this->conditions = substr_replace($this->conditions, "OR ", strlen($this->conditions) - 4);
            elseif (!$this->OR_FLAG)
                $this->conditions .= 'OR ';
            $this->OR_FLAG = true;
            $this->AND_flag = false;
        }
        return $this;
    }

    public function and_()
    {
        if (!empty($this->conditions))
        {
            if (!$this->OR_flag)
                $this->conditions = substr_replace($this->conditions, "AND ", strlen($this->conditions) - 4);
            elseif (!$this->AND_FLAG)
                $this->conditions .= 'AND ';
            $this->AND_FLAG = true;
            $this->OR_flag = false;
        }
        return $this;
    }

    protected function appendRawQuery($str)
    {
        if ($this->AND_flag)
        {
            $this->and_();
        } else
        {
            $this->or_();
        }
        $this->conditions .= trim($str) . ' ';
    }

    protected function appendQuery($field, $value, $conditional = '=')
    {
        if ($this->AND_flag)
        {
            $this->and_();
        } else
        {
            $this->or_();
        }
        $field = trim($field);
        $USF = $field . $this->counter++; //UniqueStatementField
        $this->conditions = $field . ' ' . $conditional . ' ' . self::statementFieldIdentifier . $USF . ' ';
        $this->varArray[$USF] = "$value";
    }

    public function getStatementQuery()
    {
        return $this->conditions;
    }

    public function getVarArray()
    {
        return $this->varArray;
    }
    public function isEmpty(){
        return count($this->varArray) === 0;
    }

}

