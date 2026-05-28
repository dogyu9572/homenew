document.addEventListener('DOMContentLoaded', function () {
    if (typeof jQuery === 'undefined') {
        return;
    }

    const $ = jQuery;

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    function jsonHeaders() {
        return {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            Accept: 'application/json',
        };
    }

    $(document).on('change', '#select-all', function () {
        $('.bo-row-checkbox').prop('checked', $(this).prop('checked'));
    });

    $(document).on('change', '.bo-row-checkbox', function () {
        const total = $('.bo-row-checkbox').length;
        const checked = $('.bo-row-checkbox:checked').length;
        $('#select-all').prop('checked', total > 0 && total === checked);
    });

    $(document).on('click', '#btnDeleteMultiple', function () {
        const ids = $('.bo-row-checkbox:checked').map(function () { return $(this).val(); }).get();
        if (ids.length === 0) {
            alert('삭제할 프로젝트를 선택해주세요.');
            return;
        }

        if (!confirm(`선택한 ${ids.length}건을 삭제하시겠습니까?`)) {
            return;
        }

        fetch('/backoffice/project-manages/delete-multiple', {
            method: 'POST',
            headers: jsonHeaders(),
            body: JSON.stringify({ ids, _token: csrfToken() }),
        }).then(function () {
            window.location.reload();
        });
    });

    $(document).on('click', '#btnExport', function () {
        const form = document.getElementById('searchForm');
        if (!form) {
            window.location.href = '/backoffice/project-manages/export';
            return;
        }

        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        window.location.href = '/backoffice/project-manages/export?' + params.toString();
    });
});

