@extends('backoffice.layouts.app')

@section('title', $template['name'].' 작성')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/backoffice/boards.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/approval.css') }}">
@endsection

@section('content')
<div
    class="board-container"
    data-approver-users-endpoint="{{ route('backoffice.approvals.users') }}"
    data-requester-name="{{ auth()->user()->name ?? '' }}"
>
    <div class="board-header">
        <a href="{{ route('backoffice.approvals.create', ['form_type' => $template['form_type']]) }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> 양식 선택으로
        </a>
    </div>

    <div class="board-card">
        <div class="board-card-body">
            <div class="approval-draft-header">
                <h4>{{ $template['name'] }}</h4>
                <p>{{ $template['form_type_label'] }}</p>
            </div>

            <form method="POST" action="{{ route('backoffice.approvals.store') }}" enctype="multipart/form-data" id="approvalDraftForm">
                @csrf
                <input type="hidden" name="template_key" value="{{ $template['key'] }}">
                <input type="hidden" name="form_type" value="{{ $template['form_type'] }}">
                <div id="approvalLineInputs"></div>
                <div id="cooperationLineInputs"></div>

                <div class="approval-document-shell">
                    @include($templateView)
                </div>

                <div class="board-btn-group approval-draft-actions">
                    <button type="submit" class="btn btn-success" data-approval-submit>상신</button>
                    <a href="{{ route('backoffice.approvals.create', ['form_type' => $template['form_type']]) }}" class="btn btn-secondary">취소</a>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="approval-modal-backdrop" id="approverModal" hidden>
    <div class="approval-modal-panel" role="dialog" aria-modal="true" aria-labelledby="approverModalTitle">
        <div class="approval-modal-header">
            <h5 id="approverModalTitle">결재선 지정</h5>
            <button type="button" class="btn btn-outline-secondary btn-sm" data-approver-close>닫기</button>
        </div>
        <div class="approval-modal-search">
            <input type="text" id="approverKeyword" class="form-control" placeholder="이름/부서/직급 검색">
            <button type="button" class="btn btn-primary btn-sm" id="approverSearchBtn">검색</button>
        </div>
        <div class="table-responsive approval-modal-table-wrap">
            <table class="board-table">
                <thead>
                    <tr>
                        <th class="w15">이름</th>
                        <th class="w20">부서</th>
                        <th class="w15">직급</th>
                        <th class="w15">아이디</th>
                        <th class="w10">선택</th>
                    </tr>
                </thead>
                <tbody id="approverListBody">
                    <tr>
                        <td colspan="5" class="text-center">목록을 불러오는 중입니다.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<x-backoffice-ckeditor-assets />
<script src="{{ asset('js/backoffice/approval.js') }}"></script>
@endsection

