<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Exam Miner 2.0 — View Exam (Mobile)</title>
  @vite('resources/css/app.css')
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"/>

  <!-- Export libs (order matters) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/html-to-pdfmake@2.4.5/browser.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.4/purify.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

  <style>
    :root{
      --safe-top: env(safe-area-inset-top, 0px);
      --safe-bottom: env(safe-area-inset-bottom, 0px);
    }
    @keyframes gradientShift { 0%{background-position:0% 50%}50%{background-position:100% 50%}100%{background-position:0% 50%}}
    .gradient-animated{background:linear-gradient(-45deg,#1e3a8a,#3b82f6,#60a5fa,#93c5fd,#1e40af,#1d4ed8);background-size:400% 400%;animation:gradientShift 15s ease infinite}
    .prose{font-family:ui-sans-serif,system-ui,-apple-system,"Segoe UI",Arial;line-height:1.6}
    .prose h1,.prose h2,.prose h3{font-weight:700;margin-top:1rem;margin-bottom:.5rem}
    .prose ol,.prose ul{padding-left:1.25rem}
    .prose .page-break{page-break-before:always}
    #paper:focus{box-shadow:0 0 0 3px rgba(59,130,246,.25)}
  </style>
</head>

<body class="min-h-screen bg-gray-50">
  <div class="absolute inset-0 gradient-animated pointer-events-none"></div>

  <script>
    const jwt = localStorage.getItem('jwt_token');
    if (!jwt) location.replace('/login');
  </script>

  <!-- ===== Mobile Top Bar ===== -->
  <header class="sticky top-0 z-50 bg-white/85 backdrop-blur border-b border-gray-200">
    <div class="flex items-center justify-between px-4 py-1">
      <button id="mOpenBtn"
              class="inline-flex items-center justify-center w-10 h-10 rounded-lg border border-gray-200 bg-white shadow-sm active:scale-95 transition">
        <i class="fas fa-bars text-gray-700"></i>
        <span class="sr-only">Open menu</span>
      </button>

      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow">
          <img style="width:30px" src="/images/icon.png"></img>
        </div>
        <h1 class="font-bold text-gray-900 whitespace-nowrap leading-tight
                   text-[clamp(1.1rem,4.6vw,1.35rem)]">Exam Miner 2.0</h1>
      </div>

      <a href="/profile" class="w-10 h-10 rounded-full overflow-hidden border border-gray-200 shadow">
        <img id="headerAvatar" src="/images/default-avatar.png" alt="Profile" class="w-full h-full object-cover"/>
      </a>
    </div>
  </header>

  <!-- ===== Mobile Drawer ===== -->
  <div id="mNav" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40" data-close></div>
    <aside id="mPanel"
           class="absolute left-0 top-0 h-full w-72 max-w-[85vw] bg-white shadow-2xl
                  -translate-x-full transition-transform duration-200">
      <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-blue-500 to-blue-600">
        <div class="flex items-center">
          <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center mr-3 shadow">
            <img style="width:30px" src="/images/icon.png"></img>
          </div>
          <h2 class="text-white font-bold text-lg">Exam Miner 2.0</h2>
        </div>
      </div>

      <nav class="p-4">
        <a href="/dashboard" class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-xl mb-2">
          <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
        </a>
        <a href="/generate-exam" class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-xl mb-2">
          <i class="fas fa-plus mr-3"></i> Generate Exam
        </a>
        <a href="/my-exams" class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-xl mb-2">
          <i class="fas fa-file-alt mr-3"></i> My Exams
        </a>
      </nav>

      <div class="mt-auto p-4 border-t border-gray-100 bg-gray-50" style="padding-bottom:calc(1rem + var(--safe-bottom))">
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

  <!-- ===== Main ===== -->
  <main class="px-4 pb-5 relative z-10">
    <!-- Header -->
    <section class="mb-1" style="margin-top: -2.0rem;">
      <div class="flex flex-col gap-3">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Exam Details</h1>
          <p class="text-gray-600">View, edit, export, and save your exam.</p>
        </div>

        <!-- Actions (scrollable row on small screens) -->
        <div class="flex items-center gap-1 overflow-x-auto pb-1 -mx-1 px-1">
          <button id="btnDocx" class="shrink-0 bg-blue-600 text-white px-3 py-3 rounded-lg hover:bg-blue-700 transition shadow-lg font-medium inline-flex items-center" style="padding: 10px; margin: 5px;background-color: #2563eb !important; color: white !important;">
            <i class="fas fa-file-word mr-1"></i> DOCX
          </button>
          <button id="btnPdf" class="shrink-0 bg-red-600 text-white px-3 py-3 rounded-lg hover:bg-red-700 transition shadow-lg font-medium inline-flex items-center" style="padding: 10px; margin: 5px;background-color: #dc2626 !important; color: white !important;">
            <i class="fas fa-file-pdf mr-1"></i> PDF
          </button>
          <button id="btnSave" class="shrink-0 bg-green-600 text-white px-3 py-3 rounded-lg hover:bg-green-700 transition shadow-lg font-medium inline-flex items-center" style="padding: 10px; margin: 5px;background-color: #16a34a !important; color: white !important;">
            <i class="fas fa-save mr-1"></i> Save
          </button>
          <a href="/my-exams" class="shrink-0 bg-gray-500 text-white px-3 py-3 rounded-lg hover:bg-gray-600 transition shadow-lg font-medium inline-flex items-center" style="padding: 10px; margin: 5px;background-color: #6b7280 !important; color: white !important;">
            <i class="fas fa-arrow-left mr-1"></i> Back
          </a>
        </div>
      </div>
      
      <!-- Notice: best experience on desktop for exports -->
<!--div style="padding: 10px; background-color: #fefefe;" class="mt-3 rounded-xl border border-amber-200 bg-amber-50 text-amber-900 p-3 text-sm leading-snug">
  <i class="fas fa-circle-info mr-2"></i>
  For the best export quality (DOCX/PDF with images), please use the desktop site. Mobile browsers may skip images when downloading files.
</div-->
<!-- Export Tip (mobile) — paste anywhere -->
<div class="export-note" role="note" aria-live="polite">
  <div class="en-icon" aria-hidden="true">
    <!-- inline svg so no external icons needed -->
    <svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 .001 20.001A10 10 0 0 0 12 2zm0 6.75a1.25 1.25 0 1 1 0 2.5 1.25 1.25 0 0 1 0-2.5zm-1.4 4.15h2.2c.33 0 .6.27.6.6v4.1c0 .33-.27.6-.6.6h-2.2a.6.6 0 0 1-.6-.6v-4.1c0-.33.27-.6.6-.6z"/></svg>
  </div>
  <div class="en-content">
    <div class="en-title">Best with Desktop</div>
    <div class="en-text">
      For the highest-quality DOCX/PDF exports <span class="nowrap">(with images)</span>, please use the desktop site.
      Some mobile browsers block image downloads in exported files.
    </div>
  </div>
</div>

<style>
  .export-note{
    --en-bg: rgba(255,255,255,.78);
    --en-fg: #0f172a;          /* slate-900 */
    --en-sub:#475569;          /* slate-600 */
    --en-ac: #2563eb;          /* blue-600 */
    --en-grad1:#60a5fa;        /* blue-400 */
    --en-grad2:#a78bfa;        /* violet-400 */
    position: relative;
    display: flex;
    align-items: flex-start;
    gap: .9rem;
    padding: .95rem 1rem;
    border-radius: 14px;
    background: 
      linear-gradient(var(--en-bg), var(--en-bg)) padding-box,
      linear-gradient(135deg,var(--en-grad1),var(--en-grad2)) border-box;
    border: 1.5px solid transparent;
    color: var(--en-fg);
    box-shadow: 0 8px 20px rgba(2,6,23,.08);
    -webkit-backdrop-filter: blur(6px);
    backdrop-filter: blur(6px);
  }
  .export-note .en-icon{
    flex: 0 0 36px;
    width: 36px; height: 36px;
    border-radius: 10px;
    display: grid; place-items: center;
    background: radial-gradient(120% 120% at 0% 0%, #dbeafe, #ede9fe);
    box-shadow: inset 0 1px 0 rgba(255,255,255,.6), 0 6px 14px rgba(37,99,235,.18);
  }
  .export-note .en-icon svg{
    width: 20px; height: 20px; fill: var(--en-ac);
  }
  .export-note .en-content{ min-width: 0; }
  .export-note .en-title{
    font-weight: 800;
    letter-spacing: .2px;
    font-size: .98rem;
    margin-bottom: .15rem;
  }
  .export-note .en-text{
    font-size: .86rem;
    line-height: 1.35;
    color: var(--en-sub);
  }
  .export-note .nowrap{ white-space: nowrap; }
  /* nice subtle pulse on mount (optional) */
  .export-note{ animation: en-pop .28s ease-out; }
  @keyframes en-pop{ from{ transform: translateY(4px) scale(.98); opacity: .0 } to{ transform:none; opacity:1 } }
  /* compact on very small screens */
  @media (max-width:380px){
    .export-note{ padding:.8rem .85rem; border-radius:12px }
    .export-note .en-icon{ width:32px;height:32px }
    .export-note .en-title{ font-size:.95rem }
    .export-note .en-text{ font-size:.83rem }
  }
</style>



    </section>

    <!-- Exam Info -->
    <section class="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden mb-6">
      <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6 text-white">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <h2 id="examTitle" class="text-2xl font-bold mb-1 outline-none break-words" contenteditable="true">Loading…</h2>
            <p id="examDesc" class="text-blue-100 outline-none break-words" contenteditable="true">Please wait</p>
            <p class="text-xs opacity-80 mt-1">Tip: You can edit the title and description above.</p>
          </div>
          <span id="examStatus" class="bg-white bg-opacity-20 px-3 py-1 rounded-full text-sm font-medium text-gray-900 whitespace-nowrap">—</span>
        </div>
      </div>

      <div class="p-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
          <div class="text-center p-4 bg-blue-50 rounded-lg">
            <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center mx-auto mb-3">
              <i class="fas fa-file-alt text-white text-lg"></i>
            </div>
            <h3 class="font-semibold text-gray-900 mb-1">Exam Type</h3>
            <p id="examType" class="text-gray-600">—</p>
          </div>

          <div class="text-center p-4 bg-green-50 rounded-lg">
            <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center mx-auto mb-3">
              <i class="fas fa-question-circle text-white text-lg"></i>
            </div>
            <h3 class="font-semibold text-gray-900 mb-1">Questions</h3>
            <p id="examQuestions" class="text-gray-600">0</p>
            <p id="examDetected" class="text-xs text-gray-500 mt-1"></p>
          </div>

          <div class="text-center p-4 bg-purple-50 rounded-lg">
            <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center mx-auto mb-3">
              <i class="fas fa-copy text-white text-lg"></i>
            </div>
            <h3 class="font-semibold text-gray-900 mb-1">Exam Sets</h3>
            <p id="examSets" class="text-gray-600">0</p>
          </div>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-sm text-gray-500">
          <div class="flex items-center">
            <i class="fas fa-clock mr-2"></i>
            Created: <span id="createdAt" class="ml-1">—</span>
          </div>
          <div class="flex items-center">
            <i class="fas fa-upload mr-2"></i>
            Last updated: <span id="updatedAt" class="ml-1">—</span>
          </div>
        </div>
      </div>
    </section>
    
    <style>
  .editor-toolbar {
    --et-bg: #ffffff;
    --et-border: #e5e7eb;     /* gray-200 */
    --et-shadow: 0 6px 18px -8px rgba(0,0,0,.15);
    --et-hover: #f8fafc;      /* gray-50 */
    --et-press: #f1f5f9;      /* gray-100 */
    --et-accent: #3b82f6;     /* blue-500 */
    --et-accent-600: #2563eb; /* blue-600 */
    --et-text: #0f172a;       /* slate-900 */
  }

  /* Base button look */
  .editor-toolbar button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .5rem;
    padding: .55rem .8rem;
    border-radius: .75rem;
    border: 1px solid var(--et-border);
    background: linear-gradient(180deg, #fff, #f9fafb 80%, #f3f4f6);
    box-shadow: 0 1px 0 rgba(255,255,255,.8) inset, var(--et-shadow);
    color: var(--et-text);
    transition: background .18s ease, transform .05s ease, border-color .18s ease, box-shadow .18s ease;
    cursor: pointer;
  }

  .editor-toolbar button i { font-size: .95rem; }

  .editor-toolbar button:hover {
    background: var(--et-hover);
    border-color: #d1d5db; /* gray-300 */
  }

  .editor-toolbar button:active {
    transform: translateY(1px);
    background: var(--et-press);
  }

  .editor-toolbar button:focus-visible {
    outline: 0;
    box-shadow: 0 0 0 3px rgba(59,130,246,.25), var(--et-shadow);
  }

  /* Active/toggled state (apply .is-active or aria-pressed="true") */
  .editor-toolbar button.is-active,
  .editor-toolbar button[aria-pressed="true"] {
    background: linear-gradient(180deg, var(--et-accent), var(--et-accent-600));
    border-color: var(--et-accent-600);
    color: #fff;
    box-shadow: 0 1px 0 rgba(255,255,255,.15) inset, 0 10px 20px -10px rgba(37,99,235,.65);
  }

  /* Group alignment buttons nicely if you want to wrap them (optional) */
  .editor-toolbar .align-group {
    display: inline-flex;
    border: 1px solid var(--et-border);
    border-radius: .75rem;
    overflow: hidden;
    box-shadow: var(--et-shadow);
    background: #fff;
  }
  .editor-toolbar .align-group button {
    border: 0;
    border-right: 1px solid var(--et-border);
    border-radius: 0;
  }
  .editor-toolbar .align-group button:last-child { border-right: 0; }

  /* Select (custom “chip” style) */
  .editor-toolbar select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;

    padding: .55rem 2.25rem .55rem .8rem;
    border-radius: .75rem;
    border: 1px solid var(--et-border);
    background:
      linear-gradient(180deg, #fff, #f9fafb 80%, #f3f4f6),
      /* arrow */
      url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 20 20' fill='none' stroke='%236b7280' stroke-width='1.7' stroke-linecap='round' stroke-linejoin='round'><polyline points='6 8 10 12 14 8'/></svg>") no-repeat right .7rem center / 14px 14px;
    color: var(--et-text);
    box-shadow: var(--et-shadow);
    transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
  }
  .editor-toolbar select:hover  { border-color: #d1d5db; }
  .editor-toolbar select:focus-visible {
    outline: 0;
    box-shadow: 0 0 0 3px rgba(59,130,246,.25), var(--et-shadow);
  }

  /* Compact on very small screens */
  @media (max-width: 420px) {
    .editor-toolbar button { padding: .48rem .65rem; border-radius: .65rem; }
    .editor-toolbar select { padding: .48rem 2.1rem .48rem .65rem; border-radius: .65rem; }
    .editor-toolbar button i { font-size: .9rem; }
  }
</style>

    
    <!-- Editor Toolbar -->
    <div class="editor-toolbar bg-white rounded-xl shadow-md border border-gray-100 p-3 mb-3">
      <div class="flex flex-wrap gap-2">
        <button data-cmd="bold"><i class="fas fa-bold"></i></button>
        <button data-cmd="italic"><i class="fas fa-italic"></i></button>
        <button data-cmd="underline"><i class="fas fa-underline"></i></button>
        <button data-cmd="insertUnorderedList"><i class="fas fa-list-ul"></i></button>
        <button data-cmd="insertOrderedList"><i class="fas fa-list-ol"></i></button>
        <button data-align="left"><i class="fas fa-align-left"></i></button>
        <button data-align="center"><i class="fas fa-align-center"></i></button>
        <button data-align="right"><i class="fas fa-align-right"></i></button>
        <select id="fontSizeSel">
          <option value="">Font Size</option>
          <option value="12px">12</option>
          <option value="14px">14</option>
          <option value="16px">16</option>
          <option value="18px">18</option>
          <option value="24px">24</option>
          <option value="32px">32</option>
        </select>
      </div>
    </div>


    <!-- Editable Paper -->
    <section class="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden">
      <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-6 border-b border-gray-200">
        <h3 class="text-xl font-bold text-gray-900">Exam Paper (Editable)</h3>
        <p class="text-gray-600 mt-1">Edit directly below. Changes are local until you click “Save”.</p>
      </div>

      <div class="p-6">
        <div id="paper" class="prose max-w-none outline-none border border-gray-200 rounded-lg p-5 min-h-[600px] bg-white" contenteditable="true">
          <div class="bg-gray-50 text-gray-600 rounded p-4">Loading paper…</div>
        </div>
        <div id="saveNote" class="text-xs text-gray-500 mt-2">Unsaved</div>
      </div>
    </section>

    <div style="height:var(--safe-bottom)"></div>
  </main>

  <!-- ===== Drawer Toggle (mobile only) ===== -->
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

  <!-- ===== App Logic (mobile) ===== -->
  <script>
    /* --------- config --------- */
    const TOKEN_KEY   = 'jwt_token';
    const CACHE_KEY   = 'profile_cache';
    const DEFAULT_AVATAR = '/images/default-avatar.png';

    const api = {
      examShow:   '/api/exam_show.php',
      examUpdate: '/api/exam_update.php',
      me:         '/api/me.php',
    };

    /* --------- utils --------- */
    const $ = (id) => document.getElementById(id);
    const RAW_JWT = (localStorage.getItem(TOKEN_KEY) || '').replace(/^"|"$/g,'').replace(/^Bearer\s+/i,'');
    if (!RAW_JWT) location.replace('/login');

    function bearerHeaders(extra = {}) {
      const t = (localStorage.getItem(TOKEN_KEY) || '').replace(/^"|"$/g,'').replace(/^Bearer\s+/i,'');
      return { 'Authorization': 'Bearer ' + t, 'Accept': 'application/json', ...extra };
    }
    function parseJwt(t){
      try{
        const [,p]=t.split('.'); if(!p) return {};
        const b=p.replace(/-/g,'+').replace(/_/g,'/');
        const json=decodeURIComponent(atob(b).split('').map(c => '%'+('00'+c.charCodeAt(0).toString(16)).slice(-2)).join(''));
        return JSON.parse(json);
      }catch{ return {}; }
    }
    (function expiryGuard(){
      const payload = parseJwt(RAW_JWT);
      if (payload?.exp && Date.now() >= payload.exp * 1000) {
        localStorage.removeItem(TOKEN_KEY);
        localStorage.removeItem(CACHE_KEY);
        location.replace('/login');
      }
    })();

    async function getJSONWithRetry(url, {params = {}, method='GET', body=null, retries = 5, backoffMs = 600, headersExtra={}} = {}) {
      const u = new URL(url, location.origin);
      if (method === 'GET') {
        Object.entries(params).forEach(([k,v]) => u.searchParams.set(k, v));
      }
      for (let attempt = 0; attempt <= retries; attempt++) {
        try {
          const res = await fetch(u.toString(), {
            method,
            headers: bearerHeaders(headersExtra),
            body,
            cache: 'no-store',
            credentials: 'omit',
          });
          if ([401].includes(res.status)) throw new Error('Unauthorized');
          if ([429,502,503,504].includes(res.status)) {
            if (attempt === retries) throw new Error('Server temporarily unavailable.');
            const wait = backoffMs * Math.pow(2, attempt) + Math.floor(Math.random()*250);
            await new Promise(r => setTimeout(r, wait));
            continue;
          }
          const text = await res.text();
          if (!text) return {};
          if (/^\s*</.test(text) && !text.trim().startsWith('{')) throw new Error('Unexpected HTML from server');
          return JSON.parse(text);
        } catch (e) {
          if (attempt === retries) throw e;
          const wait = backoffMs * Math.pow(2, attempt) + Math.floor(Math.random()*250);
          await new Promise(r => setTimeout(r, wait));
        }
      }
    }

    const titleCase = (s='') => s.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
    const fmtDate   = (s) => s ? new Date(String(s).replace(' ','T')).toLocaleString() : '—';

    // editor helpers
    function exec(cmd, value=null){ document.execCommand(cmd, false, value); }
    function applyAlign(val){
      if (val==='left') exec('justifyLeft');
      if (val==='center') exec('justifyCenter');
      if (val==='right') exec('justifyRight');
    }
    function applyFontSize(px){
      if (!px) return;
      const sel = window.getSelection();
      if (!sel.rangeCount) return;
      const range = sel.getRangeAt(0);
      const span = document.createElement('span');
      span.style.fontSize = px;
      span.textContent = sel.toString() || ' ';
      range.deleteContents(); range.insertNode(span);
    }

    function stripUnsupportedFonts(html){ return html.replace(/font-family\s*:\s*[^;"]+;?/gi, ''); }

  </script>
  <!--script>
/* --- ensure the converter is present (retry with alternate CDNs) --- */
async function ensureHtmlDocx() {
  if (window.htmlDocx?.asBlob) return true;
  const urls = [
    'https://cdn.jsdelivr.net/npm/html-docx-js@0.4.1/dist/html-docx.js',
    'https://unpkg.com/html-docx-js@0.4.1/dist/html-docx.js',
    'https://cdnjs.cloudflare.com/ajax/libs/html-docx-js/0.4.1/html-docx.min.js'
  ];
  const load = (src) => new Promise((res, rej) => {
    const s = document.createElement('script');
    s.src = src; s.async = false;
    s.onload = () => res(true);
    s.onerror = rej;
    document.head.appendChild(s);
  });
  for (const u of urls) {
    try { await load(u); } catch {}
    if (window.htmlDocx?.asBlob) return true;
  }
  return false;
}

/* --- get clean editor HTML --- */
function getEditorHTMLClean() {
  const el = document.getElementById('paper');
  if (!el) return '';
  const node = el.cloneNode(true);
  node.querySelectorAll('script,style').forEach(n => n.remove());
  node.removeAttribute('contenteditable');
  node.querySelectorAll('[contenteditable]').forEach(n=>n.removeAttribute('contenteditable'));
  return node.innerHTML || '';
}

/* --- convert DIV soup to P/UL/OL, which Word Mobile likes better --- */
function normalizeForDocx(html) {
  const tmp = document.createElement('div');
  tmp.innerHTML = html;

  // wrap stray text nodes
  [...tmp.childNodes].forEach(n => {
    if (n.nodeType === Node.TEXT_NODE && n.textContent.trim() !== '') {
      const p = document.createElement('p'); p.textContent = n.textContent; n.replaceWith(p);
    }
  });

  // simple DIV → P when it contains only inline content
  tmp.querySelectorAll('div').forEach(d => {
    const hasBlock = [...d.children].some(c =>
      /^(DIV|P|UL|OL|LI|TABLE|THEAD|TBODY|TR|TD|TH|SECTION|ARTICLE|H1|H2|H3|H4|H5|H6|PRE)$/i.test(c.tagName)
    );
    if (!hasBlock) {
      const p = document.createElement('p');
      p.innerHTML = d.innerHTML;
      d.replaceWith(p);
    }
  });

  // drop empty paragraphs
  tmp.querySelectorAll('p').forEach(p => {
    if (p.textContent.replace(/\u00a0/g,' ').trim() === '' && !p.querySelector('img,table,ul,ol')) p.remove();
  });

  return tmp.innerHTML;
}

/* --- allow typical inline styles/tags; DOMPurify is already loaded --- */
function sanitizeForDocx(html){
  return DOMPurify.sanitize(html, {
    ADD_ATTR: ['style'],
    ALLOWED_TAGS: [
      'p','br','strong','b','em','i','u','sub','sup','span',
      'ul','ol','li','blockquote','hr',
      'table','thead','tbody','tr','td','th','colgroup','col',
      'h1','h2','h3','h4','h5','h6','img','pre','code'
    ]
  });
}

// function sanitizeFilename(s){ return String(s).trim().replace(/[^\w\-\. ]+/g,'_'); }
function downloadDOC_Fallback(titleText, innerHtml, fname){
  const html = `<!doctype html><html><head><meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>${titleText}</title>
    <style>
      body{font-family:Arial,sans-serif;font-size:12pt;line-height:1.5;color:#000}
      p{margin:0 0 10pt} table{border-collapse:collapse;margin:8pt 0}
      td,th{border:1px solid #999;padding:4pt;vertical-align:top}
    </style></head><body>
    <div style="text-align:center;font-weight:bold;font-size:14pt;margin:0 0 12pt">${titleText}</div>
    ${innerHtml}
  </body></html>`;
  const blob = new Blob([html], { type: 'application/msword;charset=utf-8' });
  saveAs(blob, fname + '.doc');
}

/* --- REPLACE your mobile downloadDOCX with this --- */
async function downloadDOCX(title){
  // 1) make sure the converter exists (mobile network/CDN can be flaky)
  await ensureHtmlDocx();

  const titleText = (title || 'Exam Paper').toUpperCase();
  const fname     = sanitizeFilename(title || 'exam_paper');

  // 2) clean + sanitize + normalize for Word Mobile
  const raw  = getEditorHTMLClean();
  const safe = sanitizeForDocx(raw);
  const norm = normalizeForDocx(safe);

  if (!norm.trim()){
    alert('There is no content to export yet.');
    return;
  }

  const html = `
    <!DOCTYPE html><html><head><meta charset="utf-8">
      <style>
        body{font-family:Arial,sans-serif;font-size:14pt;line-height:1.6;color:#000}
        p{margin:0 0 10pt} h1,h2,h3{font-weight:bold;margin:12pt 0 6pt}
        ul,ol{margin:0 0 10pt 22pt}
        table{border-collapse:collapse;margin:8pt 0}
        td,th{border:1px solid #999;padding:4pt;vertical-align:top}
        pre,code{font-family:Consolas,Monaco,monospace}
      </style>
    </head><body>
      <div style="text-align:center;font-weight:bold;font-size:16pt;margin:0 0 12pt">${titleText}</div>
      ${norm}
    </body></html>`.trim();

  // 3) if converter still missing or fails, fall back to .doc (works great on phones)
  if (!window.htmlDocx?.asBlob) {
    downloadDOC_Fallback(titleText, norm, fname);
    return;
  }

  const blob = window.htmlDocx.asBlob(html);

  // 4) mobile safeguard: some bad conversions return a tiny/invalid zip → use .doc
  if (!blob || (blob.size && blob.size < 3000)) {
    downloadDOC_Fallback(titleText, norm, fname);
    return;
  }

  saveAs(blob, fname + '.docx');
}
</script-->
<script>
 function sanitizeFilename(s){ return String(s).trim().replace(/[^\w\-\. ]+/g,'_'); }
function getEditorHTML(){ return document.getElementById('paper')?.innerHTML || ''; }

async function downloadFromServer(format){
    //alert("Downloading DOCX files is not currently supported on mobile devices. Please use a desktop computer or download the PDF version instead.");
    // return;
    
  const title = (document.getElementById('examTitle').innerText || 'Exam Paper').trim();
  const html  = getEditorHTML();
  const fd = new FormData();
  fd.append('id', (window.loadedExam?.id || ''));       // optional (server can fetch from DB)
  fd.append('title', title);
  fd.append('html', html);                               // export exactly what's in the editor
  // NOTE: token removed intentionally for debug / direct download

  const url = format === 'docx' ? '/api/export_docx.php' : '/api/export_pdf.php';
  const res = await fetch(url, { method: 'POST', body: fd });
  if (!res.ok) {
    const t = await res.text().catch(()=> '');
    console.error('Export failed', res.status, t);
    alert('Export failed: ' + (t || res.status));
    return;
  }
  const blob = await res.blob();
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = sanitizeFilename(title) + '.' + format;
  document.body.appendChild(a);
  a.click();
  setTimeout(() => { URL.revokeObjectURL(a.href); a.remove(); }, 0);
}

//document.getElementById('btnDocx')?.addEventListener('click', ()=>downloadDOCX(($('examTitle').innerText || 'Exam Paper').trim()));
document.getElementById('btnDocx')?.addEventListener('click', downloadDOCXbyId);

// document.getElementById('btnPdf') ?.addEventListener('click', ()=>downloadFromServer('pdf'));

    
    
    function downloadDOCX(title){
     // downloadFromServer('docx');
      
     fetch('/api/export_docx_diag.php', {
  method: 'POST',
  body: new FormData(Object.assign(document.createElement('form'), {
    title: document.getElementById('examTitle').innerText.trim(),
    html:  document.getElementById('paper').innerHTML
  }))
}).then(r=>r.json()).then(console.log);
    }
    
   async function downloadDOCXbyId() {
  // extract exam id from URL (your code already does this)
  const examId = (location.pathname.match(/\/exam\/(\d+)/)?.[1]) ||
                 new URLSearchParams(location.search).get('id');
  if (!examId) { alert('No exam id'); return; }

  const title = (document.getElementById('examTitle')?.innerText || 'Exam Paper').trim();

  const fd = new FormData();
  fd.append('id', examId);     // let PHP fetch body_html
  fd.append('title', title);

  const res = await fetch('/api/export_docx.php', { method: 'POST', body: fd });
  if (!res.ok) {
    const t = await res.text().catch(()=> '');
    console.error('Export failed', res.status, t);
    alert('Export failed: ' + (t || res.status));
    return;
  }
  const blob = await res.blob();
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = (title.replace(/[^\w\-. ]+/g,'_') || 'Exam Paper') + '.docx';
  document.body.appendChild(a);
  a.click();
  setTimeout(()=>{ URL.revokeObjectURL(a.href); a.remove(); }, 0);
}

    
    function downloadPDF(title){
      const docTitle = (title || 'Exam Paper').toUpperCase();
      let html = DOMPurify.sanitize(getEditorHTML(), { ADD_ATTR: ['style'] });
      html = stripUnsupportedFonts(html);
      const pdfContent = window.htmlToPdfmake(html, { window });
      const docDefinition = {
        info: { title: docTitle },
        pageSize: "A4",
        pageMargins: [40, 60, 40, 60],
        content: [
          { text: docTitle, style: "header", alignment: "center", margin: [0,0,0,12] },
          ...pdfContent
        ],
        styles: { header: { fontSize: 16, bold: true } },
        defaultStyle: { font: "Roboto", fontSize: 12, lineHeight: 1.4 }
      };
      pdfMake.createPdf(docDefinition).download(docTitle + ".pdf");
    } 

    // IDs
    const examId = (() => {
      const m = location.pathname.match(/\/exam\/(\d+)(?:\/)?$/i);
      if (m && m[1]) return m[1];
      const qs = new URLSearchParams(location.search);
      return qs.get('id');
    })();
    if (!examId) { location.replace('/my-exams'); }

    // quick paint avatar/name from cache
    (function fillHeaderQuick(){
      const cache = (()=>{ try{return JSON.parse(localStorage.getItem(CACHE_KEY)||'{}')}catch{return{}}})() || {};
      const img = $('headerAvatar'); if (img) { img.src = cache.profile_picture || '/images/default-avatar.png'; img.onerror = () => { img.src = DEFAULT_AVATAR; }; }
      const dimg = $('drawerAvatar'); if (dimg) { dimg.src = cache.profile_picture || '/images/default-avatar.png'; dimg.onerror = () => { dimg.src = DEFAULT_AVATAR; }; }
      const dn = $('drawerName'); if (dn) dn.textContent = cache.name || cache.username || cache.email || 'User';
    })();

    async function refreshHeaderProfile(){
      try {
        const me = await getJSONWithRetry(api.me);
        if (me?.status === 'success' && me.user) {
          const name = me.user.name || me.user.username || me.user.email || 'User';
          const img = $('headerAvatar'); if (img) { img.src = (me.user.profile_picture || '').trim() || DEFAULT_AVATAR; img.onerror = () => { img.src = DEFAULT_AVATAR; }; }
          const dimg = $('drawerAvatar'); if (dimg) { dimg.src = (me.user.profile_picture || '').trim() || DEFAULT_AVATAR; dimg.onerror = () => { dimg.src = DEFAULT_AVATAR; }; }
          const dn = $('drawerName'); if (dn) dn.textContent = name;
          localStorage.setItem(CACHE_KEY, JSON.stringify({
            name,
            username: me.user.username || '',
            email: me.user.email || '',
            profile_picture: (me.user.profile_picture || '').trim(),
            updated_at: me.user.updated_at || new Date().toISOString()
          }));
        }
      } catch {}
    }

    let loadedExam = { id:null, title:'', description:'', exam_type:'', number_of_questions:0, sets_of_exam:0 };

    async function loadExam(){
      try {
        const r = await getJSONWithRetry(api.examShow, { params: { id: examId } });
        if (r?.status !== 'success' || !r.exam) throw new Error(r?.message || 'Exam not found');

        const e = r.exam;
        loadedExam = {
          id: e.id || examId,
          title: e.title || 'Untitled Exam',
          description: e.description || '',
          exam_type: e.exam_type || '',
          number_of_questions: +e.number_of_questions || 0,
          sets_of_exam: +e.sets_of_exam || 0
        };

        $('examTitle').textContent     = loadedExam.title;
        $('examDesc').textContent      = loadedExam.description || '—';
        $('examStatus').textContent    = titleCase(e.status || 'generated');
        $('examType').textContent      = titleCase(loadedExam.exam_type);
        $('examQuestions').textContent = loadedExam.number_of_questions;
        $('examSets').textContent      = loadedExam.sets_of_exam;
        $('createdAt').textContent     = fmtDate(e.created_at);
        $('updatedAt').textContent     = fmtDate(e.updated_at);

        const detected  = +e.computed_questions || 0;
        const requested = loadedExam.number_of_questions;
        if (detected && requested && detected !== requested) {
          $('examDetected') && ($('examDetected').textContent = `Detected from paper: ${detected}`);
        }

        $('paper').innerHTML = e.body_html
          ? e.body_html
          : '<div class="bg-yellow-50 border border-yellow-200 text-yellow-700 rounded p-4">No saved content for this exam.</div>';

        $('saveNote').textContent = 'Loaded';
      } catch (err) {
        console.error('exam_show failed:', err);
        $('paper').innerHTML = '<div class="bg-red-50 border border-red-200 text-red-700 rounded p-4">Failed to load exam.</div>';
        $('examTitle').textContent = 'Exam Not Found';
        $('examDesc').textContent  = 'We couldn’t load this exam.';
      }
    }

    // toolbar wiring
    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('[data-cmd]').forEach(btn=>btn.addEventListener('click',()=>exec(btn.dataset.cmd)));
      document.querySelectorAll('[data-align]').forEach(btn=>btn.addEventListener('click',()=>applyAlign(btn.dataset.align)));
      $('fontSizeSel')?.addEventListener('change', e => applyFontSize(e.target.value));
      $('paper')?.addEventListener('input', ()=> $('saveNote').textContent = 'Unsaved changes…');
      $('examTitle')?.addEventListener('input', ()=> $('saveNote').textContent = 'Unsaved changes…');
      $('examDesc')?.addEventListener('input', ()=> $('saveNote').textContent = 'Unsaved changes…');

      $('btnSave')?.addEventListener('click', async ()=>{
        const btn = $('btnSave');
        btn.disabled = true; btn.classList.add('opacity-60','pointer-events-none');
        try {
          const title = $('examTitle').innerText.trim();
          const description = $('examDesc').innerText.trim();
          const cleanHtml = DOMPurify.sanitize($('paper').innerHTML, { ADD_ATTR: ['style'] });

          const fd = new FormData();
          fd.append('id', loadedExam.id);
          if (title) fd.append('title', title);
          fd.append('description', description);
          fd.append('body_html', cleanHtml);
          //fd.append('token', RAW_JWT); // fallback for PHP envs stripping Authorization

          const res = await fetch(api.examUpdate, { method:'POST', headers: bearerHeaders(), body: fd });
          const data = await res.json().catch(()=>({status:'error',message:'Invalid server response'}));
          if (data.status !== 'success') throw new Error(data.message || 'Save failed');
          $('updatedAt').textContent = fmtDate(data.updated_at || new Date().toISOString());
          $('saveNote').textContent = 'Saved';
          loadedExam.title = title || loadedExam.title;
          alert('Exam saved successfully.');
        } catch(e){
          console.error(e);
          alert(e.message || 'Save failed.');
        } finally {
          btn.disabled = false; btn.classList.remove('opacity-60','pointer-events-none');
        }
      });

      //$('btnDocx')?.addEventListener('click', ()=>downloadDOCX(($('examTitle').innerText || 'Exam Paper').trim()));
      $('btnPdf')?.addEventListener('click', ()=>downloadPDF(($('examTitle').innerText || 'Exam Paper').trim()));
      $('logoutBtn')?.addEventListener('click', () => {
        localStorage.removeItem(TOKEN_KEY);
        localStorage.removeItem(CACHE_KEY);
        location.replace('/login');
      });
    });

    (async function init(){
      await refreshHeaderProfile();
      await loadExam();
    })();
  </script>
</body>
</html>
