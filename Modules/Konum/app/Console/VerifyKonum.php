<?php

namespace Modules\Konum\Console;

use Illuminate\Console\Command;
use Modules\Konum\Services\KonumVerificationService;
use Throwable;

class VerifyKonum extends Command
{
    protected $signature = 'konum:verify';

    protected $description = 'Konum kayıtlarını kaynak özeti, ilişkiler ve geometriyle karşılaştırır.';

    public function handle(KonumVerificationService $verification): int
    {
        try {
            $this->line(json_encode($verification->verify(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}
