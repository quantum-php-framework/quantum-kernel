<?php

namespace Quantum;

/**
 * Class TableElementsFactory
 * @package Quantum
 */
abstract class TableElementsFactory
{
    /**
     * @param $field
     * @return mixed
     */

    public $name;

    /**
     * TableElementsFactory constructor.
     * @param null $link
     * @param null $name
     */
    public function __construct($link = null, $name = null)
    {
        if (is_array($link))
            $this->setLink($link[0], $link[1]);

        $this->name = $name;
    }

    /**
     * @param $fields
     * @return mixed
     */
    abstract public function getHeaderHtml($fields);

    /**
     * @return mixed
     */
    abstract public function getFooterHtml();

    /**
     * @param $data
     * @param $fieldsToShow
     * @param bool $shouldAddActionsColumn
     * @return mixed
     */
    abstract public function getRowsHtml($data, $fieldsToShow, $shouldAddActionsColumn = false);


    /**
     * @param $datum
     * @param $field
     * @return string
     */
    protected function getRowValue($datum, $field)
    {
        $html = '';

        if (qs($field)->contains('()'))
        {
            $function_name = qs($field)->upToFirstOccurrenceOf('|')->toStdString();

            $row = call_user_func([$datum, qs($function_name)->removeCharacters('()')->getText()]);

            $functions_list = qs($field)->fromFirstOccurrenceOf('|');

            if ($functions_list->isNotEmpty())
            {
                $functions = $functions_list->explode('|');

                if (!empty($functions))
                {
                    foreach ($functions as $function)
                    {
                        if (!empty($function))
                            $row = call_user_func($function, $row);
                    }
                }
            }



            if (instance_of($datum, \ActiveRecord\Model::class))
            {
                $html .= "<td>".$this->surroundWithLinkIfNeeded($row, $field, $datum->getId())."</td>";
            }
            if (is_array($datum) && isset($row))
            {
                $html .= "<td>".$this->surroundWithLinkIfNeeded($row, $field, $datum[$field])."</td>";
            }



        }
        else
        {
            if (qs($field)->contains('|'))
            {
                $functions = qs($field)->fromFirstOccurrenceOf('|')->explode('|');
                $field = qs($field)->upToFirstOccurrenceOf('|')->toStdString();

                $text = '';

                if (is_object($datum))
                {
                    $text = $datum->$field;
                }
                elseif (is_array($datum))
                {
                    $text = $datum[$field];
                }

                foreach ($functions as $function)
                {
                    $text = call_user_func($function, $text);
                }

                if (is_object($datum) && isset($text))
                {
                    if (instance_of($datum, \ActiveRecord\Model::class))
                    {
                        $html .= "<td>".$this->surroundWithLinkIfNeeded($text, $field, $datum->getId())."</td>";
                    }
                    else
                    {
                        $html .= "<td>".$this->surroundWithLinkIfNeeded($text, $field, $datum->$field)."</td>";
                    }
                }

                if (is_array($datum) && isset($text))
                {
                    $html .= "<td>".$this->surroundWithLinkIfNeeded($text, $field, $datum[$field])."</td>";
                }

                return $html;
            }

            if (is_object($datum))
            {
                if (instance_of($datum, \ActiveRecord\Model::class))
                {
                    $html .= "<td>".$this->surroundWithLinkIfNeeded($datum->$field, $field, $datum->getId())."</td>";
                }
                else
                {
                    $html .= "<td>".$this->surroundWithLinkIfNeeded($datum->$field, $field, $datum->$field)."</td>";
                }
            }
            elseif (is_array($datum))
            {
                //echo (json_encode($datum));
                //exit();
                $html .= "<td>".$this->surroundWithLinkIfNeeded($datum[$field], $field, $datum[$field])."</td>";
            }

        }

        return $html;
    }

    /**
     * @param $link
     * @param $field
     * @param string $display_name
     */
    public function setLink($link, $field, $display_name = "")
    {
        $this->link = [$link, $field, $display_name];
    }

    /**
     * @param $data
     * @param $field
     * @param null $id
     * @return string
     */
    protected function surroundWithLinkIfNeeded($data, $field, $id = null)
    {
        if (empty($this->link))
        {
            return $data;
        }

        if ($this->link[1] == $field)
        {
            $displayValue = $data;

            if (!empty($this->link[2]))
            {
                $displayValue = $this->link[2];
            }

            $toggleModalTag = "";
            if (qs($this->link[0])->startsWith('#'))
                $toggleModalTag = "data-toggle='modal'";

            $html = "<a href='".$this->link[0].$id."' $toggleModalTag>".$displayValue."</a>";
            return $html;
        }

        return $data;

    }


}