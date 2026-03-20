@php
    $portfolio = $portfolio ?? null;
    $isEdit = (bool) $portfolio;
    $action = $isEdit ? route('backoffice.portfolio.update', $portfolio) : route('backoffice.portfolio.store');
    $method = $isEdit ? 'PUT' : 'POST';
    $keywords = old('keywords_input', $isEdit ? implode(' ', $portfolio->keywords ?? []) : '');
    $featureImages = old('feature_images', []);
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

<form action="{{ $action }}" method="POST" enctype="multipart/form-data" id="portfolioForm">
    @csrf
    @if($isEdit) @method($method) @endif

    <div class="member-form-section">
        <h3 class="member-section-title">기본 정보</h3>

        <div class="member-form-list">
            <div class="member-form-row">
                <label class="member-form-label">카테고리 <span class="required">*</span></label>
                <div class="member-form-field">
                    <select name="category" class="board-form-control" required>
                        <option value="">선택</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" @selected(old('category', $portfolio->category ?? '') === $category)>{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="member-form-row">
                <label class="member-form-label">주요 개발 내용</label>
                <div class="member-form-field">
                    <input type="text" class="board-form-control" name="development_summary" value="{{ old('development_summary', $portfolio->development_summary ?? '') }}">
                </div>
            </div>

            <div class="member-form-row">
                <label class="member-form-label">제목 <span class="required">*</span></label>
                <div class="member-form-field">
                    <input type="text" class="board-form-control" name="title" required value="{{ old('title', $portfolio->title ?? '') }}">
                </div>
            </div>

            <div class="member-form-row">
                <label class="member-form-label">키워드(# 구분)</label>
                <div class="member-form-field">
                    <input type="text" class="board-form-control" name="keywords_input" value="{{ $keywords }}" placeholder="#유지보수 #통합SI시스템개발">
                </div>
            </div>

            <div class="member-form-row">
                <label class="member-form-label">사이트 링크(URL)</label>
                <div class="member-form-field">
                    <input type="url" class="board-form-control" name="site_url" value="{{ old('site_url', $portfolio->site_url ?? '') }}">
                </div>
            </div>

            <div class="member-form-row">
                <label class="member-form-label">썸네일 이미지</label>
                <div class="member-form-field">
                    <div class="board-file-upload">
                        <div class="board-file-input-wrapper">
                            <input type="file" class="board-file-input" id="thumbnail_image" name="thumbnail_image" accept="image/*">
                            <div class="board-file-input-content">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span class="board-file-input-text">썸네일 파일을 선택하거나 드래그하세요</span>
                                <span class="board-file-input-subtext">이미지 파일 1개</span>
                            </div>
                        </div>
                        <div class="board-file-preview" id="thumbnailPreview"></div>
                    </div>
                </div>
            </div>

            <div class="member-form-row">
                <label class="member-form-label">메인 노출 여부</label>
                <div class="member-form-field">
                    <div class="board-checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_main_display" value="1" @checked(old('is_main_display', $portfolio->is_main_display ?? false))>
                            <span>메인 노출</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="member-form-row">
                <label class="member-form-label">노출 설정 여부</label>
                <div class="member-form-field">
                    <div class="board-checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $portfolio->is_active ?? true))>
                            <span>노출</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="member-form-row">
                <label class="member-form-label">노출 순서</label>
                <div class="member-form-field">
                    <input type="number" class="board-form-control" name="sort_order" min="0" value="{{ old('sort_order', $portfolio->sort_order ?? 0) }}">
                </div>
            </div>
        </div>
    </div>

    <div class="member-form-section">
        <h3 class="member-section-title">상세 설명</h3>
        <div class="member-form-list">
            <div class="member-form-row">
                <label class="member-form-label">상세 설명(요약)</label>
                <div class="member-form-field">
                    <textarea class="board-form-control board-textarea" name="detail_summary" rows="3">{{ old('detail_summary', $portfolio->detail_summary ?? '') }}</textarea>
                </div>
            </div>
            <div class="member-form-row">
                <label class="member-form-label">상세 설명(editor)</label>
                <div class="member-form-field">
                    <textarea class="board-form-control board-textarea" name="detail_editor" rows="8">{{ old('detail_editor', $portfolio->detail_editor ?? '') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="member-form-section">
        <h3 class="member-section-title">Problem</h3>
        <div class="member-form-list">
            <div class="member-form-row">
                <label class="member-form-label">제목</label>
                <div class="member-form-field">
                    <input type="text" class="board-form-control" name="problem_title" value="{{ old('problem_title', $portfolio->problem_title ?? '') }}">
                </div>
            </div>
            <div class="member-form-row">
                <label class="member-form-label">내용</label>
                <div class="member-form-field">
                    <textarea class="board-form-control board-textarea" name="problem_content" rows="3">{{ old('problem_content', $portfolio->problem_content ?? '') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="member-form-section">
        <h3 class="member-section-title">Solution</h3>
        <div class="member-form-list">
            <div class="member-form-row">
                <label class="member-form-label">제목</label>
                <div class="member-form-field">
                    <input type="text" class="board-form-control" name="solution_title" value="{{ old('solution_title', $portfolio->solution_title ?? '') }}">
                </div>
            </div>
            <div class="member-form-row">
                <label class="member-form-label">내용</label>
                <div class="member-form-field">
                    <textarea class="board-form-control board-textarea" name="solution_content" rows="3">{{ old('solution_content', $portfolio->solution_content ?? '') }}</textarea>
                </div>
            </div>
            <div class="member-form-row">
                <label class="member-form-label">Before / After</label>
                <div class="member-form-field file-row">
                    <div>
                        <label class="sub-label">Before 이미지</label>
                        <div class="board-file-upload">
                            <div class="board-file-input-wrapper">
                                <input type="file" class="board-file-input" id="solution_before_image" name="solution_before_image" accept="image/*">
                                <div class="board-file-input-content">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span class="board-file-input-text">Before 파일 선택</span>
                                    <span class="board-file-input-subtext">이미지 파일 1개</span>
                                </div>
                            </div>
                            <div class="board-file-preview" id="beforeImagePreview"></div>
                        </div>
                    </div>
                    <div>
                        <label class="sub-label">After 이미지</label>
                        <div class="board-file-upload">
                            <div class="board-file-input-wrapper">
                                <input type="file" class="board-file-input" id="solution_after_image" name="solution_after_image" accept="image/*">
                                <div class="board-file-input-content">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span class="board-file-input-text">After 파일 선택</span>
                                    <span class="board-file-input-subtext">이미지 파일 1개</span>
                                </div>
                            </div>
                            <div class="board-file-preview" id="afterImagePreview"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="member-form-section">
        <h3 class="member-section-title">Feature development</h3>
        <div class="member-form-list">
            <div class="member-form-row">
                <label class="member-form-label">제목</label>
                <div class="member-form-field">
                    <input type="text" class="board-form-control" name="feature_title" value="{{ old('feature_title', $portfolio->feature_title ?? '') }}">
                </div>
            </div>
            <div class="member-form-row">
                <label class="member-form-label">내용</label>
                <div class="member-form-field">
                    <textarea class="board-form-control board-textarea" name="feature_content" rows="3">{{ old('feature_content', $portfolio->feature_content ?? '') }}</textarea>
                </div>
            </div>
            <div class="member-form-row">
                <label class="member-form-label">Feature 이미지</label>
                <div class="member-form-field" id="featureImagesWrap">
                    <div class="board-file-upload">
                        <div class="board-file-input-wrapper">
                            <input type="file" class="board-file-input" id="feature_images" name="feature_images[]" accept="image/*" multiple>
                            <div class="board-file-input-content">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span class="board-file-input-text">Feature 파일을 선택하거나 드래그하세요</span>
                                <span class="board-file-input-subtext">최대 5개 이미지</span>
                            </div>
                        </div>
                        <div class="board-file-preview" id="featureImagePreview"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="member-form-section">
        <h3 class="member-section-title">고객 리뷰</h3>
        <div id="reviewsWrap">
            @php
                $reviews = old('reviews', $isEdit ? $portfolio->reviews->toArray() : [['title' => '', 'manager_name' => '', 'content' => '']]);
            @endphp
            @foreach($reviews as $idx => $review)
                <div class="review-row">
                    <div class="review-row-grid">
                        <input type="text" class="board-form-control" name="reviews[{{ $idx }}][title]" placeholder="제목" value="{{ $review['title'] ?? '' }}">
                        <input type="text" class="board-form-control" name="reviews[{{ $idx }}][manager_name]" placeholder="담당자명" value="{{ $review['manager_name'] ?? '' }}">
                    </div>
                    <textarea class="board-form-control board-textarea" name="reviews[{{ $idx }}][content]" rows="3" placeholder="내용 (strong 태그 가능)">{{ $review['content'] ?? '' }}</textarea>
                    <button type="button" class="btn btn-danger btn-sm remove-review">리뷰 삭제</button>
                </div>
            @endforeach
        </div>
        <button type="button" class="btn btn-secondary btn-sm" id="addReviewBtn">리뷰 추가</button>
    </div>

    <div class="board-form-actions">
        <button type="submit" class="btn btn-primary">저장</button>
        <a href="{{ route('backoffice.portfolio.index') }}" class="btn btn-secondary">취소</a>
    </div>
</form>

<script src="{{ asset('js/backoffice/portfolio.js') }}"></script>
<script src="{{ asset('js/backoffice/portfolio-file-upload.js') }}"></script>

