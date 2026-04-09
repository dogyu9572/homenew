@extends('backoffice.layouts.app')

@section('title', '문의 관리')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/backoffice/boards.css') }}">
@endsection

@section('scripts')
<script src="{{ asset('js/backoffice/contact-index.js') }}"></script>
@endsection

@section('content')
@if (session('success'))
    <div class="alert alert-success board-hidden-alert">{{ session('success') }}</div>
@endif

<div class="board-container contacts-index-page">
    <div class="board-page-header">
        <div class="board-page-buttons">
            <button type="button" id="btnDeleteMultiple" class="btn btn-danger">
                <i class="fas fa-trash"></i> 선택 삭제
            </button>
        </div>
    </div>

    <div class="board-card">
        <div class="board-card-body">
            <div class="board-filter">
                <form method="GET" action="{{ route('backoffice.contacts.index') }}" class="filter-form">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="status" class="filter-label">처리 상태</label>
                            <select id="status" name="status" class="filter-select">
                                <option value="">전체</option>
                                @foreach($statuses as $st)
                                    <option value="{{ $st }}" @selected(request('status') === $st)>{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="keyword" class="filter-label">검색어</label>
                            <input id="keyword" type="text" name="keyword" value="{{ request('keyword') }}" class="filter-input" placeholder="회사명, 담당자, 연락처, 이메일, 문의내용">
                        </div>
                        <div class="filter-group">
                            <div class="filter-buttons">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> 검색
                                </button>
                                <a href="{{ route('backoffice.contacts.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-undo"></i> 초기화
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="board-list-header">
                <div class="list-info">
                    <span class="list-count">Total : {{ $contacts->total() }}</span>
                </div>
                <div class="list-controls">
                    <form method="GET" action="{{ route('backoffice.contacts.index') }}" class="per-page-form">
                        @foreach(request()->except('per_page') as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach
                        <label for="per_page" class="per-page-label">표시 개수:</label>
                        <select id="per_page" name="per_page" class="per-page-select" onchange="this.form.submit()">
                            <option value="10" @selected((int) request('per_page', 10) === 10)>10개</option>
                            <option value="20" @selected((int) request('per_page') === 20)>20개</option>
                            <option value="50" @selected((int) request('per_page') === 50)>50개</option>
                            <option value="100" @selected((int) request('per_page') === 100)>100개</option>
                        </select>
                    </form>
                </div>
            </div>

            <form id="bulkDeleteForm" method="POST" action="{{ route('backoffice.contacts.delete-multiple') }}">
                @csrf
            <div class="table-responsive">
                <table class="board-table">
                    <thead>
                        <tr>
                            <th class="w5 board-checkbox-column">
                                <input type="checkbox" id="select-all" class="form-check-input">
                            </th>
                            <th>No</th>
                            <th>접수일시</th>
                            <th>유입 경로</th>
                            <th>회사명</th>
                            <th>담당자성함/직책</th>
                            <th>연락처</th>
                            <th>이메일</th>
                            <th>관심 서비스</th>
                            <th>상태</th>
                            <th>관리</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($contacts as $index => $row)
                        <tr>
                            <td>
                                <input type="checkbox" name="ids[]" value="{{ $row->id }}" class="form-check-input contact-checkbox">
                            </td>
                            <td>{{ $contacts->total() - ($contacts->currentPage() - 1) * $contacts->perPage() - $index }}</td>
                            <td>{{ $row->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                @if(!empty($row->source_type) && filled($row->resolved_source_title))
                                    {{ $row->resolved_source_title }}
                                @elseif(!empty($row->source_type))
                                    -
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $row->company }}</td>
                            <td>{{ $row->contact_person }}</td>
                            <td>{{ $row->phone }}</td>
                            <td>{{ $row->email }}</td>
                            <td>{{ is_array($row->services) ? implode(', ', $row->services) : '' }}</td>
                            <td>{{ $row->status }}</td>
                            <td>
                                <div class="board-btn-group">
                                    <a href="{{ route('backoffice.contacts.edit', $row) }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i> 수정
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="11" class="text-center">데이터가 없습니다.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            </form>
            <x-pagination :paginator="$contacts" />
        </div>
    </div>
</div>
@endsection
