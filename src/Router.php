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
    const ANY_METHOD = 254;
    const EVENT_BEFORE_ROUTE = 0;
    const EVENT_AFTER_ROUTE = 1;

    /**
     *
     * @var \Sofi\Router\Executer $Executer
     */
    public $Executer;

    /**
     * Route collections
     * 
     * @var RouteCollection[]
     */
    protected $Collection;

    /**
     * 
     * @param \Sofi\Router\Executer $ExecuterClass
     */
    function __construct(\Sofi\Router\Executer $ExecuterClass = null)
    {
        if ($ExecuterClass == null) {
            $this->Executer = new Executer();
        } else {
            $this->Executer = $ExecuterClass;
        }
    }

    public function collection(RouteCollection $Collection)
    {
        $this->Collection = $Collection;

        return $this;
    }

    public function dispatch(\ServerRequestInterface $Request, \ResponseInterface $Response)
    {
        $method = $Request->getMethod();

        foreach ($this->Collection->routesByMethod($method) as $Route) {
            if ($Route->parse($Request->getUri()->getPath())) {
                foreach ($Route->filters() as $filter) {
                    $Response = $this->Executer->exec($filter, [$Request, $Response]);
                    if (!$Response) {
                        return $Response;
                    }
                }

                foreach ($Route->events(self::EVENT_BEFORE_ROUTE) as $event) {
                    $this->Executer->exec($event, ['route' => $Route]);
                }

                foreach ($Route->actionsByMethod($method) as $action) {
                    $this->result[] = $this->Executer->exec($action, $Route->params);
                }

                foreach ($Route->events(self::EVENT_AFTER_ROUTE) as $event) {
                    $this->Executer->exec($event, ['route' => $Route, 'result' => $result]);
                }

                return $this;
            }
        }


        throw new \Sofi\Router\exceptions\RouteNotFound($uri);
    }

}
