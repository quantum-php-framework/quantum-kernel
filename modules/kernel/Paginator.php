<?php


namespace Quantum;

/**
 * Class Paginator
 * @package Quantum
 */
class Paginator
{
    /**
     * @var
     */
    public $total_items_count;


    /**
     * Paginator constructor.
     * @param $system_url
     * @param $items_count
     */
    function __construct($system_url, $items_count)
    {
        $this->setPaginationVars();

	    $this->setup($system_url, $items_count);
    }


    /**
     * @param $system_url
     * @param $items_count
     */
    function setup($system_url, $items_count)
    {
        $this->total_items_count = $items_count;

        Import::library('pagination/paginator_qsa.class.php');

        $this->paginator = new \Paginator;
        $this->paginator->items_total = $items_count;
        $this->paginator->mid_range = 9;
        $this->paginator->system_url = $system_url;
        $this->paginator->items_per_page = $this->pagination->ipp;
        $this->paginator->paginate($this->pagination->page, $this->pagination->ipp);
    }

    /**
     * @return mixed
     */
    function getLow()
    {
        return $this->paginator->low;
    }

    /**
     * @return mixed
     */
    function getItemsPerPage()
    {
        return $this->paginator->items_per_page;
    }

    /**
     * @return mixed
     */
    function getPages()
    {
        return $this->paginator;
    }

    /**
     *
     */
    public function setPaginationVars()
    {
        $this->pagination = new \stdClass();

        if (isset($_REQUEST["start"]))
            $this->pagination->page = $_REQUEST["start"];

        if (isset($_REQUEST["page"]))
            $this->pagination->page = $_REQUEST["page"];

        if (empty($this->pagination->page))
            $this->pagination->page = 1;



        if (isset($_REQUEST["length"]))
            $this->pagination->ipp = $_REQUEST["length"];

        if (isset($_REQUEST["ipp"]))
            $this->pagination->ipp = $_REQUEST["ipp"];


        if (empty($this->pagination->ipp))
            $this->pagination->ipp = 25;



    }
    
    
    
}