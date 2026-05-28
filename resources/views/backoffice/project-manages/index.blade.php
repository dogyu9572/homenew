@extends('backoffice.layouts.app')

@section('title', '프로젝트 관리')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/backoffice/boards.css') }}">
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('js/backoffice/project-manages.js') }}"></script>
@endsection

@section('content')
@if (session('success'))
    <div class="alert alert-success board-hidden-alert">
        {{ session('success') }}
    </div>
@endif

<div class="board-container">
    <div class="board-page-header">
        <div class="board-page-buttons">
            <button type="button" id="btnDeleteMultiple" class="btn btn-danger">
                <i class="fas fa-trash"></i> 선택 삭제
            </button>
            <button type="button" id="btnExport" class="btn btn-secondary">
                <i class="fas fa-file-excel"></i> 엑셀 다운로드
            </button>
            <a href="{{ route('backoffice.project-manages.create', $listQuery ?? []) }}" class="btn btn-success">
                <i class="fas fa-plus"></i> 신규등록
            </a>
        </div>
    </div>

    <div class="board-card">
        <div class="board-card-body">
            <div class="board-filter">
                <form method="GET" action="{{ route('backoffice.project-manages.index') }}" class="filter-form" id="searchForm">
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                    <input type="hidden" name="sortField" value="{{ $filters['sortField'] ?? 'idx' }}">
                    <input type="hidden" name="sort" value="{{ $filters['sort'] ?? 'desc' }}">
                    <div class="filter-row">
                        <div class="filter-group w-100">
                            <label class="filter-label">진행상태</label>
                            <div class="checkbox-group">
                                @php
                                    $states = ['계약','기획','디자인','퍼블리싱','개발','작업완료','수정사항','유지보수','보류','취소','광고기획','호스팅'];
                                @endphp
                                @foreach($states as $index => $state)
                                    @php $name = 'ch' . ($index + 1); @endphp
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="{{ $name }}" value="{{ $state }}" @checked(in_array($state, $filters['states'] ?? [], true))>
                                        <span>{{ $state }}({{ $stateCounts[$state] ?? 0 }})</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="HostingSdate" class="filter-label">호스팅 기간(시작)</label>
                            <input type="date" id="HostingSdate" name="HostingSdate" class="filter-input" value="{{ $filters['HostingSdate'] ?? '' }}">
                        </div>
                        <div class="filter-group">
                            <label for="HostingEdate" class="filter-label">호스팅 기간(끝)</label>
                            <input type="date" id="HostingEdate" name="HostingEdate" class="filter-input" value="{{ $filters['HostingEdate'] ?? '' }}">
                        </div>
                        <div class="filter-group">
                            <label for="FindValue" class="filter-label">검색어</label>
                            <input type="text" id="FindValue" name="FindValue" class="filter-input" value="{{ $filters['FindValue'] ?? '' }}" placeholder="프로젝트명/URL/담당자/이메일">
                        </div>
                        <div class="filter-group">
                            <label for="TeamUser" class="filter-label">담당자</label>
                            <input type="text" id="TeamUser" name="TeamUser" class="filter-input" value="{{ $filters['TeamUser'] ?? '' }}">
                        </div>
                        <div class="filter-group">
                            <label for="gubun" class="filter-label">구분</label>
                            <select id="gubun" name="gubun" class="filter-select">
                                <option value="">전체</option>
                                @foreach(['신규','리뉴얼','유지보수','수리엘'] as $gubun)
                                    <option value="{{ $gubun }}" @selected(($filters['gubun'] ?? '') === $gubun)>{{ $gubun }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-group">
                            <div class="filter-buttons">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> 검색</button>
                                <a href="{{ route('backoffice.project-manages.index') }}" class="btn btn-secondary"><i class="fas fa-undo"></i> 초기화</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="board-list-header">
                <div class="list-info">
                    <span class="list-count">Total : {{ $projects->total() }}</span>
                </div>
                <div class="list-controls">
                    <form method="GET" action="{{ route('backoffice.project-manages.index') }}" class="per-page-form">
                        @for($i = 1; $i <= 12; $i++)
                            @if(!empty(request("ch{$i}")))
                                <input type="hidden" name="ch{{ $i }}" value="{{ request("ch{$i}") }}">
                            @endif
                        @endfor
                        <input type="hidden" name="HostingSdate" value="{{ $filters['HostingSdate'] ?? '' }}">
                        <input type="hidden" name="HostingEdate" value="{{ $filters['HostingEdate'] ?? '' }}">
                        <input type="hidden" name="FindValue" value="{{ $filters['FindValue'] ?? '' }}">
                        <input type="hidden" name="TeamUser" value="{{ $filters['TeamUser'] ?? '' }}">
                        <input type="hidden" name="gubun" value="{{ $filters['gubun'] ?? '' }}">
                        <input type="hidden" name="sortField" value="{{ $filters['sortField'] ?? 'idx' }}">
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
                            <th class="w5 board-checkbox-column">
                                <input type="checkbox" id="select-all" class="form-check-input">
                            </th>
                            <th class="w10">no.</th>
                            <th class="bo-project-col">
                                <a class="bo-sort-link" href="{{ route('backoffice.project-manages.index', array_merge(request()->query(), ['sortField' => 'ProjectName', 'sort' => ($filters['sortField'] ?? '') === 'ProjectName' && ($filters['sort'] ?? 'desc') === 'asc' ? 'desc' : 'asc'])) }}">
                                    프로젝트명
                                    <span class="bo-sort-arrow">↕</span>
                                </a>
                            </th>
                            <th>URL</th>
                            <th class="w10">담당자</th>
                            <th class="w15">이메일</th>
                            <th class="w10">담당자 연락처</th>
                            <th class="w10">기획</th>
                            <th class="w10">
                                <a class="bo-sort-link" href="{{ route('backoffice.project-manages.index', array_merge(request()->query(), ['sortField' => 'HostingEdate', 'sort' => ($filters['sortField'] ?? '') === 'HostingEdate' && ($filters['sort'] ?? 'desc') === 'asc' ? 'desc' : 'asc'])) }}">
                                    호스팅 만료
                                    <span class="bo-sort-arrow">↕</span>
                                </a>
                            </th>
                            <th class="w10">
                                <a class="bo-sort-link" href="{{ route('backoffice.project-manages.index', array_merge(request()->query(), ['sortField' => 'LastPayDate', 'sort' => ($filters['sortField'] ?? '') === 'LastPayDate' && ($filters['sort'] ?? 'desc') === 'asc' ? 'desc' : 'asc'])) }}">
                                    최근 결제
                                    <span class="bo-sort-arrow">↕</span>
                                </a>
                            </th>
                            <th class="w10">
                                <a class="bo-sort-link" href="{{ route('backoffice.project-manages.index', array_merge(request()->query(), ['sortField' => 'ProjectIngState', 'sort' => ($filters['sortField'] ?? '') === 'ProjectIngState' && ($filters['sort'] ?? 'desc') === 'asc' ? 'desc' : 'asc'])) }}">
                                    진행상태
                                    <span class="bo-sort-arrow">↕</span>
                                </a>
                            </th>
                            <th class="bo-manage-col">관리</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $index => $project)
                            <tr>
                                <td>
                                    <input type="checkbox" name="selected_projects[]" value="{{ $project->idx }}" class="form-check-input bo-row-checkbox">
                                </td>
                                <td>{{ $projects->total() - ($projects->currentPage() - 1) * $projects->perPage() - $index }}</td>
                                <td class="text-start bo-project-col">
                                    <a class="bo-project-title-link" href="{{ route('backoffice.project-manages.edit', array_merge(['idx' => $project->idx], $listQuery ?? [])) }}">
                                        {{ $project->CompanyName ?: '(미지정)' }}
                                    </a>
                                </td>
                                <td class="text-start">{{ $project->HomepageUrl ?: '' }}</td>
                                <td>{{ $project->ManagerName ?? '' }}</td>
                                <td>{{ $project->CompanyEmail ?: '' }}</td>
                                <td>{{ $project->CompanyHp ?: '' }}</td>
                                <td>{{ trim(str_replace(',', '', (string) ($project->InternalPlanningName ?? ''))) }}</td>
                                <td>{{ preg_match('/^\d{4}-\d{2}-\d{2}/', (string) $project->HostingEdate) ? substr((string) $project->HostingEdate, 2, 8) : '' }}</td>
                                <td>{{ $project->LastPayDate ?: '' }}</td>
                                <td>
                                    @php
                                        $state = trim((string) ($project->ProjectIngState ?? ''));
                                        $stateClass = match ($state) {
                                            '계약' => 'bo-status-contract',
                                            '기획' => 'bo-status-plan',
                                            '디자인' => 'bo-status-design',
                                            '퍼블리싱' => 'bo-status-publish',
                                            '개발' => 'bo-status-dev',
                                            '작업완료' => 'bo-status-done',
                                            '수정사항' => 'bo-status-fix',
                                            '유지보수' => 'bo-status-maint',
                                            '보류' => 'bo-status-hold',
                                            '취소' => 'bo-status-cancel',
                                            '광고기획' => 'bo-status-ad',
                                            '호스팅' => 'bo-status-hosting',
                                            default => 'bo-status-default',
                                        };
                                    @endphp
                                    @if($state !== '')
                                        <span class="bo-status-badge {{ $stateClass }}">{{ $state }}</span>
                                    @endif
                                </td>
                                <td class="bo-manage-col">
                                    <div class="board-btn-group bo-action-inline">
                                        <a href="{{ route('backoffice.project-manages.edit', array_merge(['idx' => $project->idx], $listQuery ?? [])) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i> 수정
                                        </a>
                                        <form method="POST" action="{{ route('backoffice.project-manages.destroy', $project->idx) }}" class="bo-inline-form" onsubmit="return confirm('삭제하시겠습니까?');">
                                            @csrf
                                            @method('DELETE')
                                            @foreach($listQuery ?? [] as $rk => $rv)
                                                <input type="hidden" name="return_{{ $rk }}" value="{{ $rv }}">
                                            @endforeach
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i> 삭제
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center">등록된 데이터가 없습니다.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-pagination :paginator="$projects" />
        </div>
    </div>
</div>
@endsection

