@extends('backoffice.layouts.app')

@section('title', $board->name ?? '게시판')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/common/modal.css') }}">
    <style>
        .board-post-comments .comment-form-row {
            align-items: flex-start;
        }

        .board-post-comments .comment-form-field {
            flex: 1;
            max-width: none;
            width: 100%;
        }

        .board-post-comments .comment-textarea {
            max-width: none;
            width: 100%;
            min-height: 120px;
            resize: vertical;
        }

        .board-post-comments .comment-form-actions {
            display: flex;
            justify-content: flex-end;
            padding-top: 10px;
        }

        @media (max-width: 768px) {
            .board-post-comments .comment-form-row {
                flex-direction: column;
            }

            .board-post-comments .comment-form-row .bo-form-label {
                width: 100%;
                margin-bottom: 8px;
            }

            .board-post-comments .comment-form-actions .btn {
                width: 100%;
            }
        }
    </style>
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success board-hidden-alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="board-container">
        <div class="board-page-header">
            <div class="board-page-buttons">
                <a href="{{ route('backoffice.board-posts.index', $board->slug ?? 'notice') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> 목록으로
                </a>
            </div>
        </div>

        <div class="board-card">
            <div class="board-card-body">
                <div class="board-post-header">
                    <div class="board-post-title">
                        <h3>{{ $post->title }}</h3>
                    </div>
                    <div class="board-post-meta">
                        <span class="board-post-author">작성자: {{ $post->author_name ?? '알 수 없음' }}</span>
                        <span class="board-post-date">작성일: {{ $post->created_at->format('Y-m-d H:i') }}</span>
                        <span class="board-post-views">조회수: {{ $post->view_count ?? 0 }}</span>
                    </div>
                </div>

                @if ($post->is_notice)
                    <div class="board-post-notice">
                        <span class="badge badge-warning">공지</span>
                    </div>
                @endif

                @if ($post->category)
                    <div class="board-post-category">
                        <span class="badge badge-info">{{ $post->category }}</span>
                    </div>
                @endif

                <div class="board-post-content">
                    {!! $post->content !!}
                </div>

                <!-- 커스텀 필드 정보 표시 -->
                @if($board->custom_fields_config && $post->custom_fields)
                    @php
                        $customFields = json_decode($post->custom_fields, true);
                    @endphp
                    @if($customFields && is_array($customFields))
                        <div class="board-post-custom-fields">
                            <h6>추가 정보</h6>
                            <div class="board-custom-fields-list">
                                @foreach($board->custom_fields_config as $fieldConfig)
                                    @if(isset($customFields[$fieldConfig['name']]) && !empty($customFields[$fieldConfig['name']]))
                                        @php
                                            $fieldValue = $customFields[$fieldConfig['name']];
                                            $displayValue = match($fieldConfig['type']) {
                                                'date' => \Carbon\Carbon::parse($fieldValue)->format('Y-m-d'),
                                                'checkbox' => $fieldValue == '1' ? '예' : '아니오',
                                                default => $fieldValue
                                            };
                                        @endphp
                                        <div class="board-custom-field-item">
                                            <span class="board-custom-field-label">{{ $fieldConfig['label'] }}:</span>
                                            <span class="board-custom-field-value">{{ $displayValue }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif

                @if ($post->attachments)
                    <div class="board-post-attachments">
                        <h6>첨부파일</h6>
                        <div class="board-attachment-list">
                            @php
                                $attachments = json_decode($post->attachments, true);
                            @endphp
                            @if (is_array($attachments))
                                @foreach ($attachments as $attachment)
                                    <div class="board-attachment-item">
                                        <i class="fas fa-file"></i>
                                        <a href="{{ asset('storage/' . $attachment['path']) }}" 
                                           class="board-attachment-link" 
                                           target="_blank"
                                           download="{{ $attachment['name'] }}">
                                            {{ $attachment['name'] }}
                                        </a>
                                        <span class="board-attachment-size">
                                            ({{ number_format($attachment['size'] / 1024 / 1024, 2) }}MB)
                                        </span>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                @endif

                @php
                    $currentUser = auth()->user();
                    $authorName = (string) ($post->author_name ?? '');
                    $canManagePost = $currentUser
                        && (
                            (int) ($post->user_id ?? 0) === (int) $currentUser->id
                            || (
                                empty($post->user_id)
                                && (
                                    ($currentUser->name && str_contains($authorName, $currentUser->name))
                                    || ($currentUser->login_id && str_contains($authorName, $currentUser->login_id))
                                )
                            )
                        );
                @endphp

                @if($canManagePost)
                    <div class="board-post-actions">
                        <a href="{{ route('backoffice.board-posts.edit', [$board->slug ?? 'notice', $post->id]) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> 수정
                        </a>
                        <form action="{{ route('backoffice.board-posts.destroy', [$board->slug ?? 'notice', $post->id]) }}"
                              method="POST" class="d-inline"
                              onsubmit="return confirm('정말 이 게시글을 삭제하시겠습니까?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i> 삭제
                            </button>
                        </form>
                    </div>
                @endif

                <div class="board-post-comments mt-4">
                    <h4 class="board-comments-title">댓글 {{ ($comments ?? collect())->count() }}개</h4>

                    <div class="board-card">
                        <div class="board-card-body">
                            @if(isset($errors) && $errors->has('content'))
                                <div class="alert alert-danger">
                                    {{ $errors->first('content') }}
                                </div>
                            @endif

                            <form method="POST" action="{{ route('backoffice.board-posts.comments.store', [$board->slug ?? 'notice', $post->id]) }}">
                                @csrf
                                <div class="bo-form-row comment-form-row">
                                    <label class="bo-form-label" for="comment-content">내용</label>
                                    <div class="bo-form-field comment-form-field">
                                        <textarea id="comment-content" class="board-form-control comment-textarea" name="content" rows="4" required placeholder="댓글 내용을 입력하세요.">{{ old('content') }}</textarea>
                                        <div class="comment-form-actions">
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="fas fa-comment"></i> 댓글 등록
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="board-card mt-3">
                        <div class="board-card-body">
                            <table class="board-table">
                                <thead>
                                    <tr>
                                        <th class="w10">번호</th>
                                        <th class="w15">작성자</th>
                                        <th>내용</th>
                                        <th class="w15">등록일</th>
                                        <th class="w10">관리</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse(($comments ?? collect()) as $index => $comment)
                                        @php
                                            $canDeleteComment = (int) ($comment->user_id ?? 0) === (int) auth()->id() || $canManagePost;
                                        @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $comment->author_name ?? '관리자' }}</td>
                                            <td class="text-start">{!! nl2br(e($comment->content ?? '')) !!}</td>
                                            <td>{{ $comment->created_at ? $comment->created_at->format('Y-m-d H:i') : '' }}</td>
                                            <td>
                                                @if($canDeleteComment)
                                                    <form method="POST" action="{{ route('backoffice.board-posts.comments.destroy', [$board->slug ?? 'notice', $post->id, $comment->id]) }}" class="d-inline" onsubmit="return confirm('댓글을 삭제하시겠습니까?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm">
                                                            <i class="fas fa-trash"></i> 삭제
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">댓글이 없습니다.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/backoffice/board-posts.js') }}"></script>
@endsection
