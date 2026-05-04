const API_BASE = window.API_BASE || '/dev/aibiorenew/api.php';

async function apiCall(path, method = 'GET', body = null) {
  try {
    const token = localStorage.getItem('mp_token');
    const opts = {
      method,
      headers: { 'Content-Type': 'application/json' }
    };
    if (token) opts.headers['Authorization'] = `Bearer ${token}`;
    if (body) opts.body = JSON.stringify(body);

    const res = await fetch(API_BASE + path, opts);
    if (res.status === 401) {
      localStorage.removeItem('mp_token');
      location.href = '/dev/aibiorenew/app/frontend/area-pessoal.html';
      return null;
    }
    return await res.json();
  } catch (err) {
    console.error('apiCall erro:', path, err);
    return { erro: 'Sem ligação ao servidor (' + path + ')' };
  }
}

function getToken() { return localStorage.getItem('mp_token'); }
function setToken(t) { localStorage.setItem('mp_token', t); }
function clearToken() { localStorage.removeItem('mp_token'); }
function isLoggedIn() { return !!localStorage.getItem('mp_token'); }
