<?php

declare(strict_types=1);

namespace App\Core;

class Container
{
    private static $instance = null;
    private $bindings = [];
    private $instances = [];
    private $resolved = [];

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function bind($abstract, $concrete = null)
    {
        if ($concrete === null) {
            $concrete = $abstract;
        }
        $this->bindings[$abstract] = $concrete;
        return $this;
    }

    public function singleton($abstract, $concrete = null)
    {
        $this->bind($abstract, $concrete);
        $this->instances[$abstract] = true;
        return $this;
    }

    public function instance($abstract, $instance)
    {
        $this->resolved[$abstract] = $instance;
        $this->instances[$abstract] = true;
        return $this;
    }

    public function make($abstract, array $parameters = [])
    {
        if (isset($this->resolved[$abstract]) && isset($this->instances[$abstract])) {
            return $this->resolved[$abstract];
        }

        $concrete = $this->bindings[$abstract] ?? $abstract;

        if ($concrete instanceof \Closure) {
            $object = $concrete($this, $parameters);
        } elseif (is_string($concrete)) {
            $object = $this->build($concrete, $parameters);
        } elseif (is_object($concrete)) {
            $object = $concrete;
        } else {
            throw new \Exception("Unable to resolve [{$abstract}]");
        }

        if (isset($this->instances[$abstract])) {
            $this->resolved[$abstract] = $object;
        }

        return $object;
    }

    public function has($abstract)
    {
        return isset($this->bindings[$abstract]) || isset($this->resolved[$abstract]);
    }

    private function build($class, array $parameters = [])
    {
        $reflector = new \ReflectionClass($class);

        if (!$reflector->isInstantiable()) {
            throw new \Exception("Class [{$class}] is not instantiable");
        }

        $constructor = $reflector->getConstructor();

        if ($constructor === null) {
            return new $class();
        }

        $args = $this->resolveDependencies($constructor->getParameters(), $parameters);

        return $reflector->newInstanceArgs($args);
    }

    private function resolveDependencies(array $parameters, array $primitives = [])
    {
        $args = [];
        foreach ($parameters as $param) {
            $type = $param->getType();
            $name = $param->getName();

            if (isset($primitives[$name])) {
                $args[] = $primitives[$name];
                continue;
            }

            if ($type === null || $type->isBuiltin()) {
                if ($param->isDefaultValueAvailable()) {
                    $args[] = $param->getDefaultValue();
                } else {
                    throw new \Exception("Cannot resolve primitive parameter [{$name}]");
                }
                continue;
            }

            $typeName = $type->getName();
            $args[] = $this->make($typeName);
        }
        return $args;
    }
}
