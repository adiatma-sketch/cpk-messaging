const loginForm = document.getElementById('login-form');
const registerForm = document.getElementById('register-form');
const messageEl = document.getElementById('message');

if (getToken()) {
  window.location.href = 'contacts.html';
}

function showMessage(text, isError = true) {
  messageEl.textContent = text;
  messageEl.className = 'message ' + (isError ? 'error' : 'success');
}

document.getElementById('to-register').onclick = () => {
  loginForm.hidden = true;
  registerForm.hidden = false;
  showMessage('');
};
document.getElementById('to-login').onclick = () => {
  registerForm.hidden = true;
  loginForm.hidden = false;
  showMessage('');
};

loginForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  const res = await api('login.php', {
    method: 'POST',
    auth: false,
    body: {
      phone: document.getElementById('login-phone').value.trim(),
      password: document.getElementById('login-password').value,
    },
  });
  if (res.ok) {
    saveSession(res.data.data.token, res.data.data.user);
    window.location.href = 'contacts.html';
  } else {
    showMessage(res.data.message);
  }
});

registerForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  const phone = document.getElementById('reg-phone').value.trim();
  const res = await api('register.php', {
    method: 'POST',
    auth: false,
    body: {
      name: document.getElementById('reg-name').value.trim(),
      phone,
      password: document.getElementById('reg-password').value,
    },
  });
  if (res.ok) {
    registerForm.hidden = true;
    loginForm.hidden = false;
    document.getElementById('login-phone').value = phone;
    showMessage('Registrasi berhasil, silakan masuk', false);
  } else {
    showMessage(res.data.message);
  }
});