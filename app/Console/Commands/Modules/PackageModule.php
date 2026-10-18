<?php

namespace App\Console\Commands\Modules;

use App\Services\Admin\Modules\ModuleDownload;
use App\Services\Admin\Modules\ModuleRelease;
use Illuminate\Console\Command;
use JsonException;
use Nwidart\Modules\Exceptions\ModuleNotFoundException;
use Nwidart\Modules\Facades\Module;
use RuntimeException;

class PackageModule extends Command
{
    protected $signature = 'module:release {module : Modül adı} {--output= : ZIP hedef klasörü} {--download-url= : Gerçek release ZIP adresi verilirse katalog da üretir}';

    protected $description = 'Modülü derlenmiş assetleri ve testleriyle bağımsız sürüm ZIP olarak paketler';

    public function handle(ModuleRelease $releases): int
    {
        try {
            $module = Module::findOrFail($this->argument('module'));
            $url = $this->option('download-url');
            if ($url) {
                app(ModuleDownload::class)->assertAllowed($url);
            }
            $directory = $this->option('output') ?: storage_path('app/module-releases');
            $existing = $directory.'/'.$module->getName().'-v'.$module->get('version').'.zip';
            $release = $url && is_file($existing)
                ? $releases->existing($module, $existing)
                : $releases->package($module, $directory);
            if ($url) {
                $catalog = ['schema_version' => 1, 'modules' => [$releases->catalogEntry($module, $release, $url)]];
                $catalogPath = substr($release['path'], 0, -4).'.catalog.json';
                file_put_contents($catalogPath, json_encode($catalog, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL);
                $this->info($catalogPath);
            }
        } catch (RuntimeException|JsonException|ModuleNotFoundException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
        $this->info($release['path']);
        $this->line('SHA-256: '.$release['sha256']);

        return self::SUCCESS;
    }
}
