@extends('backoffice.layouts.app')

@section('title', '프로젝트 등록')

@section('styles')
@endsection

@section('scripts')
<script src="{{ asset('js/backoffice/board-post-form.js') }}"></script>
<script src="{{ asset('js/backoffice/project-manages-form.js') }}"></script>
@endsection

@section('content')
@if ($errors->any())
    <div class="alert alert-danger board-hidden-alert">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php $listQuery = $listQuery ?? []; @endphp
<div class="board-container">
    <div class="board-header">
        <a href="{{ route('backoffice.project-manages.index', $listQuery) }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> 목록으로
        </a>
    </div>

    <div class="board-card">
        <div class="board-card-body">
            <form method="POST" action="{{ route('backoffice.project-manages.store') }}" enctype="multipart/form-data">
                @csrf
                @include('backoffice.project-manages._form', ['mode' => 'create', 'project' => (object) [], 'attachmentItems' => collect()])

                <div class="board-form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> 저장
                    </button>
                    <a href="{{ route('backoffice.project-manages.index', $listQuery) }}" class="btn btn-secondary">취소</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

