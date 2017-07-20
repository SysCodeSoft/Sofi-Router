<?php

namespace Sofi\Router;

class Route
{

    protected $path = '/';
    protected $actions = [];
    protected $filters = [];
    protected $events = [];
    protected $name;
    protected $pattern = false;
    protected $param_names = [];
    
    protected $result = [];

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
        $this->path($path);

        $this->actions[$method] = $actions;

        $this->name = $name;
        $this->filters = $filters;
        $this->events = $events;

        return $this;
    }
    
    public function getName()
    {
        return $this->name;
    }

    public function path($path)
    {
        $this->path = $path;

        if (mb_strpos($this->path, '{') === false) {
            $this->pattern = false;
        } else {
            list($this->pattern, $this->param_names) = $this->Parser->getPattern($this->path);
        }

        return $this;
    }

    public function getPattern()
    {
        return [$this->pattern, $this->param_names];
    }

    public function filters()
    {
        foreach ($this->filters as $filter) {
            yield $filter;
        }
    }

    public function events($type)
    {
        if (isset($this->events[$type]) && is_array($this->events[$type]))
            foreach ($this->events[$type] as $event) {
                yield $event;
            }
    }

    public function addAction($action, $method = Router::ANY_METHOD)
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
                return $actions;
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

    public function run()
    {
        foreach ($this->events(Router::EVENT_BEFORE_ROUTE) as $event) {
            \Sofi\Base\Sofi::exec($event, ['route' => $this]);
        }
        
        /**
         * TODO Methods
         */
        $method = 
                \Sofi\Base\Sofi::app()->Router->methodByName(
                        \Sofi\Base\Sofi::app()->Request->getMethod()
                    );

        foreach ($this->actionsByMethod($method) as $action) {
            $this->result[] = \Sofi\Base\Sofi::exec($action, $this->params);
        }

        foreach ($this->events(Router::EVENT_AFTER_ROUTE) as $event) {
            \Sofi\Base\Sofi::exec($event, ['route' => $this, 'result' => $result]);
        }
        
        return $this->result;
    }

}
