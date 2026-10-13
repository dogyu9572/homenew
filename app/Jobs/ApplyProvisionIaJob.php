<?php

namespace App\Jobs;

use App\Models\ProjectProvision;
use App\Services\Backoffice\JavaIaApplyService;
use App\Services\Backoffice\PubIaApplyService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class ApplyProvisionIaJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 900;

    public int $tries = 1;

    public function __construct(public int $provisionId, public bool $resetBackoffice = false) {}

    public function handle(PubIaApplyService $service, JavaIaApplyService $java): void
    {
        $provision = ProjectProvision::query()->find($this->provisionId);
        if (! $provision || $provision->status !== ProjectProvision::STATUS_SUCCESS) {
            return;
        }

        if (! is_string($provision->ia_file_path) || $provision->ia_file_path === '') {
            return;
        }

        try {
            if ($provision->stack === ProjectProvision::STACK_JAVA) {
                $java->apply($provision);
            } elseif ($this->resetBackoffice) {
                $service->reapply($provision);
            } else {
                $service->apply($provision);
            }
        } catch (Throwable $exception) {
            $message = $exception instanceof \RuntimeException ? $exception->getMessage() : '';
            if ($message === '' || str_contains($message, 'http') || str_contains(strtolower($message), 'password')) {
                $message = 'IA 적용에 실패했습니다.';
            }
            $provision->ia_apply_note = $message;
            $provision->save();
            report($exception);
        }
    }
}
