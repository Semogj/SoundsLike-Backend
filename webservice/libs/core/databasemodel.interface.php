<?php
namespace VIRUS\webservice\models;
use VIRUS\webservice\models\ModelFilter;

if (!defined("VIRUS"))
{//prevent script direct accessF
    header('HTTP/1.1 404 Not Found');
    header("X-Powered-By: ");
    echo "<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">\n<html><head>\n<title>404 Not Found</title>\n</head>
          <body>\n<h1>Not Found</h1>\n<p>The requested URL " . $_SERVER['REQUEST_URI'] . " was not found on this server.</p>\n
          <hr>\n" . $_SERVER['SERVER_SIGNATURE'] . "\n</body></html>\n";
    die();
}

require_once 'model.interface.php';

interface DatabaseModel extends Model
{

    public static function filter();

    public static function get($limit, $offsetPage);

    public static function getFiltered(ModelFilter $filter, $limit = API_DEFAULT_RESULT_LIMIT, $offsetPage = API_DEFAULT_RESULT_PAGE);

    public static function getSingle($id);
    
    public static function getCount(ModelFilter $filter = null);

    public static function createEntry($a);
    
    public static function updateEntry($id);
    
}