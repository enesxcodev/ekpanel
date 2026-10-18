<?php

namespace App\Console\Commands;

use App\Services\Settings\SettingsService;
use Illuminate\Console\Command;

class LinkSettingsLogoMedia extends Command
{
    protected $signature = 'media:link-settings-logos';

    protected $description = 'Mevcut üst ve alt logo dosyalarını medya kimlikleriyle ilişkilendirir';

    public function handle(SettingsService $settings): int
    {
        $this->info($settings->linkExistingLogoMedia().' logo medya kaydıyla ilişkilendirildi.');

        return self::SUCCESS;
    }
}
