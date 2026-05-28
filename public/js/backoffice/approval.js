document.addEventListener('DOMContentLoaded', function () {
    function initVacationDayCalculator() {
        var startInput = document.querySelector('[data-approval-start-date]');
        var endInput = document.querySelector('[data-approval-end-date]');
        var daysInput = document.querySelector('[data-approval-days-input]');
        var daysDisplay = document.querySelector('[data-approval-days-display]');
        var hourOnlyMode = startInput && startInput.hasAttribute('data-hour-only');
        var quarterStartDate = document.querySelector('[data-quarter-date="start"]');
        var quarterStartHour = document.querySelector('[data-quarter-hour="start"]');
        var quarterEndDate = document.querySelector('[data-quarter-date="end"]');
        var quarterEndHour = document.querySelector('[data-quarter-hour="end"]');

        if (!startInput || !endInput || !daysInput || !daysDisplay) {
            return;
        }

        function normalizeHourValue(value) {
            if (!value || value.indexOf('T') === -1) {
                return value;
            }
            return value.slice(0, 13) + ':00';
        }

        function buildHourValue(dateValue, hourValue) {
            if (!dateValue || !hourValue) {
                return '';
            }
            return dateValue + 'T' + hourValue + ':00';
        }

        function syncQuarterDateTimeInputs() {
            if (!hourOnlyMode) {
                return;
            }
            if (quarterStartDate && quarterStartHour) {
                startInput.value = buildHourValue(quarterStartDate.value, quarterStartHour.value);
            }
            if (quarterEndDate && quarterEndHour) {
                endInput.value = buildHourValue(quarterEndDate.value, quarterEndHour.value);
            }
        }

        function toDate(value, isHourOnly) {
            if (!value) {
                return null;
            }
            var parsed = isHourOnly ? new Date(value) : new Date(value + 'T00:00:00');
            if (Number.isNaN(parsed.getTime())) {
                return null;
            }
            return parsed;
        }

        function calculateDays() {
            if (hourOnlyMode) {
                syncQuarterDateTimeInputs();
            }

            var startValue = startInput.value;
            var endValue = endInput.value;
            var days = 0;

            if (startValue && endValue) {
                var startDate = toDate(startValue, hourOnlyMode);
                var endDate = toDate(endValue, hourOnlyMode);

                if (startDate && endDate && endDate >= startDate) {
                    var oneDayMs = 24 * 60 * 60 * 1000;
                    days = Math.floor((endDate - startDate) / oneDayMs) + 1;
                }
            }

            daysInput.value = String(days);
            daysDisplay.textContent = String(days);
        }

        if (quarterStartDate && quarterStartHour && quarterEndDate && quarterEndHour) {
            quarterStartDate.addEventListener('change', calculateDays);
            quarterStartHour.addEventListener('change', calculateDays);
            quarterEndDate.addEventListener('change', calculateDays);
            quarterEndHour.addEventListener('change', calculateDays);
        } else {
            startInput.addEventListener('change', calculateDays);
            endInput.addEventListener('change', calculateDays);
        }

        if (hourOnlyMode && !(quarterStartDate && quarterStartHour && quarterEndDate && quarterEndHour)) {
            startInput.addEventListener('input', function () {
                startInput.value = normalizeHourValue(startInput.value);
            });
            endInput.addEventListener('input', function () {
                endInput.value = normalizeHourValue(endInput.value);
            });
        }

        calculateDays();
    }

    initVacationDayCalculator();

    function initDraftSubmitGuard() {
        var draftForm = document.getElementById('approvalDraftForm');
        if (!draftForm) {
            return;
        }

        var submitButton = draftForm.querySelector('[data-approval-submit]');
        var submitting = false;

        draftForm.addEventListener('submit', function (event) {
            if (submitting) {
                event.preventDefault();
                return;
            }

            var approvalInputs = draftForm.querySelectorAll('input[name="approval_user_ids[]"]');
            if (!approvalInputs.length) {
                event.preventDefault();
                window.alert('결재자를 1명 이상 지정해주세요.');
                return;
            }

            if (typeof draftForm.checkValidity === 'function' && !draftForm.checkValidity()) {
                return;
            }

            submitting = true;
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = '상신중...';
            }
        });
    }

    initDraftSubmitGuard();

    var box = document.querySelector('[data-approval-box]');
    if (box) {
        // 탭 버튼 접근성 보강: 현재 탭을 aria-current로 표시
        var tabButtons = box.querySelectorAll('[data-approval-tab]');
        tabButtons.forEach(function (button) {
            var isActive = button.classList.contains('btn-primary');
            if (isActive) {
                button.setAttribute('aria-current', 'page');
            }
        });
    }

    var root = document.querySelector('[data-approver-users-endpoint]');
    if (!root) {
        return;
    }

    var usersEndpoint = root.getAttribute('data-approver-users-endpoint');
    var requesterName = (root.getAttribute('data-requester-name') || '').trim();
    var modal = document.getElementById('approverModal');
    var listBody = document.getElementById('approverListBody');
    var keywordInput = document.getElementById('approverKeyword');
    var searchBtn = document.getElementById('approverSearchBtn');
    var currentLineType = null;
    var approvalSimulationState = {
        approval1: { name: requesterName, approvedAt: '' },
        approval2: { name: '', approvedAt: '' },
        approval3: { name: '', approvedAt: '' },
        approval4: { name: '', approvedAt: '' },
        approval5: { name: '', approvedAt: '' },
        cooperation1: { name: '', approvedAt: '' },
    };

    if (!usersEndpoint || !modal || !listBody || !keywordInput || !searchBtn) {
        return;
    }

    function renderEmpty(message) {
        listBody.innerHTML = '<tr><td colspan="5" class="text-center">' + message + '</td></tr>';
    }

    function renderUsers(users) {
        if (!users.length) {
            renderEmpty('검색 결과가 없습니다.');
            return;
        }

        var rows = users.map(function (user) {
            return (
                '<tr>' +
                '<td>' + user.name + '</td>' +
                '<td>' + user.department + '</td>' +
                '<td>' + user.position + '</td>' +
                '<td>' + user.login_id + '</td>' +
                '<td><button type="button" class="btn btn-primary btn-sm" data-approver-add ' +
                'data-user-id="' + user.id + '" ' +
                'data-user-name="' + user.name + '" ' +
                'data-user-department="' + user.department + '" ' +
                'data-user-position="' + user.position + '">' +
                '추가</button></td>' +
                '</tr>'
            );
        }).join('');

        listBody.innerHTML = rows;
    }

    function fetchUsers() {
        var keyword = keywordInput.value.trim();
        renderEmpty('목록을 불러오는 중입니다.');

        var url = new URL(usersEndpoint, window.location.origin);
        if (keyword !== '') {
            url.searchParams.set('keyword', keyword);
        }

        fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then(function (res) {
                return res.json();
            })
            .then(function (data) {
                renderUsers(data.users || []);
            })
            .catch(function () {
                renderEmpty('목록을 불러오지 못했습니다.');
            });
    }

    function openModal(lineType) {
        currentLineType = lineType;
        modal.hidden = false;
        fetchUsers();
    }

    function closeModal() {
        modal.hidden = true;
        currentLineType = null;
        keywordInput.value = '';
        renderEmpty('목록을 불러오려면 지정 버튼을 눌러주세요.');
    }

    function addApprover(userData) {
        if (!currentLineType) {
            return;
        }

        var target = root.querySelector('[data-line-target="' + currentLineType + '"]');
        if (!target) {
            return;
        }

        var exists = target.querySelector('[data-user-id="' + userData.id + '"]');
        if (exists) {
            return;
        }

        if (currentLineType === 'approval') {
            var currentApprovers = target.querySelectorAll('.approval-line-chip');
            if (currentApprovers.length >= 4) {
                window.alert('결재자는 최대 4명까지 지정할 수 있습니다.');
                return;
            }
        }

        var chip = document.createElement('span');
        chip.className = 'approval-line-chip';
        chip.setAttribute('data-user-id', userData.id);
        chip.innerHTML =
            '<span class="approval-line-chip-name">' + userData.name + '</span>' +
            '<span class="approval-line-chip-meta">(' + userData.department + ' / ' + userData.position + ')</span>' +
            '<button type="button" class="approval-line-chip-remove" data-approver-remove aria-label="삭제">×</button>';
        target.appendChild(chip);
        syncSignoffNames(currentLineType);
        closeModal();
    }

    function syncSignoffNames(lineType) {
        var selectedChips = root.querySelectorAll('[data-line-target="' + lineType + '"] .approval-line-chip');
        var selected = root.querySelectorAll('[data-line-target="' + lineType + '"] .approval-line-chip-name');
        if (lineType === 'approval') {
            approvalSimulationState.approval1.name = requesterName;
            approvalSimulationState.approval2.name = selected[0] ? selected[0].textContent.trim() : '';
            approvalSimulationState.approval3.name = selected[1] ? selected[1].textContent.trim() : '';
            approvalSimulationState.approval4.name = selected[2] ? selected[2].textContent.trim() : '';
            approvalSimulationState.approval5.name = selected[3] ? selected[3].textContent.trim() : '';
            syncLineInputs('approvalLineInputs', 'approval_user_ids[]', selectedChips);
        } else if (lineType === 'cooperation') {
            var coop = selected[0] ? selected[0].textContent.trim() : '';
            approvalSimulationState.cooperation1.name = coop || '';
            syncLineInputs('cooperationLineInputs', 'cooperation_user_ids[]', selectedChips);
        }
        renderSignoffState();
    }

    function syncLineInputs(containerId, inputName, chips) {
        var container = document.getElementById(containerId);
        if (!container) {
            return;
        }
        container.innerHTML = '';
        chips.forEach(function (chip) {
            var userId = chip.getAttribute('data-user-id');
            if (!userId) {
                return;
            }
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = inputName;
            input.value = userId;
            container.appendChild(input);
        });
    }

    function clearApproverLine(lineType) {
        var target = root.querySelector('[data-line-target="' + lineType + '"]');
        if (!target) {
            return;
        }
        target.innerHTML = '';
        syncSignoffNames(lineType);
    }

    function renderSignoffState() {
        [
            { slot: 'approval-1', stateKey: 'approval1', requester: true },
            { slot: 'approval-2', stateKey: 'approval2', requester: false },
            { slot: 'approval-3', stateKey: 'approval3', requester: false },
            { slot: 'approval-4', stateKey: 'approval4', requester: false },
            { slot: 'approval-5', stateKey: 'approval5', requester: false },
            { slot: 'cooperation-1', stateKey: 'cooperation1' },
        ].forEach(function (item) {
            var slot = root.querySelector('[data-sign-slot="' + item.slot + '"]');
            if (!slot) {
                return;
            }
            var nameEl = slot.querySelector('.approval-signoff-name');
            var dateEl = slot.querySelector('.approval-signoff-date');
            if (nameEl) {
                nameEl.textContent = approvalSimulationState[item.stateKey].name;
            }
            if (dateEl) {
                dateEl.textContent = approvalSimulationState[item.stateKey].approvedAt;
            }
            var approved = Boolean((approvalSimulationState[item.stateKey].approvedAt || '').trim());
            slot.classList.toggle('is-requester', Boolean(item.requester));
            slot.classList.toggle('is-approved', approved && !item.requester);
        });
    }

    document.addEventListener('click', function (event) {
        var openBtn = event.target.closest('[data-approver-open]');
        if (openBtn) {
            event.preventDefault();
            openModal(openBtn.getAttribute('data-line-type'));
            return;
        }

        var closeBtn = event.target.closest('[data-approver-close]');
        if (closeBtn) {
            event.preventDefault();
            closeModal();
            return;
        }

        var addBtn = event.target.closest('[data-approver-add]');
        if (addBtn) {
            event.preventDefault();
            addApprover({
                id: addBtn.getAttribute('data-user-id'),
                name: addBtn.getAttribute('data-user-name'),
                department: addBtn.getAttribute('data-user-department'),
                position: addBtn.getAttribute('data-user-position'),
            });
            return;
        }

        var clearBtn = event.target.closest('[data-approver-clear]');
        if (clearBtn) {
            event.preventDefault();
            clearApproverLine(clearBtn.getAttribute('data-line-type'));
            return;
        }

        var removeBtn = event.target.closest('[data-approver-remove]');
        if (removeBtn) {
            event.preventDefault();
            var chip = removeBtn.closest('.approval-line-chip');
            if (chip) {
                var lineTarget = chip.closest('[data-line-target]');
                chip.remove();
                if (lineTarget) {
                    syncSignoffNames(lineTarget.getAttribute('data-line-target'));
                }
            }
            return;
        }

        var closeByBackdrop = event.target === modal;
        if (closeByBackdrop) {
            closeModal();
            return;
        }
    });

    renderSignoffState();

    searchBtn.addEventListener('click', fetchUsers);
    keywordInput.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            fetchUsers();
        }
    });

    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    document.querySelectorAll('[data-approval-file-input]').forEach(function (input) {
        input.addEventListener('change', function () {
            var targetId = input.getAttribute('data-preview-target');
            var preview = targetId ? document.getElementById(targetId) : null;
            if (!preview) {
                return;
            }

            if (!input.files || !input.files.length) {
                preview.innerHTML = '';
                return;
            }

            var names = Array.from(input.files).map(function (file) {
                return (
                    '<div class="board-attachment-item">' +
                    '<i class="fas fa-file"></i>' +
                    '<span class="board-attachment-name">' + file.name + '</span>' +
                    '</div>'
                );
            }).join('');

            preview.innerHTML = '<div class="board-attachment-list">' + names + '</div>';
        });
    });
});

