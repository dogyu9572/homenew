@extends('backoffice.layouts.app')

@section('title', '회원 정보 수정')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/backoffice/boards.css') }}">
<link rel="stylesheet" href="{{ asset('css/backoffice/users.css') }}">
@endsection

@section('content')
@if ($errors->any())
    <div class="alert alert-danger board-hidden-alert">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="board-container">
    <div class="board-header">
        <a href="{{ route('backoffice.users.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> 목록으로
        </a>
    </div>

    <div class="board-card">
        <div class="board-card-body">
            <form id="userForm" action="{{ route('backoffice.users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="member-form-section">
                    <h3 class="member-section-title">기본 정보</h3>

                    <div class="member-form-list">
                        <div class="member-form-row">
                            <label class="member-form-label" for="name">이름 <span class="required">*</span></label>
                            <div class="member-form-field">
                                <input type="text" class="board-form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            </div>
                        </div>

                        <div class="member-form-row">
                            <label class="member-form-label" for="email">이메일 <span class="required">*</span></label>
                            <div class="member-form-field">
                                <input type="email" class="board-form-control" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            </div>
                        </div>

                        <div class="member-form-row">
                            <label class="member-form-label" for="login_id">아이디</label>
                            <div class="member-form-field">
                                <input type="text" class="board-form-control" id="login_id" name="login_id" value="{{ old('login_id', $user->login_id) }}">
                            </div>
                        </div>

                        <div class="member-form-row">
                            <label class="member-form-label" for="department">부서</label>
                            <div class="member-form-field">
                                <input type="text" class="board-form-control" id="department" name="department" value="{{ old('department', $user->department) }}">
                            </div>
                        </div>

                        <div class="member-form-row">
                            <label class="member-form-label" for="position">직위</label>
                            <div class="member-form-field">
                                <input type="text" class="board-form-control" id="position" name="position" value="{{ old('position', $user->position) }}">
                            </div>
                        </div>

                        <div class="member-form-row">
                            <label class="member-form-label" for="contact">연락처</label>
                            <div class="member-form-field">
                                <input type="text" class="board-form-control" id="contact" name="contact" value="{{ old('contact', $user->contact) }}">
                            </div>
                        </div>

                        <div class="member-form-row">
                            <label class="member-form-label" for="password">새 비밀번호</label>
                            <div class="member-form-field">
                                <input type="password" class="board-form-control" id="password" name="password" placeholder="변경하지 않으려면 비워두세요">
                            </div>
                        </div>

                        <div class="member-form-row">
                            <label class="member-form-label" for="password_confirmation">비밀번호 확인</label>
                            <div class="member-form-field">
                                <input type="password" class="board-form-control" id="password_confirmation" name="password_confirmation" placeholder="새 비밀번호를 다시 입력하세요">
                            </div>
                        </div>

                        <div class="member-form-row">
                            <label class="member-form-label">계정 상태</label>
                            <div class="member-form-field">
                                <div class="board-radio-group">
                                    <div class="board-radio-item">
                                        <input type="radio" id="is_active_y" name="is_active" value="1" class="board-radio-input" @checked(old('is_active', (int) $user->is_active) == 1)>
                                        <label for="is_active_y">활성화</label>
                                    </div>
                                    <div class="board-radio-item">
                                        <input type="radio" id="is_active_n" name="is_active" value="0" class="board-radio-input" @checked(old('is_active', (int) $user->is_active) == 0)>
                                        <label for="is_active_n">비활성화</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <div class="board-form-actions">
                <button type="submit" form="userForm" class="btn btn-primary">
                    <i class="fas fa-save"></i> 저장
                </button>
                <a href="{{ route('backoffice.users.index') }}" class="btn btn-secondary">취소</a>
                <form action="{{ route('backoffice.users.destroy', $user) }}" method="POST" style="display: inline-block; margin: 0;" onsubmit="return confirm('정말로 삭제하시겠습니까?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> 삭제
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
