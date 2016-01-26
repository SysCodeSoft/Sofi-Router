<?php

namespace Sofi\Router\exceptions;

class RouteNotFound extends \Exception
{

    /**
     * @return string the name of this exception
     */
    public function getName()
    {
        return 'Route not found Exception';
    }

}
