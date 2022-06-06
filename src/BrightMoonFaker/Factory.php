<?php

namespace BrightMoonFaker;

use BrightMoon\Model;

class Factory
{
    protected $faker;

    protected $model;

    public function __construct(Faker $faker)
    {
        $this->faker = $faker;
    }

    public function setModel(Model $model)
    {
        $this->model = $model;

        return $this;
    }

    public function make($number)
    {
        $results = [];

        for ($i = 0; $i < $number; $i++) {
            $results[] = $this->define();
        }

        return $results;
    }

    public function create($number)
    {
        if (is_null($this->model) || ! is_subclass_of($this->model, Model::class)) {
            return [];
        }

        $data = $this->make($number);

        $result = call_user_func_array([$this->model, 'insert'], [$data]);

        return $result ? $data : [];
    }

    public function define()
    {
        //
    }
}
