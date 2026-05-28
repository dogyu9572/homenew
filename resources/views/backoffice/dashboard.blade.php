@extends('backoffice.layouts.app')

@section('title', $pageTitle ?? '')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/backoffice/dashboard.css') }}">
@endsection

@section('content')
<div class="dashboard-content">
    <!-- 대시보드 헤더 -->
    <div class="dashboard-header">
        <div class="dashboard-welcome">
            <p>{{ auth()->user()->name ?? '관리자' }}님, 환영합니다!</p>
            <p>{{ date('Y년 m월 d일') }} 백오피스 대시보드 현황입니다.</p>
        </div>
        <div class="dashboard-actions">
            <a href="{{ route('backoffice.setting.index') }}" class="dashboard-action-btn">
                <i class="fas fa-cog"></i> 환경설정
            </a>
            <a href="{{ url('/') }}" target="_blank" class="dashboard-action-btn">
                <i class="fas fa-home"></i> 사이트 방문
            </a>
        </div>
    </div>

    <!-- 통계 요약 -->
    <div class="stats-row">
        <div class="stat-card stat-boards">
            <div class="stat-icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div class="stat-info">
                <h3>개인 문서함 &gt; 상신문서</h3>
                <p class="stat-number">{{ number_format((int) ($approvalStats['personal_submitted'] ?? 0)) }}</p>
            </div>
        </div>

        <div class="stat-card stat-posts">
            <div class="stat-icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="stat-info">
                <h3>결재할 문서함 &gt; 미결재 문서</h3>
                <p class="stat-number">{{ number_format((int) ($approvalStats['pending_approval'] ?? 0)) }}</p>
            </div>
        </div>

        <div class="stat-card stat-banners">
            <div class="stat-icon">
                <i class="fas fa-handshake"></i>
            </div>
            <div class="stat-info">
                <h3>협조 문서함 &gt; 미결재 문서</h3>
                <p class="stat-number">{{ number_format((int) ($approvalStats['pending_cooperation'] ?? 0)) }}</p>
            </div>
        </div>

        <div class="stat-card stat-notices">
            <div class="stat-icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-info">
                <h3>{{ now()->format('Y') }}년 사용 연차(일)</h3>
                <p class="stat-number">{{ $usedLeaveDaysThisYearDisplay ?? '0' }}</p>
            </div>
        </div>
    </div>

    <div class="approval-summary-grid dashboard-approval-summary-grid">
        @foreach($boxSummaries ?? [] as $summary)
            <div class="grid-item approval-summary-card">
                <div class="grid-item-body">
                    <h5 class="approval-summary-title">
                        <a href="{{ $summary['route'] }}">{{ $summary['title'] }}</a>
                    </h5>
                    <div class="approval-summary-list">
                        @foreach($summary['items'] as $item)
                            <div class="approval-summary-row">
                                <span class="approval-summary-label">{{ $item['label'] }}</span>
                                <strong class="approval-summary-count">{{ number_format((int) $item['count']) }}건</strong>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- 데이터 그리드 -->
    <div class="dashboard-grid">
        <!-- 공지사항 미리보기 -->
        <div class="grid-item grid-col-12">
            <div class="grid-item-header">
                <h3>공지사항 미리보기</h3>
                <a href="{{ route('backoffice.board-posts.index', ['slug' => 'notices']) }}" class="more-btn">
                    <i class="fas fa-arrow-right"></i> 더보기
                </a>
            </div>
            <div class="grid-item-body">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>제목</th>
                            <th>등록일</th>
                            <th>구분</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($noticePreviewPosts ?? [] as $noticePost)
                            <tr>
                                <td>
                                    <a href="{{ route('backoffice.board-posts.index', ['slug' => 'notices']) }}"
                                       class="text-decoration-none text-dark fw-medium">
                                        {{ $noticePost->title ?? '-' }}
                                    </a>
                                </td>
                                <td>{{ !empty($noticePost->created_at) ? \Carbon\Carbon::parse($noticePost->created_at)->format('Y-m-d') : '-' }}</td>
                                <td>
                                    <span class="table-badge badge-{{ !empty($noticePost->is_notice) ? 'warning' : 'secondary' }}">
                                        {{ !empty($noticePost->is_notice) ? '공지' : '일반' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">등록된 공지사항이 없습니다.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- 접속 통계 그래프 -->
    <div class="stats-chart-section">
        <div class="grid-item grid-col-12">
            <div class="grid-item-header">
                <h3>방문객 통계</h3>
                <div class="chart-controls">
                    <button class="chart-type-btn active" data-type="daily">일별</button>
                    <button class="chart-type-btn" data-type="monthly">월별</button>
                </div>
            </div>
            <div class="grid-item-body">
                <div class="visitor-summary">
                    <div class="visitor-stat">
                        <span class="visitor-label">오늘 방문객</span>
                        <span class="visitor-number">{{ $visitorStats['today_visitors'] ?? 0 }}</span>
                    </div>
                    <div class="visitor-stat">
                        <span class="visitor-label">총 방문객</span>
                        <span class="visitor-number">{{ number_format($visitorStats['total_visitors'] ?? 0) }}</span>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="visitorChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/backoffice/dashboard.js') }}"></script>
@endsection
