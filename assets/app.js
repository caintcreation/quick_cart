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

  // 7. Shopping Cart Management & Multi-Item Checkout
  const shopSlug = document.body.getAttribute('data-shop-slug') || 'default';
  const shopCurrency = document.body.getAttribute('data-currency') || 'KES';
  const cartStorageKey = `qc_cart_${shopSlug}`;

  function getCart() {
    try {
      return JSON.parse(localStorage.getItem(cartStorageKey) || '[]');
    } catch {
      return [];
    }
  }

  function saveCart(cart) {
    localStorage.setItem(cartStorageKey, JSON.stringify(cart));
    updateCartUI();
  }

  function updateCartUI() {
    const cart = getCart();
    const countBadge = document.getElementById('cartCountBadge');
    const totalCount = cart.reduce((acc, item) => acc + item.quantity, 0);

    if (countBadge) {
      if (totalCount > 0) {
        countBadge.textContent = totalCount;
        countBadge.style.display = 'block';
      } else {
        countBadge.style.display = 'none';
      }
    }

    const itemsContainer = document.getElementById('cartItemsList');
    const grandTotalEl = document.getElementById('cartGrandTotal');

    if (itemsContainer && grandTotalEl) {
      if (cart.length === 0) {
        itemsContainer.innerHTML = `
          <div class="empty-state" style="padding: 28px 16px; border: 1px dashed var(--border-light);">
            <div style="font-size: 14px; font-weight: 600; color: var(--text-muted);">Your shopping cart is empty</div>
            <div style="font-size: 12px; color: var(--text-subtle); margin-top: 4px;">Click "+ Cart" on any product to add items.</div>
          </div>
        `;
        grandTotalEl.textContent = `${shopCurrency} 0.00`;
      } else {
        let grandTotal = 0;
        let html = '<div style="display: flex; flex-direction: column; gap: 12px;">';

        cart.forEach((item, index) => {
          const itemSubtotal = item.price * item.quantity;
          grandTotal += itemSubtotal;

          html += `
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; padding-bottom: 12px; border-bottom: 1px solid var(--border-light);">
              <div style="flex: 1; min-width: 0;">
                <div style="font-size: 14px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${escapeHtml(item.name)}</div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                  ${shopCurrency} ${item.price.toLocaleString('en-US', { minimumFractionDigits: 2 })} each
                </div>
              </div>
              <div style="display: flex; align-items: center; gap: 8px;">
                <button type="button" class="btn btn-secondary btn-sm cart-qty-dec" data-index="${index}" style="padding: 2px 8px; font-weight: bold;">-</button>
                <span style="font-size: 13px; font-weight: 700; min-width: 20px; text-align: center;">${item.quantity}</span>
                <button type="button" class="btn btn-secondary btn-sm cart-qty-inc" data-index="${index}" style="padding: 2px 8px; font-weight: bold;">+</button>
                <button type="button" class="btn btn-secondary btn-sm cart-remove-item" data-index="${index}" style="padding: 2px 8px; color: var(--color-danger); border-color: var(--border-light); font-size: 12px;">&times;</button>
              </div>
            </div>
          `;
        });

        html += '</div>';
        itemsContainer.innerHTML = html;
        grandTotalEl.textContent = `${shopCurrency} ${grandTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
      }
    }
  }

  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  // Cart Drawer open/close
  const cartModal = document.getElementById('cartModal');
  const cartToggleBtn = document.getElementById('cartToggleBtn');
  const closeCartModal = document.getElementById('closeCartModal');
  const clearCartBtn = document.getElementById('clearCartBtn');

  if (cartToggleBtn && cartModal) {
    cartToggleBtn.addEventListener('click', () => {
      cartModal.style.display = 'flex';
      updateCartUI();
    });
  }

  if (closeCartModal && cartModal) {
    closeCartModal.addEventListener('click', () => {
      cartModal.style.display = 'none';
    });
  }

  if (clearCartBtn) {
    clearCartBtn.addEventListener('click', () => {
      if (confirm('Are you sure you want to clear your shopping cart?')) {
        saveCart([]);
      }
    });
  }

  if (cartModal) {
    cartModal.addEventListener('click', (e) => {
      if (e.target === cartModal) {
        cartModal.style.display = 'none';
      }
    });

    // Delegate cart item increment, decrement, and remove
    cartModal.addEventListener('click', (e) => {
      const incBtn = e.target.closest('.cart-qty-inc');
      const decBtn = e.target.closest('.cart-qty-dec');
      const remBtn = e.target.closest('.cart-remove-item');
      const cart = getCart();

      if (incBtn) {
        const idx = parseInt(incBtn.getAttribute('data-index'), 10);
        if (cart[idx]) {
          if (cart[idx].quantity < cart[idx].stock) {
            cart[idx].quantity++;
            saveCart(cart);
          } else {
            alert(`Only ${cart[idx].stock} units available in stock.`);
          }
        }
      } else if (decBtn) {
        const idx = parseInt(decBtn.getAttribute('data-index'), 10);
        if (cart[idx]) {
          if (cart[idx].quantity > 1) {
            cart[idx].quantity--;
            saveCart(cart);
          } else {
            cart.splice(idx, 1);
            saveCart(cart);
          }
        }
      } else if (remBtn) {
        const idx = parseInt(remBtn.getAttribute('data-index'), 10);
        if (cart[idx]) {
          cart.splice(idx, 1);
          saveCart(cart);
        }
      }
    });
  }

  // 8. Add to Cart Button Handlers
  document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const pId = parseInt(btn.getAttribute('data-product-id'), 10);
      const pName = btn.getAttribute('data-product-name');
      const pPrice = parseFloat(btn.getAttribute('data-product-price') || '0');
      const pStock = parseInt(btn.getAttribute('data-product-stock') || '999', 10);
      const qtyInputId = btn.getAttribute('data-qty-input');
      const qtyEl = document.getElementById(qtyInputId);
      const qty = qtyEl ? parseInt(qtyEl.value, 10) || 1 : 1;

      const cart = getCart();
      const existing = cart.find(item => item.product_id === pId);

      if (existing) {
        const newQty = existing.quantity + qty;
        if (newQty > pStock) {
          alert(`Cannot add more. Only ${pStock} units available in stock.`);
          return;
        }
        existing.quantity = newQty;
      } else {
        cart.push({
          product_id: pId,
          name: pName,
          price: pPrice,
          stock: pStock,
          quantity: Math.min(qty, pStock)
        });
      }

      saveCart(cart);

      // Micro-interaction feedback
      const originalText = btn.innerHTML;
      btn.innerHTML = `<span>✓ Added</span>`;
      btn.classList.add('btn-primary');
      btn.classList.remove('btn-secondary');
      setTimeout(() => {
        btn.innerHTML = originalText;
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-secondary');
      }, 1200);
    });
  });

  // 9. Storefront Order Modal Controller (Single Buy Now & Cart Multi-Item Checkout)
  const orderModal = document.getElementById('orderModal');
  const modalProductId = document.getElementById('modalProductId');
  const modalQuantity = document.getElementById('modalQuantity');
  const modalCartItemsJson = document.getElementById('modalCartItemsJson');
  const modalProductName = document.getElementById('modalProductName');
  const modalProductSummary = document.getElementById('modalProductSummary');
  const closeOrderModal = document.getElementById('closeOrderModal');
  const cancelOrderModal = document.getElementById('cancelOrderModal');
  const modalOrderForm = document.getElementById('modalOrderForm');
  const cartProceedCheckoutBtn = document.getElementById('cartProceedCheckoutBtn');

  // Single Item "Buy Now" flow
  document.querySelectorAll('.order-single-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const pId = btn.getAttribute('data-product-id');
      const pName = btn.getAttribute('data-product-name');
      const pPrice = parseFloat(btn.getAttribute('data-product-price') || '0');
      const qtyInputId = btn.getAttribute('data-qty-input');
      const qtyEl = document.getElementById(qtyInputId);
      const qty = qtyEl ? parseInt(qtyEl.value, 10) || 1 : 1;

      if (modalProductId) modalProductId.value = pId;
      if (modalQuantity) modalQuantity.value = qty;
      if (modalCartItemsJson) modalCartItemsJson.value = ''; // empty means single item order

      if (modalProductName) modalProductName.textContent = pName;
      if (modalProductSummary) {
        const total = (pPrice * qty).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        modalProductSummary.textContent = `${qty} unit(s) • Total: ${shopCurrency} ${total}`;
      }

      if (orderModal) orderModal.style.display = 'flex';
    });
  });

  // Cart Multi-item Checkout flow
  if (cartProceedCheckoutBtn) {
    cartProceedCheckoutBtn.addEventListener('click', () => {
      const cart = getCart();
      if (cart.length === 0) {
        alert('Your shopping cart is empty! Please add products before checking out.');
        return;
      }

      if (cartModal) cartModal.style.display = 'none';

      if (modalProductId) modalProductId.value = '';
      if (modalQuantity) modalQuantity.value = '';
      if (modalCartItemsJson) modalCartItemsJson.value = JSON.stringify(cart);

      const totalQty = cart.reduce((acc, it) => acc + it.quantity, 0);
      const grandTotal = cart.reduce((acc, it) => acc + (it.price * it.quantity), 0);

      if (modalProductName) modalProductName.textContent = `Cart Checkout (${cart.length} unique items)`;
      if (modalProductSummary) {
        modalProductSummary.textContent = `${totalQty} total item(s) • Total: ${shopCurrency} ${grandTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
      }

      if (orderModal) orderModal.style.display = 'flex';
    });
  }

  const hideOrderModal = () => {
    if (orderModal) orderModal.style.display = 'none';
  };

  if (closeOrderModal) closeOrderModal.addEventListener('click', hideOrderModal);
  if (cancelOrderModal) cancelOrderModal.addEventListener('click', hideOrderModal);

  if (orderModal) {
    orderModal.addEventListener('click', (e) => {
      if (e.target === orderModal) hideOrderModal();
    });
  }

  if (modalOrderForm) {
    modalOrderForm.addEventListener('submit', () => {
      // If multi-item checkout, clear cart on order dispatch
      if (modalCartItemsJson && modalCartItemsJson.value !== '') {
        saveCart([]);
      }
      setTimeout(hideOrderModal, 500);
    });
  }

  // Initialize UI on load
  updateCartUI();
});
