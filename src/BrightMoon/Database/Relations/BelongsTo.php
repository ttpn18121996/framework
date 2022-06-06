<?php

namespace BrightMoon\Database\Relations;

use BrightMoon\Model;

class BelongsTo
{
    protected $related;
    protected $foreignKey;
    protected $localKey;
    protected $child;

    /**
     * Khởi tạo đối tượng.
     *
     * @param  \BrightMoon\Model  $child
     * @param  string  $related
     * @param  string|null  $foreignKey
     * @param  string|null  $ownerKey
     * @param  string|null  $relation
     * @return void
     */
    public function __construct(Model $child, $related, $foreignKey = null, $ownerKey = null, $relation= null)
    {
        $this->child = $child;
        $this->related = $related;
        $this->foreignKey = $foreignKey;
        $this->ownerKey = $ownerKey;
        $this->relation = $relation;
    }

    /**
     * Lấy thông tin quan hệ bảng cha.
     *
     * @return mixed
     */
    public function get()
    {
        return $this->related::where($this->ownerKey, $this->child->{$this->foreignKey})->first();
    }
}
