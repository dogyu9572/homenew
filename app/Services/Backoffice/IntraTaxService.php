<?php

namespace App\Services\Backoffice;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class IntraTaxService
{
    private const SORT_MAP = [
        'B_idx' => 'b.legacy_b_idx',
        'B_Title' => 'b.title',
        'B_Name' => 'b.author_name',
        'B_Hit' => 'b.hit',
        'B_InpDate' => 'b.posted_at',
    ];

    public function getMenuConfig(string $board = 'intraTax'): object
    {
        return (object) [
            'B_Board' => $board,
            'B_PageListView' => 20,
        ];
    }

    public function getPosts(array $filters, int $perPage = 20, string $board = 'intraTax'): LengthAwarePaginator
    {
        $sortField = $filters['sortField'] ?? 'B_InpDate';
        $sort = strtolower((string) ($filters['sort'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';

        $query = DB::table('intra_tax_posts as b')
            ->select([
                'b.*',
                DB::raw('(SELECT COUNT(*) FROM intra_tax_post_comments c WHERE c.intra_tax_post_id = b.id) as comments_count'),
            ])
            ->where('b.board_code', $board);

        $this->applyFilters($query, $filters);

        $sortColumn = self::SORT_MAP[$sortField] ?? 'b.posted_at';

        $paginator = $query
            ->orderByDesc('b.is_notice')
            ->orderBy($sortColumn, $sort)
            ->orderByDesc('b.thread_ref')
            ->orderBy('b.thread_step')
            ->paginate($perPage)
            ->withQueryString();

        $paginator->getCollection()->transform(function ($row) {
            return $this->normalizePostForBackoffice($this->mapDbRowToLegacyPost($row));
        });

        return $paginator;
    }

    public function getPost(int $idx, string $board = 'intraTax', bool $withHit = true): ?object
    {
        if ($withHit) {
            DB::table('intra_tax_posts')
                ->where('board_code', $board)
                ->where('legacy_b_idx', $idx)
                ->update(['hit' => DB::raw('hit + 1')]);
        }

        $post = DB::table('intra_tax_posts')
            ->where('board_code', $board)
            ->where('legacy_b_idx', $idx)
            ->first();

        return $this->normalizePostForBackoffice($this->mapDbRowToLegacyPost($post));
    }

    public function getFiles(int $idx, string $board = 'intraTax'): Collection
    {
        $postId = $this->getPostPrimaryKey($idx, $board);
        if ($postId === null) {
            return collect();
        }

        return DB::table('intra_tax_post_files')
            ->where('intra_tax_post_id', $postId)
            ->orderBy('legacy_f_idx')
            ->get()
            ->map(static function ($file) {
                return (object) [
                    'F_Idx' => $file->legacy_f_idx,
                    'B_Idx' => null,
                    'B_Board' => 'intraTax',
                    'F_Name' => $file->original_name,
                    'F_InpDate' => $file->registered_at,
                ];
            });
    }

    public function getComments(int $idx, string $board = 'intraTax'): Collection
    {
        $postId = $this->getPostPrimaryKey($idx, $board);
        if ($postId === null) {
            return collect();
        }

        return DB::table('intra_tax_post_comments')
            ->where('intra_tax_post_id', $postId)
            ->orderBy('legacy_c_idx')
            ->get()
            ->map(static function ($c) {
                return (object) [
                    'C_Idx' => $c->legacy_c_idx,
                    'B_Idx' => null,
                    'B_Board' => 'intraTax',
                    'C_Name' => $c->author_name,
                    'C_Comment' => $c->body,
                    'C_Inpdate' => $c->posted_at,
                ];
            });
    }

    public function createPost(array $data, string $board = 'intraTax'): int
    {
        $nextLegacy = (int) DB::table('intra_tax_posts')->max('legacy_b_idx') + 1;
        if ($nextLegacy < 1) {
            $nextLegacy = 1;
        }
        $maxRef = (int) DB::table('intra_tax_posts')->where('board_code', $board)->max('thread_ref');
        $now = now();

        $insert = [
            'legacy_b_idx' => $nextLegacy,
            'board_code' => $board,
            'member_id' => auth()->user()->name ?? 'admin',
            'author_name' => $data['B_Name'] ?? (auth()->user()->name ?? '관리자'),
            'password' => $data['B_Password'] ?? null,
            'title' => $data['B_Title'],
            'content' => $data['B_Content'] ?? '',
            'has_file' => 'N',
            'is_secret' => ! empty($data['B_Secret']) ? 'Y' : 'N',
            'hit' => 0,
            'ip' => request()->ip(),
            'thread_ref' => $maxRef + 1,
            'thread_step' => 0,
            'thread_level' => 0,
            'comment_count' => 0,
            'work_state' => 'R',
            'is_notice' => ! empty($data['B_Notice']) ? 'Y' : 'N',
            'email' => $data['B_Email'] ?? null,
            'posted_at' => $now,
            'modified_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ];
        $this->applyCategoryPayload($insert, $data['B_Category'] ?? null);

        DB::table('intra_tax_posts')->insert($insert);

        return $nextLegacy;
    }

    public function updatePost(int $idx, array $data, string $board = 'intraTax'): void
    {
        $payload = [
            'author_name' => $data['B_Name'] ?? (auth()->user()->name ?? '관리자'),
            'title' => $data['B_Title'],
            'content' => $data['B_Content'] ?? '',
            'is_secret' => ! empty($data['B_Secret']) ? 'Y' : 'N',
            'is_notice' => ! empty($data['B_Notice']) ? 'Y' : 'N',
            'email' => $data['B_Email'] ?? null,
            'modified_at' => now(),
            'updated_at' => now(),
        ];
        $this->applyCategoryPayload($payload, $data['B_Category'] ?? null);

        if (! empty($data['B_Password'])) {
            $payload['password'] = $data['B_Password'];
        }

        DB::table('intra_tax_posts')
            ->where('board_code', $board)
            ->where('legacy_b_idx', $idx)
            ->update($payload);
    }

    public function deletePost(int $idx, string $board = 'intraTax'): void
    {
        DB::table('intra_tax_posts')
            ->where('board_code', $board)
            ->where('legacy_b_idx', $idx)
            ->delete();
    }

    public function deletePosts(array $ids, string $board = 'intraTax'): void
    {
        foreach ($ids as $id) {
            $this->deletePost((int) $id, $board);
        }
    }

    public function addComment(int $idx, array $data, string $board = 'intraTax'): void
    {
        $postId = $this->getPostPrimaryKey($idx, $board);
        if ($postId === null) {
            return;
        }

        $nextIdx = (int) DB::table('intra_tax_post_comments')->max('legacy_c_idx') + 1;
        $now = now();

        DB::table('intra_tax_post_comments')->insert([
            'intra_tax_post_id' => $postId,
            'legacy_c_idx' => $nextIdx,
            'member_id' => auth()->user()->name ?? 'admin',
            'author_name' => $data['C_Name'] ?? (auth()->user()->name ?? '관리자'),
            'password' => $data['C_Passwd'] ?? null,
            'body' => $data['C_Comment'],
            'ip' => request()->ip(),
            'posted_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('intra_tax_posts')
            ->where('id', $postId)
            ->update(['comment_count' => DB::raw('comment_count + 1')]);
    }

    public function deleteComment(int $idx, int $commentIdx, string $board = 'intraTax'): void
    {
        $postId = $this->getPostPrimaryKey($idx, $board);
        if ($postId === null) {
            return;
        }

        $deleted = DB::table('intra_tax_post_comments')
            ->where('intra_tax_post_id', $postId)
            ->where('legacy_c_idx', $commentIdx)
            ->delete();

        if ($deleted > 0) {
            DB::table('intra_tax_posts')
                ->where('id', $postId)
                ->update(['comment_count' => DB::raw('IF(comment_count > 0, comment_count - 1, 0)')]);
        }
    }

    /**
     * 신규 업로드 파일을 파일별 1레코드로 저장한다.
     *
     * @param  array<int, UploadedFile>  $attachments
     */
    public function addAttachments(int $idx, array $attachments, string $board = 'intraTax'): void
    {
        $postId = $this->getPostPrimaryKey($idx, $board);
        if ($postId === null || $attachments === []) {
            return;
        }

        $nextLegacyFIdx = (int) DB::table('intra_tax_post_files')->max('legacy_f_idx') + 1;
        if ($nextLegacyFIdx < 1) {
            $nextLegacyFIdx = 1;
        }
        $now = now();
        $inserted = 0;

        foreach ($attachments as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $targetName = $this->resolveUploadFileName((string) $file->getClientOriginalName());
            Storage::disk('public')->putFileAs('intra-tax', $file, $targetName);

            DB::table('intra_tax_post_files')->insert([
                'intra_tax_post_id' => $postId,
                'legacy_f_idx' => $nextLegacyFIdx++,
                'original_name' => $targetName,
                'registered_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $inserted++;
        }

        if ($inserted > 0) {
            DB::table('intra_tax_posts')
                ->where('id', $postId)
                ->update([
                    'has_file' => 'Y',
                    'updated_at' => $now,
                ]);
        }
    }

    /**
     * 화면에 남겨둔 첨부 토큰(legacy_f_idx)만 유지하고 나머지는 삭제한다.
     *
     * @param  array<int, mixed>  $tokens
     */
    public function syncExistingAttachments(int $idx, array $tokens, string $board = 'intraTax'): void
    {
        $postId = $this->getPostPrimaryKey($idx, $board);
        if ($postId === null) {
            return;
        }

        $keepIds = array_values(array_unique(array_filter(array_map(
            static fn ($v): int => (int) $v,
            $tokens
        ), static fn (int $v): bool => $v > 0)));

        $query = DB::table('intra_tax_post_files')
            ->where('intra_tax_post_id', $postId);

        if ($keepIds !== []) {
            $query->whereNotIn('legacy_f_idx', $keepIds);
        }

        $query->delete();

        $remainCount = (int) DB::table('intra_tax_post_files')
            ->where('intra_tax_post_id', $postId)
            ->count();

        DB::table('intra_tax_posts')
            ->where('id', $postId)
            ->update([
                'has_file' => $remainCount > 0 ? 'Y' : 'N',
                'updated_at' => now(),
            ]);
    }

    public function resolveLegacyAttachmentPath(string $board, string $fileName): ?string
    {
        $name = trim($fileName);
        if ($name === '') {
            return null;
        }

        $candidates = [
            storage_path('app/public/intra-tax/'.$name),
            storage_path('app/public/'.$board.'/'.$name),
            public_path('vip/DATA/'.$board.'/'.$name),
            base_path('vip/DATA/'.$board.'/'.$name),
        ];

        foreach ($candidates as $path) {
            if (File::exists($path)) {
                return $path;
            }
        }

        return null;
    }

    public function canAccessPost(?object $post, ?string $passwordInput, string $board = 'intraTax'): bool
    {
        if ($post === null) {
            return false;
        }

        if (($post->B_Secret ?? 'N') !== 'Y') {
            return true;
        }

        if (auth()->check()) {
            return true;
        }

        $sessionKey = $this->getSecretSessionKey($board, (int) $post->B_idx);
        if (session()->get($sessionKey) === true) {
            return true;
        }

        if ($passwordInput === null || $passwordInput === '') {
            return false;
        }

        if ((string) ($post->B_Password ?? '') !== $passwordInput) {
            return false;
        }

        session()->put($sessionKey, true);

        return true;
    }

    public function getSecretSessionKey(string $board, int $idx): string
    {
        return 'intra_tax_secret.'.$board.'.'.$idx;
    }

    private function getPostPrimaryKey(int $legacyIdx, string $board): ?int
    {
        $id = DB::table('intra_tax_posts')
            ->where('board_code', $board)
            ->where('legacy_b_idx', $legacyIdx)
            ->value('id');

        return $id !== null ? (int) $id : null;
    }

    /**
     * DB 행을 기존 Blade/라우트와 호환되도록 ECMS_Board 형태 속성으로 맞춘다.
     */
    private function mapDbRowToLegacyPost(?object $row): ?object
    {
        if ($row === null) {
            return null;
        }

        $o = new \stdClass;
        $o->B_idx = (int) $row->legacy_b_idx;
        $o->B_Board = $row->board_code;
        $o->M_ID = $row->member_id;
        $o->B_Name = $row->author_name;
        $o->B_Password = $row->password;
        $o->B_Title = $row->title;
        $o->B_Content = $row->content;
        $o->B_File = $row->has_file;
        $o->B_Secret = $row->is_secret;
        $o->B_Hit = (int) $row->hit;
        $o->B_IP = $row->ip;
        $o->B_Ref = (int) $row->thread_ref;
        $o->B_Step = (int) $row->thread_step;
        $o->B_Level = (int) $row->thread_level;
        $o->B_Comment = (int) $row->comment_count;
        $o->B_State = $row->work_state;
        $o->B_Notice = $row->is_notice;
        $o->B_Email = $row->email;
        $o->B_Design = $row->design ?? null;
        $o->B_Program = $row->program ?? null;
        $o->B_Flash = $row->flash ?? null;
        $o->B_Etc = $row->etc ?? null;
        $o->B_Category = $row->category ?? null;
        $o->B_InpDate = $row->posted_at;
        $o->B_ModDate = $row->modified_at;

        if (property_exists($row, 'comments_count')) {
            $o->comments_count = (int) $row->comments_count;
        }

        return $o;
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['start_date'])) {
            $query->whereDate('b.posted_at', '>=', $filters['start_date']);
        }

        if (! empty($filters['end_date'])) {
            $query->whereDate('b.posted_at', '<=', $filters['end_date']);
        }

        if (! empty($filters['category'])) {
            $keyword = $filters['category'];
            $query->where(function (Builder $q) use ($keyword) {
                $q->where('b.category', $keyword)
                    ->orWhere('b.etc', $keyword)
                    ->orWhere('b.program', $keyword);
            });
        }

        if (! empty($filters['keyword'])) {
            $query->where('b.title', 'like', '%'.$filters['keyword'].'%');
        }

        if (! empty($filters['state'])) {
            $query->where('b.work_state', $filters['state']);
        }
    }

    /**
     * 목록·상세에서 표시용으로 레거시 엔티티 인코딩을 풀고, 분류 컬럼 별칭을 B_Category로 맞춘다.
     */
    private function normalizePostForBackoffice(?object $post): ?object
    {
        if ($post === null) {
            return null;
        }

        $resolvedCategory = $this->resolveCategoryFromLegacyRow($post);
        if ($resolvedCategory !== '') {
            $post->B_Category = $resolvedCategory;
        }

        if (isset($post->B_Content) && is_string($post->B_Content)) {
            $post->B_Content = $this->decodeLegacyBoardHtml($post->B_Content);
        }

        return $post;
    }

    private function decodeLegacyBoardHtml(?string $raw): string
    {
        if ($raw === null || $raw === '') {
            return '';
        }

        $s = $raw;
        for ($i = 0; $i < 6; $i++) {
            $decoded = html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($decoded === $s) {
                break;
            }
            $s = $decoded;
        }

        return $s;
    }

    /**
     * 사내 스킨은 분류를 B_Etc(신규 테이블 etc)에 두는 경우가 많아 etc를 우선한다.
     */
    private function resolveCategoryFromLegacyRow(object $post): string
    {
        $keysPriority = [
            'B_Etc', 'b_etc',
            'B_Category', 'b_category',
            'B_Class', 'b_class',
            'B_Type', 'b_type',
            'B_Program', 'b_program',
        ];

        foreach ($keysPriority as $key) {
            if (! property_exists($post, $key)) {
                continue;
            }
            $v = trim((string) ($post->{$key} ?? ''));
            if ($v !== '') {
                return $this->decodeLegacyBoardHtml($v);
            }
        }

        return '';
    }

    /**
     * 분류 입력값을 category·etc에 동시 반영(레거시 ECMS와 동일한 이중 저장 패턴).
     */
    private function applyCategoryPayload(array &$payload, ?string $category): void
    {
        $value = ($category !== null && trim($category) !== '') ? trim($category) : null;
        $payload['category'] = $value;
        $payload['etc'] = $value;
    }

    private function resolveUploadFileName(string $originalName): string
    {
        $clean = trim($originalName);
        if ($clean === '') {
            return uniqid('attach_', true);
        }

        if (! Storage::disk('public')->exists('intra-tax/'.$clean)) {
            return $clean;
        }

        $dotPos = strrpos($clean, '.');
        if ($dotPos === false) {
            return pathinfo($clean, PATHINFO_FILENAME).'_'.now()->format('YmdHisv');
        }

        $name = substr($clean, 0, $dotPos);
        $ext = substr($clean, $dotPos);

        return $name.'_'.now()->format('YmdHisv').$ext;
    }
}
