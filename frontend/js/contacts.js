if (!getToken()) {
  window.location.href = 'index.html';
}

const user = JSON.parse(localStorage.getItem('user') || '{}');
const form = document.getElementById('contact-form');
const idInput = document.getElementById('contact-id');
const nameInput = document.getElementById('contact-name');
const phoneInput = document.getElementById('contact-phone');
const saveBtn = document.getElementById('btn-save');
const cancelBtn = document.getElementById('btn-cancel');
const listEl = document.getElementById('contact-list');
const messageEl = document.getElementById('message');

document.getElementById('welcome').textContent = 'Masuk sebagai ' + (user.name || '');

function showMessage(text, isError = true) {
  messageEl.textContent = text;
  messageEl.className = 'message ' + (isError ? 'error' : 'success');
}

function resetForm() {
  form.reset();
  idInput.value = '';
  saveBtn.textContent = 'Tambah Kontak';
  cancelBtn.hidden = true;
}

async function loadContacts() {
  const res = await api('contacts.php');
  listEl.innerHTML = '';
  if (!res.ok) {
    showMessage(res.data.message);
    return;
  }
  if (res.data.data.length === 0) {
    listEl.innerHTML = '<li class="empty">Belum ada kontak</li>';
    return;
  }
  res.data.data.forEach((c) => {
    const li = document.createElement('li');

    const info = document.createElement('div');
    const strong = document.createElement('strong');
    strong.textContent = c.alias_name;
    const small = document.createElement('small');
    small.textContent = c.phone;
    info.append(strong, document.createElement('br'), small);

    const actions = document.createElement('div');
    const editBtn = document.createElement('button');
    editBtn.textContent = 'Ubah';
    editBtn.className = 'small secondary';
    editBtn.onclick = () => {
      idInput.value = c.id;
      nameInput.value = c.alias_name;
      phoneInput.value = c.phone;
      saveBtn.textContent = 'Simpan Perubahan';
      cancelBtn.hidden = false;
    };
    const delBtn = document.createElement('button');
    delBtn.textContent = 'Hapus';
    delBtn.className = 'small danger';
    delBtn.onclick = () => deleteContact(c.id);
    actions.append(editBtn, delBtn);

    li.append(info, actions);
    listEl.appendChild(li);
  });
}

async function deleteContact(id) {
  if (!confirm('Hapus kontak ini?')) return;
  const res = await api('contacts.php', { method: 'DELETE', body: { id } });
  showMessage(res.data.message, !res.ok);
  if (res.ok) loadContacts();
}

form.addEventListener('submit', async (e) => {
  e.preventDefault();
  const body = {
    alias_name: nameInput.value.trim(),
    phone: phoneInput.value.trim(),
  };
  let res;
  if (idInput.value) {
    body.id = Number(idInput.value);
    res = await api('contacts.php', { method: 'PUT', body });
  } else {
    res = await api('contacts.php', { method: 'POST', body });
  }
  showMessage(res.data.message, !res.ok);
  if (res.ok) {
    resetForm();
    loadContacts();
  }
});

cancelBtn.onclick = resetForm;

document.getElementById('btn-logout').onclick = () => {
  clearSession();
  window.location.href = 'index.html';
};

document.getElementById('btn-delete-account').onclick = async () => {
  if (!confirm('Akun akan dihapus permanen. Lanjutkan?')) return;
  const res = await api('delete_account.php', { method: 'DELETE' });
  if (res.ok) {
    clearSession();
    window.location.href = 'index.html';
  } else {
    showMessage(res.data.message);
  }
};

loadContacts();