document.addEventListener('click', function (event) {
    const removeButton = event.target.closest('.btn-remove-existing-attachment');
    if (!removeButton) {
        return;
    }

    const item = removeButton.closest('.existing-file');
    if (!item) {
        return;
    }

    item.remove();
});

