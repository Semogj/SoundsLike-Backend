<?php

namespace VIRUS\webservice;

if (!defined("VIRUS"))
{
    die("You are not allowed here!");
}

/**
 * This class is meant to represent a collection of results from the webservice.
 * THe idea is to provide more utility beyond simple arrays, allowing to label the
 * collection and allowing to add aditional information.
 * 
 */
class WebserviceCollection //extends JsonSerializable    --- this class is PHP5.4 feature...
{

    private $resultArray, $size, $totalSize, $limit, $currentPage, $totalPages, $resourseLabel;

    public function __construct($resourseLabel, array $resultArray, $total = null, $limitPerPage = null, $page = null)
    {
        $this->resourseLabel = singular($resourseLabel);
        $this->resultArray = $resultArray;
        $this->size = count($resultArray);
        $this->totalSize = $total;
        $this->limit = $limitPerPage;
        $this->currentPage = $page;
        if ($total !== null && $limitPerPage !== null)
        {
            $tpages = $limitPerPage != 0 ? $total / $limitPerPage : 0;
            $this->totalPages = is_float($tpages) ? intval($tpages, 10) + 1 : $tpages;
        } else
        {
            $this->totalPages = null;
        }
    }

    public function getResultArray()
    {
        return $this->resultArray;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function getTotalSize()
    {
        return $this->totalSize;
    }

    public function getPerPage()
    {
        return $this->limit;
    }

    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    public function getTotalPages()
    {
        return $this->totalPages;
    }

    public function getResourseLabel()
    {
        return $this->resourseLabel;
    }

    const XML_LABEL_COUNT = 1;
    const XML_LABEL_PAGE = 2;
    const XML_LABEL_LIMIT = 3;
    const XML_LABEL_TOTAL = 4;
    const XML_LABEL_TOTALPAGES = 5;
    const XML_LALEL_NODES_COUNT = 6;
    const XML_LABEL_INT_PREFIX = 7;

    private static $labels = array(self::XML_LABEL_COUNT => 'size',
        self::XML_LABEL_PAGE => 'page', self::XML_LABEL_LIMIT => 'limit',
        self::XML_LABEL_TOTAL => 'totalsize', self::XML_LABEL_TOTALPAGES => 'totalpages'
    );

    public function getResultXML($xmlCallback, array $xmlLabels = array())
    {

        $labels = self::$labels;
        if (!empty($xmlLabels))
        {
//            $labels = array_merge($labels, $xmlLabels);
            $labels = $xmlLabels + $labels;
        }
        $sufix = " {$labels[self::XML_LABEL_COUNT]}=\"{$this->size}\"";
        if ($this->currentPage !== null)
        {
            $sufix .= " {$labels[self::XML_LABEL_PAGE]}=\"{$this->currentPage}\"";
        }
        if ($this->limit !== null)
        {
            $sufix .= " {$labels[self::XML_LABEL_LIMIT]}=\"{$this->limit}\"";
        }
        if ($this->totalSize !== null)
        {
            $sufix .= " {$labels[self::XML_LABEL_TOTAL]}=\"{$this->totalSize}\"";
        }
        if ($this->totalPages !== null)
        {
            $sufix .= " {$labels[self::XML_LABEL_TOTALPAGES]}=\"{$this->totalPages}\"";
        }
        $value = '';
//        var_dump($sufix);

        if (is_array($this->resultArray))
        {
            foreach ($this->resultArray as $entry)
            {

                $value .= "<{$this->resourseLabel}>"
                        . (is_object($entry) && $entry instanceof WebserviceCollection ?
                                $entry->getResultXML($xmlCallback, $xmlLabels) : $xmlCallback($entry, $xmlLabels))
                        . "</{$this->resourseLabel}>";
            }
        }
        else
            $value = htmlspecialchars($this->resultArray, null, 'UTF-8');
        $key = strtolower(plural($this->resourseLabel));
        return "<{$key}{$sufix}>$value</$key>";
    }

    public function jsonSerialize()
    {
        $res =  array('videos' => $this->resultArray);
        if ($this->currentPage !== null)
        {
            $res[$labels[self::XML_LABEL_PAGE]] = $this->currentPage;
        }
        if ($this->limit !== null)
        {
            $res[$labels[self::XML_LABEL_LIMIT]] = $this->limit;
        }
        if ($this->totalSize !== null)
        {
            $res[$labels[self::XML_LABEL_TOTAL]] = $this->totalSize;
        }
        if ($this->totalPages !== null)
        {
            $res[$labels[self::XML_LABEL_TOTALPAGES]] = $this->totalPages;
        }
        return $res;
    }

}