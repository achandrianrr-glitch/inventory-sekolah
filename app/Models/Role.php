<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $primaryKey = 'role_id';
    protected $keyType = 'int';
    public $incrementing = true;
}
