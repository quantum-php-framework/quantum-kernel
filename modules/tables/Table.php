<?php


namespace Quantum;

/**
 * Class Table
 * @package Quantum
 */
class Table
{
    /**
     * @var ValueTree
     */
    public $fields;

    /**
     * @var TableElementsFactory
     */
    public $factory;

    /**
     * @var bool
     */
    public $shouldAddActionsColumn;



    /**
     * FormBuilder constructor.
     * @param QString $action
     * @param QString $method
     * @param QString $name
     * @param bool $addCSRF
     */
    public function __construct(TableElementsFactory $factory, $action = "", $method = "post", $name = "", $addCSRF = true)
    {
        $this->fields = new_vt();
        $this->factory = $factory;
        $this->shouldAddActionsColumn = false;
    }

    /**
     * @return QString
     */
    public function __toString()
    {
        return $this->getHtml();
    }

    /**
     * @return string
     */
    public function getHtml()
    {
        $html  = $this->getHeader();
        $html .= $this->getRows();

        $html .= $this->getFooter();

        if (!empty($this->pages) && !empty($this->modelData))
            $html.= $this->pages->getPages()->display_jump_menu();

        return $html;

    }

    /**
     * @return mixed
     */
    public function getHeader()
    {
        return $this->factory->getHeaderHtml($this->headers);
    }

    /**
     * @return mixed
     */
    public function getRows()
    {
        if (!empty($this->factory_rows_method))
        {
            $rows = call_user_func(array($this->factory, $this->factory_rows_method), $this->modelData, $this->dataFields, $this->shouldAddActionsColumn);
        }
        else
        {
            $rows = $this->factory->getRowsHtml($this->modelData, $this->dataFields, $this->shouldAddActionsColumn);
        }

        return $rows;
    }

    /**
     * @return mixed
     */
    public function getFooter()
    {
        return $this->factory->getFooterHtml();
    }

    /**
     * @param $array
     * @return $this
     */
    public function addHeaders($array)
    {
        $this->headers = $array;

        return $this;
    }

    /**
     * @param $array
     * @param $fieldsToShow
     * @return $this
     */
    public function addModelData($array, $fieldsToShow)
    {
        $this->modelData = $array;
        $this->dataFields = $fieldsToShow;

        return $this;
    }

    /**
     * @param $key
     * @param $value
     */
    function addAdditionalModelColumn($key, $value)
    {
        if (empty($this->additional_model_columns))
            $this->additional_model_columns = new_vt();

        $this->additional_model_columns->set($key, $value);
    }


    /**
     * @param $pages
     * @return $this
     */
    public function setPages($pages)
    {
        $this->pages = $pages;

        return $this;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setFactoryRowsMethod($name)
    {
        $this->factory_rows_method = $name;

        return $this;
    }

    /**
     * @return $this
     */
    public function addActionsColumn()
    {
        array_push($this->headers, 'Actions');
        $this->shouldAddActionsColumn = true;

        return $this;
    }


    /**
     * @param string $name
     * @return $this
     */
    public function toOutput($name = 'table')
    {
        Output::getInstance()->set($name, $this->getHtml());
        return $this;
    }






}


?>