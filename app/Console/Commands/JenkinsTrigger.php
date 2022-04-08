<?php

namespace App\Console\Commands;

use App\Models\AppInfo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class JenkinsTrigger extends Command
{
    protected $signature = 'jenkins:trigger {appInfoID} {isWorkspace} {tfVersion}';
    protected $description = 'Trigger jenkins pipeline to build.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle() : void
    {
        $app = AppInfo::where('id', $this->argument('appInfoID'))->first();
        if ($app)
        {
            $url = config('jenkins.host').
                "/job/".
                config('jenkins.ws').
                "/job/{$app->project_name}/job/master/buildWithParameters?IS_WORKSPACE={$this->argument('isWorkspace')}&TF_BUILD_VERSION={$this->argument('tfVersion')}&APP_ID={$app->id}";

            echo 'Jenkins response code: ' . Http::withBasicAuth(config('jenkins.user'), config('jenkins.token'))->post($url)->status();
        }
    }
}
