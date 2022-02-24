<?php

namespace App\Http\Controllers;

use App\Http\Requests\AppInfoRequest;
use App\Models\AppInfo;
use App\Models\File;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('list-app-info');
    }

    public function create()
    {
        return view('add-app-info-form');
    }

    public function select(Request $request)
    {
        return view('update-app-info-form')->with('id', $request->id);
    }

    public function store(AppInfoRequest $request)
    {
        return $this->PopulateAppData($request, new AppInfo());
    }

    public function update(AppInfoRequest $request)
    {
        return $this->PopulateAppData($request, AppInfo::find($request->id));
    }

    public function delete(Request $request)
    {
        $appInfo = AppInfo::find($request->id);
        $appInfo?->delete();

        return redirect()->route('get_app_list');
    }

    /**
     * @param string                            $iconPath
     * @param string                            $iconHash
     * @param \App\Http\Requests\AppInfoRequest $request
     *
     * @return void
     */
    public function GenerateHashAndUpload(string $iconPath, string $iconHash, AppInfoRequest $request) : void
    {
        $iconFile = new File();
        $iconFile->path = $iconPath;
        $iconFile->hash = $iconHash;
        $iconFile->save();

        $request->app_icon->move(public_path('images'), $iconPath);
    }

    /**
     * @param \App\Http\Requests\AppInfoRequest $request
     * @param \App\Models\AppInfo               $appInfo
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function PopulateAppData(AppInfoRequest $request, AppInfo $appInfo) : RedirectResponse
    {
        $inputs = $request->all();

        if (isset($inputs['app_icon']))
        {
            $currentIconHash = md5_file($inputs['app_icon']);
            $matchingHash = File::where('hash', $currentIconHash)->first();

            // icon hash not found so generate hash and upload icon file.
            if (!$matchingHash)
            {
                $iconPath = time() . '-' . $inputs['app_name'] . '.' . $inputs['app_icon']->getClientOriginalExtension();
                $this->GenerateHashAndUpload($iconPath, $currentIconHash, $request);
            }

            $appInfo->app_icon = ($matchingHash) ? $matchingHash->path : $iconPath;
        }

        //
        $appInfo->app_name = $inputs['app_name'];

        if (!$appInfo->exists)
        {
            $appInfo->app_bundle = $inputs['app_bundle'];
        }

        $appInfo->fb_app_id = $inputs['fb_app_id'];
        $appInfo->elephant_id = $inputs['elephant_id'];
        $appInfo->elephant_secret = $inputs['elephant_secret'];

        $appInfo->save();
        return redirect()->route('get_app_list');
    }
}
