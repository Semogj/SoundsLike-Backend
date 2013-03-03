<?php

namespace VIRUS\webservice\models;

use \PDO;
use VIRUS\webservice\CoreVIRUS;
use VIRUS\webservice\models\ModelFilter;

class SoundSegmentModel implements DatabaseModel
{
    //table soundSegment

    const FIELD_SOUND_ID = "idSoundSegment";
    const FIELD_SOUND_START = "start";
    const FIELD_SOUND_END = 'end';
    const FIELD_VIDEO_ID = 'videoId';
    const TABLE_SOUND = 'SoundSegment';
    //table video
    const TABLE_VIDEO = 'Video';
    const FIELD_VIDEO_ID_VIDEO = 'idVideo';
    const FIELD_VIDEO_TEXTID = 'textId';
    const FIELD_VIDEO_TITLE = 'title';
    //view similarities (merges soundSimilarities and soundsegment tables)
    const VIEW_SIM = 'SoundSegmentSimilarities';
    

    private static $filter;

    public static function filter()
    {
        return new SoundSegmentFilter();
    }

    public static function get($limit, $offsetPage)
    {
        $limit = validate_pos_int($limit, API_DEFAULT_RESULT_LIMIT);
        $offsetPage = (validate_pos_int($offsetPage, API_DEFAULT_RESULT_PAGE) - 1) * $limit; //offset
        //SELECT idSoundSegment, start, end, idVideo, textId, title FROM SoundSegment, Video WHERE videoId = idVideo
        /* @var $db \PDO */
        $db = CoreVIRUS::getDb();
        $result = $db->query('SELECT ' . self::FIELD_SOUND_ID .
                ', ' . self::FIELD_SOUND_START .
                ', ' . self::FIELD_SOUND_END .
                ', ' . self::FIELD_VIDEO_ID_VIDEO .
                ', ' . self::FIELD_VIDEO_TITLE .
                ' FROM ' . self::TABLE_SOUND .
                ',' . self::TABLE_VIDEO .
                ' WHERE ' . self::FIELD_VIDEO_ID . ' = ' . self::FIELD_VIDEO_ID_VIDEO .
                "LIMIT $offsetPage, $limit");

        return $result ? $result->fetchAll(PDO::FETCH_ASSOC) : false;
    }

    public static function getFiltered(ModelFilter $filter, $limit = API_DEFAULT_RESULT_LIMIT, $offsetPage = API_DEFAULT_RESULT_PAGE)
    {
        $limit = validate_pos_int($limit, API_DEFAULT_RESULT_LIMIT);
        $offsetPage = (validate_pos_int($offsetPage, API_DEFAULT_RESULT_PAGE) - 1) * $limit; //offset

        /* @var $db \PDO */
        $db = CoreVIRUS::getDb();
        $sql = 'SELECT ' . self::FIELD_SOUND_ID .
                ', ' . self::FIELD_SOUND_START .
                ', ' . self::FIELD_SOUND_END .
                ', ' . self::FIELD_VIDEO_ID_VIDEO .
                ', ' . self::FIELD_VIDEO_TITLE .
                ' FROM ' . self::TABLE_SOUND .
                ',' . self::TABLE_VIDEO .
                ' WHERE ' . self::FIELD_VIDEO_ID . ' = ' . self::FIELD_VIDEO_ID_VIDEO .
                ($filter->isEmpty() ? ' ' : ' AND ' . $filter->getStatementQuery() ) .
                " ORDER BY start ASC LIMIT $offsetPage, $limit";

        $statement = $db->prepare($sql);
        if (!$statement->execute($filter->getVarArray()))
            return false;
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Get the most similars 
      SELECT soundSegmentId1, soundSegmentId2, value
      FROM SoundSimilarity, (SELECT idSoundSegment FROM SoundSegment WHERE videoId = [VIDEO_ID] ) as table2
      WHERE soundSegmentId1 = table2.idSoundSegment OR soundSegmentId2 = table2.idSoundSegment
      ORDER BY value DESC


      SELECT id1, start1, end1, videoId1, id2, start2, end2, videoId2, value
      FROM SoundSegmentSimilarities
      WHERE videoId1 = [VIDEO_ID]
      AND videoId2 = [VIDEO_ID]
      AND (id1 = [SEGMENT_ID] OR id2 = [SEGMENT_ID])
      ORDER BY value DESC




      SELECT soundSegmentId1, soundSegmentId2, value
      FROM SoundSimilarity, (SELECT idSoundSegment FROM SoundSegment WHERE idSoundSegment = [SEGMENT_ID] ) as table2
      WHERE soundSegmentId1 = table2.idSoundSegment OR soundSegmentId2 = table2.idSoundSegment
      ORDER BY value DESC

      CREATE VIEW SoundSegmentSimilarities AS
      SELECT s1.idSoundSegment as id1,
      s1.start as start1,
      s1.end as end1,
      s1.videoId as videoId1 ,
      s2.idSoundSegment as id2,
      s2.start as start2,
      s2.end as end2,
      s2.videoId as videoId2,
      s.value as value,
      s.lastUpdate as lastUpdate
      FROM SoundSimilarity s, SoundSegment as s1, SoundSegment as s2
      WHERE s.soundSegmentId1 = s1.idSoundSegment
      AND s.soundSegmentId2 = s2.idSoundSegment
      AND s.soundSegmentId1 != s.soundSegmentId2

     */

    public static function getMostSimilarInVideo($segmentId, $videoId, $limit = API_DEFAULT_RESULT_LIMIT, $offsetPage = API_DEFAULT_RESULT_PAGE)
    {
        $limit = validate_pos_int($limit, API_DEFAULT_RESULT_LIMIT);
        $offsetPage = (validate_pos_int($offsetPage, API_DEFAULT_RESULT_PAGE) - 1) * $limit; //offset
        $segmentId = intval($segmentId, 10);
        $videoId = intval($videoId, 10);
        /* @var $db \PDO */
        $db = CoreVIRUS::getDb();
        $sql = 'SELECT id1, start1, end1, videoId1, id2, start2, end2, videoId2, value
                FROM SoundSegmentSimilarities
                WHERE videoId1 = :vidid
                AND videoId2 = :vidid
                AND (id1 = :id OR id2 = :id)
                ORDER BY value DESC
                LIMIT :offset, :limit';

        $statement = $db->prepare($sql);
        $statement->bindValue(':offset', $offsetPage, PDO::PARAM_INT);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->bindValue(':id', $segmentId);
        $statement->bindValue(':vidid', $videoId);
        
        if (!$statement->execute())
            return array();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function getMostSimilar($segmentId, $limit = API_DEFAULT_RESULT_LIMIT, $offsetPage = API_DEFAULT_RESULT_PAGE)
    {
        $limit = validate_pos_int($limit, API_DEFAULT_RESULT_LIMIT);
        $offsetPage = (validate_pos_int($offsetPage, API_DEFAULT_RESULT_PAGE) - 1) * $limit; //offset
        $segmentId = intval($segmentId, 10);
        /* @var $db \PDO */
        $db = CoreVIRUS::getDb();
        $sql = 'SELECT id1, start1, end1, videoId1, id2, start2, end2, videoId2, value
                FROM SoundSegmentSimilarities
                WHERE id1 = :id OR id2 = :id
                ORDER BY value DESC
                LIMIT :offset, :limit';
        
        $statement = $db->prepare($sql);
        $statement->bindValue(':offset', $offsetPage, PDO::PARAM_INT);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->bindValue(':id', $segmentId, PDO::PARAM_INT);

        return $statement->execute() ? $statement->fetchAll(PDO::FETCH_ASSOC) : array();
    }

    public static function getSingle($id)
    {
        $id = validate_pos_int($id, -1);
        /* @var $db \PDO */
        $db = CoreVIRUS::getDb();
        $sql = 'SELECT * FROM ' . self::TABLE_SOUND . ' WHERE ' . self::FIELD_SOUND_ID . " = '$id' LIMIT 1";
        $result = $db->query($sql);
        return $result ? $result->fetchAll(PDO::FETCH_ASSOC) : array();
    }

    public static function createEntry($videoId, $start = null, $end = null)
    {
        return false;
//        $logger = CoreVIRUS::getLogger();
//        $fields = array();
//        if (empty($videoId))
//        {
//            $logger->LogWarn('Video id should not be empty on SoundSegmentModel::createEntry(), file ' . __FILE__ . ' line ' . __LINE__ . '.');
//            return false;
//        }
//        /**
//         * @var $videoModel VideoModel
//         */
//        $videoModel = CoreVIRUS::loadModel('video');
//        if (!$videoModel->entryExistsById($videoId))
//        {
//            $logger->LogWarn("Inexistent video id '$videoId' in the database on SoundSegmentModel::createEntry(), file " . __FILE__ . ' line ' . __LINE__ . '.');
//            return false;
//        }
//
//        $fields[self::FIELD_VIDEO_ID] = trim($title);
//
//
//        if (empty($genres))
//        {
//            if (is_array($genres))
//                $genres = implode(', ', $genres);
//            $fields[self::FIELD_GENRES] = trim($genres);
//        }else
//        {
//            $logger->LogDebug('Empty video genres on SoundSegmentModel::createEntry(), file ' . __FILE__ . ' line ' . __LINE__ . '.');
//            $genres = false;
//        }
//        if (empty($actors))
//        {
//            if (is_array($actors))
//                $actors = implode(', ', $actors);
//            $actors = trim($actors);
//            $fields[self::FIELD_ACTORS] = trim($actors);
//        }else
//        {
//            $logger->LogDebug('Empty video actors on SoundSegmentModel::createEntry(), file ' . __FILE__ . ' line ' . __LINE__ . '.');
//            $actors = false;
//        }
//        if (!empty($year))
//        {
//            $year = validate_pos_int($id, -1);
//            if ($year <= 0)
//            {
//                $logger->LogError('Invalid video year on SoundSegmentModel::createEntry(), file ' . __FILE__ . ' line ' . __LINE__ . '.');
//                return false;
//            }
//            $fields[self::FIELD_YEAR] = trim($year);
//        }
//        $x = function ($s) { //For making "?,?,?,?", depending on the number of available fields to insert
//                    return $s == 0 ? '' : '?' + str_repeat(',?', $s - 1);
//                };
//        $query = 'INSERT INTO ' . self::TABLE_SOUND . ' (' . implode(', ', array_keys($fields)) . ') VALUES (' . $x(count($fields)) . ')';
//        /* @var $db \PDO */
//        $db = CoreVIRUS::getDb();
//        $statement = $db->prepare($query);
//        return $statement->execute(array_values($fields)) && $statement->rowCount() > 0 ? $db->lastInsertId() : false;
    }

    public static function getCount(ModelFilter $filter = NULL)
    {

        $db = CoreVIRUS::getDb();
        if (isset($filter))
        {
            $result = $db->query('SELECT COUNT(*) FROM ' . self::TABLE_SOUND);
            if (!$result || !($result = $result->fetch(PDO::FETCH_NUM)))
                return false;
            return intval($result[0], 10);
        }else
        {
            $where = $filter->getStatementQuery();
            $statement = $db->prepare('SELECT count(*)  FROM ' . self::TABLE_SOUND . " WHERE $where");
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

class SoundSegmentFilter extends ModelFilter
{

    const FIELD_SOUND_ID = "idSoundSegment";
    const FIELD_START = "start";
    const FIELD_END = 'end';
    const FIELD_VIDEO_ID = 'videoId';
    const TABLE_SOUND = 'SoundSegment';

    public function __construct()
    {
        parent::__construct();
    }

    public function byId($id)
    {
        $this->appendQuery(self::FIELD_SOUND_ID, intval($id, 10));
        return $this;
    }

    public function byIdRange($lower = null, $higher = null)
    {
        $lower = intval($lower, 10);
        $higher = intval($higher, 10);
        if (isset($lower, $higher))
            $this->appendQuery(self::FIELD_SOUND_ID, $lower, '>=')->and_()->appendQuery(self::FIELD_SOUND_ID, $higher, '<=');
        else if (isset($lower))
        {
            $this->appendQuery(self::FIELD_SOUND_ID, $lower, '>=');
        } else
        {
            $this->appendQuery(self::FIELD_SOUND_ID, $higher, '<=');
        }
        return $this;
    }

    public function byTimeRange($lower, $higher)
    {
        $lower = intval($lower, 10);
        $higher = intval($higher, 10);
        if (isset($lower, $higher))
            $this->appendQuery(self::FIELD_START, $lower, '>=')->and_()->appendQuery(self::FIELD_END, $higher, '<=');
        else if (isset($lower))
        {
            $this->appendQuery(self::FIELD_START, $lower, '>=');
        } else
        {
            $this->appendQuery(self::FIELD_END, $higher, '<=');
        }
        return $this;
    }

    public function byVideoId($videoId)
    {
        $this->appendQuery(self::FIELD_VIDEO_ID, $videoId, '=');
        return $this;
    }

}