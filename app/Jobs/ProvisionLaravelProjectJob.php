<?php

namespace App\Jobs;

use App\Models\ProjectProvision;
use App\Services\Backoffice\ProjectProvisionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProvisionLaravelProjectJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 1800;

    public int $tries = 1;

    public function __construct(public int $provisionId) {}

    public function handle(ProjectProvisionService $service): void
    {
        $provision = ProjectProvision::query()->find($this->provisionId);
        if (! $provision || $provision->status === ProjectProvision::STATUS_SUCCESS) {
            return;
        }

        $lock = Cache::lock('project-provision-'.$this->provisionId, 1900);
        if (! $lock->get()) {
            return;
        }

        try {
            $service->run($provision);
        } finally {
            $lock->release();
        }
    }

    public function failed(?Throwable $exception): void
    {
        $provision = ProjectProvision::query()->find($this->provisionId);
        if (! $provision || in_array($provision->status, [ProjectProvision::STATUS_SUCCESS, ProjectProvision::STATUS_FAILED], true)) {
            return;
        }

        $provision->status = ProjectProvision::STATUS_FAILED;
        $provision->log = trim(($provision->log ?? '')."\n빌드 작업이 중단되었습니다.");
        $provision->save();

        Log::error('프로젝트 빌드 작업이 중단되었습니다.', [
            'provision_id' => $this->provisionId,
            'message' => $exception?->getMessage(),
        ]);
    }
}
