<?php

namespace App\Http\Requests\Workspace;

use App\Rules\AlphaDashDot;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;

class StoreWorkspaceSettingsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [

            'name' => [
                'required',
                'alpha_num',
                Rule::unique('workspaces')->whereNull('deleted_at'),
            ],

            'appstore_private_key' => [ 'nullable', 'string', ],
            'appstore_issuer_id' => [ 'nullable', 'string', ],
            'appstore_kid' => [ 'nullable', 'string', ],

            'apple_usermail' => [ 'nullable', 'string', ],
            'apple_app_pass' => [ 'nullable', 'string', ],

            'github_org_name' => [ 'nullable', new AlphaDashDot(), ],
            'github_access_token' => [ 'nullable', 'string', ],
            'github_template' => [ 'nullable', new AlphaDashDot(), ],
            'github_topic' => [ 'nullable', 'string', ],

            'api_key' => [ 'nullable', 'string', ],
        ];
    }
}
