<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class JenkinsController extends Controller
{
    public function GetJobList(Request $request) : JsonResponse
    {
        $url = implode('', [
            env('JENKINS_HOST', 'http://localhost:8080'),
            "/job/Talus-WorkSpace/api/json"
        ]);

        $jenkinsInfo = Http::withBasicAuth(env('JENKINS_USER'), env('JENKINS_TOKEN'))->get($url);
        $jenkinsJobList = collect(json_decode($jenkinsInfo)->jobs);

        return response()->json([
            'job_list' => $jenkinsJobList
        ]);
    }

    public function GetJob(Request $request) : JsonResponse
    {
        $url = implode('', [
            env('JENKINS_HOST', 'http://localhost:8080'),
            "/job/Talus-WorkSpace/job/{$request->projectName}/api/json"
        ]);

        $jenkinsInfo = Http::withBasicAuth(env('JENKINS_USER'), env('JENKINS_TOKEN'))->get($url);
        $jenkinsJobInfo = collect(json_decode($jenkinsInfo));

        return response()->json([
            'job' => $jenkinsJobInfo
        ]);
    }

    public function GetBuildList(Request $request) : JsonResponse
    {
        $url = implode('', [
            env('JENKINS_HOST', 'http://localhost:8080'),
            "/job/Talus-WorkSpace/job/{$request->projectName}/job/master/api/json"
        ]);

        $jenkinsInfo = Http::withBasicAuth(env('JENKINS_USER'), env('JENKINS_TOKEN'))->get($url);
        $jenkinsJobBuildList = collect(json_decode($jenkinsInfo)->builds);

        return response()->json([
            'build_list' => $jenkinsJobBuildList 
        ]);
    }
}
