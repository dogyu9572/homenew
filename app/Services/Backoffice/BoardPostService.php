<?php

namespace App\Services\Backoffice;

use App\Models\Board;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BoardPostService
{
    /**
     * 동적 테이블명 생성
     */
    public function getTableName(string $slug): string
    {
        return 'board_'.$slug;
    }

    /**
     * 게시글 목록 조회
     */
    public function getPosts(string $slug, Request $request)
    {
        $query = DB::table($this->getTableName($slug));

        $this->applySearchFilters($query, $request);

        // 목록 개수 설정
        $perPage = $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 20, 50, 100]) ? $perPage : 10;

        // 정렬 기능이 활성화된 게시판인지 확인
        $board = Board::where('slug', $slug)->first();
        if ($board && $board->enable_sorting) {
            // 정렬 기능 활성화: sort_order 내림차순 (큰 값이 위), 공지글, 최신순
            $posts = $query->orderBy('sort_order', 'desc')
                ->orderBy('is_notice', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage)
                ->withQueryString();
        } else {
            // 정렬 기능 비활성화: 공지글, 최신순
            $posts = $query->orderBy('is_notice', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage)
                ->withQueryString();
        }

        $this->transformDates($posts);

        return $posts;
    }

    /**
     * 검색 필터 적용
     */
    public function matchingPosts(string $slug, Request $request)
    {
        $query = DB::table($this->getTableName($slug));
        $this->applySearchFilters($query, $request);

        return $query->orderByDesc('id')->limit(5000)->get();
    }

    public function setCustomStatus(string $slug, array $ids, string $field, string $value): int
    {
        if (! preg_match('/^[a-z][a-z0-9_]{0,40}$/', $field)) {
            return 0;
        }

        $count = 0;
        foreach ($ids as $id) {
            $row = DB::table($this->getTableName($slug))->where('id', (int) $id)->first();
            if (! $row) {
                continue;
            }
            $data = json_decode((string) ($row->custom_fields ?? ''), true);
            if (! is_array($data)) {
                $data = [];
            }
            $data[$field] = $value;
            DB::table($this->getTableName($slug))->where('id', (int) $id)->update([
                'custom_fields' => json_encode($data, JSON_UNESCAPED_UNICODE),
                'updated_at' => now(),
            ]);
            $count++;
        }

        return $count;
    }

    private function applySearchFilters($query, Request $request): void
    {
        $dateField = $this->safeCustomField((string) $request->input('date_field', ''));
        if ($dateField !== '') {
            if ($request->filled('start_date')) {
                $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_fields, '$.\"{$dateField}\"')) >= ?", [$request->start_date]);
            }
            if ($request->filled('end_date')) {
                $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_fields, '$.\"{$dateField}\"')) <= ?", [$request->end_date.' 23:59:59']);
            }
        } else {
            if ($request->filled('start_date')) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $filters = $request->input('cf', []);
        if (is_array($filters)) {
            foreach ($filters as $name => $value) {
                $field = $this->safeCustomField((string) $name);
                if ($field === '' || ! is_string($value) || $value === '') {
                    continue;
                }
                $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_fields, '$.\"{$field}\"')) = ?", [$value]);
            }
        }

        if ($request->filled('keyword')) {
            $searchType = (string) $request->input('search_type', '');
            $field = $this->safeCustomField($searchType);
            if (in_array($searchType, ['title', 'content'], true)) {
                $this->applyKeywordSearch($query, $request->keyword, $searchType);
            } elseif ($searchType === 'all' || $searchType === '') {
                $keyword = '%'.$request->keyword.'%';
                $query->where(function ($inner) use ($keyword) {
                    $inner->where('title', 'like', $keyword)
                        ->orWhere('content', 'like', $keyword)
                        ->orWhere('custom_fields', 'like', $keyword);
                });
            } elseif ($field !== '') {
                $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_fields, '$.\"{$field}\"')) like ?", ['%'.$request->keyword.'%']);
            } else {
                $this->applyKeywordSearch($query, $request->keyword, $searchType);
            }
        }
    }

    private function safeCustomField(string $name): string
    {
        return preg_match('/^[a-z][a-z0-9_]{0,40}$/', $name) ? $name : '';
    }

    /**
     * 키워드 검색 적용
     */
    private function applyKeywordSearch($query, string $keyword, ?string $searchType): void
    {
        if ($searchType === 'title') {
            $query->where('title', 'like', "%{$keyword}%");
        } elseif ($searchType === 'content') {
            $query->where('content', 'like', "%{$keyword}%");
        } else {
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('content', 'like', "%{$keyword}%");
            });
        }
    }

    /**
     * 날짜 변환
     */
    private function transformDates($posts): void
    {
        $posts->getCollection()->transform(function ($post) {
            foreach (['created_at', 'updated_at'] as $dateField) {
                if (isset($post->$dateField) && is_string($post->$dateField)) {
                    $post->$dateField = Carbon::parse($post->$dateField);
                }
            }

            return $post;
        });
    }

    /**
     * 게시글 저장
     */
    public function storePost(string $slug, array $validated, Request $request, $board): int
    {
        $data = $this->preparePostData($validated, $request, $slug, $board);

        return DB::table($this->getTableName($slug))->insertGetId($data);
    }

    /**
     * 게시글 데이터 준비
     */
    private function preparePostData(array $validated, Request $request, string $slug, $board): array
    {
        // 정렬 기능 활성화된 게시판인 경우 자동으로 sort_order 설정
        $sortOrder = 0;
        if ($board && $board->enable_sorting) {
            $sortOrder = $this->getNextSortOrder($slug);
        }

        // is_active 필드 처리: 필드가 활성화되어 있고 요청에 있으면 사용, 없으면 기본값 true
        $isActive = true;
        if ($board && $board->isFieldEnabled('is_active')) {
            $isActive = $request->has('is_active') ? (bool) $request->input('is_active') : true;
        }

        return [
            'user_id' => Auth::id(),
            'author_name' => $validated['author_name'] ?? (Auth::user()->name ?? '관리자'),
            'title' => (string) ($validated['title'] ?? ''),
            'content' => $this->sanitizeContent((string) ($validated['content'] ?? '')),
            'category' => $validated['category'] ?? null,
            'is_notice' => $request->has('is_notice'),
            'is_secret' => $request->has('is_secret'),
            'is_active' => $isActive,
            'password' => $validated['password'] ?? null,
            'thumbnail' => $this->handleThumbnail($request, $slug),
            'attachments' => json_encode($this->handleAttachments($request, $slug)),
            'custom_fields' => $this->getCustomFieldsJson($request, $board),
            'view_count' => 0,
            'sort_order' => $sortOrder,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * 다음 정렬 순서 값을 계산합니다 (외부에서 호출 가능)
     */
    public function calculateNextSortOrder(string $slug): int
    {
        $maxSortOrder = DB::table($this->getTableName($slug))
            ->max('sort_order');

        return ($maxSortOrder ?? 0) + 1;
    }

    /**
     * 다음 정렬 순서 값을 가져옵니다 (정렬 기능 활성화된 게시판에만 사용)
     */
    private function getNextSortOrder(string $slug): int
    {
        return $this->calculateNextSortOrder($slug);
    }

    /**
     * HTML 내용 정리
     */
    private function sanitizeContent(string $content): string
    {
        $allowedTags = '<p><br><strong><em><u><ol><ul><li><h1><h2><h3><h4><h5><h6><blockquote><pre><code><table><thead><tbody><tr><td><th><a><img><div><span><iframe><video><source>';

        return strip_tags($content, $allowedTags);
    }

    /**
     * 썸네일 처리
     */
    private function handleThumbnail(Request $request, string $slug): ?string
    {
        // 썸네일 제거 요청이 있는 경우
        if ($request->has('remove_thumbnail')) {
            return null;
        }

        // 새 썸네일이 업로드된 경우
        if ($request->hasFile('thumbnail')) {
            return $request->file('thumbnail')->store('thumbnails/'.$slug, 'public');
        }

        // 기존 썸네일이 있는 경우 보존
        if ($request->has('existing_thumbnail')) {
            return $request->input('existing_thumbnail');
        }

        return null;
    }

    /**
     * 첨부파일 처리
     */
    private function handleAttachments(Request $request, string $slug): array
    {
        $attachments = [];
        $removeIndices = $request->input('remove_attachments', []);

        // 기존 첨부파일 보존 (제거 요청이 없는 것만)
        if ($request->has('existing_attachments')) {
            $existingAttachments = $request->input('existing_attachments', []);
            foreach ($existingAttachments as $index => $attachment) {
                // 제거 요청이 있는 인덱스는 제외
                if (in_array($index, $removeIndices)) {
                    continue;
                }

                if (is_string($attachment)) {
                    $attachment = json_decode($attachment, true);
                }
                if (is_array($attachment) && isset($attachment['name'], $attachment['path'])) {
                    $attachments[] = $attachment;
                }
            }
        }

        // 새 첨부파일 추가
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $file->store('uploads/'.$slug, 'public'),
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType(),
                ];
            }
        }

        return $attachments;
    }

    /**
     * 커스텀 필드 JSON 생성
     */
    private function getCustomFieldsJson(Request $request, $board): ?string
    {
        $customFields = $this->processCustomFields($request, $board);

        return ! empty($customFields) ? json_encode($customFields) : null;
    }

    /**
     * 커스텀 필드 처리
     */
    private function processCustomFields(Request $request, $board): array
    {
        if (! $board->custom_fields_config || ! is_array($board->custom_fields_config)) {
            return [];
        }

        $customFields = [];
        foreach ($board->custom_fields_config as $fieldConfig) {
            $fieldName = $fieldConfig['name'];
            $value = $request->input("custom_field_{$fieldName}");
            if (($fieldConfig['type'] ?? null) === 'file') {
                if ($request->hasFile("custom_field_{$fieldName}")) {
                    $value = $request->file("custom_field_{$fieldName}")->store('board-files/'.$board->slug, 'public');
                } else {
                    $value = $request->input('existing_custom_field_'.$fieldName, $value);
                }
            }
            $customFields[$fieldName] = ($fieldConfig['type'] ?? null) === 'editor'
                ? $this->sanitizeContent((string) $value)
                : $value;
        }

        return $customFields;
    }

    /**
     * 게시글 조회
     */
    public function getPost(string $slug, int $postId)
    {
        $post = DB::table($this->getTableName($slug))->where('id', $postId)->first();

        if (! $post) {
            return null;
        }

        $this->transformSinglePostDates($post);

        return $post;
    }

    /**
     * 단일 게시글 날짜 변환
     */
    private function transformSinglePostDates($post): void
    {
        foreach (['created_at', 'updated_at'] as $dateField) {
            if (isset($post->$dateField) && is_string($post->$dateField)) {
                $post->$dateField = Carbon::parse($post->$dateField);
            }
        }
    }

    /**
     * 게시글 수정
     */
    public function updatePost(string $slug, int $postId, array $validated, Request $request, $board): bool
    {
        // 기존 게시물 조회
        $existingPost = $this->getPost($slug, $postId);

        $data = $this->prepareUpdateData($validated, $request, $slug, $board, $existingPost);

        return DB::table($this->getTableName($slug))
            ->where('id', $postId)
            ->update($data);
    }

    /**
     * 수정 데이터 준비
     */
    private function prepareUpdateData(array $validated, Request $request, string $slug, $board, $existingPost = null): array
    {
        // is_active 필드 처리
        // 필드가 활성화되어 있고 요청에 있으면 사용
        // 필드가 비활성화되어 있으면 기존 값 유지 (수정 시)
        // 필드가 활성화되어 있지만 요청에 없으면 기본값 true
        $isActive = true;
        if ($board && $board->isFieldEnabled('is_active')) {
            $isActive = $request->has('is_active') ? (bool) $request->input('is_active') : true;
        } elseif ($existingPost && isset($existingPost->is_active)) {
            // 필드가 비활성화되어 있으면 기존 값 유지
            $isActive = (bool) $existingPost->is_active;
        }

        return [
            'title' => (string) ($validated['title'] ?? ''),
            'content' => $this->sanitizeContent((string) ($validated['content'] ?? '')),
            'category' => $validated['category'] ?? null,
            'is_notice' => $request->has('is_notice'),
            'is_secret' => $request->has('is_secret'),
            'is_active' => $isActive,
            'author_name' => $this->resolveAuthorNameForUpdate($validated, $existingPost),
            'password' => $validated['password'] ?? null,
            'thumbnail' => $this->handleThumbnail($request, $slug),
            'attachments' => json_encode($this->handleAttachments($request, $slug)),
            'custom_fields' => $this->getCustomFieldsJson($request, $board),
            'sort_order' => $request->input('sort_order', 0),
            'updated_at' => now(),
        ];
    }

    /**
     * 수정 시 작성자명: 요청값이 없거나 빈 값이면 기존 글 값 유지, 그것도 없으면 신규 등록과 동일하게 기본값
     * (작성자 필드가 게시판 설정에서 꺼진 경우 폼에 name이 없어 validated에 키가 없음 → NOT NULL 위반 방지)
     */
    private function resolveAuthorNameForUpdate(array $validated, $existingPost): string
    {
        $incoming = $validated['author_name'] ?? null;
        if (is_string($incoming) && trim($incoming) !== '') {
            return trim($incoming);
        }
        if ($existingPost && isset($existingPost->author_name) && is_string($existingPost->author_name) && trim($existingPost->author_name) !== '') {
            return $existingPost->author_name;
        }

        return '관리자';
    }

    /**
     * 게시글 삭제
     */
    public function deletePost(string $slug, int $postId): bool
    {
        $post = DB::table($this->getTableName($slug))->where('id', $postId)->first();

        if (! $post) {
            return false;
        }

        $this->deleteAttachments($post);

        return DB::table($this->getTableName($slug))->where('id', $postId)->delete();
    }

    /**
     * 첨부파일 삭제
     */
    private function deleteAttachments($post): void
    {
        if (! $post->attachments) {
            return;
        }

        $attachments = json_decode($post->attachments, true);
        if (! is_array($attachments)) {
            return;
        }

        foreach ($attachments as $attachment) {
            if (isset($attachment['path'])) {
                $filePath = storage_path('app/public/'.$attachment['path']);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }
    }

    /**
     * 일괄 삭제
     */
    public function bulkDelete(string $slug, array $postIds): int
    {
        return DB::table($this->getTableName($slug))->whereIn('id', $postIds)->delete();
    }
}
