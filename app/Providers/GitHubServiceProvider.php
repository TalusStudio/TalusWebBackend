<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;

use App\Services\GitHubService;

class GitHubServiceProvider extends ServiceProvider
{
    public function register() : void
    {
        // Note: GitHub Service must be resolved before using GitHub api!
        $this->app->singleton(GitHubService::class, function(Application $app) {
            return new GitHubService();
        });
    }

    public function boot() : void
    {

    }
}
