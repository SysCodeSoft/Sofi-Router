<?php

namespace Sofi\Router;

class ControllerParser
{

    public $paths = [];

    public function __construct($paths = [])
    {
        $this->paths = $paths;
    }

    function parse()
    {
        $collection = new RouteCollection();

        foreach ($this->paths as $path) {
            foreach (glob(BASE_PATH . $path . "/*.php") as $file) {
                $fileName = explode('/', $file);
                $fileName = $fileName[count($fileName) - 1];
                $nm = str_replace('/', '\\', $path);
                $class = $nm . '\\' . substr($fileName, 0, strpos($fileName, '.'));

                try {
                    $ref = new \ReflectionClass($class);
                    foreach ($ref->getMethods() as $method) {
                        if (false != ($doc = $method->getDocComment())) {
                            preg_match_all("/@sofiRoute(.*);/", $doc, $matches);
                            if (!empty($matches[1][0])) {
                                $rules = explode(' ', $matches[1][0]);
                                $pathR = $rules[1];
                                $action = $class . '@' . $method->name;
                                
                                $collection->route($pathR, [$action]);
                            }
                        }
                    }
                } catch (\ReflectionException $ex) {
//                    echo $ex->getMessage();
                }
            }
        }

        return $collection;
    }

}
