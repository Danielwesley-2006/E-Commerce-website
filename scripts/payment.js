// Load the order summary from localStorage
const orderSummary = JSON.parse(localStorage.getItem('orderSummary')) || {
  items: '₹0.00',
  delivery: '₹0.00',
  total: '₹0.00'
};

// Update the order summary in the page
document.querySelector('.js-payment-items').textContent = orderSummary.items;
document.querySelector('.js-payment-delivery').textContent = orderSummary.delivery;
document.querySelector('.js-payment-total').textContent = orderSummary.total;

// Handle payment method selection
document.querySelector('.use-payment-button').addEventListener('click', () => {
  const selectedPayment = document.querySelector('input[name="payment"]:checked');
  
  if (!selectedPayment) {
    alert('Please select a payment method');
    return;
  }

  // Here you would typically process the payment
  // For this demo, we'll just show a success message
  alert('Order placed successfully!');
  
  // Clear the cart and order summary
  localStorage.removeItem('cart');
  localStorage.removeItem('orderSummary');
  
  // Redirect to the main page
  window.location.href = 'amazon.html';
});
