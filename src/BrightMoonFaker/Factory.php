<?php

namespace BrightMoonFaker;

use BrightMoon\Model;

class Factory
{
    protected $faker;

    protected $model;

    protected $count;

    public function __construct(Faker $faker)
    {
        $this->faker = $faker;
    }

    /**
     * Thiết lập tên model cho factory.
     *
     * @param  string  $model
     * @return $this
     */
    public function setModel(string $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Thiết lập tổng số item cần khởi tạo.
     *
     * @param  int|null  $number
     * @return $this
     */
    public function count(?int $number)
    {
        if (! is_null($number) && $number > 0) {
            $this->count = $number;
        }

        return $this;
    }

    /**
     * Tạo dữ liệu mẫu theo tổng số item được thiết lập.
     *
     * @param  int  $number
     * @return array
     */
    public function make(int $number = 0): array
    {
        $results = [];

        if ($number <= 0) {
            if ($this->count <= 0) {
                return $results;
            }

            $number = $this->count;
        }

        for ($i = 0; $i < $number; $i++) {
            $results[] = empty($this->model) ? $this->define() : app($this->model, ['attributes' => $this->define()]);
        }

        return $results;
    }

    /**
     * Tạo và lưu dữ liệu mẫu vào database theo tổng số item được thiết lập.
     *
     * @param  int  $number
     * @return array
     */
    public function create($number)
    {
        if (is_null($this->model) || ! is_subclass_of($this->model, Model::class)) {
            return [];
        }

        $data = collect($this->make($number));

        $result = call_user_func_array([$this->model, 'insert'], [$data]);

        return $result ? $data : [];
    }

    public function define()
    {
        //
    }
}
