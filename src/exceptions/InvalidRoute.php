<?php

namespace Sofi\Router\exceptions;

class InvalidRoute extends \Exception
{

    /**
     * @return string the name of this exception
     */
    public function getName()
    {
        return 'Invalid route Exception';
    }

}
