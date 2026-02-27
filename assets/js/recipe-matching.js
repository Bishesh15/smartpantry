/**
 * Recipe Matching JavaScript
 * Handles ingredient selection and recipe matching
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize ingredient search
    const ingredientSearch = document.getElementById('ingredientSearch');
    if (ingredientSearch) {
        ingredientSearch.addEventListener('input', debounce(filterIngredients, 300));
    }

    // Initialize clear selection button
    const clearBtn = document.getElementById('clearSelection');
    if (clearBtn) {
        clearBtn.addEventListener('click', clearIngredientSelection);
    }

    // Initialize ingredient form submission
    const ingredientForm = document.getElementById('ingredientForm');
    if (ingredientForm) {
        ingredientForm.addEventListener('submit', function(e) {
            const selectedIngredients = getSelectedIngredients();
            if (selectedIngredients.length === 0) {
                e.preventDefault();
                alert('Please select at least one ingredient');
                return false;
            }
        });
    }
});

/**
 * Filter ingredients based on search term
 */
function filterIngredients() {
    const searchTerm = document.getElementById('ingredientSearch').value.toLowerCase();
    const ingredientItems = document.querySelectorAll('.ingredient-item');
    
    ingredientItems.forEach(function(item) {
        const ingredientName = item.querySelector('span').textContent.toLowerCase();
        const dataName = item.querySelector('.ingredient-checkbox').dataset.name;
        
        if (ingredientName.includes(searchTerm) || dataName.includes(searchTerm)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

/**
 * Get selected ingredients
 */
function getSelectedIngredients() {
    const checkboxes = document.querySelectorAll('.ingredient-checkbox:checked');
    const selected = [];
    checkboxes.forEach(function(checkbox) {
        selected.push(checkbox.value);
    });
    return selected;
}

/**
 * Clear ingredient selection
 */
function clearIngredientSelection() {
    const checkboxes = document.querySelectorAll('.ingredient-checkbox');
    checkboxes.forEach(function(checkbox) {
        checkbox.checked = false;
    });
    
    // Clear search
    const searchInput = document.getElementById('ingredientSearch');
    if (searchInput) {
        searchInput.value = '';
        filterIngredients();
    }
}

/**
 * Get selected ingredients count
 */
function getSelectedIngredientsCount() {
    return getSelectedIngredients().length;
}

/**
 * Debounce function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = function() {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

