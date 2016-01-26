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

/**
 * Router
 */
class Router
{

    const ANY_METHOD = 510;
    const EVENT_BEFORE_ROUTE = 0;
    const EVENT_AFTER_ROUTE = 1;

    /**
     *
     * @var \Sofi\Router\Executer $Executer
     */
    public $Executer;

    /**
     * Current collection
     * @var type 
     */
    protected $current = 0;

    /**
     * All collections
     * 
     * @var RouteCollection[]
     */
    protected $Collections = [];
    public $result = [];

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
        $this->current++;
        $this->Collections[$this->current] = $Collection;

        return $this;
    }

    public function dispatch($uri, $method = Router::ANY_METHOD)
    {
        $this->result = [];

        if (!empty($this->Collections[$this->current])) {
//            $routes = $this->Collections[$this->current]->routesByMethod($method);
            foreach ($this->Collections[$this->current]->routesByMethod($method) as $Route) {
                if ($Route->parse($uri)) {
                    foreach ($Route->filters as $filter) {
                        if (!$this->Executer->exec($filter)) {
                            return false;
                        }
                    }

                    if (isset($Route->events[self::EVENT_BEFORE_ROUTE])) {
                        $this->Executer->exec(
                                $Route->events[self::EVENT_BEFORE_ROUTE], ['route' => $Route]
                        );
                    }

                     foreach ($Route->actionsByMethod($method) as $actions) {
                        foreach ($actions as $action) {
                            $this->result[] = $this->Executer->exec($action, $Route->params);
                        }
                    }

                    if (isset($Route->events[self::EVENT_AFTER_ROUTE])) {
                        $this->Executer->exec(
                                $Route->events[self::EVENT_AFTER_ROUTE], [
                            'route' => $Route,
                            'result' => $result
                                ]
                        );
                    }
                    return $this;
                }
            }
        }

        throw new \Sofi\Router\exceptions\RouteNotFound($uri);
    }

}
