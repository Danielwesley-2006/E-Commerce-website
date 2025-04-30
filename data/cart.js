export let cart = JSON.parse(localStorage.getItem('cart')) || [];
const currentUser = JSON.parse(localStorage.getItem('currentUser'));

export function saveToStorage() {
  localStorage.setItem('cart', JSON.stringify(cart));
}

async function syncWithBackend(action, productId, quantity) {
  if (!currentUser) {
    console.log('User not logged in, only saving to local storage');
    return;
  }

  try {
    const response = await fetch('http://localhost/13-javascript-amazon-project/backend/api/cart.php', {
      method: action === 'DELETE' ? 'DELETE' : 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        user_id: currentUser.id,
        product_id: productId,
        quantity: quantity || 0
      })
    });

    const data = await response.json();
    if (!data.success) {
      throw new Error(data.message);
    }
  } catch (error) {
    console.error('Error syncing with backend:', error);
  }
}

export async function addToCart(productId, quantity) {
  let matchingItem;

  cart.forEach((cartItem) => {
    if (productId === cartItem.productId) {
      matchingItem = cartItem;
    }
  });

  if (matchingItem) {
    matchingItem.quantity += quantity;
  } else {
    cart.push({
      productId,
      quantity,
    });
  }

  saveToStorage();
  await syncWithBackend('POST', productId, quantity);
}

export async function removeFromCart(productId) {
  const newCart = [];

  cart.forEach((item) => {
    if (item.productId != productId) {
      newCart.push(item);
    }
  });

  cart = newCart;
  saveToStorage();
  await syncWithBackend('DELETE', productId);
}

export async function updateCartQuantity(productId, quantity) {
  cart.forEach((item) => {
    if (item.productId == productId) {
      item.quantity = quantity;
    }
  });
  saveToStorage();
  await syncWithBackend('POST', productId, quantity);
}

// Load cart from backend when user is logged in
export async function loadCartFromBackend() {
  if (!currentUser) {
    return;
  }

  try {
    const response = await fetch(`http://localhost/13-javascript-amazon-project/backend/api/cart.php?user_id=${currentUser.id}`);
    const data = await response.json();

    if (data.success) {
      cart = data.cart_items.map(item => ({
        productId: item.product_id,
        quantity: parseInt(item.quantity)
      }));
      saveToStorage();
    }
  } catch (error) {
    console.error('Error loading cart from backend:', error);
  }
}
