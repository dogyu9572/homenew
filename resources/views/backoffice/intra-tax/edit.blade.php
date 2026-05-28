@extends('backoffice.layouts.app')

@section('title', $mode === 'create' ? '세금계산서 등록' : '세금계산서 수정')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/backoffice/boards.css') }}">
@endsection

@section('scripts')
<script src="{{ asset('js/backoffice/board-post-form.js') }}"></script>
<script src="{{ asset('js/backoffice/project-manages-form.js') }}"></script>
<x-backoffice-ckeditor-assets />
@endsection

@section('content')
@if (session('success'))
    <div class="alert alert-success board-hidden-alert">{{ session('success') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger board-hidden-alert">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $listQuery = $listQuery ?? [];
    $intraTaxReturnKeys = ['page', 'per_page', 'start_date', 'end_date', 'category', 'keyword', 'state', 'sortField', 'sort'];
    if (old()) {
        foreach ($intraTaxReturnKeys as $k) {
            $v = old('return_'.$k);
            if ($v !== null && $v !== '') {
                $listQuery[$k] = $v;
            }
        }
    }
@endphp
<div class="board-container">
    <div class="board-header">
        <a href="{{ route('backoffice.intra-tax.index', $listQuery) }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> 목록으로
        </a>
    </div>

    <div class="board-card">
        <div class="board-card-body">
            <form method="POST" action="{{ $mode === 'create' ? route('backoffice.intra-tax.store') : route('backoffice.intra-tax.update', $post->B_idx) }}" enctype="multipart/form-data">
                @csrf
                @if($mode === 'edit')
                    @method('PUT')
                    @foreach($listQuery as $rk => $rv)
                        <input type="hidden" name="return_{{ $rk }}" value="{{ $rv }}">
                    @endforeach
                @endif

                <div class="bo-form-section">
                    <h3 class="bo-section-title">기본정보</h3>
                    <div class="bo-form-list">
                        <div class="bo-form-row bo-form-row-2">
                            <label class="bo-form-label">제목 <span class="required">*</span></label>
                            <div class="bo-form-field">
                                <input type="text" class="board-form-control" name="B_Title" value="{{ old('B_Title', $post->B_Title ?? '') }}" required>
                            </div>
                            <label class="bo-form-label">분류</label>
                            <div class="bo-form-field">
                                <input type="text" class="board-form-control" name="B_Category" value="{{ old('B_Category', $post->B_Category ?? '') }}">
                            </div>
                        </div>

                        <div class="bo-form-row bo-form-row-2">
                            <label class="bo-form-label">작성자</label>
                            <div class="bo-form-field">
                                <input type="text" class="board-form-control" name="B_Name" value="{{ old('B_Name', $post->B_Name ?? '') }}">
                            </div>
                            <label class="bo-form-label">이메일</label>
                            <div class="bo-form-field">
                                <input type="text" class="board-form-control" name="B_Email" value="{{ old('B_Email', $post->B_Email ?? '') }}">
                            </div>
                        </div>

                        <div class="bo-form-row bo-form-row-2">
                            <label class="bo-form-label">비밀번호</label>
                            <div class="bo-form-field">
                                <input type="text" class="board-form-control" name="B_Password" value="">
                            </div>
                            <label class="bo-form-label">옵션</label>
                            <div class="bo-form-field">
                                <label class="checkbox-label"><input type="checkbox" name="B_Notice" value="Y" @checked(old('B_Notice', $post->B_Notice ?? 'N') === 'Y')> 공지</label>
                                <label class="checkbox-label"><input type="checkbox" name="B_Secret" value="Y" @checked(old('B_Secret', $post->B_Secret ?? 'N') === 'Y')> 비밀글</label>
                            </div>
                        </div>

                        <div class="bo-form-row">
                            <label class="bo-form-label">본문</label>
                            <div class="bo-form-field">
                                <textarea class="board-form-control" name="B_Content" rows="12" data-backoffice-ckeditor data-source-editing="true">{{ old('B_Content', $post->B_Content ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bo-form-section">
                    <h3 class="bo-section-title">첨부파일</h3>
                    <div class="bo-form-list">
                        <div class="bo-form-row">
                            <label class="bo-form-label">첨부파일</label>
                            <div class="bo-form-field">
                                <div class="board-file-upload">
                                    <div class="board-file-input-wrapper">
                                        <input type="file" class="board-file-input" id="attachments" name="attachments[]" multiple accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar">
                                        <div class="board-file-input-content">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                            <span class="board-file-input-text">파일을 선택하거나 여기로 드래그하세요</span>
                                            <span class="board-file-input-subtext">최대 5개, 각 파일 10MB 이하</span>
                                        </div>
                                    </div>

                                    @if(($files ?? collect())->isNotEmpty())
                                        <div class="board-existing-files">
                                            <div class="board-attachment-list">
                                                @foreach($files as $file)
                                                    <div class="board-attachment-item existing-file">
                                                        <i class="fas fa-file"></i>
                                                        <a class="board-attachment-link board-attachment-name" href="{{ route('backoffice.intra-tax.files.download', [$post->B_idx, $file->F_Idx]) }}">
                                                            {{ $file->F_Name }}
                                                        </a>
                                                        @if(!empty($file->F_InpDate))
                                                            <span class="board-attachment-size">({{ \Illuminate\Support\Carbon::parse($file->F_InpDate)->format('Y-m-d H:i') }})</span>
                                                        @endif
                                                        <button type="button" class="board-attachment-remove btn-remove-existing-attachment">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                        <input type="hidden" name="existing_attachment_tokens[]" value="{{ $file->F_Idx }}">
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <div class="board-file-preview" id="filePreview"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="board-form-actions">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> 저장</button>
                    <a href="{{ route('backoffice.intra-tax.index', $listQuery) }}" class="btn btn-secondary">취소</a>
                </div>
            </form>

            @if($mode === 'edit')
                <div class="bo-form-section mt-4">
                    <h3 class="bo-section-title">댓글</h3>
                    <div class="board-card">
                        <div class="board-card-body">
                            <div class="bo-form-list">
                                <form method="POST" action="{{ route('backoffice.intra-tax.comments.store', $post->B_idx) }}">
                                    @csrf
                                    @foreach($listQuery as $rk => $rv)
                                        <input type="hidden" name="return_{{ $rk }}" value="{{ $rv }}">
                                    @endforeach
                                    <div class="bo-form-row bo-form-row-2">
                                        <label class="bo-form-label">작성자</label>
                                        <div class="bo-form-field">
                                            <input type="text" class="board-form-control" name="C_Name" placeholder="작성자">
                                        </div>
                                        <label class="bo-form-label">비밀번호</label>
                                        <div class="bo-form-field">
                                            <input type="text" class="board-form-control" name="C_Passwd" placeholder="비밀번호">
                                        </div>
                                    </div>
                                    <div class="bo-form-row">
                                        <label class="bo-form-label">내용</label>
                                        <div class="bo-form-field">
                                            <textarea class="board-form-control" name="C_Comment" rows="4" required placeholder="댓글 내용을 입력하세요."></textarea>
                                            <div class="mt-2 text-end">
                                                <button type="submit" class="btn btn-primary btn-sm">댓글 등록</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="board-card mt-3">
                        <div class="board-card-body">
                            <table class="board-table">
                                <thead>
                                    <tr>
                                        <th class="w10">번호</th>
                                        <th class="w15">작성자</th>
                                        <th>내용</th>
                                        <th class="w15">등록일</th>
                                        <th class="w10">관리</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($comments as $index => $comment)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $comment->C_Name ?? '' }}</td>
                                            <td class="text-start">{!! nl2br(e($comment->C_Comment ?? '')) !!}</td>
                                            <td>{{ !empty($comment->C_Inpdate) ? \Illuminate\Support\Carbon::parse($comment->C_Inpdate)->format('Y-m-d H:i') : '' }}</td>
                                            <td>
                                                <form method="POST" action="{{ route('backoffice.intra-tax.comments.destroy', [$post->B_idx, $comment->C_Idx]) }}" class="bo-inline-form" onsubmit="return confirm('댓글을 삭제하시겠습니까?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    @foreach($listQuery as $rk => $rv)
                                                        <input type="hidden" name="return_{{ $rk }}" value="{{ $rv }}">
                                                    @endforeach
                                                    <button type="submit" class="btn btn-danger btn-sm">삭제</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">댓글이 없습니다.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

