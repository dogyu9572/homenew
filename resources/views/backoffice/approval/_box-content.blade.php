<div class="board-container" data-approval-box="{{ $boxType }}">
    @php
        $detailRoute = match($boxType) {
            'personal' => 'backoffice.approvals.personal.show',
            'cooperation' => 'backoffice.approvals.cooperation.show',
            default => 'backoffice.approvals.pending.show',
        };
    @endphp
    <div class="board-card">
        <div class="board-card-body">
            <div class="board-btn-group approval-tab-group">
                @foreach($tabs as $tabKey => $tabLabel)
                    <a
                        href="{{ request()->fullUrlWithQuery(['tab' => $tabKey, 'page' => null]) }}"
                        class="btn {{ $activeTab === $tabKey ? 'btn-primary' : 'btn-outline-secondary' }} btn-sm"
                        data-approval-tab="{{ $tabKey }}"
                    >
                        {{ $tabLabel }}
                    </a>
                @endforeach
            </div>

            <div class="board-filter">
                <form method="GET" action="{{ url()->current() }}" class="filter-form">
                    <input type="hidden" name="tab" value="{{ $activeTab }}">
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="find_field" class="filter-label">검색조건</label>
                            <select id="find_field" name="find_field" class="filter-select">
                                @foreach($searchFields as $fieldKey => $fieldLabel)
                                    <option value="{{ $fieldKey }}" @selected(($filters['find_field'] ?? 'ALL') === $fieldKey)>{{ $fieldLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="keyword" class="filter-label">검색어</label>
                            <input id="keyword" type="text" name="keyword" value="{{ $filters['keyword'] ?? '' }}" class="filter-input" placeholder="검색어 입력">
                        </div>
                        <div class="filter-group">
                            <div class="filter-buttons">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> 검색
                                </button>
                                <a href="{{ request()->url() . '?tab=' . $activeTab }}" class="btn btn-secondary">
                                    <i class="fas fa-undo"></i> 초기화
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="board-list-header">
                <div class="list-info">
                    <span class="list-count">Total : {{ $documents->total() }}</span>
                </div>
                <div class="list-controls">
                    <form method="GET" action="{{ url()->current() }}" class="per-page-form">
                        <input type="hidden" name="tab" value="{{ $activeTab }}">
                        <input type="hidden" name="find_field" value="{{ $filters['find_field'] ?? 'ALL' }}">
                        <input type="hidden" name="keyword" value="{{ $filters['keyword'] ?? '' }}">
                        <label for="per_page" class="per-page-label">표시 개수:</label>
                        <select id="per_page" name="per_page" class="per-page-select" onchange="this.form.submit()">
                            <option value="10" @selected($perPage === 10)>10개</option>
                            <option value="20" @selected($perPage === 20)>20개</option>
                            <option value="50" @selected($perPage === 50)>50개</option>
                            <option value="100" @selected($perPage === 100)>100개</option>
                        </select>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="board-table approval-list-table">
                    <colgroup>
                        <col style="width:5%;">
                        <col style="width:10%;">
                        <col style="width:10%;">
                        <col style="width:10%;">
                        <col style="width:22%;">
                        <col style="width:8%;">
                        <col style="width:8%;">
                        <col style="width:8%;">
                        <col style="width:8%;">
                        <col style="width:4%;">
                        <col style="width:7%;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>NO</th>
                            <th>문서번호</th>
                            <th>기안일자</th>
                            <th>결재일자</th>
                            <th>문서명</th>
                            <th>기안자</th>
                            <th>나의결재</th>
                            <th>상태</th>
                            <th>다음결재자</th>
                            <th>의견</th>
                            <th>처리</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $index => $document)
                            <tr>
                                <td>{{ $documents->total() - (($documents->currentPage() - 1) * $documents->perPage()) - $index }}</td>
                                <td>{{ $document['doc_no'] }}</td>
                                <td>{{ $document['drafted_at'] }}</td>
                                <td>{{ $document['approved_at'] ?? '-' }}</td>
                                <td class="text-start approval-title-cell">{{ $document['title'] }}</td>
                                <td>{{ $document['writer'] }}</td>
                                <td>{{ $document['my_status'] ?? '-' }}</td>
                                <td>{{ $document['status'] }}</td>
                                <td>{{ $document['next_approver'] ?? '-' }}</td>
                                <td>{{ $document['opinion_count'] ?? 0 }}</td>
                                <td>
                                    <a href="{{ route($detailRoute, ['docNo' => $document['doc_no'], 'tab' => $activeTab]) }}" class="btn btn-outline-primary btn-sm">
                                        보기
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center">{{ $tabs[$activeTab] }} 문서가 없습니다.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-pagination :paginator="$documents" />
        </div>
    </div>
</div>

