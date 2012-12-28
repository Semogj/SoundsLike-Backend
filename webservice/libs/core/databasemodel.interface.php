<?php
namespace VIRUS\webservice\models;
use VIRUS\webservice\models\ModelFilter;

if(!defined("VIRUS")){
    die("You are not allowed here!");    
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