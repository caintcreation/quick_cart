'use strict';
const searchInput = document.querySelector('#search');
const cards = [...document.querySelectorAll('.product-card')];
const emptyState = document.querySelector('#emptyState');
searchInput?.addEventListener('input', () => {
  const term = searchInput.value.trim().toLowerCase();
  let visible = 0;
  cards.forEach(card => {
    const match = (card.dataset.search || '').includes(term);
    card.hidden = !match;
    if (match) visible++;
  });
  if (emptyState) emptyState.hidden = visible !== 0;
});
