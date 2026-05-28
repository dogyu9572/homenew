<?php

namespace App\Services\Backoffice;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProjectManageService
{
    private const DEFAULT_STATES = [
        '계약', '기획', '디자인', '퍼블리싱', '개발', '작업완료',
        '수정사항', '유지보수', '보류', '취소', '광고기획', '호스팅',
    ];

    public function getProjects(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $sortField = $this->resolveSortField($filters['sortField'] ?? '');
        $sortDirection = strtolower((string) ($filters['sort'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';
        $query = DB::table('project_manages as p')
            ->select([
                'p.id as idx',
                'p.ProjectName as CompanyName',
                'p.DomainUrl as HomepageUrl',
                'p.CompanyPerson as ManagerName',
                'p.CpEmail as CompanyEmail',
                'p.CpTel as CompanyHp',
                'p.CpPhone as MobilePhone',
                'p.IntraBusiness as InternalSalesName',
                'p.IntraManager as InternalPlanningName',
                'p.ProjectGubun as CategoryBusiness',
                'p.ProjectIngState',
                'p.HostingEdate',
                DB::raw('(SELECT pm.PaymentDate FROM project_manage_payments pm WHERE pm.project_manage_id = p.id ORDER BY pm.id DESC LIMIT 1) as LastPayDate'),
                'p.Regdate',
            ]);

        $this->applyFilters($query, $filters);

        return $query
            ->orderBy($sortField, $sortDirection)
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getProject(int $idx): ?object
    {
        $row = DB::table('project_manages')
            ->where('id', $idx)
            ->first();

        if ($row !== null) {
            $row->idx = (int) $row->id;
            $this->decodeTextColumns($row);
        }

        return $row;
    }

    public function createProject(array $data): int
    {
        $this->ensureProjectManageTable();
        $payload = $this->sanitizePayload($data);
        $payload['Regdate'] = now();
        $payload['created_at'] = now();
        $payload['updated_at'] = now();

        return (int) DB::table('project_manages')->insertGetId($payload);
    }

    public function updateProject(int $idx, array $data): array
    {
        $this->ensureProjectManageTable();
        $before = DB::table('project_manages')->where('id', $idx)->first();
        if ($before === null) {
            return [];
        }

        $payload = $this->sanitizePayload($data);
        $changedColumns = [];
        foreach ($payload as $column => $newValue) {
            $oldValue = $before->{$column} ?? null;
            if (! $this->isSameValue($oldValue, $newValue)) {
                $changedColumns[] = $column;
            }
        }

        $payload['updated_at'] = now();
        DB::table('project_manages')->where('id', $idx)->update($payload);

        return $this->resolveChangedSections($changedColumns);
    }

    public function getModifyLogs(int $projectIdx): Collection
    {
        if (! DB::getSchemaBuilder()->hasTable('project_manage_modify_logs')) {
            return collect();
        }

        return DB::table('project_manage_modify_logs')
            ->where('project_manage_id', $projectIdx)
            ->orderByDesc('regdate')
            ->orderByDesc('id')
            ->get();
    }

    public function addModifyLog(int $projectIdx, string $memo, ?string $userName = null): void
    {
        if (! DB::getSchemaBuilder()->hasTable('project_manage_modify_logs')) {
            return;
        }

        $project = DB::table('project_manages')->where('id', $projectIdx)->first();
        if ($project === null) {
            return;
        }

        $now = now();
        DB::table('project_manage_modify_logs')->insert([
            'project_manage_id' => $projectIdx,
            'legacy_project_idx' => $project->legacy_idx ?? null,
            'project_name' => $project->ProjectName ?? null,
            'company_name' => $project->CompanyName ?? null,
            'user_name' => $userName ?: '관리자',
            'memo' => $memo,
            'regdate' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function deleteProject(int $idx): void
    {
        $this->ensureProjectManageTable();
        $this->deleteAllAttachments($idx);
        DB::table('project_manages')->where('id', $idx)->delete();
    }

    public function deleteProjects(array $ids): void
    {
        $this->ensureProjectManageTable();
        foreach ($ids as $id) {
            $this->deleteAllAttachments((int) $id);
        }
        DB::table('project_manages')->whereIn('id', $ids)->delete();
    }

    public function getAttachmentItems(int $projectIdx): Collection
    {
        if (! $this->hasAttachmentTable()) {
            return collect();
        }

        return DB::table('project_manage_files')
            ->where('project_manage_id', $projectIdx)
            ->orderBy('id')
            ->get()
            ->map(function ($row) use ($projectIdx) {
                $displayName = (string) ($row->original_name ?? '');
                $resolvedPath = $this->resolveAttachmentPath($projectIdx, $displayName, (string) ($row->stored_path ?? ''));

                $sizeBytes = $row->size_bytes !== null ? (int) $row->size_bytes : null;
                if ($resolvedPath !== null && Storage::disk('public')->exists($resolvedPath)) {
                    $sizeBytes = (int) Storage::disk('public')->size($resolvedPath);
                }

                if ($displayName === '' && $resolvedPath !== null) {
                    $displayName = basename($resolvedPath);
                }

                $tokenPayload = [
                    'stored_path' => $resolvedPath,
                    'display_name' => $displayName,
                ];

                return (object) [
                    'row_idx' => (int) $row->id,
                    'display_name' => $displayName,
                    'stored_path' => $resolvedPath,
                    'token' => base64_encode(json_encode($tokenPayload, JSON_UNESCAPED_UNICODE)),
                    'size_bytes' => $sizeBytes,
                ];
            })
            ->values();
    }

    public function getAttachment(int $projectIdx, int $attachmentIdx): ?object
    {
        if (! $this->hasAttachmentTable()) {
            return null;
        }

        $row = DB::table('project_manage_files')
            ->where('project_manage_id', $projectIdx)
            ->where('id', $attachmentIdx)
            ->first();

        if ($row === null) {
            return null;
        }

        $row->stored_path = $this->resolveAttachmentPath(
            $projectIdx,
            (string) ($row->original_name ?? ''),
            (string) ($row->stored_path ?? '')
        );

        return $row;
    }

    public function deleteAttachmentsNotIn(int $projectIdx, array $keepAttachmentIds): void
    {
        if (! $this->hasAttachmentTable()) {
            return;
        }

        $query = DB::table('project_manage_files')->where('project_manage_id', $projectIdx);
        if (! empty($keepAttachmentIds)) {
            $query->whereNotIn('id', $keepAttachmentIds);
        }

        $rows = $query->get();
        foreach ($rows as $row) {
            $path = (string) ($row->stored_path ?? '');
            $this->deleteAttachmentFile($path);
        }

        $deleteQuery = DB::table('project_manage_files')->where('project_manage_id', $projectIdx);
        if (! empty($keepAttachmentIds)) {
            $deleteQuery->whereNotIn('id', $keepAttachmentIds);
        }
        $deleteQuery->delete();
    }

    public function addAttachments(int $projectIdx, array $files): int
    {
        if (! $this->hasAttachmentTable()) {
            return 0;
        }

        $addedCount = 0;
        foreach ($files as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $originalName = $file->getClientOriginalName();
            $storedBasename = Str::uuid().'_'.$originalName;
            Storage::disk('public')->putFileAs('project-manages', $file, $storedBasename);
            $relativePath = 'project-manages/'.$storedBasename;

            DB::table('project_manage_files')->insert([
                'project_manage_id' => $projectIdx,
                'stored_path' => $relativePath,
                'original_name' => $originalName,
                'size_bytes' => $file->getSize(),
                'regdate' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $addedCount++;
        }

        return $addedCount;
    }

    public function deleteAttachments(int $projectIdx, array $attachmentIds): void
    {
        if (! $this->hasAttachmentTable() || empty($attachmentIds)) {
            return;
        }

        $rows = DB::table('project_manage_files')
            ->where('project_manage_id', $projectIdx)
            ->whereIn('id', $attachmentIds)
            ->get();

        foreach ($rows as $row) {
            $path = (string) ($row->stored_path ?? '');
            $this->deleteAttachmentFile($path);
        }

        DB::table('project_manage_files')
            ->where('project_manage_id', $projectIdx)
            ->whereIn('id', $attachmentIds)
            ->delete();
    }

    public function syncExistingAttachments(int $projectIdx, array $tokens): array
    {
        if (! $this->hasAttachmentTable()) {
            return ['removed_count' => 0, 'kept_count' => 0];
        }

        $beforeCount = (int) DB::table('project_manage_files')
            ->where('project_manage_id', $projectIdx)
            ->count();

        $keepItems = collect();
        foreach ($tokens as $token) {
            $decoded = json_decode(base64_decode((string) $token, true) ?: '', true);
            if (! is_array($decoded) || empty($decoded['display_name'])) {
                continue;
            }
            $keepItems->push([
                'stored_path' => $decoded['stored_path'] ?? null,
                'display_name' => (string) $decoded['display_name'],
            ]);
        }

        $keepStoredPaths = $keepItems->pluck('stored_path')->filter()->values()->all();
        $rows = DB::table('project_manage_files')->where('project_manage_id', $projectIdx)->get();
        foreach ($rows as $row) {
            $path = (string) ($row->stored_path ?? '');
            if ($path !== '' && ! in_array($path, $keepStoredPaths, true)) {
                $this->deleteAttachmentFile($path);
            }
        }

        DB::table('project_manage_files')->where('project_manage_id', $projectIdx)->delete();

        foreach ($keepItems as $item) {
            DB::table('project_manage_files')->insert([
                'project_manage_id' => $projectIdx,
                'stored_path' => $item['stored_path'] ?: null,
                'original_name' => $item['display_name'],
                'regdate' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $keptCount = (int) $keepItems->count();

        return [
            'removed_count' => max(0, $beforeCount - $keptCount),
            'kept_count' => $keptCount,
        ];
    }

    public function exportProjects(array $filters): Collection
    {
        $sortField = $this->resolveSortField($filters['sortField'] ?? 'idx');
        $sortDirection = strtolower((string) ($filters['sort'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';
        $query = DB::table('project_manages as p')
            ->select([
                'p.id as idx',
                'p.ProjectName as CompanyName',
                'p.DomainUrl as HomepageUrl',
                'p.CompanyPerson as ManagerName',
                'p.CpEmail as CompanyEmail',
                'p.CpTel as CompanyHp',
                'p.CpPhone as MobilePhone',
                'p.IntraBusiness as InternalSalesName',
                'p.IntraManager as InternalPlanningName',
                'p.ProjectGubun as CategoryBusiness',
                'p.ProjectIngState',
                'p.HostingEdate',
                DB::raw('(SELECT pm.PaymentDate FROM project_manage_payments pm WHERE pm.project_manage_id = p.id ORDER BY pm.id DESC LIMIT 1) as LastPayDate'),
                'p.Regdate',
            ]);

        $this->applyFilters($query, $filters);

        return $query
            ->orderBy($sortField, $sortDirection)
            ->get();
    }

    public function getMoneyHistories(int $projectIdx)
    {
        return DB::table('project_manage_payments')
            ->where('project_manage_id', $projectIdx)
            ->orderByDesc('id')
            ->get();
    }

    public function getStateCounts(array $filters): array
    {
        $query = DB::table('project_manages as p');
        $this->applyFilters($query, array_merge($filters, ['states' => []]));
        $rows = $query->select('p.ProjectIngState as state', DB::raw('COUNT(*) as cnt'))
            ->groupBy('p.ProjectIngState')
            ->pluck('cnt', 'state')
            ->toArray();

        $counts = [];
        foreach (self::DEFAULT_STATES as $state) {
            $counts[$state] = (int) ($rows[$state] ?? 0);
        }

        return $counts;
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['states'])) {
            $query->whereIn('p.ProjectIngState', $filters['states']);
        }

        if (! empty($filters['HostingSdate'])) {
            $query->whereDate('p.HostingEdate', '>=', $filters['HostingSdate']);
        }

        if (! empty($filters['HostingEdate'])) {
            $query->whereDate('p.HostingEdate', '<=', $filters['HostingEdate']);
        }

        if (! empty($filters['TeamUser'])) {
            $query->where('p.IntraBusiness', $filters['TeamUser']);
        }

        if (! empty($filters['gubun'])) {
            $query->where('p.ProjectGubun', $filters['gubun']);
        }

        if (! empty($filters['FindValue'])) {
            $keyword = $filters['FindValue'];
            $query->where(function (Builder $builder) use ($keyword) {
                $builder->where('p.ProjectName', 'like', '%'.$keyword.'%')
                    ->orWhere('p.DomainUrl', 'like', '%'.$keyword.'%')
                    ->orWhere('p.CompanyPerson', 'like', '%'.$keyword.'%')
                    ->orWhere('p.CpEmail', 'like', '%'.$keyword.'%');
            });
        }
    }

    private function resolveSortField(string $sortField): string|\Illuminate\Database\Query\Expression
    {
        return match ($sortField) {
            'ProjectName' => 'p.ProjectName',
            'HostingEdate' => 'p.HostingEdate',
            'LastPayDate' => DB::raw('(SELECT pm.PaymentDate FROM project_manage_payments pm WHERE pm.project_manage_id = p.id ORDER BY pm.id DESC LIMIT 1)'),
            'ProjectIngState' => 'p.ProjectIngState',
            default => 'p.id',
        };
    }

    private function ensureProjectManageTable(): void
    {
        abort_unless(DB::getSchemaBuilder()->hasTable('project_manages'), 500, 'project_manages 테이블이 필요합니다.');
    }

    private function sanitizePayload(array $data): array
    {
        // SiteType은 create/edit 폼에 없음 -> 수정 시 기존 DB 값 유지(컬럼에서 제외)
        $columns = [
            'ProjectName', 'CompanyName', 'DomainUrl', 'DomainAdminUrl', 'TestUrl', 'DomainSubUrl',
            'DomainCompany', 'ServerCompany', 'DevelopLanguage', 'DbType', 'DbHost', 'DbName', 'DbId', 'DbPasswd',
            'FtpUrl', 'FtpPort', 'FtpId', 'FtpPasswd', 'Domain_Etc', 'CompanyPerson', 'CpEmail', 'CpTel', 'CpPhone',
            'CompanyAddPerson', 'CompanyCollaborator', 'ProjectGubun', 'ProjectIngState', 'ReferSiteUrl', 'ReferEtc',
            'IntraBusiness', 'IntraManager', 'IntraDesiner', 'IntraPublisher', 'IntraProgramer',
            'ProjectSdate', 'ProjectEdate', 'HostingSdate', 'HostingEdate',
            'LicensessName', 'LicensessNumber', 'LicensessTel', 'LicensessFax', 'LicensessPost', 'LicensessAddr',
            'ProjectMemo', 'SpeicalMemo',
        ];

        $payload = [];
        foreach ($columns as $column) {
            $payload[$column] = $this->decodeHtmlEntities($data[$column] ?? null);
        }

        return $payload;
    }

    private function decodeTextColumns(object $row): void
    {
        foreach ([
            'Domain_Etc',
            'CompanyAddPerson',
            'CompanyCollaborator',
            'ReferSiteUrl',
            'ReferEtc',
            'ProjectMemo',
            'SpeicalMemo',
        ] as $column) {
            if (property_exists($row, $column)) {
                $row->{$column} = $this->decodeHtmlEntities($row->{$column});
            }
        }
    }

    private function decodeHtmlEntities(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $decoded = $value;
        for ($i = 0; $i < 3; $i++) {
            $next = html_entity_decode($decoded, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($next === $decoded) {
                break;
            }
            $decoded = $next;
        }

        return $decoded;
    }

    private function hasAttachmentTable(): bool
    {
        return DB::getSchemaBuilder()->hasTable('project_manage_files');
    }

    private function deleteAllAttachments(int $projectIdx): void
    {
        if (! $this->hasAttachmentTable()) {
            return;
        }

        $rows = DB::table('project_manage_files')
            ->where('project_manage_id', $projectIdx)
            ->get();

        foreach ($rows as $row) {
            $path = (string) ($row->stored_path ?? '');
            $this->deleteAttachmentFile($path);
        }

        DB::table('project_manage_files')->where('project_manage_id', $projectIdx)->delete();
    }

    private function deleteAttachmentFile(string $path): void
    {
        if ($path === '') {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    private function resolveAttachmentPath(int $projectIdx, string $originalName, string $storedPath): ?string
    {
        $disk = Storage::disk('public');
        foreach ($this->buildAttachmentCandidates($projectIdx, $originalName, $storedPath) as $candidate) {
            if ($candidate !== '' && $disk->exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function buildAttachmentCandidates(int $projectIdx, string $originalName, string $storedPath): array
    {
        $candidates = [];

        if ($storedPath !== '') {
            $candidates[] = $storedPath;
        }

        $nameVariants = [];
        foreach ([$originalName, html_entity_decode($originalName, ENT_QUOTES | ENT_HTML5, 'UTF-8')] as $baseName) {
            $baseName = trim($baseName);
            if ($baseName === '') {
                continue;
            }
            $nameVariants[] = $baseName;
            $nameVariants[] = mb_encode_numericentity($baseName, [0x80, 0x10FFFF, 0, 0xFFFFFF], 'UTF-8');
        }

        $nameVariants = array_values(array_unique(array_filter($nameVariants, static fn ($name) => $name !== '')));
        foreach ($nameVariants as $name) {
            $candidates[] = 'project-manages/'.$name;
            $candidates[] = 'project-manages/'.$projectIdx.'/'.$name;
        }

        return array_values(array_unique($candidates));
    }

    private function isSameValue(mixed $oldValue, mixed $newValue): bool
    {
        $normalize = static fn (mixed $value): string => trim((string) ($value ?? ''));

        return $normalize($oldValue) === $normalize($newValue);
    }

    private function resolveChangedSections(array $changedColumns): array
    {
        $sectionMap = [
            '도메인 및 서버정보' => [
                'ProjectName', 'DomainUrl', 'DomainAdminUrl', 'TestUrl', 'DomainSubUrl',
                'DomainCompany', 'ServerCompany', 'DevelopLanguage', 'DbType', 'DbHost',
                'DbName', 'DbId', 'DbPasswd', 'FtpUrl', 'FtpPort', 'FtpId', 'FtpPasswd', 'Domain_Etc',
            ],
            '업체 담당자 정보' => [
                'CompanyPerson', 'CpEmail', 'CpTel', 'CpPhone', 'CompanyAddPerson', 'CompanyCollaborator',
            ],
            '상세내용' => [
                'ProjectGubun', 'ProjectIngState', 'ReferSiteUrl', 'ReferEtc',
                'IntraBusiness', 'IntraManager', 'IntraDesiner', 'IntraPublisher', 'IntraProgramer',
                'ProjectSdate', 'ProjectEdate', 'HostingSdate', 'HostingEdate', 'ProjectMemo', 'SpeicalMemo',
            ],
            '업체 상세정보' => [
                'CompanyName', 'LicensessName', 'LicensessNumber', 'LicensessTel',
                'LicensessFax', 'LicensessPost', 'LicensessAddr',
            ],
        ];

        $sections = [];
        foreach ($sectionMap as $section => $columns) {
            if (array_intersect($changedColumns, $columns) !== []) {
                $sections[] = $section;
            }
        }

        if ($sections === [] && $changedColumns !== []) {
            $sections[] = '기타';
        }

        return $sections;
    }
}
