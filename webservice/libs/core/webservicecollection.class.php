<?php
namespace VIRUS\webservice;

if(!defined("VIRUS")){
    die("You are not allowed here!");    
}

class WebserviceCollection
{

    public $resulArray, $count, $total, $perPage, $page, $totalPages, $resourceTag;

    public function __construct($resourceTag, $resultArray, $total = null, $perPage = null, $page = null)
    {
        $this->resourceTag = $resourceTag;
        $this->resulArray = $resultArray;
        $this->count = count($resultArray);
        $this->total = $total;
        $this->perPage = $perPage;
        $this->page = $page;
        if ($total !== null && $perPage !== null)
        {
            $tpages = $perPage != 0 ? $total / $perPage : 0;
            $this->totalPages = is_float($tpages) ? intval($tpages, 10) + 1 : $tpages;
        } else
        {
            $this->totalPages = null;
        }
    }

}