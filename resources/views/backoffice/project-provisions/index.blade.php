@extends('backoffice.layouts.app')

@section('title', '프로젝트 생성')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/backoffice/boards.css') }}">
@endsection

@section('content')
    @if (session('success'))
        <div class="board-alert board-alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="board-container">
        <div class="board-page-header">
            <div class="board-page-buttons">
                <a href="{{ route('backoffice.project-provisions.create') }}" class="btn btn-success">
                    <i class="fas fa-plus"></i> 새 프로젝트
                </a>
            </div>
        </div>

        <div class="board-card">
            <div class="board-card-body">
                <div class="board-list-header">
                    <div class="list-info">
                        <span class="list-count">Total : {{ $provisions->total() }}</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="board-table">
                        <thead>
                            <tr>
                                <th class="w5">번호</th>
                                <th>프로젝트명</th>
                                <th class="w10">종류</th>
                                <th class="w10">상태</th>
                                <th class="w15">단계</th>
                                <th>접속 주소</th>
                                <th class="w15">등록일</th>
                                <th class="w10">관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($provisions as $provision)
                                <tr>
                                    <td>{{ $provision->id }}</td>
                                    <td>{{ $provision->project_name }}</td>
                                    <td>{{ $provision->stackLabel() }}</td>
                                    <td>{{ $provision->statusLabel() }}</td>
                                    <td>{{ $provision->stepLabel() }}</td>
                                    <td>
                                        @if($provision->site_url)
                                            <a href="{{ $provision->site_url }}" target="_blank" rel="noopener noreferrer">{{ $provision->site_url }}</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $provision->created_at?->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <a href="{{ route('backoffice.project-provisions.show', $provision) }}" class="btn btn-primary btn-sm">
                                            로그
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">등록된 프로젝트가 없습니다.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <x-pagination :paginator="$provisions" />
            </div>
        </div>
    </div>
@endsection
