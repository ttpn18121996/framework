<?php

namespace BrightMoon\Database\Relations;

trait HasRelationships
{
    protected $relations = [];

    public function hasMany($related, $foreignKey = null, $localKey = null)
    {
        return new HasMany($this, $related, $foreignKey, $localKey);
    }

    public function belongsTo($related, $foreignKey = null, $ownerKey = null, $relation = null)
    {
        return new BelongsTo($this, $related, $foreignKey, $ownerKey, $relation);
    }
}
