<?php

namespace App\Console\Commands;

use App\Services\Media\MediaService;
use Illuminate\Console\Command;

class SyncMediaUploads extends Command
{
    protected $signature = 'media:sync';

    protected $description = 'Uploads diskindeki mevcut klasör ve güvenli dosyaları medya kütüphanesine kaydeder';

    public function handle(MediaService $mediaService): int
    {
        $result = $mediaService->synchronize();
        $this->info($result['added'].' dosya eklendi; '.$result['skipped'].' dosya atlandı.');

        return self::SUCCESS;
    }
}
