<!DOCTYPE html>
<html lang="ko">
<head>
    @include('backoffice.layouts.header')
    <link rel="stylesheet" href="{{ asset('css/backoffice/session-timer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/backoffice/boards.css') }}">
    @yield('styles')
</head>
<body class="backoffice">
    <!-- 모바일에서 사이드바 활성화 시 표시되는 백드롭 -->
    <div class="backdrop" id="backdrop"></div>

    <div class="dashboard-container">
        @include('backoffice.layouts.sidebar')

        <div class="content">
            <div class="header">
                <!-- 모바일에서만 보이는 사이드바 토글 버튼 -->
                <a href="#" id="sidebarToggle" class="sidebar-toggle">
                    <div class="hamburger">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </a>
                <h2>@yield('title', '백오피스')</h2>

                <div id="backofficeAttendanceConfig" class="sound_only" hidden
                    data-quick-store-url="{{ route('backoffice.attendance.quick-store') }}"
                ></div>

                <!-- 유저 드롭다운과 세션 타이머를 함께 배치 -->
                <div class="user-dropdown">
					<img src="/images/img_human.svg" alt="">
                    <span class="name">{{ auth()->user()->name ?? '관리자' }}님</span>
                    <div class="header-attendance-wrap" role="group" aria-label="출퇴근 빠른 등록">
                        <button type="button" class="btn btn-attendance-quick btn-success" id="btnAttendanceClockIn">출근</button>
                        <button type="button" class="btn btn-attendance-quick btn-outline-secondary" id="btnAttendanceClockOut">퇴근</button>
                    </div>
					<span class="session-timer" id="sessionTimer">
						<i class="fas fa-clock"></i>
						<span class="session-timer-text">
							<span id="sessionTimeLeft">--:--</span>
						</span>
	                    <button class="session-extend-btn" id="sessionExtendBtn" title="120분 연장">연장</button>
					</span>
                    <div class="dropdown-content">
                        <a href="{{ route('backoffice.admins.edit', auth()->user()->id) }}">정보수정</a>
                        <a href="{{ url('/backoffice/logout') }}">로그아웃</a>
                    </div>
                </div>
            </div>
            <div class="main-content">
                @yield('content')
            </div>
        </div>
    </div>

    <div id="attendanceQuickOverlay" class="attendance-quick-overlay" hidden>
        <div class="attendance-quick-panel board-card" role="dialog" aria-modal="true" aria-labelledby="attendanceQuickTitle">
            <div class="board-card-body">
                <h3 id="attendanceQuickTitle" class="attendance-quick-modal-title">출퇴근</h3>
                <p class="attendance-quick-hint" id="attendanceQuickHint"></p>

                @auth
                <div class="board-template-info attendance-quick-user" aria-label="등록자 정보">
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
                            <span class="template-info-value"><code>{{ request()->ip() }}</code> (저장 시 동일하게 기록)</span>
                        </div>
                    </div>
                </div>
                @endauth

                <fieldset class="attendance-quick-fieldset">
                    <legend class="sound_only">근무지 선택</legend>
                    <span class="attendance-quick-field-label">근무지</span>
                    <div class="board-checkbox-group attendance-quick-workplace">
                        <label class="checkbox-label">
                            <input type="radio" name="attendance_quick_workplace" value="remote" checked>
                            <span>재택</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="radio" name="attendance_quick_workplace" value="office">
                            <span>사무실</span>
                        </label>
                    </div>
                </fieldset>
                <div class="attendance-quick-actions">
                    <button type="button" class="btn btn-secondary btn-sm" id="attendanceQuickCancel">취소</button>
                    <button type="button" class="btn btn-primary btn-sm" id="attendanceQuickSubmit">확인</button>
                </div>
            </div>
        </div>
    </div>

    @include('backoffice.layouts.footer')

    <!-- 세션 타이머 구성 및 로그아웃 URL -->
    <script>
        // 전역 변수로 설정
        window.sessionConfig = {
            lifetime: {{ config('session.lifetime', 120) }} // 세션 타임아웃(분 단위)
        };
        window.logoutUrl = "{{ url('/backoffice/logout') }}"; // 로그아웃 URL
        
        // 세션 리셋 플래그 확인 및 localStorage 초기화
        @if(session('session_reset'))
            localStorage.removeItem('backoffice_login_time');
            @php
                session()->forget('session_reset');
            @endphp
        @endif
        
        // 세션 연장 플래그 확인 및 localStorage 동기화
        @if(session('session_extended'))
            const serverLoginTime = {{ session('login_time', 0) }} * 1000;
            if (serverLoginTime > 0) {
                localStorage.setItem('backoffice_login_time', serverLoginTime);
            }
            @php
                session()->forget('session_extended');
            @endphp
        @endif
    </script>
    <!-- SortableJS 라이브러리 -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    
    <script src="{{ asset('js/backoffice/button-utils.js') }}"></script>
    <script src="{{ asset('js/backoffice/session-timer.js') }}"></script>
    <script src="{{ asset('js/backoffice/attendance-quick.js') }}"></script>
    <script src="{{ asset('js/backoffice/backoffice.js') }}"></script>
    <script src="{{ asset('js/common/app.js') }}"></script>

    @yield('scripts')
    @stack('scripts')
</body>
</html>
