@extends('backoffice.layouts.app')

@section('title', '결재 문서 상세')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/backoffice/boards.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/approval.css') }}">
@endsection

@section('scripts')
<x-backoffice-ckeditor-assets />
<script src="{{ asset('js/backoffice/approval.js') }}"></script>
@endsection

@section('content')
<div class="board-container" data-approval-detail>
    @php
        $approveRoute = match($boxType) {
            'personal' => 'backoffice.approvals.personal.approve',
            default => 'backoffice.approvals.pending.approve',
        };
        $rejectRoute = match($boxType) {
            'personal' => 'backoffice.approvals.personal.reject',
            default => 'backoffice.approvals.pending.reject',
        };
        $confirmRoute = 'backoffice.approvals.cooperation.confirm';
        $delegateRoute = match($boxType) {
            'cooperation' => 'backoffice.approvals.cooperation.delegate',
            'personal' => 'backoffice.approvals.personal.delegate',
            default => 'backoffice.approvals.pending.delegate',
        };
        $holdRoute = match($boxType) {
            'cooperation' => 'backoffice.approvals.cooperation.hold',
            'personal' => 'backoffice.approvals.personal.hold',
            default => 'backoffice.approvals.pending.hold',
        };
        $cooperationRejectRoute = 'backoffice.approvals.cooperation.reject';
        $latestRejectOpinion = $documentModel->opinions
            ->firstWhere('type', \App\Models\ApprovalOpinion::TYPE_REJECT);
    @endphp
    <div class="board-header">
        <a
            href="{{ match($boxType) {
                'personal' => route('backoffice.approvals.personal', ['tab' => $tab]),
                'cooperation' => route('backoffice.approvals.cooperation', ['tab' => $tab]),
                default => route('backoffice.approvals.pending', ['tab' => $tab]),
            } }}"
            class="btn btn-secondary btn-sm"
        >
            <i class="fas fa-arrow-left"></i> 목록으로
        </a>
    </div>

    <div class="board-card">
        <div class="board-card-body">
            @if($boxType === 'cooperation' && ($canConfirmCooperation ?? false))
                <div class="approval-form-actions">
                    <div class="approval-action-toolbar">
                        <form method="POST" action="{{ route($confirmRoute, ['docNo' => $document['doc_no'], 'tab' => $tab]) }}">
                            @csrf
                            <input type="hidden" name="action" value="confirm">
                            <button type="submit" class="btn btn-primary btn-sm approval-btn-approve">결재</button>
                        </form>
                        <form method="POST" action="{{ route($delegateRoute, ['docNo' => $document['doc_no'], 'box' => $boxType, 'tab' => $tab]) }}">
                            @csrf
                            <input type="hidden" name="action" value="delegate">
                            <button type="submit" class="btn btn-outline-primary btn-sm">전결</button>
                        </form>
                        <form method="POST" action="{{ route($holdRoute, ['docNo' => $document['doc_no'], 'box' => $boxType, 'tab' => $tab]) }}">
                            @csrf
                            <input type="hidden" name="action" value="hold">
                            <button type="submit" class="btn btn-outline-secondary btn-sm">보류</button>
                        </form>
                        <form method="POST" action="{{ route($cooperationRejectRoute, ['docNo' => $document['doc_no'], 'tab' => $tab]) }}" class="approval-reject-form">
                            @csrf
                            <input type="hidden" name="action" value="reject">
                            <button type="submit" class="btn btn-outline-danger btn-sm approval-btn-reject">기각</button>
                            <input type="text" name="opinion" class="form-control form-control-sm" placeholder="기각 사유 입력" required>
                        </form>
                    </div>
                </div>
            @elseif($boxType === 'pending' && ($canApproveOrReject ?? false))
                <div class="approval-form-actions">
                    <div class="approval-action-toolbar">
                        <form method="POST" action="{{ route($approveRoute, ['docNo' => $document['doc_no'], 'tab' => $tab]) }}">
                            @csrf
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" class="btn btn-primary btn-sm approval-btn-approve">결재</button>
                        </form>
                        <form method="POST" action="{{ route($delegateRoute, ['docNo' => $document['doc_no'], 'box' => $boxType, 'tab' => $tab]) }}">
                            @csrf
                            <input type="hidden" name="action" value="delegate">
                            <button type="submit" class="btn btn-outline-primary btn-sm">전결</button>
                        </form>
                        <form method="POST" action="{{ route($holdRoute, ['docNo' => $document['doc_no'], 'box' => $boxType, 'tab' => $tab]) }}">
                            @csrf
                            <input type="hidden" name="action" value="hold">
                            <button type="submit" class="btn btn-outline-secondary btn-sm">보류</button>
                        </form>
                        <form method="POST" action="{{ route($rejectRoute, ['docNo' => $document['doc_no'], 'tab' => $tab]) }}" class="approval-reject-form">
                            @csrf
                            <input type="hidden" name="action" value="reject">
                            <button type="submit" class="btn btn-outline-danger btn-sm approval-btn-reject">기각</button>
                            <input type="text" name="opinion" class="form-control form-control-sm" placeholder="기각 사유 입력" required>
                        </form>
                    </div>
                </div>
            @endif

            @if($latestRejectOpinion)
                <div class="approval-reject-note">
                    <div class="approval-reject-note-label">반려 사유</div>
                    <div class="approval-reject-note-content">{{ $latestRejectOpinion->content }}</div>
                    <div class="approval-reject-note-meta">
                        {{ $latestRejectOpinion->user?->name ?? '결재자' }} / {{ optional($latestRejectOpinion->created_at)->format('Y-m-d H:i') }}
                    </div>
                </div>
            @endif

            @if($canEditDraft ?? false)
                <form method="POST" action="{{ route('backoffice.approvals.personal.update', ['docNo' => $document['doc_no']]) }}" enctype="multipart/form-data" id="approvalEditForm">
                    @csrf
                    @method('PUT')
                    <div class="approval-form-actions">
                        <div class="approval-action-toolbar">
                            <button type="submit" class="btn btn-primary btn-sm">저장</button>
                        </div>
                    </div>
                    <div class="approval-document-shell">
                        @include($templateView)
                    </div>
                </form>
            @else
                <div class="approval-document-shell">
                    @include($templateView)
                </div>
            @endif

        </div>
    </div>
</div>
@endsection

