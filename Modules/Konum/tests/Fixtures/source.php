<?php

use Modules\Konum\Services\KonumSource;

function konumFixtureSource(): string
{
    $directory = __DIR__;
    foreach (KonumSource::TABLES as $table) {
        config(['konum.source.files.'.$table => [
            'bytes' => filesize($directory.'/'.$table.'.sql'),
            'sha256' => hash_file('sha256', $directory.'/'.$table.'.sql'),
        ]]);
    }

    return $directory;
}
