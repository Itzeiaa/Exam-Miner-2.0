<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Exam Miner 2.0 - View Exam</title>
  @vite('resources/css/app.css')
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"/>

  <!-- ----- Export libs (order matters) ----- -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/html-to-pdfmake@2.4.5/browser.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.4/purify.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/html-docx-js/dist/html-docx.js"></script>
  <!--script src="https://cdn.jsdelivr.net/npm/html-docx-js@0.3.1/dist/html-docx.js"></script-->
</head>
<body class="min-h-screen relative overflow-x-hidden bg-gray-50">
  <div class="absolute inset-0 gradient-animated pointer-events-none"></div>

  <script>
    const jwt = localStorage.getItem('jwt_token');
    if (!jwt) location.replace('/login');
  </script>

   <div class="flex relative z-10 w-full min-h-screen overflow-y-auto">
    <!-- Sidebar -->
    <aside class="w-64 bg-white shadow-2xl min-h-screen border-r border-gray-200 relative">
      <a href="/dashboard" class="flex items-center p-6 border-b border-gray-100 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 transition-all duration-200">
        <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center mr-3 shadow-lg">
          <img style="width:30px" src="/images/icon.png"></img>
        </div>
        <h1 class="text-xl font-bold text-white">Exam Miner 2.0</h1>
      </a>

      <nav class="mt-6 px-4">
        <a href="/dashboard" class="flex items-center px-4 py-3 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition-all duration-200 mb-2 group">
          <i class="fas fa-tachometer-alt mr-3 group-hover:scale-110 transition-transform duration-200"></i>
          Dashboard
        </a>
        <a href="/generate-exam" class="flex items-center px-4 py-3 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition-all duration-200 mb-2 group">
          <i class="fas fa-plus mr-3 group-hover:scale-110 transition-transform duration-200"></i>
          Generate Exam
        </a>
        <a href="/my-exams" class="flex items-center px-4 py-3 text-white bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg mb-2 transform hover:scale-105 transition-all duration-200">
          <i class="fas fa-file-alt mr-3"></i>
          My Exams
        </a>
        <a href="/profile" class="flex items-center px-4 py-3 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition-all duration-200 mb-2 group">
          <i class="fas fa-user mr-3 group-hover:scale-110 transition-transform duration-200"></i>
          Profile
        </a>
      </nav>

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

    <!-- Main -->
    <main class="flex-1 p-8 min-h-screen overflow-y-auto">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Exam Details</h1>
            <p class="text-gray-600">View, edit, export, and save your exam.</p>
          </div>
          <div class="flex flex-wrap gap-4">
            <button id="btnDocx" class="bg-blue-600 text-white px-4 py-3 rounded-lg hover:bg-blue-700 transition-all duration-200 shadow-lg hover:shadow-xl font-medium inline-flex items-center transform hover:scale-105 active:scale-95" style="background-color: #2563eb !important; color: white !important;">
              <i class="fas fa-file-word mr-2"></i> Download DOCX
            </button>
            <button id="btnPdf" class="bg-red-600 text-white px-4 py-3 rounded-lg hover:bg-red-700 transition-all duration-200 shadow-lg hover:shadow-xl font-medium inline-flex items-center transform hover:scale-105 active:scale-95" style="background-color: #dc2626 !important; color: white !important;">
              <i class="fas fa-file-pdf mr-2"></i> Download PDF
            </button>
            <button id="btnSave" class="bg-green-600 text-white px-4 py-3 rounded-lg hover:bg-green-700 transition-all duration-200 shadow-lg hover:shadow-xl font-medium inline-flex items-center transform hover:scale-105 active:scale-95" style="background-color: #16a34a !important; color: white !important;">
              <i class="fas fa-save mr-2"></i> Save Changes
            </button>
            <a href="/my-exams" class="bg-gray-500 text-white px-4 py-3 rounded-lg hover:bg-gray-600 transition-all duration-200 shadow-lg hover:shadow-xl font-medium inline-flex items-center transform hover:scale-105 active:scale-95">
              <i class="fas fa-arrow-left mr-2"></i> Back to Exams
            </a>
          </div>
        </div>
      </div>

      <!-- Exam Info -->
      <section class="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden mb-8">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6 text-white">
          <div class="flex items-center justify-between">
            <div>
              <!-- Make title and description editable inline -->
              <h2 id="examTitle" class="text-2xl font-bold mb-1 outline-none" contenteditable="true">Loading…</h2>
              <p id="examDesc" class="text-blue-100 outline-none" contenteditable="true">Please wait</p>
              <p class="text-xs opacity-80 mt-1">Tip: You can edit the title and description above.</p>
            </div>
            <div class="text-right">
              <span id="examStatus" class="bg-white bg-opacity-20 px-3 py-1 rounded-full text-sm font-medium text-gray-900">—</span>
            </div>
          </div>
        </div>

        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
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

          <div class="flex items-center justify-between text-sm text-gray-500">
            <div class="flex items-center">
              <i class="fas fa-clock mr-2"></i>
              Created: <span id="createdAt">—</span>
            </div>
            <div class="flex items-center">
              <i class="fas fa-upload mr-2"></i>
              Last updated: <span id="updatedAt">—</span>
            </div>
          </div>
        </div>
      </section>

      <!-- Editor Toolbar >
      <div class="bg-white rounded-xl shadow-md border border-gray-100 p-3 mb-3 flex flex-wrap gap-2">
        <button data-cmd="bold" class="px-3 py-2 border rounded hover:bg-gray-50"><i class="fas fa-bold"></i></button>
        <button data-cmd="italic" class="px-3 py-2 border rounded hover:bg-gray-50"><i class="fas fa-italic"></i></button>
        <button data-cmd="underline" class="px-3 py-2 border rounded hover:bg-gray-50"><i class="fas fa-underline"></i></button>
        <button data-cmd="insertUnorderedList" class="px-3 py-2 border rounded hover:bg-gray-50"><i class="fas fa-list-ul"></i></button>
        <button data-cmd="insertOrderedList" class="px-3 py-2 border rounded hover:bg-gray-50"><i class="fas fa-list-ol"></i></button>
        <button data-align="left" class="px-3 py-2 border rounded hover:bg-gray-50"><i class="fas fa-align-left"></i></button>
        <button data-align="center" class="px-3 py-2 border rounded hover:bg-gray-50"><i class="fas fa-align-center"></i></button>
        <button data-align="right" class="px-3 py-2 border rounded hover:bg-gray-50"><i class="fas fa-align-right"></i></button>
        <select id="fontSizeSel" class="px-2 py-2 border rounded">
          <option value="">Font Size</option>
          <option value="12px">12</option>
          <option value="14px">14</option>
          <option value="16px">16</option>
          <option value="18px">18</option>
          <option value="24px">24</option>
          <option value="32px">32</option>
        </select>
      </div-->
      
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

      <!-- Rendered/Editable Paper -->
      <section class="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-6 border-b border-gray-200">
          <h3 class="text-xl font-bold text-gray-900">Exam Paper (Editable)</h3>
          <p class="text-gray-600 mt-1">Edit directly below. Changes are local until you click “Save Changes”.</p>
        </div>

        <div class="p-6">
          <div id="paper" class="prose max-w-none outline-none border border-gray-200 rounded-lg p-5 min-h-[600px] bg-white" contenteditable="true">
            <div class="bg-gray-50 text-gray-600 rounded p-4">Loading paper…</div>
          </div>
          <div id="saveNote" class="text-xs text-gray-500 mt-2">Unsaved</div>
        </div>
      </section>
    </main>
  </div>

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

    @keyframes gradientShift { 0% { background-position: 0% 50% } 50% { background-position: 100% 50% } 100% { background-position: 0% 50% } }
    .gradient-animated { background: linear-gradient(-45deg,#1e3a8a,#3b82f6,#60a5fa,#93c5fd,#1e40af,#1d4ed8); background-size: 400% 400%; animation: gradientShift 15s ease infinite; }
    .prose { font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Arial; line-height: 1.6; }
    .prose h1,.prose h2,.prose h3{ font-weight:700; margin-top: 1rem; margin-bottom: .5rem; }
    .prose ol, .prose ul { padding-left: 1.25rem; }
    .prose .page-break { page-break-before: always; }
    #paper:focus { box-shadow: 0 0 0 3px rgba(59,130,246,.25); }
  </style>

  <script>
    /* --------- config --------- */
    const TOKEN_KEY   = 'jwt_token';
    const CACHE_KEY   = 'profile_cache';
    const DEFAULT_AVATAR = '/images/default-avatar.png';

    const api = {
      examShow:     '/api/exam_show.php',
      examDownload: '/api/exam_download.php',   // (kept) if you still need server .doc
      examUpdate:   '/api/exam_update.php',     // NEW: to save edited HTML/title/desc
      me:           '/api/me.php',
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

    // ---- editor helpers for toolbar ----
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

    // ---- export helpers (DOCX/PDF) ----
    function getEditorHTML(){
      // clone to strip scripts/styles for export safety
      const node = $('paper').cloneNode(true);
      node.querySelectorAll('script,style').forEach(el => el.remove());
      return node.innerHTML;
    }
    function stripUnsupportedFonts(html){
      return html.replace(/font-family\s*:\s*[^;"]+;?/gi, '');
    }
    function downloadDOCX(title){
      const titleText = (title || 'Exam Paper').toUpperCase();
      const fname = (title || 'exam_paper').trim();
      const bodyHtml = DOMPurify.sanitize(getEditorHTML(), { ADD_ATTR: ['style'] });
      const html = `
      <!DOCTYPE html><html><head><meta charset="utf-8">
      <style>body{font-family:Arial,sans-serif;font-size:14pt;line-height:1.6}</style>
      </head><body>
        <div style="text-align:center;font-weight:bold;font-size:16pt;margin:0 0 12pt">${titleText}</div>
        ${bodyHtml}
      </body></html>`;
      const blob = window.htmlDocx.asBlob(html);
      saveAs(blob, fname.replace(/[^\w\-\. ]+/g,'_') + ".docx");
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

    // ---- state + load ----
    const examId = (() => {
      const m = location.pathname.match(/\/exam\/(\d+)(?:\/)?$/i);
      if (m && m[1]) return m[1];
      const qs = new URLSearchParams(location.search);
      return qs.get('id');
    })();
    if (!examId) { location.replace('/my-exams'); }

    (function fillSidebarQuick(){
      // quick paint name + avatar from cache; optional refresh omitted for brevity
      const cache = (()=>{ try{return JSON.parse(localStorage.getItem(CACHE_KEY)||'{}')}catch{return{}}})();
      $('displayName') && ($('displayName').textContent = cache.name || cache.username || cache.email || 'User');
      const img = $('profilePic');
      if (img) img.src = cache.profile_picture || '/images/default-avatar.png';
    })();

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
    loadExam();

    // ---- toolbar wiring ----
    document.querySelectorAll('[data-cmd]').forEach(btn=>{
      btn.addEventListener('click', () => exec(btn.dataset.cmd));
    });
    document.querySelectorAll('[data-align]').forEach(btn=>{
      btn.addEventListener('click', () => applyAlign(btn.dataset.align));
    });
    $('fontSizeSel').addEventListener('change', e => applyFontSize(e.target.value));

    // dirty flag
    $('paper').addEventListener('input', ()=> $('saveNote').textContent = 'Unsaved changes…');
    $('examTitle').addEventListener('input', ()=> $('saveNote').textContent = 'Unsaved changes…');
    $('examDesc').addEventListener('input', ()=> $('saveNote').textContent = 'Unsaved changes…');

    // ---- Save changes -> api/exam_update.php ----
    async function saveEditedExam(){
      const title = $('examTitle').innerText.trim();
      const description = $('examDesc').innerText.trim();
      // sanitize with styles allowed so we keep inline formatting
      const cleanHtml = DOMPurify.sanitize($('paper').innerHTML, { ADD_ATTR: ['style'] });

      const fd = new FormData();
        fd.append('id', loadedExam.id);
        if (title) fd.append('title', title);
        fd.append('description', description);
        fd.append('body_html', cleanHtml);
        
        // send token in the body as a fallback for PHP environments that strip Authorization
        fd.append('token', RAW_JWT);
        
        const res = await fetch('/api/exam_update.php', {
          method: 'POST',
          // keep Authorization header AND body token for maximum compatibility
          headers: bearerHeaders(),
          body: fd
        });

      const data = await res.json().catch(()=>({status:'error',message:'Invalid server response'}));
      if (data.status !== 'success') throw new Error(data.message || 'Save failed');
      $('updatedAt').textContent = fmtDate(data.updated_at || new Date().toISOString());
      $('saveNote').textContent = 'Saved';
      loadedExam.title = title || loadedExam.title;
    }

    $('btnSave').addEventListener('click', async ()=>{
      const btn = $('btnSave');
      btn.disabled = true; btn.classList.add('opacity-60','pointer-events-none');
      try {
        await saveEditedExam();
        alert('Exam saved successfully.');
      } catch(e){
        console.error(e);
        alert(e.message || 'Save failed.');
      } finally {
        btn.disabled = false; btn.classList.remove('opacity-60','pointer-events-none');
      }
    });

    // ---- Client-side exports from current editor content ----
    $('btnDocx').addEventListener('click', ()=>{
      downloadDOCX(($('examTitle').innerText || 'Exam Paper').trim());
    });
    $('btnPdf').addEventListener('click', ()=>{
      downloadPDF(($('examTitle').innerText || 'Exam Paper').trim());
    });

    // ---- logout ----
    $('logoutBtn')?.addEventListener('click', () => {
      localStorage.removeItem(TOKEN_KEY);
      localStorage.removeItem(CACHE_KEY);
      location.replace('/login');
    });
  </script>
</body>
</html>
