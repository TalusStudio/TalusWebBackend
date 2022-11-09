<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Laravel\Sanctum\HasApiTokens;

class Workspace extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasApiTokens;

    protected $fillable = [
        'name',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [

    ];

    protected $with = [
        'AppStoreConnectSetting',
        'AppleSetting',
        'GithubSetting',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function apps()
    {
        return $this->hasMany(AppInfo::class);
    }

    public function inviteCodes()
    {
        return $this->hasMany(WorkspaceInviteCode::class);
    }

    public function appStoreConnectSetting()
    {
        return $this->hasOne(AppStoreConnectSetting::class);
    }

    public function appleSetting()
    {
        return $this->hasOne(AppleSetting::class);
    }

    public function githubSetting()
    {
        return $this->hasOne(GithubSetting::class);
    }

    public function createApiToken()
    {
        return $this->createToken('api-key')->plainTextToken;
    }
}
