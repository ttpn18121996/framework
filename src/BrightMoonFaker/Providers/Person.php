<?php

namespace BrightMoonFaker\Providers;

class Person
{
    const FEMALE = 0;
    const MALE = 1;
    const GENDER = [
        self::FEMALE,
        self::MALE,
    ];

    public function person()
    {
        $gender = static::GENDER[rand(0, 1)];

        return [
            'gender' => $gender,
            'name' => $this->name($gender),
        ];
    }

    public function name($gender = -1)
    {
        if ($gender == static::MALE) {
            $name = $this->nameMales[rand(0, count($this->nameMales) - 1)];
            $middleName = $this->middleNameMales[rand(0, count($this->middleNameMales) - 1)];
            $surname = $this->surnames[rand(0, count($this->surnames) - 1)];

            return "{$surname} {$middleName} {$name}";
        } elseif ($gender == static::FEMALE) {
            $name = $this->nameFemales[rand(0, count($this->nameFemales) - 1)];
            $middleName = $this->middleNameFemales[rand(0, count($this->middleNameFemales) - 1)];
            $surname = $this->surnames[rand(0, count($this->surnames) - 1)];

            return "{$surname} {$middleName} {$name}";
        }

        $names = [
            $this->name(static::MALE),
            $this->name(static::FEMALE),
        ];

        return $names[rand(0, 1)];
    }
}
