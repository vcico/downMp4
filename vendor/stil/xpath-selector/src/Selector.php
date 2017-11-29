<?php
namespace XPathSelector;

use DOMDocument;

class Selector extends Node
{
    public static function load($path)
    {
        $dom = new DOMDocument('1.0','utf-8');
        @$dom->load($path);
        return new self($dom);
    }

    public static function loadXML($xml)
    {
        $dom = new DOMDocument('1.0','utf-8');
        @$dom->loadXML($xml);
        return new self($dom);
    }

    public static function loadHTMLFile($html)
    {
        $dom = new DOMDocument('1.0','utf-8');
        @$dom->loadHTMLFile($html);
        return new self($dom);
    }

    public static function loadHTML($path)
    {
        $dom = new DOMDocument('1.0','utf-8');
        @$dom->loadHTML($path);
        return new self($dom);
    }
}
