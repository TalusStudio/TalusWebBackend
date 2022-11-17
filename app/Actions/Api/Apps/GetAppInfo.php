<?php

namespace App\Actions\Api\Apps;

use Lorisleiva\Actions\Concerns\AsAction;

use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

use App\Models\AppInfo;
use App\Models\Workspace;
use App\Http\Requests\AppInfo\GetAppInfoRequest;

class GetAppInfo
{
    use AsAction;

    public function handle(GetAppInfoRequest $request) : AppInfo
    {
        return AppInfo::find($request->validated('id'), [
            'id',
            'app_name',
            'project_name',
            'app_bundle',
            'appstore_id',
            'fb_app_id',
            'fb_client_token',
            'ga_id',
            'ga_secret',
        ]);
    }

    public function htmlResponse(AppInfo $appInfo) : View
    {
        return view('appinfo-form')->with('appInfo', $appInfo);
    }

    public function jsonResponse(AppInfo $appInfo) : JsonResponse
    {
        return response()->json($appInfo->makeHidden([
            'id',
            'project_name',
            'appstore_id',
        ]));
    }

    public function authorize(GetAppInfoRequest $request) : bool
    {
        $app = Auth::user()->workspace->apps()->find($request->validated('id'));
        $userWsId = Auth::user()->workspace->id;

        return $app && $userWsId !== Workspace::$DEFAULT_WS_ID && Auth::user()->can('view apps');
    }
}
