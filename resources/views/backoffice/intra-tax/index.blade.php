@extends('backoffice.layouts.app')

@section('title', '세금계산서')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/backoffice/boards.css') }}">
@endsection

@section('scripts')
<script src="{{ asset('js/backoffice/intra-tax.js') }}"></script>
@endsection

@section('content')
@php $listQuery = $listQuery ?? []; @endphp
@if (session('success'))
    <div class="alert alert-success board-hidden-alert">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-danger board-hidden-alert">{{ session('error') }}</div>
@endif

<div class="board-container" data-delete-multiple-endpoint="{{ route('backoffice.intra-tax.delete-multiple') }}" data-list-query='@json($listQuery)'>
    <div class="board-page-header">
        <div class="board-page-buttons">
            <button type="button" id="btnDeleteMultiple" class="btn btn-danger">
                <i class="fas fa-trash"></i> 선택 삭제
            </button>
            <a href="{{ route('backoffice.intra-tax.create', $listQuery) }}" class="btn btn-success">
                <i class="fas fa-plus"></i> 신규등록
            </a>
        </div>
    </div>

    <div class="board-card">
        <div class="board-card-body">
            <div class="board-filter">
                <form method="GET" action="{{ route('backoffice.intra-tax.index') }}" class="filter-form">
                    <input type="hidden" name="sortField" value="{{ $filters['sortField'] ?? 'B_InpDate' }}">
                    <input type="hidden" name="sort" value="{{ $filters['sort'] ?? 'desc' }}">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="start_date" class="filter-label">등록일 시작</label>
                            <input type="date" id="start_date" name="start_date" class="filter-input" value="{{ $filters['start_date'] ?? '' }}">
                        </div>
                        <div class="filter-group">
                            <label for="end_date" class="filter-label">등록일 끝</label>
                            <input type="date" id="end_date" name="end_date" class="filter-input" value="{{ $filters['end_date'] ?? '' }}">
                        </div>
                        <div class="filter-group">
                            <label for="category" class="filter-label">분류</label>
                            <input type="text" id="category" name="category" class="filter-input" value="{{ $filters['category'] ?? '' }}">
                        </div>
                        <div class="filter-group">
                            <label for="state" class="filter-label">상태</label>
                            <select id="state" name="state" class="filter-select">
                                <option value="">전체</option>
                                @foreach(['A' => '답변완료', 'R' => '접수', 'I' => '처리중', 'E' => '보류', 'H' => '숨김'] as $code => $label)
                                    <option value="{{ $code }}" @selected(($filters['state'] ?? '') === $code)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="keyword" class="filter-label">제목 검색</label>
                            <input type="text" id="keyword" name="keyword" class="filter-input" value="{{ $filters['keyword'] ?? '' }}">
                        </div>
                        <div class="filter-group">
                            <div class="filter-buttons">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> 검색</button>
                                <a href="{{ route('backoffice.intra-tax.index') }}" class="btn btn-secondary"><i class="fas fa-undo"></i> 초기화</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="board-list-header">
                <div class="list-info">
                    <span class="list-count">Total : {{ $posts->total() }}</span>
                </div>
                <div class="list-controls">
                    <form method="GET" action="{{ route('backoffice.intra-tax.index') }}" class="per-page-form">
                        <input type="hidden" name="start_date" value="{{ $filters['start_date'] ?? '' }}">
                        <input type="hidden" name="end_date" value="{{ $filters['end_date'] ?? '' }}">
                        <input type="hidden" name="category" value="{{ $filters['category'] ?? '' }}">
                        <input type="hidden" name="state" value="{{ $filters['state'] ?? '' }}">
                        <input type="hidden" name="keyword" value="{{ $filters['keyword'] ?? '' }}">
                        <input type="hidden" name="sortField" value="{{ $filters['sortField'] ?? 'B_InpDate' }}">
                        <input type="hidden" name="sort" value="{{ $filters['sort'] ?? 'desc' }}">
                        <label for="per_page" class="per-page-label">표시 개수:</label>
                        <select name="per_page" id="per_page" class="per-page-select" onchange="this.form.submit()">
                            <option value="10" @selected($perPage === 10)>10개</option>
                            <option value="20" @selected($perPage === 20)>20개</option>
                            <option value="50" @selected($perPage === 50)>50개</option>
                            <option value="100" @selected($perPage === 100)>100개</option>
                        </select>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="board-table">
                    <thead>
                        <tr>
                            <th class="w5"><input type="checkbox" id="select-all" class="form-check-input"></th>
                            <th class="w10">번호</th>
                            <th>제목</th>
                            <th class="w10">분류</th>
                            <th class="w10">작성자</th>
                            <th class="w15">등록일</th>
                            <th class="bo-manage-col">관리</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($posts as $index => $post)
                            <tr>
                                <td><input type="checkbox" class="form-check-input bo-row-checkbox" value="{{ $post->B_idx }}"></td>
                                <td>
                                    @if($post->B_Notice === 'Y')
                                        <span class="board-notice-badge">공지</span>
                                    @else
                                        {{ $posts->total() - ($posts->currentPage() - 1) * $posts->perPage() - $index }}
                                    @endif
                                </td>
                                <td class="text-start">
                                    @if(($post->B_Secret ?? 'N') === 'Y')
                                        <a class="bo-project-title-link js-secret-post-link" href="#" data-unlock-url="{{ route('backoffice.intra-tax.unlock', $post->B_idx) }}">
                                            <i class="fas fa-lock"></i> {{ $post->B_Title ?: '(제목없음)' }}
                                            @if((int) $post->comments_count > 0)
                                                ({{ (int) $post->comments_count }})
                                            @endif
                                        </a>
                                    @else
                                        <a class="bo-project-title-link" href="{{ route('backoffice.intra-tax.edit', array_merge(['idx' => $post->B_idx], $listQuery)) }}">
                                            {{ $post->B_Title ?: '(제목없음)' }}
                                            @if((int) $post->comments_count > 0)
                                                ({{ (int) $post->comments_count }})
                                            @endif
                                        </a>
                                    @endif
                                </td>
                                <td>{{ $post->B_Category ?: '' }}</td>
                                <td>{{ $post->B_Name ?: '' }}</td>
                                <td>{{ !empty($post->B_InpDate) ? \Illuminate\Support\Carbon::parse($post->B_InpDate)->format('Y-m-d H:i') : '' }}</td>
                                <td class="bo-manage-col">
                                    <div class="board-btn-group bo-action-inline">
                                        <a href="{{ route('backoffice.intra-tax.edit', array_merge(['idx' => $post->B_idx], $listQuery)) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i> 수정
                                        </a>
                                        <form method="POST" action="{{ route('backoffice.intra-tax.destroy', $post->B_idx) }}" class="bo-inline-form" onsubmit="return confirm('삭제하시겠습니까?');">
                                            @csrf
                                            @method('DELETE')
                                            @foreach($listQuery as $rk => $rv)
                                                <input type="hidden" name="return_{{ $rk }}" value="{{ $rv }}">
                                            @endforeach
                                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> 삭제</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">등록된 게시글이 없습니다.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-pagination :paginator="$posts" />
        </div>
    </div>
</div>
@endsection
