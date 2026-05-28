@include('backoffice.approval.forms.partials.document-header')
@php
    $templateKey = (string) ($template['key'] ?? '');
    $detailMode = (bool) ($isDetail ?? false);
    $editableDetailMode = (bool) ($canEditDraft ?? false);
    $readOnlyMode = $detailMode && ! $editableDetailMode;
    $content = $documentContent ?? [];
    $proposalVariantFields = [
        'proposal-education' => [
            ['label' => '교육명', 'name' => 'education_name', 'type' => 'text', 'placeholder' => '교육명을 입력하세요'],
            ['label' => '교육기관', 'name' => 'education_provider', 'type' => 'text', 'placeholder' => '교육기관을 입력하세요'],
            ['label' => '교육기간', 'name' => 'education_period', 'type' => 'text', 'placeholder' => '예: 2026-05-01 ~ 2026-05-03'],
        ],
        'proposal-open-approval' => [
            ['label' => '오픈일', 'name' => 'open_date', 'type' => 'date'],
            ['label' => '오픈 URL', 'name' => 'open_url', 'type' => 'text', 'placeholder' => 'https://'],
            ['label' => '검수 담당자', 'name' => 'reviewer', 'type' => 'text', 'placeholder' => '검수 담당자'],
        ],
        'proposal-dining' => [
            ['label' => '회식일', 'name' => 'dining_date', 'type' => 'date'],
            ['label' => '회식 장소', 'name' => 'dining_place', 'type' => 'text', 'placeholder' => '회식 장소를 입력하세요'],
            ['label' => '참석 예정 인원', 'name' => 'participant_count', 'type' => 'number', 'placeholder' => '0'],
        ],
        'proposal-general' => [
            ['label' => '요청 금액', 'name' => 'amount', 'type' => 'number', 'placeholder' => '0'],
        ],
    ];
    $variantFields = $proposalVariantFields[$templateKey] ?? [];
    $usesOutsourcingSection = $templateKey === 'proposal-outsourcing';
    $usesProjectSummary = in_array($templateKey, ['proposal-outsourcing', 'proposal-open-approval'], true);
    $showDevelopmentDetail = in_array($templateKey, ['proposal-outsourcing', 'proposal-general'], true);
@endphp

<div class="approval-doc-body">
    <table class="approval-doc-table">
        <tbody>
            <tr>
                <th>제목</th>
                <td><input type="text" class="form-control" name="title" placeholder="품의 제목을 입력하세요" value="{{ $detailMode ? ($documentModel->title ?? '') : '' }}" @readonly($readOnlyMode)></td>
            </tr>
            @foreach($variantFields as $variantField)
                <tr>
                    <th>{{ $variantField['label'] }}</th>
                    <td>
                        <input
                            type="{{ $variantField['type'] }}"
                            class="form-control"
                            name="content[{{ $variantField['name'] }}]"
                            value="{{ $content[$variantField['name']] ?? '' }}"
                            placeholder="{{ $variantField['placeholder'] ?? '' }}"
                            @readonly($readOnlyMode)
                        >
                    </td>
                </tr>
            @endforeach
            @if($usesProjectSummary)
                <tr>
                    <th>프로젝트명</th>
                    <td><input type="text" class="form-control" name="content[project_name]" placeholder="프로젝트명을 입력하세요" value="{{ $content['project_name'] ?? '' }}" @readonly($readOnlyMode)></td>
                </tr>
                <tr>
                    <th>계약일</th>
                    <td><input type="date" class="form-control" name="content[contract_date]" value="{{ $content['contract_date'] ?? '' }}" @readonly($readOnlyMode)></td>
                </tr>
            @endif
            @if($usesOutsourcingSection)
                <tr>
                    <th>프로젝트 개발비용</th>
                    <td><input type="number" class="form-control" name="content[project_dev_cost]" placeholder="0" value="{{ $content['project_dev_cost'] ?? '' }}" @readonly($readOnlyMode)></td>
                </tr>
                <tr>
                    <th>외주 총비용</th>
                    <td><input type="number" class="form-control" name="content[outsourcing_total_cost]" placeholder="0" value="{{ $content['outsourcing_total_cost'] ?? '' }}" @readonly($readOnlyMode)></td>
                </tr>
                <tr>
                    <th>외주 개발자</th>
                    <td><input type="text" class="form-control" name="content[outsourcing_developer]" placeholder="외주 개발자를 입력하세요" value="{{ $content['outsourcing_developer'] ?? '' }}" @readonly($readOnlyMode)></td>
                </tr>
            @endif
            @if($showDevelopmentDetail)
                <tr>
                    <th>개발내역</th>
                    <td>
                        @if($readOnlyMode)
                            <div class="approval-richtext-view">{!! $content['development_detail'] ?? '' !!}</div>
                        @else
                            <textarea
                                class="form-control"
                                id="approval_proposal_reason"
                                name="content[development_detail]"
                                rows="6"
                                placeholder="개발내역을 입력하세요"
                                data-backoffice-ckeditor
                                data-source-editing="true"
                            >{{ $content['development_detail'] ?? '' }}</textarea>
                        @endif
                    </td>
                </tr>
            @endif
            <tr>
                <th>비고</th>
                <td>
                    @if($readOnlyMode)
                        <div class="approval-richtext-view">{!! $content['detail'] ?? '' !!}</div>
                    @else
                        <textarea
                            class="form-control"
                            id="approval_proposal_detail"
                            name="content[detail]"
                            rows="5"
                            placeholder="추가 내용을 입력하세요"
                            data-backoffice-ckeditor
                            data-source-editing="true"
                        >{{ $content['detail'] ?? '' }}</textarea>
                    @endif
                </td>
            </tr>
            @if($usesOutsourcingSection)
                <tr>
                    <th>외주자 정보</th>
                    <td class="approval-subtable-cell">
                        <table class="approval-subtable">
                            <tbody>
                                <tr>
                                    <th rowspan="5" class="approval-subtable-side-label">외주자정보</th>
                                    <th>이름</th>
                                    <td><input type="text" class="form-control" name="content[vendor_name]" value="{{ $content['vendor_name'] ?? '' }}" @readonly($readOnlyMode)></td>
                                </tr>
                                <tr>
                                    <th>이메일</th>
                                    <td><input type="email" class="form-control" name="content[vendor_email]" value="{{ $content['vendor_email'] ?? '' }}" @readonly($readOnlyMode)></td>
                                </tr>
                                <tr>
                                    <th>전화번호</th>
                                    <td><input type="text" class="form-control" name="content[vendor_phone]" value="{{ $content['vendor_phone'] ?? '' }}" @readonly($readOnlyMode)></td>
                                </tr>
                                <tr>
                                    <th>작업 완료일</th>
                                    <td><input type="date" class="form-control" name="content[vendor_completion_date]" value="{{ $content['vendor_completion_date'] ?? '' }}" @readonly($readOnlyMode)></td>
                                </tr>
                                <tr>
                                    <th>작업 비용 협의일</th>
                                    <td><input type="date" class="form-control" name="content[vendor_cost_agreed_date]" value="{{ $content['vendor_cost_agreed_date'] ?? '' }}" @readonly($readOnlyMode)></td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            @endif
            <tr>
                <th>첨부파일</th>
                <td>
                    @if($readOnlyMode)
                        @if(($documentModel->attachments ?? collect())->count() > 0)
                            <div class="approval-attachment-list">
                                @foreach($documentModel->attachments as $attachment)
                                    <a
                                        href="{{ asset('storage/'.$attachment->stored_path) }}"
                                        class="approval-attachment-item"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        <i class="fas fa-paperclip"></i>
                                        <span>{{ $attachment->original_name }}</span>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <span class="text-muted">첨부파일 없음</span>
                        @endif
                    @else
                        @if(($documentModel->attachments ?? collect())->count() > 0)
                            <div class="approval-attachment-list mb-2">
                                @foreach($documentModel->attachments as $attachment)
                                    <a
                                        href="{{ asset('storage/'.$attachment->stored_path) }}"
                                        class="approval-attachment-item"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        <i class="fas fa-paperclip"></i>
                                        <span>{{ $attachment->original_name }}</span>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                        <div class="board-file-upload approval-file-upload">
                            <div class="board-file-input-wrapper">
                                <input
                                    type="file"
                                    class="board-file-input"
                                    id="approval_proposal_attachments"
                                    name="attachments[]"
                                    multiple
                                    data-approval-file-input
                                    data-preview-target="approvalProposalAttachmentPreview"
                                >
                                <div class="board-file-input-content">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span class="board-file-input-text">첨부파일을 선택하거나 드래그하세요</span>
                                    <span class="board-file-input-subtext">여러 파일 첨부 가능</span>
                                </div>
                            </div>
                            <div class="board-file-preview" id="approvalProposalAttachmentPreview"></div>
                        </div>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>
</div>

<div class="approval-doc-footer-note">
    상기와 같은 내용으로 {{ $template['name'] }}를 상신하오니 검토 부탁드립니다.
</div>
</div>

