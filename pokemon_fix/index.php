<?php
require_once 'config/session_security.php';
$sessionExists = isset($_SESSION['user_id']);
// include_once 'api/check_expired_auctions.php'; // opsional, bisa diaktifkan nanti
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>RK.POKE - Jual Beli Kartu Pokemon</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .tab-active { background: linear-gradient(135deg, #f97316, #ea580c); color: #fff; }
        .listing-card { transition: all 0.2s; cursor: pointer; }
        .listing-card:hover { transform: translateY(-4px); box-shadow: 0 8px 25px -5px rgba(0,0,0,0.15); }
        .auth-card { background: rgba(255,255,255,0.98); border-radius: 24px; max-width: 450px; margin: 0 auto; }
        .auth-toggle.active { border-bottom: 3px solid #f97316; color: #f97316; }
        .captcha { background: #f3f4f6; border-radius: 12px; padding: 12px; display: inline-flex; align-items: center; gap: 10px; }
        #imagePreviewContainer img { max-width: 150px; max-height: 150px; object-fit: contain; }
        #notifDropdown { max-width: 350px; right: 0; left: auto; }
        .chat-container { max-height: 300px; overflow-y: auto; }
        .message-bubble { max-width: 80%; word-wrap: break-word; }
    </style>
</head>
<body>

<!-- Auth Page -->
<div id="authPage" class="min-h-screen flex items-center justify-center p-4">
    <div class="auth-card p-8 shadow-2xl">
        <div class="text-center mb-8">
            <i class="fas fa-gavel text-orange-500 text-5xl mb-3"></i>
            <h1 class="text-3xl font-bold text-gray-800">RK.<span class="text-orange-500">POKE</span></h1>
            <p class="text-gray-500 mt-2">Jual Beli & Lelang Kartu Pokemon Indonesia</p>
        </div>
        <div class="flex gap-4 mb-6 border-b">
            <button id="showLoginBtn" class="auth-toggle active pb-2 px-4 font-semibold">Login</button>
            <button id="showRegisterBtn" class="auth-toggle pb-2 px-4 text-gray-400 font-semibold">Daftar</button>
        </div>
        <div id="loginForm">
            <input type="text" id="loginUsername" placeholder="Username" class="w-full px-4 py-3 border rounded-xl mb-3">
            <input type="password" id="loginPassword" placeholder="Password" class="w-full px-4 py-3 border rounded-xl mb-3">
            <div class="captcha flex justify-between mb-4">
                <span id="captchaQuestion" class="bg-gray-100 px-3 py-2 rounded">5 + 3 = ?</span>
                <input type="text" id="loginCaptcha" class="w-24 px-3 py-2 border rounded-xl text-center" placeholder="Jawab">
            </div>
            <button id="loginButton" class="w-full bg-orange-500 text-white py-3 rounded-xl font-bold">Login</button>
        </div>
        <div id="registerForm" class="hidden">
            <input type="text" id="regFullname" placeholder="Nama Lengkap" class="w-full px-4 py-3 border rounded-xl mb-2">
            <input type="text" id="regUsername" placeholder="Username (3-20 huruf/angka)" class="w-full px-4 py-3 border rounded-xl mb-2">
            <input type="email" id="regEmail" placeholder="Email" class="w-full px-4 py-3 border rounded-xl mb-2">
            <input type="password" id="regPassword" placeholder="Password (min 6)" class="w-full px-4 py-3 border rounded-xl mb-2">
            <input type="password" id="regConfirmPassword" placeholder="Konfirmasi Password" class="w-full px-4 py-3 border rounded-xl mb-2">
            <input type="tel" id="regWhatsapp" placeholder="WhatsApp" class="w-full px-4 py-3 border rounded-xl mb-2">
            <div class="captcha flex justify-between mb-4">
                <span id="regCaptchaQuestion" class="bg-gray-100 px-3 py-2 rounded">7 + 2 = ?</span>
                <input type="text" id="regCaptcha" class="w-24 px-3 py-2 border rounded-xl text-center" placeholder="Jawab">
            </div>
            <button id="registerButton" class="w-full bg-orange-500 text-white py-3 rounded-xl font-bold">Daftar</button>
        </div>
        <div id="authError" class="mt-4 hidden bg-red-100 text-red-700 p-3 rounded-xl text-sm text-center"></div>
    </div>
</div>

<!-- Main App -->
<div id="mainApp" class="hidden">
    <header class="bg-white shadow-lg sticky top-0 z-30 p-3 flex justify-between items-center">
        <h1 class="text-xl font-bold">RK.<span class="text-orange-500">POKE</span></h1>
        <div class="flex items-center gap-4">
            <span id="currentUsername" class="text-sm font-semibold"></span>
            <div class="relative">
                <button id="notifBtn" class="relative focus:outline-none">
                    <i class="fas fa-bell text-gray-600 text-xl"></i>
                    <span id="notifBadge" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center hidden">0</span>
                </button>
                <div id="notifDropdown" class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg hidden z-50 border">
                    <div class="p-3 border-b font-bold">Notifikasi</div>
                    <div id="notifList" class="max-h-96 overflow-y-auto"></div>
                </div>
            </div>
            <button id="logoutButton" class="bg-red-500 text-white px-3 py-1 rounded-lg text-sm">Logout</button>
        </div>
    </header>
    <div class="container mx-auto px-4 py-3">
        <div class="flex gap-2 bg-gray-100 p-1 rounded-xl">
            <button id="tabMarket" class="tab-active px-4 py-2 rounded-lg font-semibold">Marketplace</button>
            <button id="tabSell" class="px-4 py-2 rounded-lg font-semibold text-gray-600">Jual / Lelang</button>
            <button id="tabMyListings" class="px-4 py-2 rounded-lg font-semibold text-gray-600">Listing Saya</button>
        </div>
    </div>
    <main class="container mx-auto px-4 py-6">
        <div id="secMarket">
            <div class="bg-white rounded-2xl shadow-xl p-5">
                <div id="marketplaceGrid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4"></div>
            </div>
        </div>
        <div id="secSell" class="hidden">
            <div class="bg-white rounded-2xl shadow-xl p-5">
                <h2 class="text-2xl font-bold mb-4">Pasang Kartu</h2>
                <form id="sellForm" enctype="multipart/form-data">
                    <input type="text" id="cardName" placeholder="Nama Kartu *" class="border p-2 rounded-xl w-full mb-2" required>
                    <input type="text" id="cardSet" placeholder="Set" class="border p-2 rounded-xl w-full mb-2">
                    <select id="cardRarity" class="border p-2 rounded-xl w-full mb-2"><option>Common</option><option>Rare</option><option>Ultra Rare</option></select>
                    <select id="cardCondition" class="border p-2 rounded-xl w-full mb-2"><option>Mint</option><option>Near Mint</option><option>Excellent</option></select>
                    <div class="flex gap-2 mb-2">
                        <button type="button" id="typeDirectBtn" class="bg-orange-500 text-white px-4 py-1 rounded">Jual Langsung</button>
                        <button type="button" id="typeAuctionBtn" class="bg-gray-300 px-4 py-1 rounded">Lelang</button>
                    </div>
                    <div id="directFields">
                        <input type="number" id="directPrice" placeholder="Harga (Rp)" class="border p-2 rounded-xl w-full mb-2" min="1000" max="1000000000">
                        <input type="url" id="productLink" placeholder="Link Produk" class="border p-2 rounded-xl w-full mb-2">
                        <select id="directPlatform" class="border p-2 rounded-xl w-full mb-2"><option>shopee</option><option>tokopedia</option><option>whatsapp</option></select>
                    </div>
                    <div id="auctionFields" class="hidden">
                        <input type="number" id="startPrice" placeholder="Harga Awal (Rp)" class="border p-2 rounded-xl w-full mb-2" min="1000">
                        <input type="number" id="minBidIncrement" placeholder="Min Kenaikan (Rp)" value="10000" class="border p-2 rounded-xl w-full mb-2" min="1000" max="1000000" step="1000">
                        <input type="number" id="buyNowPrice" placeholder="Buy Now (opsional)" class="border p-2 rounded-xl w-full mb-2" min="0">
                        <select id="auctionDuration" class="border p-2 rounded-xl w-full mb-2">
                            <option value="1">1 Hari</option>
                            <option value="3" selected>3 Hari</option>
                            <option value="7">7 Hari</option>
                        </select>
                    </div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Foto Kartu (upload dari HP/galeri)</label>
                    <input type="file" id="cardImageFile" accept="image/jpeg,image/png,image/webp" class="border p-2 rounded-xl w-full mb-2">
                    <div id="imagePreviewContainer" class="hidden mb-2">
                        <img id="imagePreview" class="h-24 rounded border">
                        <button type="button" id="removeImageBtn" class="text-red-500 text-xs mt-1">Hapus gambar</button>
                    </div>
                    <textarea id="cardDesc" placeholder="Deskripsi" rows="2" class="border p-2 rounded-xl w-full mb-2"></textarea>
                    <div class="captcha flex justify-between mb-4">
                        <span id="sellCaptchaQuestion" class="bg-gray-100 px-3 py-2 rounded">4 + 6 = ?</span>
                        <input type="text" id="sellCaptcha" class="w-24 px-3 py-2 border rounded-xl text-center" placeholder="Jawab">
                    </div>
                    <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded-xl">Pasang Listing</button>
                </form>
            </div>
        </div>
        <div id="secMyListings" class="hidden">
            <div class="bg-white rounded-2xl shadow-xl p-5">
                <div id="myListingsGrid" class="space-y-3"></div>
            </div>
        </div>
    </main>
</div>

<!-- Modal Detail Kartu -->
<div id="detailModal" class="fixed inset-0 bg-black bg-opacity-75 hidden z-50 items-center justify-center p-4" style="display:none;">
    <div class="bg-white rounded-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b p-4 flex justify-between items-center">
            <h3 id="modalTitle" class="text-xl font-bold">Detail Kartu</h3>
            <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        </div>
        <div id="modalContent" class="p-5"></div>
    </div>
</div>

<script>
// ==================== CAPTCHA ====================
let currentCaptcha = { login: { a:5,b:3 }, register: { a:7,b:2 }, sell: { a:4,b:6 } };
function generateCaptcha(type) {
    let a = Math.floor(Math.random()*9)+1, b = Math.floor(Math.random()*9)+1;
    currentCaptcha[type] = { a, b };
    if(type==='login') document.getElementById('captchaQuestion').innerText = `${a} + ${b} = ?`;
    if(type==='register') document.getElementById('regCaptchaQuestion').innerText = `${a} + ${b} = ?`;
    if(type==='sell') document.getElementById('sellCaptchaQuestion').innerText = `${a} + ${b} = ?`;
}
function verifyCaptcha(type, answer) { return parseInt(answer) === currentCaptcha[type].a + currentCaptcha[type].b; }

// ==================== CSRF ====================
let csrfToken = null;
let csrfRetry = 0;
async function fetchCsrfToken() {
    try {
        const res = await fetch('./api/csrf_token.php');
        if (!res.ok) throw new Error('HTTP '+res.status);
        const data = await res.json();
        csrfToken = data.csrf_token;
        csrfRetry = 0;
        return csrfToken;
    } catch(e) {
        console.error('CSRF token fetch failed', e);
        if (csrfRetry < 3) {
            csrfRetry++;
            await new Promise(r => setTimeout(r, 500));
            return fetchCsrfToken();
        }
        return null;
    }
}

// ==================== API ====================
async function apiCall(endpoint, options = {}) {
    if (!csrfToken) {
        await fetchCsrfToken();
    }
    const headers = {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken,
        ...options.headers
    };
    const res = await fetch(`./api/${endpoint}`, { ...options, headers });
    const text = await res.text();
    let data;
    try { data = JSON.parse(text); } catch(e) { throw new Error('Server returned non-JSON: ' + text.substring(0,200)); }
    if (!res.ok) throw new Error(data.error || 'Request failed');
    return data;
}

// ==================== TAB TOKEN VALIDATION ====================
async function validateTabToken() {
    let tabToken = sessionStorage.getItem('rkpoke_tab_token');
    if (!tabToken) {
        return false;
    }
    try {
        const res = await fetch('./api/verify_tab.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ tab_token: tabToken })
        });
        const data = await res.json();
        if (!data.valid) {
            sessionStorage.removeItem('rkpoke_tab_token');
            return false;
        }
        return true;
    } catch(e) {
        console.error('Tab validation failed', e);
        return false;
    }
}

async function safeLogout(reason = '') {
    console.log('Logout karena:', reason);
    try {
        await fetch('./api/logout.php', { method: 'POST' }).catch(()=>{});
    } catch(e) {}
    sessionStorage.removeItem('rkpoke_tab_token');
    window.location.href = window.location.href;
}

// ==================== GLOBAL STATE ====================
let currentUser = null;
let allListings = [];
let myListings = [];
let uploadedImageUrl = '';
let chatInterval = null;
let currentChatListingId = null;

// ==================== USER VALIDATION ====================
async function checkCurrentUser() {
    try {
        const data = await apiCall('me.php');
        if (data.success && data.user) {
            if (!currentUser || currentUser.id !== data.user.id) {
                currentUser = data.user;
                document.getElementById('currentUsername').innerText = currentUser.fullname || currentUser.username;
                await loadMarketplace();
                await loadMyListings();
                loadNotifications();
            }
        } else {
            if (document.getElementById('mainApp') && !document.getElementById('mainApp').classList.contains('hidden')) {
                await safeLogout('session invalid');
            }
        }
    } catch(e) { console.error('User check failed', e); }
}

// ==================== AUTH ====================
async function handleLogin(e) {
    if (e) e.preventDefault();
    const username = document.getElementById('loginUsername').value.trim().toLowerCase();
    const password = document.getElementById('loginPassword').value;
    const captcha = document.getElementById('loginCaptcha').value;
    if (!verifyCaptcha('login', captcha)) {
        document.getElementById('authError').innerText = 'Captcha salah';
        document.getElementById('authError').classList.remove('hidden');
        generateCaptcha('login');
        return;
    }
    try {
        const result = await apiCall('login.php', { method: 'POST', body: JSON.stringify({ username, password }) });
        if (result.success && result.tab_token) {
            sessionStorage.setItem('rkpoke_tab_token', result.tab_token);
            currentUser = result.user;
            document.getElementById('currentUsername').innerText = currentUser.fullname || currentUser.username;
            document.getElementById('authPage').style.display = 'none';
            document.getElementById('mainApp').classList.remove('hidden');
            await loadMarketplace();
            await loadMyListings();
            loadNotifications();
            alert('Login berhasil');
        } else {
            throw new Error('Login response invalid');
        }
    } catch(err) {
        document.getElementById('authError').innerText = err.message;
        document.getElementById('authError').classList.remove('hidden');
    }
}

async function handleRegister(e) {
    if (e) e.preventDefault();
    const fullname = document.getElementById('regFullname').value.trim();
    const username = document.getElementById('regUsername').value.trim().toLowerCase();
    const email = document.getElementById('regEmail').value.trim();
    const password = document.getElementById('regPassword').value;
    const confirm = document.getElementById('regConfirmPassword').value;
    const whatsapp = document.getElementById('regWhatsapp').value.trim();
    const captcha = document.getElementById('regCaptcha').value;
    if (!username.match(/^[a-z0-9]{3,20}$/)) { alert('Username 3-20 huruf/angka'); return; }
    if (password.length < 6) { alert('Password minimal 6'); return; }
    if (password !== confirm) { alert('Password tidak cocok'); return; }
    if (!verifyCaptcha('register', captcha)) { alert('Captcha salah'); generateCaptcha('register'); return; }
    try {
        const result = await apiCall('register.php', { method: 'POST', body: JSON.stringify({ fullname, username, email, password, whatsapp }) });
        if (result.success && result.tab_token) {
            sessionStorage.setItem('rkpoke_tab_token', result.tab_token);
            currentUser = result.user;
            document.getElementById('currentUsername').innerText = currentUser.fullname || currentUser.username;
            document.getElementById('authPage').style.display = 'none';
            document.getElementById('mainApp').classList.remove('hidden');
            await loadMarketplace();
            await loadMyListings();
            loadNotifications();
            alert('Pendaftaran berhasil! Selamat datang.');
        } else {
            throw new Error('Registrasi gagal');
        }
    } catch(err) {
        alert('Error: ' + err.message);
    }
}

async function logout() {
    await apiCall('logout.php', { method: 'POST' }).catch(()=>{});
    sessionStorage.removeItem('rkpoke_tab_token');
    window.location.reload();
}

// ==================== LISTINGS ====================
async function loadMarketplace() {
    try {
        const data = await apiCall('listings.php?all=true');
        allListings = data.listings;
        renderMarketplace();
    } catch(err) { console.error(err); }
}

async function loadMyListings() {
    if (!currentUser) return;
    try {
        const data = await apiCall('listings.php');
        myListings = data.listings;
        renderMyListings();
    } catch(err) { console.error(err); }
}

async function createListing(listingData) {
    const captcha = document.getElementById('sellCaptcha').value;
    if (!verifyCaptcha('sell', captcha)) { alert('Captcha salah'); generateCaptcha('sell'); return false; }
    if (!uploadedImageUrl) { alert('Silakan upload gambar kartu terlebih dahulu'); return false; }
    
    if (listingData.type === 'auction') {
        if (listingData.min_bid_increment > 1000000) {
            alert('Minimal kenaikan maksimal Rp 1.000.000');
            return false;
        }
        if (listingData.min_bid_increment < 1000) {
            alert('Minimal kenaikan minimal Rp 1.000');
            return false;
        }
    }
    
    listingData.image = uploadedImageUrl;
    try {
        await fetchCsrfToken();
        const response = await fetch('./api/listings.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify(listingData)
        });
        const text = await response.text();
        let data;
        try { data = JSON.parse(text); } catch(e) { throw new Error('Server returned non-JSON: ' + text.substring(0,200)); }
        if (!response.ok) throw new Error(data.error || 'Request failed');
        await loadMarketplace();
        await loadMyListings();
        alert('Listing berhasil dipasang');
        document.getElementById('sellForm').reset();
        uploadedImageUrl = '';
        document.getElementById('imagePreviewContainer').classList.add('hidden');
        generateCaptcha('sell');
        return true;
    } catch(err) {
        console.error('Create listing error:', err);
        alert('Error: ' + err.message);
        return false;
    }
}

async function deleteListing(id) {
    const listing = allListings.find(l => l.id == id) || myListings.find(l => l.id == id);
    if (!listing) {
        alert('Listing tidak ditemukan');
        return;
    }
    if (listing.type === 'auction') {
        const isActive = new Date(listing.end_time) > new Date();
        if (isActive) {
            alert('Lelang tidak dapat dihapus sebelum waktu berakhir!');
            return;
        }
    }
    if (!confirm('Hapus listing ini?')) return;
    try {
        await fetchCsrfToken();
        await apiCall('listings.php', { method: 'DELETE', body: `id=${id}`, headers: { 'Content-Type': 'application/x-www-form-urlencoded' } });
        await loadMarketplace();
        await loadMyListings();
        alert('Listing dihapus');
    } catch(err) { alert(err.message); }
}

// ==================== FORMAT RUPIAH ====================
function formatRupiah(angka) {
    if (angka === undefined || angka === null) return 'Rp 0';
    let num = parseInt(angka);
    if (isNaN(num)) return 'Rp 0';
    return 'Rp ' + num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// ==================== CHAT FUNCTIONS ====================
async function loadChatMessages(listingId) {
    if (!currentUser) return;
    try {
        const res = await fetch(`./api/chat_fetch.php?listing_id=${listingId}`, {
            headers: { 'X-CSRF-Token': csrfToken }
        });
        const data = await res.json();
        const container = document.getElementById(`chatMessages_${listingId}`);
        if (!container) return;
        if (data.messages.length === 0) {
            container.innerHTML = '<div class="text-center text-gray-400 py-4">Belum ada pesan. Mulai chat dengan penjual!</div>';
        } else {
            container.innerHTML = data.messages.map(msg => `
                <div class="mb-3 flex ${msg.sender_id == currentUser?.id ? 'justify-end' : 'justify-start'}">
                    <div class="message-bubble px-3 py-2 rounded-lg ${msg.sender_id == currentUser?.id ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800'}">
                        <div class="text-xs font-bold">${escapeHtml(msg.sender_name)}</div>
                        <div class="text-sm">${escapeHtml(msg.message)}</div>
                        <div class="text-xs opacity-70 mt-1">${new Date(msg.created_at).toLocaleString('id-ID')}</div>
                    </div>
                </div>
            `).join('');
        }
        container.scrollTop = container.scrollHeight;
    } catch(e) { console.error('Load chat error', e); }
}

async function sendChat(listingId) {
    const input = document.getElementById(`chatInput_${listingId}`);
    const message = input.value.trim();
    if (!message) return;
    try {
        await fetchCsrfToken();
        const res = await fetch('./api/chat_send.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
            body: JSON.stringify({ listing_id: listingId, message: message })
        });
        const data = await res.json();
        if (data.success) {
            input.value = '';
            loadChatMessages(listingId);
        } else {
            alert(data.error);
        }
    } catch(e) { alert('Gagal mengirim pesan'); }
}

function startChatPolling(listingId) {
    if (chatInterval) clearInterval(chatInterval);
    currentChatListingId = listingId;
    chatInterval = setInterval(() => {
        if (document.getElementById(`chatMessages_${listingId}`)) {
            loadChatMessages(listingId);
        } else {
            clearInterval(chatInterval);
            currentChatListingId = null;
        }
    }, 5000);
}

// ==================== MODAL DETAIL ====================
function openDetailModal(listing) {
    document.getElementById('modalTitle').innerText = listing.card_name;
    const modalContent = document.getElementById('modalContent');
    
    let actionHtml = '';
    if (listing.type === 'auction') {
        const bids = listing.bids || [];
        const currentBid = bids.length ? Math.max(...bids.map(b => b.amount)) : listing.start_price;
        let minBidIncrement = listing.min_bid_increment;
        let isDataInvalid = false;
        if (minBidIncrement > 1000000) {
            isDataInvalid = true;
            minBidIncrement = 10000;
        }
        const minBid = currentBid + minBidIncrement;
        const isActive = new Date(listing.end_time) > new Date();
        
        if (isActive) {
            actionHtml = `
                <div class="bg-orange-50 p-4 rounded-xl mb-4">
                    <p class="text-sm text-gray-600">Harga awal: ${formatRupiah(listing.start_price)}</p>
                    <p class="text-2xl font-bold text-orange-600">Tawaran tertinggi: ${formatRupiah(currentBid)}</p>
                    <p class="text-sm">Minimal tawaran berikutnya: ${formatRupiah(minBid)}</p>
                    ${isDataInvalid ? '<p class="text-red-500 text-xs mt-1">⚠️ Data kenaikan tidak wajar, hubungi admin</p>' : ''}
                    <p class="text-sm">Berakhir: ${new Date(listing.end_time).toLocaleString('id-ID')}</p>
                    <div class="mt-3">
                        <input type="number" id="bidAmount" class="border p-2 rounded w-full mb-2" placeholder="Masukkan tawaran (min ${formatRupiah(minBid)})">
                        <button onclick="placeBid(${listing.id})" class="bg-orange-500 text-white px-4 py-2 rounded w-full">Tawar</button>
                        ${listing.buy_now_price ? `<button onclick="buyNow(${listing.id})" class="mt-2 bg-green-500 text-white px-4 py-2 rounded w-full">Beli Langsung ${formatRupiah(listing.buy_now_price)}</button>` : ''}
                    </div>
                </div>
                <div class="mt-4">
                    <h4 class="font-bold">Riwayat Tawaran</h4>
                    <ul class="text-sm">
                        ${bids.sort((a,b)=>b.amount - a.amount).slice(0,5).map(b => `<li>${formatRupiah(b.amount)} - ${b.bidder_name} (${new Date(b.time).toLocaleString('id-ID')})</li>`).join('')}
                        ${!bids.length ? '<li>Belum ada tawaran</li>' : ''}
                    </ul>
                </div>
            `;
        } else {
            const winnerBid = bids.length ? bids.sort((a,b)=>b.amount - a.amount)[0] : null;
            let contactHtml = '';
            if (winnerBid && currentUser) {
                const isWinner = winnerBid.bidder_id === currentUser.id;
                const isSeller = listing.user_id === currentUser.id;
                if (isWinner && listing.seller_whatsapp) {
                    let wa = listing.seller_whatsapp.replace(/[^0-9]/g, '');
                    if (!wa.startsWith('62') && wa.startsWith('0')) wa = '62' + wa.substring(1);
                    contactHtml = `<p class="mt-2">Hubungi penjual via WhatsApp: <a href="https://wa.me/${wa}" target="_blank" class="text-green-600 underline">Klik di sini</a></p>`;
                } else if (isSeller && winnerBid.bidder_whatsapp) {
                    let wa = winnerBid.bidder_whatsapp.replace(/[^0-9]/g, '');
                    if (!wa.startsWith('62') && wa.startsWith('0')) wa = '62' + wa.substring(1);
                    contactHtml = `<p class="mt-2">Hubungi pemenang via WhatsApp: <a href="https://wa.me/${wa}" target="_blank" class="text-green-600 underline">Klik di sini</a></p>`;
                }
            }
            actionHtml = `
                <div class="bg-gray-100 p-4 rounded-xl mb-4">
                    <p class="font-bold text-red-600">Lelang telah berakhir.</p>
                    ${winnerBid ? `<p>Pemenang: ${escapeHtml(winnerBid.bidder_name)} dengan tawaran ${formatRupiah(winnerBid.amount)}</p>` : '<p>Tidak ada pemenang</p>'}
                    ${contactHtml}
                    <p class="text-xs text-gray-500 mt-2">Segera hubungi via WhatsApp untuk menyelesaikan transaksi.</p>
                </div>
                <div class="mt-4">
                    <h4 class="font-bold">Riwayat Tawaran</h4>
                    <ul class="text-sm">
                        ${bids.sort((a,b)=>b.amount - a.amount).map(b => `<li>${formatRupiah(b.amount)} - ${b.bidder_name} (${new Date(b.time).toLocaleString('id-ID')})</li>`).join('')}
                    </ul>
                </div>
            `;
        }
    } else {
        actionHtml = `
            <div class="bg-green-50 p-4 rounded-xl mb-4">
                <p class="text-2xl font-bold text-green-600">Harga: ${formatRupiah(listing.price)}</p>
                <a href="${listing.link}" target="_blank" class="mt-2 inline-block bg-blue-500 text-white px-4 py-2 rounded">Beli di ${listing.platform}</a>
            </div>
        `;
    }
    
    // Chat section
    const chatHtml = `
        <div class="mt-6 border-t pt-4">
            <h4 class="font-bold mb-2"><i class="fas fa-comment-dots"></i> Chat dengan Penjual</h4>
            <div id="chatMessages_${listing.id}" class="chat-container bg-gray-50 rounded-lg p-3 h-64 overflow-y-auto mb-3">
                <div class="text-center text-gray-400 py-4">Memuat pesan...</div>
            </div>
            <div class="flex gap-2">
                <input type="text" id="chatInput_${listing.id}" placeholder="Tulis pesan..." class="flex-1 border rounded-lg px-3 py-2">
                <button onclick="sendChat(${listing.id})" class="bg-blue-500 text-white px-4 py-2 rounded-lg">Kirim</button>
            </div>
        </div>
    `;
    
    modalContent.innerHTML = `
        <img src="${listing.image || 'https://via.placeholder.com/300'}" class="w-full rounded-xl mb-4">
        <p><strong>Set:</strong> ${listing.set || '-'}</p>
        <p><strong>Rarity:</strong> ${listing.rarity}</p>
        <p><strong>Kondisi:</strong> ${listing.condition}</p>
        <p><strong>Deskripsi:</strong> ${listing.desc || '-'}</p>
        <p><strong>Penjual:</strong> ${listing.seller_username}</p>
        ${actionHtml}
        ${currentUser ? chatHtml : '<div class="mt-6 border-t pt-4 text-center text-gray-500">Login untuk chat dengan penjual</div>'}
    `;
    document.getElementById('detailModal').style.display = 'flex';
    
    if (currentUser) {
        loadChatMessages(listing.id);
        startChatPolling(listing.id);
    }
}

function closeModal() {
    document.getElementById('detailModal').style.display = 'none';
    if (chatInterval) {
        clearInterval(chatInterval);
        chatInterval = null;
    }
}

async function placeBid(listingId) {
    const amount = parseInt(document.getElementById('bidAmount').value);
    if (!amount) { alert('Masukkan nominal tawaran'); return; }
    try {
        await fetchCsrfToken();
        const res = await fetch('./api/place_bid.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
            body: JSON.stringify({ listing_id: listingId, amount: amount })
        });
        const data = await res.json();
        if (data.success) {
            alert('Tawaran berhasil!');
            closeModal();
            loadMarketplace();
            loadNotifications();
        } else {
            alert(data.error);
        }
    } catch(e) { alert('Error: ' + e.message); }
}

async function buyNow(listingId) {
    if (!confirm('Anda yakin ingin membeli kartu ini langsung? Seller akan menghubungi Anda.')) return;
    try {
        await fetchCsrfToken();
        const res = await fetch('./api/buy_now.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
            body: JSON.stringify({ listing_id: listingId })
        });
        const data = await res.json();
        if (data.success) {
            alert(data.message);
            closeModal();
            loadMarketplace();
            loadNotifications();
        } else {
            alert(data.error);
        }
    } catch(e) { alert('Error: ' + e.message); }
}

// ==================== RENDER ====================
function renderMarketplace() {
    const container = document.getElementById('marketplaceGrid');
    if (!allListings.length) { container.innerHTML = '<div class="col-span-full text-center py-10">Belum ada listing</div>'; return; }
    container.innerHTML = allListings.map(l => `
        <div class="bg-white border rounded-xl p-3 shadow listing-card" onclick='openDetailModal(${JSON.stringify(l).replace(/'/g, "\\'")})'>
            <img src="${escapeHtml(l.image) || 'https://via.placeholder.com/150'}" class="h-32 mx-auto object-contain">
            <h3 class="font-bold mt-2">${escapeHtml(l.card_name)}</h3>
            <p class="text-sm text-gray-600">${l.type === 'direct' ? '💰 ' + formatRupiah(l.price) : '⏱️ Lelang'}</p>
            <p class="text-xs text-gray-400">Seller: ${escapeHtml(l.seller_username)}</p>
        </div>
    `).join('');
}

function renderMyListings() {
    const container = document.getElementById('myListingsGrid');
    if (!myListings.length) { container.innerHTML = '<div class="text-center py-10">Belum ada listing</div>'; return; }
    container.innerHTML = myListings.map(l => `
        <div class="bg-gray-50 border rounded-xl p-3 flex justify-between items-center">
            <div><strong>${escapeHtml(l.card_name)}</strong> - ${l.type==='direct'?formatRupiah(l.price):'Lelang'}</div>
            <button onclick="deleteListing(${l.id})" class="bg-red-500 text-white px-3 py-1 rounded text-sm">Hapus</button>
        </div>
    `).join('');
}

// ==================== NOTIFICATIONS ====================
async function loadNotifications() {
    if (!currentUser) return;
    try {
        const data = await apiCall('notifications.php');
        const unread = data.unread;
        const badge = document.getElementById('notifBadge');
        if (unread > 0) {
            badge.innerText = unread > 99 ? '99+' : unread;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
        const list = document.getElementById('notifList');
        if (list) {
            list.innerHTML = data.notifications.map(n => `
                <div class="p-3 border-b hover:bg-gray-50 ${n.is_read ? '' : 'bg-blue-50'}">
                    <div class="font-semibold">${escapeHtml(n.title)}</div>
                    <div class="text-sm text-gray-600">${escapeHtml(n.message)}</div>
                    <div class="text-xs text-gray-400 mt-1">${new Date(n.created_at).toLocaleString('id-ID')}</div>
                    ${!n.is_read ? `<button onclick="markRead(${n.id})" class="text-xs text-blue-500 mt-1">Tandai sudah dibaca</button>` : ''}
                </div>
            `).join('');
            if (!data.notifications.length) list.innerHTML = '<div class="p-3 text-gray-500">Tidak ada notifikasi</div>';
        }
    } catch(e) { console.error(e); }
}

async function markRead(id) {
    try {
        await apiCall('mark_read.php', { method: 'POST', body: JSON.stringify({ id }) });
        loadNotifications();
    } catch(e) { alert(e.message); }
}

// ==================== UTILITIES ====================
function escapeHtml(str) { if(!str) return ''; return str.replace(/[&<>]/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;'})[m]); }

// ==================== TAB & EVENT LISTENERS ====================
function activateTab(tab) {
    document.getElementById('secMarket').classList.toggle('hidden', tab !== 'market');
    document.getElementById('secSell').classList.toggle('hidden', tab !== 'sell');
    document.getElementById('secMyListings').classList.toggle('hidden', tab !== 'myListings');
    const btns = { market:'tabMarket', sell:'tabSell', myListings:'tabMyListings' };
    for(let k in btns) {
        let btn = document.getElementById(btns[k]);
        if(k===tab) btn.classList.add('tab-active'); else btn.classList.remove('tab-active');
    }
    if(tab === 'market') loadMarketplace();
    if(tab === 'myListings') { checkCurrentUser(); loadMyListings(); }
}

function showLogin() {
    document.getElementById('loginForm').classList.remove('hidden');
    document.getElementById('registerForm').classList.add('hidden');
    document.getElementById('showLoginBtn').classList.add('active');
    document.getElementById('showRegisterBtn').classList.remove('active');
    generateCaptcha('login');
}
function showRegister() {
    document.getElementById('loginForm').classList.add('hidden');
    document.getElementById('registerForm').classList.remove('hidden');
    document.getElementById('showRegisterBtn').classList.add('active');
    document.getElementById('showLoginBtn').classList.remove('active');
    generateCaptcha('register');
}

// ==================== UPLOAD GAMBAR ====================
document.getElementById('cardImageFile').addEventListener('change', async function(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(ev) {
        document.getElementById('imagePreview').src = ev.target.result;
        document.getElementById('imagePreviewContainer').classList.remove('hidden');
    };
    reader.readAsDataURL(file);
    const formData = new FormData();
    formData.append('image', file);
    try {
        await fetchCsrfToken();
        const response = await fetch('./api/upload_image.php', {
            method: 'POST',
            headers: { 'X-CSRF-Token': csrfToken },
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            uploadedImageUrl = result.image_url;
            alert('Gambar berhasil diupload');
        } else {
            alert('Upload gagal: ' + result.error);
            document.getElementById('cardImageFile').value = '';
            document.getElementById('imagePreviewContainer').classList.add('hidden');
        }
    } catch(err) {
        alert('Error upload: ' + err.message);
        document.getElementById('cardImageFile').value = '';
        document.getElementById('imagePreviewContainer').classList.add('hidden');
    }
});

document.getElementById('removeImageBtn')?.addEventListener('click', function() {
    uploadedImageUrl = '';
    document.getElementById('cardImageFile').value = '';
    document.getElementById('imagePreviewContainer').classList.add('hidden');
});

// ==================== NOTIFICATION DROPDOWN ====================
document.getElementById('notifBtn').addEventListener('click', () => {
    const dropdown = document.getElementById('notifDropdown');
    dropdown.classList.toggle('hidden');
    if (!dropdown.classList.contains('hidden')) {
        loadNotifications();
    }
});
document.addEventListener('click', function(event) {
    const btn = document.getElementById('notifBtn');
    const dropdown = document.getElementById('notifDropdown');
    if (btn && dropdown && !btn.contains(event.target) && !dropdown.contains(event.target)) {
        dropdown.classList.add('hidden');
    }
});

// ==================== INIT ====================
document.addEventListener('DOMContentLoaded', async () => {
    const loginBtn = document.getElementById('loginButton');
    const registerBtn = document.getElementById('registerButton');
    if (loginBtn) loginBtn.addEventListener('click', (e) => { e.preventDefault(); handleLogin(e); });
    if (registerBtn) registerBtn.addEventListener('click', (e) => { e.preventDefault(); handleRegister(e); });
    document.getElementById('logoutButton').addEventListener('click', logout);
    document.getElementById('showLoginBtn').addEventListener('click', showLogin);
    document.getElementById('showRegisterBtn').addEventListener('click', showRegister);
    document.getElementById('tabMarket').addEventListener('click', () => activateTab('market'));
    document.getElementById('tabSell').addEventListener('click', () => activateTab('sell'));
    document.getElementById('tabMyListings').addEventListener('click', () => activateTab('myListings'));
    
    document.getElementById('sellForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!currentUser) { alert('Login dulu'); return; }
        let data = {
            type: window.saleType,
            card_name: document.getElementById('cardName').value.trim(),
            set: document.getElementById('cardSet').value,
            rarity: document.getElementById('cardRarity').value,
            condition: document.getElementById('cardCondition').value,
            desc: document.getElementById('cardDesc').value
        };
        if (window.saleType === 'direct') {
            const price = parseInt(document.getElementById('directPrice').value);
            if (isNaN(price) || price < 1000) {
                alert('Harga minimal Rp 1.000');
                return;
            }
            data.price = price;
            data.link = document.getElementById('productLink').value;
            data.platform = document.getElementById('directPlatform').value;
        } else {
            const startPrice = parseInt(document.getElementById('startPrice').value);
            if (isNaN(startPrice) || startPrice < 1000) {
                alert('Harga awal minimal Rp 1.000');
                return;
            }
            data.start_price = startPrice;
            let minBid = parseInt(document.getElementById('minBidIncrement').value);
            if (isNaN(minBid)) minBid = 10000;
            if (minBid < 1000) minBid = 1000;
            if (minBid > 1000000) {
                alert('Minimal kenaikan maksimal Rp 1.000.000');
                return;
            }
            data.min_bid_increment = minBid;
            data.buy_now_price = document.getElementById('buyNowPrice').value ? parseInt(document.getElementById('buyNowPrice').value) : null;
            data.auction_duration = parseInt(document.getElementById('auctionDuration').value);
        }
        await createListing(data);
    });
    
    window.saleType = 'direct';
    document.getElementById('typeDirectBtn').onclick = () => { window.saleType='direct'; document.getElementById('directFields').classList.remove('hidden'); document.getElementById('auctionFields').classList.add('hidden'); };
    document.getElementById('typeAuctionBtn').onclick = () => { window.saleType='auction'; document.getElementById('directFields').classList.add('hidden'); document.getElementById('auctionFields').classList.remove('hidden'); };
    
    await fetchCsrfToken();
    if (<?= json_encode($sessionExists) ?>) {
        const isValid = await validateTabToken();
        if (isValid) {
            try {
                const me = await apiCall('me.php');
                if (me.success && me.user) {
                    currentUser = me.user;
                    document.getElementById('currentUsername').innerText = currentUser.fullname || currentUser.username;
                    document.getElementById('authPage').style.display = 'none';
                    document.getElementById('mainApp').classList.remove('hidden');
                    loadMarketplace();
                    loadMyListings();
                    loadNotifications();
                } else {
                    document.getElementById('authPage').style.display = 'flex';
                    generateCaptcha('login');
                    generateCaptcha('register');
                }
            } catch(e) {
                console.error(e);
                document.getElementById('authPage').style.display = 'flex';
                generateCaptcha('login');
                generateCaptcha('register');
            }
        } else {
            document.getElementById('authPage').style.display = 'flex';
            generateCaptcha('login');
            generateCaptcha('register');
        }
    } else {
        document.getElementById('authPage').style.display = 'flex';
        generateCaptcha('login');
        generateCaptcha('register');
    }
    generateCaptcha('sell');
    
    setInterval(async () => {
        try {
            await apiCall('me.php');
        } catch(e) {
            if (e.message.includes('Unauthorized') || e.message.includes('Not logged in')) {
                logout();
            }
        }
    }, 30000);
    setInterval(() => {
        if (currentUser) loadNotifications();
    }, 30000);
});
</script>
</body>
</html>