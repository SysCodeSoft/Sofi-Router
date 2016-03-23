<?php

namespace Sofi\Router;

class RouteCollection
{

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
    function __construct(interfaces\ParserInterface $Parser = null)
    {
        $this->Parser = ($Parser === null) ? new Parser() : $Parser;
    }

    /**
     *
     * @param \Sofi\Router\Router $Router
     * @return \Sofi\Router\RouteCollection
     */
    function addRoute(Route $Route)
    {
        $this->collection[] = $Route;

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
     * @return RouteCollection
     */
    function route(
        $path = '/', $actions, $method = Router::ANY_METHOD, $name = null, array $filters = [], array $events = []
    )
    {
        if (is_array($path)) {
            foreach ($path as $p) {
                $this->collection[$name ?: count($this->collection)] 
                    = (new Route($this->Parser))
                    ->route($p, is_array($actions) ? $actions : [$actions], $method, $name, $filters, $events);
            }
        } else {
            $this->collection[$name ?: count($this->collection)] 
                = (new Route($this->Parser))
                ->route($path, is_array($actions) ? $actions : [$actions], $method, $name, $filters, $events);
        }

        return $this;
    }

    /**
     * @return \Generator
     */
    function routesByMethod($method = Router::ANY_METHOD)
    {
        foreach ($this->collection as $route) {
            if ($route->checkMethod($method)) {
                yield $route;
            }
        }
    }

}
