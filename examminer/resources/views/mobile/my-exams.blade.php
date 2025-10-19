<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Exam Miner 2.0 — My Exams (Mobile)</title>
  @vite('resources/css/app.css')
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>

  <style>
    :root {
      /* iOS safe areas */
      --safe-bottom: env(safe-area-inset-bottom, 0px);
      --safe-top:    env(safe-area-inset-top, 0px);
    }
  </style>
</head>

<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
  <script>
    // Require JWT from your API-based login
    const token = localStorage.getItem('jwt_token');
    if (!token) location.replace('/login');
  </script>

  <!-- ========== MOBILE TOP BAR ========== -->
  <header class="sticky top-0 z-50 bg-white/80 backdrop-blur border-b border-gray-200"
          style="padding-top: var(--safe-top)">
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
        <h1 class="font-bold text-gray-900 leading-tight whitespace-nowrap
                   text-[clamp(1.1rem,4.6vw,1.35rem)]">Exam Miner 2.0</h1>
      </a>

      <a href="/profile" class="w-10 h-10 rounded-full overflow-hidden border border-gray-200 shadow">
        <img id="headerAvatar" src="/images/default-avatar.png" alt="Profile" class="w-full h-full object-cover"/>
      </a>
    </div>
  </header>

  <!-- ========== MOBILE NAV DRAWER ========== -->
  <div id="mNav" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40" data-close></div>

    <aside id="mPanel"
           class="absolute left-0 top-0 h-full w-72 max-w-[85vw] bg-white shadow-2xl
                  -translate-x-full transition-transform duration-200">

      <!-- Drawer header -->
      <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-blue-500 to-blue-600">
        <div class="flex items-center">
          <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center mr-3 shadow">
            <img style="width:30px" src="/images/icon.png"></img>
          </div>
          <h2 class="text-white font-bold text-lg">Exam Miner 2.0</h2>
        </div>
      </div>

      <!-- Drawer nav -->
      <nav class="p-4">
        <a href="/dashboard"
           class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-xl mb-2">
          <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
        </a>
        <a href="/generate-exam"
           class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-xl mb-2">
          <i class="fas fa-plus mr-3"></i> Generate Exam
        </a>
        <a href="/my-exams"
           class="flex items-center px-4 py-3 text-white bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg mb-2">
          <i class="fas fa-file-alt mr-3"></i> My Exams
        </a>
      </nav>

      <!-- Drawer user -->
      <div class="mt-auto p-4 border-t border-gray-100 bg-gray-50">
        <div class="flex items-center mb-4">
          <div class="w-10 h-10 rounded-full mr-3 shadow border border-gray-300 overflow-hidden">
            <img id="drawerAvatar" src="/images/default-avatar.png" alt="" class="w-full h-full object-cover">
          </div>
          <div class="min-w-0">
            <p id="drawerName" class="font-bold text-gray-900 truncate">User</p>
            <a href="/profile" class="text-sm text-blue-600 hover:text-blue-700">View Profile</a>
          </div>
        </div>
        <button id="logoutBtn"
                class="w-full bg-white text-gray-700 py-2.5 rounded-lg hover:bg-gray-100 border border-gray-200 shadow-sm transition">
          Logout
        </button>
      </div>
    </aside>
  </div>

  <!-- ========== MAIN CONTENT ========== -->
  <main class="px-4 py-5">
    <!-- Busy/alert banner -->
    <div id="alertBox" class="hidden mb-4 p-3 rounded-lg"></div>

    <!-- Page header -->
    <section class="mb-6">
      <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">My Exams</h1>
        <a style="padding: 5px" href="/generate-exam"
           class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-5 py-2 rounded-lg hover:from-blue-600 hover:to-blue-700 transition shadow-lg hover:shadow-xl transform active:scale-95 inline-flex items-center">
          <i class="fas fa-plus mr-2"></i>
          New
        </a>
      </div>
      <p class="text-gray-600 mt-1">Manage and export your created exams.</p>
    </section>

    <!-- List container -->
    <div id="listWrap"></div>

    <!-- Empty state (hidden until needed) -->
    <section id="emptyState" class="hidden bg-white rounded-xl shadow-lg p-6 border border-gray-100">
      <div class="text-center">
        <div class="w-24 h-24 bg-gradient-to-br from-blue-100 to-blue-200 rounded-full flex items-center justify-center mx-auto mb-6">
          <i class="fas fa-file-alt text-blue-500 text-3xl"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 mb-3">No Exams Generated Yet</h2>
        <p class="text-gray-600 mb-8 max-w-md mx-auto">
          You haven't generated any exams yet. Create your first exam by uploading learning materials.
        </p>
        <div class="flex flex-col gap-4 justify-center">
          <a href="/generate-exam"
             class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-8 py-4 rounded-lg hover:from-blue-600 hover:to-blue-700 transition shadow-lg hover:shadow-xl transform active:scale-95 font-medium text-lg inline-flex items-center justify-center">
            <i class="fas fa-plus mr-2"></i>
            Generate Your First Exam
          </a>
          <!--button onclick="showTutorial()" class="bg-white text-blue-600 px-8 py-4 rounded-lg hover:bg-gray-50 transition shadow-lg hover:shadow-xl transform active:scale-95 font-medium text-lg inline-flex items-center justify-center border-2 border-blue-600">
            <i class="fas fa-info-circle mr-2"></i>Learn How It Works</button-->
      </div>
      </div>
    </section>
  </main>

  <!-- ========== Tutorial Modal ========== -->
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
      <div class="p-6">
        <div class="grid grid-cols-1 gap-6">
          <div class="flex flex-col items-center text-center">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
              <span class="text-2xl font-bold text-blue-600 leading-none">1</span>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Upload Your Material</h3>
            <p class="text-gray-600 max-w-xs">Upload your learning materials (PDF, DOCX, PPT, etc.) that you want to create exams from.</p>
          </div>
          <div class="flex flex-col items-center text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
              <span class="text-2xl font-bold text-green-600 leading-none">2</span>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Configure Your Exam</h3>
            <p class="text-gray-600 max-w-xs">Set the exam type, number of questions, and how many sets you want to generate.</p>
          </div>
          <div class="flex flex-col items-center text-center">
            <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mb-4">
              <span class="text-2xl font-bold text-purple-600 leading-none">3</span>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">AI Generates Questions</h3>
            <p class="text-gray-600 max-w-xs">Our AI analyzes your content and creates intelligent, contextually relevant questions.</p>
          </div>
          <div class="flex flex-col items-center text-center">
            <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mb-4">
              <span class="text-2xl font-bold text-orange-600 leading-none">4</span>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Download & Use</h3>
            <p class="text-gray-600 max-w-xs">Download your generated exams and use them for assessments, practice, or study.</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ========== Drawer Toggle (mobile only) ========== -->
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

  <!-- ========== App Logic (mobile IDs only) ========== -->
  <script>
(() => {
  const TOKEN_KEY = 'jwt_token';
  const CACHE_KEY = 'profile_cache';
  const DEFAULT_AVATAR = '/images/default-avatar.png';

  const RAW_JWT = (localStorage.getItem(TOKEN_KEY) || '')
    .replace(/^"|"$/g,'')
    .replace(/^Bearer\s+/i,'');

  if (!RAW_JWT) { location.replace('/login'); return; }

  const $ = (id) => document.getElementById(id);

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

  // Expiry guard
  const payload = parseJwt(RAW_JWT);
  if (payload.exp && Date.now() >= payload.exp * 1000) {
    localStorage.removeItem(TOKEN_KEY);
    localStorage.removeItem(CACHE_KEY);
    location.replace('/login');
    return;
  }

  const api = {
    me: '/api/me.php',
    listExams: '/api/my_exams.php',
    deleteExam: '/api/exam_delete.php'
  };

  function showAlert(type, text){
    const box = $('alertBox'); if (!box) return;
    box.className = 'mb-4 p-3 rounded-lg ' + (type === 'ok'
      ? 'bg-green-50 border border-green-200 text-green-700'
      : 'bg-amber-50 border border-amber-200 text-amber-800');
    box.textContent = text; box.classList.remove('hidden');
  }

  function bearerHeaders(extra = {}) {
  return {
    Accept: 'application/json',
    Authorization: 'Bearer ' + RAW_JWT,
    ...extra,
  };
}

  async function getJSONWithRetry(url, {params = {}, retries = 5, backoffMs = 600} = {}) {
    const u = new URL(url, location.origin);
    Object.entries(params).forEach(([k,v]) => u.searchParams.set(k, v));

    for (let attempt = 0; attempt <= retries; attempt++) {
      try {
        const res = await fetch(u.toString(), { headers: bearerHeaders(), cache: 'no-store', credentials: 'omit' });
        if ([429,502,503,504].includes(res.status)) {
          if (attempt === retries) throw new Error('Server temporarily unavailable.');
          const wait = backoffMs * Math.pow(2, attempt) + Math.floor(Math.random()*250);
          showAlert('err', `Server busy (HTTP ${res.status}). Retrying… ${attempt+1}/${retries}`);
          await new Promise(r => setTimeout(r, wait));
          continue;
        }
        const text = await res.text();
        if (/^\s*</.test(text) && !text.trim().startsWith('{')) throw new Error('Unexpected HTML from server');
        return JSON.parse(text);
      } catch (e) {
        if (attempt === retries) throw e;
        const wait = backoffMs * Math.pow(2, attempt) + Math.floor(Math.random()*250);
        showAlert('err', `Network error. Retrying… ${attempt+1}/${retries}`);
        await new Promise(r => setTimeout(r, wait));
      }
    }
  }

  function fmtDate(s){ return s ? new Date((s+'').replace(' ', 'T')).toLocaleDateString() : '—'; }

  async function refreshHeaderProfile(){
    try {
      const me = await getJSONWithRetry(api.me);
      if (me?.status === 'success' && me.user) {
        const name = me.user.name || me.user.username || me.user.email || 'User';
        const img = $('headerAvatar'); if (img) { img.src = resolveAvatar(me.user.profile_picture); img.onerror = () => { img.src = DEFAULT_AVATAR; }; }
        const drawerImg = $('drawerAvatar'); if (drawerImg) { drawerImg.src = resolveAvatar(me.user.profile_picture); drawerImg.onerror = () => { drawerImg.src = DEFAULT_AVATAR; }; }
        const dn = $('drawerName'); if (dn) dn.textContent = name;

        writeCache({
          name,
          username: me.user.username || '',
          email: me.user.email || '',
          profile_picture: (me.user.profile_picture || '').trim(),
          updated_at: me.user.updated_at || new Date().toISOString()
        });
      }
    } catch {}
  }

  async function loadExams(){
    const wrap = $('listWrap');
    const empty = $('emptyState');
    if (!wrap) return;

    wrap.innerHTML = '';
    empty?.classList.add('hidden');

    try {
      const data = await getJSONWithRetry(api.listExams);
      if (data.status !== 'success') { showAlert('err', data.message || 'Failed to load exams.'); return; }

      const exams = Array.isArray(data.exams) ? data.exams : [];
      if (exams.length === 0) { empty?.classList.remove('hidden'); return; }

      // Container card
      const card = document.createElement('div');
      card.className = 'bg-white rounded-xl shadow-lg border border-gray-100';
      card.innerHTML = `
        <div class="p-6 border-b border-gray-100">
          <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">Your Generated Exams</h2>
            <span style="padding: 8px" class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full">
              ${exams.length} ${exams.length===1?'Exam':'Exams'}
            </span>
          </div>
        </div>`;
      const list = document.createElement('div');
      list.className = 'divide-y divide-gray-100';
      card.appendChild(list);
      wrap.appendChild(card);

      exams.forEach(e => {
        const status = (e.status||'new').toString().replace('_',' ').replace(/\b\w/g,c=>c.toUpperCase());
        const row = document.createElement('div');
        row.className = 'p-6 hover:bg-gray-50 transition-colors duration-200';
        row.innerHTML = `
          <div class="flex flex-col gap-4">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <div class="flex items-center gap-2 mb-2">
                  <h3 class="text-lg font-semibold text-gray-900 truncate">${e.title || 'Untitled Exam'}</h3>
                  <span class="bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded-full shrink-0">${status}</span>
                </div>
                ${e.description ? `<p class="text-gray-600 mb-3 line-clamp-3">${e.description}</p>` : ''}
                <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-gray-500">
                  <span class="inline-flex items-center"><i class="fas fa-file-alt mr-1 text-xs"></i>${e.exam_type || '—'}</span>
                  <span class="inline-flex items-center"><i class="fas fa-question-circle mr-1 text-xs"></i>${e.number_of_questions ?? 0} Questions</span>
                  <span class="inline-flex items-center"><i class="fas fa-copy mr-1 text-xs"></i>${e.sets_of_exam ?? 0} Sets</span>
                  <span class="inline-flex items-center"><i class="fas fa-clock mr-1 text-xs"></i>${fmtDate(e.created_at)}</span>
                </div>
              </div>
            </div>

            <div class="flex items-center gap-3">
              <a href="/exam/${encodeURIComponent(e.id)}" style="padding: 5px;margin:  2px"
                 class="flex-1 bg-blue-500 text-white px-5 py-2.5 rounded-lg hover:bg-blue-600 transition text-center font-medium">
                View
              </a>
              <button type="button" data-id="${e.id}" style="padding: 5px;margin:  2px"
                 class="btn-del flex-1 bg-red-500 text-white px-5 py-2.5 rounded-lg hover:bg-red-600 transition font-medium">
                Delete
              </button>
            </div>
          </div>`;
        list.appendChild(row);
      });

      // Bind delete
      list.querySelectorAll('.btn-del').forEach(btn => {
        btn.addEventListener('click', async () => {
          const id = btn.getAttribute('data-id');
          if (!confirm('Are you sure you want to delete this exam?')) return;
          try {
            const body = new URLSearchParams({ id }).toString();
            const res = await fetch(api.deleteExam, {
              method: 'POST',
              headers: bearerHeaders({ 'Content-Type':'application/x-www-form-urlencoded' }),
              body
            });
            const text = await res.text();
            const resp = /^\s*</.test(text) ? {status:'error', message:'Unexpected HTML from server'} : JSON.parse(text);
            if (resp.status === 'success') {
              showAlert('ok','Deleted successfully');
              loadExams();
            } else {
              showAlert('err', resp.message || 'Failed to delete exam.');
            }
          } catch (err) {
            console.error(err);
            showAlert('err','Server error while deleting.');
          }
        });
      });

    } catch (err) {
      console.error(err);
      showAlert('err','Server is temporarily busy. Please try again.');
    }
  }

  // Bootstrap
  document.addEventListener('DOMContentLoaded', async () => {
    const cache = readCache();
    const img = $('headerAvatar'); if (img) { img.src = resolveAvatar(cache.profile_picture); img.onerror = () => { img.src = DEFAULT_AVATAR; }; }
    const dimg = $('drawerAvatar'); if (dimg) { dimg.src = resolveAvatar(cache.profile_picture); dimg.onerror = () => { dimg.src = DEFAULT_AVATAR; }; }
    const dn = $('drawerName'); if (dn) dn.textContent = cache.name || 'User';

    // Logout
    document.getElementById('logoutBtn')?.addEventListener('click', () => {
      localStorage.removeItem(TOKEN_KEY);
      localStorage.removeItem(CACHE_KEY);
      location.replace('/login');
    });

    await refreshHeaderProfile();
    await loadExams();
  });
})();
  </script>
</body>
</html>
