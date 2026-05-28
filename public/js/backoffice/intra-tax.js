document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('select-all');
    const checkboxes = Array.from(document.querySelectorAll('.bo-row-checkbox'));
    const deleteBtn = document.getElementById('btnDeleteMultiple');
    const endpoint = document.querySelector('.board-container')?.dataset.deleteMultipleEndpoint || '';
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checkboxes.forEach((cb) => {
                cb.checked = this.checked;
            });
        });
    }

    if (!deleteBtn || endpoint === '') {
        // continue
    }

    if (deleteBtn && endpoint !== '') {
        deleteBtn.addEventListener('click', function () {
            const ids = checkboxes.filter((cb) => cb.checked).map((cb) => cb.value);
            if (ids.length === 0) {
                alert('선택된 게시글이 없습니다.');
                return;
            }
            if (!confirm('선택한 게시글을 삭제하시겠습니까?')) {
                return;
            }

            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ ids: ids }),
            })
                .then((res) => res.json())
                .then((json) => {
                    if (!json.success) {
                        alert(json.message || '삭제에 실패했습니다.');
                        return;
                    }
                    window.location.reload();
                });
        });
    }

    document.querySelectorAll('.js-secret-post-link').forEach((link) => {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            const unlockUrl = this.dataset.unlockUrl || '';
            if (unlockUrl === '') {
                return;
            }
            const password = window.prompt('비밀글 비밀번호를 입력하세요.');
            if (password === null || password === '') {
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = unlockUrl;

            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = csrf;
            form.appendChild(tokenInput);

            const pwInput = document.createElement('input');
            pwInput.type = 'hidden';
            pwInput.name = 'password';
            pwInput.value = password;
            form.appendChild(pwInput);

            const container = document.querySelector('.board-container');
            let listQuery = {};
            if (container?.dataset?.listQuery) {
                try {
                    listQuery = JSON.parse(container.dataset.listQuery);
                } catch (e) {
                    listQuery = {};
                }
            }
            if (listQuery && typeof listQuery === 'object') {
                Object.entries(listQuery).forEach(([key, val]) => {
                    if (val === null || val === undefined || val === '') {
                        return;
                    }
                    const ret = document.createElement('input');
                    ret.type = 'hidden';
                    ret.name = 'return_' + key;
                    ret.value = String(val);
                    form.appendChild(ret);
                });
            }

            document.body.appendChild(form);
            form.submit();
        });
    });
});

