<?php

namespace Quantum;

/**
 * Class ActiverecordModel
 * @package Quantum
 */
class ActiverecordModel extends \ActiveRecord\Model
{
    /**
     * @param $key
     * @param $value
     */
    public function setNonStoredProperty($key, $value)
    {
        $key = $this->getNonStoredPropertyKeyPrefix($key);

        \QM::register($key, $value);
    }

    /**
     * @param $key
     * @param string $fallback
     * @return mixed
     */
    public function getNonStoredProperty($key, $fallback = '')
    {
        $key = $this->getNonStoredPropertyKeyPrefix($key);

        return \QM::registry ($key, $fallback);
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function getCached($key, $value)
    {
        if (RuntimeRegistry::has($this->getNonStoredPropertyKeyPrefix($key)))
        {
            return $this->getNonStoredProperty($key);
        }

        $this->setNonStoredProperty($key, $value);

        return $value;
    }


    /**
     * @param $key
     * @return string
     */
    private function getNonStoredPropertyKeyPrefix($key)
    {
        $prefix = get_class($this);
        $key = $prefix.'_'.$this->getNonStoredId().'_'.$key;

        return $key;
    }

    /**
     * @return mixed|string
     */
    public function getNonStoredId()
    {
        if (isset($this->uuid)&& !empty($this->uuid))
            return $this->uuid;

        if (!empty($this->id))
            return $this->id;

        $id = Uuid::v4();

        $prefix = get_class($this);
        $key = $prefix.'_'.$id.'_'.'non_stored_id_';

        return $key;
    }


    /**
     * @return Output
     */
    public function getOutput()
    {
        return Output::getInstance();
    }

    public static function getAllAsKeyPair($id='id', $name='name')
    {
        $types = new_vt(self::all());
        $data = new_vt();

        foreach ($types as $type)
        {
            $data->set($type->$id, $type->$name);
        }

        return $data;
    }

    //ObjectMeta
    public function setMeta($key, $value)
    {
        $meta = ObjectMeta::find_by_object_uuid_and_key($this->uuid, $key);

        if (empty($meta))
        {
            $meta = new ObjectMeta();
            $meta->object_uuid = $this->uuid;
            $meta->key = $key;
            $meta->value = $value;
            $meta->save();
        }
        else
        {
            $meta->value = $value;
            $meta->save();
        }
    }

    public function getMeta($key, $fallback = null)
    {
        $meta = ObjectMeta::find_by_object_uuid_and_key($this->uuid, $key);

        if (!empty($meta))
            return $meta->value;

        return $fallback;
    }

    public function hasMeta($key)
    {
        $meta = ObjectMeta::find_by_object_uuid_and_key($this->uuid, $key);

        return !empty($meta);
    }

    public function deleteMeta($key)
    {
        $meta = ObjectMeta::find_by_object_uuid_and_key($this->uuid, $key);

        if (!empty($meta))
            $meta->delete();
    }

    public function getAllMeta()
    {
        $metas = ObjectMeta::find_all_by_object_uuid($this->uuid);

        $data = array();

        foreach ($metas as $meta)
        {
            $data[$meta->key] = $meta->value;
        }

        return $data;
    }

    public function getMetaForm(\AdminFormElementsFactory $factory, $excluded_params = array(), $special_params = array())
    {
        $form = new Form($factory);

        $attributes = $this->getAllMeta();

        foreach ($attributes as $key => $attribute)
        {
            if (!in_array($key, $excluded_params))
            {
                if (!array_has($special_params, $key))
                {
                    $form->text(qs($key)->humanize()->toStdString(), $key, $attribute);
                }
                else
                {
                    $callback = $special_params[$key];
                    $form->custom(qs($key)->humanize()->toStdString(), $key, call_user_func($callback));
                }
            }
        }

        return $form;
    }

    public function getAutoForm(\AdminFormElementsFactory $factory, $excluded_params = array(), $special_params = array())
    {
        $form = $this->initAutoForm($factory);
        $form->setExcludedParams($excluded_params);
        $form->setSpecialParams($special_params);
        return $form->create();
    }

    public function getAutoFormWithAllowedParams(\AdminFormElementsFactory $factory, $allowed_params = array())
    {
        $form = $this->initAutoForm($factory);
        return $form->createWithAllowedParams($allowed_params);
    }

    public function initAutoForm(\AdminFormElementsFactory $factory)
    {
        $form = new AutoForm($this, $factory);
        return $form;
    }



}