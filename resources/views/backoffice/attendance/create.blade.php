@extends('backoffice.layouts.app')

@section('title', '출퇴근 등록')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/backoffice/portfolio.css') }}">
@endsection

@section('content')
<div class="board-container">
    <div class="board-header">
        <a href="{{ route('backoffice.attendance.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> 목록으로
        </a>
    </div>

    <div class="board-card">
        <div class="board-card-body">
            @if ($errors->any())
                <div class="alert alert-danger board-hidden-alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('backoffice.attendance.store') }}">
                @csrf

                <div class="board-template-info attendance-create-user mb-4" aria-label="등록자 정보">
                    <div class="template-info-header">
                        <h6><i class="fas fa-user"></i> 내 정보</h6>
                    </div>
                    <div class="template-info-content">
                        <div class="template-info-item">
                            <span class="template-info-label">이름</span>
                            <span class="template-info-value">{{ auth()->user()->name }}</span>
                        </div>
                        <div class="template-info-item">
                            <span class="template-info-label">소속</span>
                            <span class="template-info-value">{{ auth()->user()->department ?: '—' }}</span>
                        </div>
                        <div class="template-info-item">
                            <span class="template-info-label">직급</span>
                            <span class="template-info-value">{{ auth()->user()->position ?: '—' }}</span>
                        </div>
                        <div class="template-info-item">
                            <span class="template-info-label">접속 IP</span>
                            <span class="template-info-value"><code>{{ request()->ip() }}</code> (저장 시 기록)</span>
                        </div>
                    </div>
                </div>

                <div class="member-form-section">
                    <h3 class="member-section-title">출퇴근</h3>
                    <p class="sub-label">기본은 현재 시각 저장이며, 누락 보정이 필요하면 기록일시/사유를 함께 입력하세요.</p>

                    <div class="member-form-list">
                        <div class="member-form-row">
                            <label class="member-form-label">구분 <span class="required">*</span></label>
                            <div class="member-form-field">
                                <div class="board-checkbox-group">
                                    <label class="checkbox-label">
                                        <input type="radio" name="kind" value="{{ \App\Models\StaffAttendanceRecord::KIND_CLOCK_IN }}" @checked(old('kind', \App\Models\StaffAttendanceRecord::KIND_CLOCK_IN) === \App\Models\StaffAttendanceRecord::KIND_CLOCK_IN) required>
                                        <span>출근</span>
                                    </label>
                                    <label class="checkbox-label">
                                        <input type="radio" name="kind" value="{{ \App\Models\StaffAttendanceRecord::KIND_CLOCK_OUT }}" @checked(old('kind', \App\Models\StaffAttendanceRecord::KIND_CLOCK_IN) === \App\Models\StaffAttendanceRecord::KIND_CLOCK_OUT)>
                                        <span>퇴근</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="member-form-row">
                            <label class="member-form-label">근무지 <span class="required">*</span></label>
                            <div class="member-form-field">
                                <div class="board-checkbox-group">
                                    <label class="checkbox-label">
                                        <input type="radio" name="workplace" value="{{ \App\Models\StaffAttendanceRecord::WORKPLACE_REMOTE }}" @checked(old('workplace', \App\Models\StaffAttendanceRecord::WORKPLACE_REMOTE) === \App\Models\StaffAttendanceRecord::WORKPLACE_REMOTE) required>
                                        <span>재택</span>
                                    </label>
                                    <label class="checkbox-label">
                                        <input type="radio" name="workplace" value="{{ \App\Models\StaffAttendanceRecord::WORKPLACE_OFFICE }}" @checked(old('workplace', \App\Models\StaffAttendanceRecord::WORKPLACE_REMOTE) === \App\Models\StaffAttendanceRecord::WORKPLACE_OFFICE)>
                                        <span>사무실</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="member-form-row">
                            <label class="member-form-label" for="recorded_at">기록일시</label>
                            <div class="member-form-field">
                                <input
                                    type="datetime-local"
                                    id="recorded_at"
                                    name="recorded_at"
                                    class="board-form-control"
                                    value="{{ old('recorded_at') }}"
                                >
                                <p class="sub-label" style="margin-top:6px;">비우면 현재 시각으로 저장됩니다.</p>
                            </div>
                        </div>

                        <div class="member-form-row">
                            <label class="member-form-label" for="adjustment_reason">보정 사유</label>
                            <div class="member-form-field">
                                <textarea
                                    id="adjustment_reason"
                                    name="adjustment_reason"
                                    rows="3"
                                    class="board-form-control"                                    
                                >{{ old('adjustment_reason') }}</textarea>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="board-form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> 저장
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
