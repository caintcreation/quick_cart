/**
 * QuickCart Studio - Interactive Client Controller
 * Lightweight Vanilla JS: Theme switcher, mobile nav, live filtering, modals, image preview
 */

'use strict';

// 1. Theme Management (Light / Dark Mode with localStorage persistence)
(function initTheme() {
  const savedTheme = localStorage.getItem('qc_theme') || 
    (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
  
  document.documentElement.setAttribute('data-theme', savedTheme);

  document.addEventListener('DOMContentLoaded', () => {
    const themeToggles = document.querySelectorAll('.theme-toggle-btn');
    themeToggles.forEach(btn => {
      btn.addEventListener('click', () => {
        const current = document.documentElement.getAttribute('data-theme') || 'light';
        const next = current === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('qc_theme', next);
      });
    });
  });
})();

// 2. Mobile Navigation Toggle
document.addEventListener('DOMContentLoaded', () => {
  const mobileNavBtn = document.querySelector('.mobile-menu-btn');
  const siteNav = document.querySelector('.site-nav');
  if (mobileNavBtn && siteNav) {
    mobileNavBtn.addEventListener('click', () => {
      siteNav.classList.toggle('menu-open');
    });
  }

  // Admin Sidebar Mobile Toggle
  const adminMenuBtn = document.querySelector('.admin-menu-toggle');
  const adminSidebar = document.querySelector('.admin-sidebar');
  if (adminMenuBtn && adminSidebar) {
    adminMenuBtn.addEventListener('click', () => {
      adminSidebar.classList.toggle('open');
    });
  }

  // 3. Product Storefront Search & Category Filter
  const searchInput = document.querySelector('#productSearch');
  const categoryFilters = document.querySelectorAll('.category-filter-btn');
  const productCards = document.querySelectorAll('.catalog-card');
  const emptyState = document.querySelector('#productEmptyState');
  let activeCategory = 'all';

  function filterProducts() {
    const term = (searchInput ? searchInput.value : '').trim().toLowerCase();
    let visibleCount = 0;

    productCards.forEach(card => {
      const cardCategory = card.getAttribute('data-category') || '';
      const cardSearchText = (card.getAttribute('data-search') || '').toLowerCase();

      const matchesCategory = (activeCategory === 'all' || cardCategory === activeCategory);
      const matchesSearch = term === '' || cardSearchText.includes(term);

      if (matchesCategory && matchesSearch) {
        card.style.display = '';
        visibleCount++;
      } else {
        card.style.display = 'none';
      }
    });

    if (emptyState) {
      emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', filterProducts);
  }

  categoryFilters.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      categoryFilters.forEach(b => b.classList.remove('active', 'btn-primary'));
      btn.classList.add('active', 'btn-primary');
      btn.classList.remove('btn-secondary');
      activeCategory = btn.getAttribute('data-category-slug') || 'all';
      filterProducts();
    });
  });

  // 4. Image Upload Instant Preview
  const imageInputs = document.querySelectorAll('input[type="file"][data-preview]');
  imageInputs.forEach(input => {
    const previewSelector = input.getAttribute('data-preview');
    const previewEl = document.querySelector(previewSelector);
    if (previewEl) {
      input.addEventListener('change', () => {
        const file = input.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = (e) => {
            previewEl.src = e.target.result;
            previewEl.style.display = 'block';
          };
          reader.readAsDataURL(file);
        }
      });
    }
  });

  // 5. Password Visibility Toggle
  const togglePassBtns = document.querySelectorAll('.password-toggle-btn');
  togglePassBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const targetId = btn.getAttribute('data-target');
      const input = document.getElementById(targetId);
      if (input) {
        const isPass = input.type === 'password';
        input.type = isPass ? 'text' : 'password';
      }
    });
  });

  // 6. Dismissible Alerts
  const dismissBtns = document.querySelectorAll('.alert-dismiss');
  dismissBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const alert = btn.closest('.alert');
      if (alert) alert.remove();
    });
  });
});
