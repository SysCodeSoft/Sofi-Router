<?php

namespace Sofi\Router\interfaces;

interface ParserInterface {
       
    function getPattern($path);

    function parse($url, array $pattern);

}
