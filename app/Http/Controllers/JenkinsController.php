<?php

namespace App\Http\Controllers;

use App\Models\AppInfo;

use App\Http\Requests\Jenkins\GetJobRequest;
use App\Http\Requests\Jenkins\BuildRequest;
use App\Http\Requests\Jenkins\StopJobRequest;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;

class JenkinsController extends Controller
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('jenkins.host').'/job/'.config('jenkins.ws');
    }

    public function GetJob(GetJobRequest $request) : JsonResponse
    {
        $app = AppInfo::find($request->id);

        if (!$app)
        {
            return response()->json(['status' => 'App can not found!']);
        }

        $jenkinsResponse = $this->GetJenkinsJobResponse("/job/{$app->project_name}/api/json")->getData();

        return response()->json([
            'job' => collect($jenkinsResponse->job_info)->only(['name', 'url'])
        ]);
    }

    public function GetJobList() : JsonResponse
    {
        $jenkinsResponse = $this->GetJenkinsJobResponse('/api/json')->getData();

        return response()->json([
            'job_list' => collect($jenkinsResponse->job_info->jobs)->pluck('name')
        ]);
    }

    public function GetLastBuildSummary(GetJobRequest $request) : JsonResponse
    {
        $app = AppInfo::find($request->id);

        if (!$app)
        {
            return response()->json(['status' => 'App can not found!']);
        }

        $validatedResponse = collect($this->GetJenkinsJobResponse("/job/{$app->project_name}/job/master/api/json")->getData());

        // job doesn't exist.
        if (!$validatedResponse->get('jenkins_status') || !$validatedResponse->get('job_exists'))
        {
            return response()->json($validatedResponse->except('job_info'));
        }

        $response = collect();
        $response = $response->merge($validatedResponse->get('job_info')?->builds);

        // job exists, but builds are deleted or no build.
        // add nextBuildNumber value to build list for detailed info for job parametrization.
        if (isset($validatedResponse->get('job_info')->builds) && empty($validatedResponse->get('job_info')->builds))
        {
            $additionalBuildInfo = collect([
                '_class' => '',
                'number' => $validatedResponse->get('job_info')->nextBuildNumber,
                'url' => ''
            ]);

            $response = $response->add($additionalBuildInfo);
        }

        $buildList = collect([ 'build_list' => $response->last() ]);

        // copy jenkins params.
        $validatedResponse->map(function ($item, $key) use (&$buildList) {
            $buildList->put($key, $item);
        });

        return response()->json($buildList->except('job_info'));
    }

    public function GetLastBuildWithDetails(Request $request, $job = null) : JsonResponse
    {
        $jobName = is_null($job) ? $request->projectName : $job;
        $validatedResponse = collect($this->GetJenkinsJobResponse("/job/{$jobName}/job/master/wfapi/runs")->getData());

        if (!$validatedResponse->get('jenkins_status') || !$validatedResponse->get('job_exists'))
        {
            return response()->json($validatedResponse->except('job_info'));
        }

        // builds exist.
        if (!empty($validatedResponse->get('job_info')))
        {
            $buildCollection = collect($validatedResponse->get('job_info'));
            $lastBuild = $buildCollection->first();

            // add job url
            $validatedResponse->put('job_url', $lastBuild->_links->self->href);

            // add build number
            $validatedResponse->put('build_number', $lastBuild->id);

            // add commit history
            $lastBuildDetailResponse = collect($this->GetJenkinsJobResponse("/job/{$jobName}/job/master/{$lastBuild->id}/api/json")->getData());
            $changeSets = isset($lastBuildDetailResponse->get('job_info')->changeSets[0])
                ? collect($lastBuildDetailResponse->get('job_info')->changeSets[0]->items)->pluck('msg')->reverse()->take(5)->values()
                : collect();
            $validatedResponse->put('change_sets', $changeSets);

            // add job build detail
            $jobStages = collect($lastBuild->stages);
            $jobStopStage = $jobStages->whereIn('status', ['FAILED', 'ABORTED'])?->first()?->name ?? '';
            $jobStopStageDetail = $jobStages->whereIn('status', ['FAILED', 'ABORTED'])?->first()?->error?->message ?? '';

            $validatedResponse->put('build_status', collect([
                'status' => $lastBuild->status,
                'message' => $jobStopStage,
                'message_detail' => $jobStopStageDetail
            ]));

            // parameters[1] equals == platform
            $jobHasParameters = isset($lastBuildDetailResponse->get('job_info')->actions[0]->parameters);
            $buildPlatform = ($jobHasParameters) ? $lastBuildDetailResponse->get('job_info')->actions[0]?->parameters[1]?->value : 'Appstore';
            $validatedResponse->put('build_platform', $buildPlatform);

            $validatedResponse->put('build_stage', $jobStages->last()?->name);
            $validatedResponse->put('timestamp', $lastBuild->startTimeMillis);
            $validatedResponse->put('estimated_duration', collect($validatedResponse->get('job_info'))->avg('durationMillis'));
        }

        return response()->json($validatedResponse->except('job_info'));
    }

    public function BuildJob(BuildRequest $request) : JsonResponse
    {
        $appInfo = AppInfo::find($request->id);

        if (!$appInfo)
        {
            return response()->json(['status' => 'App can not found! Try again...']);
        }

        $job = $this->GetLastBuildSummary($request)->getData();
        $latestBuild = $job->build_list;

        // job exists but doesn't parameterized
        if ($latestBuild->number == 1 && empty($latestBuild->url))
        {
            Artisan::call("jenkins:default-trigger {$request->id}");
            return response()->json(['status' => "{$appInfo->app_name} building for first time. This build gonna be aborted by Jenkins!"]);
        }
        else
        {
            $hasStoreCustomVersion = isset($request->storeCustomVersion) && $request->storeCustomVersion == 'true';
            $hasStoreCustomVersion = var_export($hasStoreCustomVersion, true);
            $storeBuildNumber = ($hasStoreCustomVersion == 'true') ? $request->storeBuildNumber : 0;

            Artisan::call("jenkins:trigger {$request->id} master {FALSE} {$request->platform} {$request->storeVersion} {$hasStoreCustomVersion} {$storeBuildNumber}");
            return response()->json(['status' => "{$appInfo->app_name} building for {$request->platform}..."]);
        }
    }

    public function StopJob(StopJobRequest $request) : JsonResponse
    {
        $app = AppInfo::find($request->id);

        if (!$app)
        {
            return response()->json(['status' => 'App can not found! Try again...']);
        }

        $url = "/job/{$app->project_name}/job/master/{$request->build_number}/stop";

        return response()->json([
            'status' => Http::withBasicAuth(config('jenkins.user'), config('jenkins.token'))->post($this->baseUrl . $url)->status()
        ]);
    }

    private function GetJenkinsJobResponse($url) : JsonResponse
    {
        $response = collect([
            'jenkins_status' => false,
            'jenkins_message' => '',
            'job_exists' => false,
            'job_info' => collect()
        ]);

        $jenkinsResponse = '';

        try
        {
            $jenkinsResponse = $this->GetJenkinsApi($this->baseUrl . $url);
        }
        catch (\Exception $exception)
        {
            $response->put('jenkins_message', $exception->getMessage());

            return response()->json($response);
        }

        $response->put('jenkins_status', true);
        $response->put('jenkins_message', 'success');

        $jobExists = !is_null($jenkinsResponse);
        if ($jobExists)
        {
            $response->put('job_exists', true);
            $response->put('job_info', collect($jenkinsResponse));
        }

        return response()->json($response);
    }

    private function GetJenkinsApi($url)
    {
        return json_decode(Http::withBasicAuth(config('jenkins.user'), config('jenkins.token'))
            ->timeout(20)
            ->connectTimeout(8)
            ->get($url));
    }
}
