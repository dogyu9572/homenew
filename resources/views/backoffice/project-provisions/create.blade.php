@extends('backoffice.layouts.app')

@section('title', '프로젝트 생성')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/backoffice/boards.css') }}">
@endsection

@section('content')
<div class="board-container">
    <div class="board-header">
        <a href="{{ route('backoffice.project-provisions.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> 목록으로
        </a>
    </div>

    <div class="board-card">
        <div class="board-card-body">
            @if ($errors->any())
                <div class="board-alert board-alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('backoffice.project-provisions.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="board-form-group">
                    <label for="customer_name" class="board-form-label">고객사명 <span class="required">*</span></label>
                    <input type="text" class="board-form-control" id="customer_name" name="customer_name" value="{{ old('customer_name') }}" required maxlength="100">
                </div>

                <div class="board-form-group">
                    <label for="project_name" class="board-form-label">프로젝트명 <span class="required">*</span></label>
                    <input type="text" class="board-form-control" id="project_name" name="project_name" value="{{ old('project_name') }}" required maxlength="31" placeholder="yoonsunginc">
                    <small class="board-form-text" id="project-name-help">영문 소문자로 시작하며, 소문자·숫자·하이픈만 사용할 수 있습니다. 주소는 https://프로젝트명.hk-test.co.kr, 퍼블 주소는 https://pub프로젝트명.hk-test.co.kr 입니다.</small>
                </div>

                <div class="board-form-group">
                    <label for="ftp_username" class="board-form-label">SFTP 아이디 <span class="required">*</span></label>
                    <input type="text" class="board-form-control" id="ftp_username" name="ftp_username" value="{{ old('ftp_username') }}" required maxlength="29">
                    <small class="board-form-text" id="ftp-username-help">퍼블 사이트는 이 아이디 앞에 pub를 붙인 계정으로 관리합니다.</small>
                </div>

                <div class="board-form-group">
                    <label for="ftp_password" class="board-form-label">SFTP 비밀번호 <span class="required">*</span></label>
                    <input type="password" class="board-form-control" id="ftp_password" name="ftp_password" required minlength="8" maxlength="128" autocomplete="new-password">
                </div>

                <div class="board-form-group">
                    <label for="db_username" class="board-form-label">DB 아이디 <span class="required">*</span></label>
                    <input type="text" class="board-form-control" id="db_username" name="db_username" value="{{ old('db_username') }}" required maxlength="31">
                </div>

                <div class="board-form-group">
                    <label for="db_password" class="board-form-label">DB 비밀번호 <span class="required">*</span></label>
                    <input type="password" class="board-form-control" id="db_password" name="db_password" required minlength="8" maxlength="128" autocomplete="new-password">
                </div>

                <div class="board-form-group">
                    <label for="ia_file" class="board-form-label">관리자 IA</label>
                    <input type="file" class="board-form-control" id="ia_file" name="ia_file" accept=".xlsx">
                    <small class="board-form-text" id="ia-file-help">엑셀(.xlsx)만 올릴 수 있습니다. 서버 생성이 끝나면 이 IA의 메뉴와 필드를 퍼블 관리자에 넣습니다.</small>
                </div>

                <div class="board-form-group">
                    <label for="stack" class="board-form-label">프로젝트 종류 <span class="required">*</span></label>
                    <select class="board-form-control" id="stack" name="stack" required>
                        <option value="laravel" @selected(old('stack', 'laravel') === 'laravel')>라라벨</option>
                        <option value="java" @selected(old('stack') === 'java')>자바</option>
                    </select>
                    <small class="board-form-text" id="stack-help">자바를 고르면 공통모듈, 깃 저장소, 푸시 자동 배포, 관리자 IA가 함께 만들어집니다.</small>
                </div>

                <div class="board-form-group">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> 저장
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    (function () {
        var stack = document.getElementById('stack');
        var projectHelp = document.getElementById('project-name-help');
        var ftpHelp = document.getElementById('ftp-username-help');
        var iaHelp = document.getElementById('ia-file-help');
        if (!stack || !projectHelp || !ftpHelp || !iaHelp) {
            return;
        }
        var sync = function () {
            if (stack.value === 'java') {
                projectHelp.textContent = '영문 소문자로 시작하며, 소문자·숫자·하이픈만 사용할 수 있습니다. 주소는 https://프로젝트명.hk-test.co.kr, 퍼블 관리자는 https://pub프로젝트명-adm.hk-test.co.kr 입니다.';
                ftpHelp.textContent = '퍼블 사이트는 이 아이디 앞에 pub를 붙인 계정으로 관리합니다.';
                iaHelp.textContent = '엑셀(.xlsx)만 올릴 수 있습니다. 서버 생성이 끝나면 이 IA의 메뉴와 게시판을 퍼블 관리자에 넣습니다.';
                return;
            }
            projectHelp.textContent = '영문 소문자로 시작하며, 소문자·숫자·하이픈만 사용할 수 있습니다. 주소는 https://프로젝트명.hk-test.co.kr, 퍼블 주소는 https://pub프로젝트명.hk-test.co.kr 입니다.';
            ftpHelp.textContent = '퍼블 사이트는 이 아이디 앞에 pub를 붙인 계정으로 관리합니다.';
            iaHelp.textContent = '엑셀(.xlsx)만 올릴 수 있습니다. 서버 생성이 끝나면 이 IA의 메뉴와 필드를 퍼블 관리자에 넣습니다.';
        };
        stack.addEventListener('change', sync);
        sync();
    })();
</script>
@endsection
