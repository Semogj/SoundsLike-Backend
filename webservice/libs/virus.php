<?php

namespace VIRUS\webservice;

/**
 * Class for global database access,
 * (I could do it simply by global variable, but I dislike that way!)
 *
 * @author semogj
 */
class VIRUS
{
    public static $dbArray = array();
    
    /**
     * @return \PDO the db
     */
    public static function getDb($dbIdentifier = "DEFAULT"){
        if(key_exists($dbIdentifier, self::$dbArray)){
            return self::$dbArray[$dbIdentifier];
        }
        return null;
    }
    public static function registerDB(\PDO $db, $dbIdentifier = "DEFAULT"){
        self::$dbArray[$dbIdentifier] = $db;
    }
    
    public static function getLogger(){
        return getLogger();
    }
    
    public static function loadModel($model){
        $model = strtolower(trim($model));
        if(includeSafe("models/{$model}model.php")){
            self::getLogger()->LogFatal("Unable to include '$model' model");
        }
    }
}

