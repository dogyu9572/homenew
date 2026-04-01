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
      const subheading = row.querySelector('input[name*="[subheading]"]');
      const content = row.querySelector('textarea[name*="[content]"]');
      if (subtitle) {
        subtitle.name = `sections[${index}][subtitle]`;
      }
      if (subheading) {
        subheading.name = `sections[${index}][subheading]`;
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
        <input type="text" class="board-form-control" name="sections[${rows.length}][subtitle]" placeholder="목차">
        <input type="text" class="board-form-control" name="sections[${rows.length}][subheading]" placeholder="소제목">
      </div>
      <textarea class="board-form-control board-textarea" name="sections[${rows.length}][content]" rows="5" placeholder="본문" data-backoffice-ckeditor data-source-editing="true"></textarea>
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

  const initBlogFaqPicker = () => {
    const titlesEl = document.getElementById('blog-faq-titles-json');
    const initialEl = document.getElementById('blog-faq-initial-ids');
    const listEl = document.getElementById('blogFaqSelectedList');
    const hiddenWrap = document.getElementById('blogFaqHiddenInputs');
    const addSelect = document.getElementById('blogFaqAddSelect');
    const addBtn = document.getElementById('blogFaqAddBtn');
    if (!titlesEl || !listEl || !hiddenWrap || !addSelect || !addBtn) {
      return;
    }

    let titles = {};
    try {
      titles = JSON.parse(titlesEl.textContent || '{}');
    } catch {
      titles = {};
    }
    let order = [];
    try {
      order = JSON.parse(initialEl?.textContent || '[]');
    } catch {
      order = [];
    }
    if (!Array.isArray(order)) {
      order = [];
    }
    order = order.map((id) => parseInt(String(id), 10)).filter((id) => id > 0);

    const escapeHtml = (s) => {
      const div = document.createElement('div');
      div.textContent = s;
      return div.innerHTML;
    };

    const syncHidden = () => {
      hiddenWrap.innerHTML = '';
      order.forEach((id) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'faq_board_post_ids[]';
        input.value = String(id);
        hiddenWrap.appendChild(input);
      });
    };

    const renderList = () => {
      listEl.innerHTML = '';
      order.forEach((id, idx) => {
        const rawTitle = titles[id] != null ? titles[id] : `FAQ #${id}`;
        const title = escapeHtml(String(rawTitle));
        const li = document.createElement('li');
        li.className = 'blog-faq-selected-item';
        li.innerHTML = `
          <span class="blog-faq-selected-title">${title}</span>
          <span class="blog-faq-selected-actions">
            <button type="button" class="btn btn-secondary btn-sm blog-faq-up" ${idx === 0 ? 'disabled' : ''} aria-label="위로">↑</button>
            <button type="button" class="btn btn-secondary btn-sm blog-faq-down" ${idx === order.length - 1 ? 'disabled' : ''} aria-label="아래로">↓</button>
            <button type="button" class="btn btn-danger btn-sm blog-faq-remove" aria-label="제거">삭제</button>
          </span>
        `;
        li.querySelector('.blog-faq-up').addEventListener('click', () => {
          if (idx <= 0) {
            return;
          }
          const t = order[idx - 1];
          order[idx - 1] = order[idx];
          order[idx] = t;
          renderList();
        });
        li.querySelector('.blog-faq-down').addEventListener('click', () => {
          if (idx >= order.length - 1) {
            return;
          }
          const t = order[idx + 1];
          order[idx + 1] = order[idx];
          order[idx] = t;
          renderList();
        });
        li.querySelector('.blog-faq-remove').addEventListener('click', () => {
          order = order.filter((x) => x !== id);
          renderList();
        });
        listEl.appendChild(li);
      });
      syncHidden();
    };

    addBtn.addEventListener('click', () => {
      const val = addSelect.value;
      if (!val) {
        return;
      }
      const id = parseInt(val, 10);
      if (!id || order.includes(id)) {
        return;
      }
      if (order.length >= 20) {
        alert('FAQ는 최대 20개까지 선택할 수 있습니다.');
        return;
      }
      order.push(id);
      addSelect.value = '';
      renderList();
    });

    renderList();
  };

  initBlogFaqPicker();
});
