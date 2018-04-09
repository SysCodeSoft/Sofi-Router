<?php

namespace Sofi\Router;

/**
 * Контекст вызова
 * 
 * @property \Sofi\HTTP\message\ServerRequest  $Request
 * @property \Sofi\HTTP\message\Response       $Response
 * @property \Sofi\Router\Router               $Router
 * @property \Sofi\Router\Route                $Route
 * @property \Sofi\mvc\web\Page                $Page
 * 
 */
class Context extends \stdClass implements \Psr\Http\Server\RequestHandlerInterface
{

    public $Request = null;
    public $Response = null;
    
    public $Router = null;
    
    public $Route = null;
    public $Wrapper = null;
    
    public $Result = null;
    public $Page;
    
    function __construct(Router $Router, callable $wrapper)
    {
        $this->Request = \Sofi\HTTP\message\ServerRequest::createFromGlobals();
        $this->Response = new \Sofi\HTTP\message\Response();
        $this->Router = $Router;
        $this->Wrapper = $wrapper;
        $this->Page = new \Sofi\mvc\web\Page();
    }

    public function prepareResult()
    {
        $Priority = $this->Request->requestAcceptPriority();
        foreach ($this->Route->run() as $r) {
            $this->Result[] = (string) $r;
        }
    }

    public function prepareResponse(callable $Wrapper = null)
    {
        if ($this->Result != null) {
            $bodyS = $this->Response->getBody();
            $content = '';
            foreach ($this->Result as $key => $value) {
                $content .= $value;
            }
            if (is_callable($Wrapper)) {
                $content = $Wrapper($content);
            }
            $bodyS->write($content);
        }

        return false;
    }

    public function dispatch()
    {
        $this->Route = $this->Router->dispatch($this->Request);
        $this->Route->setContext($this);

        $this->prepareResult();
        
        $this->Wrapper->Context = $this;
        $this->prepareResponse($this->Wrapper ?? null);
    }

    public function handle(\Psr\Http\Message\ServerRequestInterface $request): \Psr\Http\Message\ResponseInterface
    {
        
    }

}
