document.querySelector('.js-register-form').addEventListener('submit', async (event) => {
  event.preventDefault();

  const username = document.querySelector('.js-name-input').value;
  const email = document.querySelector('.js-email-input').value;
  const password = document.querySelector('.js-password-input').value;
  const passwordCheck = document.querySelector('.js-password-check-input').value;

  if (password !== passwordCheck) {
    alert('Passwords do not match');
    return;
  }

  try {
    const response = await fetch('http://localhost/13-javascript-amazon-project/backend/api/auth.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        action: 'register',
        username,
        email,
        password
      })
    });

    const data = await response.json();

    if (data.success) {
      // After successful registration, automatically log in
      const loginResponse = await fetch('http://localhost/13-javascript-amazon-project/backend/api/auth.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          action: 'login',
          email,
          password
        })
      });

      const loginData = await loginResponse.json();

      if (loginData.success) {
        localStorage.setItem('currentUser', JSON.stringify(loginData.user));
        window.location.href = 'amazon.html';
      }
    } else {
      alert(data.message || 'Registration failed. Please try again.');
    }
  } catch (error) {
    console.error('Registration error:', error);
    alert('Error during registration. Please try again.');
  }
});
