<?php

namespace BrightMoon\Database\Factories;
use BrightMoon\Support\Str;

trait HasFactory
{
    protected static $namespace = 'Database\\Factories';

    public static function factory($count = 0)
    {
        $factory = static::newFactory() ?? static::factoryForModel(get_called_class());

        return $factory->count($count);
    }

    protected static function newFactory()
    {
        //
    }

    protected static function factoryForModel(string $modelName)
    {
        $appNamespace = app()->getNamespace();

        $factoryNameResolved = Str::of($modelName)
            ->after($appNamespace.'Models\\')
            ->prepend(static::$namespace.'\\')
            ->append('Factory')
            ->toString();

        return app($factoryNameResolved)->setModel($modelName);
    }
}
