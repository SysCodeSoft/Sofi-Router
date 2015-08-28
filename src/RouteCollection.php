<?php

namespace Sofi\Router;

class RouteCollection {

    /**
     *
     * @var \Sofi\Router\Parser 
     */
    protected $Parser = null;
    
    /**
     *
     * @var \Sofi\Router\Route[] array 
     */
    protected $collection = [];
    
    /**
     * 
     * @param \Sofi\Router\interfaces\ParserInterface $Parser
     */
    function __construct(interfaces\ParserInterface $Parser = null) {
        $this->Parser = ($Parser === null) ? new Parser() : $Parser;
    }
    
    /**
     * 
     * @param \Sofi\Router\Router $Router
     * @return \Sofi\Router\RouteCollection
     */
    function addRoute(Router $Router) {
        $this->collection[] = $Router;
        
        return $this;
    }
    
    /**
     * Добавление роута
     * 
     * @param string $path URL path
     * @param function|object $actions
     * @param int $method HTTP method
     * @param string $name Alias route
     * @param type $filters
     * @param type $events
     * @return \Sofi\base\RouteCollection
     */
    function route(
        $path = '/', 
        array $actions = [], 
        $method = Router::ANY_METHOD,
        $name = '',
        array $filters = [],
        array $events = []
    ) {
        $this->collection[] 
                = (new Route($this->Parser))
                    ->route($path, $actions, $method, $name, $filters, $events);
        
        return $this;
    }
    
    
    /**
     * @return \Sofi\Router\Route Route
     */
    function routes() {
        foreach ($this->collection as $route) {
            yield $route;
        }
    }
    
    
    /**
     * @return \Sofi\Router\Route
     */
    function routesByMethod($method = Route::ANY_METHOD) {
        foreach ($this->routes() as $route) {
            if ($route->checkMethod($method)) {
                yield $route;
            }
        }
    }
    
}
