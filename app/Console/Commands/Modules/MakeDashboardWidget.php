<?php

namespace App\Console\Commands\Modules;

use App\Services\Admin\Dashboard\DashboardWidget;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Nwidart\Modules\Facades\Module;
use ReflectionClass;

class MakeDashboardWidget extends Command
{
    protected $signature = 'module:make-dashboard-widget {module} {model}
        {--title= : Kart başlığı} {--permission= : Veri görüntüleme izni}
        {--route= : İlgili liste route adı} {--id= : Kalıcı widget kimliği}
        {--default : İlk kişisel düzende görünür olsun}';

    protected $description = 'Generate and register a module-owned count widget for an existing model';

    public function handle(): int
    {
        $module = Module::find((string) $this->argument('module'));
        if (! $module) {
            $this->error('Modül bulunamadı.');

            return self::FAILURE;
        }
        $namespace = 'Modules\\'.$module->getName();
        $model = ltrim((string) $this->argument('model'), '\\');
        $model = str_contains($model, '\\') ? $model : $namespace.'\\Models\\'.$model;
        $alias = (string) $module->get('alias', strtolower($module->getName()));
        $permission = (string) ($this->option('permission') ?: $alias.'.view');
        $route = (string) ($this->option('route') ?: 'admin.'.$alias.'.index');
        $title = (string) $this->option('title');
        $id = (string) ($this->option('id') ?: $alias.'.'.strtolower(class_basename($model)).'.count');
        if (! str_starts_with($model, $namespace.'\\Models\\') || ! is_subclass_of($model, Model::class)
            || ! in_array($permission, $module->get('panel', [])['permissions'] ?? [], true)
            || ! str_ends_with($permission, '.view') || ! Route::has($route) || trim($title) === ''
            || ! str_starts_with($id, $alias.'.') || strlen($id) > 120
            || ! preg_match('/^[a-z0-9]+(?:[.][a-z0-9_-]+)+$/', $id)) {
            $this->error('Geçerli modül modeli, başlık, kalıcı kimlik, görüntüleme izni ve named route gereklidir.');

            return self::FAILURE;
        }
        $root = str_replace('\\', '/', (string) realpath($module->getPath()));
        $modelFile = str_replace('\\', '/', (string) (new ReflectionClass($model))->getFileName());
        if (! str_starts_with($modelFile, $root.'/')) {
            $this->error('Model modül sınırında olmalıdır.');

            return self::FAILURE;
        }
        $class = class_basename($model).'CountWidget';
        $className = $namespace.'\\Dashboard\\Widgets\\'.$class;
        $directory = $module->getPath().'/app/Dashboard/Widgets';
        $destination = $directory.'/'.$class.'.php';
        $source = $module->getPath().'/config/widgets.php';
        if (! $this->withinModule($directory, $root) || ! $this->withinModule($source, $root)) {
            $this->error('Widget dosyaları modül sınırında kalmalıdır.');

            return self::FAILURE;
        }
        $panel = $module->get('panel', []);
        if (isset($panel['widgets']) && $panel['widgets'] !== 'config/widgets.php') {
            $this->error('Özel widget kaynağını önce ortak config/widgets.php düzenine uyarlayın.');

            return self::FAILURE;
        }
        if (File::exists($destination)) {
            $this->error('Widget sınıfı zaten var.');

            return self::FAILURE;
        }
        $classes = File::exists($source) ? require $source : [];
        if (! is_array($classes) || ! array_is_list($classes)) {
            $this->error('Widget kayıt dosyası geçersiz.');

            return self::FAILURE;
        }
        foreach ($classes as $existing) {
            if (! is_string($existing) || ! is_subclass_of($existing, DashboardWidget::class) || app($existing)->definition()['id'] === $id) {
                $this->error('Widget kimliği tekrarlanıyor veya kayıt geçersiz.');

                return self::FAILURE;
            }
        }
        $template = File::get(base_path('stubs/modules/dashboard/widget.stub'));
        $content = strtr($template, [
            '$NAMESPACE$' => $namespace.'\\Dashboard\\Widgets', '$CLASS$' => $class,
            '$MODEL$' => $model, '$MODEL_NAME$' => class_basename($model),
            '$ID$' => var_export($id, true), '$TITLE$' => var_export($title, true),
            '$PERMISSION$' => var_export($permission, true), '$ROUTE$' => var_export($route, true),
            '$DEFAULT$' => $this->option('default') ? 'true' : 'false',
        ]);
        File::ensureDirectoryExists($directory);
        File::put($destination, $content);
        File::ensureDirectoryExists(dirname($source));
        File::put($source, "<?php\n\nreturn ".var_export([...$classes, $className], true).";\n");
        $metadataPath = $module->getPath().'/module.json';
        $metadata = json_decode(File::get($metadataPath), true, flags: JSON_THROW_ON_ERROR);
        $metadata['panel']['widgets'] = 'config/widgets.php';
        File::put($metadataPath, json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n");
        $this->info('Widget oluşturuldu ve kaydedildi. İş alanına özgü filtreleri ve metrik testini tamamlayın.');

        return self::SUCCESS;
    }

    private function withinModule(string $path, string $root): bool
    {
        while (! file_exists($path)) {
            $parent = dirname($path);
            if ($parent === $path) {
                return false;
            }
            $path = $parent;
        }
        $resolved = str_replace('\\', '/', (string) realpath($path));

        return $resolved === $root || str_starts_with($resolved, $root.'/');
    }
}
