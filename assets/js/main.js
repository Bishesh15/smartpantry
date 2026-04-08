/**
 * SmartPantry — Main JavaScript
 * Handles: SuperCook-style ingredient selection, AJAX recipe matching,
 *          pantry modal, favorite toggle, star ratings, flash alerts,
 *          ingredient checkboxes on recipe-detail.
 */

'use strict';

/* ============================================================
   Config
   ============================================================ */
const BASE_URL = document.documentElement.dataset.baseUrl || '/smartpantry/';

/* ============================================================
   Utility
   ============================================================ */
const $  = (sel, ctx = document) => ctx.querySelector(sel);
const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];

function debounce(fn, ms = 300) {
    let timer;
    return (...args) => { clearTimeout(timer); timer = setTimeout(() => fn(...args), ms); };
}

/* ============================================================
   Flash message auto-dismiss
   ============================================================ */
$$('.alert.alert-dismissible').forEach(el => {
    setTimeout(() => {
        el.style.transition = 'opacity .5s';
        el.style.opacity = '0';
        setTimeout(() => el.remove(), 500);
    }, 4000);
});

/* ============================================================
   SuperCook Ingredient Selector
   Chips on the left sidebar → selected tags shown in bar above results
   AJAX call to api/match-recipes.php on every chip toggle.
   ============================================================ */
const selectedIngredients = new Map(); // id → name

function initIngredientSelector() {
    const chips  = $$('.ingredient-chip');
    if (!chips.length) return;

    const bar    = $('#selected-bar');
    const form   = $('#match-form');

    chips.forEach(chip => {
        chip.addEventListener('click', () => {
            const id   = chip.dataset.id;
            const name = chip.dataset.name;
            if (selectedIngredients.has(id)) {
                selectedIngredients.delete(id);
                chip.classList.remove('active');
            } else {
                selectedIngredients.set(id, name);
                chip.classList.add('active');
            }
            renderSelectedBar();
            fetchMatchingRecipes();
        });
    });

    // "Use My Pantry" button
    const pantryBtn = $('#use-pantry-btn');
    if (pantryBtn) {
        pantryBtn.addEventListener('click', () => {
            const pantryIds   = JSON.parse(pantryBtn.dataset.pantryIds || '[]');
            const pantryNames = JSON.parse(pantryBtn.dataset.pantryNames || '[]');
            selectedIngredients.clear();
            $$('.ingredient-chip').forEach(c => c.classList.remove('active'));
            pantryIds.forEach((id, i) => {
                selectedIngredients.set(String(id), pantryNames[i] || '');
                const chip = $(`.ingredient-chip[data-id="${id}"]`);
                if (chip) chip.classList.add('active');
            });
            renderSelectedBar();
            fetchMatchingRecipes();
        });
    }

    // "Clear All" button
    const clearBtn = $('#clear-all-btn');
    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            selectedIngredients.clear();
            $$('.ingredient-chip').forEach(c => c.classList.remove('active'));
            renderSelectedBar();
            renderResults([]);
            showNoResultsMsg('Select ingredients to find recipes.', false);
        });
    }

    // Initial render if there are pre-selected (from session)
    const preSelected = window.preSelectedIngredients || [];
    preSelected.forEach(({ id, name }) => {
        selectedIngredients.set(String(id), name);
        const chip = $(`.ingredient-chip[data-id="${id}"]`);
        if (chip) chip.classList.add('active');
    });
    if (preSelected.length) {
        renderSelectedBar();
        fetchMatchingRecipes();
    }
}

function renderSelectedBar() {
    const bar = $('#selected-bar');
    if (!bar) return;
    bar.innerHTML = '';
    if (selectedIngredients.size === 0) {
        bar.innerHTML = '<span class="text-muted small">Click ingredients on the left to add them here…</span>';
        return;
    }
    selectedIngredients.forEach((name, id) => {
        const tag = document.createElement('span');
        tag.className = 'selected-tag';
        tag.innerHTML = `${name} <span class="remove-tag" data-id="${id}">✕</span>`;
        tag.querySelector('.remove-tag').addEventListener('click', () => {
            selectedIngredients.delete(id);
            const chip = $(`.ingredient-chip[data-id="${id}"]`);
            if (chip) chip.classList.remove('active');
            renderSelectedBar();
            fetchMatchingRecipes();
        });
        bar.appendChild(tag);
    });
}

const fetchMatchingRecipes = debounce(async () => {
    const resultsGrid = $('#results-grid');
    if (!resultsGrid) return;

    if (selectedIngredients.size === 0) {
        renderResults([]);
        showNoResultsMsg('Select ingredients on the left to find matching recipes.', false);
        return;
    }

    // Show spinner
    resultsGrid.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-success"></div></div>';
    hideNoResultsMsg();

    const formData = new FormData();
    selectedIngredients.forEach((_, id) => formData.append('ingredients[]', id));
    formData.append('diet_type', $('#diet-filter')?.value  || '');
    formData.append('sort_by',   $('#sort-select')?.value  || 'match');

    try {
        const res  = await fetch(`${BASE_URL}api/match-recipes.php`, { method: 'POST', body: formData });
        const data = await res.json();
        renderResults(data.recipes || []);
    } catch (e) {
        resultsGrid.innerHTML = '<div class="alert alert-danger">Error fetching recipes. Please try again.</div>';
    }
}, 400);

function renderResults(recipes) {
    const grid        = $('#results-grid');
    const countEl     = $('#results-count');
    if (!grid) return;

    if (!recipes.length) {
        grid.innerHTML = '';
        showNoResultsMsg('No recipes match your selected ingredients. Try adding more or removing filters.', true);
        if (countEl) countEl.textContent = '0 recipes found';
        return;
    }
    hideNoResultsMsg();
    if (countEl) countEl.textContent = `${recipes.length} recipe${recipes.length > 1 ? 's' : ''} found`;

    grid.innerHTML = recipes.map(r => buildRecipeCard(r)).join('');
    initFavoriteButtons();
}

function buildRecipeCard(r) {
    const imageUrl  = resolveImage(r.image_url);
    const dietClass = r.diet_type === 'Vegan' ? 'badge-vegan' : (r.diet_type === 'Vegetarian' ? 'badge-veg' : 'badge-nonveg');
    const dietIcon  = r.diet_type === 'Vegan' ? '🌱' : (r.diet_type === 'Vegetarian' ? '🥦' : '🍗');

    const haveItems = (r.matched_ingredients || []).slice(0, 4).map(n => `<span class="ing-have">✓ ${n}</span>`).join(' ');
    const needItems = (r.missing_ingredients || []).slice(0, 4).map(n => `<span class="ing-need">✗ ${n}</span>`).join(' ');
    const needMore  = r.missing_ingredients?.length > 4 ? `<span class="text-muted small">+${r.missing_ingredients.length - 4} more</span>` : '';

    const matchPct  = r.match_percentage;
    const matchCol  = matchPct >= 70 ? '#16a34a' : matchPct >= 40 ? '#d97706' : '#ef4444';

    return `
    <div class="sp-card recipe-card animate-fade" data-diet="${r.diet_type}" data-time="${r.prep_time}">
      ${matchPct > 0 ? `<div class="match-badge" style="background:${matchCol};">${matchPct}% Match</div>` : ''}
      <a href="${BASE_URL}views/user/recipe-detail.php?id=${r.id}">
        <img src="${imageUrl}" class="card-img-top w-100" style="height:200px;object-fit:cover;"
             alt="${escHtml(r.name)}"
             onerror="this.src='${BASE_URL}assets/images/default-recipes.jpg'">
      </a>
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <h5 class="card-title mb-0">
            <a href="${BASE_URL}views/user/recipe-detail.php?id=${r.id}" class="text-dark text-decoration-none">
              ${escHtml(r.name)}
            </a>
          </h5>
          <button class="fav-btn ms-2 btn btn-sm btn-light" data-recipe-id="${r.id}" title="Save">❤</button>
        </div>
        <div class="card-meta">
          <span>⏱ ${r.prep_time}m</span>
          <span>🔥 ${Math.round(r.calories)} kcal</span>
          <span>⭐ ${parseFloat(r.average_rating || 0).toFixed(1)}</span>
        </div>
        <span class="badge ${dietClass}">${dietIcon} ${r.diet_type}</span>
        <div class="ingredient-tags mt-2">
          ${haveItems} ${needItems} ${needMore}
        </div>
      </div>
    </div>`;
}

function resolveImage(url) {
    if (!url) return `${BASE_URL}assets/images/default-recipes.jpg`;
    if (url.startsWith('http')) return url;
    return `${BASE_URL}assets/images/${url}`;
}

function escHtml(str) {
    const d = document.createElement('div');
    d.textContent = str || '';
    return d.innerHTML;
}

function showNoResultsMsg(text, showWarn = true) {
    let el = $('#no-results-msg');
    if (!el) return;
    el.innerHTML = showWarn
        ? `<i class="bi bi-search" style="font-size:3rem;color:#cbd5e1;"></i>
           <h5 class="mt-3 text-muted">${text}</h5>`
        : `<i class="bi bi-basket" style="font-size:3rem;color:#cbd5e1;"></i>
           <h5 class="mt-3 text-muted">${text}</h5>`;
    el.style.display = 'block';
}

function hideNoResultsMsg() {
    const el = $('#no-results-msg');
    if (el) el.style.display = 'none';
}

/* ── Filter / Sort change ───────────────────────────────────── */
['#diet-filter', '#sort-select'].forEach(sel => {
    const el = $(sel);
    if (el) el.addEventListener('change', fetchMatchingRecipes);
});

/* ── Text search form ───────────────────────────────────────── */
const textSearchForm = $('#text-search-form');
if (textSearchForm) {
    textSearchForm.addEventListener('submit', e => {
        // Allow normal form submit — RecipeController handles it
    });
}

/* ============================================================
   Pantry Modal — Add Ingredient (AJAX search)
   ============================================================ */
function initPantryModal() {
    const modal       = $('#add-ingredient-modal');
    const openBtn     = $$('[data-open-pantry-modal]');
    const closeBtn    = $('#modal-close');
    const searchInput = $('#modal-search');
    const resultsCont = $('#modal-results');
    if (!modal) return;

    openBtn.forEach(b => b.addEventListener('click', () => {
        modal.style.display = 'flex';
        searchInput?.focus();
    }));
    closeBtn?.addEventListener('click', () => modal.style.display = 'none');
    modal.addEventListener('click', e => { if (e.target === modal) modal.style.display = 'none'; });

    searchInput?.addEventListener('input', debounce(async e => {
        const term = e.target.value.trim();
        if (term.length < 1) { resultsCont.innerHTML = ''; return; }
        resultsCont.innerHTML = '<p class="text-center text-muted small py-2">Searching…</p>';
        try {
            const res  = await fetch(`${BASE_URL}api/search-ingredients.php?term=${encodeURIComponent(term)}`);
            const data = await res.json();
            if (!data.length) {
                resultsCont.innerHTML = '<p class="text-center text-muted small py-2">No ingredients found.</p>';
                return;
            }
            resultsCont.innerHTML = data.map(ing => `
              <form method="POST" action="${BASE_URL}controllers/UserController.php" class="d-block mb-1">
                <input type="hidden" name="action" value="add_to_pantry">
                <input type="hidden" name="ingredient_id" value="${ing.id}">
                <input type="hidden" name="redirect" value="${window.location.href}">
                <button type="submit" class="btn btn-light w-100 text-start d-flex justify-content-between align-items-center py-2 px-3">
                  <div>
                    <strong>${escHtml(ing.name)}</strong>
                    <small class="d-block text-muted" style="font-size:.7rem;">${ing.category} · ${ing.calories_per_unit} kcal/${ing.unit}</small>
                  </div>
                  <span class="text-success fw-bold">+ Add</span>
                </button>
              </form>`).join('');
        } catch {
            resultsCont.innerHTML = '<p class="text-center text-danger small py-2">Error loading results.</p>';
        }
    }, 350));
}

/* ============================================================
   Favorite Toggle (AJAX)
   ============================================================ */
function initFavoriteButtons() {
    $$('.fav-btn').forEach(btn => {
        btn.addEventListener('click', async e => {
            e.preventDefault();
            const recipeId = btn.dataset.recipeId;
            if (!recipeId) return;
            const formData = new FormData();
            formData.append('recipe_id', recipeId);
            try {
                const res  = await fetch(`${BASE_URL}api/toggle-favorite.php`, { method: 'POST', body: formData });
                if (res.status === 401) {
                    window.location.href = `${BASE_URL}views/user/login.php`;
                    return;
                }
                const data = await res.json();
                btn.textContent = data.favorited ? '❤' : '🤍';
                btn.style.color = data.favorited ? '#dc2626' : '';
                showToast(data.message, data.favorited ? 'success' : 'info');
            } catch {
                showToast('Error. Please try again.', 'error');
            }
        });
    });
}

/* ============================================================
   Toast notification
   ============================================================ */
function showToast(msg, type = 'success') {
    const colours = { success: '#16a34a', info: '#2563eb', error: '#dc2626' };
    const toast   = document.createElement('div');
    toast.style.cssText = `
      position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;
      background:${colours[type] || colours.success};color:#fff;
      padding:.75rem 1.25rem;border-radius:12px;font-weight:700;font-size:.88rem;
      box-shadow:0 8px 24px rgba(0,0,0,.15);transition:opacity .4s;
    `;
    toast.textContent = msg;
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 400); }, 3000);
}

/* ============================================================
   Star Rating UI (recipe detail page)
   ============================================================ */
function initStarRating() {
    const stars = $$('.rating-star');
    if (!stars.length) return;
    stars.forEach((star, idx) => {
        star.addEventListener('click', () => {
            const val = parseInt(star.dataset.value);
            const radio = document.querySelector(`input[name="rating"][value="${val}"]`);
            if (radio) radio.checked = true;
            stars.forEach((s, i) => {
                s.style.color = i < val ? '#fbbf24' : '#e2e8f0';
            });
        });
        star.addEventListener('mouseenter', () => {
            const val = parseInt(star.dataset.value);
            stars.forEach((s, i) => {
                s.style.color = i < val ? '#fbbf24' : '#e2e8f0';
            });
        });
    });
    const container = stars[0]?.closest('.star-group');
    if (container) {
        container.addEventListener('mouseleave', () => {
            const checked = document.querySelector('input[name="rating"]:checked');
            const val = checked ? parseInt(checked.value) : 0;
            stars.forEach((s, i) => { s.style.color = i < val ? '#fbbf24' : '#e2e8f0'; });
        });
    }
}

/* ============================================================
   Ingredient checkboxes on recipe detail (cross-off ability)
   ============================================================ */
function initIngredientCheckboxes() {
    $$('.ing-check').forEach(box => {
        box.addEventListener('click', () => {
            box.classList.toggle('have');
        });
    });
}

/* ============================================================
   Admin: Calorie Calculator (recipe form)
   ============================================================ */
function initCalorieCalculator() {
    if (!document.getElementById('calorie-count')) return;

    function recalc() {
        let total = 0;
        $$('.ingredient-row').forEach(row => {
            const sel = row.querySelector('select');
            const qty = parseFloat(row.querySelector('.qty-input')?.value || 0);
            if (sel?.value && window.ingredientCalMap) {
                const cals = window.ingredientCalMap[sel.value] || 0;
                total += cals * qty;
            }
        });
        document.getElementById('calorie-count').textContent = Math.round(total);
    }

    document.getElementById('ingredients-container')?.addEventListener('change',  recalc);
    document.getElementById('ingredients-container')?.addEventListener('input',   recalc);
    recalc();
}

/* ============================================================
   Admin: Add ingredient row to recipe form
   ============================================================ */
window.addIngredientRow = function() {
    const container = document.getElementById('ingredients-container');
    if (!container) return;
    const existing = container.querySelector('.ingredient-row select');
    if (!existing) return;
    const newRow = existing.closest('.ingredient-row').cloneNode(true);
    newRow.querySelectorAll('select, input').forEach(el => {
        if (el.tagName === 'INPUT') el.value = '';
    });
    newRow.querySelector('.btn-remove-row')?.addEventListener('click', e => {
        e.currentTarget.closest('.ingredient-row').remove();
        initCalorieCalculator();
    });
    container.appendChild(newRow);
    newRow.querySelector('select')?.focus();
};

/* ============================================================
   Admin: Image preview
   ============================================================ */
function initImagePreview() {
    const fileInput = document.getElementById('image-file-input');
    const preview   = document.getElementById('image-preview-box');
    if (!fileInput || !preview) return;

    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            let img = preview.querySelector('img');
            if (!img) { img = document.createElement('img'); preview.appendChild(img); }
            img.src = e.target.result;
            img.style.cssText = 'width:100%;height:100%;object-fit:cover;';
            preview.querySelector('.preview-placeholder')?.remove();
        };
        reader.readAsDataURL(file);
    });

    // URL field live preview
    const urlInput = document.getElementById('image-url-input');
    if (urlInput) {
        urlInput.addEventListener('input', debounce(() => {
            const url = urlInput.value.trim();
            if (url.startsWith('http')) {
                let img = preview.querySelector('img');
                if (!img) { img = document.createElement('img'); preview.appendChild(img); }
                img.src = url;
                img.style.cssText = 'width:100%;height:100%;object-fit:cover;';
                preview.querySelector('.preview-placeholder')?.remove();
            }
        }, 500));
    }
}

/* ============================================================
   Admin: Delete confirm
   ============================================================ */
$$('[data-confirm]').forEach(el => {
    el.addEventListener('click', e => {
        if (!confirm(el.dataset.confirm)) e.preventDefault();
    });
});

/* ============================================================
   Pantry quantity inline edit
   ============================================================ */
function initPantryQuantity() {
    $$('.qty-edit-form select, .qty-edit-form input[type=number]').forEach(el => {
        el.addEventListener('change', () => el.closest('form').submit());
    });
}

/* ============================================================
   Init all
   ============================================================ */
document.addEventListener('DOMContentLoaded', () => {
    initIngredientSelector();
    initPantryModal();
    initFavoriteButtons();
    initStarRating();
    initIngredientCheckboxes();
    initCalorieCalculator();
    initImagePreview();
    initPantryQuantity();

    // Delete confirm for admin rows
    $$('[data-confirm]').forEach(el => {
        el.addEventListener('click', e => {
            if (!confirm(el.dataset.confirm)) e.preventDefault();
        });
    });
});
