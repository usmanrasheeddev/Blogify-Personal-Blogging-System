// script.js - Client-side interactive script for Blogify

document.addEventListener('DOMContentLoaded', () => {
  const loginForm = document.getElementById('loginForm');
  const registerForm = document.getElementById('registerForm');
  const messageEl = document.getElementById('auth-message');

  const showMessage = (text, ok) => {
    if (!messageEl) return;
    messageEl.textContent = text;
    messageEl.className = ok ? 'auth-message-box alert alert-success' : 'auth-message-box alert alert-error';
  };

  // Login Form Submission
  if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(loginForm);
      const submitBtn = loginForm.querySelector('button[type="submit"]');
      if (submitBtn) submitBtn.disabled = true;

      try {
        const res = await fetch('index.php?page=login_action', {
          method: 'POST',
          body: formData
        });
        const data = await res.json();
        showMessage(data.message, data.ok);
        if (data.ok) {
          setTimeout(() => {
            window.location.href = 'index.php?page=home';
          }, 800);
        } else {
          if (submitBtn) submitBtn.disabled = false;
        }
      } catch (err) {
        showMessage('An unexpected error occurred. Please try again.', false);
        if (submitBtn) submitBtn.disabled = false;
      }
    });
  }

  // Registration Form Submission
  if (registerForm) {
    registerForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(registerForm);
      const password = formData.get('password') || '';
      const confirmPassword = formData.get('confirm_password') || '';

      if (password !== confirmPassword) {
        showMessage('Passwords do not match. Please re-type your password.', false);
        return;
      }

      const submitBtn = registerForm.querySelector('button[type="submit"]');
      if (submitBtn) submitBtn.disabled = true;

      try {
        const res = await fetch('index.php?page=register_action', {
          method: 'POST',
          body: formData
        });
        const data = await res.json();
        showMessage(data.message, data.ok);
        if (data.ok) {
          setTimeout(() => {
            window.location.href = 'index.php?page=login';
          }, 1200);
        } else {
          if (submitBtn) submitBtn.disabled = false;
        }
      } catch (err) {
        showMessage('Registration error. Please check your fields and try again.', false);
        if (submitBtn) submitBtn.disabled = false;
      }
    });
  }
});
