/**
 * 백오피스 헤더 출퇴근 빠른 등록 (근무지 선택 후 API 저장)
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var cfg = document.getElementById('backofficeAttendanceConfig');
        var btnIn = document.getElementById('btnAttendanceClockIn');
        var btnOut = document.getElementById('btnAttendanceClockOut');
        var overlay = document.getElementById('attendanceQuickOverlay');
        var titleEl = document.getElementById('attendanceQuickTitle');
        var hintEl = document.getElementById('attendanceQuickHint');
        var btnCancel = document.getElementById('attendanceQuickCancel');
        var btnSubmit = document.getElementById('attendanceQuickSubmit');

        if (!cfg || !btnIn || !btnOut || !overlay || !titleEl || !hintEl || !btnCancel || !btnSubmit) {
            return;
        }

        var quickUrl = cfg.getAttribute('data-quick-store-url');
        if (!quickUrl) {
            return;
        }

        var pendingKind = null;

        function getCsrfToken() {
            var meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.getAttribute('content') : '';
        }

        function selectedWorkplace() {
            var el = overlay.querySelector('input[name="attendance_quick_workplace"]:checked');
            return el ? el.value : 'office';
        }

        function openOverlay(kind) {
            pendingKind = kind;
            if (kind === 'clock_in') {
                titleEl.textContent = '출근';
                hintEl.textContent = '근무지를 선택한 뒤 확인을 누르면 출근이 저장됩니다. (기본: 재택)';
            } else {
                titleEl.textContent = '퇴근';
                hintEl.textContent = '근무지를 선택한 뒤 확인을 누르면 퇴근이 저장됩니다. (기본: 재택)';
            }
            var remoteRadio = overlay.querySelector('input[name="attendance_quick_workplace"][value="remote"]');
            if (remoteRadio) {
                remoteRadio.checked = true;
            }
            overlay.removeAttribute('hidden');
        }

        function closeOverlay() {
            overlay.setAttribute('hidden', 'hidden');
            pendingKind = null;
        }

        function showAlert(message) {
            window.alert(message);
        }

        function postQuick() {
            var workplace = selectedWorkplace();
            btnSubmit.disabled = true;

            fetch(quickUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    kind: pendingKind,
                    workplace: workplace,
                }),
            })
                .then(function (res) {
                    return res.json().then(function (data) {
                        return { ok: res.ok, status: res.status, data: data };
                    });
                })
                .then(function (result) {
                    btnSubmit.disabled = false;
                    if (result.ok && result.data.success) {
                        closeOverlay();
                        showAlert(result.data.message || '저장되었습니다.');
                        return;
                    }
                    var msg =
                        (result.data && result.data.message) ||
                        (result.data && result.data.errors && Object.values(result.data.errors).flat().join('\n')) ||
                        '저장에 실패했습니다.';
                    showAlert(msg);
                })
                .catch(function () {
                    btnSubmit.disabled = false;
                    showAlert('요청 중 오류가 발생했습니다.');
                });
        }

        btnIn.addEventListener('click', function () {
            openOverlay('clock_in');
        });

        btnOut.addEventListener('click', function () {
            openOverlay('clock_out');
        });

        btnCancel.addEventListener('click', function () {
            closeOverlay();
        });

        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) {
                closeOverlay();
            }
        });

        btnSubmit.addEventListener('click', function () {
            if (!pendingKind) {
                return;
            }
            postQuick();
        });
    });
})();
