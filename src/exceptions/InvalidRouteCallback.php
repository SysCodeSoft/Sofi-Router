<?php
namespace Sofi\Router\exceptions;

class InvalidRouteCallback extends \Sofi\base\exceptions\Exception
{

    /**
     * @return string the name of this exception
     */
    public function getName()
    {
        return 'Invalid route Exception';
    }

}
