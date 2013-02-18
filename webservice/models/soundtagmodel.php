<?php

namespace VIRUS\webservice\models;

use \PDO;
use VIRUS\webservice\CoreVIRUS;
use VIRUS\webservice\models\ModelFilter;

class SoundTagModel implements DatabaseModel
{

    public static function filter()
    {
        return new SoundTagFilter();
    }

    public static function get($limit, $offsetPage)
    {
        $limit = validate_pos_int($limit, API_DEFAULT_RESULT_LIMIT);
        $offsetPage = (validate_pos_int($offsetPage, API_DEFAULT_RESULT_PAGE) - 1) * $limit; //offset

        /* @var $db \PDO */
        $db = CoreVIRUS::getDb();
        $result = $db->query("SELECT * FROM SoundTag LIMIT $offsetPage, $limit");

        return $result ? $result->fetchAll(PDO::FETCH_ASSOC) : false;
    }

    public static function getFiltered(ModelFilter $filter, $limit = API_DEFAULT_RESULT_LIMIT, $offsetPage = API_DEFAULT_RESULT_PAGE)
    {
        $limit = validate_pos_int($limit, API_DEFAULT_RESULT_LIMIT);
        $offsetPage = (validate_pos_int($offsetPage, API_DEFAULT_RESULT_PAGE) - 1) * $limit; //offset

        /* @var $db \PDO */
        $db = CoreVIRUS::getDb();
        $where = $filter->getStatementQuery();
        $statement = $db->prepare("SELECT *  FROM SoundTag WHERE $where LIMIT $offsetPage, $limit");
        if (!$statement->execute($filter->getVarArray()))
            return false;
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getSingle($id)
    {
        $id = validate_pos_int($id, -1);
        /* @var $db \PDO */
        $db = CoreVIRUS::getDb();
        $result = $db->query("SELECT * FROM SoundTag WHERE idSoundTag = '$id' LIMIT 1");
        return $result ? $result->fetchAll(PDO::FETCH_ASSOC) : array();
    }

    public static function createEntry($userId, $soundSegmentId = null, $tagName = null, $type = NULL)
    {
        $logger = CoreVIRUS::getLogger();
        $fields = array();
        $userId = intval($userId, 10);
        //check if userId is valid (exists in the database)
        if (count(UserModel::getSingle($userId)) === 0)
        {

            $logger->LogWarn("Invalid userId '$userId' in SoundTagModel::createEntry(), file " . __FILE__ . ' line ' . __LINE__ . '.');
            return false;
        }
        $fields['userId'] = $userId;
        $soundSegmentId = intval($soundSegmentId, 10);
        //check if soundSegmentId is valid (exists in the database)
        if (count(UserModel::getSingle($soundSegmentId)) === 0)
        {
            $logger->LogWarn("Invalid soundSegmentId '$soundSegmentId' in SoundTagModel::createEntry(), file " . __FILE__ . ' line ' . __LINE__ . '.');
            return false;
        }
        $fields['soundSegmentId'] = $soundSegmentId;

        if (empty($tagName))
        {
            $logger->LogWarn("The tagname cannot be empty, in SoundTagModel::createEntry(), file " . __FILE__ . ' line ' . __LINE__ . '.');
            return false;
        }
        $fields['tagName'] = trim($tagName);

        if (!empty($type))
        {
            $fields['type'] = trim($type);
        }

        $db = CoreVIRUS::getDb();
        //insert into user
        $query = 'INSERT INTO SoundTag (' . implode(', ', array_keys($fields)) . ') VALUES (' . $x(count($fields)) . ')';
        $statement = $db->prepare($query);
        $statement->execute(array_values($fields));
        if ($statement->rowCount() > 0)
        {
            $insertedId = $db->lastInsertId();
            $logger->LogWarn("A new soundtag has been inserted successfully on the database with id $insertedId'" .
                    ' SoundTagModel::createEntry(), file ' . __FILE__ . ' line ' . __LINE__ . '.');
            return $insertedId;
        } else
        {
            $logger->LogError("Database error while inserting a new soundTag. The soundTag may already exist! " .
                    'On SoundTagModel::createEntry(),  file ' . __FILE__ . ' line ' . __LINE__ . '.');
        }
        return false;
    }

    public static function getUserTags($userId, $limit = API_DEFAULT_RESULT_LIMIT, $offsetPage = API_DEFAULT_RESULT_PAGE)
    {
        $limit = validate_pos_int($limit, API_DEFAULT_RESULT_LIMIT);
        $offsetPage = (validate_pos_int($offsetPage, API_DEFAULT_RESULT_PAGE) - 1) * $limit; //offset
        $userId = intval($userId, 10);
        $sql = 'SELECT idSoundTag, tagName, type, soundSegmentId, userId, username 
                FROM SoundTag, User
                WHERE  userId = :uid
                AND SoundTag.userId = User.idUser
                LIMIT :offset, :limit';
        $db = CoreVIRUS::getDb();
        $statement = $db->prepare($sql);
        $statement->bindValue(':uid', $userId, PDO::PARAM_INT);
        $statement->bindValue(':offset', $userId, PDO::PARAM_INT);
        $statement->bindValue(':limit', $userId, PDO::PARAM_INT);
        if (!$statement->execute())
        {
            CoreVIRUS::getLogger()->LogError('Unknown database error while executing the statement' .
                    ',in SoundTagModel::getUserTags(), file ' . __FILE__ . ' line ' . __LINE__ . '.');
            return array();
        }
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAudioSegmentTags($audioSegmentId, $limit = API_DEFAULT_RESULT_LIMIT, $offsetPage = API_DEFAULT_RESULT_PAGE)
    {
        $audioSegmentId = intval($audioSegmentId, 10);
        self::getFiltered(self::filter()->bySoundSegmentId($audioSegmentId), $limit, $offsetPage);
    }

    public static function getAudioSegmentWeightedTags($audioSegmentId, $limit = API_DEFAULT_RESULT_LIMIT, $offsetPage = API_DEFAULT_RESULT_PAGE)
    {
        $limit = validate_pos_int($limit, API_DEFAULT_RESULT_LIMIT);
        $offsetPage = (validate_pos_int($offsetPage, API_DEFAULT_RESULT_PAGE) - 1) * $limit; //offset
        $sql = 'SELECT tagName, count(tagname) as weight, soundSegmentId
                FROM SoundTag
                WHERE soundSegmentId = :sid
                GROUP BY tagName
                ORDER BY weight DESC
                LIMIT :offset, :limit';
        $db = CoreVIRUS::getDb();
        $statement = $db->prepare($sql);
        $statement->bindValue(':sid', $audioSegmentId, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offsetPage, PDO::PARAM_INT);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        if (!$statement->execute())
        {
            CoreVIRUS::getLogger()->LogError('Unknown database error while executing the statement' .
                    ',in SoundTagModel::getAudioSegmentWeightedTags(), file ' . __FILE__ . ' line ' . __LINE__ . '.');
            return array();
        }
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getUserTagsInAudioSegment($userId, $audioSegmentId, $limit = API_DEFAULT_RESULT_LIMIT, $offsetPage = API_DEFAULT_RESULT_PAGE)
    {
        $limit = validate_pos_int($limit, API_DEFAULT_RESULT_LIMIT);
        $offsetPage = (validate_pos_int($offsetPage, API_DEFAULT_RESULT_PAGE) - 1) * $limit; //offset
        $userId = intval($userId, 10);
        $sql = 'SELECT idSoundTag, tagName, type, soundSegmentId, userId, username 
                FROM SoundTag, User
                WHERE  userId = :uid
                AND soundSegmentId = :sid
                AND SoundTag.userId = User.idUser
                LIMIT :offset, :limit';
        $db = CoreVIRUS::getDb();
        $statement = $db->prepare($sql);
        $statement->bindValue(':uid', $userId, PDO::PARAM_INT);
        $statement->bindValue(':sid', $audioSegmentId, PDO::PARAM_INT);
        $statement->bindValue(':offset', $userId, PDO::PARAM_INT);
        $statement->bindValue(':limit', $userId, PDO::PARAM_INT);
        if (!$statement->execute())
        {
            CoreVIRUS::getLogger()->LogError('Unknown database error while executing the statement' .
                    ',in SoundTagModel::getUserTagsInAudioSegment(), file ' . __FILE__ . ' line ' . __LINE__ . '.');
            return array();
        }
        return $statement->fetchAll(PDO::FETCH_ASSOC);
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

class SoundTagFilter extends ModelFilter
{

    const FIELD_SOUNDTAG_ID = 'idSoundTag';
    const FIELD_SOUNDTAG_NAME = 'tagName';
    const FIELD_SOUNDTAG_TYPE = 'type';
    const FIELD_SOUNDTAG_USER_ID = 'userId';
    const FIELD_SOUNDTAG_SOUNDSEGMENT_ID = 'soundSegmentId';

    public function __construct()
    {
        parent::__construct();
    }

    public function byId($id)
    {
        $this->appendQuery(self::FIELD_SOUNDTAG_ID, $id);
        return $this;
    }

    public function byIdRange($lower, $higher)
    {
        $this->appendQuery(self::FIELD_SOUNDTAG_ID, $lower, '>=')->and_()->appendQuery(self::FIELD_SOUNDTAG_ID, $higher, '<=');
        return $this;
    }

    public function byTagName($tagname, $like = false)
    {
        if ($like)
            $this->appendQuery(self::FIELD_USER_USERNAME, $tagname, 'LIKE');
        else
            $this->appendQuery(self::FIELD_USER_USERNAME, $tagname);
        return $this;
    }

    public function byTagType($type, $like = false)
    {
        if ($like)
            $this->appendQuery(self::FIELD_SOUNDTAG_TYPE, $type, 'LIKE');
        else
            $this->appendQuery(self::FIELD_SOUNDTAG_TYPE, $type);
        return $this;
    }

    public function byUserId($userId)
    {
        $this->appendQuery(self::FIELD_SOUNDTAG_USER_ID, $userId);
        return $this;
    }

    public function bySoundSegmentId($userId)
    {
        $this->appendQuery(self::FIELD_SOUNDTAG_SOUNDSEGMENT_ID, $userId);
        return $this;
    }

}