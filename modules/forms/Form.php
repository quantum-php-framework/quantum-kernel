<?php

namespace Quantum;

/**
 * Class Form
 * @package Quantum
 */
class Form implements \Countable
{
    /**
     * @var ValueTree
     */
    public $fields;
    /**
     * @var ValueTree
     */
    public $validations;
    /**
     * @var QString
     */
    public $action;
    /**
     * @var QString
     */
    public $method;
    /**
     * @var QString
     */
    public $name;
    /**
     * @var QString
     */
    public $auto_complete;

    /**
     * @var QString
     */
    public $error_message;
    /**
     * @var
     */
    public $enctype;

    /**
     * @var FormElementsFactory
     */
    public $factory;

    /**
     * @var QString
     */
    public $css_class;

    /**
     * @var bool
     */
    public $show_back_button;

    /**
     * @var string
     */
    public $back_button_url;



    /**
     * FormBuilder constructor.
     * @param QString $action
     * @param QString $method
     * @param QString $name
     * @param bool $addCSRF
     */
    public function __construct(FormElementsFactory $factory, $action = "", $method = "post", $name = "", $addCSRF = true)
    {
        $this->setFactory($factory);

        $this->fields = new_vt();
        $this->validations = new_vt();
        $this->action = $action;
        $this->method = $method;
        $this->name = $name;
        $this->back_button_url = '"javascript:history.back()"';
        $this->showBackButton(true);
        $this->setAutoComplete(false);

        if ($addCSRF)
            $this->addCSRF();
    }

    /**
     * @return QString
     */
    public function __toString()
    {
        return $this->getHtml();
    }

    /**
     * @param $action
     */
    public function setAction ($action)
    {
        $this->action = $action;

        return $this;
    }

    public function showBackButton($shouldShow)
    {
        $this->show_back_button = $shouldShow;

        return $this;
    }

    public function setBackButtonUrl($url)
    {
        $this->back_button_url = $url;

        return $this;
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->fields->size();
    }

    /**
     * @param FormElementsFactory $factory
     * @return $this
     */
    public function setFactory(FormElementsFactory $factory)
    {
        $this->factory = $factory;

        return $this;
    }

    /**
     * @param $method
     */
    public function setMethod ($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param $name
     */
    public function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * @param $type : multipart\text\application
     */
    public function setEnctype($type)
    {
        switch ($type)
        {
            case "text":
                $this->enctype = "text/plain";
                break;
            case "multipart":
                $this->enctype = "multipart/form-data";
                break;
            case "application":
                $this->enctype = "application/x-www-form-urlencoded";
                break;
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function enableFileUpload()
    {
        $this->setEnctype("multipart");

        return $this;
    }

    /**
     * @return $this
     */
    public function setAllFieldsAsRequired()
    {
        foreach ($this->fields as $key => $field)
        {
            $this->setRequiredParam($key, true);
        }

        return $this;
    }

    /**
     * @param $shouldAutoComplete
     */
    public function setAutoComplete($shouldAutoComplete)
    {
        if ($shouldAutoComplete)
            $this->auto_complete = "on";
        else
            $this->auto_complete = "off";

        return $this;
    }

    /**
     * @param $visibleName
     * @param $paramName
     * @param $defaultValue
     */
    public function text($visibleName, $paramName, $defaultValue = '', $required = false)
    {
        $this->addParam('text', $visibleName, $paramName, $defaultValue, $required);
        return $this;
    }

    /**
     * @param $visibleName
     * @param $paramName
     * @param $defaultValue
     */
    public function password($visibleName, $paramName, $defaultValue = '', $required = false)
    {
        $this->addParam('password', $visibleName, $paramName, $defaultValue, $required);
        return $this;
    }

    /**
     * @param $visibleName
     * @param $paramName
     * @param $defaultValue
     */
    public function textarea($visibleName, $paramName, $defaultValue = '', $required = false)
    {
        $this->addParam('textarea', $visibleName, $paramName, $defaultValue, $required);
        return $this;
    }

    /**
     * @param $visibleName
     * @param $paramName
     * @param $defaultValue
     */
    public function checkbox($visibleName, $paramName, $defaultValue = '', $required = false)
    {
        $this->addParam('checkbox', $visibleName, $paramName, $defaultValue, $required);
        return $this;
    }

    /**
     * @param $paramName
     * @param $defaultValue
     */
    public function hidden($paramName, $defaultValue, $required = false)
    {
        $this->addParam('hidden', $paramName, $paramName, $defaultValue, $required);
        return $this;
    }

    /**
     * @param $paramName
     * @param $defaultValue
     */
    public function file($visibleName, $paramName, $required = false)
    {
        $this->addParam('file', $visibleName, $paramName, '', $required);
        return $this;
    }

    /**
     * @param $visibleName
     * @param $paramName
     * @param $options
     */
    public function select($visibleName, $paramName, $options, $default_value = false, $required = false)
    {
        $this->addParam("select", $visibleName, $paramName, $default_value, $required, $options);
        return $this;
    }

    /**
     * @param $visibleName
     */
    public function submitButton($visibleName, $id = "")
    {
        $this->addParam ("submit", $visibleName, $id, null, false);
        return $this;
    }

    /**
     *
     */
    public function addCSRF()
    {
        $this->hidden('csrf', \Quantum\Crypto::encryptWithLocalKey(\QM::session()->get('csrf')));
        return $this;
    }

    public function custom($visibleName, $paramName, $callback)
    {
        $this->addParam('custom', $visibleName, $paramName, $callback);
    }

    /**
     * @param $type
     * @param $visibleName
     * @param $paramName
     * @param $defaultValue
     */
    private function addParam($type, $visibleName, $paramName, $defaultValue, $required = false, $options= array())
    {
        $this->fields->set($paramName, new FormParam($type, $visibleName, $paramName, $defaultValue, $required, $options));
    }

    /**
     * @param $key
     * @param $shouldBeRequired
     * @return $this
     */
    public function setRequiredParam($key, $shouldBeRequired = true)
    {
        if ($this->fields->has($key))
        {
            $param = $this->fields->get($key);
            $param->required = $shouldBeRequired;
            $this->fields->set($key, $param);
        }

        return $this;
    }

    public function setRequiredParams($params)
    {
        foreach ($params as $param)
        {
            $this->setRequiredParam($param);
        }

        return $this;
    }

    /**
     * @param $key
     * @param $shouldBeRequired
     * @return $this
     */
    public function setNonRequiredParam($key)
    {
        if ($this->fields->has($key))
        {
            $param = $this->fields->get($key);
            $param->required = false;
            $this->fields->set($key, $param);
        }

        return $this;
    }

    public function setConfirmMessage($msg)
    {
        $this->confirm_message = $msg;
    }

    public function setCSSClass($class)
    {
        $this->css_class = $class;
    }



    /**
     * @return QString
     */
    public function getHtml()
    {
        if (empty($this->factory))
            throw new \RuntimeException("Form Elements Factory missing");

        $html = $this->getHeader();

        $html .= $this->getAllFieldsHtml();

        $html .= $this->getFooter();

        return $html;
    }

    /**
     * Set to Quantum\Output
     */
    public function toOutput($smarty_tag_name = 'form')
    {
        Output::getInstance()->set($smarty_tag_name, $this->getHtml());
        return $this;
    }


    public function validator()
    {
        if (empty($this->validator))
        {
            $this->validator = new \Quantum\RequestParamValidator();
        }

        return $this->validator;
    }



    /**
     * @return string
     */
    private function getHeader()
    {
        $html = '<form action="'.$this->action.'" method="'.$this->method.'" autocomplete="'.$this->auto_complete.'"';

        if (!empty($this->enctype))
        {
            $html .= 'enctype="'.$this->enctype.'"';
        }

        if (!empty($this->name))
        {
            $html .= ' name="'.$this->name.'"' ;
            $html .= ' id="'.$this->name.'"' ;
        }

        if (!empty($this->target))
        {
            $html .= ' target="'.$this->target.'"' ;
        }

        if (!empty($this->css_class))
        {
            $html .= ' class="'.$this->css_class.'"' ;
        }

        if (!empty($this->confirm_message))
        {
            $html .= " onsubmit='return confirm(\"$this->confirm_message\");'";
        }

        $html .= '>';

        return $html;
    }

    /**
     * @return string
     */
    private function getFooter()
    {
        $html = "</form>";

        return $html;
    }

    /**
     * @return string
     */
    private function getAllFieldsHtml()
    {
        $html = "";

        foreach ($this->fields->all() as $field)
        {
            switch ($field->type)
            {
                case 'text':
                    $html .= $this->prepare($field, $this->factory->getTextInputHtml($field));
                    break;
                case 'select':
                    $html .= $this->factory->getSelectInputHtml($field);
                    break;
                case 'hidden':
                    $html .= '<input type="hidden" name="' . $field->paramName . '" value="' . $field->value . '" id="'.$field->paramName.'">';
                    break;
                case 'textarea':
                    $html .= $this->factory->getTextAreaHtml($field);
                    break;
                case 'checkbox':
                    $html .= $this->factory->getCheckboxHtml($field);
                    break;
                case 'submit':
                    $html .= $this->factory->getSubmitButtonHtml($field, $this->show_back_button, $this->back_button_url);
                    break;
                case 'password':
                    $html .= $this->prepare($field, $this->factory->getPasswordInputHtml($field));
                    break;
                case 'custom':
                    $html .= $field->value;
                    break;
                case 'file':
                    $html .= $this->factory->getFileInputHtml($field);
                    break;
            }
        }

        return $html;
    }

    /**
     * @param $field
     * @param $html
     * @return QString
     */
    private function prepare($field, $html)
    {
        if (!$field->required)
            return $html;

        $s = qs($html);
        $s = $s->insertBeforeLastOcurrenceOf('required ', "type=");

        if ($s->contains('value=""'))
        {
            $reg = RuntimeRegistry::getInstance();

            if ($reg->has('postedparam_'.$field->paramName))
            {
                $s = $s->insertAfterFirstOcurrenceOf($reg->get('postedparam_'.$field->paramName), 'value="');
            }
        }

        return $s;
    }

    /**
     * @param $factory
     * @param $model
     * @param array $allowedAttributes
     * @return Form
     */
    public static function createFromModel($factory, $model, $allowedAttributes = array())
    {
        $form = new Form($factory);

        $model_attributes = $model->attributes();

        if (empty($allowedAttributes))
            $allowedAttributes = array_keys($model_attributes);

        foreach ($allowedAttributes as $key => $param)
        {
            $shouldAdd = true;

            if (!array_key_exists($param, $model_attributes))
            {
                $shouldAdd = false;
            }

            if ($shouldAdd)
                $form->text(qs($param)->replaceCharacters("_", " ")->toTitleCase(), $param, $model_attributes[$param]);

        }

        return $form;
    }





}


?>