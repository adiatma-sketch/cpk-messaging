if (!getToken()) {
  window.location.href = 'index.html';
}

const params = new URLSearchParams(window.location.search);
const withId = params.get('with');
const withName = params.get('name') || 'Chat';
if (!withId) {
  window.location.href = 'contacts.html';
}

const me = JSON.parse(localStorage.getItem('user') || '{}');
const messagesEl = document.getElementById('messages');
const messageEl = document.getElementById('message');
const bodyInput = document.getElementById('body');
let lastId = 0;

document.getElementById('chat-title').textContent = withName;

function showMessage(text) {
  messageEl.textContent = text;
  messageEl.className = 'message error';
}

function formatTime(s) {
  return s ? s.substring(11, 16) : '';
}

function appendMessage(m) {
  const mine = m.sender_id === me.id;
  const div = document.createElement('div');
  div.className = 'bubble ' + (mine ? 'mine' : 'theirs');

  const text = document.createElement('span');
  text.textContent = m.body || '';
  const time = document.createElement('small');
  time.textContent = formatTime(m.created_at);

  div.append(text, time);
  messagesEl.appendChild(div);
}

async function loadMessages() {
  const res = await api('messages.php?with=' + encodeURIComponent(withId) + '&after_id=' + lastId);
  if (!res.ok) {
    showMessage(res.data.message);
    return;
  }
  const list = res.data.data;
  if (list.length === 0) return;
  list.forEach((m) => {
    appendMessage(m);
    lastId = Math.max(lastId, Number(m.id));
  });
  messagesEl.scrollTop = messagesEl.scrollHeight;
}

document.getElementById('send-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const body = bodyInput.value.trim();
  if (!body) return;
  const res = await api('messages.php', {
    method: 'POST',
    body: { receiver_id: withId, body },
  });
  if (res.ok) {
    bodyInput.value = '';
    messageEl.textContent = '';
    loadMessages();
  } else {
    showMessage(res.data.message);
  }
});

loadMessages();
setInterval(loadMessages, 3000); // polling tiap 3 detik