<?php

namespace App\Actions\Api\Jenkins\Post;

use Lorisleiva\Actions\Concerns\AsAction;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

use App\Models\AppInfo;
use App\Services\JenkinsService;
use App\Http\Requests\Jenkins\StopJobRequest;

class AbortJob
{
    use AsAction;

    private AppInfo $app;
    private JenkinsService $service;

    public function handle(StopJobRequest $request) : RedirectResponse|JsonResponse
    {
        $buildNumber = $request->validated('build_number');

        $url = "/job/{$this->app->project_name}/job/master/{$buildNumber}/stop";
        $response =  $this->service->PostResponse($url);
        $responseCode = $response->jenkins_status;
        $isResponseSucceed = $responseCode == Response::HTTP_OK;

        $flashMessage = ($isResponseSucceed)
            ? "<b>{$this->app->project_name}</b>, Build: <b>{$buildNumber}</b> aborted!"
            : "{$this->app->project_name}, Build: {$buildNumber} could not aborted! Error Code: {$responseCode}";

        if ($request->expectsJson())
        {
            return response()->json([
                'status' => $responseCode
            ]);
        }

        if ($isResponseSucceed)
        {
            return back()->with('success', $flashMessage);
        }

        return back()->withErrors($flashMessage);
    }

    public function authorize(StopJobRequest $request) : bool
    {
        $this->service = new JenkinsService($request);
        $this->app = AppInfo::find($request->validated('id'));

        return $request->expectsJson()
            ? $this->service->GetTargetWorkspaceId() === $this->app->workspace_id
            : Auth::user()->can('abort job') && $this->service->GetTargetWorkspaceId() === $this->app->workspace_id;
    }
}