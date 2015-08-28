<?php

namespace Sofi\Router;

class Parser implements interfaces\ParserInterface {

    public $templates = [
        '([а-яА-Я0-9a-zA-Z\-_]+)',
        'int' => '([0-9]+)',
        'float' => '([0-9\,]+)',
        'name' => '([а-яА-Яa-zA-Z\-_]+)',
        'latin' => '([a-zA-Z\-_]+)',
        'cirilic' => '([а-яА-Я\-_]+)'
    ];
    
    function getPattern($path) {
        $names = [];

        $pattern = '^';
        $t = explode('{', str_replace('/', '\/', $path));
        foreach ($t as $val) {
            if ($pos = mb_strpos($val, '}')) {
                $templ = $this->templates[0];
                $type = null;

                $param = substr($val, 0, $pos);

                if ($pos2 = mb_strpos($param, ':')) {
                    $type = mb_substr($param, $pos2 + 1);
                    $names[] = mb_substr($param, 0, $pos2);
                    if (isset($this->templates[$type])) {
                        $templ = $this->templates[$type];
                    }
                    $val = $templ . mb_substr($val, $pos + 1, $pos2);
                } else {
                    $names[] = $param;
                    $val = $templ . mb_substr($val, $pos + 1);
                }
            }
            $pattern .= $val;
        }
        $pattern .= '$';

        return [ $pattern, $names];
    }

    function parse($uri, array $pattern) {
        $params = [];
        
        if ($pattern[0] != '') {
            $rs = [];

            if (mb_eregi($pattern[0], $uri, $rs)) {
                array_shift($rs);
                reset($rs);
                foreach ($pattern[1] as $key => $value) {
                    $params[$value] = current($rs);
                    next($rs);
                }
                
                return $params;
            }
        }

        return false;
    }

}
