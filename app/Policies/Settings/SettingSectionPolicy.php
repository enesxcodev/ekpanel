<?php

namespace App\Policies\Settings;

use App\Models\Admin\Admin;
use App\Models\Settings\SettingSection;

class SettingSectionPolicy
{
    public function view(Admin $admin, SettingSection $section): bool
    {
        return $admin->can('settings.view') && $admin->can('settings.sections.'.$section->key.'.view');
    }

    public function update(Admin $admin, SettingSection $section): bool
    {
        return $this->view($admin, $section)
            && $admin->can('settings.update')
            && $admin->can('settings.sections.'.$section->key.'.update');
    }
}
