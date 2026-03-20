@extends('backoffice.layouts.app')

@section('title', $pageTitle ?? '')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/backoffice/boards.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/users.css') }}">
@endsection

@section('content')
@if(session('success'))
        <div class="alert alert-success board-hidden-alert">
            {{ session('success') }}
        </div>
@endif

@if(session('error'))
        <div class="alert alert-danger board-hidden-alert">
            {{ session('error') }}
        </div>
@endif

<div class="board-container">
    <div class="board-page-header">
        <div class="board-page-buttons">
            <a href="{{ route('backoffice.users.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> 신규등록
            </a>
        </div>
    </div>

    <div class="board-card">
        <div class="board-card-body">
            <div class="board-filter">
                <form method="GET" action="{{ route('backoffice.users.index') }}" class="filter-form">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="name" class="filter-label">이름</label>
                            <input type="text" id="name" name="name" class="filter-input"
                                placeholder="이름을 입력하세요" value="{{ request('name') }}">
                        </div>
                        <div class="filter-group">
                            <label for="email" class="filter-label">이메일</label>
                            <input type="text" id="email" name="email" class="filter-input"
                                placeholder="이메일을 입력하세요" value="{{ request('email') }}">
                        </div>
                        <div class="filter-group">
                            <label for="login_id" class="filter-label">로그인 ID</label>
                            <input type="text" id="login_id" name="login_id" class="filter-input"
                                placeholder="로그인 ID를 입력하세요" value="{{ request('login_id') }}">
                        </div>
                        <div class="filter-group">
                            <label for="is_active" class="filter-label">상태</label>
                            <select id="is_active" name="is_active" class="filter-select">
                                <option value="">전체</option>
                                <option value="1" @selected(request('is_active') == '1')>활성화</option>
                                <option value="0" @selected(request('is_active') == '0')>비활성화</option>
                            </select>
                        </div>
                    </div>

                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="created_from" class="filter-label">가입일</label>
                            <div class="date-range">
                                <input type="date" id="created_from" name="created_from" class="filter-input"
                                    value="{{ request('created_from') }}">
                                <span class="date-separator">~</span>
                                <input type="date" id="created_to" name="created_to" class="filter-input"
                                    value="{{ request('created_to') }}">
                            </div>
                        </div>
                        <div class="filter-group">
                            <div class="filter-buttons">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> 검색
                                </button>
                                <a href="{{ route('backoffice.users.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-undo"></i> 초기화
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            @if($users->count() > 0)
                <div class="board-list-header">
                    <div class="list-info">
                        <span class="list-count">Total : {{ $users->total() }}</span>
                    </div>
                    <div class="list-controls">
                        <form method="GET" action="{{ route('backoffice.users.index') }}" class="per-page-form">
                            @foreach(request()->except('per_page') as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach
                            <label for="per_page" class="per-page-label">표시 개수:</label>
                            <select id="per_page" name="per_page" class="per-page-select" onchange="this.form.submit()">
                                <option value="10" @selected(request('per_page', 10) == 10)>10개</option>
                                <option value="20" @selected(request('per_page') == 20)>20개</option>
                                <option value="50" @selected(request('per_page') == 50)>50개</option>
                                <option value="100" @selected(request('per_page') == 100)>100개</option>
                            </select>
                        </form>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="board-table">
                        <thead>
                            <tr>
                                <th>번호</th>
                                <th>이름</th>
                                <th>이메일</th>
                                <th>로그인 ID</th>
                                <th>가입일</th>
                                <th>상태</th>
                                <th>관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $index => $user)
                                <tr>
                                    <td>{{ $users->total() - ($users->currentPage() - 1) * $users->perPage() - $loop->index }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->login_id ?? '-' }}</td>
                                    <td>{{ $user->created_at->format('Y.m.d H:i') }}</td>
                                    <td>
                                        {{ $user->is_active ? '활성화' : '비활성화' }}
                                    </td>
                                    <td>
                                        <div class="board-btn-group">
                                            <a href="{{ route('backoffice.users.edit', $user) }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> 수정
                                            </a>
                                            <form action="{{ route('backoffice.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('이 회원을 삭제하시겠습니까?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i> 삭제
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <x-pagination :paginator="$users" />
            @else
                <div class="no-data">
                    <p>등록된 회원이 없습니다.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
