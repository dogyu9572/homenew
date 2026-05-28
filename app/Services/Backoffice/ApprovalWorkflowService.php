<?php

namespace App\Services\Backoffice;

use App\Models\ApprovalAttachment;
use App\Models\ApprovalDocument;
use App\Models\ApprovalLine;
use App\Models\ApprovalOpinion;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ApprovalWorkflowService
{
    /**
     * @param  array<int,int>  $approvalUserIds
     * @param  array<int,int>  $cooperationUserIds
     * @param  array<int,UploadedFile>  $attachments
     */
    public function createDocument(
        User $writer,
        string $templateKey,
        string $formType,
        string $title,
        array $content,
        array $approvalUserIds,
        array $cooperationUserIds,
        array $attachments
    ): ApprovalDocument {
        return DB::transaction(function () use (
            $writer,
            $templateKey,
            $formType,
            $title,
            $content,
            $approvalUserIds,
            $cooperationUserIds,
            $attachments
        ) {
            $document = ApprovalDocument::query()->create([
                'doc_no' => $this->nextDocumentNumber(),
                'template_key' => $templateKey,
                'form_type' => $formType,
                'title' => $title,
                'content' => $content,
                'writer_id' => $writer->id,
                'status' => ApprovalDocument::STATUS_PENDING,
                'submitted_at' => now(),
            ]);

            foreach (array_values($approvalUserIds) as $index => $userId) {
                ApprovalLine::query()->create([
                    'document_id' => $document->id,
                    'user_id' => $userId,
                    'line_type' => ApprovalLine::TYPE_APPROVAL,
                    'line_order' => $index + 1,
                    'status' => ApprovalLine::STATUS_PENDING,
                ]);
            }

            foreach (array_values($cooperationUserIds) as $index => $userId) {
                ApprovalLine::query()->create([
                    'document_id' => $document->id,
                    'user_id' => $userId,
                    'line_type' => ApprovalLine::TYPE_COOPERATION,
                    'line_order' => $index + 1,
                    'status' => ApprovalLine::STATUS_PENDING,
                ]);
            }

            foreach ($attachments as $file) {
                $storedPath = $file->store('uploads/approvals', 'public');

                ApprovalAttachment::query()->create([
                    'document_id' => $document->id,
                    'user_id' => $writer->id,
                    'original_name' => $file->getClientOriginalName(),
                    'stored_path' => $storedPath,
                    'mime_type' => $file->getClientMimeType(),
                    'size' => (int) $file->getSize(),
                ]);
            }

            return $document->load(['writer', 'lines.user', 'attachments']);
        });
    }

    public function approve(ApprovalDocument $document, User $actor, ?string $opinion = null): ApprovalDocument
    {
        return DB::transaction(function () use ($document, $actor, $opinion) {
            $line = $this->findActorLine($document, $actor, ApprovalLine::TYPE_APPROVAL);
            if (! $line || $line->status !== ApprovalLine::STATUS_PENDING) {
                abort(403, '결재 권한이 없습니다.');
            }

            $line->update([
                'status' => ApprovalLine::STATUS_APPROVED,
                'acted_at' => now(),
                'action_comment' => $this->normalizeText($opinion),
                'meta' => array_merge((array) ($line->meta ?? []), ['action_type' => 'approve']),
            ]);

            // 마지막 결재순번 승인 시 문서 완료 처리
            // (이전 순번 미승인 상태여도 마지막 결재자가 승인하면 완료)
            $lastApprovalOrder = (int) $document->lines()
                ->where('line_type', ApprovalLine::TYPE_APPROVAL)
                ->max('line_order');

            if ((int) $line->line_order === $lastApprovalOrder) {
                $document->update([
                    'status' => ApprovalDocument::STATUS_COMPLETED,
                    'completed_at' => now(),
                ]);
            } elseif ($lastApprovalOrder <= 1) {
                // 결재자가 1명인 문서는 승인 즉시 완료
                $document->update([
                    'status' => ApprovalDocument::STATUS_COMPLETED,
                    'completed_at' => now(),
                ]);
            }

            return $document->fresh(['writer', 'lines.user', 'opinions.user', 'attachments']);
        });
    }

    public function reject(ApprovalDocument $document, User $actor, string $opinion): ApprovalDocument
    {
        return DB::transaction(function () use ($document, $actor, $opinion) {
            $line = $this->findActorLine($document, $actor, ApprovalLine::TYPE_APPROVAL);
            if (! $line || $line->status !== ApprovalLine::STATUS_PENDING) {
                abort(403, '반려 권한이 없습니다.');
            }

            $line->update([
                'status' => ApprovalLine::STATUS_REJECTED,
                'acted_at' => now(),
                'action_comment' => $this->normalizeText($opinion),
                'meta' => array_merge((array) ($line->meta ?? []), ['action_type' => 'reject']),
            ]);

            $document->update([
                'status' => ApprovalDocument::STATUS_REJECTED,
                'rejected_at' => now(),
            ]);

            ApprovalOpinion::query()->create([
                'document_id' => $document->id,
                'user_id' => $actor->id,
                'type' => ApprovalOpinion::TYPE_REJECT,
                'content' => $this->normalizeText($opinion) ?? '',
            ]);

            return $document->fresh(['writer', 'lines.user', 'opinions.user', 'attachments']);
        });
    }

    public function confirmCooperation(ApprovalDocument $document, User $actor, ?string $opinion = null): ApprovalDocument
    {
        return DB::transaction(function () use ($document, $actor, $opinion) {
            $line = $this->findActorLine($document, $actor, ApprovalLine::TYPE_COOPERATION);
            if (
                ! $line
                || $line->status !== ApprovalLine::STATUS_PENDING
                || $this->hasPendingApprovalLine($document)
            ) {
                abort(403, '협조 확인 권한이 없습니다.');
            }

            $line->update([
                'status' => ApprovalLine::STATUS_CONFIRMED,
                'acted_at' => now(),
                'action_comment' => $this->normalizeText($opinion),
                'meta' => array_merge((array) ($line->meta ?? []), ['action_type' => 'approve']),
            ]);

            // 협조 라인은 문서 완료 조건에 영향 없음
            return $document->fresh(['writer', 'lines.user', 'opinions.user', 'attachments']);
        });
    }

    public function delegate(ApprovalDocument $document, User $actor, string $lineType, ?string $opinion = null): ApprovalDocument
    {
        return DB::transaction(function () use ($document, $actor, $lineType, $opinion) {
            $line = $this->findActorLine($document, $actor, $lineType);
            if (! $line || $line->status !== ApprovalLine::STATUS_PENDING) {
                abort(403, '전결 권한이 없습니다.');
            }
            if ($lineType === ApprovalLine::TYPE_COOPERATION && $this->hasPendingApprovalLine($document)) {
                abort(403, '협조 전결 권한이 없습니다.');
            }

            $line->update([
                'status' => $lineType === ApprovalLine::TYPE_COOPERATION
                    ? ApprovalLine::STATUS_CONFIRMED
                    : ApprovalLine::STATUS_APPROVED,
                'acted_at' => now(),
                'action_comment' => $this->normalizeText($opinion),
                'meta' => array_merge((array) ($line->meta ?? []), ['action_type' => 'delegate']),
            ]);

            if ($lineType === ApprovalLine::TYPE_APPROVAL) {
                $this->completeIfFinalApprover($document, (int) $line->line_order);
            }

            return $document->fresh(['writer', 'lines.user', 'opinions.user', 'attachments']);
        });
    }

    public function hold(ApprovalDocument $document, User $actor, string $lineType, ?string $opinion = null): ApprovalDocument
    {
        return DB::transaction(function () use ($document, $actor, $lineType, $opinion) {
            $line = $this->findActorLine($document, $actor, $lineType);
            if (! $line || $line->status !== ApprovalLine::STATUS_PENDING) {
                abort(403, '보류 권한이 없습니다.');
            }
            if ($lineType === ApprovalLine::TYPE_COOPERATION && $this->hasPendingApprovalLine($document)) {
                abort(403, '협조 보류 권한이 없습니다.');
            }

            $line->update([
                'status' => ApprovalLine::STATUS_REJECTED,
                'acted_at' => now(),
                'action_comment' => $this->normalizeText($opinion),
                'meta' => array_merge((array) ($line->meta ?? []), ['action_type' => 'hold', 'held_at' => now()->toDateTimeString()]),
            ]);

            return $document->fresh(['writer', 'lines.user', 'opinions.user', 'attachments']);
        });
    }

    public function rejectCooperation(ApprovalDocument $document, User $actor, string $opinion): ApprovalDocument
    {
        return DB::transaction(function () use ($document, $actor, $opinion) {
            $line = $this->findActorLine($document, $actor, ApprovalLine::TYPE_COOPERATION);
            if (! $line || $line->status !== ApprovalLine::STATUS_PENDING || $this->hasPendingApprovalLine($document)) {
                abort(403, '협조 기각 권한이 없습니다.');
            }

            $line->update([
                'status' => ApprovalLine::STATUS_REJECTED,
                'acted_at' => now(),
                'action_comment' => $this->normalizeText($opinion),
                'meta' => array_merge((array) ($line->meta ?? []), ['action_type' => 'reject']),
            ]);

            return $document->fresh(['writer', 'lines.user', 'opinions.user', 'attachments']);
        });
    }

    /**
     * @param  array<string,mixed>  $content
     * @param  array<int,UploadedFile>  $attachments
     */
    public function updateDraft(
        ApprovalDocument $document,
        User $actor,
        string $title,
        array $content,
        array $attachments
    ): ApprovalDocument {
        return DB::transaction(function () use ($document, $actor, $title, $content, $attachments) {
            $isEditable = (int) $document->writer_id === (int) $actor->id
                && $document->status === ApprovalDocument::STATUS_PENDING
                && ! $document->lines()
                    ->where('line_type', ApprovalLine::TYPE_APPROVAL)
                    ->whereNotNull('acted_at')
                    ->exists();

            if (! $isEditable) {
                abort(403, '수정 가능한 문서가 아닙니다.');
            }

            $document->update([
                'title' => $this->normalizeText($title) ?? $document->title,
                'content' => $content,
            ]);

            foreach ($attachments as $file) {
                $storedPath = $file->store('uploads/approvals', 'public');

                ApprovalAttachment::query()->create([
                    'document_id' => $document->id,
                    'user_id' => $actor->id,
                    'original_name' => $file->getClientOriginalName(),
                    'stored_path' => $storedPath,
                    'mime_type' => $file->getClientMimeType(),
                    'size' => (int) $file->getSize(),
                ]);
            }

            return $document->fresh(['writer', 'lines.user', 'opinions.user', 'attachments']);
        });
    }

    private function findActorLine(ApprovalDocument $document, User $actor, string $lineType): ?ApprovalLine
    {
        return $document->lines()
            ->where('line_type', $lineType)
            ->where('user_id', $actor->id)
            ->orderBy('line_order')
            ->first();
    }

    private function hasPendingApprovalLine(ApprovalDocument $document): bool
    {
        return $document->lines()
            ->where('line_type', ApprovalLine::TYPE_APPROVAL)
            ->where('status', ApprovalLine::STATUS_PENDING)
            ->exists();
    }

    private function completeIfFinalApprover(ApprovalDocument $document, int $lineOrder): void
    {
        $lastApprovalOrder = (int) $document->lines()
            ->where('line_type', ApprovalLine::TYPE_APPROVAL)
            ->max('line_order');

        if ($lineOrder === $lastApprovalOrder || $lastApprovalOrder <= 1) {
            $document->update([
                'status' => ApprovalDocument::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);
        }
    }

    private function nextDocumentNumber(): string
    {
        $prefix = now()->format('ym');
        $latest = ApprovalDocument::query()
            ->where('doc_no', 'like', $prefix.'-%')
            ->orderByDesc('doc_no')
            ->value('doc_no');

        $next = 1;
        if (is_string($latest) && preg_match('/^\d{4}-(\d{4})$/', $latest, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        return sprintf('%s-%04d', $prefix, $next);
    }

    private function normalizeText(?string $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
