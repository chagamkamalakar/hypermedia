<?php
/**
 * Created by PhpStorm.
 * User: kamal
 * Date: 5/11/15
 * Time: 4:33 PM
 */

namespace HyperMedia\Helpers;


class CamelCaseTocamel_case
{
    static function decamelize($camel, $glue = '_') {
        return strtolower(implode('_',preg_split('/([[:upper:]][[:lower:]]+)/', $camel, null, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY)));
    }
    static function splitCamelCase($str) {
        $words = preg_split('/(?<=\\w)(?=[A-Z])/', $str);
        return strtolower(implode('_',$words));
    }
}