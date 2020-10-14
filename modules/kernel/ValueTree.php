<?php

namespace Quantum;
use ArrayAccess;
use Countable;
use IteratorAggregate;

/**
 * Class ValueTree
 * @package Quantum
 */
class ValueTree implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * @var array
     */
    public $_properties;
    /**
     * @var
     */
    private $_isUnmutable;
    /**
     * @var
     */
    private $_isLocked;

    /**
     * @var
     */
    private $_callbacks;

    /**
     * ValueTree constructor.
     * @param array $properties
     * @param bool $shouldBeLocked
     * @param bool $shouldBeUnmutable
     */
    public function __construct($properties = array(), $shouldBeLocked = false, $shouldBeUnmutable = false)
    {
        if (!is_array($properties) && !is_object($properties))
            throw new \InvalidArgumentException("Invalid argument for creation of ValueTree");

        if (is_object($properties))
            $properties = (array) $properties;

        $this->replaceProperties($properties);

        $this->setUnmutable($shouldBeLocked);

        $this->setLocked($shouldBeUnmutable);
    }

    /**
     * @param $propertyName
     * @param $value
     * @return bool
     */
    public function set($propertyName, $value)
    {
        if ($this->isUnmutable() && $this->has($propertyName))
            throw new \InvalidArgumentException("Unmutable property change to  key '" . $propertyName . "' detected");

        if (!$this->isLocked())
        {
            $this->_properties[$propertyName] = $value;
            $this->runCallbacks();

            return true;
        }

        return false;
    }

    /**
     * @param $shouldBeUnmutable
     */
    public function setUnmutable($shouldBeUnmutable)
    {
        $this->_isUnmutable = $shouldBeUnmutable;
    }

    /**
     * @param $shouldBeLocked
     */
    public function setLocked($shouldBeLocked)
    {
        $this->_isLocked = $shouldBeLocked;
    }

    /**
     * @return mixed
     */
    public function isUnmutable()
    {
        return $this->_isUnmutable;
    }

    /**
     * @return mixed
     */
    public function isLocked()
    {
        return $this->_isLocked;
    }

    /**
     * @param $propertyName
     * @param bool $fallbackValue
     * @return bool|mixed
     */
    public function get($propertyName, $fallbackValue = false)
    {
        if (isset($this->_properties[$propertyName]))
            return $this->_properties[$propertyName];
        return $fallbackValue;
    }

    /**
     * @param $index
     * @return mixed
     */
    public function getByIndex($index)
    {
        assert(is_numeric($index));
        return $this->_properties[$index];
    }

    /**
     * @param $propertyName
     * @return bool
     */
    public function has($propertyName)
    {
        return array_key_exists($propertyName, $this->_properties);
    }

    /**
     * @param $propertyName
     */
    public function remove($propertyName)
    {
        if ($this->isUnmutable())
            throw new \InvalidArgumentException("Impossible to remove properties unto unmutable valuetree");

        if (isset($this->_properties[$propertyName]) && !$this->isLocked())
        {
            if (is_object($this->_properties[$propertyName]) && (method_exists($this->_properties[$propertyName], '__destruct')))
                $this->_properties[$propertyName]->__destruct();

            unset($this->_properties[$propertyName]);

            $this->runCallbacks();
        }
    }

    /**
     * @param $value
     */
    public function removeByValue($value)
    {
        if ($this->isUnmutable())
            throw new \InvalidArgumentException("Impossible to remove properties by value unto unmutable valuetree");
        if ($this->isLocked())
            return;
        if (($key = array_search($value, $this->_properties)) !== false) {
            unset($this->_properties[$key]);
            $this->runCallbacks();
        }
    }

    /**
     * @return int
     */
    public function getNumChildren()
    {
        return count($this->_properties);
    }

    /**
     * @return int
     */
    public function size()
    {
        return $this->getNumChildren();
    }

    /**
     * @return bool|mixed
     */
    public function first()
    {
        if (count($this->_properties)) {
            $this->reset();
            return $this->current();
        }
        return false;
    }

    /**
     * @return bool|mixed
     */
    public function last()
    {
        if (count($this->_properties))
            return end($this->_properties);
        return false;
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->getNumChildren();
    }

    /**
     * @return \ArrayIterator|\Traversable
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->_properties);
    }

    /**
     * @return mixed
     */
    public function current()
    {
        return current($this->_properties);
    }

    /**
     * @param $element
     */
    public function add($element)
    {
        array_push($this->_properties, $element);
        $this->runCallbacks();
    }

    /**
     * @return mixed
     */
    public function next()
    {
        return next($this->_properties);
    }

    /**
     * @return mixed
     */
    public function prev()
    {
        return prev($this->_properties);
    }

    /**
     * calls PHP reset on internal array
     * @return mixed
     */
    public function reset()
    {
        return reset($this->_properties);
    }

    /**
     * Clears all properties
     */
    public function clear()
    {
        $this->_properties = array();

        $this->runCallbacks();
    }

    /**
     * Removes all properties, calling remove for each property
     */
    public function removeAllProperties()
    {
        foreach ($this->_properties as $key => $property) {
            $this->remove($key);
        }
    }

    /**
     * @return false|string
     */
    public function toJson()
    {
        $result = json_encode($this->_properties);
        if (false === $result) {
            throw new \InvalidArgumentException('Unable to serialize value.');
        }
        return $result;
    }


    /**
     * @param $propertyName
     * @return false|int|string
     */
    public function indexOf($propertyName)
    {
        $index = array_search($propertyName, array_keys($this->_properties));
        return $index;
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->_properties;
    }

    /**
     * @return array
     */
    public function toStdArray()
    {
        return $this->getProperties();
    }

    /**
     * @param $method
     * @param $args
     * @return bool|mixed|void
     */
    public function __call($method, $args)
    {
        $key = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", substr($method, 3)));
        switch (substr($method, 0, 3)) {
            case 'get':
                if (!$this->has($key))
                    throw new \InvalidArgumentException("Automagically ValueTree Property not found: " . $key . "::" . $method.$this->toCommaSeparatedString());
                $result = $this->get($key, isset($args[0]) ? $args[0] : null);
                break;
            case 'set':
                $result = $this->set($key, isset($args[0]) ? $args[0] : null);
                break;
            case 'uns':
                $result = $this->remove($key);
                break;
            case 'has':
                $result = $this->has($key);
                break;
        }
        return $result;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return (empty($this->_properties));
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->_properties[$offset] = $value;
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->_properties[$offset]);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->_properties[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        return isset($this->_properties[$offset]) ? $this->_properties[$offset] : null;
    }

    /**
     * @param null $data
     * @param array $objects
     * @return array|string
     */
    public function debug($data = null, &$objects = array())
    {
        if (is_null($data)) {
            $hash = spl_object_hash($this);
            if (!empty($objects[$hash])) {
                return 'RECURSION';
            }
            $objects[$hash] = true;
            $data           = $this->getProperties();
        }
        $debug = array();
        foreach ($data as $key => $value) {
            if (is_scalar($value)) {
                $debug[$key] = $value;
            } elseif (is_array($value)) {
                $debug[$key] = $this->debug($value, $objects);
            } elseif ($value instanceof ValueTree) {
                $debug[$key . ' (' . get_class($value) . ')'] = $value->debug(null, $objects);
            }
        }
        return $debug;
    }

    /**
     * @return false|string
     */
    public function serialize()
    {
        return $this->toJson();
    }

    /**
     * @param $properties
     */
    public function setProperties($properties)
    {
        foreach ($properties as $key => $property) {
            $this->set($key, $property);
        }
    }

    /**
     * @param $properties
     */
    public function replaceProperties($properties)
    {
        if ($this->isUnmutable())
            throw new \InvalidArgumentException("Impossible to replace properties unto unmutable valuetree");
        if ($this->isLocked())
            return;
        $this->clear();
        $this->_properties = $properties;
        $this->runCallbacks();
    }

    /**
     * @param int $case
     */
    public function changeKeysCase($case = CASE_LOWER)
    {
        if ($this->isUnmutable())
            throw new \InvalidArgumentException("Impossible to change keys unto unmutable valuetree");
        if ($this->isLocked())
            return;
        $this->replaceProperties(array_change_key_case($this->_properties, $case));
        $this->runCallbacks();
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->getProperties();
    }

    /**
     * @return array
     */
    public function getArray()
    {
        return $this->all();
    }

    /**
     * @param $value
     * @return bool|false|int|string
     */
    public function getKeyFromValue($value)
    {
        if (($key = array_search($value, $this->_properties)) !== false) {
            return $key;
        }
        return false;
    }

    /**
     * @param $value
     * @return bool
     */
    public function hasValue($value)
    {
        $key = $this->getKeyFromValue($value);
        if (is_numeric($key))
            return true;
        return false;
    }

    /**
     * @param $paramKey
     * @return bool
     */
    function isMissingParam($paramKey)
    {
        return !$this->has($paramKey);
    }

    /**
     * @param $params
     */
    function mergeArray($params)
    {
        if ($this->isUnmutable())
            throw new \InvalidArgumentException("Impossible to merge unto unmutable valuetree");
        if ($this->isLocked())
            return;
        if (!is_array($params))
            throw new \RuntimeException("merge subject must be an array");
        $this->replaceProperties(array_merge($this->_properties, $params));
    }

    /**
     * @param ValueTree $otherTree
     */
    function mergeValueTree(ValueTree $otherTree)
    {
        $params = $otherTree->getProperties();
        $this->replaceProperties(array_merge($this->_properties, $params));
    }

    /**
     * Changes the internal array keys to uppercase
     */
    function changeKeysToUpperCase()
    {
        $this->replaceProperties(array_change_key_case($this->_properties, CASE_UPPER));
    }

    /**
     * Changes the internal array keys to lowercase
     */
    function changeKeysToLowerCase()
    {
        $this->replaceProperties(array_change_key_case($this->_properties, CASE_LOWER));
    }

    /**
     * @param $size
     * @param bool $preserve_keys
     * @return array
     */
    function chunk($size, $preserve_keys = false)
    {
        return array_chunk($this->_properties, $size, $preserve_keys);
    }

    /**
     * @param $column_key
     * @param null $index_key
     * @return array
     */
    function getColumnValues($column_key, $index_key = null)
    {
        return array_column($this->_properties, $column_key, $index_key);
    }

    /**
     * @return string
     */
    function toCommaSeparatedString()
    {
        return $this->implode(",");
    }

    /**
     * @param $glue
     * @return string
     */
    function implode($glue)
    {
        return implode($glue, $this->_properties);
    }

    /**
     * Calls php asort on internal array
     */
    function sortAlphabeticallyByValue()
    {
        asort($this->_properties);
    }

    /**
     *
     */
    function removeDuplicates()
    {
        $this->_properties = array_unique($this->_properties);
    }


    /**
     *
     */
    function clearCallbacks()
    {
        $this->_callbacks = array();
    }


    /**
     * @param $callback
     */
    function addCallback($callback)
    {
        if (!isset($this->_callbacks))
            $this->_callbacks = array();

        array_push($this->_callbacks, $callback);
    }


    /**
     *
     */
    private function runCallbacks()
    {
        if (empty($this->_callbacks))
            return;

        foreach ($this->_callbacks as $callback)
        {
            $callback($this);
        }
    }


    /**
     * @param $key
     * @param $valueKey
     * @return array
     */
    function toKeyPair($key, $valueKey)
    {
        $data = new ValueTree();

        foreach ($this->_properties as $property)
        {
            $data->set($property->$key, $property->$valueKey);

        }
        return $data->getProperties();
    }

    /**
     * @param $keys
     * @return ValueTree
     */
    function withChildrenKeys($keys)
    {
        $filtered_data = new_vt();
        foreach ($this->getProperties() as $datum) {
            $new_datum = new_vt();
            foreach ($keys as $key) {
                $new_datum->set($key, $datum[$key]);
            }
            $filtered_data->add($new_datum->getArray());
        }
        return $filtered_data;
    }


    /**
     * @param string $rootName
     * @param bool $addOpenTag
     * @param bool $addCdata
     * @return string
     */
    public function toXml($rootName = 'item', $addOpenTag = false, $addCdata = true)
    {
        $xml = ValueTreeToXml::toXml($this, $rootName, $addOpenTag, $addCdata);

        return $xml;
    }

    /**
     *
     */
    public function reverse()
    {
        $this->_properties = array_reverse($this->_properties);
    }

    public function pop()
    {
        return array_pop($this->_properties);
    }

    public function hasEqualParam($key, $valueToCompare)
    {
        if ($this->has($key))
        {
            return $this->get($key) == $valueToCompare;
        }

        return false;
    }

    public function hasExactParam($key, $valueToCompare)
    {
        if ($this->has($key))
        {
            return $this->get($key) === $valueToCompare;
        }

        return false;
    }


    public function getHash()
    {
        $f = '';
        foreach ($this->_properties as $key => $property)
        {
            if (!empty($property))
            {
                $f .= sha1($property);
            }
        }

        return sha1($property);
    }

    public function increment($key, $offset = 1, $initial_value = 0)
    {
        if (!$this->has($key))
        {
            $value = $initial_value+$offset;
            $this->set($key, $value);
            return $value;
        }

        $value = $this->get($key) + $offset;
        $this->set($key, $value);
        return $value;
    }

    public function decrement($key, $offset = 1, $initial_value = 0)
    {
        if (!$this->has($key))
        {
            $value = $initial_value-$offset;
            $this->set($key, $value);
            return $value;
        }

        $value = $this->get($key) - $offset;
        $this->set($key, $value);
        return $value;
    }

    public function lock()
    {
        $this->setLocked(true);
        return $this;
    }

    public function unlock()
    {
        $this->setLocked(false);
        return $this;
    }

    public function makeUnmutable()
    {
        $this->setUnmutable(true);
        return $this;
    }

    public function makeMutable()
    {
        $this->setUnmutable(false);
        return $this;
    }

}