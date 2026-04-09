@extends('backoffice.layouts.app')

@section('title', $pageTitle ?? '사용자 접속로그')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/common/buttons.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/users.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/boards.css') }}">
@endsection

@section('content')
<div class="board-container users-page">  

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="board-card">
        <div class="board-card-body">
            <!-- 검색 필터 -->
            <div class="user-filter">
                <form method="GET" action="{{ route('backoffice.user-access-logs') }}" class="filter-form">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="name" class="filter-label">회원명</label>
                            <input type="text" id="name" name="name" class="filter-input"
                                placeholder="로그인 방문만 검색" value="{{ request('name') }}">
                        </div>
                        <div class="filter-group">
                            <label for="ip" class="filter-label">IP</label>
                            <input type="text" id="ip" name="ip" class="filter-input"
                                placeholder="IP 일부" value="{{ request('ip') }}">
                        </div>
                        <div class="filter-group">
                            <label for="url" class="filter-label">페이지 URL</label>
                            <input type="text" id="url" name="url" class="filter-input"
                                placeholder="URL 일부" value="{{ request('url') }}">
                        </div>
                        <div class="filter-group">
                            <label for="from" class="filter-label">기간</label>
                            <div class="date-range">
                                <input type="date" id="from" name="from" class="filter-input"
                                    value="{{ request('from') }}">
                                <span class="date-separator">~</span>
                                <input type="date" id="to" name="to" class="filter-input"
                                    value="{{ request('to') }}">
                            </div>
                        </div>
                        <div class="filter-group">
                            <div class="filter-buttons">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> 검색
                                </button>
                                <a href="{{ route('backoffice.user-access-logs') }}" class="btn btn-secondary">
                                    <i class="fas fa-undo"></i> 초기화
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            @if($logs->count() > 0)
                <!-- 목록 개수 선택 -->
                <div class="board-list-header">
                    <div class="list-info">
                        <span class="list-count">Total : {{ $logs->total() }}</span>
                    </div>
                    <div class="list-controls">
                        <form method="GET" action="{{ route('backoffice.user-access-logs') }}" class="per-page-form">
                            @foreach(request()->except('per_page') as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach
                            <label for="per_page" class="per-page-label">목록 개수:</label>
                            <select id="per_page" name="per_page" class="per-page-select" onchange="this.form.submit()">
                                <option value="10" @selected(request('per_page') == 10)>10</option>
                                <option value="20" @selected(request('per_page') == 20 || !request()->has('per_page'))>20</option>
                                <option value="50" @selected(request('per_page') == 50)>50</option>
                                <option value="100" @selected(request('per_page') == 100)>100</option>
                            </select>
                        </form>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="board-table">
                        <thead>
                            <tr>
                                <th>NO</th>
                                <th>회원</th>
                                <th>IP</th>
                                <th>접속 URL</th>
                                <th>회원 가입일</th>
                                <th>접속일시</th>
                                <th>REFERER</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td>{{ $logs->total() - ($logs->currentPage() - 1) * $logs->perPage() - $loop->index }}</td>
                                    <td>{{ $log->user ? $log->user->name : '비회원' }}</td>
                                    <td>{{ $log->ip_address }}</td>
                                    <td class="text-break"><span title="{{ $log->page_url }}">{{ \Illuminate\Support\Str::limit($log->page_url, 80) }}</span></td>
                                    <td>{{ $log->user ? $log->user->created_at->format('Y-m-d H:i:s') : '-' }}</td>
                                    <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                    <td class="text-break">{{ $log->referer ? \Illuminate\Support\Str::limit($log->referer, 60) : '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <x-pagination :paginator="$logs" />
            @else
                <div class="no-data">
                    <p>등록된 접속로그가 없습니다.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
