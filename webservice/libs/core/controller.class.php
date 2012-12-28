<?php

namespace VIRUS\webservice\controllers;

if(!defined("VIRUS")){
    die("You are not allowed here!");    
}

abstract class Controller
{

    abstract function __construct();

    abstract function _remap($resource, array $segments);

    abstract function _default();
}

