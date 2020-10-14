<?php


namespace Quantum;

/**
 * Class SpeciallyHandledParameter
 * @package Quantum
 */
class SpeciallyHandledParameter
{
    /**
     * SpeciallyHandledParameter constructor.
     * @param $key
     * @param $callable
     */
    public function __construct($key, $callable)
    {
        $this->key = $key;
        $this->callable = $callable;
    }

    /**
     * @return mixed
     */
    public function callback()
    {
        return call_user_func($this->callable);
    }
}


/**
 * Class AutoForm
 * @package Quantum
 */
class AutoForm
{
    /**
     * AutoForm constructor.
     * @param ActiverecordModel $model
     * @param \AdminFormElementsFactory $factory
     * @param array $excluded_params
     * @param array $special_parameters
     */
    public function __construct(ActiverecordModel $model, \AdminFormElementsFactory $factory, $excluded_params = array(), $special_parameters = array())
    {
        $this->model = $model;
        $this->factory = $factory;
        $this->excluded_params = $excluded_params;
        $this->special_params = array();

        $this->setSpecialParams($special_parameters);
    }

    /**
     * @param $special_parameters
     */
    public function setSpecialParams($special_parameters)
    {
        foreach ($special_parameters as $key => $callable)
        {
            $this->setParamHandler($key, $callable);
        }
    }

    /**
     * @param $params
     */
    public function setExcludedParams($params)
    {
        $this->excluded_params = $params;
    }

    /**
     * @param $key
     * @param $callable
     */
    public function setParamHandler($key, $callable)
    {
        $special_param = new SpeciallyHandledParameter($key, $callable);
        $this->special_params[$key] = $special_param;
    }

    /**
     * @return Form
     */
    public function createEmpty()
    {
        $form = new Form($this->factory);
        return $form;
    }

    /**
     * @return Form
     */
    public function create()
    {
        $form = $this->createEmpty();

        $attributes = $this->model->attributes();

        foreach ($attributes as $key => $attribute)
        {

            if (!in_array($key, $this->excluded_params))
            {
                if (!array_has($this->special_params, $key))
                {
                    $form->text(qs($key)->humanize()->toStdString(), $key, $attribute);
                }
                else
                {
                    $specialParam = $this->special_params[$key];
                    $form->custom(qs($key)->humanize()->toStdString(), $key, $specialParam->callback());
                }

            }
        }

        return $form;
    }

    /**
     * @param $allowed_params
     * @return Form
     */
    public function createWithAllowedParams($allowed_params)
    {
        $attributes = array_keys($this->model->attributes());
        $excluded_params = array_diff($attributes, $allowed_params);

        $this->setExcludedParams($excluded_params);

        return $this->create();

    }

    /**
     * @param ActiverecordModel $model
     * @param \AdminFormElementsFactory $factory
     * @param array $excluded_params
     * @param array $special_parameters
     * @return Form
     */
    public static function createForm(ActiverecordModel $model, \AdminFormElementsFactory $factory, $excluded_params = array(), $special_parameters = array())
    {
        $form = new AutoForm($model, $factory, $excluded_params, $special_parameters);
        return $form->create();
    }

}