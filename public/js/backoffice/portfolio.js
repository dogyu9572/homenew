document.addEventListener('DOMContentLoaded', () => {
  const reviewsWrap = document.getElementById('reviewsWrap');
  const addBtn = document.getElementById('addReviewBtn');
  if (!reviewsWrap || !addBtn) return;

  const bindRemove = (container) => {
    container.querySelectorAll('.remove-review').forEach((btn) => {
      btn.onclick = () => {
        const rows = reviewsWrap.querySelectorAll('.review-row');
        if (rows.length <= 1) return;
        btn.closest('.review-row')?.remove();
      };
    });
  };

  addBtn.addEventListener('click', () => {
    const idx = reviewsWrap.querySelectorAll('.review-row').length;
    const row = document.createElement('div');
    row.className = 'review-row';
    row.innerHTML = `
      <div class="review-row-grid">
        <input type="text" class="board-form-control" name="reviews[${idx}][title]" placeholder="제목">
        <input type="text" class="board-form-control" name="reviews[${idx}][manager_name]" placeholder="담당자명">
      </div>
      <textarea class="board-form-control board-textarea" name="reviews[${idx}][content]" rows="3" placeholder="내용 (strong 태그 가능)"></textarea>
      <button type="button" class="btn btn-danger btn-sm remove-review">리뷰 삭제</button>
    `;
    reviewsWrap.appendChild(row);
    bindRemove(row);
  });

  bindRemove(reviewsWrap);
});

