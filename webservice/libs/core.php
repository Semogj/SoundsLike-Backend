<?php

namespace VIRUS\webservice;

if(!defined("VIRUS")){
    die("You are not allowed here!");    
}

require_once 'core/corevirus.class.php';
require_once 'core/controller.class.php';
require_once 'core/databasemodel.interface.php';
require_once 'core/model.interface.php';
require_once 'core/errorwebserviceresponse.class.php';
require_once 'core/modelfilter.class.php';
require_once 'core/okwebserviceresponse.class.php';
require_once 'core/viewdata.class.php';
require_once 'core/webservicecollection.class.php';
require_once 'core/webservicerequest.class.php';
require_once 'core/webserviceresponse.class.php';
require_once 'core/webserviceservice.class.php';
require_once 'core/uri.class.php';
require_once 'utils.php';
require_once 'KLogger.php';
