<?php


namespace Quantum\ActiveRecord;


/**
 * Class ModelCollection
 * @package Quantum
 */
class ModelCollection
{
    /**
     * @var ValueTree
     */
    public $records;
    /**
     * @var
     */
    public $records_count;
    /**
     * @var
     */
    public $total_records_count;

    /**
     * ModelCollection constructor.
     */
    private function __construct()
    {

    }

    /**
     * @return ValueTree
     */
    public function getRecords()
    {
        return $this->records;
    }

    /**
     * @param mixed $records
     */
    public function setRecords($records)
    {
        if (is_vt($records))
            $this->records = $records;

        if (is_array($records))
            $this->records = new_vt($records);
    }

    /**
     * @return mixed
     */
    public function getRecordsCount()
    {
        return $this->records_count;
    }

    /**
     * @param mixed $records_count
     */
    public function setRecordsCount($records_count)
    {
        $this->records_count = $records_count;
    }

    /**
     * @return int
     */
    public function getTotalRecordsCount()
    {
        return $this->total_records_count;
    }

    /**
     * @param mixed $total_records_count
     */
    public function setTotalRecordsCount($total_records_count)
    {
        $this->total_records_count = $total_records_count;
    }

    /**
     * @return ValueTree
     */
    public function toArray()
    {
        if (is_vt($this->records))
            return $this->records->all();

        if (is_array($this->records))
            return new_vt($this->records);
    }

    /**
     * @return ValueTree
     */
    public function toValueTree()
    {
        if (is_vt($this->records))
            return $this->records;

        if (is_array($this->records))
            return new_vt($this->records);
    }

    /**
     * @return Paginator
     */
    public function getPaginator()
    {
        return $this->paginator;
    }


    /**
     * @return ActiverecordModel
     */
    public function first()
    {
        return $this->records->first();
    }

    /**
     * @return ActiverecordModel
     */
    public function last()
    {
        return $this->records->last();
    }

    /**
     * @return int
     */
    public function size()
    {
        return $this->records->size();
    }

    /**
     * @return ActiverecordModel
     */
    public function prev()
    {
        return $this->records->prev();
    }

    /**
     * @return ActiverecordModel
     */
    public function next()
    {
        return $this->records->next();
    }

    /**
     * @return boolean
     */
    public function isEmpty()
    {
        return $this->records->isEmpty();
    }


    /**
     * @param $modelName
     * @param $conditions
     * @param int $ipp
     * @param string $order
     * @return ModelCollection
     */
    public static function createWithConditions($modelName, $conditions, $ipp = 25, $order = 'id DESC')
    {
        $ipp = get_request_param('ipp', $ipp);
        $low = get_request_param('page', 0);

        $total_records_count = (int)$modelName::count(array('conditions' => $conditions));

        $filtered_collection = $modelName::find('all', array('limit' => $ipp, 'offset' => $low,  'order' => $order, 'conditions' => $conditions));

        $collection = new self();
        $collection->setRecords($filtered_collection);
        $collection->setRecordsCount(count($filtered_collection));
        $collection->setTotalRecordsCount($total_records_count);

        $pages = \QM::getPages(\QM::request()->getUriWithoutQueryString(), $collection->total_records_count);

        $collection->paginator = $pages;

        return $collection;

    }

}