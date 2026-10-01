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

            <form action="{{ route('backoffice.project-provisions.store') }}" method="POST">
                @csrf

                <div class="board-form-group">
                    <label for="project_name" class="board-form-label">프로젝트명 <span class="required">*</span></label>
                    <input type="text" class="board-form-control" id="project_name" name="project_name" value="{{ old('project_name') }}" required maxlength="31" placeholder="yoonsunginc">
                    <small class="board-form-text">영문 소문자로 시작하며, 소문자·숫자·하이픈만 사용할 수 있습니다. 주소는 https://프로젝트명.hk-test.co.kr 입니다.</small>
                </div>

                <div class="board-form-group">
                    <label for="root_password" class="board-form-label">ROOT 비밀번호 <span class="required">*</span></label>
                    <input type="password" class="board-form-control" id="root_password" name="root_password" required minlength="8" maxlength="128" autocomplete="new-password">
                    <small class="board-form-text">이 프로젝트의 리눅스 계정 비밀번호입니다.</small>
                </div>

                <div class="board-form-group">
                    <label for="ftp_username" class="board-form-label">FTP 아이디 <span class="required">*</span></label>
                    <input type="text" class="board-form-control" id="ftp_username" name="ftp_username" value="{{ old('ftp_username') }}" required maxlength="31">
                    <small class="board-form-text">프로젝트명과 다른 아이디를 사용합니다.</small>
                </div>

                <div class="board-form-group">
                    <label for="ftp_password" class="board-form-label">FTP 비밀번호 <span class="required">*</span></label>
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
                    <label for="stack" class="board-form-label">프로젝트 종류 <span class="required">*</span></label>
                    <select class="board-form-control" id="stack" name="stack" required>
                        <option value="laravel" @selected(old('stack', 'laravel') === 'laravel')>라라벨</option>
                    </select>
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
@endsection
