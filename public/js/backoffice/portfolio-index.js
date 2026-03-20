document.addEventListener('DOMContentLoaded', () => {
  const selectAll = document.getElementById('select-all');
  const itemCheckboxes = Array.from(document.querySelectorAll('.portfolio-checkbox'));
  const bulkDeleteBtn = document.getElementById('btnDeleteMultiple');
  const bulkDeleteForm = document.getElementById('bulkDeleteForm');
  const singleDeleteForm = document.getElementById('singleDeleteForm');

  if (selectAll) {
    selectAll.addEventListener('change', () => {
      itemCheckboxes.forEach((checkbox) => {
        checkbox.checked = selectAll.checked;
      });
    });
  }

  itemCheckboxes.forEach((checkbox) => {
    checkbox.addEventListener('change', () => {
      const checkedCount = itemCheckboxes.filter((item) => item.checked).length;
      if (selectAll) {
        selectAll.checked = itemCheckboxes.length > 0 && checkedCount === itemCheckboxes.length;
      }
    });
  });

  if (bulkDeleteBtn && bulkDeleteForm) {
    bulkDeleteBtn.addEventListener('click', () => {
      const checkedCount = itemCheckboxes.filter((item) => item.checked).length;
      if (checkedCount === 0) {
        alert('삭제할 포트폴리오를 선택해주세요.');
        return;
      }

      if (!confirm(`선택한 ${checkedCount}개의 포트폴리오를 삭제하시겠습니까?`)) {
        return;
      }

      bulkDeleteForm.submit();
    });
  }

  document.querySelectorAll('.btn-delete-single').forEach((btn) => {
    btn.addEventListener('click', () => {
      const action = btn.getAttribute('data-action');
      if (!action || !singleDeleteForm) {
        return;
      }

      if (!confirm('삭제하시겠습니까?')) {
        return;
      }

      singleDeleteForm.setAttribute('action', action);
      singleDeleteForm.submit();
    });
  });
});
