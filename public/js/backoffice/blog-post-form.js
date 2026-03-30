document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('blogPostForm');
  if (!form) {
    return;
  }

  const initSingleFileUpload = (inputId, previewId, maxFiles) => {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    const upload = input?.closest('.board-file-upload');
    if (!input || !preview || !upload) {
      return;
    }

    const updatePreview = () => {
      const files = Array.from(input.files || []).slice(0, maxFiles);
      preview.innerHTML = '';

      files.forEach((file) => {
        const fileItem = document.createElement('div');
        fileItem.className = 'board-file-item';
        fileItem.innerHTML = `
          <div class="board-file-info">
            <i class="fas fa-file-image"></i>
            <span class="board-file-name">${file.name}</span>
            <span class="board-file-size">(${(file.size / 1024 / 1024).toFixed(2)}MB)</span>
          </div>
        `;
        preview.appendChild(fileItem);
      });
    };

    input.addEventListener('change', updatePreview);

    upload.addEventListener('dragover', (event) => {
      event.preventDefault();
      upload.classList.add('board-file-drag-over');
    });

    upload.addEventListener('dragleave', () => {
      upload.classList.remove('board-file-drag-over');
    });

    upload.addEventListener('drop', (event) => {
      event.preventDefault();
      upload.classList.remove('board-file-drag-over');
      const droppedFiles = Array.from(event.dataTransfer?.files || []).slice(0, maxFiles);
      const dt = new DataTransfer();
      droppedFiles.forEach((f) => dt.items.add(f));
      input.files = dt.files;
      updatePreview();
    });
  };

  initSingleFileUpload('blog_post_thumbnail', 'blogPostThumbnailPreview', 1);

  const sectionsWrap = document.getElementById('blogSectionsWrap');
  const addSectionButton = document.getElementById('addBlogSectionBtn');
  const maxSections = 10;

  const updateSectionIndexes = () => {
    const rows = sectionsWrap.querySelectorAll('.blog-section-row');
    rows.forEach((row, index) => {
      const subtitle = row.querySelector('input[name*="[subtitle]"]');
      const content = row.querySelector('textarea[name*="[content]"]');
      if (subtitle) {
        subtitle.name = `sections[${index}][subtitle]`;
      }
      if (content) {
        content.name = `sections[${index}][content]`;
      }
    });
  };

  const bindSectionRemove = (target = sectionsWrap) => {
    target.querySelectorAll('.remove-blog-section').forEach((button) => {
      button.onclick = () => {
        const rows = sectionsWrap.querySelectorAll('.blog-section-row');
        if (rows.length <= 1) {
          return;
        }
        button.closest('.blog-section-row')?.remove();
        updateSectionIndexes();
      };
    });
  };

  addSectionButton?.addEventListener('click', () => {
    const rows = sectionsWrap.querySelectorAll('.blog-section-row');
    if (rows.length >= maxSections) {
      alert('목차·본문 구간은 최대 10개까지 추가할 수 있습니다.');
      return;
    }

    const row = document.createElement('div');
    row.className = 'review-row blog-section-row';
    row.innerHTML = `
      <div class="review-row-grid">
        <input type="text" class="board-form-control" name="sections[${rows.length}][subtitle]" placeholder="목차 제목 (CONTENTS에 표시)">
      </div>
      <textarea class="board-form-control board-textarea" name="sections[${rows.length}][content]" rows="5" placeholder="본문 (해당 위치에 출력)" data-backoffice-ckeditor data-source-editing="true"></textarea>
      <button type="button" class="btn btn-danger btn-sm remove-blog-section">구간 삭제</button>
    `;
    sectionsWrap.appendChild(row);
    if (typeof window.initBackofficeCKEditors === 'function') {
      window.initBackofficeCKEditors(row);
    }
    bindSectionRemove(row);
  });

  if (typeof window.initBackofficeCKEditors === 'function') {
    window.initBackofficeCKEditors(form);
  }

  form.addEventListener('submit', () => {
    if (typeof window.syncBackofficeCKEditorFields === 'function') {
      window.syncBackofficeCKEditorFields(form);
    }
  });

  bindSectionRemove();
});
