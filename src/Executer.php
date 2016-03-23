<?php

namespace Sofi\Router;

class Executer
{

    public function exec($action, array $params = [])
    {
        // Маршрут замыкание
        if ($action instanceof \Closure) {
            return call_user_func_array($action, $params);
        
        // 
        } elseif (is_callable($action)) {
            return $action(...array_values($params));
            
        // Маршрут задан строкой
        } elseif (is_string($action)) {
            if (mb_strpos($action, '{')) {
                foreach ($params as $key => $val) {
                    $action = str_replace('{' . $key . '}', $val, $action);
                }
            }

            $callback = explode('@', $action);

            if (class_exists($callback[0])) {
                $class = new $callback[0]();

                if (method_exists($class, 'init')) {
                    call_user_func([$class, 'init'], []);
                }

                if (method_exists($class, $callback[1])) {
                    return call_user_func_array([$class, $callback[1]], $params);
                } else {
                    throw new exceptions\InvalidRouteCallback('Bad method callback ' . $action);
                }
            } else {
                throw new exceptions\InvalidRouteCallback('Bad class callback ' . $action);
            }

            throw new exceptions\InvalidRouteCallback('Bad callback ' . $action);
            //  Маршрут задан массивом
        } elseif (is_array($action)) {
            // TODO Маршрут массивом
        }
    }

}
