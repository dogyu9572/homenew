@extends('backoffice.layouts.app')

@section('title', '출퇴근 관리')

@section('content')
@if (session('success'))
    <div class="alert alert-success board-hidden-alert">{{ session('success') }}</div>
@endif

<div class="board-container attendance-index-page">
    <div class="board-page-header">
        <div class="board-page-buttons">
            <a href="{{ route('backoffice.attendance.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> 등록하기
            </a>
        </div>
    </div>

    <div class="board-card">
        <div class="board-card-body">
            <div class="board-list-header">
                <div class="list-info">
                    <span class="list-count">Total : {{ $records->total() }}</span>
                </div>
                <div class="list-controls">
                    <form method="GET" action="{{ route('backoffice.attendance.index') }}" class="per-page-form">
                        @foreach(request()->except('per_page') as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach
                        <label for="per_page" class="per-page-label">표시 개수:</label>
                        <select id="per_page" name="per_page" class="per-page-select" onchange="this.form.submit()">
                            <option value="10" @selected((int) request('per_page', 20) === 10)>10개</option>
                            <option value="20" @selected((int) request('per_page', 20) === 20)>20개</option>
                            <option value="50" @selected((int) request('per_page') === 50)>50개</option>
                            <option value="100" @selected((int) request('per_page') === 100)>100개</option>
                        </select>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="board-table">
                    <thead>
                        <tr>
                            <th>NO</th>
                            <th>이름</th>
                            <th>소속</th>
                            <th>직급</th>
                            <th class="attendance-col-kind">구분</th>
                            <th>근무지</th>
                            <th>IP</th>
                            <th>일시</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $index => $row)
                            <tr>
                                <td>{{ $records->total() - ($records->currentPage() - 1) * $records->perPage() - $index }}</td>
                                <td>{{ $row->user->name ?? '-' }}</td>
                                <td>{{ $row->user->department ?? '-' }}</td>
                                <td>{{ $row->user->position ?? '-' }}</td>
                                <td class="attendance-kind-cell">
                                    @if($row->kind === \App\Models\StaffAttendanceRecord::KIND_CLOCK_IN)
                                        <span class="attendance-kind-badge is-clock-in" title="출근">{{ \App\Models\StaffAttendanceRecord::kindLabel($row->kind) }}</span>
                                    @else
                                        <span class="attendance-kind-badge is-clock-out" title="퇴근">{{ \App\Models\StaffAttendanceRecord::kindLabel($row->kind) }}</span>
                                    @endif
                                </td>
                                <td>{{ \App\Models\StaffAttendanceRecord::workplaceLabel($row->workplace) }}</td>
                                <td>{{ $row->ip_address ?? '-' }}</td>
                                <td>{{ $row->recorded_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">데이터가 없습니다.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-pagination :paginator="$records" />
        </div>
    </div>
</div>
@endsection
