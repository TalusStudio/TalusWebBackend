<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\FtpService;
use Illuminate\Support\Str;

class FtpServiceProvider extends ServiceProvider
{
    public function register()
    {

    }

    public function boot()
    {
        $this->app->bind(FtpService::class, function($app) {
            return new FtpService(
                Str::of(config('filesystems.disks.ftp.host'))
                    ->explode('.')
                    ->slice(1)
                    ->prepend('http://www')
                    ->implode('.')
            );
        });
    }
}