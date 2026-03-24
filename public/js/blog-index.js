document.addEventListener('DOMContentLoaded', () => {
  if (typeof AOS !== 'undefined' && typeof AOS.init === 'function') {
    AOS.init({
      duration: 2000,
    });
  }
});
