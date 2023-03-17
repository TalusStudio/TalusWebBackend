<?php

namespace App\Services;

use App\Models\GithubSetting;
use GrahamCampbell\GitHub\Facades\GitHub;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

use App\Models\Workspace;

class GitHubService
{
    private readonly Workspace $workspace;

    public function __construct()
    {
        $this->workspace = Auth::user()->workspace;

        Config::set(
            'github.connections.main.token',
            $this->GetSetting()->personal_access_token ?? 'INVALID_TOKEN'
        );
    }

    // request handler to capture all exceptions in one place
    public function MakeGithubRequest(string $api, $func, ...$parameters) : JsonResponse
    {
        try
        {
            $response = GitHub::api($api)->{$func}(...$parameters);
        }
        catch (\Exception $exception)
        {
            return response()->json([
                'response' => [
                    'error' => [
                        'error_code' => $exception->getCode(),
                        'error_msg' => $exception->getMessage(),
                    ],
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([ 'response' => $response ], Response::HTTP_OK);
    }

    public function GetOrganizationName() : null|string
    {
        return $this->GetSetting()->organization_name;
    }

    public function GetRepoTopic() : null|string
    {
        return $this->GetSetting()->topic_name;
    }

    public function IsPrivateReposEnabled() : bool
    {
        return $this->GetSetting()->private_repo === true;
    }

    public function IsPublicReposEnabled() : bool
    {
        return $this->GetSetting()->public_repo === true;
    }

    private function GetSetting() : GithubSetting
    {
        return $this->workspace->githubSetting;
    }
}
