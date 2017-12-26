<?php

namespace Sofi\Router;

/**
 * Контекст вызова
 * 
 * @property \Sofi\HTTP\message\Request   $Request
 * @property \Sofi\Router\Route          $Route
 * @property \Sofi\HTTP\message\Response  $Response
 * 
 */
class Context
{
    public $Request = null;
    public $Response = null;
    public $Route = null;
    public $Result = null;
    
    protected $runtimeData = [];

    function __construct()
    {
        $this->Request = \Sofi\HTTP\message\Request::createFromGlobals();
        $this->Response = new \Sofi\HTTP\message\Response();
    }

    public function requestAcceptPriority()
    {
        if (!empty($this->runtimeData['AcceptPriority']))
            return $this->runtimeData['AcceptPriority'];
        
        $Accept = explode(',', $this->Request->getHeader('Accept')[0]);
        $this->runtimeData['AcceptPriority'] = [];
        foreach ($Accept as $value) {
            $q = explode(';', $value);
            if (isset($q[1])) {
                $this->runtimeData['AcceptPriority'][10 * floatval(mb_substr($q[1], 2))][] = $q[0];
            } else {
                $this->runtimeData['AcceptPriority'][10][] = $q[0];
            }
        }

        return $this->runtimeData['AcceptPriority'];
    }

    public function prepareResult()
    {
        $Priority = $this->requestAcceptPriority();
        \Sofi\Base\Sofi::d($Priority);
        foreach ($this->Route->run() as $r) {
            $this->Result[] = (string) $r;
        }

        \Sofi\Base\Sofi::d($this->Result);
    }

    public function prepareRequest(\stdClass $Wrapper = null)
    {
        if ($this->Result != null) {
            $bodyS = $Context->Response->getBody();
            foreach ($this->Result as $key => $value) {
                $bodyS->write($value);
            }
            $body = $this->Layout
                    ->addContent($res)
                    ->render();
            $bodyS->write($body);
        }

        return false;
    }

}
