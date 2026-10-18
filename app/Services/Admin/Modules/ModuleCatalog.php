<?php

namespace App\Services\Admin\Modules;

use Illuminate\Support\Facades\Validator;
use RuntimeException;

class ModuleCatalog
{
    public function __construct(private ModuleDownload $download) {}

    /** @return list<array<string, mixed>> */
    public function all(): array
    {
        $url = config('module-distribution.catalog_url');
        if (! is_string($url) || $url === '') {
            return [];
        }
        $document = json_decode($this->download->get($url, 1048576), true, 64, JSON_THROW_ON_ERROR);
        $validator = Validator::make(['catalog' => $document], [
            'catalog' => ['required', 'array:schema_version,modules'],
            'catalog.schema_version' => ['required', 'integer', 'in:1'],
            'catalog.modules' => ['present', 'list', 'max:500'],
            'catalog.modules.*' => ['array:id,name,package,description,version,author,requires,download'],
            'catalog.modules.*.id' => ['required', 'string', 'regex:/^[a-z][a-z0-9-]*$/D', 'distinct:ignore_case', 'max:80'],
            'catalog.modules.*.name' => ['required', 'string', 'max:150'],
            'catalog.modules.*.package' => ['required', 'string', 'regex:~^[a-z0-9_.-]+/[a-z0-9_.-]+$~D', 'distinct', 'max:150'],
            'catalog.modules.*.description' => ['present', 'string', 'max:2000'],
            'catalog.modules.*.version' => ['required', 'string', 'regex:/^\\d+\\.\\d+\\.\\d+(?:-[a-zA-Z0-9.-]+)?$/D'],
            'catalog.modules.*.author' => ['present', 'string', 'max:150'],
            'catalog.modules.*.requires' => ['required', 'array:php,laravel,modules'],
            'catalog.modules.*.requires.php' => ['required', 'string', 'max:100'],
            'catalog.modules.*.requires.laravel' => ['required', 'string', 'max:100'],
            'catalog.modules.*.requires.modules' => ['present', 'array', 'max:100'],
            'catalog.modules.*.requires.modules.*' => ['required', 'string', 'max:100'],
            'catalog.modules.*.download' => ['required', 'array:url,sha256'],
            'catalog.modules.*.download.url' => ['required', 'string', 'max:2048'],
            'catalog.modules.*.download.sha256' => ['required', 'string', 'regex:/^[a-f0-9]{64}$/D'],
        ]);
        if ($validator->fails()) {
            throw new RuntimeException('Modül kataloğu geçersiz: '.$validator->errors()->first());
        }
        foreach ($document['modules'] as $module) {
            $this->download->assertAllowed($module['download']['url']);
            foreach ($module['requires']['modules'] as $id => $constraint) {
                if (! is_string($id) || ! preg_match('/^[a-z][a-z0-9-]*$/D', $id)) {
                    throw new RuntimeException('Katalog modül bağımlılığı geçersiz.');
                }
            }
        }

        return $document['modules'];
    }
}
