<?php

namespace VIRUS\webservice;

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
 * As you can see, here we load all the core classes.
 */
require_once 'core/corevirus.class.php';
require_once 'core/controller.class.php';
require_once 'core/databasemodel.interface.php';
require_once 'core/model.interface.php';
require_once 'core/modelfilter.class.php';
require_once 'core/viewdata.class.php';
require_once 'core/webservicecollection.class.php';
require_once 'core/webservicerequest.class.php';
require_once 'core/webserviceresponse.class.php';
require_once 'core/webserviceerrorresponse.class.php';
require_once 'core/webserviceokresponse.class.php';
require_once 'core/webserviceservice.class.php';
require_once 'core/uri.class.php';
require_once 'utils.php';
require_once 'KLogger.php';
require_once 'xml2json/xml2json.php';
