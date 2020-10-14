<?php

namespace Quantum;

/**
 * Class ArrayToXml
 * @package Quantum
 */
class ArrayToXml
{
    /**
     * @param $obj
     * @param string $node_name
     * @param null $wrapper
     * @param array $replacements
     * @param bool $add_header
     * @param array $header_params
     * @return string
     */
    public static function convert($obj, $node_name = 'node', $wrapper = null, $replacements=array(), $add_header = true, $header_params=array())
    {
        $xml = '';
        if($add_header)
            $xml .= self::generateHeader($header_params);
        if($wrapper!=null) $xml .= '<' . $wrapper . '>';
        if(is_object($obj))
        {
            $node_block = strtolower(get_class($obj));
            if(isset($replacements[$node_block])) $node_block = $replacements[$node_block];
            $xml .= '<' . $node_block . '>';
            $vars = get_object_vars($obj);
            if(!empty($vars))
            {
                foreach($vars as $var_id => $var)
                {
                    if(isset($replacements[$var_id])) $var_id = $replacements[$var_id];
                    $xml .= '<' . $var_id . '>';
                    $xml .= self::convert($var, $node_name, null, $replacements,  false, null);
                    $xml .= '</' . $var_id . '>';
                }
            }
            $xml .= '</' . $node_block . '>';
        }
        else if(is_array($obj))
        {
            foreach($obj as $var_id => $var)
            {
                if(!is_object($var))
                {
                    if (is_numeric($var_id))
                        $var_id = $node_name;
                    if(isset($replacements[$var_id])) $var_id = $replacements[$var_id];
                    $xml .= '<' . $var_id . '>';
                }
                $xml .= self::convert($var, $node_name, null, $replacements,  false, null);
                if(!is_object($var))
                    $xml .= '</' . $var_id . '>';
            }
        }
        else
        {
            $xml .= htmlspecialchars($obj, ENT_QUOTES);
        }

        if($wrapper!=null) $xml .= '</' . $wrapper . '>';

        return $xml;
    }

    /**
     *
     * xml header generator
     * @param array $params
     */
    public static function generateHeader($params = array())
    {
        $basic_params = array('version' => '1.0', 'encoding' => 'UTF-8');
        if(!empty($params))
            $basic_params = array_merge($basic_params,$params);

        $header = '<?phpxml';
        foreach($basic_params as $k=>$v)
        {
            $header .= ' '.$k.'='.$v;
        }
        $header .= ' ?>';
        return $header;
    }
}