<?php

namespace App\Http\Livewire;

use Livewire\Component;

use Jantinnerezo\LivewireAlert\LivewireAlert;

use App\Models\AppInfo;
use App\Actions\Api\Ftp\CreateAppPrivacy;

class AppPrivacyCreator extends Component
{
    use LivewireAlert;

    public AppInfo $appInfo;

    public function integrate()
    {
        $response = CreateAppPrivacy::run($this->appInfo);

        $this->alert(
            $response['success'] ? 'success' : 'error',
            $response['message']
        );
    }

    public function render()
    {
        return <<<'HTML'
            <div class="input-group-append">
                <button wire:loading.remove
                    wire:click="integrate"
                    class="btn btn-success rounded-right"
                    type="button"
                    onclick="return confirm('Privacy2 file will be created in talusstudio.com, are you sure?') || event.stopImmediatePropagation()"
                    data-toggle="tooltip"
                    data-placement="bottom"
                    title="Create Privacy File">
                    <i class="fa fa-user-secret"></i>
                </button>
                <div wire:loading class="btn alert-warning font-weight-bold">
                    Wait...
                </div>
            </div>
        HTML;
    }
}