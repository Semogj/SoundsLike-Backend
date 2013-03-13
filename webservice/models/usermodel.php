<?php

namespace VIRUS\webservice\models;

use \PDO;
use VIRUS\webservice\CoreVIRUS;
use VIRUS\webservice\models\ModelFilter;

class UserModel implements DatabaseModel
{
    public static function filter()
    {
        return new UserFilter();
    }

    public static function get($limit, $offsetPage)
    {
        $limit = validate_pos_int($limit, API_DEFAULT_RESULT_LIMIT);
        $offsetPage = (validate_pos_int($offsetPage, API_DEFAULT_RESULT_PAGE) - 1) * $limit; //offset

        /* @var $db \PDO */
        $db = CoreVIRUS::getDb();
        $result = $db->query("SELECT * FROM User LIMIT $offsetPage, $limit");

        return $result ? $result->fetchAll(PDO::FETCH_ASSOC) : false;
    }

    public static function getFiltered(ModelFilter $filter, $limit = API_DEFAULT_RESULT_LIMIT,
                                       $offsetPage = API_DEFAULT_RESULT_PAGE)
    {
        $limit = validate_pos_int($limit, API_DEFAULT_RESULT_LIMIT);
        $offsetPage = (validate_pos_int($offsetPage, API_DEFAULT_RESULT_PAGE) - 1) * $limit; //offset

        /* @var $db \PDO */
        $db = CoreVIRUS::getDb();
        $where = $filter->getStatementQuery();
        $statement = $db->prepare("SELECT *  FROM User WHERE $where LIMIT $offsetPage, $limit");
        if (!$statement->execute($filter->getVarArray()))
            return false;
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getSingle($id)
    {
        $id = validate_pos_int($id, -1);
        /* @var $db \PDO */
        $db = CoreVIRUS::getDb();
        $result = $db->query("SELECT * FROM User WHERE idUser = '$id' LIMIT 1");
        return $result ? $result->fetchAll(PDO::FETCH_ASSOC) : array();
    }

    public static function createEntry($username, $password = NULL, $email = NULL)
    {
        $logger = CoreVIRUS::getLogger();
        $fields = array();
        if (empty($username))
        {
            $logger->logError('User\'s username should not be empty on UserModel::createEntry(), file ' . __FILE__ . ' line ' . __LINE__ . '.');
            return false;
        }
        $fields['username'] = trim($username);
        $db = CoreVIRUS::getDb();

        //check if username exists
        $statement = $db->prepare("SELECT idUser FROM User WHERE username = ?");
        $statement->execute(array($username));
        if ($statement->rowCount() > 0)
        {
            $logger->logWarning('User\'s username should not be empty on UserModel::createEntry(), file ' . __FILE__ . ' line ' . __LINE__ . '.');
            return false;
        }

        $x = function ($s) { //For making "?,?,?,?", depending on the number of available fields to insert
                    return $s == 0 ? '' : '?' + str_repeat(',?', $s - 1);
                };
        //insert into user
        $query = 'INSERT INTO User (' . implode(', ', array_keys($fields)) . ') VALUES (' . $x(count($fields)) . ')';
        $statement = $db->prepare($query);
        $statement->execute(array_values($fields));
        if($statement->rowCount() > 0) {
            $insertedId = $db->lastInsertId();
            $logger->logWarning("A new user has been inserted successfully on the database with id $insertedId and username '$username'" .
                    ' UserModel::createEntry(), file ' . __FILE__ . ' line ' . __LINE__ . '.');
            return $insertedId;
        } else {
            $logger->logError("Unknown database error while inserting a new user with the username '$username' "
            .'on UserModel::createEntry(),  file ' . __FILE__ . ' line ' . __LINE__ . '.');
        }
        return false;
    }

    public static function getCount(ModelFilter $filter = NULL)
    {

        $db = CoreVIRUS::getDb();
        if (isset($filter))
        {
            $result = $db->query('SELECT COUNT(*) FROM USER');
            if (!$result || !($result = $result->fetch(PDO::FETCH_NUM)))
                return false;
            return intval($result[0], 10);
        }else
        {
            $where = $filter->getStatementQuery();
            $statement = $db->prepare("SELECT count(*)  FROM User WHERE $where");
            if (!$statement->execute($filter->getVarArray()))
                return false;
            $result = null;
            if (!($result = $result->fetch(PDO::FETCH_NUM)))
                return false;
            return intval($result[0], 10);
        }
    }

    public static function updateEntry($id)
    {
        return false;
    }

}

class UserFilter extends ModelFilter
{

    const FIELD_USER_USERNAME = "username";
    const FIELD_USER_ID = "idUser";



    public function __construct()
    {
        parent::__construct();
    }

    public function byId($id)
    {
        $this->appendQuery(self::FIELD_USER_ID, $id);
        return $this;
    }

    public function byIdRange($lower, $higher)
    {
        $this->appendQuery(self::FIELD_USER_ID, $lower, '>=')->and_()->appendQuery(self::FIELD_USER_ID, $higher, '<=');
        return $this;
    }


    public function byUsername($username, $like = false)
    {
        if ($like)
            $this->appendQuery(self::FIELD_USER_USERNAME, $username, 'LIKE');
        else
            $this->appendQuery(self::FIELD_USER_USERNAME, $username);
        return $this;
    }
}