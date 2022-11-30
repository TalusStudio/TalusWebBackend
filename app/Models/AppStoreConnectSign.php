<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppStoreConnectSign extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'cert',
        'provision_profile',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
    ];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }
}