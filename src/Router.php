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
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \Psr\Http\Server\RequestHandlerInterface;

/**
 * Router
 */
class Router extends \Sofi\Base\Initialized
{    
    protected $controllerParse = false;
    protected $controllerPaths = ['app/controllers'];
    protected $Routes = [];

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
    
    public function init($params = array())
    {
        parent::init($params);
        
        if ($this->controllerParse) {
            $this->Collection = (new ControllerParser($this->controllerPaths))->parse();
        }
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
        if ($this->Collection == null) {
            $this->Collection = new RouteCollection();
        }

        return $this->Collection;
    }

    public function setCollection(RouteCollection $Collection = null)
    {
        if ($Collection != null && $Collection instanceof RouteCollection) {
            $this->Collection = $Collection;
        }

        return $this;
    }

    /**
     * 
     * @param \Sofi\Router\Context $Context
     * @return Route
     * @throws \Sofi\Base\exceptions\RouteNotFound
     */
    public function dispatch(\Sofi\HTTP\message\ServerRequest $Request) : Route
    {
        $method = $this->methodByName($Request->getMethod());

        foreach ($this->Collection->routesByMethod($method) as $Route) {
            if ($Route->parse($Request->getUri()->getPath())) {
                return $Route->setMethod($method);
            }
        }

        if ($routeNotFound = $this->Collection->routeNotFound()) {
            return (new Route)->alias('pageNotFound')->addAction($routeNotFound);
        } else {
            throw new \Sofi\Base\exceptions\RouteNotFound($Request->getUri());
        }
    }

}
