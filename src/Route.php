<?php

namespace Sofi\Router;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \Psr\Http\Server\RequestHandlerInterface;

class Route  implements \Psr\Http\Server\MiddlewareInterface
{

    protected $path = '/';
    protected $actions = [];
    protected $name;
    protected $pattern = false;
    protected $param_names = [];
    
    protected $use_method = Router::ANY_METHOD;
    
    /**
     *
     * @var \Sofi\Router\Context
     */
    protected $Context;

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
     */
    function route($path = '/', array $actions = [], $method = Router::ANY_METHOD, $name = '')
    {
        $this->path($path);
        $this->actions[$method] = $actions;
        $this->name = $name;

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

    public function addAction($action, $method = Router::ANY_METHOD)
    {
        $this->actions[$method][] = $action;

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
    
    public function setMethod($method = Router::ANY_METHOD)
    {
        $this->use_method = $method;
        
        return $this;
    }

    public function setContext(Context $Context)
    {
        $this->Context = $Context;
        $Context->Route = $this;
        
        return $this;
    }
    
    public function getPath()
    {
        return $this->path;
    }

    public function run()
    {
        foreach ($this->actionsByMethod($this->use_method) as $action) {
            yield \Sofi\Base\Sofi::exec($action, is_array($this->params)?$this->params:[], ['Context' => $this->Context]);
        }
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        
    }

}
