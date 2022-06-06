<?php

namespace BrightMoonFaker\Providers;

class Address
{
    public function address()
    {
        return implode(', ', $this->addressDetail());
    }

    public function addressDetail()
    {
        return [
            'province' => $this->province(),
            'city' => $this->city(),
        ];
    }

    public function city()
    {
        return $this->cities[rand(0, count($this->cities) - 1)];
    }

    public function province()
    {
        return $this->provinces[rand(0, count($this->provinces) - 1)];
    }
}
