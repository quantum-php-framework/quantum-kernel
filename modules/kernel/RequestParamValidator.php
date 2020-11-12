<?php

namespace Quantum;


/**
 * Class Validation
 * @package Quantum
 */
class Validation
{
    /**
     * Validation constructor.
     * @param $key
     * @param $type
     * @param $options
     */
    function __construct($key, $type, $options)
    {
        $this->key = $key;
        $this->type = $type;
        $this->options = $options;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return mixed
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param mixed $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
}

/**
 * Class ValidationError
 * @package Quantum
 */
class ValidationError
{
    /**
     * ValidationError constructor.
     * @param $param
     * @param $msg
     */
    public function __construct($param, $msg)
    {
        $this->field = $param;
        $this->message = $msg;
    }

    /**
     * @return mixed
     */
    public function __toString()
    {
        return $this->message;
    }

    /**
     * @return mixed
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

}


/**
 * Class RequestParamValidator
 * @package Quantum
 */
class RequestParamValidator
{

    /**
     * @var ValueTree
     */
    public $validations;
    /**
     * @var array
     */
    public $error_messages;

    /**
     * @var bool
     */
    public $success;

    /**
     * FormParam constructor.
     * @param $type
     * @param $visibleName
     * @param $paramName
     * @param $defaultValue
     * @param array $options
     */
    public function __construct()
    {
        $this->validations = new_vt();
        $this->error_messages = array();
        $this->last_error_messages = array();
        $this->errors = array();
        $this->success = false;
    }

    /**
     * @param $param
     * @param $type
     * @return QString
     */
    public function getValidationId($param, $type)
    {
        return $param."_".$type;
    }

    /**
     * @param $type
     * @param $paramName
     * @param array $options
     * @return RequestParamValidator
     */
    public function add($type, $params, $options = array())
    {
        foreach ((array)$params as $param)
        {
            $this->validations->add(new Validation($param, $type, (array)$options));
        }

        return $this;
    }

    /**
     * @param $type
     * @param $paramName
     * @param array $options
     * @return RequestParamValidator
     */
    public function addIfPostHasNonEmpty($type, $paramName, $options = array())
    {
        $request = Request::getInstance();

        if (!$request->isPost())
            return $this;

        if ($request->hasEmptyParam($paramName))
            return $this;

        $this->add($type, $paramName, $options);

        return $this;
    }

    /**
     * @param $type
     * @param $paramName
     * @param array $options
     * @return RequestParamValidator
     */
    public function addIfPostHas($type, $paramName, $options = array())
    {
        $request = Request::getInstance();

        if (!$request->isPost())
            return $this;

        if ($request->isMissingParam($paramName))
            return $this;

        $this->add($type, $paramName, $options);

        return $this;
    }

    /**
     * @param $method
     * @param $args
     * @return RequestParamValidator
     */
    public function __call($method, $args)
    {
        $key = qs($method)->toLowerCase()->getText();
        $param = $args[0];
        return $this->add($key, $param);
    }

    /**
     * @return bool
     */
    public function validatePost()
    {
        $request = Request::getInstance();

        if (!$request->isPost())
        {
            $this->success = false;
            $this->addErrorMessage('request_method', 'Invalid Request Method');
            return $this->success;
        }

        return $this->processValidations($request->getRawPost());
    }

    /**
     * @return bool
     */
    public function validateGet()
    {
        $request = Request::getInstance();

        if (!$request->isGet())
        {
            $this->success = false;
            $this->addErrorMessage('request_method', 'Invalid Request Method');
            return $this->success;
        }

        return $this->processValidations($request->getRawGet());
    }

    /**
     * @return bool
     */
    public function validatePut()
    {
        $request = Request::getInstance();

        if (!$request->isPut())
        {
            $this->success = false;
            $this->addErrorMessage('request_method', 'Invalid Request Method');
            return $this->success;
        }

        return $this->processValidations($request->getRawPut());
    }

    /**
     * @param $data
     * @return bool
     */
    public function validateData($data)
    {
        $this->success = $this->processValidations($data);
        return $this->success;
    }


    /**
     * @param $param
     * @return bool
     */
    public function isRequired($param)
    {
        foreach ($this->validations as $validation)
        {
            if ($validation->key == $param && $validation->type == 'required')
                return true;
        }

        return false;
    }



    /**
     * @param $callback
     * @param null $data
     */
    public function onPostSuccess($callback, $data = null)
    {
        $request = Request::getInstance();

        if (!$request->isPost())
        {
            $this->success = false;
            return;
        }

        if ($this->validatePost())
        {
            $callback($request, $data);
        }

    }

    /**
     * @param $callback
     * @param null $data
     */
    public function onGetSuccess($callback, $data = null)
    {
        $request = Request::getInstance();

        if (!$request->isGet())
        {
            $this->success = false;
            return;
        }

        if ($this->validateGet())
        {
            $callback($request, $data);
        }
    }


    /**
     * @param $callback
     * @param null $data
     */
    public function onPostError($callback, $data = null)
    {
        $request = Request::getInstance();

        if (!$request->isPost())
        {
            $this->success = false;
            return;
        }

        if (!$this->validatePost())
        {
            $callback($this->error_messages, $data = null);
        }
    }

    /**
     * @param $callback
     * @param null $data
     */
    public function onGetError($callback, $data = null)
    {
        $request = Request::getInstance();

        if (!$request->isGet())
        {
            $this->success = false;
            return;
        }

        if (!$this->validateGet())
        {
            $callback($this->error_messages, $data = null);
        }
    }

    /**
     * @return boolean
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param $param
     * @return bool|array
     */
    public function getErrorsFor($param)
    {
        if (!array_key_exists($param, $this->errors))
            return false;

        return $this->errors[$param];
    }

    /**
     * @return ValueTree
     */
    public function getErrorsValueTree()
    {
        return new_vt($this->errors);
    }

    /**
     * @return array
     */
    public function getAllErrorMessages()
    {
        return $this->error_messages;
    }

    /**
     * @return array
     */
    public function getLastErrorMessages()
    {
        return $this->last_error_messages;
    }

    /**
     * @return int
     */
    public function getErrorsCount()
    {
        return count($this->error_messages);
    }

    /**
     * @return Result
     */
    public function getResult()
    {
        if (!$this->hasErrors())
            return Result::ok();

        return Result::fail('One or more errors were found');
    }



    /**
     * @return QString
     */
    public function getErrorsHtml()
    {
        $txt = qs();

        foreach ($this->error_messages as $key => $msg)
        {
            $line = qs($msg);

            if ($key > 0)
                $line = qs()->withHtmlLineBreak()->append($line);

            $txt = $txt->append($line);
        }

        return $txt->getText();
    }

    /**
     * @return bool
     */
    public function failed()
    {
        return !$this->validated();
    }

    /**
     * @return bool
     */
    public function validated()
    {
        return $this->success;
    }

    /**
     * @param $rules
     * @return $this
     */
    public function rules($rules)
    {
        if (empty($rules))
            return $this;

        foreach ($rules as $param => $types)
        {
            $validations = qs($types)->explode('|');
            $options = array();

            foreach ($validations as $validation)
            {
                if (qs($validation)->contains(':'))
                {
                    $options    = qs($validation)->fromFirstOccurrenceOf(':')->explode(',');
                    $validation = qs($validation)->upToFirstOccurrenceOf(':')->getText();
                }

                $this->add($validation, $param, $options);
            }

        }

        return $this;
    }

    /**
     * @param $rules
     * @return bool
     */
    public function post($rules)
    {
        $this->rules($rules);

        return $this->validatePost();
    }

    /**
     * @param $rules
     * @return bool
     */
    public function get($rules)
    {
        $this->rules($rules);

        return $this->validateGet();
    }

    /**
     * @param $key
     * @param $error_msg
     */
    private function addErrorMessage($key, $error_msg)
    {
        $this->error_messages[] = $error_msg;

        $this->last_error_messages[$key] = $error_msg;

        $this->errors[$key][] = new ValidationError($key, $error_msg);
    }

    /**
     * @return bool
     */
    public function processValidations($data)
    {
        qm_profiler_start('RequestParamValidator::processValidations');

        if (empty($this->validations))
        {
            qm_profiler_stop('RequestParamValidator::processValidations');
            $this->success = true;
            return $this->success;
        }

        foreach ($this->validations as $validation)
        {
            qm_profiler_start('RequestParamValidator\Validate::'.$validation->type);
            $key  = $validation->key;
            $type = $validation->type;

            $param = new_vt($data)->get($key, '');
            $paramName = qs($key)->humanize();

            switch ($type)
            {
                case "email":
                    $success = qs($param)->isEmail();
                    if (!$success)
                        $this->addErrorMessage($key, "Invalid email on field: " . $paramName);
                    break;
                case "url":
                    $success = qs($param)->isUrl();
                    if (!$success)
                        $this->addErrorMessage($key, "Invalid url on field: " . $paramName);
                    break;
                case "number":
                    $success = qs($param)->isNumber();
                    if (!$success)
                        $this->addErrorMessage($key, $paramName. " must be a number");
                    break;
                case "alpha":
                    $success = qs($param)->isAlpha();
                    if (!$success)
                        $this->addErrorMessage($key, "Text must be alpha numeric on field: " . $paramName);
                    break;
                case "alphanum":
                    $success = qs($param)->isAlphanumeric();
                    if (!$success)
                        $this->addErrorMessage($key, "Text must be alpha numeric on field: " . $paramName);
                    break;
                case "alphanumspace":
                    $success = qs($param)->isAlphaNumericWithSpaces();
                    if (!$success)
                        $this->addErrorMessage($key, "Text must be alpha numeric on field: " . $paramName);
                    break;
                case "alphanumspacedash":
                    $success = qs($param)->isAlphaNumericWithSpaceAndDash();
                    if (!$success)
                        $this->addErrorMessage($key, "Text must be alpha numeric on field: " . $paramName);
                    break;
                case "date":
                    $success = qs($param)->isDate();
                    if (!$success)
                        $this->addErrorMessage($key, "Invalid date on field: " . $paramName);
                    break;
                case "ip":
                    $success = qs($param)->isIp();
                    if (!$success)
                        $this->addErrorMessage($key, "Invalid IP on field: " . $paramName);
                    break;
                case "ipv6":
                    $success = qs($param)->isIpV6();
                    if (!$success)
                        $this->addErrorMessage($key, "Invalid IPV6 on field: " . $paramName);
                    break;
                case "lowercase":
                    $success = qs($param)->isLowerCase();
                    if (!$success)
                        $this->addErrorMessage($key, "Text must be lowercase on field: " . $paramName);
                    break;
                case "uppercase":
                    $success = qs($param)->isUpperCase();
                    if (!$success)
                        $this->addErrorMessage($key, "Text must be uppercase on field: " . $paramName);
                    break;
                case "json":
                    $success = qs($param)->isJson();
                    if (!$success)
                        $this->addErrorMessage($key, "Text must be JSON on field: " . $paramName);
                    break;
                case "size":
                    $success = qs($param)->length() == $validation->getOptions()[0];
                    if (!$success)
                        $this->addErrorMessage($key, "Characters count should be: " . $validation->getOptions()[0] . " at field:". $paramName);
                    break;
                case "max":
                    $success = qs($param)->length() <= $validation->getOptions()[0];
                    if (!$success)
                        $this->addErrorMessage($key, "Characters count must be lower than " . $validation->getOptions()[0] . " at field: ". $paramName);
                    break;
                case "min":
                    $success = qs($param)->length() >= $validation->getOptions()[0];
                    if (!$success)
                        $this->addErrorMessage($key, "Characters count must be greater than " . $validation->getOptions()[0] . " at field: ". $paramName);
                    break;
                case "not_contains":
                    $success = !qs($param)->contains($validation->getOptions()[0]);
                    if (!$success)
                        $this->addErrorMessage($key, "Text must not contain " . $validation->getOptions()[0] . " at field: ". $paramName);
                    break;
                case "contains":
                    $success = qs($param)->contains($validation->getOptions()[0]);
                    if (!$success)
                        $this->addErrorMessage($key, "Text must contain " . $validation->getOptions()[0] . " at field: ". $paramName);
                    break;
                case "starts_with":
                    $success = qs($param)->startsWith($validation->getOptions()[0]);
                    if (!$success)
                        $this->addErrorMessage($key, "Text must start with: " . $validation->getOptions()[0] . " at field: ". $paramName);
                    break;
                case "ends_with":
                    $success = qs($param)->endsWith($validation->getOptions()[0]);
                    if (!$success)
                        $this->addErrorMessage($key, "Text must end with: " . $validation->getOptions()[0] . " at field: ". $paramName);
                    break;
                case "base64":
                    $success = qs($param)->isBase64();
                    if (!$success)
                        $this->addErrorMessage($key, "Text must be base 64 at field: " . $paramName);
                    break;
                case "integer":
                    $success = qs($param)->isInteger();
                    if (!$success)
                        $this->addErrorMessage($key, "Text must be an integer at field: " . $paramName);
                    break;
                case "decimal":
                    $success = qs($param)->isDecimal();
                    if (!$success)
                        $this->addErrorMessage($key, "Text must be decimal at field: " . $paramName);
                    break;
                case "float":
                    $success = qs($param)->isFloat();
                    if (!$success)
                        $this->addErrorMessage($key, "Text must be decimal at field: " . $paramName);
                    break;
                case "double":
                    $success = qs($param)->isDouble();
                    if (!$success)
                        $this->addErrorMessage($key, "Text must be a double precission float at field: " . $paramName);
                    break;
                case "numeric":
                    $success = qs($param)->isNumeric();
                    if (!$success)
                        $this->addErrorMessage($key, "Text must be decimal at field: " . $paramName);
                    break;
                case "equal":
                    $success = qs($param)->equals($validation->getOptions()[0]);
                    if (!$success)
                        $this->addErrorMessage($key, "Text must be equal to: " . $validation->getOptions()[0] . " at field:". $paramName);
                    break;
                case "hex":
                    $success = qs($param)->isHexadecimal();
                    if (!$success)
                        $this->addErrorMessage($key, "Text must be hex at field:" . $paramName);
                    break;
                case "rgb":
                    $success = qs($param)->isRgbColor();
                    if (!$success)
                        $this->addErrorMessage($key, "Text must be an RGB color at field: " . $paramName);
                    break;
                case "accepted":
                    $success = qs($param)->toBoolean();
                    if (!$success)
                        $this->addErrorMessage($key, "Must accept field: " . $paramName);
                    break;
                case "empty":
                    $success = qs($param)->isEmpty();
                    if (!$success)
                        $this->addErrorMessage($key, "Must be empty: " . $paramName);
                    break;
                case "required":
                    $success = qs($param)->isNotEmpty();
                    if (!$success)
                        $this->addErrorMessage($key, "Required field: " . $paramName);
                    break;
                case "filled":
                    $success = qs($param)->isNotEmpty();
                    if (!$success)
                        $this->addErrorMessage($key, "Must filled field: " . $paramName);
                    break;
                case "password":
                    $success = PasswordPolicy::isValid($param);
                    if (!$success)
                        $this->addErrorMessage($key, PasswordPolicy::getPolicyDescription());
                    break;
                case "string":
                    $success = is_string($param);
                    if (!$success)
                        $this->addErrorMessage($key, 'Text must be a string at field: '.$paramName);
                    break;
                case "mac":
                    $success = qs($param)->isMacAddress();
                    if (!$success)
                        $this->addErrorMessage($key, 'Text must be a mac address at field: '.$paramName);
                    break;
                case "domain":
                    $success = qs($param)->isDomain();
                    if (!$success)
                        $this->addErrorMessage($key, 'Text must be a mac address at field: '.$paramName);
                    break;
                case "uuid":
                    $success = qs($param)->isUuid();
                    if (!$success)
                        $this->addErrorMessage($key, 'Text must be a uuid at field: '.$paramName);
                    break;

                case "unique":

                    $options = $validation->getOptions();

                    $column = $key;

                    $modelName =  qs($options[0])->upperCaseFirst()->toStdString();

                    if (isset($options[1]))
                        $column = $options[1];

                    $previous = $modelName::find(array('conditions' => array("$column = ?", $param)));

                    $success  =  empty($previous);

                    if (!$success)
                        $this->addErrorMessage($key, "Another $modelName is already using ".$param. ' as '.qs($key)->humanize()->toLowerCase());
                    break;

                case "unique_but_ignore":

                    $options = $validation->getOptions();

                    $column = $key;

                    if (count($options) <= 1)
                        throw new \InvalidArgumentException("Missing second parameter (ignore_id) on unique_but_ignore validation for ".$key);

                    $modelName =  qs($options[0])->humanize()->toStdString();

                    $ignore_id = $options[1];

                    if (isset($options[2]))
                        $column = $options[2];

                    $previous = $modelName::find(array('conditions' => array("$column = ?", $param)));

                    if (!empty($previous))
                    {
                        if ($previous->id != $ignore_id)
                            $this->addErrorMessage($key, "Another $modelName is already using ".$param. ' as '.qs($key)->humanize()->toLowerCase());
                    }

                    break;
                default:
                    throw new \RuntimeException("Validation not found:".$type);
                    break;

            }

            qm_profiler_stop('RequestParamValidator\Validate::'.$validation->type);
        }

        $this->success = empty($this->error_messages);

        if (!$this->success)
        {
            $this->flashPostedParamsToRegistry();
        }

        qm_profiler_stop('RequestParamValidator::processValidations');

        return $this->success;
    }


    /**
     *
     */
    public function flashPostedParamsToRegistry()
    {
        $request = Request::getInstance();

        if (!$request->isPost())
            return;

        $registry = RuntimeRegistry::getInstance();

        $params = $request->getPostParams();

        if (!$params) {
            return;
        }

        foreach ($params as $key => $param)
        {
            $registry->set('postedparam_'.$key, $param);
        }
    }
}