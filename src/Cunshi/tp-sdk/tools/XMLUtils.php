<?php

namespace Cunshi\TpSdk\tools;

class XMLUtils
{
    /**
     * xml to array
     *
     * @param $xml
     * @return mixed
     */
    public static function xml_to_array($xml)
    {
        libxml_disable_entity_loader(true); // 禁止引用外部xml实体
        $xml_string = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        return json_decode(json_encode($xml_string), true);
    }

    /**
     * array to xml
     *
     * @param $arr
     * @return string
     */
    public static function array_to_xml($arr)
    {
        $xml = "<xml>";

        foreach ($arr as $key => $val) {
            if (is_array($val)) {
                $xml .= "<" . $key . ">" . self::array_to_xml($val) . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }
}