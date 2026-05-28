@extends('backoffice.layouts.app')

@section('title', '결재문서 작성')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/backoffice/boards.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/approval.css') }}">
@endsection

@section('content')
<div class="board-container">
    <div class="board-header">
        <a href="{{ route('backoffice.approvals.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> 목록으로
        </a>
    </div>

    <div class="board-card">
        <div class="board-card-body">
            <div class="approval-create-layout">
                <aside class="approval-template-tree">
                    <div class="approval-template-tree-header">
                        <h6>결재문서양식</h6>
                    </div>
                    <div class="approval-template-tree-body">
                        @foreach($formTypes as $typeKey => $typeLabel)
                            <div class="approval-template-group">
                                <a href="{{ route('backoffice.approvals.create', ['form_type' => $typeKey]) }}" class="approval-template-group-title {{ $selectedFormType === $typeKey ? 'active' : '' }}">
                                    {{ $typeLabel }}
                                    <span>{{ count($templateCatalog[$typeKey] ?? []) }}</span>
                                </a>
                                @if($selectedFormType === $typeKey && !empty($templateCatalog[$typeKey]))
                                    <ul class="approval-template-list">
                                        @foreach($templateCatalog[$typeKey] as $template)
                                            <li>
                                                <a href="{{ route('backoffice.approvals.create', ['form_type' => $typeKey, 'template' => $template['key']]) }}" class="{{ $selectedTemplate === $template['key'] ? 'active' : '' }}">
                                                    {{ $template['name'] }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </aside>

                <section class="approval-template-preview">
                    <div class="approval-template-preview-header">
                        <h6>양식 선택</h6>
                    </div>
                    <div class="approval-template-preview-body">
                        <div class="approval-template-grid">
                            @forelse($templateCatalog[$selectedFormType] ?? [] as $template)
                                <article class="approval-template-card {{ $selectedTemplate === $template['key'] ? 'is-selected' : '' }}">
                                    <h6>{{ $template['name'] }}</h6>
                                    <p>{{ $template['description'] }}</p>
                                    <div class="approval-template-card-actions">
                                        <a href="{{ route('backoffice.approvals.drafts.create', ['templateKey' => $template['key']]) }}" class="btn btn-primary btn-sm">작성하기</a>
                                    </div>
                                </article>
                            @empty
                                <p class="approval-template-empty">등록된 양식이 없습니다.</p>
                            @endforelse
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/backoffice/approval.js') }}"></script>
@endsection
