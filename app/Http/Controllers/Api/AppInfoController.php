<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Http\Requests\AppInfo\GetAppInfoRequest;
use App\Http\Requests\AppInfo\StoreAppInfoRequest;
use App\Http\Requests\AppInfo\UpdateAppInfoRequest;

use App\Models\AppInfo;
use App\Models\File;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;

use Illuminate\Support\Facades\Auth;

class AppInfoController extends Controller
{
    public function GetApp(GetAppInfoRequest $request) : JsonResponse
    {
        $response = AppInfo::find($request->validated('id'), [
            'app_bundle',
            'app_name',
            'fb_app_id',
            'fb_client_token',
            'ga_id',
            'ga_secret'
        ]);

        return response()->json($response, Response::HTTP_OK);
    }

    public function CreateApp(StoreAppInfoRequest $request) : JsonResponse
    {
        $this->authorize('create', AppInfo::class);

        // prepare model
        $appModel = AppInfo::withTrashed()
            ->where('app_bundle', '=', $request->validated('app_bundle'))
            ->firstOrNew();

        // populate model
        $this->RestoreOrCreate($appModel, $request);

        $githubResponse = app(GithubController::class)->CreateRepository($request)->getData();

        return response()->json([
            'app' => $appModel,
            'git' => $githubResponse
        ], Response::HTTP_OK);
    }

    public function UpdateApp(UpdateAppInfoRequest $request) : JsonResponse
    {
        $app = AppInfo::find($request->validated('id'));
        $this->authorize('update', $app);
        $app->update($request->safe()->all());

        return response()->json($app, Response::HTTP_OK);
    }

    public function DeleteApp(GetAppInfoRequest $request) : JsonResponse
    {
        $app = AppInfo::find($request->validated('id'));
        $this->authorize('delete', $app);
        $app->delete();

        return response()->json(['message' => "Project: <b>{$app->project_name}</b> deleted."], Response::HTTP_OK);
    }

    private function RestoreOrCreate(AppInfo $appModel, StoreAppInfoRequest $request)
    {
        if ($appModel->trashed()) {
            $appModel->restore();
        }

        $appModel->fill([ 'workspace_id' => Auth::user()->workspace_id ]);
        $appModel->fill($request->safe()->all());

        if ($request->hasFile('app_icon')) {
            $appModel->app_icon = $this->GenerateHashAndUpload($request->safe()->app_icon);
        }

        $appModel->save();
    }

    // todo: move to service class
    private function GenerateHashAndUpload(UploadedFile $iconImage) : string
    {
        // hash used for file path

        $hash = hash_file('sha256', $iconImage);
        $iconFileModel = File::firstOrNew([ 'hash' => $hash ]);

        if (!$iconFileModel->exists)
        {
            $iconFileModel->fill([
                'path' => $hash,
                'hash' => $hash,
            ])->save();

            $iconImage->storePubliclyAs('app-icons', $hash);
            return $iconFileModel->path;
        }

        return $hash;
    }
}
