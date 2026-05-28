<?php

namespace App\Console\Commands;

use App\Support\LegacyAttachmentFileNames;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class NormalizeProjectManageLegacyAttachmentsCommand extends Command
{
    protected $signature = 'project-manages:normalize-legacy-attachments
                            {--apply : 실제 DB 변경을 실행합니다. 없으면 분석만 합니다}
                            {--project= : 특정 project_manage_id만 처리합니다}
                            {--limit= : 처리할 원본 행 수를 제한합니다}
                            {--show-missing : 실제 파일이 없는 첨부 목록을 출력합니다}';

    protected $description = 'project_manage_files의 파이프(|) 구분 레거시 첨부 행을 파일별 행으로 분리';

    public function handle(): int
    {
        if (! Schema::hasTable('project_manage_files')) {
            $this->error('project_manage_files 테이블이 없습니다.');

            return self::FAILURE;
        }

        $apply = (bool) $this->option('apply');
        $project = $this->option('project');
        $limit = $this->option('limit');

        $query = DB::table('project_manage_files')
            ->where(function ($q) {
                $q->where('original_name', 'like', '%|%')
                    ->orWhere('stored_path', 'like', '%|%');
            })
            ->orderBy('id');

        if ($project !== null && $project !== '') {
            $query->where('project_manage_id', (int) $project);
        }

        if ($limit !== null && $limit !== '') {
            $query->limit(max(1, (int) $limit));
        }

        $rows = $query->get();
        $this->info($apply ? '[APPLY] 첨부 행 분리를 실행합니다.' : '[DRY-RUN] 변경 없이 분석만 합니다.');
        $this->line('분리 대상 원본 행: '.$rows->count().'건');

        $plannedInserts = 0;
        $skippedExisting = 0;
        $missingFiles = 0;
        $deletedRows = 0;
        $missingRows = [];

        $columns = array_flip(Schema::getColumnListing('project_manage_files'));

        if ($apply) {
            DB::beginTransaction();
        }

        try {
            foreach ($rows as $row) {
                $names = $this->splitRowNames($row);
                if ($names === []) {
                    continue;
                }

                $insertedForRow = 0;
                foreach ($names as $name) {
                    if ($this->attachmentExists((int) $row->project_manage_id, $name, (int) $row->id)) {
                        $skippedExisting++;
                        continue;
                    }

                    $storedPath = 'project-manages/'.$name;
                    $sizeBytes = null;
                    if (Storage::disk('public')->exists($storedPath)) {
                        $sizeBytes = Storage::disk('public')->size($storedPath);
                    } else {
                        $missingFiles++;
                        if ((bool) $this->option('show-missing')) {
                            $missingRows[] = [
                                'project_manage_id' => (int) $row->project_manage_id,
                                'source_row_id' => (int) $row->id,
                                'file' => $name,
                            ];
                        }
                    }

                    $payload = [
                        'project_manage_id' => (int) $row->project_manage_id,
                        'legacy_idx' => $row->legacy_idx ?? null,
                        'legacy_project_idx' => $row->legacy_project_idx ?? $row->project_manage_id,
                        'stored_path' => $storedPath,
                        'original_name' => $name,
                        'size_bytes' => $sizeBytes,
                        'regdate' => $row->regdate ?? now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $payload = array_intersect_key($payload, $columns);

                    if ($apply) {
                        DB::table('project_manage_files')->insert($payload);
                    }

                    $plannedInserts++;
                    $insertedForRow++;
                }

                $shouldDeleteSourceRow = $insertedForRow > 0
                    || $this->allSplitNamesExist((int) $row->project_manage_id, $names, (int) $row->id);

                if ($apply && $shouldDeleteSourceRow) {
                    DB::table('project_manage_files')->where('id', $row->id)->delete();
                    $deletedRows++;
                } elseif (! $apply && $shouldDeleteSourceRow) {
                    $deletedRows++;
                }
            }

            if ($apply) {
                DB::commit();
            }
        } catch (\Throwable $e) {
            if ($apply) {
                DB::rollBack();
            }
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->line('추가될 파일별 행: '.$plannedInserts.'건');
        $this->line('이미 존재해서 건너뜀: '.$skippedExisting.'건');
        $this->line('실제 파일 미존재: '.$missingFiles.'건');
        $this->line(($apply ? '삭제한' : '삭제 예정').' 원본 묶음 행: '.$deletedRows.'건');
        if ($missingRows !== []) {
            $this->table(['project_manage_id', 'source_row_id', 'file'], $missingRows);
        }
        $this->info('완료');

        return self::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function splitRowNames(object $row): array
    {
        $raw = (string) ($row->original_name ?: $row->stored_path ?: '');

        return array_values(array_filter(array_unique(array_map(
            fn (string $name): string => $this->normalizeFileName($name),
            LegacyAttachmentFileNames::split($raw)
        )), static fn (string $name): bool => $name !== ''));
    }

    private function normalizeFileName(string $name): string
    {
        $name = str_replace('\\', '/', trim($name));

        return basename($name);
    }

    private function attachmentExists(int $projectId, string $name, int $exceptId): bool
    {
        return DB::table('project_manage_files')
            ->where('project_manage_id', $projectId)
            ->where('id', '<>', $exceptId)
            ->where(function ($q) use ($name) {
                $q->where('original_name', $name)
                    ->orWhere('stored_path', 'project-manages/'.$name);
            })
            ->exists();
    }

    /**
     * @param  list<string>  $names
     */
    private function allSplitNamesExist(int $projectId, array $names, int $exceptId): bool
    {
        foreach ($names as $name) {
            if (! $this->attachmentExists($projectId, $name, $exceptId)) {
                return false;
            }
        }

        return true;
    }
}
