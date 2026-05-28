@extends('backoffice.layouts.app')

@section('title', '프로젝트 수정')

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

@php
    $listQuery = $listQuery ?? [];
    $pmReturnKeys = array_merge(
        ['page', 'per_page', 'HostingSdate', 'HostingEdate', 'FindValue', 'TeamUser', 'gubun', 'sortField', 'sort'],
        array_map(static fn (int $i) => 'ch'.$i, range(1, 12))
    );
    if (old()) {
        foreach ($pmReturnKeys as $k) {
            $v = old('return_'.$k);
            if ($v !== null && $v !== '') {
                $listQuery[$k] = $v;
            }
        }
    }
@endphp
<div class="board-container">
    <div class="board-header">
        <a href="{{ route('backoffice.project-manages.index', $listQuery) }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> 목록으로
        </a>
    </div>

    <div class="board-card">
        <div class="board-card-body">
            <form method="POST" action="{{ route('backoffice.project-manages.update', $project->idx) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @foreach($listQuery as $rk => $rv)
                    <input type="hidden" name="return_{{ $rk }}" value="{{ $rv }}">
                @endforeach
                @include('backoffice.project-manages._form', ['mode' => 'edit', 'project' => $project, 'attachmentItems' => $attachmentItems ?? collect()])

                <div class="board-form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> 저장
                    </button>
                    <a href="{{ route('backoffice.project-manages.index', $listQuery) }}" class="btn btn-secondary">취소</a>
                </div>
            </form>

            <div class="mt-4">
                <h5>수정로그</h5>
                <table class="board-table">
                    <thead>
                        <tr>
                            <th class="w20">등록일</th>
                            <th class="w15">작성자</th>
                            <th>내용</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($modifyLogs ?? collect()) as $log)
                            <tr>
                                <td>{{ !empty($log->regdate) ? \Illuminate\Support\Carbon::parse($log->regdate)->format('Y-m-d H:i:s') : '' }}</td>
                                <td>{{ $log->user_name ?? '' }}</td>
                                <td class="text-start">{{ $log->memo ?? '' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">수정로그가 없습니다.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

