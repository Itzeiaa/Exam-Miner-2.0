<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Exam Miner 2.0 - Dashboard</title>
  @vite('resources/css/app.css')
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>

  <!-- Hard mobile/desktop visibility (bypasses Tailwind variants if they fail) -->
  <style>
    @media (max-width: 767.98px) {
      .only-desktop { display: none !important; }
      .only-mobile  { display: block !important; }
    }
    @media (min-width: 768px) {
      .only-desktop { display: block !important; }
      .only-mobile  { display: none !important; }
    }
  </style>
</head>

<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
  <script>
    // Require JWT from your API-based login
    const token = localStorage.getItem('jwt_token');
    if (!token) location.replace('/login');
  </script>

  <!-- ===== MOBILE TOP BAR (shown only on mobile) ===== -->
  <header class="sticky top-0 z-50 bg-white/70 backdrop-blur border-b border-gray-200 only-mobile">
    <div class="flex items-center justify-between px-4 py-3">
      <button id="mOpenBtn"
              class="inline-flex items-center justify-center w-10 h-10 rounded-lg border border-gray-200 bg-white shadow-sm active:scale-95 transition">
        <i class="fas fa-bars text-gray-700"></i>
        <span class="sr-only">Open menu</span>
      </button>
      <a href="/dashboard" class="flex items-center gap-3">
        <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow">
          <img style="width:30px" src="/images/icon.png"></img>
        </div>
        <h1 class="text-lg font-bold text-gray-900">Exam Miner 2.0</h1>
      </a>
      <div class="w-10"></div>
    </div>
  </header>

  <!-- ===== MOBILE DRAWER (shown only on mobile) ===== -->
  <div id="mNav" class="fixed inset-0 z-50 hidden only-mobile">
    <div class="absolute inset-0 bg-black/40" data-close></div>
    <aside id="mPanel"
           class="absolute left-0 top-0 h-full w-72 max-w-[85vw] bg-white shadow-2xl
                  -translate-x-full transition-transform duration-200">
      <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-blue-500 to-blue-600 flex items-center">
        <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center mr-3 shadow">
          <img style="width:30px" src="/images/icon.png"></img>
        </div>
        <h2 class="text-white font-bold text-lg">Exam Miner 2.0</h2>
      </div>

      <nav class="p-4">
        <a href="/dashboard" class="flex items-center px-4 py-3 text-white bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg mb-2">
          <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
        </a>
        <a href="/generate-exam" class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-xl mb-2">
          <i class="fas fa-plus mr-3"></i> Generate Exam
        </a>
        <a href="/my-exams" class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-xl mb-2">
          <i class="fas fa-file-alt mr-3"></i> My Exams
        </a>
      </nav>

      <div class="mt-auto p-4 border-t border-gray-100 bg-gray-50">
        <div class="flex items-center mb-4">
          <div class="w-10 h-10 rounded-full mr-3 shadow border border-gray-300 overflow-hidden">
            <img id="profilePic_m" src="/images/default-avatar.png" class="w-full h-full object-cover" alt="">
          </div>
        </div>
        <div class="flex items-center justify-between mb-4">
          <p id="displayName_m" class="font-bold text-gray-900">User</p>
          <a href="/profile" class="text-sm text-blue-600 hover:text-blue-700">View Profile</a>
        </div>
        <button id="logoutBtn_m"
                class="w-full bg-white text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-100 border border-gray-200 shadow-sm transition">
          Logout
        </button>
      </div>
    </aside>
  </div>

  <div class="flex">
    <!-- ===== DESKTOP SIDEBAR (shown only on desktop) ===== -->
    <aside class="w-64 bg-white shadow-2xl min-h-screen border-r border-gray-200 relative only-desktop">
      <a href="/dashboard" class="flex items-center p-6 border-b border-gray-100 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 transition-all duration-200">
        <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center mr-3 shadow-lg">
          <img style="width:30px" src="/images/icon.png"></img>
        </div>
        <h1 class="text-xl font-bold text-white">Exam Miner 2.0</h1>
      </a>

      <!-- Navigation -->
      <nav class="mt-6 px-4">
        <a href="/dashboard" class="flex items-center px-4 py-3 text-white bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg mb-2 transform hover:scale-105 transition-all duration-200">
          <i class="fas fa-tachometer-alt mr-3"></i>
          Dashboard
        </a>
        <a href="/generate-exam" class="flex items-center px-4 py-3 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition-all duration-200 mb-2 group">
          <i class="fas fa-plus mr-3 group-hover:scale-110 transition-transform duration-200"></i>
          Generate Exam
        </a>
        <a href="/my-exams" class="flex items-center px-4 py-3 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition-all duration-200 mb-2 group">
          <i class="fas fa-file-alt mr-3 group-hover:scale-110 transition-transform duration-200"></i>
          My Exams
        </a>
      </nav>

      <!-- User Profile Section -->
      <div class="absolute bottom-0 w-64 p-6 border-t border-gray-100 bg-gray-50">
        <div class="flex items-center mb-4">
          <div class="w-10 h-10 rounded-full mr-3 shadow-md border border-gray-300 overflow-hidden">
            <img id="profilePic" src="/images/default-avatar.png" alt="Profile Picture" class="w-full h-full object-cover">
          </div>
          <div>
            <p id="displayName" class="font-bold text-gray-900">User</p>
            <a href="/profile" class="text-sm text-blue-600 hover:text-blue-700 transition-colors duration-200">View Profile</a>
          </div>
        </div>
        <button id="logoutBtn" class="w-full bg-white text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-100 border border-gray-200 shadow-sm transition-all duration-200 hover:shadow-md">
          Logout
        </button>
      </div>
    </aside>

    <!-- ===== MAIN CONTENT ===== -->
    <main class="flex-1 p-4 sm:p-6 md:p-8">
      <!-- Welcome Header -->
      <div class="mb-6 md:mb-8">
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-2">
          Welcome, <span id="welcomeName">User</span>!
        </h1>
        <p id="welcomeSubtitle" class="text-gray-600">Loading your stats…</p>
      </div>

      <!-- Summary Cards -->
      <div style="padding: 10px" class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mb-8">
        <!-- Total Exam Created -->
        <div style="padding: 10px" class="bg-white rounded-xl shadow-lg p-5 sm:p-6 border border-gray-100 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
              <i class="fas fa-file-alt text-white text-lg"></i>
            </div>
            <div>
              <p class="text-sm text-gray-600 font-medium">Total Exam Created</p>
              <p id="examCount" class="text-3xl sm:text-4xl font-bold text-gray-900">0</p>
            </div>
          </div>
        </div>

        <!-- User Profile Summary -->
        <div style="padding: 10px" class="bg-white rounded-xl shadow-lg p-5 sm:p-6 border border-gray-100 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
          <div class="flex items-center justify-between gap-4">
            <div class="flex items-center">
              <div class="w-10 h-10 rounded-full mr-4 shadow-md border border-gray-300 overflow-hidden">
                <img id="profilePic2" src="/images/default-avatar.png" alt="Profile Picture" class="w-full h-full object-cover">
              </div>
              <div>
                <p id="profileName" class="font-bold text-gray-900 text-base sm:text-lg">User</p>
                <p id="profileBadge" class="text-sm text-blue-600 font-medium">New Account</p>
              </div>
            </div>
            <a style="padding: 10px" href="/profile" class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-5 py-2 sm:px-6 rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105 inline-block whitespace-nowrap">Edit</a>
          </div>
        </div>
      </div>

      <!-- Recent Exams Section -->
      <section id="recentSection" class="bg-white rounded-xl shadow-lg p-6 sm:p-8 border border-gray-100 hidden">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Recent Exams</h2>
          <a href="/my-exams" class="text-blue-600 hover:text-blue-700 font-medium">View All</a>
        </div>
        <div id="recentList" class="space-y-4"></div>
        <div class="mt-6 text-center">
          <a href="/generate-exam" class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-6 sm:px-8 py-3 rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105 font-medium inline-flex items-center">
            <i class="fas fa-plus mr-2"></i>
            Create Another Exam
          </a>
        </div>
      </section>

      <!-- Empty State -->
      <section id="emptySection" class="bg-white rounded-xl shadow-lg p-6 sm:p-8 border border-gray-100 hidden">
        <div class="text-center">
          <div class="w-20 h-20 sm:w-24 sm:h-24 bg-gradient-to-br from-blue-100 to-blue-200 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-plus text-blue-500 text-2xl sm:text-3xl"></i>
          </div>
          <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4">No Exams Created Yet</h2>
          <p class="text-gray-600 mb-8 max-w-md mx-auto">
            Welcome to Exam Miner 2.0! You haven't created any exams yet.
            Let's get started by creating your first exam.
          </p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/generate-exam" class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-8 py-4 rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105 font-medium text-lg text-center">
              <i class="fas fa-plus inline mr-2"></i>
              Create Your First Exam
            </a>
            <!--button onclick="showTutorial()" class="border-2 border-blue-500 text-blue-500 px-8 py-4 rounded-lg hover:bg-blue-50 transition-all duration-200 font-medium text-lg hover:border-blue-600">
              <i class="fas fa-info-circle inline mr-2"></i>
              Learn How It Works
            </button-->
          </div>
        </div>
      </section>
    </main>
  </div>

  <!-- ===== Tutorial Modal ===== -->
  <div id="tutorialModal" class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
      <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6 rounded-t-2xl">
        <div class="flex items-center justify-between">
          <h2 class="text-2xl font-bold text-white">How Exam Miner 2.0 Works</h2>
          <button onclick="closeTutorial()" class="text-white hover:text-gray-200 transition">
            <i class="fas fa-times text-xl"></i>
            <span class="sr-only">Close</span>
          </button>
        </div>
      </div>
      <div class="p-6 sm:p-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8">
          <div class="flex flex-col items-center text-center">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4"><span class="text-2xl font-bold text-blue-600 leading-none">1</span></div>
            <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-3">Upload Your Material</h3>
            <p class="text-gray-600 max-w-xs">Upload your learning materials (PDF, DOCX, PPT, etc.) that you want to create exams from.</p>
          </div>
          <div class="flex flex-col items-center text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4"><span class="text-2xl font-bold text-green-600 leading-none">2</span></div>
            <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-3">Configure Your Exam</h3>
            <p class="text-gray-600 max-w-xs">Set the exam type, number of questions, and how many sets you want to generate.</p>
          </div>
          <div class="flex flex-col items-center text-center">
            <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mb-4"><span class="text-2xl font-bold text-purple-600 leading-none">3</span></div>
            <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-3">AI Generates Questions</h3>
            <p class="text-gray-600 max-w-xs">Our AI analyzes your content and creates intelligent, contextually relevant questions.</p>
          </div>
          <div class="flex flex-col items-center text-center">
            <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mb-4"><span class="text-2xl font-bold text-orange-600 leading-none">4</span></div>
            <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-3">Download & Use</h3>
            <p class="text-gray-600 max-w-xs">Download your generated exams and use them for assessments, practice, or study.</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ===== Mobile drawer toggle (doesn't touch desktop) ===== -->
  <script>
    (function(){
      const openBtn = document.getElementById('mOpenBtn');
      const mNav    = document.getElementById('mNav');
      const mPanel  = document.getElementById('mPanel');

      function open() {
        mNav.classList.remove('hidden');
        requestAnimationFrame(() => mPanel.classList.remove('-translate-x-full'));
      }
      function close() {
        mPanel.classList.add('-translate-x-full');
        setTimeout(() => mNav.classList.add('hidden'), 180);
      }

      openBtn?.addEventListener('click', open);
      mNav?.addEventListener('click', (e) => { if (e.target.hasAttribute('data-close')) close(); });
      document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
      mPanel?.querySelectorAll('a').forEach(a => a.addEventListener('click', close));
      window.addEventListener('orientationchange', close);
    })();
  </script>

  <!-- ===== Your existing app logic (mirrors desktop data to mobile drawer) ===== -->
  
  <script>
(() => {
  const TOKEN_KEY = 'jwt_token';
  const CACHE_KEY = 'profile_cache';
  const DEFAULT_AVATAR = '/images/default-avatar.png';

  const RAW_JWT = (localStorage.getItem(TOKEN_KEY) || '')
    .replace(/^"|"$/g,'')
    .replace(/^Bearer\s+/i,'');

  if (!RAW_JWT) { location.replace('/login'); return; }

  const API = {
    me: '/api/me.php',
    examsCount: '/api/exams_count.php',
    examsRecent: '/api/exams_recent.php',
  };

  const $ = (id) => document.getElementById(id);

  // helpers (mirror my-exams)
  function approxBase64Bytes(b64='') {
    const s = String(b64).split(',').pop() || '';
    const len = s.length - (s.endsWith('==') ? 2 : s.endsWith('=') ? 1 : 0);
    return Math.floor(len * 3 / 4);
  }
  function resolveAvatar(pic){
    if (!pic) return DEFAULT_AVATAR;
    const p = String(pic).trim();
    if (p.startsWith('data:image/')) {
      if (approxBase64Bytes(p) > 200*1024) return DEFAULT_AVATAR;
      return p;
    }
    if (p.startsWith('http://') || p.startsWith('https://') || p.startsWith('/')) return p;
    return '/images/' + p;
  }
  function readCache(){ try { return JSON.parse(localStorage.getItem(CACHE_KEY)||'{}'); } catch { return {}; } }
  function writeCache(obj){ try { localStorage.setItem(CACHE_KEY, JSON.stringify(obj||{})); } catch {} }
  function parseJwt(token){ try{ const [,p]=token.split('.'); if(!p) return {}; const b=p.replace(/-/g,'+').replace(/_/g,'/'); return JSON.parse(decodeURIComponent(atob(b).split('').map(c=>'%'+('00'+c.charCodeAt(0).toString(16)).slice(-2)).join('')));}catch{return{};} }

  // expiry guard
  const payload = parseJwt(RAW_JWT);
  if (payload.exp && Date.now() >= payload.exp * 1000) {
    localStorage.removeItem(TOKEN_KEY);
    localStorage.removeItem(CACHE_KEY);
    location.replace('/login');
    return;
  }
/*
\
  function bearerHeaders(extra = {}) {
    // IMPORTANT: no Authorization header
    return { 'Accept': 'application/json', ...extra };
  }

  async function getJSONWithRetry(url, {params = {}, retries = 5, backoffMs = 600} = {}) {
    const u = new URL(url, location.origin);
    u.searchParams.set('token', RAW_JWT);
    Object.entries(params).forEach(([k,v]) => u.searchParams.set(k, v));

    for (let attempt = 0; attempt <= retries; attempt++) {
      try {
        const res = await fetch(u.toString(), { headers: bearerHeaders(), cache: 'no-store', credentials: 'omit' });
        
        
        */
        
        function bearerHeaders(extra = {}) {
          return {
            Accept: 'application/json',
            Authorization: 'Bearer ' + RAW_JWT,
            ...extra
          };
        }
        
        async function getJSONWithRetry(url, {params = {}, retries = 5, backoffMs = 600} = {}) {
          const u = new URL(url, location.origin);
          Object.entries(params).forEach(([k, v]) => u.searchParams.set(k, v));
        
          for (let attempt = 0; attempt <= retries; attempt++) {
            try {
              const res = await fetch(u.toString(), {
                headers: bearerHeaders(),
                cache: 'no-store',
                credentials: 'omit'
              });
              // ... (rest unchanged)
        
        
        if ([429,502,503,504].includes(res.status)) {
          if (attempt === retries) throw new Error('Server temporarily unavailable.');
          const wait = backoffMs * Math.pow(2, attempt) + Math.floor(Math.random()*250);
          showBusyBanner(`Server busy (HTTP ${res.status}). Retrying… ${attempt+1}/${retries}`);
          await new Promise(r => setTimeout(r, wait));
          continue;
        }
        const text = await res.text();
        if (/^\s*</.test(text) && !text.trim().startsWith('{')) throw new Error('Unexpected HTML from server');
        return text ? JSON.parse(text) : {};
      } catch (e) {
        if (attempt === retries) { clearBusyBanner(); throw e; }
        const wait = backoffMs * Math.pow(2, attempt) + Math.floor(Math.random()*250);
        showBusyBanner(`Network error. Retrying… ${attempt+1}/${retries}`);
        await new Promise(r => setTimeout(r, wait));
      }
    }
  }

  function showBusyBanner(msg){
    let b = document.getElementById('busyBanner');
    if (!b) {
      b = document.createElement('div');
      b.id = 'busyBanner';
      b.className = 'mb-4 p-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-800';
      const main = document.querySelector('main');
      if (main) main.prepend(b);
    }
    b.textContent = msg;
    b.classList.remove('hidden');
  }
  function clearBusyBanner(){
    const b = document.getElementById('busyBanner');
    if (b) b.classList.add('hidden');
  }

  // UI fillers
  function setNames({name, username, email}) {
    const display = name || username || email || 'User';
    const d1 = $('displayName');      if (d1) d1.textContent = display;
    const wn = $('welcomeName');      if (wn) wn.textContent = (display.split(' ')[0] || display);
    const pn = $('profileName');      if (pn) pn.textContent = display;
    const pb = $('profileBadge');     if (pb) pb.textContent = username;
    const ws = $('welcomeSubtitle');  if (ws) ws.textContent = "Let's get started with creating your first exam.";
    const dm = $('displayName_m');    if (dm) dm.textContent = display;
  }
  function setProfilePic(pic) {
    const url = resolveAvatar(pic);
    const a = $('profilePic');   if (a) { a.src = url; a.onerror = () => (a.src = DEFAULT_AVATAR); }
    const b = $('profilePic2');  if (b) { b.src = url; b.onerror = () => (b.src = DEFAULT_AVATAR); }
    const c = $('profilePic_m'); if (c) { c.src = url; c.onerror = () => (c.src = DEFAULT_AVATAR); }
  }
  function setCount(n) {
    const c = $('examCount'); if (c) c.textContent = n;
    if (n > 0) {
      $('recentSection')?.classList.remove('hidden');
      $('emptySection')?.classList.add('hidden');
      const ws = $('welcomeSubtitle');
      if (ws) ws.textContent = `You've created ${n} ${n === 1 ? 'exam' : 'exams'}. Keep up the great work!`;
    } else {
      $('recentSection')?.classList.add('hidden');
      $('emptySection')?.classList.remove('hidden');
    }
  }
  function renderRecent(exams = []) {
    const box = $('recentList'); if (!box) return;
    box.innerHTML = '';
    exams.slice(0, 3).forEach(exam => {
      const created = exam.created_at ? new Date(String(exam.created_at).replace(' ','T')).toLocaleDateString() : '';
      const status  = (exam.status || 'new');
      const row = document.createElement('div');
      row.className = 'flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors';
      row.innerHTML = `
        <div class="flex items-center">
          <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center mr-4">
            <i class="fas fa-file-alt text-white text-sm"></i>
          </div>
          <div>
            <h3 class="font-semibold text-gray-900">${exam.title || 'Untitled Exam'}</h3>
            <p class="text-sm text-gray-600">${exam.exam_type || '—'} • ${exam.number_of_questions || 0} questions • ${created}</p>
          </div>
        </div>
        <div class="flex items-center space-x-2">
          <span class="bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded-full">${status.charAt(0).toUpperCase() + status.slice(1)}</span>
          <a href="/my-exams" class="text-blue-600 hover:text-blue-700 text-sm font-medium">View</a>
        </div>`;
      box.appendChild(row);
    });
  }

  async function refreshHeaderProfile(){
    try {
      const me = await getJSONWithRetry(API.me);
      if (me?.status === 'success' && me.user) {
        const name = me.user.name || me.user.username || me.user.email || 'User';
        const pic  = (me.user.profile_picture || '').trim();
        setNames({name, username: me.user.username, email: me.user.email});
        setProfilePic(pic);
        writeCache({
          name,
          username: me.user.username || '',
          email: me.user.email || '',
          profile_picture: pic,
          updated_at: me.user.updated_at || new Date().toISOString()
        });
      }
    } catch(e) {} // keep cache UI
  }

  // Bootstrap
  (async function init(){
    // paint from cache
    const cache = readCache();
    if (cache && (cache.name || cache.email || cache.username)) {
      setNames(cache);
      setProfilePic(cache.profile_picture);
    }

    // fresh me + count + recent
    await refreshHeaderProfile();

    try {
      const c = await getJSONWithRetry(API.examsCount);
      setCount((c && c.status === 'success') ? (+c.count || 0) : 0);
    } catch { setCount(0); }

    try {
      const r = await getJSONWithRetry(API.examsRecent);
      if (r?.status === 'success' && Array.isArray(r.exams) && r.exams.length > 0) {
        $('recentSection')?.classList.remove('hidden');
        $('emptySection')?.classList.add('hidden');
        renderRecent(r.exams);
      }
    } catch {}
  })();

  // Logout (desktop + mobile)
  function doLogout(){
    localStorage.removeItem('jwt_token');
    localStorage.removeItem('profile_cache');
    location.replace('/login');
  }
  document.getElementById('logoutBtn')?.addEventListener('click', doLogout);
  document.getElementById('logoutBtn_m')?.addEventListener('click', doLogout);
})();
</script>

  
  <!--script>
(() => {
  // ---------- Config ----------
  const API = {
    me: '/api/me.php',
    examsCount: '/api/exams_count.php',
    examsRecent: '/api/exams_recent.php',
  };

  // ---------- Token / cache ----------
  const TOKEN_KEY = 'jwt_token';
  const CACHE_KEY = 'profile_cache';

  const RAW_JWT = (localStorage.getItem(TOKEN_KEY) || '')
    .replace(/^"|"$/g, '')
    .replace(/^Bearer\s+/i, '');

  if (!RAW_JWT) { location.replace('/login'); return; }

  // ---------- Helpers ----------
  const $ = (id) => document.getElementById(id);
  const DEFAULT_AVATAR = '/images/default-avatar.png';

  function approxBase64Bytes(b64='') {
    const s = String(b64).split(',').pop() || '';
    const len = s.length - (s.endsWith('==') ? 2 : s.endsWith('=') ? 1 : 0);
    return Math.floor(len * 3 / 4);
  }

  function resolveAvatar(pic){
    if (!pic) return DEFAULT_AVATAR;
    const p = String(pic).trim();
    if (p.startsWith('data:image/')) {
      const bytes = approxBase64Bytes(p);
      if (bytes > 200 * 1024) return DEFAULT_AVATAR;
      return p;
    }
    if (p.startsWith('http://') || p.startsWith('https://') || p.startsWith('/')) return p;
    return '/images/' + p;
  }

  function readCache(){ try { return JSON.parse(localStorage.getItem(CACHE_KEY) || '{}'); } catch { return {}; } }
  function writeCache(obj){ try { localStorage.setItem(CACHE_KEY, JSON.stringify(obj || {})); } catch {} }

  function parseJwt(token){
    try{
      const [, p] = token.split('.');
      if (!p) return {};
      const b64 = p.replace(/-/g,'+').replace(/_/g,'/');
      const json = decodeURIComponent(atob(b64).split('').map(c => '%' + ('00'+c.charCodeAt(0).toString(16)).slice(-2)).join(''));
      return JSON.parse(json);
    }catch{ return {}; }
  }

  function tokenLooksApi(payload) {
    return !!(payload && (payload.sub || payload.scope === 'api'));
  }

  function showBusyBanner(msg){
    let b = document.getElementById('busyBanner');
    if (!b) {
      b = document.createElement('div');
      b.id = 'busyBanner';
      b.className = 'mb-4 p-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-800';
      const main = document.querySelector('main');
      if (main) main.prepend(b);
    }
    b.textContent = msg;
    b.classList.remove('hidden');
  }
  function clearBusyBanner(){
    const b = document.getElementById('busyBanner');
    if (b) b.classList.add('hidden');
  }

  function forceReauth(msg = 'Session updated. Please sign in again.') {
    showBusyBanner(msg);
    setTimeout(() => {
      localStorage.removeItem(TOKEN_KEY);
      localStorage.removeItem(CACHE_KEY);
      location.replace('/login');
    }, 900);
  }

  // Expiry / shape guard ASAP
  (function(){
    const payload = parseJwt(RAW_JWT);
    if (payload && payload.exp && Date.now() >= payload.exp * 1000) {
      forceReauth('Session expired. Please sign in again.');
      return;
    }
    if (!tokenLooksApi(payload) && (payload.user_id || payload.name || payload.profile_picture)) {
      forceReauth('Profile updated. Please sign in again.');
      return;
    }
  })();

  function bearerHeaders(extra = {}) {
    return { 'Authorization': 'Bearer ' + RAW_JWT, 'Accept': 'application/json', ...extra };
  }

  async function getJSONWithRetry(url, {params = {}, retries = 5, backoffMs = 600} = {}) {
    const u = new URL(url, location.origin);
    u.searchParams.set('token', RAW_JWT);
    Object.entries(params).forEach(([k,v]) => u.searchParams.set(k, v));

    for (let attempt = 0; attempt <= retries; attempt++) {
      try {
        const res = await fetch(u.toString(), { headers: bearerHeaders(), cache: 'no-store', credentials: 'omit' });

        if (res.status === 401 || res.status === 403) {
          forceReauth('Your session changed. Please sign in again.');
          return;
        }

        if ([429,502,503,504].includes(res.status)) {
          if (attempt === retries) throw new Error('Server temporarily unavailable.');
          const wait = backoffMs * Math.pow(2, attempt) + Math.floor(Math.random()*250);
          showBusyBanner(`Server busy (HTTP ${res.status}). Retrying… ${attempt+1}/${retries}`);
          await new Promise(r => setTimeout(r, wait));
          continue;
        }

        const text = await res.text();
        clearBusyBanner();
        if (/^\s*</.test(text) && !text.trim().startsWith('{')) throw new Error('Unexpected HTML from server');

        const json = text ? JSON.parse(text) : {};
        if (json && json.status === 'error' && /token|unauthor|expired|scope/i.test(json.message || '')) {
          forceReauth('Session issue detected. Please sign in again.');
          return;
        }
        return json;
      } catch (e) {
        if (attempt === retries) { clearBusyBanner(); throw e; }
        const wait = backoffMs * Math.pow(2, attempt) + Math.floor(Math.random()*250);
        showBusyBanner(`Network error. Retrying… ${attempt+1}/${retries}`);
        await new Promise(r => setTimeout(r, wait));
      }
    }
  }

  // ---------- UI Fillers ----------
  function setNames({name, username, email}) {
    const display = name || username || email || 'User';
    const d1 = $('displayName');      if (d1) d1.textContent = display;
    const wn = $('welcomeName');      if (wn) wn.textContent = (display.split(' ')[0] || display);
    const pn = $('profileName');      if (pn) pn.textContent = display;
    const ws = $('welcomeSubtitle');  if (ws) ws.textContent = "Let's get started with creating your first exam.";
    const dm = $('displayName_m');    if (dm) dm.textContent = display;
  }

  function setProfilePic(pic) {
    const url = resolveAvatar(pic);
    const a = $('profilePic');   if (a) { a.src = url; a.onerror = () => (a.src = DEFAULT_AVATAR); }
    const b = $('profilePic2');  if (b) { b.src = url; b.onerror = () => (b.src = DEFAULT_AVATAR); }
    const c = $('profilePic_m'); if (c) { c.src = url; c.onerror = () => (c.src = DEFAULT_AVATAR); }
  }

  function setCount(n) {
    const c = $('examCount'); if (c) c.textContent = n;
    if (n > 0) {
      $('recentSection')?.classList.remove('hidden');
      $('emptySection')?.classList.add('hidden');
      const ws = $('welcomeSubtitle');
      if (ws) ws.textContent = `You've created ${n} ${n === 1 ? 'exam' : 'exams'}. Keep up the great work!`;
    } else {
      $('recentSection')?.classList.add('hidden');
      $('emptySection')?.classList.remove('hidden');
    }
  }

  function renderRecent(exams = []) {
    const box = $('recentList'); if (!box) return;
    box.innerHTML = '';
    exams.slice(0, 3).forEach(exam => {
      const created = exam.created_at ? new Date(String(exam.created_at).replace(' ','T')).toLocaleDateString() : '';
      const status  = (exam.status || 'new');
      const row = document.createElement('div');
      row.className = 'flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors';
      row.innerHTML = `
        <div class="flex items-center">
          <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center mr-4">
            <i class="fas fa-file-alt text-white text-sm"></i>
          </div>
          <div>
            <h3 class="font-semibold text-gray-900">${exam.title || 'Untitled Exam'}</h3>
            <p class="text-sm text-gray-600">${exam.exam_type || '—'} • ${exam.number_of_questions || 0} questions • ${created}</p>
          </div>
        </div>
        <div class="flex items-center space-x-2">
          <span class="bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded-full">${status.charAt(0).toUpperCase() + status.slice(1)}</span>
          <a href="/my-exams" class="text-blue-600 hover:text-blue-700 text-sm font-medium">View</a>
        </div>`;
      box.appendChild(row);
    });
  }

  // ---------- Bootstrap ----------
  (async function init(){
    // 1) Fast paint from cache/JWT
    const cache = readCache();
    if (cache && (cache.name || cache.email || cache.username)) {
      setNames(cache);
      setProfilePic(cache.profile_picture);
    } else {
      const payload = parseJwt(RAW_JWT) || {};
      setNames({ name: payload.name, username: payload.username, email: payload.email });
    }

    // 2) Fresh profile from server
    try {
      const me = await getJSONWithRetry(API.me);
      if (me?.status === 'success' && me.user) {
        const old = readCache() || {};
        const serverPic = (me.user.profile_picture || '').trim();
        setNames(me.user);
        setProfilePic(serverPic || old.profile_picture || '');
        writeCache({
          name:  me.user.name  ?? old.name  ?? '',
          username: me.user.username ?? old.username ?? '',
          email: me.user.email ?? old.email ?? '',
          profile_picture: serverPic || old.profile_picture || '',
          updated_at: me.user.updated_at || old.updated_at || new Date().toISOString()
        });
      }
    } catch (e) {
      console.warn('me() failed:', e.message);
    }

    // 3) Count
    try {
      const c = await getJSONWithRetry(API.examsCount);
      setCount((c && c.status === 'success') ? (+c.count || 0) : 0);
    } catch (e) {
      console.warn('count() failed:', e.message);
      setCount(0);
    }

    // 4) Recent
    try {
      const r = await getJSONWithRetry(API.examsRecent);
      if (r?.status === 'success' && Array.isArray(r.exams) && r.exams.length > 0) {
        $('recentSection')?.classList.remove('hidden');
        $('emptySection')?.classList.add('hidden');
        renderRecent(r.exams);
      }
    } catch (e) {
      console.warn('recent() failed:', e.message);
    }
  })();

  // Tutorial modal (if you re-enable the button)
  function showTutorial(){ document.getElementById('tutorialModal')?.classList.remove('hidden'); }
  function closeTutorial(){ document.getElementById('tutorialModal')?.classList.add('hidden'); }
  document.getElementById('tutorialModal')?.addEventListener('click', e => { if (e.target === e.currentTarget) closeTutorial(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeTutorial(); });

  // Logout (desktop + mobile)
  function doLogout(){
    localStorage.removeItem('jwt_token');
    localStorage.removeItem('profile_cache');
    location.replace('/login');
  }
  document.getElementById('logoutBtn')?.addEventListener('click', doLogout);
  document.getElementById('logoutBtn_m')?.addEventListener('click', doLogout);
})();
</script-->

  
  <!--script>
(() => {
  // ---------- Config ----------
  const API = {
    me: '/api/me.php',
    examsCount: '/api/exams_count.php',
    examsRecent: '/api/exams_recent.php',
  };

  // ---------- Token / cache ----------
  const TOKEN_KEY = 'jwt_token';
  const CACHE_KEY = 'profile_cache';

  const RAW_JWT = (localStorage.getItem(TOKEN_KEY) || '')
    .replace(/^"|"$/g, '')
    .replace(/^Bearer\s+/i, '');

  if (!RAW_JWT) { location.replace('/login'); return; }

  // ---------- Helpers ----------
  const $ = (id) => document.getElementById(id);
  const DEFAULT_AVATAR = '/images/default-avatar.png';

  function approxBase64Bytes(b64='') {
    const s = String(b64).split(',').pop() || '';
       const len = s.length - (s.endsWith('==') ? 2 : s.endsWith('=') ? 1 : 0);
    return Math.floor(len * 3 / 4);
  }

  function resolveAvatar(pic){
    if (!pic) return DEFAULT_AVATAR;
    const p = String(pic).trim();
    if (p.startsWith('data:image/')) {
      const bytes = approxBase64Bytes(p);
      if (bytes > 200 * 1024) return DEFAULT_AVATAR;
      return p;
    }
    if (p.startsWith('http://') || p.startsWith('https://') || p.startsWith('/')) return p;
    return '/images/' + p;
  }

  function readCache(){ try { return JSON.parse(localStorage.getItem(CACHE_KEY) || '{}'); } catch { return {}; } }
  function writeCache(obj){ try { localStorage.setItem(CACHE_KEY, JSON.stringify(obj || {})); } catch {} }

  function parseJwt(token){
    try{
      const [, p] = token.split('.');
      if (!p) return {};
      const b64 = p.replace(/-/g,'+').replace(/_/g,'/');
      const json = decodeURIComponent(atob(b64).split('').map(c => '%' + ('00'+c.charCodeAt(0).toString(16)).slice(-2)).join(''));
      return JSON.parse(json);
    }catch{ return {}; }
  }

  // Expiry guard
  (function(){
    const payload = parseJwt(RAW_JWT);
    if (payload && payload.exp && Date.now() >= payload.exp * 1000) {
      localStorage.removeItem(TOKEN_KEY);
      localStorage.removeItem(CACHE_KEY);
      location.replace('/login');
    }
  })();

  function bearerHeaders(extra = {}) {
    return { 'Authorization': 'Bearer ' + RAW_JWT, 'Accept': 'application/json', ...extra };
  }

  async function getJSONWithRetry(url, {params = {}, retries = 5, backoffMs = 600} = {}) {
    const u = new URL(url, location.origin);
    u.searchParams.set('token', RAW_JWT);
    Object.entries(params).forEach(([k,v]) => u.searchParams.set(k, v));

    for (let attempt = 0; attempt <= retries; attempt++) {
      try {
        const res = await fetch(u.toString(), { headers: bearerHeaders(), cache: 'no-store', credentials: 'omit' });
        if (res.status === 401) {
          localStorage.removeItem(TOKEN_KEY);
          localStorage.removeItem(CACHE_KEY);
          location.replace('/login');
          return;
        }
        if ([429,502,503,504].includes(res.status)) {
          if (attempt === retries) throw new Error('Server temporarily unavailable.');
          const wait = backoffMs * Math.pow(2, attempt) + Math.floor(Math.random()*250);
          showBusyBanner(`Server busy (HTTP ${res.status}). Retrying… ${attempt+1}/${retries}`);
          await new Promise(r => setTimeout(r, wait));
          continue;
        }
        const text = await res.text();
        clearBusyBanner();
        if (/^\s*</.test(text) && !text.trim().startsWith('{')) throw new Error('Unexpected HTML from server');
        return text ? JSON.parse(text) : {};
      } catch (e) {
        if (attempt === retries) { clearBusyBanner(); throw e; }
        const wait = backoffMs * Math.pow(2, attempt) + Math.floor(Math.random()*250);
        showBusyBanner(`Network error. Retrying… ${attempt+1}/${retries}`);
        await new Promise(r => setTimeout(r, wait));
      }
    }
  }

  // Busy banner
  function showBusyBanner(msg){
    let b = document.getElementById('busyBanner');
    if (!b) {
      b = document.createElement('div');
      b.id = 'busyBanner';
      b.className = 'mb-4 p-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-800';
      const main = document.querySelector('main');
      if (main) main.prepend(b);
    }
    b.textContent = msg;
    b.classList.remove('hidden');
  }
  function clearBusyBanner(){
    const b = document.getElementById('busyBanner');
    if (b) b.classList.add('hidden');
  }

  // ---------- UI Fillers ----------
  function setNames({name, username, email}) {
    const display = name || username || email || 'User';
    const d1 = $('displayName');      if (d1) d1.textContent = display;
    const wn = $('welcomeName');      if (wn) wn.textContent = (display.split(' ')[0] || display);
    const pn = $('profileName');      if (pn) pn.textContent = display;
    const ws = $('welcomeSubtitle');  if (ws) ws.textContent = "Let's get started with creating your first exam.";
    const dm = $('displayName_m');    if (dm) dm.textContent = display; // mirror to mobile drawer
  }

  function setProfilePic(pic) {
    const url = resolveAvatar(pic);
    const a = $('profilePic');  if (a) { a.src = url; a.onerror = () => (a.src = DEFAULT_AVATAR); }
    const b = $('profilePic2'); if (b) { b.src = url; b.onerror = () => (b.src = DEFAULT_AVATAR); }
    const c = $('profilePic_m'); if (c) { c.src = url; c.onerror = () => (c.src = DEFAULT_AVATAR); }
  }

  function setCount(n) {
    const c = $('examCount'); if (c) c.textContent = n;
    if (n > 0) {
      $('recentSection')?.classList.remove('hidden');
      $('emptySection')?.classList.add('hidden');
      const ws = $('welcomeSubtitle');
      if (ws) ws.textContent = `You've created ${n} ${n === 1 ? 'exam' : 'exams'}. Keep up the great work!`;
    } else {
      $('recentSection')?.classList.add('hidden');
      $('emptySection')?.classList.remove('hidden');
    }
  }

  function renderRecent(exams = []) {
    const box = $('recentList'); if (!box) return;
    box.innerHTML = '';
    exams.slice(0, 3).forEach(exam => {
      const created = exam.created_at ? new Date(String(exam.created_at).replace(' ','T')).toLocaleDateString() : '';
      const status  = (exam.status || 'new');
      const row = document.createElement('div');
      row.className = 'flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors';
      row.innerHTML = `
        <div class="flex items-center">
          <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center mr-4">
            <i class="fas fa-file-alt text-white text-sm"></i>
          </div>
          <div>
            <h3 class="font-semibold text-gray-900">${exam.title || 'Untitled Exam'}</h3>
            <p class="text-sm text-gray-600">${exam.exam_type || '—'} • ${exam.number_of_questions || 0} questions • ${created}</p>
          </div>
        </div>
        <div class="flex items-center space-x-2">
          <span class="bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded-full">${status.charAt(0).toUpperCase() + status.slice(1)}</span>
          <a href="/my-exams" class="text-blue-600 hover:text-blue-700 text-sm font-medium">View</a>
        </div>`;
      box.appendChild(row);
    });
  }

  // ---------- Bootstrap ----------
  (async function init(){
    // 1) Fast paint from cache/JWT
    const cache = readCache();
    if (cache && (cache.name || cache.email || cache.username)) {
      setNames(cache);
    } else {
      const payload = parseJwt(RAW_JWT) || {};
      setNames({ name: payload.name, username: payload.username, email: payload.email });
    }
    setProfilePic(cache.profile_picture);

    // 2) Fresh profile from server
    try {
      const me = await getJSONWithRetry(API.me);
      if (me?.status === 'success' && me.user) {
        const old = readCache() || {};
        const serverPic = (me.user.profile_picture || '').trim();
        setNames(me.user);
        setProfilePic(serverPic || old.profile_picture || '');
        writeCache({
          name:  me.user.name  ?? old.name  ?? '',
          username: me.user.username ?? old.username ?? '',
          email: me.user.email ?? old.email ?? '',
          profile_picture: serverPic || old.profile_picture || '',
          updated_at: me.user.updated_at || old.updated_at || new Date().toISOString()
        });
      }
    } catch (e) {
      console.warn('me() failed:', e.message);
    }

    // 3) Count
    try {
      const c = await getJSONWithRetry(API.examsCount);
      setCount((c && c.status === 'success') ? (+c.count || 0) : 0);
    } catch (e) {
      console.warn('count() failed:', e.message);
      setCount(0);
    }

    // 4) Recent
    try {
      const r = await getJSONWithRetry(API.examsRecent);
      if (r?.status === 'success' && Array.isArray(r.exams) && r.exams.length > 0) {
        $('recentSection')?.classList.remove('hidden');
        $('emptySection')?.classList.add('hidden');
        renderRecent(r.exams);
      }
    } catch (e) {
      console.warn('recent() failed:', e.message);
    }
  })();

  // Tutorial modal
  function showTutorial(){ document.getElementById('tutorialModal')?.classList.remove('hidden'); }
  function closeTutorial(){ document.getElementById('tutorialModal')?.classList.add('hidden'); }
  document.getElementById('tutorialModal')?.addEventListener('click', e => { if (e.target === e.currentTarget) closeTutorial(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeTutorial(); });

  // Logout (desktop + mobile)
  function doLogout(){
    localStorage.removeItem('jwt_token');
    localStorage.removeItem('profile_cache');
    location.replace('/login');
  }
  document.getElementById('logoutBtn')?.addEventListener('click', doLogout);
  document.getElementById('logoutBtn_m')?.addEventListener('click', doLogout);
})();
  </script-->
</body>
</html>
