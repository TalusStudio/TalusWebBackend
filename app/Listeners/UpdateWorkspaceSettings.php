<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Auth;

use App\Events\WorkspaceChanged;
use App\Jobs\Jenkins\CreateOrganization;

class UpdateWorkspaceSettings
{
    public function handle(WorkspaceChanged $event) : void
    {
        $workspace = $event->workspace;
        $request = $event->request;
        $validated = $request->safe();

        $workspace->fill($validated->only([ 'name' ]));

        // AppStoreConnect
        $appStoreConnectSetting = $workspace->appStoreConnectSetting()->firstOrCreate();
        if ($request->hasFile('private_key')) {
            $appStoreConnectSetting->fill([ 'private_key' => $validated->private_key->get() ]);
        }
        $appStoreConnectSetting->fill($validated->only([
            'issuer_id',
            'kid',
        ]));

        // AppleSetting
        $appleSetting = $workspace->appleSetting()->firstOrCreate();
        $appleSetting->fill($validated->only([
            'usermail',
            'app_specific_pass',
        ]));

        // GithubSetting
        $githubSetting = $workspace->githubSetting()->firstOrCreate();
        $githubSetting->fill([
            'organization_name' => empty($workspace->githubSetting->organization_name)
                ? $request->validated('organization_name')
                : $workspace->githubSetting->organization_name,
        ]);
        $githubSetting->fill($validated->only([
            'personal_access_token',
            'template_name',
            'topic_name',
            'public_repo',
            'private_repo',
        ]));
        $githubSetting->save();

        CreateOrganization::dispatchIf(
            (
                $workspace->save()
                || $appStoreConnectSetting->save()
                || $appleSetting->save()
                || $githubSetting->save()
            ),
            $workspace,
            Auth::user(),
        );
    }
}
