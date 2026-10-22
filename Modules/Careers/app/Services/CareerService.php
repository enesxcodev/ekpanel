<?php

namespace Modules\Careers\Services;

use Illuminate\Support\Facades\DB;
use Modules\Careers\Models\JobPosting;

class CareerService
{
    /** @param array<string, mixed> $data */
    public function save(array $data, ?JobPosting $posting = null): JobPosting
    {
        return DB::transaction(function () use ($data, $posting): JobPosting {
            $posting ??= new JobPosting;
            $posting->fill($data)->save();

            return $posting;
        });
    }

    public function delete(JobPosting $posting): void
    {
        DB::transaction(fn () => $posting->delete());
    }
}
