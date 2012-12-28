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
class WebserviceCollection
{

    private $resultArray, $size, $totalSize, $perPage, $currentPage, $totalPages, $resourseLabel;

    public function __construct($resourseLabel, $resultArray, $total = null, $perPage = null, $page = null)
    {
        $this->resourseLabel = $resourseLabel;
        $this->resultArray = $resultArray;
        $this->size = count($resultArray);
        $this->totalSize = $total;
        $this->perPage = $perPage;
        $this->currentPage = $page;
        if ($total !== null && $perPage !== null)
        {
            $tpages = $perPage != 0 ? $total / $perPage : 0;
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
        return $this->perPage;
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
    const XML_LABEL_PERPAGE = 3;
    const XML_LABEL_TOTAL = 4;
    const XML_LABEL_TOTALPAGES = 5;

    private static $labels = array(self::XML_LABEL_COUNT => 'size',
        self::XML_LABEL_PAGE => 'page', self::XML_LABEL_PERPAGE => 'perPage',
        self::XML_LABEL_TOTAL => 'totalSize', self::XML_LABEL_TOTALPAGES => 'totalPages'
    );

    public function getResultXML($xmlCallback, array $xmlLabels = array())
    {
        if (is_callable($xmlCallback))
        {
            throw new \BadMethodCallException("The \$xmlCallback is not a valid callable function.");
        }
        $labels = self::$labels;
        if (!empty($xmlLabels))
        {
            $labels = array_merge($labels, $xmlLabels);
        }
        $sufix = " {$labels[self::XML_LABEL_COUNT]}=\"$this->count\"";
        if ($this->page !== null)
        {
            $sufix .= " {$labels[self::XML_LABEL_PAGE]}=\"$this->page\"";
        }
        if ($this->perPage !== null)
        {
            $sufix .= " {$labels[self::XML_LABEL_PERPAGE]}=\"$this->perPage\"";
        }
        if ($this->total !== null)
        {
            $sufix .= " {$labels[self::XML_LABEL_TOTAL]}=\"$this->total\"";
        }
        if ($this->totalPages !== null)
        {
            $sufix .= " {$labels[self::XML_LABEL_TOTALPAGES]}=\"$this->totalPages\"";
        }
        $valueTmp = null;
        if (is_array($this->resulArray))
            foreach ($this->resulArray as $entry)
            {
                $valueTmp .= "<{$this->resourseLabel}>"
                        . is_object($entry) && $entry instanceof WebserviceCollection ?
                        $entry->getResultXML($xmlCallback, $xmlLabels) : $xmlCallback($entry)
                        . "</{$this->resourseLabel}>";
            }
        else
            $valueTmp = htmlspecialchars($this->resulArray, null, 'UTF-8');
        $key = plural($this->resourceTag);
        return "<{$key}{$sufix}>$valueTmp</$key>";
    }

}