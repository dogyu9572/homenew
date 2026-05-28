@extends('backoffice.layouts.app')

@section('title', '프로젝트 상세')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/backoffice/boards.css') }}">
@endsection

@section('content')
@php $listQuery = $listQuery ?? []; @endphp
<div class="board-container">
    <div class="board-page-header">
        <div class="board-page-buttons">
            <a href="{{ route('backoffice.project-manages.edit', array_merge(['idx' => $project->idx], $listQuery)) }}" class="btn btn-primary">
                <i class="fas fa-pen"></i> 수정
            </a>
            <form method="POST" action="{{ route('backoffice.project-manages.destroy', $project->idx) }}" class="bo-inline-form" onsubmit="return confirm('삭제하시겠습니까?');">
                @csrf
                @method('DELETE')
                @foreach($listQuery as $rk => $rv)
                    <input type="hidden" name="return_{{ $rk }}" value="{{ $rv }}">
                @endforeach
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> 삭제
                </button>
            </form>
            <a href="{{ route('backoffice.project-manages.index', $listQuery) }}" class="btn btn-secondary">
                <i class="fas fa-list"></i> 목록
            </a>
        </div>
    </div>

    <div class="board-card">
        <div class="board-card-body">
            <table class="board-table">
                <tbody>
                    <tr>
                        <th class="w15">프로젝트명</th>
                        <td>{{ $project->ProjectName ?? $project->CompanyName ?? '-' }}</td>
                        <th class="w15">사이트분류</th>
                        <td>{{ $project->SiteType ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>홈페이지 주소</th>
                        <td>{{ $project->DomainUrl ?? $project->HomepageUrl ?? '-' }}</td>
                        <th>관리자 주소</th>
                        <td>{{ $project->DomainAdminUrl ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>서브도메인</th>
                        <td>{{ $project->DomainSubUrl ?? '-' }}</td>
                        <th>테스트 주소</th>
                        <td>{{ $project->TestUrl ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>FTP 주소</th>
                        <td>{{ $project->FtpUrl ?? '-' }}</td>
                        <th>FTP 포트</th>
                        <td>{{ $project->FtpPort ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>DB 호스트</th>
                        <td>{{ $project->DbHost ?? '-' }}</td>
                        <th>DB 이름</th>
                        <td>{{ $project->DbName ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>업체 담당자</th>
                        <td>{{ $project->CompanyPerson ?? '-' }}</td>
                        <th>내부 담당자</th>
                        <td class="text-start">
                            <div>영업: {{ $project->IntraBusiness ?: '-' }}</div>
                            <div>기획: {{ $project->IntraManager ?: '-' }}</div>
                            <div>디자인: {{ $project->IntraDesiner ?: '-' }}</div>
                            <div>퍼블: {{ $project->IntraPublisher ?: '-' }}</div>
                            <div>개발: {{ $project->IntraProgramer ?: '-' }}</div>
                        </td>
                    </tr>
                    <tr>
                        <th>이메일</th>
                        <td>{{ $project->CpEmail ?? $project->CompanyEmail ?? '-' }}</td>
                        <th>휴대폰/전화</th>
                        <td>{{ $project->CpPhone ?? $project->CompanyHp ?? '-' }} / {{ $project->CpTel ?? $project->CompanyPhone ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>프로젝트 구분</th>
                        <td>{{ $project->ProjectGubun ?? $project->CategoryBusiness ?? '-' }}</td>
                        <th>진행상태</th>
                        <td>{{ $project->ProjectIngState ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>프로젝트 기간</th>
                        <td>{{ $project->ProjectSdate ?? '-' }} ~ {{ $project->ProjectEdate ?? '-' }}</td>
                        <th>호스팅 기간</th>
                        <td>{{ $project->HostingSdate ?? '-' }} ~ {{ $project->HostingEdate ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>회사명</th>
                        <td>{{ $project->CompanyName ?? '-' }}</td>
                        <th>사업자번호</th>
                        <td>{{ $project->LicensessNumber ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>주소</th>
                        <td colspan="3">{{ $project->CompanyAddr ?? $project->LicensessAddr ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>메모</th>
                        <td colspan="3">{!! nl2br(e($project->CompanyMemo ?? $project->Domain_Etc ?? '')) !!}</td>
                    </tr>
                    <tr>
                        <th>특이메모</th>
                        <td colspan="3">{!! nl2br(e($project->CompanySpecialMemo ?? $project->SpeicalMemo ?? '')) !!}</td>
                    </tr>
                    <tr>
                        <th>프로젝트 메모</th>
                        <td colspan="3">{!! nl2br(e($project->ProjectMemo ?? '')) !!}</td>
                    </tr>
                </tbody>
            </table>

            <div class="mt-4">
                <h5>첨부파일</h5>
                <table class="board-table">
                    <thead>
                        <tr>
                            <th class="w10">번호</th>
                            <th>파일명</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attachmentItems as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="text-start">
                                    @if(!empty($item->stored_path))
                                        <a class="board-attachment-link board-attachment-name" href="{{ route('backoffice.project-manages.attachments.download', [$project->idx, $item->row_idx]) }}">{{ $item->display_name }}</a>
                                    @else
                                        <span class="board-attachment-name">{{ $item->display_name }}</span>
                                    @endif
                                    @if(!empty($item->size_bytes))
                                        <span class="board-attachment-size">({{ number_format($item->size_bytes / 1024 / 1024, 2) }}MB)</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center">첨부파일이 없습니다.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <h5>결제 정보</h5>
                <table class="board-table">
                    <thead>
                        <tr>
                            <th class="w10">번호</th>
                            <th class="w15">결제일</th>
                            <th class="w15">금액</th>
                            <th class="w10">담당자</th>
                            <th>메모</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($moneyHistories as $index => $money)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $money->PaymentDate ?? '-' }}</td>
                                <td>{{ isset($money->Money) ? number_format((float) $money->Money) : '-' }}</td>
                                <td>{{ $money->Consultant ?? '-' }}</td>
                                <td>{!! nl2br(e($money->Memo ?? '')) !!}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">결제 이력이 없습니다.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

