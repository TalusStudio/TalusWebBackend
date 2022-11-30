<?php

namespace Database\Seeders\Settings;

use Illuminate\Database\Seeder;

use App\Models\AppStoreConnectSign;

class AppStoreConnectSignSeeder extends Seeder
{
    public function run()
    {
        AppStoreConnectSign::factory()->create([
            'workspace_id' => 1,
            'cert' => null,
            'provision_profile' => null,
        ]);

        AppStoreConnectSign::factory()->create([
            'workspace_id' => 2,
            'cert' => 'Cert.p12',
            'provision_profile' => 'iOSProfile',
        ]);

        foreach (range(3, 5) as $id)
        {
            AppStoreConnectSign::factory()->create([
                'workspace_id' => $id,
            ]);
        }
    }
}