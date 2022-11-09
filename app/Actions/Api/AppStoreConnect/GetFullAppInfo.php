<?php

namespace App\Actions\Api\AppStoreConnect;

use Lorisleiva\Actions\Concerns\AsAction;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

use App\Services\AppStoreConnectService;

class GetFullAppInfo
{
    use AsAction;

    public function handle(Request $request) : JsonResponse
    {
        $storeService = new AppStoreConnectService($request);
        $generatedToken = $storeService->CreateToken()->getData()->appstore_token;
        $appstoreApps = Http::withToken($generatedToken)->get(AppStoreConnectService::$API_URL
            .'/apps?fields[apps]=name,bundleId&limit='
            .config('appstore.item_limit')
            .'&filter[appStoreVersions.platform]=IOS&filter[appStoreVersions.appStoreState]=PREPARE_FOR_SUBMISSION'
        );

        $sortedAppCollection = collect(($appstoreApps->failed()) ? [] : json_decode($appstoreApps)->data);
        $sortedAppList = $sortedAppCollection->sortByDesc('id');

        return response()->json([ 'app_list' => $sortedAppList ]);
    }
}
