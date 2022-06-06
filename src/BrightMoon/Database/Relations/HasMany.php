<?php

namespace BrightMoon\Database\Relations;

use BrightMoon\Model;

class HasMany
{
    protected $related;
    protected $foreignKey;
    protected $localKey;
    protected $parent;

    /**
     * Khởi tạo đối tượng.
     *
     * @param  \BrightMoon\Model  $parent
     * @param  string  $related
     * @param  string|null  $foreignKey
     * @param  string|null  $localKey
     * @return void
     */
    public function __construct(Model $parent, $related, $foreignKey = null, $localKey = null)
    {
        $this->parent = $parent;
        $this->related = $related;
        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;
    }

    /**
     * Lấy danh sách quan hệ.
     *
     * @param   
     * @return \BrightMoon\Support\Collection
     */
    public function get()
    {
        return $this->related::where($this->foreignKey, $this->parent->{$this->localKey})->get();
    }
}
