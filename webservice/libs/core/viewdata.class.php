<?php

namespace VIRUS\webservice;

if(!defined("VIRUS")){
    die("You are not allowed here!");    
}

class ViewData
{

    private $array;

    public function __construct(array $data = array())
    {
        $this->array = $data;
    }

    public function get($key, $default = null)
    {
        return isset($this->array[$key]) ? $this->array[$key] : $default;
    }

//    public function getInt($key, $default){
//        
//    }
//    public function getPositiveInt($key, $default){
//        
//    }
//    public function getFloat($key, $default){
//        
//    }
//    public function getString($key, $default){
//        
//    }
//    public function __get($name)
//    {
//        echo "Getting '$name'\n";
//        if (array_key_exists($name, $this->data))
//        {
//            return $this->data[$name];
//        }
//
//        $trace = debug_backtrace();
//        trigger_error(
//                'Undefined property via __get(): ' . $name .
//                ' in ' . $trace[0]['file'] .
//                ' on line ' . $trace[0]['line'], E_USER_NOTICE);
//        return null;
//    }
//
//    public function __call($name, $arguments)
//    {
//        // Note: value of $name is case sensitive.
//        echo "Calling object method '$name' "
//        . implode(', ', $arguments) . "\n";
//    }
}

