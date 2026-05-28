<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\BackofficeProjectManageRequest;
use App\Services\Backoffice\ProjectManageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ProjectManageController extends Controller
{
    public function __construct(private readonly ProjectManageService $projectManageService) {}

    /**
     * 목록 GET 쿼리(page, per_page, 필터, 정렬)를 안전하게 정리
     *
     * @return array<string, int|string>
     */
    private function projectManageIndexQueryFromRequest(Request $request): array
    {
        return $this->sanitizeProjectManageIndexQuery($request->query());
    }

    /**
     * 수정 폼 POST의 return_* 필드 → 목록 리다이렉트 쿼리
     *
     * @return array<string, int|string>
     */
    private function projectManageIndexQueryFromReturnRequest(Request $request): array
    {
        $keys = array_merge(
            ['page', 'per_page', 'HostingSdate', 'HostingEdate', 'FindValue', 'TeamUser', 'gubun', 'sortField', 'sort'],
            array_map(static fn (int $i) => 'ch'.$i, range(1, 12))
        );
        $raw = [];
        foreach ($keys as $key) {
            $rv = $request->input('return_'.$key);
            if ($rv !== null && $rv !== '') {
                $raw[$key] = $rv;
            }
        }

        return $this->sanitizeProjectManageIndexQuery($raw);
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array<string, int|string>
     */
    private function sanitizeProjectManageIndexQuery(array $raw): array
    {
        $out = [];
        if (isset($raw['page']) && (int) $raw['page'] >= 1) {
            $out['page'] = (int) $raw['page'];
        }
        $allowedPer = [10, 20, 50, 100];
        if (isset($raw['per_page']) && in_array((int) $raw['per_page'], $allowedPer, true)) {
            $out['per_page'] = (int) $raw['per_page'];
        }
        $stateOptions = ['계약', '기획', '디자인', '퍼블리싱', '개발', '작업완료', '수정사항', '유지보수', '보류', '취소', '광고기획', '호스팅'];
        for ($i = 1; $i <= 12; $i++) {
            $key = 'ch'.$i;
            if (! empty($raw[$key]) && in_array((string) $raw[$key], $stateOptions, true)) {
                $out[$key] = (string) $raw[$key];
            }
        }
        foreach (['HostingSdate', 'HostingEdate'] as $dateKey) {
            if (! empty($raw[$dateKey]) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $raw[$dateKey])) {
                $out[$dateKey] = (string) $raw[$dateKey];
            }
        }
        foreach (['FindValue', 'TeamUser'] as $textKey) {
            if (! empty($raw[$textKey])) {
                $out[$textKey] = mb_substr((string) $raw[$textKey], 0, 255);
            }
        }
        $gubunAllowed = ['신규', '리뉴얼', '유지보수', '수리엘'];
        if (! empty($raw['gubun']) && in_array((string) $raw['gubun'], $gubunAllowed, true)) {
            $out['gubun'] = (string) $raw['gubun'];
        }
        $allowedSort = ['idx', 'ProjectName', 'HostingEdate', 'LastPayDate', 'ProjectIngState'];
        if (! empty($raw['sortField']) && in_array((string) $raw['sortField'], $allowedSort, true)) {
            $out['sortField'] = (string) $raw['sortField'];
        }
        if (! empty($raw['sort']) && in_array(strtolower((string) $raw['sort']), ['asc', 'desc'], true)) {
            $out['sort'] = strtolower((string) $raw['sort']);
        }

        return $out;
    }

    public function index(Request $request)
    {
        $states = [];
        for ($i = 1; $i <= 12; $i++) {
            $value = $request->get("ch{$i}");
            if (! empty($value)) {
                $states[] = $value;
            }
        }

        $filters = [
            'states' => $states,
            'HostingSdate' => $request->get('HostingSdate'),
            'HostingEdate' => $request->get('HostingEdate'),
            'FindValue' => $request->get('FindValue'),
            'TeamUser' => $request->get('TeamUser'),
            'gubun' => $request->get('gubun'),
            'sortField' => $request->get('sortField', 'idx'),
            'sort' => strtolower((string) $request->get('sort', 'desc')) === 'asc' ? 'asc' : 'desc',
        ];

        $perPage = (int) $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 20, 50, 100], true) ? $perPage : 10;

        $projects = $this->projectManageService->getProjects($filters, $perPage);
        $stateCounts = $this->projectManageService->getStateCounts($filters);

        $listQuery = $this->projectManageIndexQueryFromRequest($request);

        return view('backoffice.project-manages.index', compact('projects', 'filters', 'perPage', 'stateCounts', 'listQuery'));
    }

    public function show(Request $request, int $idx)
    {
        $project = $this->projectManageService->getProject($idx);
        abort_if(! $project, 404);

        $moneyHistories = $this->projectManageService->getMoneyHistories($idx);
        $attachmentItems = $this->projectManageService->getAttachmentItems($idx);
        $listQuery = $this->projectManageIndexQueryFromRequest($request);

        return view('backoffice.project-manages.show', compact('project', 'moneyHistories', 'attachmentItems', 'listQuery'));
    }

    public function create(Request $request)
    {
        abort_unless(Schema::hasTable('project_manages'), 500, 'project_manages 테이블이 필요합니다.');
        $listQuery = $this->projectManageIndexQueryFromRequest($request);

        return view('backoffice.project-manages.create', compact('listQuery'));
    }

    public function store(BackofficeProjectManageRequest $request)
    {
        $idx = $this->projectManageService->createProject($request->validated());
        $this->projectManageService->addAttachments($idx, $request->file('attachments', []));

        return redirect()->route('backoffice.project-manages.index')
            ->with('success', '프로젝트가 등록되었습니다.');
    }

    public function edit(Request $request, int $idx)
    {
        abort_unless(Schema::hasTable('project_manages'), 500, 'project_manages 테이블이 필요합니다.');
        $project = $this->projectManageService->getProject($idx);
        abort_if(! $project, 404);
        $attachmentItems = $this->projectManageService->getAttachmentItems($idx);
        $modifyLogs = $this->projectManageService->getModifyLogs($idx);
        $listQuery = $this->projectManageIndexQueryFromRequest($request);

        return view('backoffice.project-manages.edit', compact('project', 'attachmentItems', 'modifyLogs', 'listQuery'));
    }

    public function update(BackofficeProjectManageRequest $request, int $idx)
    {
        $changedFields = $this->projectManageService->updateProject($idx, $request->validated());
        $syncResult = $this->projectManageService->syncExistingAttachments($idx, $request->input('existing_attachment_tokens', []));
        $addedAttachmentCount = $this->projectManageService->addAttachments($idx, $request->file('attachments', []));

        $changes = [];
        if (! empty($changedFields)) {
            foreach ($changedFields as $section) {
                $changes[] = '['.$section.'] 수정';
            }
        }

        $removedAttachmentCount = (int) ($syncResult['removed_count'] ?? 0);
        if ($addedAttachmentCount > 0 || $removedAttachmentCount > 0) {
            $changes[] = '[첨부파일] 수정 (추가 '.$addedAttachmentCount.'건, 삭제 '.$removedAttachmentCount.'건)';
        }

        $memo = ! empty($changes) ? implode(' / ', $changes) : '[기타] 수정';
        $this->projectManageService->addModifyLog(
            $idx,
            $memo,
            (string) (auth()->user()->name ?? '관리자')
        );

        return redirect()->route('backoffice.project-manages.index', $this->projectManageIndexQueryFromReturnRequest($request))
            ->with('success', '프로젝트가 수정되었습니다.');
    }

    public function downloadAttachment(int $idx, int $attachmentIdx)
    {
        $attachment = $this->projectManageService->getAttachment($idx, $attachmentIdx);
        abort_if(! $attachment, 404);

        $path = (string) ($attachment->stored_path ?? '');
        $downloadName = (string) ($attachment->original_name ?? 'download.file');
        abort_if($path === '', 404);
        abort_unless(Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->download($path, $downloadName);
    }

    public function destroy(Request $request, int $idx)
    {
        $this->projectManageService->addModifyLog(
            $idx,
            '[웹] 프로젝트 삭제',
            (string) (auth()->user()->name ?? '관리자')
        );
        $this->projectManageService->deleteProject($idx);

        return redirect()->route('backoffice.project-manages.index', $this->projectManageIndexQueryFromReturnRequest($request))
            ->with('success', '프로젝트가 삭제되었습니다.');
    }

    public function destroyMultiple(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => '선택된 프로젝트가 없습니다.'], 400);
        }

        $userName = (string) (auth()->user()->name ?? '관리자');
        foreach ($ids as $id) {
            $this->projectManageService->addModifyLog((int) $id, '[웹] 프로젝트 삭제', $userName);
        }
        $this->projectManageService->deleteProjects($ids);

        return response()->json(['success' => true, 'message' => '선택된 프로젝트가 삭제되었습니다.']);
    }

    public function export(Request $request)
    {
        $states = [];
        for ($i = 1; $i <= 12; $i++) {
            $value = $request->get("ch{$i}");
            if (! empty($value)) {
                $states[] = $value;
            }
        }

        $filters = [
            'states' => $states,
            'HostingSdate' => $request->get('HostingSdate'),
            'HostingEdate' => $request->get('HostingEdate'),
            'FindValue' => $request->get('FindValue'),
            'TeamUser' => $request->get('TeamUser'),
            'gubun' => $request->get('gubun'),
            'sortField' => $request->get('sortField', 'idx'),
            'sort' => strtolower((string) $request->get('sort', 'desc')) === 'asc' ? 'asc' : 'desc',
        ];

        $projects = $this->projectManageService->exportProjects($filters);
        $filename = 'project_manages_'.date('YmdHis').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = static function () use ($projects) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['No', '프로젝트명', 'URL', '담당자', '이메일', '담당자 연락처', '기획', '호스팅 만료', '최근 결제', '진행상태']);

            foreach ($projects as $index => $project) {
                fputcsv($file, [
                    $index + 1,
                    $project->CompanyName ?? '',
                    $project->HomepageUrl ?? '',
                    $project->ManagerName ?? '',
                    $project->CompanyEmail ?? '',
                    $project->CompanyHp ?? '',
                    $project->InternalPlanningName ?? '',
                    $project->HostingEdate ?? '',
                    $project->LastPayDate ?? '',
                    $project->ProjectIngState ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
