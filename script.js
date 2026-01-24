document.addEventListener('DOMContentLoaded', () => {
  const loginForm = document.getElementById('loginForm');
  const registerForm = document.getElementById('registerForm');
  const messageEl = document.getElementById('auth-message');

  const showMessage = (text, ok) => {
    if (!messageEl) return;
    messageEl.textContent = text;
    messageEl.style.color = ok ? 'green' : 'red';
  };

  if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(loginForm);

      const res = await fetch('index.php?page=login_action', {
        method: 'POST',
        body: formData
      });
      const data = await res.json();
      showMessage(data.message, data.ok);
      if (data.ok) {
        window.location.href = 'index.php?page=home';
      }
    });
  }

  if (registerForm) {
    registerForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(registerForm);
      const password = formData.get('password') || '';
      const confirmPassword = formData.get('confirm_password') || '';

      if (password !== confirmPassword) {
        showMessage('Passwords do not match.', false);
        return;
      }

      const res = await fetch('index.php?page=register_action', {
        method: 'POST',
        body: formData
      });
      const data = await res.json();
      showMessage(data.message, data.ok);
      if (data.ok) {
        window.location.href = 'index.php?page=login';
      }
    });
  }
});
