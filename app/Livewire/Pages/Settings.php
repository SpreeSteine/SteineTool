<?php

namespace App\Livewire\Pages;

use Livewire\Component;

class Settings extends Component
{
    public string $consumerKey = '';
    public string $consumerSecret = '';
    public string $accessToken = '';
    public string $tokenSecret = '';

    public function render()
    {
        // load the settings from the json file and decrypt them
        $settingFile = storage_path('app/settings.json');

        if (!file_exists($settingFile)) {
            return view('livewire.pages.settings');
        }

        $settings = json_decode(file_get_contents($settingFile), true);

        $this->consumerKey = decrypt($settings['consumerKey']) ?? '';
        $this->consumerSecret = decrypt($settings['consumerSecret']) ?? '';
        $this->accessToken = decrypt($settings['accessToken']) ?? '';
        $this->tokenSecret = decrypt($settings['tokenSecret']) ?? '';

        return view('livewire.pages.settings');
    }

    public function save(): void
    {
        $this->validate([
            'consumerKey' => 'required',
            'consumerSecret' => 'required',
            'accessToken' => 'required',
            'tokenSecret' => 'required',
        ]);

        // encrypt and save the settings to a json file
        $settings = [
            'consumerKey' => encrypt($this->consumerKey),
            'consumerSecret' => encrypt($this->consumerSecret),
            'accessToken' => encrypt($this->accessToken),
            'tokenSecret' => encrypt($this->tokenSecret),
        ];

        file_put_contents(storage_path('app/settings.json'), json_encode($settings));

        session()->flash('message', 'Settings saved successfully.');
    }
}
