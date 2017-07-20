<?php

/**
 * Router
 * 
 * @category  SofiFramework
 * @package   SofiFW
 * @author    Max Trifonov <mp.trifonov@gmail.com>
 * @copyright 2014-2015 SysCode Soft Ltd
 * @license   http://www.sofi.syscode.ru/license/ MIT Licence
 * @link      http://www.sofi.syscode.ru/ 
 */

namespace Sofi\Router;

use Sofi\Router\Route;
use Psr\Http\Message\ServerRequestInterface as ServerRequestInterface;
use Psr\Http\Message\ResponseInterface as ResponseInterface;

/**
 * Router
 */
class Router
{

    const HEAD = 2;
    const GET = 4;
    const POST = 8;
    const PUT = 16;
    const DELETE = 32;
    const OPTIONS = 64;
    const PATCH = 128;
    const AJAX = 256;
    const ANY_METHOD = 254;
    // *****************************
    const EVENT_BEFORE_ROUTE = 0;
    const EVENT_AFTER_ROUTE = 1;

    /**
     * Route collections
     * 
     * @var RouteCollection[]
     */
    protected $Collection;
    public $result;

    /**
     * 
     * @param \Sofi\Router\Executer $ExecuterClass
     */
    function __construct(RouteCollection $Collection = null)
    {
        if ($Collection == null) {
            $Collection = new RouteCollection();
        }

        $this->setCollection($Collection);
    }

    function methodByName($name = 'GET')
    {
        switch ($name) {
            case 'GET':
                return self::GET;
            case 'POST':
                return self::POST;

            default:
                break;
        }
    }

    public function getCollection()
    {
        return $this->Collection;
    }

    public function setCollection(RouteCollection $Collection = null)
    {
        if ($Collection != null && $Collection instanceof RouteCollection) {
            $this->Collection = $Collection;
        }

        return $this;
    }

    public function dispatch(ServerRequestInterface $Request, ResponseInterface $Response)
    {
        $method = $this->methodByName($Request->getMethod());
        $result = [];
                
        foreach ($this->Collection->routesByMethod($method) as $Route) {
            if ($Route->parse($Request->getUri()->getPath())) {
                
                foreach ($Route->filters() as $filter) {
                    $Res = \Sofi\Base\Sofi::exec($filter, [$Request, $Response]);
                    if ($Res instanceof ResponseInterface) {
                        return $Res;
                    } elseif (!$Res) {
                        return $Response;
                    }
                }

                $result[] = $Route;
            }
        }

        if ($result != []) {
            return $result;
        }

        throw new \Sofi\Base\exceptions\RouteNotFound($Request->getUri());
    }

}
