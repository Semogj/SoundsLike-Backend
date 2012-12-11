<?php

namespace VIRUS\webservice\models;

use \PDO;

user \VIRUS\webservice\getLogger();

class VideoFilter extends ModelFilter
{

    const FIELD_VIDEO_ID = "idVideo";
    const FIELD_TEXT_ID = "textId";
    const FIELD_TITLE = 'title';
    const FIELD_GENRES = 'genres';
    const FIELD_ACTORS = 'actors';
    const FIELD_YEAR = 'year';

    public function __construct()
    {
        parent::__construct();
    }

    public function byId($id)
    {
        $this->appendQuery(self::FIELD_VIDEO_ID, $id);
        return $this;
    }

    public function byIdRange($lower, $higher)
    {
        $this->appendQuery(self::FIELD_VIDEO_ID, $lower, '>=')->and_()->appendQuery(self::FIELD_VIDEO_ID, $higher, '<=');
        return $this;
    }

    public function byTextualId($textId)
    {
        $this->appendQuery(self::FIELD_TEXT_ID, $textId);
        return $this;
    }

    public function byTitle($title, $like = false)
    {
        if ($like)
            $this->appendQuery(self::FIELD_TITLE, $title, 'LIKE');
        else
            $this->appendQuery(self::FIELD_TITLE, $title);
        return $this;
    }

    public function byGenre($genresPattern)
    {
        $this->appendQuery(self::FIELD_GENRES, $genresPattern, 'LIKE');
        return $this;
    }

    public function byActor($actorsPattern)
    {
        $this->appendQuery(self::FIELD_ACTORS, $actorsPattern, 'LIKE');
        return $this;
    }

    public function byYear($year)
    {
        $this->appendQuery(self::FIELD_YEAR, $year);
        return $this;
    }

    public function byYearRange($lower, $higher)
    {
        $this->appendQuery(self::FIELD_YEAR, $lower, '>=')->and_()->appendQuery(self::FIELD_YEAR, $higher, '<=');
        return $this;
    }

}

use VIRUS\webservice\VIRUS;

class VideoModel implements Model
{

    const FIELD_VIDEO_ID = "idVideo";
    const FIELD_TEXT_ID = "textId";
    const FIELD_TITLE = 'title';
    const FIELD_GENRES = 'genres';
    const FIELD_ACTORS = 'actors';
    const FIELD_YEAR = 'year';
    const TABLE_VIDEO = "Video";

    private static $filter;

    public static function filter()
    {
        return new VideoFilter;
    }

    public static function get($limit, $offsetPage)
    {
        $limit = validate_pos_int($limit, API_DEFAULT_RESULT_LIMIT);
        $offsetPage = (validate_pos_int($offsetPage, API_DEFAULT_RESULT_PAGE) - 1) * $limit; //offset

        /* @var $db \PDO */
        $db = VIRUS::getDb();
        $result = $db->query('SELECT *  FROM ' . self::TABLE_VIDEO . " LIMIT $offset, $limit");

        return $result ? $result->fetchAll(PDO::FETCH_ASSOC) : false;
    }

    public static function getFiltered(ModelFilter $filter, $limit = API_DEFAULT_RESULT_LIMIT, $offsetPage = API_DEFAULT_RESULT_PAGE)
    {
        $limit = validate_pos_int($limit, API_DEFAULT_RESULT_LIMIT);
        $offsetPage = (validate_pos_int($offsetPage, API_DEFAULT_RESULT_PAGE) - 1) * $limit; //offset

        /* @var $db \PDO */
        $db = VIRUS::getDb();
        $where = $filter->getStatementQuery();
        $statement = $db->prepare('SELECT *  FROM ' . self::TABLE_VIDEO . " WHERE $where LIMIT $offset, $limit");
        if (!$statement->execute($filter->getVarArray()))
            return false;
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getSingle($id)
    {
        $id = validate_pos_int($id, -1);
        /* @var $db \PDO */
        $db = VIRUS::getDb();
        $result = $db->query('SELECT *  FROM ' . self::TABLE_VIDEO . " WHERE id=$id LIMIT 1");
        return $result ? $result->fetchAll(PDO::FETCH_ASSOC) : false;
    }

    public static function createEntry($title, $genres = '', $actors = '', $year = '')
    {
        $logger = VIRUS::getLogger();
        $fields = array();
        if (empty($title))
        {
            $logger->LogError('Video title should not be empty on VideoModel::createEntry(), file ' . __FILE__ . ' line ' . __LINE__ . '.');
            return false;
        }
        $fields[self::FIELD_TITLE] = trim($title);
        if (empty($genres))
        {
            if (is_array($genres))
                $genres = implode(', ', $genres);
            $fields[self::FIELD_GENRES] = trim($genres);
        }else
        {
            $logger->LogDebug('Empty video genres on VideoModel::createEntry(), file ' . __FILE__ . ' line ' . __LINE__ . '.');
            $genres = false;
        }
        if (empty($actors))
        {
            if (is_array($actors))
                $actors = implode(', ', $actors);
            $actors = trim($actors);
            $fields[self::FIELD_ACTORS] = trim($actors);
        }else
        {
            $logger->LogDebug('Empty video actors on VideoModel::createEntry(), file ' . __FILE__ . ' line ' . __LINE__ . '.');
            $actors = false;
        }
        if (!empty($year))
        {
            $year = validate_pos_int($id, -1);
            if ($year <= 0)
            {
                $logger->LogError('Invalid video year on VideoModel::createEntry(), file ' . __FILE__ . ' line ' . __LINE__ . '.');
                return false;
            }
            $fields[self::FIELD_YEAR] = trim($year);
        }
        $x = function ($s) { //For making "?,?,?,?", depending on the number of available fields to insert
                    return $s == 0 ? '' : '?' + str_repeat(',?', $s - 1);
                };
        $query = 'INSERT INTO ' . self::TABLE_VIDEO . ' (' . implode(', ', array_keys($fields)) . ') VALUES (' . $x(count($fields)) . ')';
        /* @var $db \PDO */
        $db = VIRUS::getDb();
        $statement = $db->prepare($query);
        return $statement->execute(array_values($fields)) && $statement->rowCount() > 0 ? $db->lastInsertId() : false;
    }

    public static function getCount(ModelFilter $filter)
    {

        $db = VIRUS::getDb();
        if (isset($filter))
        {
            $result = $db->query('SELECT COUNT(*) FROM ' . self::TABLE_VIDEO);
            if (!$result || !($result = $result->fetch(PDO::FETCH_NUM)))
                return false;
            return intval($result[0], 10);
        }else
        {
            $where = $filter->getStatementQuery();
            $statement = $db->prepare('SELECT count(*)  FROM ' . self::TABLE_VIDEO . " WHERE $where");
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

