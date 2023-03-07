<?php

namespace App\Jobs\Jenkins;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Models\User;
use App\Models\Workspace;
use App\Services\JenkinsService;

/// Creates/Updates Workspace Folder in Jenkins when Dashboard Workspace created
class CreateOrganization implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly Workspace $workspace,
        public readonly User $workspaceAdmin
    ) { }

    public function handle()
    {
        $url = $this->GetJobUrl();
        $url .= $this->GetJobParams($this->workspace, $this->workspaceAdmin);

        return app(JenkinsService::class)->GetHttpClient()->post($url);
    }

    // Job url that contains Jenkins-DSL Plugin Action
    public function GetJobUrl() : string
    {
        return implode('/', [
            config('jenkins.host'),
            'job',
            config('jenkins.seeder'),
            'buildWithParameters?',
        ]);
    }

    // parameter references: https://github.com/TalusStudio/TalusWebBackend-JenkinsDSL/blob/master/Jenkinsfile
    public function GetJobParams(Workspace $workspace, User $workspaceAdmin) : string
    {
        $githubSetting = $workspace->githubSetting;
        $tfSetting = $workspace->appleSetting;

        return implode('&', [
            // dashboard-auth related
            "DASHBOARD_TOKEN={$workspaceAdmin->createApiToken('jenkins-key')}",

            // source control related
            "GIT_USERNAME={$githubSetting->organization_name}",
            "GIT_ACCESS_TOKEN={$githubSetting->personal_access_token}",
            "GITHUB_TOPIC={$githubSetting->topic_name}",
            "REPO_OWNER={$githubSetting->organization_name}",

            // delivery platform related
            "TESTFLIGHT_USERNAME={$tfSetting->usermail}",
            "TESTFLIGHT_PASSWORD={$tfSetting->app_specific_pass}",
        ]);
    }
}
