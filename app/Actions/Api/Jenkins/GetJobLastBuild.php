<?php

namespace App\Actions\Api\Jenkins;

use Lorisleiva\Actions\Concerns\AsAction;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

use App\Models\AppInfo;
use App\Services\JenkinsService;
use App\Http\Requests\AppInfo\GetAppInfoRequest;

class GetJobLastBuild
{
    use AsAction;

    // jenkins api filters
    private array $filters = [
        'job_parameters' => 'actions[*[name,value]]{0}',
        'job_changesets' => 'changeSets[*[id,authorEmail,comment]{0,5}]',
    ];

    private AppInfo $app;

    public function handle(?GetAppInfoRequest $request, ?AppInfo $appInfo = null) : JsonResponse
    {
        $this->app = $appInfo ?? Auth::user()->workspace->apps()->findOrFail($request->validated('id'));
        $jenkinsService = app(JenkinsService::class);

        // find last build of job
        $jobResponse = $jenkinsService->GetResponse($this->CreateJobUrl());
        $builds = collect($jobResponse->jenkins_data);

        // last build returned as a first item in collection from jenkins api
        $build = $builds->first();

        if ($build)
        {
            $build->status = $this->GetStatus($build);

            // if job is running, calculate average duration
            if ($build->status == JobStatus::IN_PROGRESS)
            {
                $build->estimated_duration = $builds->avg('durationMillis');
            }

            // populate build details with another request
            $buildDetails = $jenkinsService->GetResponse($this->CreateLastBuildUrl($build->id));
            $build->build_platform = $this->GetBuildPlatform($buildDetails->jenkins_data);
            $build->change_sets = $this->GetCommitHistory($buildDetails->jenkins_data);
            $build->stop_details = $this->GetStopDetail($build);
        }

        $jobResponse->jenkins_data = $build;

        return response()->json($jobResponse);
    }

    public function authorize(GetAppInfoRequest $request) : bool
    {
        return !Auth::user()->isNew();
    }

    private function CreateLastBuildUrl(int $lastBuildId) : string
    {
        return implode('/', [
            "/job/{$this->app->project_name}/job",
            'master',
            $lastBuildId,
            "api/json?tree={$this->filters['job_parameters']},{$this->filters['job_changesets']}",
        ]);
    }

    private function CreateJobUrl() : string
    {
        return implode('/', [
            "/job/{$this->app->project_name}/job",
            'master',
            'wfapi/runs'
        ]);
    }

    private function GetBuildPlatform(mixed $rawJenkinsResponse) : string
    {
        // parameters[1] === Platform parameter in Jenkinsfile
        // todo: refactor
        return $rawJenkinsResponse->actions[0]?->parameters[1]?->value ?? JobPlatform::Appstore->value;
    }

    private function GetCommitHistory(mixed $rawJenkinsResponse) : Collection
    {
        return collect($rawJenkinsResponse->changeSets[0]->items ?? [])
                ->map(function ($commit) {
                    return [
                        'id' => $commit->id,
                        'url' => $this->GetCommitLink($commit),
                        'comment' => $commit->comment,
                        'authorEmail'=> $commit->authorEmail
                    ];
                })->reverse()->values();
    }

    private function GetCommitLink($commit) : string
    {
        $isInternalCommit = $commit->authorEmail === 'noreply@github.com';

        $orgName = Auth::user()->orgName();

        return $isInternalCommit
            ? '#'
            : "https://github.com/{$orgName}/{$this->app->project_name}/commit/{$commit->id}";
    }

    private function GetStopDetail(mixed $lastBuild) : Collection
    {
        $buildStages = collect($lastBuild->stages);

        $stopStages = $buildStages->whereIn('status', JobStatus::GetErrorStages());
        $stopStage = $stopStages?->first()?->name ?? $buildStages->last()?->name;
        $stopStageDetail = $stopStages?->first()?->error?->message ?? '';

        return collect([
            'stage' => $stopStage,
            'output' => $stopStageDetail,
        ]);
    }

    private function GetStatus($lastBuild) : JobStatus
    {
        $inProgress = $lastBuild->status === JobStatus::IN_PROGRESS->value;
        $stageCount = count(collect($lastBuild->stages));

        // queued and running jobs have same status (IN_PROGRESS)
        // lets make the distinction
        if ($inProgress && $stageCount == 0)
        {
            return JobStatus::IN_QUEUE;
        }

        return JobStatus::tryFrom($lastBuild->status) ?? JobStatus::NOT_IMPLEMENTED;
    }
}
