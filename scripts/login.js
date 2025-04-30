document.querySelector('.js-login-form').addEventListener('submit', async (event) => {
  event.preventDefault();

  const email = document.querySelector('.js-email-input').value;
  const password = document.querySelector('.js-password-input').value;

  try {
    const response = await fetch('http://localhost/13-javascript-amazon-project/backend/api/auth.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        action: 'login',
        email: email,
        password: password
      })
    });

    const data = await response.json();

    if (data.success) {
      localStorage.setItem('currentUser', JSON.stringify(data.user));
      window.location.href = 'amazon.html';
    } else {
      alert(data.message || 'Invalid email or password');
    }
  } catch (error) {
    console.error('Login error:', error);
    alert('Error during login. Please try again.');
  }
});
