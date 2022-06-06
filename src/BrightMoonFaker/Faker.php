<?php

namespace BrightMoonFaker;

class Faker
{
    protected $locale = 'vi_VN';
    protected $providers = ['Address', 'Internet', 'Person', 'PhoneNumber'];
    protected $generator;

    public function __construct(Generator $generator, string $locale = 'vi_VN')
    {
        $this->generator = $generator;
        foreach ($this->providers as $provider) {
            $this->generator->addProvider("BrightMoonFaker\\Providers\\{$this->locale}\\{$provider}");
        }
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    public function __call($method, $parameters)
    {
        return $this->generator->generate($method, $parameters);
    }
}
