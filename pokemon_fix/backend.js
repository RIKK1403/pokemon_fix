// Backend API Integration - Drop this in index.html <script> or index.php
window.BACKEND_MODE = true;
window.API_BASE = './api/';

async function api(endpoint, options = {}) {
  const url = window.API_BASE + endpoint;
  const response = await fetch(url, options);
  if (!response.ok) throw new Error(await response.text());
  return response.json();
}

async function backendLogin(username, password) {
  const result = await api('login.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({username, password})
  });
  if (result.success) {
    window.currentUser = result.user;
    document.getElementById('currentUsername').textContent = result.user.fullname || result.user.username;
    document.getElementById('authPage').classList.add('hidden');
    document.getElementById('mainApp').classList.remove('hidden');
    await loadMyListings();
    showToast(`Welcome ${result.user.fullname || result.user.username}!`);
  } else {
    throw new Error(result.error);
  }
}

async function loadMyListings() {
  const result = await api('listings.php');
  allListings = result.listings || [];
  renderAll();
}

// Override handleLogin for backend
const originalHandleLogin = handleLogin;
handleLogin = async function() {
  if (window.BACKEND_MODE) {
    const username = document.getElementById('loginUsername').value.trim().toLowerCase();
    const password = document.getElementById('loginPassword').value;
    const captchaAnswer = document.getElementById('loginCaptcha').value;
    const errorDiv = document.getElementById('authError');
    
    if (!verifyCaptcha('login', captchaAnswer)) {
      errorDiv.innerHTML = 'Captcha salah!';
      errorDiv.classList.remove('hidden');
      return;
    }
    
    try {
      await backendLogin(username, password);
    } catch (e) {
      errorDiv.innerHTML = e.message;
      errorDiv.classList.remove('hidden');
    }
  } else {
    originalHandleLogin();
  }
};

console.log('Backend mode loaded. Login: usera/userb pass=password123');

