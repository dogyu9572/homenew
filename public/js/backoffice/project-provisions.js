document.addEventListener('DOMContentLoaded', function () {
    var root = document.getElementById('project-provision-show');
    if (!root) {
        return;
    }

    var status = root.getAttribute('data-status');
    if (status !== 'queued' && status !== 'running') {
        return;
    }

    window.setTimeout(function () {
        window.location.reload();
    }, 5000);
});

(function () {
    var openButton = document.getElementById('developer-memo-open');
    var copyButton = document.getElementById('developer-memo-copy');
    var panel = document.getElementById('developer-memo-panel');
    var field = document.getElementById('developer-memo-text');
    if (!openButton || !copyButton || !panel || !field) {
        return;
    }

    function copyMemo() {
        var memo = field.value;
        field.focus();
        field.select();
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(memo).then(function () {
                copyButton.textContent = '복사했습니다';
                window.setTimeout(function () {
                    copyButton.textContent = '복사';
                }, 1500);
            }).catch(function () {
                document.execCommand('copy');
            });
            return;
        }
        document.execCommand('copy');
    }

    openButton.addEventListener('click', function () {
        panel.hidden = false;
        copyMemo();
    });
    copyButton.addEventListener('click', copyMemo);
})();
