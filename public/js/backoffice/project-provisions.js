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
