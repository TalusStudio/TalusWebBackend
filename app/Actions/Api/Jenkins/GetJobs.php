<?php

namespace App\Actions\Api\Jenkins;

use Lorisleiva\Actions\Concerns\AsAction;

use Illuminate\Http\JsonResponse;

use App\Services\JenkinsService;

class GetJobs
{
    use AsAction;

    public function handle() : JsonResponse
    {
        $jobResponse = app(JenkinsService::class)->GetResponse('/api/json');
        $jobResponse->jenkins_data = collect($jobResponse->jenkins_data?->jobs)->pluck('name');

        return response()->json($jobResponse);
    }
}
