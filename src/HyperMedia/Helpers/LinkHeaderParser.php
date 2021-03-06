<?php

namespace HyperMedia\Helpers;

class LinkHeaderParser
{
    /**
     * @param $link_header_value
     * @return mixed
     */
    static function parse($link_header_value){
        if(is_null($link_header_value) || empty($link_header_value))
            return [];
        $links = self::parseToArray($link_header_value);

        return self::processURIAndDeleteBraces($links);
    }

    static function parseToArray($link_values)
    {
        // parse these
        if (is_string($link_values)) $link_values = array($link_values);

        $links = array();

        foreach ($link_values as $link_value) {

            $state = 'link_start';
            $link = array();
            $uri = $param_name = $param_value = '';

            $link_value = trim($link_value);

            $len = strlen($link_value);

            foreach (str_split($link_value) as $chr) {
                switch ($state) {
                    case 'link_start':
                        if ('<' == $chr) {
                            $state = 'uri_start';
                            $uri = '';
                            $link = array();
                        }
                        break;
                    case 'uri_start':
                        if ('>' == $chr) {
                            $state = 'uri_end';
                            $link['uri'] = $uri;

                        } else $uri .= $chr;
                        break;
                    case 'uri_end':
                        if (';' == $chr) {
                            $state = 'param_start';
                        }
                        break;
                    case 'param_start':
                        if (!self::_is_whitespace($chr)) {
                            $state = 'param_name_start';
                            $param_name = $chr;
                        } else continue;
                        break;
                    case 'param_name_start':
                        if ('=' == $chr) {
                            $state = 'param_name_end';
                        } else $param_name .= $chr;
                        break;
                    case 'param_name_end':
                        $param_value = '';
                        if ('"' == $chr) {
                            $state = 'quoted_param_value_start';
                        } else $state = 'param_value_start';
                        break;
                    case 'quoted_param_value_start':
                        if ('"' == $chr) $state = 'quoted_param_value_end';
                        else $param_value .= $chr;
                        break;
                    case 'quoted_param_value_end':
                        if (';' == $chr) $state = 'param_value_end';
                        elseif (',' == $chr) $state = 'end_of_params';
                        break;
                    case 'param_value_start':
                        if (';' == $chr) $state = 'param_value_end';
                        elseif (',' == $chr) $state = 'end_of_params';
                        else $param_value .= $chr;
                        break;
                    case 'param_value_end':
                        $state = 'param_start';
                        $link[$param_name] = $param_value;
                        break;
                    case 'end_of_params':
                        $state = 'link_start';
                        $link[$param_name] = $param_value;
                        if (isset($link['rel'])) {
                            $rels = $link['rel'];
                            unset($link['rel']);
                            foreach (explode(' ', $rels) as $rel) $links[$rel][] = $link;
                        } else $links[] = $link;
                }
            }

            if ('link_start' != $state) {
                $link[$param_name] = $param_value;
                if (isset($link['rel'])) {
                    $rels = $link['rel'];
                    unset($link['rel']);
                    foreach (explode(' ', $rels) as $rel) $links[$rel][] = $link;
                } else $links[] = $link;

            }
        }

        return $links;
    }

    static function _is_whitespace($chr)
    {
        return in_array($chr, array(" ", "\t", "\n", "\r", "\0", "\x0B"));
    }

    static function removeBraces($uri){
        return preg_replace('/{.+}/','',$uri);
    }

    /**
     * @param $links
     * @return mixed
     */
    public static function processURIAndDeleteBraces($links)
    {
        foreach ($links as $rel => $link) {
            $formatted = $link[0];
            $formatted['uri'] = self::removeBraces($link[0]['uri']);
            $link[0] = $formatted;
            $links[$rel] = $link;
        }
        return $links;
    }
}
