import { cart , removeFromCart, updateCartQuantity, saveToStorage} from "../data/cart.js";
import { products } from "../data/products.js";
import { formatCurrency } from "./utils/money.js";
import { calculateCartQuantity } from "./utils/calculateCartQuantity.js";

let cartSummaryHTML = '';
let itemsTotal = 0;
const shippingCost = 4.99;

cart.forEach((cartItem) => {
    const productId = cartItem.productId;
    let matchingProduct;
    
    products.forEach((product) => {
        if(product.id === productId){
            matchingProduct = product;
            itemsTotal += matchingProduct.priceCents * cartItem.quantity;
        }
    });
    
    cartSummaryHTML += `
     <div class="cart-item-container js-cart-item-container-${matchingProduct.id}">
            <div class="delivery-date">
              Delivery date: Tuesday, June 21
            </div>

            <div class="cart-item-details-grid">
              <img class="product-image"
                src="${matchingProduct.image}">

              <div class="cart-item-details">
                <div class="product-name">
                  ${matchingProduct.name}
                </div>
                <div class="product-price">
                $${formatCurrency(matchingProduct.priceCents)}
                </div>
                <div class="product-quantity">
                  <span class="js-quantity-${matchingProduct.id}">
                    Quantity: <span class="quantity-label js-quantity-label-${matchingProduct.id}">${cartItem.quantity}</span>
                  </span>
                  <span class="update-quantity-link link-primary js-update-quantity-link-${matchingProduct.id}" data-product-id="${matchingProduct.id}">
                    Update
                  </span>
                  <input class="quantity-input js-quantity-input-${matchingProduct.id}">
                  <span class="save-quantity-link link-primary js-save-quantity-link-${matchingProduct.id}" data-product-id="${matchingProduct.id}">Save</span>
                  <span class="delete-quantity-link link-primary js-delete-link" data-product-id="${matchingProduct.id}">
                    Delete
                  </span>
                </div>
              </div>

              <div class="delivery-options">
                <div class="delivery-options-title">
                  Choose a delivery option:
                </div>
                <div class="delivery-option">
                  <input type="radio" checked
                    class="delivery-option-input"
                    name="delivery-option-${matchingProduct.id}">
                  <div>
                    <div class="delivery-option-date">
                      Tuesday, June 21
                    </div>
                    <div class="delivery-option-price">
                      FREE Shipping
                    </div>
                  </div>
                </div>
                <div class="delivery-option">
                  <input type="radio"
                    class="delivery-option-input"
                    name="delivery-option-${matchingProduct.id}">
                  <div>
                    <div class="delivery-option-date">
                      Wednesday, June 15
                    </div>
                    <div class="delivery-option-price">
                      $4.99 - Shipping
                    </div>
                  </div>
                </div>
                <div class="delivery-option">
                  <input type="radio"
                    class="delivery-option-input"
                    name="delivery-option-${matchingProduct.id}">
                  <div>
                    <div class="delivery-option-date">
                      Monday, June 13
                    </div>
                    <div class="delivery-option-price">
                      $9.99 - Shipping
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
    `
});

document.querySelector('.js-order-summary').innerHTML = cartSummaryHTML;

// Calculate and update payment summary
function updatePaymentSummary() {
    const itemsTotalDollars = formatCurrency(itemsTotal);
    const totalBeforeTax = itemsTotal / 100 + shippingCost;
    const tax = totalBeforeTax * 0.1;
    const total = totalBeforeTax + tax;

    document.querySelector('.payment-summary-row:nth-child(2) .payment-summary-money')
        .innerHTML = `$${itemsTotalDollars}`;
    document.querySelector('.payment-summary-row:nth-child(3) .payment-summary-money')
        .innerHTML = `$${shippingCost.toFixed(2)}`;
    document.querySelector('.payment-summary-row:nth-child(4) .payment-summary-money')
        .innerHTML = `$${totalBeforeTax.toFixed(2)}`;
    document.querySelector('.payment-summary-row:nth-child(5) .payment-summary-money')
        .innerHTML = `$${tax.toFixed(2)}`;
    document.querySelector('.payment-summary-row:nth-child(6) .payment-summary-money')
        .innerHTML = `$${total.toFixed(2)}`;
}

updatePaymentSummary();

document.querySelectorAll('.js-delete-link')
.forEach((link) => {
    link.addEventListener('click', () => {
        const productId = link.dataset.productId;
        
        // Update itemsTotal when removing item
        const removedItem = cart.find(item => item.productId === productId);
        const removedProduct = products.find(product => product.id === productId);
        if (removedItem && removedProduct) {
            itemsTotal -= removedProduct.priceCents * removedItem.quantity;
        }
        
        removeFromCart(productId);
        document.querySelector(`.js-cart-item-container-${productId}`).remove();
        updateCartDisplay();
        updatePaymentSummary();
    });
});

document.querySelectorAll('.update-quantity-link')
.forEach((link) => {
    link.addEventListener('click', () => {
        const productId = link.dataset.productId;
        const inputQuantity = document.querySelector(`.js-quantity-input-${productId}`);
        const saveLink = document.querySelector(`.js-save-quantity-link-${productId}`);
        inputQuantity.classList.add('is-editing-quantity');
        saveLink.classList.add(`is-saving-quantity`);
        document.querySelector(`.js-quantity-label-${productId}`).classList.add('editing');
        document.querySelector(`.js-update-quantity-link-${productId}`).classList.add('editing');
    });
});

document.querySelectorAll('.save-quantity-link')
.forEach((link) => {
    link.addEventListener('click', () => {
        const productId = link.dataset.productId;
        const saveLink = document.querySelector(`.js-save-quantity-link-${productId}`);
        const cartQuantityUpdate = document.querySelector(`.js-quantity-label-${productId}`);
        const quantityLink = document.querySelector(`.js-quantity-input-${productId}`);
        const updateLink = document.querySelector(`.js-update-quantity-link-${productId}`);
        
        if(quantityLink.value) {
            // Update itemsTotal when changing quantity
            const product = products.find(p => p.id === productId);
            const oldQuantity = cart.find(item => item.productId === productId)?.quantity || 0;
            const newQuantity = parseInt(quantityLink.value);
            if (product) {
                itemsTotal = itemsTotal - (product.priceCents * oldQuantity) + (product.priceCents * newQuantity);
            }
            
            updateCartQuantity(productId, quantityLink.value);
            cartQuantityUpdate.innerHTML = `${quantityLink.value}`;
            updatePaymentSummary();
        }
        
        cartQuantityUpdate.classList.remove('editing');
        updateLink.classList.remove('editing');
        saveLink.classList.remove(`is-saving-quantity`);
        quantityLink.classList.remove(`is-editing-quantity`);
        updateCartDisplay();
        saveToStorage();
    });
});

function updateCartDisplay() {
    let quantity = calculateCartQuantity();
    let output = quantity === 1 ? `${quantity} item` : `${quantity} items`;
    document.querySelector('.js-cart-quantity').innerHTML = output;
}

const dayObj = dayjs();
const newDayObj = dayObj.add(7,'days');
console.log(newDayObj.format('dddd, MMMM D'));

// Add event listener for the Place Order button
document.querySelector('.place-order-button').addEventListener('click', () => {
  // Save order summary to localStorage
  const orderSummary = {
    items: document.querySelector('.payment-summary-row:nth-child(2) .payment-summary-money').innerHTML,
    delivery: document.querySelector('.payment-summary-row:nth-child(3) .payment-summary-money').innerHTML,
    total: document.querySelector('.payment-summary-row:nth-child(6) .payment-summary-money').innerHTML
  };
  localStorage.setItem('orderSummary', JSON.stringify(orderSummary));
  
  // Redirect to payment page
  window.location.href = 'payment.html';
});
