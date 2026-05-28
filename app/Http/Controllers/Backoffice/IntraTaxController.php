<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\BackofficeIntraTaxCommentRequest;
use App\Http\Requests\BackofficeIntraTaxPostRequest;
use App\Services\Backoffice\IntraTaxService;
use Illuminate\Http\Request;

class IntraTaxController extends Controller
{
    private const INTRA_TAX_SORT_FIELDS = ['B_idx', 'B_Title', 'B_Name', 'B_Hit', 'B_InpDate'];

    public function __construct(private readonly IntraTaxService $intraTaxService) {}

    /**
     * 목록 GET 쿼리(page, per_page, 필터, 정렬)를 안전하게 정리
     *
     * @return array<string, int|string>
     */
    private function intraTaxIndexQueryFromRequest(Request $request): array
    {
        return $this->sanitizeIntraTaxIndexQuery($request->query());
    }

    /**
     * 수정·삭제·unlock 등 POST의 return_* 필드 → 목록 리다이렉트 쿼리
     *
     * @return array<string, int|string>
     */
    private function intraTaxIndexQueryFromReturnRequest(Request $request): array
    {
        $keys = ['page', 'per_page', 'start_date', 'end_date', 'category', 'keyword', 'state', 'sortField', 'sort'];
        $raw = [];
        foreach ($keys as $key) {
            $rv = $request->input('return_'.$key);
            if ($rv !== null && $rv !== '') {
                $raw[$key] = $rv;
            }
        }

        return $this->sanitizeIntraTaxIndexQuery($raw);
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array<string, int|string>
     */
    private function sanitizeIntraTaxIndexQuery(array $raw): array
    {
        $out = [];
        if (isset($raw['page']) && (int) $raw['page'] >= 1) {
            $out['page'] = (int) $raw['page'];
        }
        $allowedPer = [10, 20, 50, 100];
        if (isset($raw['per_page']) && in_array((int) $raw['per_page'], $allowedPer, true)) {
            $out['per_page'] = (int) $raw['per_page'];
        }
        foreach (['start_date', 'end_date'] as $dateKey) {
            if (! empty($raw[$dateKey]) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $raw[$dateKey])) {
                $out[$dateKey] = (string) $raw[$dateKey];
            }
        }
        foreach (['category', 'keyword'] as $textKey) {
            if (! empty($raw[$textKey])) {
                $out[$textKey] = mb_substr((string) $raw[$textKey], 0, 255);
            }
        }
        $stateAllowed = ['A', 'R', 'I', 'E', 'H'];
        if (! empty($raw['state']) && in_array((string) $raw['state'], $stateAllowed, true)) {
            $out['state'] = (string) $raw['state'];
        }
        if (! empty($raw['sortField']) && in_array((string) $raw['sortField'], self::INTRA_TAX_SORT_FIELDS, true)) {
            $out['sortField'] = (string) $raw['sortField'];
        }
        if (! empty($raw['sort']) && in_array(strtolower((string) $raw['sort']), ['asc', 'desc'], true)) {
            $out['sort'] = strtolower((string) $raw['sort']);
        }

        return $out;
    }

    public function index(Request $request)
    {
        $filters = [
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'category' => $request->get('category'),
            'keyword' => $request->get('keyword'),
            'state' => $request->get('state'),
            'sortField' => $request->get('sortField', 'B_InpDate'),
            'sort' => $request->get('sort', 'desc'),
        ];

        $menu = $this->intraTaxService->getMenuConfig('intraTax');
        $perPage = (int) $request->get('per_page', (int) ($menu->B_PageListView ?? 20));
        $perPage = in_array($perPage, [10, 20, 50, 100], true) ? $perPage : 20;

        $posts = $this->intraTaxService->getPosts($filters, $perPage, 'intraTax');

        $listQuery = $this->intraTaxIndexQueryFromRequest($request);

        return view('backoffice.intra-tax.index', compact('posts', 'filters', 'perPage', 'menu', 'listQuery'));
    }

    public function create(Request $request)
    {
        $defaultContent = implode("\n\n", [
            '<p><strong>상호명:</strong>&nbsp; </p>',
            '<p><strong>날짜:</strong>&nbsp; </p>',
            '<p><strong>발행 내역:</strong>&nbsp; </p>',
            '<p><strong>금액:</strong>&nbsp; </p>',
            '<p><strong>담당자:</strong>&nbsp; </p>',
            '<p><strong>※ 첫 계약시 - 사업자 등록증 첨부 / 세금계산서 발생 시 통장사본, 사업자등록증 첨부 여부 기재 요망</strong></p>',
        ]);

        $listQuery = $this->intraTaxIndexQueryFromRequest($request);

        return view('backoffice.intra-tax.edit', [
            'mode' => 'create',
            'post' => (object) ['B_Content' => $defaultContent],
            'files' => collect(),
            'comments' => collect(),
            'menu' => $this->intraTaxService->getMenuConfig('intraTax'),
            'listQuery' => $listQuery,
        ]);
    }

    public function store(BackofficeIntraTaxPostRequest $request)
    {
        $idx = $this->intraTaxService->createPost($request->validated(), 'intraTax');
        $this->intraTaxService->addAttachments($idx, (array) $request->file('attachments', []), 'intraTax');

        return redirect()->route('backoffice.intra-tax.index')
            ->with('success', '게시글이 등록되었습니다.');
    }

    public function edit(Request $request, int $idx)
    {
        $post = $this->intraTaxService->getPost($idx, 'intraTax', false);
        abort_if(! $post, 404);

        $passwordInput = $request->get('password');
        if (! $this->intraTaxService->canAccessPost($post, $passwordInput, 'intraTax')) {
            return redirect()->route('backoffice.intra-tax.index', $this->intraTaxIndexQueryFromRequest($request))
                ->with('error', '비밀글입니다. 비밀번호를 확인하세요.');
        }

        $post = $this->intraTaxService->getPost($idx, 'intraTax', true);
        $menu = $this->intraTaxService->getMenuConfig('intraTax');
        $files = $this->intraTaxService->getFiles($idx, 'intraTax');
        $comments = $this->intraTaxService->getComments($idx, 'intraTax');
        $listQuery = $this->intraTaxIndexQueryFromRequest($request);

        return view('backoffice.intra-tax.edit', [
            'mode' => 'edit',
            'post' => $post,
            'menu' => $menu,
            'files' => $files,
            'comments' => $comments,
            'listQuery' => $listQuery,
        ]);
    }

    public function update(BackofficeIntraTaxPostRequest $request, int $idx)
    {
        $this->intraTaxService->updatePost($idx, $request->validated(), 'intraTax');
        $this->intraTaxService->syncExistingAttachments($idx, (array) $request->input('existing_attachment_tokens', []), 'intraTax');
        $this->intraTaxService->addAttachments($idx, (array) $request->file('attachments', []), 'intraTax');

        return redirect()->route('backoffice.intra-tax.index', $this->intraTaxIndexQueryFromReturnRequest($request))
            ->with('success', '게시글이 수정되었습니다.');
    }

    public function destroy(Request $request, int $idx)
    {
        $this->intraTaxService->deletePost($idx, 'intraTax');

        return redirect()->route('backoffice.intra-tax.index', $this->intraTaxIndexQueryFromReturnRequest($request))
            ->with('success', '게시글이 삭제되었습니다.');
    }

    public function destroyMultiple(Request $request)
    {
        $ids = array_map('intval', (array) $request->input('ids', []));
        if ($ids === []) {
            return response()->json(['success' => false, 'message' => '선택된 게시글이 없습니다.'], 400);
        }
        $this->intraTaxService->deletePosts($ids, 'intraTax');

        return response()->json(['success' => true, 'message' => '선택된 게시글이 삭제되었습니다.']);
    }

    public function unlock(Request $request, int $idx)
    {
        $post = $this->intraTaxService->getPost($idx, 'intraTax', false);
        abort_if(! $post, 404);
        $listQuery = $this->intraTaxIndexQueryFromReturnRequest($request);
        if ($this->intraTaxService->canAccessPost($post, (string) $request->input('password', ''), 'intraTax')) {
            return redirect()->route('backoffice.intra-tax.edit', array_merge(['idx' => $idx], $listQuery));
        }

        return redirect()->route('backoffice.intra-tax.index', $listQuery)->with('error', '비밀번호가 일치하지 않습니다.');
    }

    public function commentStore(BackofficeIntraTaxCommentRequest $request, int $idx)
    {
        $this->intraTaxService->addComment($idx, $request->validated(), 'intraTax');
        $listQuery = $this->intraTaxIndexQueryFromReturnRequest($request);

        return redirect()->route('backoffice.intra-tax.edit', array_merge(['idx' => $idx], $listQuery))
            ->with('success', '댓글이 등록되었습니다.');
    }

    public function commentDestroy(Request $request, int $idx, int $commentIdx)
    {
        $this->intraTaxService->deleteComment($idx, $commentIdx, 'intraTax');
        $listQuery = $this->intraTaxIndexQueryFromReturnRequest($request);

        return redirect()->route('backoffice.intra-tax.edit', array_merge(['idx' => $idx], $listQuery))
            ->with('success', '댓글이 삭제되었습니다.');
    }

    public function downloadFile(int $idx, int $fileIdx)
    {
        $file = $this->intraTaxService->getFiles($idx, 'intraTax')
            ->firstWhere('F_Idx', $fileIdx);
        abort_if(! $file, 404);

        $path = $this->intraTaxService->resolveLegacyAttachmentPath('intraTax', (string) $file->F_Name);
        abort_if($path === null, 404);

        return response()->download($path, (string) $file->F_Name);
    }
}
