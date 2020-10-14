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

}