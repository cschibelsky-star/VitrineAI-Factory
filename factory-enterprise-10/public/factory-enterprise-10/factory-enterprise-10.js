document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.fe-card').forEach((card, index) => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(10px)';
    setTimeout(() => {
      card.style.transition = 'opacity .28s ease, transform .28s ease';
      card.style.opacity = '1';
      card.style.transform = 'translateY(0)';
    }, 40 * index);
  });

  document.addEventListener('keydown', (event) => {
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
      event.preventDefault();
      const search = document.querySelector('.fe-search input');
      if (search) search.focus();
    }
  });
});
