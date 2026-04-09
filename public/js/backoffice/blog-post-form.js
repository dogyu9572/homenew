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
  const maxTocBlocks = 10;
  const maxItemsPerToc = 10;

  const reindexBlogSectionNames = () => {
    if (!sectionsWrap) {
      return;
    }
    const tocBlocks = sectionsWrap.querySelectorAll('[data-blog-toc-block]');
    tocBlocks.forEach((tocBlock, tocIndex) => {
      const subtitle = tocBlock.querySelector('input[name*="[subtitle]"]');
      if (subtitle) {
        subtitle.name = `sections[${tocIndex}][subtitle]`;
      }
      const itemRows = tocBlock.querySelectorAll('[data-blog-section-item-row]');
      itemRows.forEach((itemRow, itemIndex) => {
        const subheading = itemRow.querySelector('input[name*="[subheading]"]');
        const content = itemRow.querySelector('textarea[name*="[content]"]');
        if (subheading) {
          subheading.name = `sections[${tocIndex}][items][${itemIndex}][subheading]`;
        }
        if (content) {
          content.name = `sections[${tocIndex}][items][${itemIndex}][content]`;
        }
      });
    });
  };

  const createEmptyItemRowHtml = (tocIndex, itemIndex) => `
      <div class="review-row blog-section-item-row" data-blog-section-item-row>
        <div class="review-row-grid">
          <input type="text" class="board-form-control" name="sections[${tocIndex}][items][${itemIndex}][subheading]" placeholder="소제목">
        </div>
        <textarea class="board-form-control board-textarea" name="sections[${tocIndex}][items][${itemIndex}][content]" rows="5" placeholder="본문" data-backoffice-ckeditor data-source-editing="true"></textarea>
        <button type="button" class="btn btn-danger btn-sm remove-blog-section-item">블록 삭제</button>
      </div>
    `;

  if (sectionsWrap) {
    sectionsWrap.addEventListener('click', (e) => {
      const removeItemBtn = e.target.closest('.remove-blog-section-item');
      if (removeItemBtn) {
        const wrap = removeItemBtn.closest('[data-blog-section-items-wrap]');
        const rows = wrap?.querySelectorAll('[data-blog-section-item-row]');
        if (!wrap || !rows || rows.length <= 1) {
          return;
        }
        removeItemBtn.closest('[data-blog-section-item-row]')?.remove();
        reindexBlogSectionNames();
        return;
      }

      const removeTocBtn = e.target.closest('.remove-blog-toc');
      if (removeTocBtn) {
        if (sectionsWrap.querySelectorAll('[data-blog-toc-block]').length <= 1) {
          return;
        }
        removeTocBtn.closest('[data-blog-toc-block]')?.remove();
        reindexBlogSectionNames();
        return;
      }

      const addItemBtn = e.target.closest('.add-blog-section-item');
      if (addItemBtn) {
        const tocBlock = addItemBtn.closest('[data-blog-toc-block]');
        const wrap = tocBlock?.querySelector('[data-blog-section-items-wrap]');
        if (!tocBlock || !wrap) {
          return;
        }
        const rows = wrap.querySelectorAll('[data-blog-section-item-row]');
        if (rows.length >= maxItemsPerToc) {
          alert('목차 하나당 소제목·본문 블록은 최대 10개까지 추가할 수 있습니다.');
          return;
        }
        const tocIndex = [...sectionsWrap.querySelectorAll('[data-blog-toc-block]')].indexOf(tocBlock);
        const itemIndex = rows.length;
        const div = document.createElement('div');
        div.innerHTML = createEmptyItemRowHtml(tocIndex, itemIndex).trim();
        const newRow = div.firstElementChild;
        wrap.appendChild(newRow);
        if (typeof window.initBackofficeCKEditors === 'function') {
          window.initBackofficeCKEditors(newRow);
        }
        reindexBlogSectionNames();
      }
    });

    addSectionButton?.addEventListener('click', () => {
      const tocBlocks = sectionsWrap.querySelectorAll('[data-blog-toc-block]');
      if (tocBlocks.length >= maxTocBlocks) {
        alert('목차는 최대 10개까지 추가할 수 있습니다.');
        return;
      }
      const tocIndex = tocBlocks.length;
      const block = document.createElement('div');
      block.className = 'review-row blog-toc-block';
      block.setAttribute('data-blog-toc-block', '');
      block.innerHTML = `
      <div class="review-row-grid">
        <input type="text" class="board-form-control" name="sections[${tocIndex}][subtitle]" placeholder="목차">
      </div>
      <div class="blog-section-items-wrap" data-blog-section-items-wrap>
        ${createEmptyItemRowHtml(tocIndex, 0).trim()}
      </div>
      <div class="member-form-field">
        <button type="button" class="btn btn-secondary btn-sm add-blog-section-item">소제목·본문 블록 추가</button>
        <button type="button" class="btn btn-danger btn-sm remove-blog-toc">목차 삭제</button>
      </div>
    `;
      sectionsWrap.appendChild(block);
      if (typeof window.initBackofficeCKEditors === 'function') {
        window.initBackofficeCKEditors(block);
      }
      reindexBlogSectionNames();
    });
  }

  if (typeof window.initBackofficeCKEditors === 'function') {
    window.initBackofficeCKEditors(form);
  }

  const slugPattern = /^[a-z0-9]+(?:-[a-z0-9]+)*$/;
  const slugInvalidMsg =
    'URL 슬러그는 영문 소문자, 숫자, 하이픈(-)만 사용할 수 있습니다.\n(한글·공백·특수문자·언더스코어(_)는 사용할 수 없습니다.)';

  form.addEventListener('submit', (e) => {
    const slugInput = form.querySelector('#blogPostSlugInput, input[name="slug"]');
    const raw = slugInput?.value?.trim() ?? '';
    if (raw !== '' && !slugPattern.test(raw)) {
      e.preventDefault();
      alert(slugInvalidMsg);
      slugInput?.focus();
      return;
    }
    if (typeof window.syncBackofficeCKEditorFields === 'function') {
      window.syncBackofficeCKEditorFields(form);
    }
  });

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
