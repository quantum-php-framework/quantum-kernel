<?php

namespace Quantum;

abstract class FormElementsFactory
{
    /**
     * @param $field
     * @return mixed
     */
    abstract public function getTextInputHtml($field);

    /**
     * @param $field
     * @return mixed
     */
    abstract public function getFileInputHtml($field);

    /**
     * @param $field
     * @return mixed
     */
    abstract public function getPasswordInputHtml($field);

    /**
     * @param $field
     * @return mixed
     */
    abstract public function getSelectInputHtml($field);

    /**
     * @param $field
     * @return mixed
     */
    abstract public function getTextAreaHtml($field);

    /**
     * @param $field
     * @return mixed
     */
    abstract public function getCheckboxHtml($field);

    /**
     * @param $field
     * @return mixed
     */
    abstract public function getSubmitButtonHtml($field, $showBackButton = true, $backButtonUrl = "");

    /**
     * @param $field
     * @param $html
     * @return mixed
     */
    abstract public function getFieldWrapperHtml($field, $html);
}