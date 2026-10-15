@extends('backoffice.layouts.app')

@section('title', '자동화 배포')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/backoffice/boards.css') }}">
    <link rel="stylesheet" href="{{ asset('css/backoffice/project-deploy.css') }}?v=20261012b">
@endsection

@section('content')
<div class="board-container">
    <div class="board-header">
        <a href="{{ route('backoffice.project-deploys.index') }}" class="btn btn-secondary btn-sm">
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

            <form action="{{ route('backoffice.project-deploys.update', $deploy) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="board-form-group">
                    <label for="stack" class="board-form-label">종류 <span class="required">*</span></label>
                    <select class="board-form-control" id="stack" name="stack" required>
                        <option value="laravel" @selected(old('stack', $deploy->stack ?? 'laravel') === 'laravel')>라라벨</option>
                        <option value="java" @selected(old('stack', $deploy->stack ?? 'laravel') === 'java')>자바</option>
                    </select>
                </div>

                <div class="board-form-group">
                    <label for="customer_name" class="board-form-label">고객사명 <span class="required">*</span></label>
                    <input type="text" class="board-form-control" id="customer_name" name="customer_name" value="{{ old('customer_name', $deploy->customer_name) }}" required maxlength="100">
                </div>

                <div class="board-form-group">
                    <label class="board-form-label">프로젝트명</label>
                    <input type="text" class="board-form-control" value="{{ $deploy->project_name }}" readonly>
                </div>

                <div class="board-form-group">
                    <label for="site_url" class="board-form-label" id="siteUrlLabel">{{ old('stack', $deploy->stack ?? 'laravel') === 'java' ? '사용자 주소' : '주소' }} <span class="required">*</span></label>
                    <input type="text" class="board-form-control" id="site_url" name="site_url" value="{{ old('site_url', $deploy->site_url) }}" required maxlength="255">
                </div>

                <div class="board-form-group" id="adminSiteUrlGroup" @if(old('stack', $deploy->stack ?? 'laravel') !== 'java') hidden @endif>
                    <label for="admin_site_url" class="board-form-label">관리자 주소 <span class="required">*</span></label>
                    <input type="text" class="board-form-control" id="admin_site_url" name="admin_site_url" value="{{ old('admin_site_url', $deploy->admin_site_url) }}" maxlength="255" @if(old('stack', $deploy->stack ?? 'laravel') === 'java') required @else disabled @endif>
                </div>

                <div class="board-form-group">
                    <label for="server_ip" class="board-form-label">IP <span class="required">*</span></label>
                    <input type="text" class="board-form-control" id="server_ip" name="server_ip" value="{{ old('server_ip', $deploy->server_ip) }}" required maxlength="45">
                </div>

                <div class="board-form-group">
                    <label for="ftp_username" class="board-form-label">SFTP 아이디 <span class="required">*</span></label>
                    <input type="text" class="board-form-control" id="ftp_username" name="ftp_username" value="{{ old('ftp_username', $deploy->ftp_username) }}" required maxlength="32">
                </div>
                @include('backoffice.project-deploys._java_account_note', ['deploy' => $deploy])

                <div class="board-form-group">
                    <label for="ftp_password" class="board-form-label">SFTP 비밀번호</label>
                    <input type="text" class="board-form-control" id="ftp_password" name="ftp_password" value="{{ old('ftp_password', $deploy->ftp_password) }}" maxlength="128" autocomplete="off">
                </div>

                <div class="board-form-group">
                    <label for="source_path" class="board-form-label">소스 위치 <span class="required">*</span></label>
                    <input type="text" class="board-form-control" id="source_path" name="source_path" value="{{ old('source_path', $deploy->source_path) }}" required maxlength="255">
                </div>

                <div class="board-form-group">
                    <label for="git_url" class="board-form-label">깃 정보 <span class="required">*</span></label>
                    <input type="text" class="board-form-control" id="git_url" name="git_url" value="{{ old('git_url', $deploy->git_url) }}" required maxlength="255">
                </div>

                <div class="board-form-group">
                    <label for="auto_deploy" class="board-form-label">자동화 배포 <span class="required">*</span></label>
                    <select class="board-form-control" id="auto_deploy" name="auto_deploy" required>
                        <option value="Y" @selected(old('auto_deploy', $deploy->auto_deploy ? 'Y' : 'N') === 'Y')>Y</option>
                        <option value="N" @selected(old('auto_deploy', $deploy->auto_deploy ? 'Y' : 'N') === 'N')>N</option>
                    </select>
                </div>

                <div class="board-form-group">
                    <label for="environment" class="board-form-label">구분 <span class="required">*</span></label>
                    <select class="board-form-control" id="environment" name="environment" required>
                        <option value="dev" @selected(old('environment', $deploy->environment) === 'dev')>데브</option>
                        <option value="live" @selected(old('environment', $deploy->environment) === 'live')>라이브</option>
                    </select>
                </div>

                @include('backoffice.project-deploys._java_targets', ['deploy' => $deploy])

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

@section('scripts')
    <script src="{{ asset('js/backoffice/project-deploy-form.js') }}?v=20261012c"></script>
@endsection
