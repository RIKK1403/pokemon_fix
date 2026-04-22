<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RK.Poké TCG - Jual Beli Kartu Pokémon</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .glass { backdrop-filter: blur(20px); }
        .pokeball-bg { background: radial-gradient(circle at 30% 30%, #ff6b6b, #4ecdc4, #45b7d1); }
    </style>
</head>
<body class="pokeball-bg min-h-screen font-sans">
    <!-- Header -->
    <header class="glass shadow-2xl sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                        <i class="fas fa-pokeball text-2xl text-red-500"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-white drop-shadow-lg">RK.Poké TCG</h1>
                        <p class="text-sm text-white/80">Jual Beli Kartu Pokémon</p>
                    </div>
                </div>
                <div id="userStatus" class="flex items-center space-x-2">
                    <!-- Dynamic user status -->
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-6xl mx-auto px-6 py-12">
        <!-- Auth Forms -->
        <div id="authSection" class="glass rounded-3xl p-10 mb-12 shadow-3xl max-w-2xl mx-auto hidden">
            <div class="text-center mb-8">
                <h2 id="authTitle" class="text-4xl font-bold bg-gradient-to-r from-purple-400 to-pink-400 bg-clip-text text-transparent mb-2">Masuk Akun</h2>
                <p id="authSubtitle" class="text-xl text-white/80">Kelola listing Pokémon card kamu</p>
            </div>
            
            <div class="grid md:grid-cols-2 gap-8">
                <!-- Login Form -->
                <div class="space-y-4">
                    <h3 class="text-2xl font-bold text-white">Masuk</h3>
                    <input id="loginUsername" type="text" placeholder="Username" class="w-full p-4 rounded-2xl bg-white/20 border border-white/30 text-white placeholder-white/60 focus:ring-4 focus:ring-purple-500/50 transition-all">
                    <input id="loginPassword" type="password" placeholder="Password" class="w-full p-4 rounded-2xl bg-white/20 border border-white/30 text-white placeholder-white/60 focus:ring-4 focus:ring-purple-500/50 transition-all">
                    <button onclick="login()" class="w-full bg-gradient-to-r from-blue-500 to-purple-600 text-white py-4 rounded-2xl font-bold text-lg hover:scale-105 transition-all shadow-xl">
                        <i class="fas fa-sign-in-alt mr-2"></i>Masuk
                    </button>
                </div>
                
                <!-- Register Form -->
                <div class="space-y-4">
                    <h3 class="text-2xl font-bold text-white">Daftar</h3>
                    <input id="regUsername" type="text" placeholder="Username (3-20 char)" class="w-full p-4 rounded-2xl bg-white/20 border border-white/30 text-white placeholder-white/60 focus:ring-4 focus:ring-green-500/50 transition-all">
                    <input id="regFullname" type="text" placeholder="Nama Lengkap" class="w-full p-4 rounded-2xl bg-white/20 border border-white/30 text-white placeholder-white/60 focus:ring-4 focus:ring-green-500/50 transition-all">
                    <input id="regEmail" type="email" placeholder="Email" class="w-full p-4 rounded-2xl bg-white/20 border border-white/30 text-white placeholder-white/60 focus:ring-4 focus:ring-green-500/50 transition-all">
                    <input id="regWhatsapp" type="tel" placeholder="Whatsapp" class="w-full p-4 rounded-2xl bg-white/20 border border-white/30 text-white placeholder-white/60 focus:ring-4 focus:ring-green-500/50 transition-all">
                    <input id="regPassword" type="password" placeholder="Password (min 6)" class="w-full p-4 rounded-2xl bg-white/20 border border-white/30 text-white placeholder-white/60 focus:ring-4 focus:ring-green-500/50 transition-all">
                    <button onclick="register()" class="w-full bg-gradient-to-r from-green-500 to-emerald-600 text-white py-4 rounded-2xl font-bold text-lg hover:scale-105 transition-all shadow-xl">
                        <i class="fas fa-user-plus mr-2"></i>Daftar Gratis
                    </button>
                </div>
            </div>
            <div class="text-center mt-8">
                <button onclick="showMarketplace()" class="text-white/80 hover:text-white transition-all">
                    <i class="fas fa-times mr-2"></i>Kembali ke Marketplace
                </button>
            </div>
        </div>

        <!-- Marketplace Tabs -->
        <div id="marketplaceSection" class="glass rounded-3xl p-8 shadow-3xl">
            <div class="flex flex-wrap items-center gap-4 mb-8 justify-center">
                <button onclick="loadListings('direct')" class="tab-btn px-8 py-4 bg-white/20 rounded-2xl font-bold text-white hover:bg-white/40 transition-all flex items-center space-x-2">
                    <i class="fas fa-tag"></i><span>Jual Langsung</span>
                </button>
                <button onclick="loadListings('auction')" class="tab-btn px-8 py-4 bg-white/20 rounded-2xl font-bold text-white hover:bg-white/40 transition-all flex items-center space-x-2">
                    <i class="fas fa-gavel"></i><span>Lelang</span>
                </button>
                <button onclick="loadMyListings()" class="tab-btn px-8 py-4 bg-white/20 rounded-2xl font-bold text-white hover:bg-white/40 transition-all flex items-center space-x-2">
                    <i class="fas fa-list"></i><span>Listing Saya</span>
                </button>
                <button onclick="showAuth()" class="px-8 py-4 bg-gradient-to-r from-pink-500 to-purple-600 text-white rounded-2xl font-bold hover:scale-105 transition-all shadow-xl flex items-center space-x-2">
                    <i class="fas fa-user-circle"></i><span id="loginBtnText">Masuk</span>
                </button>
            </div>

            <!-- Search -->
            <div class="flex gap-4 mb-8 max-w-2xl mx-auto">
                <input id="searchInput" type="text" placeholder="Cari kartu Pokémon..." class="flex-1 p-4 rounded-2xl bg-white/20 border border-white/30 text-white placeholder-white/60 focus:ring-4 focus:ring-blue-500/50 transition-all">
                <button onclick="searchListings()" class="px-8 py-4 bg-blue-500 text-white rounded-2xl font-bold hover:bg-blue-600 transition-all">
                    <i class="fas fa-search"></i>
                </button>
            </div>

            <!-- Listings -->
            <div id="listingsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="text-center py-16 text-white/60">
                    <i class="fas fa-pokeball text-6xl mb-4 opacity-50 animate-spin"></i>
                    <p class="text-xl">Muat listing Pokémon cards...</p>
                </div>
            </div>
        </div>

        <!-- Status Footer -->
        <div class="glass mt-12 p-6 rounded-2xl text-center text-white/80">
            <h3 class="font-bold text-lg mb-2"><i class="fas fa-info-circle mr-2"></i>Status Sistem</h3>
            <div class="flex flex-wrap justify-center gap-4 text-sm">
                <span>API: <a href="http://127.0.0.1:8000/api-fixed.php/api/test" target="_blank" class="text-blue-300 hover:text-blue-200"><i class="fas fa-check-circle text-green-400"></i></a></span>
                <span>Supabase: <i class="fas fa-database text-purple-400"></i> Connected</span>
                <span>Vercel: <a href="#" onclick="deployVercel()" class="text-green-400 hover:text-green-300">Deploy Now</a></span>
            </div>
        </div>
    </main>

    <script>
        let currentType = 'direct';
        let isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
        let currentUser = <?php echo isset($_SESSION['user_id']) ? json_encode($_SESSION) : '{}'; ?>;

        // API Call - Connected to Supabase
        async function apiCall(path, options = {}) {
            const res = await fetch(`/api-fixed.php/api/${path}`, {
                ...options,
                headers: { 
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await res.json();
            if (!res.ok) throw data;
            return data;
        }

        // User Status
        function updateUserStatus() {
            const status = document.getElementById('userStatus');
            const loginBtn = document.getElementById('loginBtnText');
            if (isLoggedIn) {
                status.innerHTML = `
                    <div class="flex items-center space-x-3 p-2 bg-white/20 rounded-2xl">
                        <i class="fas fa-user-circle text-2xl text-green-400"></i>
                        <span class="font-bold text-white">Logged in</span>
                        <button onclick="logout()" class="bg-red-500 text-white px-4 py-1 rounded-xl text-sm hover:bg-red-600">
                            Keluar
                        </button>
                    </div>
                `;
                loginBtn.textContent = currentUser.username || 'User';
                document.getElementById('authSection').classList.add('hidden');
                document.getElementById('marketplaceSection').classList.remove('hidden');
            } else {
                status.innerHTML = '<button onclick="showAuth()" class="bg-gradient-to-r from-pink-500 to-purple-600 text-white px-6 py-2 rounded-2xl font-bold hover:scale-105">Masuk / Daftar</button>';
                loginBtn.textContent = 'Masuk';
            }
        }

        // Auth Functions
        async function showAuth() {
            document.getElementById('authSection').classList.remove('hidden');
            document.getElementById('marketplaceSection').classList.add('hidden');
            document.getElementById('authTitle').textContent = 'Masuk Akun';
        }

        async function login() {
            const username = document.getElementById('loginUsername').value;
            const password = document.getElementById('loginPassword').value;
            try {
                const user = await apiCall('login', {
                    method: 'POST',
                    body: JSON.stringify({username, password})
                });
                isLoggedIn = true;
                currentUser = user;
                updateUserStatus();
                alert(`Selamat datang ${user.username}!`);
            } catch(e) {
                alert(`Login gagal: ${e.error || 'Cek username/password'}`);
            }
        }

        async function register() {
            const data = {
                username: document.getElementById('regUsername').value,
                fullname: document.getElementById('regFullname').value,
                email: document.getElementById('regEmail').value,
                whatsapp: document.getElementById('regWhatsapp').value,
                password: document.getElementById('regPassword').value
            };
            try {
                const result = await apiCall('register', {
                    method: 'POST',
                    body: JSON.stringify(data)
                });
                alert(`Daftar sukses! ID: ${result.id}\nSilakan login.`);
                document.getElementById('authTitle').textContent = 'Masuk Akun';
            } catch(e) {
                alert(`Daftar gagal: ${e.error || 'Cek data'}`);
            }
        }

        function showMarketplace() {
            document.getElementById('authSection').classList.add('hidden');
            document.getElementById('marketplaceSection').classList.remove('hidden');
        }

        async function logout() {
            try {
                sessionStorage.clear();
                isLoggedIn = false;
                location.reload();
            } catch(e) {
                location.reload();
            }
        }

        // Marketplace Functions
        async function loadListings(type = 'direct') {
            currentType = type;
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('bg-white/40'));
            event.target.classList.add('bg-white/40');
            
            try {
                const listings = await apiCall(`listings?type=${type}`);
                displayListings(listings);
            } catch(e) {
                console.error('Load listings failed:', e);
            }
        }

        async function loadMyListings() {
            if (!isLoggedIn) {
                showAuth();
                return;
            }
            try {
                const listings = await apiCall('my-listings');
                displayListings(listings, true);
            } catch(e) {
                console.error('My listings failed:', e);
            }
        }

        function searchListings() {
            const search = document.getElementById('searchInput').value;
            loadListings(currentType + (search ? `&search=${encodeURIComponent(search)}` : ''));
        }

        function displayListings(listings, isMine = false) {
            const container = document.getElementById('listingsContainer');
            if (!listings.length) {
                container.innerHTML = `
                    <div class="col-span-full text-center py-20">
                        <i class="fas fa-inbox text-8xl text-white/30 mb-6"></i>
                        <h3 class="text-3xl font-bold text-white mb-2">Belum ada listing</h3>
                        <p class="text-xl text-white/60">${isMine ? 'Buat listing pertama kamu' : 'Belum ada penawaran'}</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = listings.map(l => `
                <div class="glass p-6 rounded-3xl hover:scale-[1.02] transition-all shadow-2xl border border-white/20">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="text-2xl font-bold text-white drop-shadow-lg">${l.card_name || 'Unnamed Card'}</h3>
                            <div class="flex items-center space-x-2 mt-1">
                                <span class="px-3 py-1 bg-${l.type === 'auction' ? 'yellow' : 'green'}-400/20 text-${l.type === 'auction' ? 'yellow' : 'green'}-400 rounded-full text-sm font-bold">${l.type?.toUpperCase()}</span>
                                <span class="px-2 py-1 bg-blue-400/20 text-blue-400 rounded-full text-xs">Rarity: ${l.rarity || 'N/A'}</span>
                            </div>
                        </div>
                        <div class="text-right">
                            ${l.price ? `<div class="text-3xl font-black text-green-400 drop-shadow-lg">Rp ${Number(l.price).toLocaleString()}</div>` : ''}
                            ${l.type === 'auction' ? `
                                <div class="text-xl font-bold text-yellow-400">Rp ${Math.max(...(l.bids?.map(b=>b.amount) || [l.start_price || 0])).toLocaleString()}</div>
                                <div class="text-xs text-white/60">${l.bids?.length || 0} tawaran</div>
                            ` : ''}
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
                        <div>
                            <p><strong>Set:</strong> <span class="text-white/80">${l.set_name || 'N/A'}</span></p>
                            <p><strong>Kondisi:</strong> <span class="text-white/80">${l.condition || 'N/A'}</span></p>
                        </div>
                        <div class="text-right">
                            <p><strong>Views:</strong> <span class="text-white/80">${l.views || 0}</span></p>
                            <p><strong>Report:</strong> <span class="text-red-400">${l.report_count || 0}</span></p>
                        </div>
                    </div>
                    <div class="mb-4">
                        <p class="text-white/70 line-clamp-3">${l.description || 'Tidak ada deskripsi'}</p>
                    </div>
                    ${l.link ? `<a href="${l.link}" target="_blank" class="block w-full bg-blue-500 text-white text-center py-3 rounded-2xl font-bold hover:bg-blue-600 mb-4 transition-all"><i class="fab fa-${l.platform === 'tcgplayer' ? 'chrome' : 'shopify'} mr-2"></i>Lihat di ${l.platform}</a>` : ''}
                    <div class="flex items-center justify-between text-sm text-white/70">
                        <span><i class="fas fa-user mr-1"></i>${l.seller_username}</span>
                        <span><i class="fas fa-clock mr-1"></i>${new Date(l.date_created).toLocaleDateString('id-ID')}</span>
                    </div>
                    ${isMine ? `<button onclick="deleteListing('${l.id}')" class="w-full mt-4 bg-red-500 text-white py-3 rounded-2xl font-bold hover:bg-red-600 transition-all flex items-center justify-center"><i class="fas fa-trash mr-2"></i>Hapus Listing</button>` : ''}
                </div>
            </div>
            `).join('');
        }

        // Delete Listing
        async function deleteListing(id) {
            if (confirm('Hapus listing ini?')) {
                try {
                    await apiCall(`delete?id=${id}`, {method: 'DELETE'});
                    loadMyListings();
                    alert('Listing dihapus');
                } catch(e) {
                    alert('Gagal hapus: ' + e.error);
                }
            }
        }

        // Vercel Deploy Helper
        function deployVercel() {
            navigator.clipboard.writeText(`
npm i -g vercel
vercel login
vercel
vercel env add SUPABASE_PASS
vercel --prod
            `);
            alert('Commands copied! Paste in new terminal.');
        }

        // Init
        updateUserStatus();
        loadListings('direct');
        
        // Real-time search
        document.getElementById('searchInput').addEventListener('input', (e) => {
            if (e.target.value.length > 2) searchListings();
        });
    </script>
</body>
</html>
