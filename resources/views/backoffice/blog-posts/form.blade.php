@php
    $blogPost = $blogPost ?? null;
    $isEdit = (bool) $blogPost;
    $action = $isEdit ? route('backoffice.blog-posts.update', $blogPost) : route('backoffice.blog-posts.store');
    if ($isEdit) {
        $sectionsDefault = $blogPost->sections->map(function ($s) {
            $items = $s->items;
            if ($items->isEmpty()) {
                $itemsArr = [['subheading' => '', 'content' => '']];
            } else {
                $itemsArr = $items->map(fn ($i) => [
                    'subheading' => $i->subheading ?? '',
                    'content' => $i->content ?? '',
                ])->values()->all();
            }

            return [
                'subtitle' => $s->subtitle ?? '',
                'items' => $itemsArr,
            ];
        })->values()->all();
    } else {
        $sectionsDefault = [
            [
                'subtitle' => '',
                'items' => [
                    ['subheading' => '', 'content' => ''],
                ],
            ],
        ];
    }
    $sections = old('sections', $sectionsDefault);
    if (! is_array($sections) || $sections === []) {
        $sections = [
            [
                'subtitle' => '',
                'items' => [['subheading' => '', 'content' => '']],
            ],
        ];
    }
    $tagsInput = old('tags_input', $isEdit ? implode(' ', $blogPost->tags ?? []) : '');
    $faqPickerItems = $faqPickerItems ?? collect();
    $selectedFaqIds = old('faq_board_post_ids', $isEdit ? ($blogPost->faq_board_post_ids ?? []) : []);
    if (! is_array($selectedFaqIds)) {
        $selectedFaqIds = [];
    }
    $selectedFaqIds = array_values(array_filter(array_map('intval', $selectedFaqIds), fn ($id) => $id > 0));
@endphp

@if ($errors->any())
    <div class="alert alert-danger board-hidden-alert">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ $action }}" method="POST" enctype="multipart/form-data" id="blogPostForm">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="member-form-section">
        <h3 class="member-section-title">기본 정보</h3>
        <div class="member-form-list">
            <div class="member-form-row">
                <label class="member-form-label">공지 여부</label>
                <div class="member-form-field">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_notice" value="1" @checked(old('is_notice', $blogPost->is_notice ?? false))>
                        <span>공지</span>
                    </label>
                </div>
            </div>

            <div class="member-form-row">
                <label class="member-form-label">카테고리 <span class="required">*</span></label>
                <div class="member-form-field">
                    <select class="board-form-control" name="category" required>
                        <option value="">카테고리 선택</option>
                        @foreach($categories as $value => $label)
                            <option value="{{ $value }}" @selected(old('category', $blogPost->category ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="member-form-row">
                <label class="member-form-label">제목 <span class="required">*</span></label>
                <div class="member-form-field">
                    <input type="text" class="board-form-control" name="title" value="{{ old('title', $blogPost->title ?? '') }}" required>
                </div>
            </div>

            <div class="member-form-row">
                <label class="member-form-label">URL 슬러그</label>
                <div class="member-form-field">
                    <input type="text" class="board-form-control @error('slug') is-invalid @enderror" name="slug" id="blogPostSlugInput" value="{{ old('slug', $blogPost->slug ?? '') }}" placeholder="영문 소문자·숫자·하이픈 (비우면 기존/제목 기준 자동)" autocomplete="off">
                    @error('slug')
                        <p class="text-danger" style="margin-top:6px;">{{ $message }}</p>
                    @enderror
                    <p class="text-muted">공개 주소: /blog/{슬러그}</p>
                </div>
            </div>

            <div class="member-form-row">
                <label class="member-form-label">Meta Description</label>
                <div class="member-form-field">
                    <input type="text" class="board-form-control @error('meta_description') is-invalid @enderror" name="meta_description" value="{{ old('meta_description', $blogPost->meta_description ?? '') }}" maxlength="500" placeholder="검색·SNS 공유용 요약 (비우면 본문에서 자동 생성)" autocomplete="off">
                    @error('meta_description')
                        <p class="text-danger" style="margin-top:6px;">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="member-form-row">
                <label class="member-form-label">내용</label>
                <div class="member-form-field">
                    <textarea class="board-form-control board-textarea" name="lead_content" rows="5" placeholder="내용" data-backoffice-ckeditor data-source-editing="true">{{ old('lead_content', $blogPost->lead_content ?? '') }}</textarea>
                    <p class="text-muted">비우면 상세에 출력하지 않습니다.</p>
                </div>
            </div>

            <div class="member-form-row">
                <label class="member-form-label">태그</label>
                <div class="member-form-field">
                    <input type="text" class="board-form-control" name="tags_input" value="{{ $tagsInput }}" placeholder="#팀스토리 #홈페이지">
                    <p class="text-muted">추천 콘텐츠는 동일 카테고리·태그 기준으로 자동 노출됩니다.</p>
                </div>
            </div>

            <div class="member-form-row">
                <label class="member-form-label">썸네일 이미지</label>
                <div class="member-form-field">
                    <div class="board-file-upload">
                        <div class="board-file-input-wrapper">
                            <input type="file" class="board-file-input" id="blog_post_thumbnail" name="thumbnail" accept="image/*">
                            <div class="board-file-input-content">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span class="board-file-input-text">썸네일 파일을 선택하거나 드래그하세요</span>
                                <span class="board-file-input-subtext">이미지 파일 1개</span>
                            </div>
                        </div>
                        <div class="board-file-preview" id="blogPostThumbnailPreview"></div>
                        @if($isEdit && !empty($blogPost->thumbnail_path))
                            <div class="board-existing-files blog-existing-thumbnail">
                                <div class="board-attachment-list">
                                    <div class="board-attachment-item existing-file">
                                        <i class="fas fa-file-image"></i>
                                        <span class="board-attachment-name">{{ basename($blogPost->thumbnail_path) }}</span>
                                    </div>
                                </div>
                            </div>
                            <label class="checkbox-label blog-remove-thumbnail-check">
                                <input type="checkbox" name="remove_thumbnail" value="1">
                                <span>기존 썸네일 삭제</span>
                            </label>
                        @endif
                    </div>
                </div>
            </div>

            <div class="member-form-row">
                <label class="member-form-label">노출 여부</label>
                <div class="member-form-field">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $blogPost->is_published ?? true))>
                        <span>노출</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="member-form-section">
        <h3 class="member-section-title">목차·본문 (최대 10구간)</h3> 
        <div id="blogSectionsWrap">
            @foreach($sections as $index => $section)
                @php
                    $items = $section['items'] ?? [['subheading' => '', 'content' => '']];
                    if (! is_array($items) || $items === []) {
                        $items = [['subheading' => '', 'content' => '']];
                    }
                @endphp
                <div class="review-row blog-toc-block" data-blog-toc-block>
                    <div class="review-row-grid">
                        <input type="text" class="board-form-control" name="sections[{{ $index }}][subtitle]" value="{{ $section['subtitle'] ?? '' }}" placeholder="목차">
                    </div>
                    <div class="blog-section-items-wrap" data-blog-section-items-wrap>
                        @foreach($items as $j => $item)
                            <div class="review-row blog-section-item-row" data-blog-section-item-row>
                                <div class="review-row-grid">
                                    <input type="text" class="board-form-control" name="sections[{{ $index }}][items][{{ $j }}][subheading]" value="{{ $item['subheading'] ?? '' }}" placeholder="소제목">
                                </div>
                                <textarea class="board-form-control board-textarea" name="sections[{{ $index }}][items][{{ $j }}][content]" rows="5" placeholder="본문" data-backoffice-ckeditor data-source-editing="true">{{ $item['content'] ?? '' }}</textarea>
                                <button type="button" class="btn btn-danger btn-sm remove-blog-section-item">블록 삭제</button>
                            </div>
                        @endforeach
                    </div>
                    <div class="member-form-field">
                        <button type="button" class="btn btn-secondary btn-sm add-blog-section-item">소제목·본문 추가</button>
                        <button type="button" class="btn btn-danger btn-sm remove-blog-toc">목차 삭제</button>
                    </div>
                </div>
            @endforeach
        </div>
        <button type="button" class="btn btn-secondary btn-sm" id="addBlogSectionBtn">목차 추가</button>
    </div>

    <div class="member-form-section" id="blogFaqPickerSection">
        <h3 class="member-section-title">하단 FAQ</h3>
        <p class="text-muted">위에서부터 노출 순서입니다. 비우면 상세에 FAQ 영역을 넣지 않습니다.</p>
        @if($faqPickerItems->isEmpty())
            <p class="text-muted">등록된 FAQ가 없습니다.</p>
        @else
            <script type="application/json" id="blog-faq-titles-json">@json($faqPickerItems->pluck('title', 'id'))</script>
            <script type="application/json" id="blog-faq-initial-ids">@json($selectedFaqIds)</script>
            <div class="member-form-row blog-faq-add-row">
                <div class="member-form-field blog-faq-add-select-wrap">
                    <label class="member-form-label" for="blogFaqAddSelect">FAQ 추가</label>
                    <select id="blogFaqAddSelect" class="board-form-control">
                        <option value="">선택</option>
                        @foreach($faqPickerItems as $f)
                            <option value="{{ $f->id }}">{{ $f->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="member-form-field blog-faq-add-btn-wrap">
                    <button type="button" class="btn btn-secondary btn-sm" id="blogFaqAddBtn">추가</button>
                </div>
            </div>
            <ul id="blogFaqSelectedList" class="blog-faq-selected-list" aria-label="선택된 FAQ 순서"></ul>
            <div id="blogFaqHiddenInputs" class="blog-faq-hidden-inputs"></div>
        @endif
    </div>

    <div class="board-form-actions">
        <button type="submit" class="btn btn-primary">저장</button>
        <a href="{{ route('backoffice.blog-posts.index') }}" class="btn btn-secondary">취소</a>
    </div>
</form>
