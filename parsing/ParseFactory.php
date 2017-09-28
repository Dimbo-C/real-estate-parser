<?php

require_once __DIR__ . "/../includes.php";

class ParseFactory {
    /**
     * @param $data array with data for parsers
     * @return bool|Parsable
     */
    public static function getSiteParser($data) {
        // determine what site is it
        $siteName = getSiteFromUrl($data["url"]);
        if (!$siteName) return false;

        // construct a classname to return
        $class = ucfirst($siteName) . "Parser";

        return new $class($data['url'], $data['lang']);
    }
}