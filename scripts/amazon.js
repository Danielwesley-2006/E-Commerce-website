import {cart, addToCart, loadCartFromBackend} from '../data/cart.js';
import {products} from '../data/products.js';
import { formatCurrency } from './utils/money.js';
import { calculateCartQuantity } from './utils/calculateCartQuantity.js';

// Update the sign-in link based on authentication state
const currentUser = JSON.parse(localStorage.getItem('currentUser'));
const signInLink = document.querySelector('.js-sign-in-link');

if (currentUser) {
  signInLink.innerHTML = `
    <span class="hello-text">Hello, ${currentUser.username}</span>
    <span class="orders-text">Account & Lists</span>
  `;
  signInLink.href = '#';
  signInLink.addEventListener('click', (event) => {
    event.preventDefault();
    localStorage.removeItem('currentUser');
    window.location.reload();
  });

  // Load cart from backend when user is logged in
  loadCartFromBackend().then(() => {
    document.querySelector('.js-cart-quantity').innerHTML = calculateCartQuantity();
  });
} else {
  // Update cart quantity from localStorage for non-logged in users
  document.querySelector('.js-cart-quantity').innerHTML = calculateCartQuantity();
}

// Search functionality
function renderProducts(productsToRender) {
  let productsHTML = '';

  productsToRender.forEach((product) => {
    productsHTML += `
      <div class="product-container">
        <div class="product-image-container">
          <img class="product-image"
            src="${product.image}">
        </div>

        <div class="product-name limit-text-to-2-lines">
          ${product.name}
        </div>

        <div class="product-rating-container">
          <img class="product-rating-stars"
            src="images/ratings/rating-${product.rating.stars * 10}.png">
          <div class="product-rating-count link-primary">
            ${product.rating.count}
          </div>
        </div>

        <div class="product-price">
          $${formatCurrency(product.priceCents)}
        </div>

        <div class="product-quantity-container">
          <select class="js-quantity-selector-${product.id}">
            <option selected value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
            <option value="6">6</option>
            <option value="7">7</option>
            <option value="8">8</option>
            <option value="9">9</option>
            <option value="10">10</option>
          </select>
        </div>

        <div class="product-spacer"></div>

        <div class="added-to-cart js-added-to-cart-${product.id}">
          <img src="images/icons/checkmark.png">
          Added
        </div>

        <button class="add-to-cart-button button-primary js-add-to-cart"
          data-product-id="${product.id}">
          Add to Cart
        </button>
      </div>
    `;
  });

  document.querySelector('.js-products-grid').innerHTML = productsHTML;

  // Re-attach event listeners after rendering
  document.querySelectorAll('.js-add-to-cart').forEach((button) => {
    button.addEventListener('click', async () => {
      const productId = button.dataset.productId;
      const quantitySelector = document.querySelector(`.js-quantity-selector-${productId}`);
      const quantity = Number(quantitySelector.value);

      try {
        await addToCart(productId, quantity);

        // Update cart quantity
        document.querySelector('.js-cart-quantity').innerHTML = calculateCartQuantity();

        // Show added message
        const addedMessage = document.querySelector(`.js-added-to-cart-${productId}`);
        addedMessage.classList.add('added-to-cart-visible');

        setTimeout(() => {
          addedMessage.classList.remove('added-to-cart-visible');
        }, 2000);
      } catch (error) {
        console.error('Error adding to cart:', error);
        alert('Error adding item to cart. Please try again.');
      }
    });
  });
}

// Initial render
renderProducts(products);

// Search functionality
const searchBar = document.querySelector('.search-bar');
const searchButton = document.querySelector('.search-button');

function handleSearch() {
  const searchText = searchBar.value.toLowerCase().trim();
  
  if (searchText === '') {
    renderProducts(products);
    return;
  }

  const searchTerms = searchText.split(' ').filter(term => term.length > 0);
  
  const filteredProducts = products.filter(product => {
    const productName = product.name.toLowerCase();
    const productKeywords = product.keywords.map(keyword => keyword.toLowerCase());
    
    // Check if any search term matches the product name or keywords
    return searchTerms.some(term => 
      productName.includes(term) || 
      productKeywords.some(keyword => keyword.includes(term))
    );
  });

  renderProducts(filteredProducts);
}

// Debounce function to limit how often the search is performed
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

// Add event listeners for search
const debouncedSearch = debounce(handleSearch, 300);
searchBar.addEventListener('input', debouncedSearch);
searchButton.addEventListener('click', (event) => {
  event.preventDefault();
  handleSearch();
});