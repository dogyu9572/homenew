@extends('backoffice.layouts.app')

@section('title', '전자결재 메인')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/backoffice/boards.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/approval.css') }}">
@endsection

@section('content')
<div class="board-container">
    <div class="board-page-header">
        <div class="board-page-buttons">
            <a href="{{ route('backoffice.approvals.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> 결재문서 등록
            </a>
        </div>
    </div>

    <div class="approval-summary-grid">
        @foreach($boxSummaries as $summary)
            <div class="board-card approval-summary-card">
                <div class="board-card-body">
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

    <div class="board-card">
        <div class="board-card-body">
            <div class="approval-section-header">
                <h5>개인 문서함 &gt; 상신문서</h5>
                <a href="{{ route('backoffice.approvals.personal', ['tab' => 'submitted']) }}" class="btn btn-outline-secondary btn-sm">더보기</a>
            </div>
            <div class="table-responsive">
                <table class="board-table">
                    <thead>
                        <tr>
                            <th>문서번호</th>
                            <th>기안일자</th>
                            <th>문서명</th>
                            <th>상태</th>
                            <th>의견</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentPersonalDocuments as $doc)
                            <tr>
                                <td>{{ $doc['doc_no'] }}</td>
                                <td>{{ $doc['drafted_at'] }}</td>
                                <td class="text-start">{{ $doc['title'] }}</td>
                                <td>{{ $doc['status'] }}</td>
                                <td>{{ $doc['opinion_count'] ?? 0 }}개</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">해당 문서 정보가 없습니다.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="board-card">
        <div class="board-card-body">
            <div class="approval-section-header">
                <h5>결재할 문서함 &gt; 미결재 문서</h5>
                <a href="{{ route('backoffice.approvals.pending', ['tab' => 'pending']) }}" class="btn btn-outline-secondary btn-sm">더보기</a>
            </div>
            <div class="table-responsive">
                <table class="board-table">
                    <thead>
                        <tr>
                            <th>문서번호</th>
                            <th>기안일자</th>
                            <th>문서명</th>
                            <th>기안자</th>
                            <th>나의결재</th>
                            <th>상태</th>
                            <th>다음결재자</th>
                            <th>의견</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentPendingDocuments as $doc)
                            <tr>
                                <td>{{ $doc['doc_no'] }}</td>
                                <td>{{ $doc['drafted_at'] }}</td>
                                <td class="text-start">{{ $doc['title'] }}</td>
                                <td>{{ $doc['writer'] }}</td>
                                <td>{{ $doc['my_status'] }}</td>
                                <td>{{ $doc['status'] }}</td>
                                <td>{{ $doc['next_approver'] }}</td>
                                <td>{{ $doc['opinion_count'] ?? 0 }}개</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">해당 문서 정보가 없습니다.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="board-card">
        <div class="board-card-body">
            <div class="approval-section-header">
                <h5>협조 문서함 &gt; 미결재 문서</h5>
                <a href="{{ route('backoffice.approvals.cooperation', ['tab' => 'pending']) }}" class="btn btn-outline-secondary btn-sm">더보기</a>
            </div>
            <div class="table-responsive">
                <table class="board-table">
                    <thead>
                        <tr>
                            <th>문서번호</th>
                            <th>기안일자</th>
                            <th>문서명</th>
                            <th>기안자</th>
                            <th>나의결재</th>
                            <th>상태</th>
                            <th>다음결재자</th>
                            <th>의견</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentCooperationDocuments as $doc)
                            <tr>
                                <td>{{ $doc['doc_no'] }}</td>
                                <td>{{ $doc['drafted_at'] }}</td>
                                <td class="text-start">{{ $doc['title'] }}</td>
                                <td>{{ $doc['writer'] }}</td>
                                <td>{{ $doc['my_status'] }}</td>
                                <td>{{ $doc['status'] }}</td>
                                <td>{{ $doc['next_approver'] }}</td>
                                <td>{{ $doc['opinion_count'] ?? 0 }}개</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">해당 문서 정보가 없습니다.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="board-card">
        <div class="board-card-body">
            <h5 class="approval-guide-title">전자결재 문서함 설명</h5>
            <div class="table-responsive approval-guide-table-wrap">
                <table class="board-table approval-guide-table">
                    <thead>
                        <tr>
                            <th class="w20">문서함</th>
                            <th class="w20">상태</th>
                            <th>설명</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td rowspan="2" class="approval-guide-box">결재할 문서함</td>
                            <td class="approval-guide-status">미결재 문서</td>
                            <td class="text-start approval-guide-desc">내가 결재할 문서 중 아직 결재하지 않은 문서입니다.</td>
                        </tr>
                        <tr>
                            <td class="approval-guide-status">결재완료 문서</td>
                            <td class="text-start approval-guide-desc">내가 결재할 문서 중 결재를 완료한 문서입니다.</td>
                        </tr>
                        <tr>
                            <td rowspan="3" class="approval-guide-box">개인 문서함</td>
                            <td class="approval-guide-status">상신문서</td>
                            <td class="text-start approval-guide-desc">결재를 올린 문서 중 아직 진행중인 문서입니다. (결재/보류/기각/결재완료가 안된 문서)</td>
                        </tr>
                        <tr>
                            <td class="approval-guide-status">반려문서</td>
                            <td class="text-start approval-guide-desc">상신한 문서 중에 결재자가 보류/기각한 문서입니다.</td>
                        </tr>
                        <tr>
                            <td class="approval-guide-status">결재완료</td>
                            <td class="text-start approval-guide-desc">상신한 문서 중에서 결재자가 결재/전결한 문서입니다.</td>
                        </tr>
                        <tr>
                            <td rowspan="2" class="approval-guide-box">협조 문서함</td>
                            <td class="approval-guide-status">미결재 문서</td>
                            <td class="text-start approval-guide-desc">본인이 협조 결재라인에 들어가 있으며, 아직 협조라인 결재를 하지 않은 상태입니다.</td>
                        </tr>
                        <tr>
                            <td class="approval-guide-status">결재완료 문서</td>
                            <td class="text-start approval-guide-desc">본인이 협조 결재라인에 들어가 있으며, 협조라인 결재를 완료한 상태입니다.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

