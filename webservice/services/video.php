<?php

namespace VIRUS\webservice\services;

function videoWebservice(\Slim\Slim $webservice, $baseRoute)
{
    $logger = \VIRUS\webservice\VIRUS::getLogger();
    $webservice->get($baseRoute . "/", function() use($webservice, $logger)
    {
        
    });
    
    
    $webservice->get($baseRoute . "/", function() use($webservice, $logger)
    {
        
    });
    
}
