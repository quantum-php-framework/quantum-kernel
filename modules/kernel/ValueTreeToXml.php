<?php

namespace Quantum;

/**
 * Class ArrayToXml
 * @package Quantum
 */
class ValueTreeToXml
{
    #ifndef DOXYGEN_SHOULD_SKIP_THIS
    /**
     * @param $obj
     * @param string $node_name
     * @param null $wrapper
     * @param array $replacements
     * @param bool $add_header
     * @param array $header_params
     * @return string
     */
    public static function toXml($valueTree, $rootName = 'item', $addOpenTag = false, $addCdata = true)
    {
        $xml = '';
        if ($addOpenTag) {
            $xml .= '<?phpxml version="1.0" encoding="UTF-8"?>' . "\n";
        }
        if (!empty($rootName)) {
            $xml .= '<' . $rootName . '>' . "\n";
        }
        $xmlModel = new \SimpleXMLElement('<node></node>');
        $arrData  = $valueTree->getProperties();
        foreach ($arrData as $fieldName => $fieldValue) {
            if ($addCdata === true) {
                $fieldValue = "<![CDATA[$fieldValue]]>";
            } else {
                $fieldValue = $xmlModel->xmlentities($fieldValue);
            }
            $xml .= "<$fieldName>$fieldValue</$fieldName>" . "\n";
        }
        if (!empty($rootName)) {
            $xml .= '</' . $rootName . '>' . "\n";
        }
        return $xml;
    }
    #endif DOXYGEN_SHOULD_SKIP_THIS

}