@extends('backoffice.layouts.app')

@section('title', '프로젝트 생성')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/backoffice/boards.css') }}">
@endsection

@section('content')
    <div id="provision-alert" hidden data-success="{{ session('success') }}" data-error="{{ session('error') }}"></div>

    <div class="board-container" id="project-provision-show" data-status="{{ $provision->status }}">
        <div class="board-header">
            <a href="{{ route('backoffice.project-provisions.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> 목록으로
            </a>
        </div>

        <div class="board-card">
            <div class="board-card-body">
                <div class="board-form-group">
                    <label class="board-form-label">고객사명</label>
                    <input type="text" class="board-form-control" value="{{ $provision->customer_name }}" readonly>
                </div>

                <div class="board-form-group">
                    <label class="board-form-label">프로젝트명</label>
                    <input type="text" class="board-form-control" value="{{ $provision->project_name }}" readonly>
                </div>

                <div class="board-form-group">
                    <label class="board-form-label">종류</label>
                    <input type="text" class="board-form-control" value="{{ $provision->stackLabel() }}" readonly>
                </div>

                <div class="board-form-group">
                    <label class="board-form-label">상태</label>
                    <input type="text" class="board-form-control" value="{{ $provision->statusLabel() }}" readonly>
                    @if(in_array($provision->status, ['queued', 'running'], true))
                        <small class="board-form-text">진행 중이면 이 화면이 자동으로 새로고침됩니다. 대기가 계속되면 서버 큐 워커를 확인하세요.</small>
                    @endif
                </div>

                <div class="board-form-group">
                    <label class="board-form-label">단계</label>
                    <input type="text" class="board-form-control" value="{{ $provision->stepLabel() }}" readonly>
                </div>

                <div class="board-form-group">
                    <label class="board-form-label">FTP 아이디</label>
                    <input type="text" class="board-form-control" value="{{ $provision->ftp_username }}" readonly>
                    @if($provision->stack !== \App\Models\ProjectProvision::STACK_JAVA)
                        <small class="board-form-text">퍼블 사이트 계정은 pub{{ $provision->ftp_username }} 입니다.</small>
                    @endif
                </div>

                <div class="board-form-group">
                    <label class="board-form-label">FTP 비밀번호</label>
                    <input type="text" class="board-form-control" value="{{ $provision->ftp_password }}" readonly>
                </div>

                <div class="board-form-group">
                    <label class="board-form-label">DB 아이디</label>
                    <input type="text" class="board-form-control" value="{{ $provision->db_username }}" readonly>
                </div>

                <div class="board-form-group">
                    <label class="board-form-label">DB 비밀번호</label>
                    <input type="text" class="board-form-control" value="{{ $provision->db_password }}" readonly>
                </div>

                <div class="board-form-group">
                    <label class="board-form-label">DB 이름</label>
                    <input type="text" class="board-form-control" value="{{ $provision->db_database }}" readonly>
                </div>

                <div class="board-form-group">
                    <label class="board-form-label">접속 주소</label>
                    @if($provision->site_url)
                        <div class="board-form-text">
                            <a href="{{ $provision->site_url }}" target="_blank" rel="noopener noreferrer">{{ $provision->site_url }}</a>
                        </div>
                    @else
                        <input type="text" class="board-form-control" value="-" readonly>
                    @endif
                </div>

                <div class="board-form-group">
                    <label class="board-form-label">{{ $provision->stack === \App\Models\ProjectProvision::STACK_JAVA ? '관리자 주소' : '퍼블 주소' }}</label>
                    @if($provision->pub_site_url)
                        <div class="board-form-text">
                            <a href="{{ $provision->pub_site_url }}" target="_blank" rel="noopener noreferrer">{{ $provision->pub_site_url }}</a>
                        </div>
                    @else
                        <input type="text" class="board-form-control" value="-" readonly>
                    @endif
                </div>

                <div class="board-form-group">
                    <label class="board-form-label">깃 저장소</label>
                    @if($provision->git_url)
                        <div class="board-form-text">
                            <a href="{{ $provision->git_url }}" target="_blank" rel="noopener noreferrer">{{ $provision->git_url }}</a>
                        </div>
                    @else
                        <input type="text" class="board-form-control" value="-" readonly>
                    @endif
                </div>

                @if ($errors->any())
                    <div class="board-alert board-alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('success'))
                    <div class="board-alert board-alert-success">{{ session('success') }}</div>
                @endif

                @if (session('error'))
                    <div class="board-alert board-alert-danger">{{ session('error') }}</div>
                @endif

                @if($provision->status === 'success')
                    <form action="{{ route('backoffice.project-provisions.ia-reapply', $provision) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="board-form-group">
                            <label for="ia_file" class="board-form-label">IA 재적용</label>
                            <input type="file" class="board-form-control" id="ia_file" name="ia_file" accept=".xlsx" required>
                            @if($provision->stack === \App\Models\ProjectProvision::STACK_JAVA)
                                <small class="board-form-text">엑셀을 올리고 저장하면 자바 관리자의 IA 메뉴와 게시판을 지우고 다시 넣습니다. 공통모듈 기본 메뉴는 그대로 둡니다.</small>
                            @else
                                <small class="board-form-text">엑셀을 올리고 저장하면 퍼블 관리자 화면만 지우고 기존 모듈을 다시 받은 뒤, IA를 다시 넣습니다. 사용자 화면과 개발 사이트는 그대로 둡니다.</small>
                            @endif
                        </div>
                        <div class="board-form-group">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> 저장
                            </button>
                        </div>
                    </form>
                @endif

                @if($provision->ia_apply_note)
                    <div class="board-form-group">
                        <label for="ia-apply-note" class="board-form-label">IA 적용 메모</label>
                        <textarea id="ia-apply-note" class="board-form-control board-form-textarea" rows="8" readonly>{{ $provision->ia_apply_note }}</textarea>
                    </div>
                @endif

                <div class="board-form-group">
                    <label for="provision-log" class="board-form-label">단계 로그</label>
                    <textarea id="provision-log" class="board-form-control board-form-textarea" rows="18" readonly>{{ $provision->log }}</textarea>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @if(in_array($provision->status, ['queued', 'running'], true))
        <script src="{{ asset('js/backoffice/project-provisions.js') }}"></script>
    @endif
@endsection
