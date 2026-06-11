<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Requests\Backoffice\ApprovalActionRequest;
use App\Http\Requests\Backoffice\StoreApprovalDraftRequest;
use App\Http\Requests\Backoffice\UpdateApprovalDraftRequest;
use App\Models\ApprovalDocument;
use App\Models\ApprovalLine;
use App\Models\ApprovalOpinion;
use App\Models\User;
use App\Services\Backoffice\ApprovalWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends BaseController
{
    private const BOX_TYPES = [
        'personal' => [
            'title' => '개인문서함',
            'tabs' => [
                'submitted' => '상신문서함',
                'rejected' => '반려문서',
                'completed' => '결재완료',
            ],
        ],
        'pending' => [
            'title' => '결재할 문서함',
            'tabs' => [
                'pending' => '미결재 문서',
                'completed' => '결재완료 문서',
            ],
        ],
        'cooperation' => [
            'title' => '협조 문서함',
            'tabs' => [
                'pending' => '미결재 문서',
                'completed' => '결재완료 문서',
            ],
        ],
    ];

    private const FORM_TYPES = [
        'proposal' => '품의서',
        'vacation' => '휴가계',
        'expense' => '지출결의서',
    ];

    private const TEMPLATE_CATALOG = [
        'proposal' => [
            ['key' => 'proposal-education', 'name' => '교육품의서', 'description' => '교육 관련 품의 결재', 'implemented' => true],
            ['key' => 'proposal-outsourcing', 'name' => '외주용역품의서', 'description' => '외주용역 품의 결재', 'implemented' => true],
            ['key' => 'proposal-general', 'name' => '품의서', 'description' => '일반 품의 결재 요청', 'implemented' => true],
            ['key' => 'proposal-open-approval', 'name' => '홈페이지오픈승인서', 'description' => '오픈 승인 결재', 'implemented' => true],
            ['key' => 'proposal-dining', 'name' => '회식품의서', 'description' => '회식 품의 결재', 'implemented' => true],
        ],
        'vacation' => [
            ['key' => 'vacation-half-day', 'name' => '반차계', 'description' => '반차 사용 신청', 'implemented' => true],
            ['key' => 'vacation-quarter-day', 'name' => '반반차계', 'description' => '반반차 사용 신청', 'implemented' => true],
            ['key' => 'vacation-annual', 'name' => '연차휴가계', 'description' => '연차 사용 신청', 'implemented' => true],
            ['key' => 'vacation-reserve-training', 'name' => '예비군민방위훈련계', 'description' => '예비군/민방위 훈련 신청', 'implemented' => true],
            ['key' => 'vacation-long-sick', 'name' => '장기병가원', 'description' => '장기 병가 신청', 'implemented' => true],
            ['key' => 'vacation-leave-of-absence', 'name' => '휴직서', 'description' => '휴직 신청', 'implemented' => true],
            ['key' => 'vacation-regular', 'name' => '정기휴가계', 'description' => '정기 휴가 신청', 'implemented' => true],
            ['key' => 'vacation-sick-absence', 'name' => '결근(병가)계', 'description' => '결근/병가 신청', 'implemented' => true],
            ['key' => 'vacation-health', 'name' => '보건휴가계', 'description' => '보건 휴가 신청', 'implemented' => true],
            ['key' => 'vacation-early-leave', 'name' => '조퇴계', 'description' => '조퇴 신청', 'implemented' => true],
            ['key' => 'vacation-special', 'name' => '경조사휴가계', 'description' => '경조사 휴가 신청', 'implemented' => true],
            ['key' => 'vacation-training', 'name' => '교육훈련계', 'description' => '교육/훈련 참여 신청', 'implemented' => true],
            ['key' => 'vacation-maternity', 'name' => '출산휴가계', 'description' => '출산 휴가 신청', 'implemented' => true],
        ],
        'expense' => [
            ['key' => 'expense-congrats-support', 'name' => '경조지원신청서', 'description' => '경조지원 결재 요청', 'implemented' => true],
            ['key' => 'expense-resolution', 'name' => '지출결의서', 'description' => '일반 지출 결재 요청', 'implemented' => true],
            ['key' => 'expense-transport', 'name' => '교통비지출결의서', 'description' => '교통비 지출 결재 요청', 'implemented' => true],
            ['key' => 'expense-purchase', 'name' => '구매신청서', 'description' => '구매 결재 요청', 'implemented' => true],
            ['key' => 'expense-outsourcing-deposit', 'name' => '외주용역입금요청서', 'description' => '외주 용역 입금 요청', 'implemented' => true],
        ],
    ];

    private const SEARCH_FIELDS = [
        'ALL' => '전체',
        'subject' => '문서명',
        'writer' => '기안자',
    ];

    public function __construct(
        private readonly ApprovalWorkflowService $workflowService
    ) {}

    public function index()
    {
        $user = Auth::user();
        abort_unless($user, 401);

        $boxSummaries = [
            [
                'title' => '개인 문서함',
                'route' => route('backoffice.approvals.personal'),
                'items' => [
                    ['label' => '상신문서', 'count' => ApprovalDocument::query()->where('writer_id', $user->id)->where('status', ApprovalDocument::STATUS_PENDING)->count()],
                    ['label' => '반려문서', 'count' => ApprovalDocument::query()->where('writer_id', $user->id)->where('status', ApprovalDocument::STATUS_REJECTED)->count()],
                    ['label' => '결재완료', 'count' => ApprovalDocument::query()->where('writer_id', $user->id)->where('status', ApprovalDocument::STATUS_COMPLETED)->count()],
                ],
            ],
            [
                'title' => '결재할 문서함',
                'route' => route('backoffice.approvals.pending'),
                'items' => [
                    ['label' => '미결재', 'count' => $this->countPendingApprovals((int) $user->id)],
                    ['label' => '결재완료', 'count' => $this->countCompletedApprovals((int) $user->id)],
                ],
            ],
            [
                'title' => '협조 문서함',
                'route' => route('backoffice.approvals.cooperation'),
                'items' => [
                    ['label' => '미결재', 'count' => $this->countPendingCooperations((int) $user->id)],
                    ['label' => '결재완료', 'count' => $this->countConfirmedCooperations((int) $user->id)],
                ],
            ],
        ];

        $recentPendingDocuments = $this->transformDocuments(
            ApprovalDocument::query()
                ->with(['writer:id,name', 'lines.user:id,name'])
                ->whereHas('lines', function ($query) use ($user) {
                    $query->where('line_type', ApprovalLine::TYPE_APPROVAL)
                        ->where('user_id', $user->id)
                        ->where('status', ApprovalLine::STATUS_PENDING);
                })
                ->latest()
                ->limit(5)
                ->get(),
            (int) $user->id
        )->all();

        $recentPersonalDocuments = $this->transformDocuments(
            $this->buildBoxQuery('personal', 'submitted', (int) $user->id)
                ->limit(5)
                ->get(),
            (int) $user->id
        )->all();

        $recentCooperationDocuments = $this->transformDocuments(
            $this->buildBoxQuery('cooperation', 'pending', (int) $user->id)
                ->limit(5)
                ->get(),
            (int) $user->id
        )->all();

        return $this->view('backoffice.approval.index', [
            'pageKey' => 'main',
            'boxSummaries' => $boxSummaries,
            'recentPendingDocuments' => $recentPendingDocuments,
            'recentPersonalDocuments' => $recentPersonalDocuments,
            'recentCooperationDocuments' => $recentCooperationDocuments,
            'formTypes' => self::FORM_TYPES,
        ]);
    }

    public function create(Request $request)
    {
        $formType = (string) $request->query('form_type', 'proposal');
        if (! array_key_exists($formType, self::FORM_TYPES)) {
            $formType = 'proposal';
        }

        return $this->view('backoffice.approval.create', [
            'pageKey' => 'create',
            'selectedFormType' => $formType,
            'formTypes' => self::FORM_TYPES,
            'templateCatalog' => self::TEMPLATE_CATALOG,
            'selectedTemplate' => (string) $request->query('template', ''),
        ]);
    }

    public function createDraft(string $templateKey)
    {
        $template = $this->findTemplateByKey($templateKey);
        if (! $template) {
            abort(404);
        }

        $templateView = $this->resolveTemplateView((string) $template['key'], (string) $template['form_type']);
        if ($templateView === null) {
            abort(404);
        }

        return $this->view('backoffice.approval.drafts.create', [
            'pageKey' => 'create',
            'template' => $template,
            'templateView' => $templateView,
            'isDetail' => false,
        ]);
    }

    public function store(StoreApprovalDraftRequest $request)
    {
        $template = $this->findTemplateByKey((string) $request->input('template_key'));
        if (! $template) {
            return back()->withErrors(['template_key' => '유효하지 않은 결재 양식입니다.'])->withInput();
        }

        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        $document = $this->workflowService->createDocument(
            $user,
            (string) $template['key'],
            (string) $template['form_type'],
            (string) $request->input('title'),
            (array) $request->input('content', []),
            array_map('intval', (array) $request->input('approval_user_ids', [])),
            array_map('intval', (array) $request->input('cooperation_user_ids', [])),
            $request->file('attachments', [])
        );

        return redirect()
            ->route('backoffice.approvals.personal.show', ['docNo' => $document->doc_no, 'tab' => 'submitted'])
            ->with('success', '결재 문서를 상신했습니다.');
    }

    public function approverUsers(Request $request)
    {
        $keyword = trim((string) $request->query('keyword', ''));
        $query = User::query()
            ->admins()
            ->active()
            ->select(['id', 'name', 'department', 'position', 'login_id'])
            ->orderBy('name');

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('department', 'like', "%{$keyword}%")
                    ->orWhere('position', 'like', "%{$keyword}%")
                    ->orWhere('login_id', 'like', "%{$keyword}%");
            });
        }

        $users = $query->limit(200)->get()->map(function (User $user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'department' => $user->department ?: '-',
                'position' => $user->position ?: '-',
                'login_id' => $user->login_id ?: '-',
            ];
        })->values();

        return response()->json(['users' => $users]);
    }

    public function personal(Request $request)
    {
        return $this->boxView('personal', $request, 'backoffice.approval.personal');
    }

    public function pending(Request $request)
    {
        return $this->boxView('pending', $request, 'backoffice.approval.pending');
    }

    public function cooperation(Request $request)
    {
        return $this->boxView('cooperation', $request, 'backoffice.approval.cooperation');
    }

    public function showPersonal(Request $request, string $docNo)
    {
        return $this->showByBox('personal', $request, $docNo);
    }

    public function showPending(Request $request, string $docNo)
    {
        return $this->showByBox('pending', $request, $docNo);
    }

    public function showCooperation(Request $request, string $docNo)
    {
        return $this->showByBox('cooperation', $request, $docNo);
    }

    public function show(Request $request, string $docNo)
    {
        return $this->showByBox((string) $request->query('box', 'pending'), $request, $docNo);
    }

    private function showByBox(string $boxType, Request $request, string $docNo)
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        $document = ApprovalDocument::query()
            ->with(['writer:id,name', 'lines.user:id,name', 'opinions.user:id,name', 'attachments'])
            ->where('doc_no', $docNo)
            ->firstOrFail();

        $template = $this->findTemplateByKey((string) $document->template_key);
        if (! $template) {
            abort(404);
        }
        $templateView = $this->resolveTemplateView((string) $template['key'], (string) $template['form_type']);
        if ($templateView === null) {
            abort(404);
        }

        $pageKey = in_array($boxType, ['personal', 'pending', 'cooperation'], true) ? $boxType : 'pending';
        $documentData = $this->transformDocument($document, (int) $user->id);
        $canEditDraft = $this->canEditPersonalDraft($document, $user, $boxType);
        $canApproveOrReject = $this->canActLine($document, $user, ApprovalLine::TYPE_APPROVAL);
        $canConfirmCooperation = $this->canActLine($document, $user, ApprovalLine::TYPE_COOPERATION);
        $canComment = $this->canCommentOnDocument($document, $user);
        $comments = $document->opinions
            ->where('type', ApprovalOpinion::TYPE_COMMENT)
            ->sortBy('created_at')
            ->values();

        return $this->view('backoffice.approval.show', [
            'pageKey' => $pageKey,
            'document' => $documentData,
            'documentModel' => $document,
            'template' => $template,
            'templateView' => $templateView,
            'isDetail' => true,
            'documentContent' => (array) ($document->content ?? []),
            'boxType' => $boxType,
            'tab' => (string) $request->query('tab', 'pending'),
            'canEditDraft' => $canEditDraft,
            'canApproveOrReject' => $canApproveOrReject,
            'canConfirmCooperation' => $canConfirmCooperation,
            'canComment' => $canComment,
            'comments' => $comments,
        ]);
    }

    public function updatePersonalDraft(UpdateApprovalDraftRequest $request, string $docNo)
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        $document = ApprovalDocument::query()
            ->with(['lines'])
            ->where('doc_no', $docNo)
            ->firstOrFail();

        $this->workflowService->updateDraft(
            $document,
            $user,
            (string) $request->input('title'),
            (array) $request->input('content', []),
            $request->file('attachments', [])
        );

        return redirect()
            ->route('backoffice.approvals.personal', ['tab' => 'submitted']);
    }

    public function approve(ApprovalActionRequest $request, string $docNo)
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);
        $document = ApprovalDocument::query()->where('doc_no', $docNo)->firstOrFail();

        $this->workflowService->approve($document, $user, (string) $request->input('opinion', ''));

        return back()->with('success', '승인 처리되었습니다.');
    }

    public function reject(ApprovalActionRequest $request, string $docNo)
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);
        $document = ApprovalDocument::query()->where('doc_no', $docNo)->firstOrFail();

        $this->workflowService->reject($document, $user, (string) $request->input('opinion', ''));

        return back()->with('success', '반려 처리되었습니다.');
    }

    public function delegate(ApprovalActionRequest $request, string $docNo)
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);
        $document = ApprovalDocument::query()->where('doc_no', $docNo)->firstOrFail();
        $lineType = $this->resolveLineTypeByBox((string) $request->query('box', 'pending'));

        $this->workflowService->delegate($document, $user, $lineType, (string) $request->input('opinion', ''));

        return back()->with('success', '전결 처리되었습니다.');
    }

    public function hold(ApprovalActionRequest $request, string $docNo)
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);
        $document = ApprovalDocument::query()->where('doc_no', $docNo)->firstOrFail();
        $lineType = $this->resolveLineTypeByBox((string) $request->query('box', 'pending'));

        $this->workflowService->hold($document, $user, $lineType, (string) $request->input('opinion', ''));

        return back()->with('success', '보류 처리되었습니다.');
    }

    public function confirm(ApprovalActionRequest $request, string $docNo)
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);
        $document = ApprovalDocument::query()->where('doc_no', $docNo)->firstOrFail();

        $this->workflowService->confirmCooperation($document, $user, (string) $request->input('opinion', ''));

        return back()->with('success', '협조 확인 처리되었습니다.');
    }

    public function rejectCooperation(ApprovalActionRequest $request, string $docNo)
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);
        $document = ApprovalDocument::query()->where('doc_no', $docNo)->firstOrFail();

        $this->workflowService->rejectCooperation($document, $user, (string) $request->input('opinion', ''));

        return back()->with('success', '협조 기각 처리되었습니다.');
    }

    public function commentStore(Request $request, string $docNo)
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        $document = ApprovalDocument::query()
            ->with(['lines'])
            ->where('doc_no', $docNo)
            ->firstOrFail();

        abort_unless($this->canCommentOnDocument($document, $user), 403);

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
        ]);

        ApprovalOpinion::query()->create([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'type' => ApprovalOpinion::TYPE_COMMENT,
            'content' => $validated['content'],
        ]);

        return back()->with('success', '댓글이 등록되었습니다.');
    }

    public function commentDestroy(string $docNo, ApprovalOpinion $opinion)
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        $document = ApprovalDocument::query()
            ->where('doc_no', $docNo)
            ->firstOrFail();

        abort_unless((int) $opinion->document_id === (int) $document->id, 404);
        abort_unless($opinion->type === ApprovalOpinion::TYPE_COMMENT, 404);
        abort_unless((int) $opinion->user_id === (int) $user->id, 403);

        $opinion->delete();

        return back()->with('success', '댓글이 삭제되었습니다.');
    }

    private function boxView(string $boxType, Request $request, string $viewName)
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        $boxMeta = self::BOX_TYPES[$boxType];
        $tab = (string) $request->query('tab', array_key_first($boxMeta['tabs']));
        $perPage = (int) $request->query('per_page', 10);
        $page = max(1, (int) $request->query('page', 1));
        $findField = (string) $request->query('find_field', 'ALL');
        $keyword = trim((string) $request->query('keyword', ''));

        if (! array_key_exists($tab, $boxMeta['tabs'])) {
            $tab = array_key_first($boxMeta['tabs']);
        }
        if (! in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 10;
        }
        if (! array_key_exists($findField, self::SEARCH_FIELDS)) {
            $findField = 'ALL';
        }

        $query = $this->buildBoxQuery($boxType, $tab, (int) $user->id);
        if ($keyword !== '') {
            $query->where(function ($q) use ($findField, $keyword) {
                if ($findField === 'subject') {
                    $q->where('title', 'like', "%{$keyword}%");

                    return;
                }
                if ($findField === 'writer') {
                    $q->whereHas('writer', function ($writerQuery) use ($keyword) {
                        $writerQuery->where('name', 'like', "%{$keyword}%");
                    });

                    return;
                }
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('doc_no', 'like', "%{$keyword}%")
                    ->orWhereHas('writer', function ($writerQuery) use ($keyword) {
                        $writerQuery->where('name', 'like', "%{$keyword}%");
                    });
            });
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page)->withQueryString();
        $documents = $this->transformDocuments(
            collect($paginator->items()),
            (int) $user->id
        );

        $pagedDocuments = new LengthAwarePaginator(
            $documents->values()->all(),
            $paginator->total(),
            $paginator->perPage(),
            $paginator->currentPage(),
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return $this->view($viewName, [
            'pageKey' => $boxType,
            'boxType' => $boxType,
            'boxTitle' => $boxMeta['title'],
            'tabs' => $boxMeta['tabs'],
            'activeTab' => $tab,
            'documents' => $pagedDocuments,
            'searchFields' => self::SEARCH_FIELDS,
            'filters' => ['find_field' => $findField, 'keyword' => $keyword],
            'perPage' => $perPage,
        ]);
    }

    private function buildBoxQuery(string $boxType, string $tab, int $userId)
    {
        $query = ApprovalDocument::query()
            ->with(['writer:id,name', 'lines.user:id,name', 'opinions:id,document_id'])
            ->latest();

        if ($boxType === 'personal') {
            $query->where('writer_id', $userId);
            if ($tab === 'submitted') {
                $query->where('status', ApprovalDocument::STATUS_PENDING);
            } elseif ($tab === 'rejected') {
                $query->where('status', ApprovalDocument::STATUS_REJECTED);
            } elseif ($tab === 'completed') {
                $query->where('status', ApprovalDocument::STATUS_COMPLETED);
            }

            return $query;
        }

        if ($boxType === 'pending') {
            $query->whereHas('lines', function ($lineQuery) use ($userId) {
                $lineQuery->where('line_type', ApprovalLine::TYPE_APPROVAL)
                    ->where('user_id', $userId);
            });
            if ($tab === 'pending') {
                $query->where('status', ApprovalDocument::STATUS_PENDING)
                    ->whereHas('lines', function ($lineQuery) use ($userId) {
                        $lineQuery->where('line_type', ApprovalLine::TYPE_APPROVAL)
                            ->where('user_id', $userId)
                            ->where('status', ApprovalLine::STATUS_PENDING);
                    });
            } else {
                $query->whereHas('lines', function ($lineQuery) use ($userId) {
                    $lineQuery->where('line_type', ApprovalLine::TYPE_APPROVAL)
                        ->where('user_id', $userId)
                        ->whereIn('status', [ApprovalLine::STATUS_APPROVED, ApprovalLine::STATUS_REJECTED]);
                });
            }

            return $query;
        }

        $query->whereHas('lines', function ($lineQuery) use ($userId) {
            $lineQuery->where('line_type', ApprovalLine::TYPE_COOPERATION)
                ->where('user_id', $userId);
        });
        if ($tab === 'pending') {
            $query->whereHas('lines', function ($lineQuery) use ($userId) {
                $lineQuery->where('line_type', ApprovalLine::TYPE_COOPERATION)
                    ->where('user_id', $userId)
                    ->where('status', ApprovalLine::STATUS_PENDING);
            });
        } else {
            $query->whereHas('lines', function ($lineQuery) use ($userId) {
                $lineQuery->where('line_type', ApprovalLine::TYPE_COOPERATION)
                    ->where('user_id', $userId)
                    ->where('status', ApprovalLine::STATUS_CONFIRMED);
            });
        }

        return $query;
    }

    private function transformDocuments(Collection $documents, int $userId): Collection
    {
        return $documents->map(fn (ApprovalDocument $document) => $this->transformDocument($document, $userId));
    }

    private function transformDocument(ApprovalDocument $document, int $userId): array
    {
        $myApprovalLine = $document->lines
            ->first(fn (ApprovalLine $line) => (int) $line->user_id === $userId && $line->line_type === ApprovalLine::TYPE_APPROVAL);
        $myCoopLine = $document->lines
            ->first(fn (ApprovalLine $line) => (int) $line->user_id === $userId && $line->line_type === ApprovalLine::TYPE_COOPERATION);
        $nextApprover = $this->nextApproverName($document);

        return [
            'doc_no' => $document->doc_no,
            'title' => $document->title,
            'writer' => $document->writer?->name ?? '-',
            'status' => $this->statusLabel($document->status),
            'drafted_at' => optional($document->submitted_at ?? $document->created_at)->format('Y-m-d'),
            'approved_at' => optional($document->completed_at)->format('Y-m-d') ?? '-',
            'my_status' => $this->myStatusLabel($document, $myApprovalLine, $myCoopLine),
            'next_approver' => $nextApprover,
            'opinion_count' => $document->opinions->count(),
        ];
    }

    private function nextApproverName(ApprovalDocument $document): string
    {
        if ($document->status !== ApprovalDocument::STATUS_PENDING) {
            return '-';
        }

        $next = $document->lines
            ->where('line_type', ApprovalLine::TYPE_APPROVAL)
            ->where('status', ApprovalLine::STATUS_PENDING)
            ->sortBy('line_order')
            ->first();

        return $next?->user?->name ?? '-';
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            ApprovalDocument::STATUS_COMPLETED => '결재완료',
            ApprovalDocument::STATUS_REJECTED => '반려',
            default => '진행중',
        };
    }

    private function myStatusLabel(ApprovalDocument $document, ?ApprovalLine $approvalLine, ?ApprovalLine $coopLine): string
    {
        if ($approvalLine instanceof ApprovalLine) {
            $actionType = (string) (($approvalLine->meta['action_type'] ?? '') ?: '');

            return match ($approvalLine->status) {
                ApprovalLine::STATUS_APPROVED => $actionType === 'delegate' ? '전결' : '결재',
                ApprovalLine::STATUS_REJECTED => $actionType === 'hold' ? '보류' : '기각',
                default => '미결재',
            };
        }

        if ($coopLine instanceof ApprovalLine) {
            $actionType = (string) (($coopLine->meta['action_type'] ?? '') ?: '');

            return match ($coopLine->status) {
                ApprovalLine::STATUS_CONFIRMED => $actionType === 'delegate' ? '전결' : '결재',
                ApprovalLine::STATUS_REJECTED => $actionType === 'hold' ? '보류' : '기각',
                default => '미확인',
            };
        }

        return match ($document->status) {
            ApprovalDocument::STATUS_REJECTED => '반려',
            ApprovalDocument::STATUS_COMPLETED => '결재',
            default => '미결재',
        };
    }

    private function countPendingApprovals(int $userId): int
    {
        return ApprovalDocument::query()
            ->where('status', ApprovalDocument::STATUS_PENDING)
            ->whereHas('lines', function ($query) use ($userId) {
                $query->where('line_type', ApprovalLine::TYPE_APPROVAL)
                    ->where('user_id', $userId)
                    ->where('status', ApprovalLine::STATUS_PENDING);
            })->count();
    }

    private function countCompletedApprovals(int $userId): int
    {
        return ApprovalDocument::query()
            ->whereHas('lines', function ($query) use ($userId) {
                $query->where('line_type', ApprovalLine::TYPE_APPROVAL)
                    ->where('user_id', $userId)
                    ->where('status', ApprovalLine::STATUS_APPROVED);
            })->count();
    }

    private function countPendingCooperations(int $userId): int
    {
        return ApprovalDocument::query()
            ->whereHas('lines', function ($query) use ($userId) {
                $query->where('line_type', ApprovalLine::TYPE_COOPERATION)
                    ->where('user_id', $userId)
                    ->where('status', ApprovalLine::STATUS_PENDING);
            })->count();
    }

    private function countConfirmedCooperations(int $userId): int
    {
        return ApprovalDocument::query()
            ->whereHas('lines', function ($query) use ($userId) {
                $query->where('line_type', ApprovalLine::TYPE_COOPERATION)
                    ->where('user_id', $userId)
                    ->where('status', ApprovalLine::STATUS_CONFIRMED);
            })->count();
    }

    private function findTemplateByKey(string $templateKey): ?array
    {
        foreach (self::TEMPLATE_CATALOG as $formType => $items) {
            foreach ($items as $item) {
                if (($item['key'] ?? null) === $templateKey) {
                    $item['form_type'] = $formType;
                    $item['form_type_label'] = self::FORM_TYPES[$formType] ?? $formType;

                    return $item;
                }
            }
        }

        return null;
    }

    private function resolveTemplateView(string $templateKey, string $formType): ?string
    {
        $templateView = 'backoffice.approval.forms.templates.'.$templateKey;
        if (view()->exists($templateView)) {
            return $templateView;
        }

        return match ($formType) {
            'proposal' => 'backoffice.approval.forms.proposal.general',
            'vacation' => 'backoffice.approval.forms.vacation.annual-leave',
            'expense' => 'backoffice.approval.forms.expense.resolution',
            default => null,
        };
    }

    private function canEditPersonalDraft(ApprovalDocument $document, User $user, string $boxType): bool
    {
        if ($boxType !== 'personal') {
            return false;
        }

        return (int) $document->writer_id === (int) $user->id
            && $document->status === ApprovalDocument::STATUS_PENDING
            && ! $document->lines
                ->where('line_type', ApprovalLine::TYPE_APPROVAL)
                ->whereNotNull('acted_at')
                ->isNotEmpty();
    }

    private function canActLine(ApprovalDocument $document, User $user, string $lineType): bool
    {
        if ($lineType === ApprovalLine::TYPE_COOPERATION) {
            $hasPendingApproval = $document->lines
                ->where('line_type', ApprovalLine::TYPE_APPROVAL)
                ->where('status', ApprovalLine::STATUS_PENDING)
                ->isNotEmpty();
            if ($hasPendingApproval) {
                return false;
            }
        }

        $myLine = $document->lines
            ->where('line_type', $lineType)
            ->where('user_id', $user->id)
            ->where('status', ApprovalLine::STATUS_PENDING)
            ->sortBy('line_order')
            ->first();

        if (! $myLine) {
            return false;
        }

        return true;
    }

    private function canCommentOnDocument(ApprovalDocument $document, User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ((int) $document->writer_id === (int) $user->id) {
            return true;
        }

        return $document->lines
            ->where('user_id', $user->id)
            ->isNotEmpty();
    }

    private function resolveLineTypeByBox(string $boxType): string
    {
        return $boxType === 'cooperation'
            ? ApprovalLine::TYPE_COOPERATION
            : ApprovalLine::TYPE_APPROVAL;
    }
}
