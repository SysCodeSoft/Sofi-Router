<?php

namespace Sofi\Router;

class Route
{

    protected $path = '/';
    public $actions = [];
    public $filters = [];
    public $events = [];
    public $name;
    protected $pattern = false;
    protected $param_names = [];

    /**
     *
     * @var \Sofi\Router\Parser 
     */
    protected $Parser;
    public $params = false;

    public function __construct(interfaces\ParserInterface $Parser = null)
    {
        $this->Parser = ($Parser === null) ? new Parser() : $Parser;
    }

    /**
     * Set route params
     * 
     * @param string $path Path for parse
     * @param array $actions Array actions for route
     * @param int|string $method Request method
     * @param string $name Alias for route
     * @param array $filters Array callable filters
     * @param array $events Array callable events
     */
    function route(
    $path = '/', array $actions = [], $method = Router::ANY_METHOD, $name = '', array $filters = [], array $events = []
    )
    {
        $this->setPath($path);

        $this->actions[$method] = [$actions];

        $this->name = $name;
        $this->filters = $filters;
        $this->events = $events;

        return $this;
    }

    public function setPath($path)
    {
        $this->path = $path;

        if (!mb_strpos($this->path, '{')) {
            $this->pattern = false;
        } else {
            list($this->pattern, $this->param_names) = $this->Parser->getPattern($this->path);
        }
    }

    public function getPattern()
    {
        return [$this->pattern, $this->param_names];
    }

    public function addAction($action, $method = self::ANY_METHOD)
    {
        $this->actions[$method][] = $action;

        return $this;
    }

    public function addFilter($filter)
    {
        $this->filters[] = $filter;

        return $this;
    }

    public function addEvent($event, $action)
    {
        $this->events[$event][] = $action;

        return $this;
    }

    public function alias($name)
    {
        $this->name = $name;

        return $this;
    }

    public function parse($uri)
    {
        if ($this->pattern) {
            $this->params = $this->Parser->parse($uri, $this->getPattern());

            if ($this->params != false) {
                return $this;
            }
        } else {
            if ($uri == $this->path) {
                $this->params = [];

                return $this;
            }
        }

        return false;
    }

    function actionsByMethod($method)
    {
        foreach ($this->actions as $methods => $actions) {
            if (($methods == $method) || ($method & $methods) == $method) {
                foreach ($actions as $action) {
                    yield $action;
                }
            }
        }
    }

    public function checkMethod($method = Router::ANY_METHOD)
    {
        foreach ($this->actions as $methods => $action) {
            if (($methods == $method) || ($method & $methods) == $method) {
                return true;
            }
        }
        return false;
    }

}
