const API_BASE = 'http://localhost/cpk-messaging/backend/api';

function getToken() {
  return localStorage.getItem('token');
}

function saveSession(token, user) {
  localStorage.setItem('token', token);
  localStorage.setItem('user', JSON.stringify(user));
}

function clearSession() {
  localStorage.removeItem('token');
  localStorage.removeItem('user');
}

async function api(endpoint, { method = 'GET', body = null, auth = true } = {}) {
  const isForm = body instanceof FormData;
  const headers = {};
  if (!isForm) headers['Content-Type'] = 'application/json';
  if (auth && getToken()) {
    headers['Authorization'] = 'Bearer ' + getToken();
  }

  const response = await fetch(`${API_BASE}/${endpoint}`, {
    method,
    headers,
    body: body ? (isForm ? body : JSON.stringify(body)) : null,
  });

  let data = {};
  try {
    data = await response.json();
  } catch (e) {
    data = { status: 'error', message: 'Respons server tidak valid' };
  }

  if (response.status === 401 && auth) {
    clearSession();
    window.location.href = 'index.html';
  }

  return { ok: response.ok, status: response.status, data };
}

const UPLOAD_BASE = 'http://localhost/cpk-messaging/backend/uploads';