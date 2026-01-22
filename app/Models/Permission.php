<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    protected $primaryKey = 'permission_id';
    protected $keyType = 'int';
    public $incrementing = true;
}
