<?php

namespace BrightMoonFaker;

class Generator
{
    protected $providers;

    public function addProvider($provider)
    {
        $this->providers[] = $provider;
    }

    public function generate($method, $parameters)
    {
        foreach ($this->providers as $provider) {
            if (method_exists($provider, $method)) {
                return call_user_func_array([app($provider), $method], $parameters);
            }
        }
    }
}
