<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport"
        content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0"/>
  <title>Exam Miner 2.0 - Generate Exam</title>
  @vite('resources/css/app.css')
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"/>

  <!-- libs (order matters) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/html-to-pdfmake@2.4.5/browser.js"></script>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.4/purify.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/html-docx-js/dist/html-docx.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/mammoth@1.6.0/mammoth.browser.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js"></script>

  <!-- pdf.js (for reading PDFs only) -->
  <script src="https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.min.js"></script>
  <script>
    pdfjsLib.GlobalWorkerOptions.workerSrc =
      "https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.worker.min.js";
  </script>

  <style>
    @keyframes gradientShift { 0%{background-position:0% 50%} 50%{background-position:100% 50%} 100%{background-position:0% 50%} }
    .gradient-animated { background: linear-gradient(-45deg,#1e3a8a,#3b82f6,#60a5fa,#93c5fd,#1e40af,#1d4ed8); background-size:400% 400%; animation: gradientShift 15s ease infinite; }
    :root { --editor-width: 794px; }
    .editor-wrap { margin: 16px auto; max-width: var(--editor-width); }
    #output {
      font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Arial;
      font-size: 14pt; line-height: 1.6; white-space: normal; word-wrap: break-word;
      color: #111827;
      min-height: 900px; padding: 72px 64px; background: #fff;
      border: 1px solid #e5e7eb; box-shadow: 0 0 6px rgba(0,0,0,.08);
      outline: none;
    }
    /* ensure keys are fully isolated */
    .page-break { page-break-after: always; break-after: page; }

    /* Loader progress */
    .progress-wrap { margin-top:8px; height:10px; border-radius:999px; background:#eef2ff; overflow:hidden; }
    .progress-bar  { height:100%; width:0%; background: linear-gradient(90deg,#3b82f6,#60a5fa); transition:width .25s ease; }
    .spinner { animation: spin 1s linear infinite; }
    @keyframes spin { from{ transform:rotate(0deg);} to{ transform:rotate(360deg);} }
  </style>
</head>
<body class="min-h-screen">
  <!-- bg -->
  <div class="absolute inset-0 gradient-animated"></div>

  <script>
    // client-side gate
    const jwt = (localStorage.getItem('jwt_token') || '').replace(/^Bearer\s+/i,'').replace(/^"|"$/g,'');
    if (!jwt) location.replace('/login');
  </script>

  <div class="flex relative z-10">
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
        <a href="/generate-exam" class="flex items-center px-4 py-3 text-white bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg mb-2 transform hover:scale-105 transition-all duration-200">
          <i class="fas fa-plus mr-3"></i>
          Generate Exam
        </a>
        <a href="/my-exams" class="flex items-center px-4 py-3 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition-all duration-200 mb-2 group">
          <i class="fas fa-file-alt mr-3 group-hover:scale-110 transition-transform duration-200"></i>
          My Exams
        </a>
      </nav>

      <!-- Profile -->
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
    <main class="flex-1 p-8">
      <!-- Page header -->
      <div class="mb-8">
        <div class="flex items-center mb-4">
          <a href="/dashboard" class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center mr-4 shadow-xl hover:shadow-2xl hover:scale-105 transition-all duration-200 cursor-pointer">
            <i class="fas fa-arrow-left text-blue-500 text-2xl"></i>
          </a>
          <div>
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Generate Exam</h1>
            <p class="text-gray-600">Upload learning materials, pick a TOS, include optional figures, and let AI draft your exam.</p>
          </div>
        </div>
      </div>

      <!-- Alerts -->
      <div id="alertBox" style="background-color: #fed2d2;" class="hidden fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg bg-gray-800 text-white"></div>

      <!-- Section A: Upload & Meta -->
      <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden mb-6">
        <div class="p-6 border-b border-gray-100">
          <h2 class="text-xl font-bold text-gray-900">Section A: Upload & Meta</h2>
        </div>
        <div class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Upload -->
          <div class="lg:col-span-1">
            <label class="block text-sm font-medium text-gray-700 mb-2">Upload File (PDF, DOCX, PPTX)</label>
            <div id="dropZone" class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition-colors duration-200">
              <div class="flex flex-col items-center">
                <i class="fas fa-cloud-upload-alt text-gray-400 text-3xl mb-3"></i>
                <p class="text-gray-700 mb-2">Drag & drop a file here, or click</p>
                <input id="material" type="file" class="hidden" accept=".pdf,.docx,.pptx,.xlsx,.csv,.txt,.html,.htm" />
                <button type="button" onclick="document.getElementById('material').click()" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors duration-200">Choose File</button>
                <button type="button" id="scanFigsBtn" class="mt-2 bg-white text-gray-700 px-3 py-2 rounded-lg border border-gray-300 hover:bg-gray-100">Scan Figures</button>

              </div>
            </div>
            <div id="fileInfo" class="hidden mt-3 bg-green-50 border border-green-200 rounded-lg p-3 text-green-700">
              <i class="fas fa-check mr-2"></i><span id="fileName"></span>
            </div>
          </div>

          <!-- Meta -->
          <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
              <input id="subject" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., Computer Networks"/>
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Topic/Title (used as Exam Title)</label>
              <input id="topic" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., OSI Model"/>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Grade Level</label>
              <input id="grade" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., Grade 12"/>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Model <span style="color: #FF4D00; font-style: italic;">(Note: currently gemini are only working due to api limitations)</span></label>
              <select id="model" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="gemini/gemini-aistudio:free">Gemini-aistudio(free, faster, limited)</option>
                <option value="deepseek/deepseek-r1:free">DeepSeek R1(free, moderate, limited)</option>
                <option value="deepseek/deepseek-chat-v3-0324:free">DeepSeek V3(free)</option>
                <option value="mistralai/mistral-small-3.1-24b-instruct:free">Mistral 3.1(free)</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- NEW: Section A.1 — Detected Figures (optional) -->
      <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden mb-6">
        <div class="p-6 border-b border-gray-100">
          <h2 class="text-xl font-bold text-gray-900">Section A.1: Detected Figures (optional)</h2>
          <p class="text-sm text-gray-600 mt-1">Pick images to include. Add a caption to help the AI write “Refer to Figure X” items.</p>
        </div>
        <div class="p-6">
          <div id="figurePanel" class="hidden">
            <div id="figureGrid" class="grid gap-3" style="grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));"></div>
            <div class="mt-3 flex flex-wrap items-center gap-4">
            </div>
          </div>
          <div id="noFiguresHint" class="text-sm text-gray-500 hidden">No figures detected. That’s fine — your exam will generate without images.</div>
        </div>
      </div>

      <!-- Section B: Exam Type -->
      <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden mb-6">
        <div class="p-6 border-b border-gray-100">
          <h2 class="text-xl font-bold text-gray-900">Section B: Exam Type</h2>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="md:col-span-2">
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
              <label class="flex items-center gap-2"><input type="checkbox" value="Multiple Choice" class="format"> Multiple Choice</label>
              <label class="flex items-center gap-2"><input type="checkbox" value="True or False" class="format"> True/False</label>
              <label class="flex items-center gap-2"><input type="checkbox" value="Identification" class="format"> Identification</label>
              <label class="flex items-center gap-2"><input type="checkbox" value="Matching Type" class="format"> Matching Type</label>
              <label class="flex items-center gap-2"><input type="checkbox" value="Essay" class="format"> Essay</label>
            </div>
          </div>

          <!-- Sets + Difficulty -->
          <div class="space-y-3">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1"># of Questions</label>
              <input id="questionCount" type="number" min="1" max="100" value="20" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Number of Sets</label>
              <select id="numSets" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="1" selected>1 Set</option>
                <option value="2">2 Sets</option>
                <option value="3">3 Sets</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Difficulty</label>
              <select id="difficulty" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="medium" selected>Medium</option>
                <option value="easy">Easy</option>
                <option value="hard">Hard</option>
              </select>
            </div>

            <div class="mt-2 flex items-center gap-4 text-sm">
              <label class="flex items-center"><input id="shuffle" type="checkbox"><span style="margin-left:6px">Shuffle</span></label>
              <label class="flex items-center ml-2"><input id="answerKey" type="checkbox"><span style="margin-left:6px">Include Answer Key</span></label>
            </div>
          </div>
        </div>
      </div>

      <!-- Section C: TOS -->
      <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden mb-6">
        <div class="p-6 border-b border-gray-100">
          <h2 class="text-xl font-bold text-gray-900">Section C: Table of Specifications</h2>
          <p class="text-sm text-gray-600 mt-1">Each row must sum to 100% (RU + AA + HOTS). Topic weights should total ~100%.</p>
        </div>
        <div class="p-6">
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm border rounded-lg overflow-hidden">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-3 py-2 text-left">Content Area / Topic</th>
                  <th class="px-3 py-2 text-left w-40">Weight % (Topic)</th>
                  <th class="px-3 py-2 text-left w-40">Remember/Understand %</th>
                  <th class="px-3 py-2 text-left w-40">Apply/Analyze %</th>
                  <th class="px-3 py-2 text-left w-40">HOTS %</th>
                  <th class="px-3 py-2 text-left w-28">Row Total</th>
                  <th class="px-3 py-2 w-12"></th>
                </tr>
              </thead>
              <tbody id="tosBody" class="divide-y">
                <tr>
                  <td class="px-3 py-2"><input class="w-full border rounded px-2 py-1" placeholder="e.g., Chapter 1: Networking Basics" /></td>
                  <td class="px-3 py-2"><input type="number" min="0" max="100" step="1" value="25" class="w-full border rounded px-2 py-1" /></td>
                  <td class="px-3 py-2"><input type="number" min="0" max="100" step="1" value="40" class="w-full border rounded px-2 py-1" /></td>
                  <td class="px-3 py-2"><input type="number" min="0" max="100" step="1" value="40" class="w-full border rounded px-2 py-1" /></td>
                  <td class="px-3 py-2"><input type="number" min="0" max="100" step="1" value="20" class="w-full border rounded px-2 py-1" /></td>
                  <td class="px-3 py-2 rowTotal">100%</td>
                  <td class="px-3 py-2"><button type="button" onclick="removeTosRow(this)" class="text-red-600 hover:text-red-700"><i class="fas fa-times"></i></button></td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="mt-3 flex items-center gap-3">
            <button type="button" onclick="addTosRow()" class="px-3 py-2 rounded border bg-gray-50 hover:bg-gray-100">Add Topic</button>
            <button type="button" onclick="validateTOS()" class="px-3 py-2 rounded border bg-gray-50 hover:bg-gray-100">Validate TOS</button>
            <span id="tosStatus" class="text-sm text-gray-600"></span>
          </div>
        </div>
      </div>

      <!-- Section D header -->
      <div class="p-6 border-b border-gray-100 flex flex-wrap items-center gap-3 justify-between">
        <h2 class="text-xl font-bold text-gray-900">Section D: Generate & Edit</h2>
        <div class="flex items-center gap-3">
          <button id="btnSaveDbTop" class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-6 py-2 rounded-lg hover:from-blue-600 hover:to-blue-700 shadow mr-3 hidden" disabled title="Nothing to save yet">
            <i class="fas fa-save mr-2"></i>Save Exam
          </button>

          <button id="btnGenerate"
                  class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-6 py-2 rounded-lg hover:from-blue-600 hover:to-blue-700 shadow">
            <i class="fas fa-wand-magic-sparkles mr-2"></i> Generate Exam
          </button>
        </div>
      </div>

      <!-- Loader + progress -->
      <div id="loaderBox" class="p-6 hidden">
        <div class="flex items-start gap-3 text-gray-700">
          <i class="fas fa-circle-notch spinner mt-1"></i>
          <div class="flex-1">
            <div id="loaderMain" class="font-medium">Preparing…</div>
            <div id="loaderSub" class="text-sm text-gray-500 mt-1">Please wait while we process your file.</div>
            <div class="progress-wrap">
              <div id="progressBar" class="progress-bar"></div>
            </div>
            <div id="progressText" class="text-xs text-gray-500 mt-1">0%</div>
          </div>
        </div>
      </div>

      <!-- toolbar + editor -->
      <div id="editorBox" class="p-0 hidden">
        <div class="px-6 pt-6 pb-2 border-t border-gray-100">
          <div class="flex flex-wrap items-center gap-2">
            <button onclick="execCmd('bold')" class="px-2 py-1 border rounded hover:bg-gray-50" title="Bold"><i class="fas fa-bold"></i></button>
            <button onclick="execCmd('italic')" class="px-2 py-1 border rounded hover:bg-gray-50" title="Italic"><i class="fas fa-italic"></i></button>
            <button onclick="execCmd('underline')" class="px-2 py-1 border rounded hover:bg-gray-50" title="Underline"><i class="fas fa-underline"></i></button>

            <select onchange="changeFontSize(this.value)" class="px-2 py-1 border rounded">
              <option value="">Font Size</option>
              <option value="12px">12</option><option value="14px">14</option><option value="16px">16</option>
              <option value="18px">18</option><option value="20px">20</option><option value="24px">24</option>
              <option value="32px">32</option><option value="36px">36</option>
            </select>

            <button onclick="execCmd('insertUnorderedList')" class="px-2 py-1 border rounded hover:bg-gray-50" title="Bulleted"><i class="fas fa-list-ul"></i></button>
            <button onclick="execCmd('insertOrderedList')" class="px-2 py-1 border rounded hover:bg-gray-50" title="Numbered"><i class="fas fa-list-ol"></i></button>

            <button onclick="toggleHeading('H1')" class="px-2 py-1 border rounded hover:bg-gray-50">H1</button>
            <button onclick="toggleHeading('H2')" class="px-2 py-1 border rounded hover:bg-gray-50">H2</button>
            <button onclick="toggleHeading('H3')" class="px-2 py-1 border rounded hover:bg-gray-50">H3</button>

            <button onclick="execCmd('justifyLeft')" class="px-2 py-1 border rounded hover:bg-gray-50" title="Left"><i class="fas fa-align-left"></i></button>
            <button onclick="execCmd('justifyCenter')" class="px-2 py-1 border rounded hover:bg-gray-50" title="Center"><i class="fas fa-align-center"></i></button>
            <button onclick="execCmd('justifyRight')" class="px-2 py-1 border rounded hover:bg-gray-50" title="Right"><i class="fas fa-align-right"></i></button>

            <button onclick="copyEditor()" class="px-2 py-1 border rounded hover:bg-gray-50" title="Copy"><i class="fas fa-copy"></i></button>

            <div class="ml-auto flex items-center gap-2">
              <button onclick="downloadPDF()" class="px-3 py-2 border rounded hover:bg-gray-50"><i class="fas fa-file-pdf mr-2"></i>PDF</button>
              <button onclick="downloadDOCX()" class="px-3 py-2 border rounded hover:bg-gray-50"><i class="fas fa-file-word mr-2"></i>DOCX</button>
            </div>
          </div>
        </div>

        <div class="editor-wrap">
          <div id="output" contenteditable="true" class="rounded-b-xl"></div>
        </div>

        <div class="px-6 pb-6">
          <div id="allocNote" class="text-sm text-gray-500"></div>
        </div>
      </div>
    </main>
  </div>
  
  <!--   VERSION 5.0 -->
<script>
(() => {
/* ============================ token / cache / avatar ============================ */
const TOKEN_KEY='jwt_token', CACHE_KEY='profile_cache', DEFAULT_AVATAR='/images/default-avatar.png';
const jwt = (localStorage.getItem(TOKEN_KEY)||'').replace(/^Bearer\s+/i,'').replace(/^"|"$/g,'');
if(!jwt){ location.replace('/login'); return; }

const $ = id => document.getElementById(id);

// Only these formats may contain [[FIG:n]] images
// ['Multiple Choice','Identification','True or False','Essay','Matching Type']
const FIG_ALLOWED = new Set(['Multiple Choice', 'True or False']);

// Helper: safely convert anything to string
function s(v){ 
  return v == null ? "" : String(v); 
}

function parseJwt(t){
  try{
    const [,p]=t.split('.');
    if(!p) return {};
    const b=p.replace(/-/g,'+').replace(/_/g,'/');
    const j=decodeURIComponent(atob(b).split('').map(c=>'%'+('00'+c.charCodeAt(0).toString(16)).slice(-2)).join(''));
    return JSON.parse(j);
  }catch{ return {}; }
}
function readCache(){ try{ return JSON.parse(localStorage.getItem(CACHE_KEY)||'{}'); }catch{ return {}; } }

function approxBase64Bytes(b64=''){
  const s = String(b64).split(',').pop() || '';
  const len = s.length - (s.endsWith('==') ? 2 : s.endsWith('=') ? 1 : 0);
  return Math.floor(len * 3 / 4);
}
function isMaybeBase64(s){ return /^[A-Za-z0-9+/=\s]+$/.test(String(s||'')); }
function sanitizeBase64(s){ return String(s||'').replace(/\s+/g,''); }

function resolveAvatar(pic){
  if(!pic) return DEFAULT_AVATAR;
  const p = String(pic).trim();
  if (p.startsWith('data:image/')) {
    if (approxBase64Bytes(p) > 200*1024) return DEFAULT_AVATAR;
    return p;
  }
  if (p.startsWith('http://')||p.startsWith('https://')||p.startsWith('/')) return p;
  if (isMaybeBase64(p)) {
    const data = 'data:image/jpeg;base64,'+sanitizeBase64(p);
    if (approxBase64Bytes(data) > 200*1024) return DEFAULT_AVATAR;
    return data;
  }
  return '/images/'+p;
}

function acceptHeaders(extra={}){ return { 'Accept':'application/json', ...extra }; }

/* ============================ floating alerts (toast) ============================ */
function showAlert(type, text) {
  const box = $('alertBox');
  if (!box) return;
  box.className =
    'fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-opacity duration-500 opacity-100 ' +
    (type === 'ok'
      ? 'bg-green-50 border border-green-200 text-green-700'
      : 'bg-red-50 border border-red-200 text-red-700');
  box.textContent = text;
  box.classList.remove('hidden');
  box.style.opacity = '1';
  clearTimeout(box._t);
  box._t = setTimeout(() => {
    box.style.opacity = '0';
    setTimeout(() => box.classList.add('hidden'), 500);
  }, 4000);
}

const payload = parseJwt(jwt);
if(payload.exp && Date.now() >= payload.exp*1000){
  localStorage.removeItem(TOKEN_KEY); localStorage.removeItem(CACHE_KEY); location.replace('/login'); return;
}

document.addEventListener('DOMContentLoaded', () => {
  const u = payload.user || payload.data || payload; const cache=readCache();
  const name = u.name || u.username || u.email || cache.name || 'User';
  const pic  = u.profile_picture || u.picture || u.avatar || cache.profile_picture;
  $('displayName').textContent = name;
  const img = $('profilePic'); if(img){ img.src = resolveAvatar(pic); img.onerror = () => { img.src = DEFAULT_AVATAR; }; }

  const dropZone=$('dropZone'), file=$('material'), info=$('fileInfo'), fileName=$('fileName');
  function showFileInfo(f){ if(fileName && info){ fileName.textContent=`Selected: ${f.name} (${(f.size/1024/1024).toFixed(2)} MB)`; info.classList.remove('hidden'); } }
  dropZone?.addEventListener('dragover',(e)=>{ e.preventDefault(); dropZone.classList.add('border-blue-400','bg-blue-50'); });
  dropZone?.addEventListener('dragleave',(e)=>{ e.preventDefault(); dropZone.classList.remove('border-blue-400','bg-blue-50'); });
  dropZone?.addEventListener('drop',(e)=>{ e.preventDefault(); dropZone.classList.remove('border-blue-400','bg-blue-50'); if(e.dataTransfer.files.length){ file.files=e.dataTransfer.files; showFileInfo(file.files[0]); }});
  file?.addEventListener('change', async (e) => {
    if (!e.target.files.length) return;
    const f = e.target.files[0];
    if (typeof showFileInfo === 'function') showFileInfo(f);
    try {
      $('noFiguresHint')?.classList.add('hidden');
      const figs = await extractFigures(f);
      window.__allFigures = figs;
      window.__selectedFigures = [];
      renderFigureGallery(figs);
      if (!figs.length) $('noFiguresHint')?.classList.remove('hidden');
    } catch (err) {
      console.warn('figure scan failed:', err);
      $('noFiguresHint')?.classList.remove('hidden');
    }
  });

  $('logoutBtn')?.addEventListener('click', ()=>{ localStorage.removeItem(TOKEN_KEY); localStorage.removeItem(CACHE_KEY); location.replace('/login'); });
  $('btnGenerate')?.addEventListener('click', generateExamFlow);
  $('btnSaveDbTop')?.addEventListener('click', saveExamToDB);

  $('output')?.addEventListener('input', () => setSaveEnabled(hasEditorContent()));
  setSaveEnabled(false);
  $('btnSaveDbTop')?.classList.add('hidden');

  const qc = $('questionCount');
  qc?.addEventListener('input', () => {
    let v = Number(qc.value || 0);
    if (Number.isNaN(v)) v = 1;
    if (v > 100) { qc.value = 100; showAlert('err', 'Maximum is 100 questions.'); }
    else if (v < 1) { qc.value = 1; }
  });
});

$('scanFigsBtn')?.addEventListener('click', async () => {
  const f = $('material')?.files?.[0];
  if (!f) return showAlert('err','Pick a file first.');
  try {
    const figs = await extractFigures(f);
    window.__allFigures = figs;
    window.__selectedFigures = [];
    renderFigureGallery(figs);
    if (!figs.length) showAlert('ok','No figures detected. That’s okay — you can still generate the exam.');
  } catch (e) {
    showAlert('err','Could not scan figures from this file.');
  }
});

/* ================== GLOBAL RATE LIMITER (DRIP MODE) ================== */
const LIMITS = { rpm: 1, tpm: 60000, windowMs: 60000 };
function approxTokens(str){ const n=(str||'').length; return Math.max(1, Math.ceil(n/4)); }

class GlobalScheduler {
  constructor(){
    this.q = [];
    this.state = { windowStart: Date.now(), reqs: 0, toks: 0, cooldownUntil: 0 };
    this.timer = setInterval(()=>this._tick(), 250);
  }
  _resetWindow(){
    const now=Date.now();
    if (now - this.state.windowStart >= LIMITS.windowMs){
      this.state.windowStart = now;
      this.state.reqs = 0; this.state.toks = 0;
    }
  }
  _tick(){
    this._resetWindow();
    const s=this.state;
    const now=Date.now();
    if (now < s.cooldownUntil) return;
    if (!this.q.length) return;

    const job=this.q[0];
    const nextReqs=s.reqs+1, nextToks=s.toks+job.tokenCost;
    if (nextReqs > LIMITS.rpm || nextToks > LIMITS.tpm) return;
    this.q.shift();
    s.reqs = nextReqs;
    s.toks = nextToks;
    job.run();
  }
  backoff(ms){
    const until = Date.now() + Math.max(2000, ms|0);
    this.state.cooldownUntil = Math.max(this.state.cooldownUntil, until);
  }
  enqueue(tokenCost, fn){
    return new Promise((resolve, reject) => {
      const job = {
        tokenCost: Math.max(1, tokenCost|0),
        run: async () => {
          try{
            await new Promise(r=>setTimeout(r, 200));
            const out = await fn();
            resolve(out);
          }catch(e){ reject(e); }
        }
      };
      this.q.push(job);
    });
  }
}
const globalScheduler = new GlobalScheduler();

async function scheduledJsonFetch({url, init, promptTextForToks, maxRetries=12}){
  const tokenCost = approxTokens(promptTextForToks || '') + approxTokens(init?.body || '');
  let attempt = 0;

  return globalScheduler.enqueue(tokenCost, async () => {
    while (true){
      attempt++;

      let res, text;
      try{
        res = await fetch(url, init);
        text = await res.text();
      }catch(netErr){
        if (attempt >= maxRetries) throw netErr;
        const base = 1500 * Math.pow(2, attempt - 1);
        const jitter = base * (0.7 + Math.random()*0.6);
        await new Promise(r => setTimeout(r, jitter));
        continue;
      }

      let data=null;
      try{ data=JSON.parse(text); }catch{}

      const okWithContent = (res.ok && data && !data.error && (data.choices?.[0]?.message?.content ?? '') !== '');
      if (okWithContent) return data;

      const status = res.status;
      const serverMsg = data?.error?.message || data?.message || text || `HTTP ${status}`;
      const ra = res.headers.get('retry-after');

      let retryMs = 0;
      if (ra){
        const secs = Number(ra);
        if (!Number.isNaN(secs)) retryMs = secs * 1000;
       else {
          const when = Date.parse(ra);
          if (!Number.isNaN(when)) retryMs = Math.max(0, when - Date.now());
        }
      }

      const emptyChoices = res.ok && (!data?.choices || !data?.choices?.length || !(data.choices[0]?.message?.content||'').trim());
      const retriable = (status === 429 || status >= 500 || emptyChoices);
        /* Delete this after 0970
      if (!retriable || attempt >= maxRetries){
        throw new Error(: serverMsg || 'Request failed');
      }
      */
      if (!retriable || attempt >= maxRetries){
          throw new Error(
            status === 503
              ? 'Server is busy (503). Please try again in a minute.'
              : serverMsg || 'Request failed'
          );
        }

      if (status === 429){
        globalScheduler.backoff(Math.max(retryMs, 12000));
      }
      
      if (status === 503) {
         // treat like heavy overload
         globalScheduler.backoff(Math.max(retryMs, 15000));
      }

      const base = (status===429 ? 1800 : 1400) * Math.pow(2, attempt - 1);
      const jitter = base * (0.7 + Math.random()*0.6);
      await new Promise(r => setTimeout(r, Math.max(retryMs || 0, Math.min(60000, jitter))));
    }
  });
}

/* ================== decode / normalize / parse ================== */
function decodeEntities(str=''){
  const map = { '&amp;':'&', '&lt;':'<', '&gt;':'>', '&quot;':'"', '&#39;':"'", '&nbsp;':' ' };
  return String(str).replace(/(&amp;|&lt;|&gt;|&quot;|&#39;|&nbsp;)/g, m=>map[m]);
}
function stripModelNoise(s=''){ return s.replace(/```+/g,'').replace(/^\s*"""+/gm,'').replace(/^\s*---+\s*$/gm,''); }
function standardizeTerms(s=''){ return s.replace(/\badvance\b/gi, 'inventive step (non-obviousness)'); }

function isValidMatchingBlock(txt){
  const lines = txt.split(/\r?\n/).map(l=>l.trimEnd());
  const aIdx = lines.findIndex(l=>/^Column A$/i.test(l));
  const bIdx = lines.findIndex(l=>/^Column B$/i.test(l));
  if (aIdx < 0 || bIdx < 0 || bIdx <= aIdx) return false;

  const A = lines.slice(aIdx+1, bIdx).filter(Boolean);
  const B = lines.slice(bIdx+1).filter(Boolean);

  const aCount = A.filter(l=>/^\d+\.\s+/.test(l)).length;
  const bCount = B.filter(l=>/^[a-z]\.\s+/i.test(l)).length;

  return aCount >= 2 && aCount <= 10 && aCount === bCount;
}

function trimMatchingBlockPairs(blockText, keepPairs){
  if (keepPairs <= 0) return '';
  const lines = blockText.split(/\r?\n/);

  const aIdx = lines.findIndex(l => /^Column A$/i.test(l));
  const bIdx = lines.findIndex(l => /^Column B$/i.test(l));
  if (aIdx < 0 || bIdx < 0 || bIdx <= aIdx) return blockText;

  const beforeA = lines.slice(0, aIdx + 1);
  const betweenAB = lines.slice(aIdx + 1, bIdx);
  const afterB = lines.slice(bIdx + 1);

  const Aitems = betweenAB.filter(l => /^\d+\.\s+/.test(l)).slice(0, keepPairs);
  const Bitems = afterB.filter(l => /^[a-z]\.\s+/i.test(l)).slice(0, keepPairs);

  return [
    ...beforeA,
    ...Aitems,
    '',
    'Column B',
    ...Bitems
  ].join('\n').replace(/\n{3,}/g, '\n\n').trim();
}

function normalizeMatchingBlock(txt){
  let s = txt.replace(/\r/g,'');
  if (/^Column A$/i.test(s.trim().split('\n')[0]||'')) s = 'Instruction: Match the items.\n' + s;
  s = s.replace(/^\s*Instruction\s*:/i,'Instruction:');
  s = s.replace(/^\s*[abcde]\.\s*(\d\.)/gmi,'$1');
  s = s.replace(/\n\s*\n\s*Column B/i, '\n\nColumn B');
  s = s.replace(/^\s*([a-e])\s+([^.\n])/gmi,'$1. $2');
  return s.trim();
}
function normalizeTFStem(txt){ return txt.replace(/^\s*True\s*or\s*False:\s*/i,''); }
function stemHash(txt){
  return txt.toLowerCase().replace(/^[\s_]*\d+[\.)]\s*/,'').replace(/\s+/g,' ')
    .replace(/[^\p{L}\p{N}\s]/gu,'').trim();
}

/* ================== Matching JSON helpers ================== */
function createMatchingBlockFromJson(obj){
  const instr = (obj.instruction || 'Match the items.').trim();
  const A = Array.isArray(obj.left) ? obj.left.slice(0,5) : [];
  const B = Array.isArray(obj.right) ? obj.right.slice(0,5) : [];
  if (A.length < 5 || B.length < 5) return null;

  const lines = [];
  lines.push(`Instruction: ${instr}`);
  lines.push(`Column A`);
  for (let i=0;i<5;i++) lines.push(`${i+1}. ${String(A[i]).trim()}`);
  lines.push('');
  lines.push(`Column B`);
  lines.push('');
  const letters = ['a','b','c','d','e'];
  for (let i=0;i<5;i++) lines.push(`${letters[i]}. ${String(B[i]).trim()}`);
  return lines.join('\n');
}
function _safeJsonParse(s){ try{ return JSON.parse(s); }catch{ return null; } }
function extractFirstJson(text){
  const direct = _safeJsonParse(String(text).trim());
  if (direct) return direct;
  const m = String(text).match(/(\{[\s\S]*\}|\[[\s\S]*\])/);
  if (m) return _safeJsonParse(m[1]);
  return null;
}

/* ================== editor helpers / exports ================== */
window.execCmd = (cmd,val=null)=> document.execCommand(cmd,false,val);
window.copyEditor = ()=>{
  const el=$('output'); if(!el) return;
  const sel=window.getSelection(); const r=document.createRange();
  r.selectNodeContents(el); sel.removeAllRanges(); sel.addRange(r); document.execCommand('copy'); sel.removeAllRanges();
  showAlert('ok','Copied to clipboard');
};
window.toggleHeading = (tag)=>{
  const sel=window.getSelection(); if(!sel.rangeCount) return;
  const range=sel.getRangeAt(0); let node=range.startContainer; if(node.nodeType===3) node=node.parentNode;
  while(node && node!==document && !/^H[1-6]$/.test(node.tagName)) node=node.parentNode;
  if(node && node.tagName===tag){
    const span=document.createElement('span'); span.innerHTML=node.innerHTML; node.parentNode.replaceChild(span,node);
    const r=document.createRange(); r.selectNodeContents(span); r.collapse(true); sel.removeAllRanges(); sel.addRange(r);
  } else { document.execCommand('formatBlock',false,tag); }
};
window.changeFontSize = (size)=>{
  if(!size) return;
  const sel = window.getSelection(); if(!sel.rangeCount) return;
  const span=document.createElement('span'); span.style.fontSize=size; span.textContent=sel.toString();
  const range=sel.getRangeAt(0); range.deleteContents(); range.insertNode(span);
};

function getEditorHTML(){ return ($('output')||{}).innerHTML || ''; }
window.downloadDOCX = ()=>{
  const t = ( $('topic').value || 'Exam Paper').toUpperCase();
  const fname = $('topic').value || 'exam_paper';
  const html = `<!DOCTYPE html><html><head><meta charset="utf-8"><style>body{font-family:Arial,sans-serif;font-size:14pt;line-height:1.6}</style></head><body><div style="text-align:center;font-weight:bold;font-size:16pt;margin:0 0 12pt">${t}</div>${getEditorHTML()}</body></html>`;
  const blob = window.htmlDocx.asBlob(html); saveAs(blob, fname + ".docx");
};
function stripUnsupportedFonts(html){ return html.replace(/font-family\s*:\s*[^;"]+;?/gi,''); }
window.downloadPDF = ()=>{
  const title = ( $('topic').value || 'Exam Paper').toUpperCase();
  const node = $('output').cloneNode(true); node.querySelectorAll('script,style').forEach(el=>el.remove());
  let html = DOMPurify.sanitize(node.innerHTML, { ADD_ATTR:["style","src","alt"] }); html = stripUnsupportedFonts(html);
  const content = window.htmlToPdfmake(html, { window });
  const docDefinition = { info:{title}, pageSize:'A4', pageMargins:[40,60,40,60],
    content:[ {text:title, style:'header', alignment:'center', margin:[0,0,0,12]}, ...content ],
    styles:{ header:{fontSize:16,bold:true} }, defaultStyle:{ font:'Roboto', fontSize:12, lineHeight:1.4 } };
  pdfMake.createPdf(docDefinition).download(title + ".pdf");
};

/* ================== TOS helpers ================== */
window.addTosRow = ()=>{
  const tr=document.createElement('tr'); tr.innerHTML = `
    <td class="px-3 py-2"><input class="w-full border rounded px-2 py-1" placeholder="Topic name" /></td>
    <td class="px-3 py-2"><input type="number" min="0" max="100" step="1" value="25" class="w-full border rounded px-2 py-1" /></td>
    <td class="px-3 py-2"><input type="number" min="0" max="100" step="1" value="40" class="w-full border rounded px-2 py-1" /></td>
    <td class="px-3 py-2"><input type="number" min="0" max="100" step="1" value="40" class="w-full border rounded px-2 py-1" /></td>
    <td class="px-3 py-2"><input type="number" min="0" max="100" step="1" value="20" class="w-full border rounded px-2 py-1" /></td>
    <td class="px-3 py-2 rowTotal">100%</td>
    <td class="px-3 py-2"><button type="button" onclick="removeTosRow(this)" class="text-red-600 hover:text-red-700"><i class="fas fa-times"></i></button></td>`;
  $('tosBody').appendChild(tr);
};
window.removeTosRow = (btn)=> btn.closest('tr').remove();

function readTOS(){
  const rows = Array.from(document.querySelectorAll('#tosBody tr'));
  return rows.map(r=>{
    const [topicEl, wEl, ruEl, aaEl, hotEl] = r.querySelectorAll('input');
    return { topic:(topicEl.value||'').trim(), topicWeight:Number(wEl.value||0), RU:Number(ruEl.value||0), AA:Number(aaEl.value||0), HOTS:Number(hotEl.value||0), rowEl:r };
  }).filter(r=>r.topic);
}
const tosBodyEl = document.getElementById('tosBody');
tosBodyEl && tosBodyEl.addEventListener('input',()=>validateTOS());
window.validateTOS = ()=>{
  const tos = readTOS(); const status=$('tosStatus'); if(!tos.length){ status.textContent='Add at least one topic.'; status.style.color='crimson'; return false; }
  const weightSum = tos.reduce((s,t)=>s+t.topicWeight,0); let ok=true;
  tos.forEach(t=>{ const rowSum=t.RU+t.AA+t.HOTS; const cell=t.rowEl.querySelector('.rowTotal'); cell.textContent=rowSum+'%'; cell.style.color=(rowSum===100)?'#0a0':'crimson'; if(rowSum!==100) ok=false; });
  const near100 = Math.abs(weightSum-100) <= 1; if(!near100) ok=false;
  if(ok){ status.textContent=`TOS looks good. Topic weights total ${weightSum}%.`; status.style.color='#0a0'; } else { status.textContent=`Fix TOS: Topic weights = ${weightSum}% (must be ~100), and each row must total 100%.`; status.style.color='crimson'; }
  return ok;
};

function apportion(total, percents){
  const raw=percents.map(p=>(p/100)*total);
  const floors=raw.map(Math.floor);
  let used=floors.reduce((s,x)=>s+x,0);
  const rema=raw.map((x,i)=>({i, r:x-Math.floor(x)})).sort((a,b)=>b.r-a.r);
  const out=floors.slice(); let k=0;
  while(used<total && k<rema.length){ out[rema[k].i]++; used++; k++; }
  return out;
}

function allocateItemsByTOS(total){
  const tos=readTOS();
  const perTopic=apportion(total, tos.map(t=>t.topicWeight));
  return tos.map((t,idx)=>{
    const tt=perTopic[idx];
    const perLevel=apportion(tt, [t.RU,t.AA,t.HOTS]);
    return { topic:t.topic, total:tt, RU:perLevel[0], AA:perLevel[1], HOTS:perLevel[2] };
  });
}

function distributeAcrossFormats(total, formats, mustIncludeMatch=false){
  if(formats.length===1) return {[formats[0]]:total};
  const pct=Array(formats.length).fill(100/formats.length);
  const counts=apportion(total,pct);
  const out={}; formats.forEach((f,i)=>out[f]=counts[i]);

  if (!mustIncludeMatch || total <= 0 || !formats.includes('Matching Type')) return out;

  if ((out['Matching Type']|0) === 0){
    let donor=null, donorCount=-1;
    for (const f of formats){
      if (f==='Matching Type') continue;
      if ((out[f]||0) > donorCount){ donor=f; donorCount=(out[f]||0); }
    }
    if (donor && donorCount>0){ out[donor]-=1; out['Matching Type']=1; }
  }
  return out;
}

/* ================== Extraction (local text) ================== */
const readAsArrayBuffer = f => new Promise((res,rej)=>{ const r=new FileReader(); r.onload=()=>res(r.result); r.onerror=rej; r.readAsArrayBuffer(f); });
const readAsText = f => new Promise((res,rej)=>{ const r=new FileReader(); r.onload=()=>res(r.result); r.onerror=rej; r.readAsText(f); });

async function extractDOCX(file){ const ab=await readAsArrayBuffer(file); const {value}=await window.mammoth.extractRawText({arrayBuffer:ab}); return (value||"").trim(); }
async function extractXLSX_CSV(file,ext){
  let wb; if(ext==='csv'){ const text=await readAsText(file); wb=XLSX.read(text,{type:'string'}); }
  else { const ab=await readAsArrayBuffer(file); wb=XLSX.read(ab,{type:'array'}); }
  const sheet=wb.Sheets[wb.SheetNames[0]];
  const rows=XLSX.utils.sheet_to_json(sheet,{header:1,raw:true});
  return rows.map(r=>r.map(c=>c==null?"":String(c)).join("\t")).join("\n");
}
async function extractPPTX(file){
  const ab=await readAsArrayBuffer(file); const zip=await JSZip.loadAsync(ab);
  const slideFiles=Object.keys(zip.files).filter(p=>/^ppt\/slides\/slide\d+\.xml$/.test(p))
    .sort((a,b)=>parseInt(a.match(/slide(\d+)\.xml/)[1])-parseInt(b.match(/slide(\d+)\.xml/)[1]));
  let out=[];
  for(let i=0;i<slideFiles.length;i++){
    const xml=await zip.files[slideFiles[i]].async('text');
    const runs=Array.from(xml.matchAll(/<a:t>([\s\S]*?)<\/a:t>/g)).map(m=>m[1]);
    const text=runs.map(t=>t.replace(/&lt;|&gt;|&amp;/g,s=>({'&lt;':'<','&gt;':'>','&amp;':'&'}[s]))).map(s=>s.trim()).filter(Boolean);
    if(text.length) out.push(`Slide ${i+1}:\n- `+text.join("\n- "));
  }
  return out.join("\n\n") || "(No extractable text)";
}
async function extractPDF(file){
  const ab=await readAsArrayBuffer(file);
  const pdf=await pdfjsLib.getDocument({data:ab}).promise; let pages=[];
  for(let p=1;p<=pdf.numPages;p++){
    const page=await pdf.getPage(p); const content=await page.getTextContent();
    const strings=content.items.map(it=>(it.str||"").trim()).filter(Boolean);
    const text=strings.join(" ").replace(/\s{2,}/g," ").trim();
    if(text) pages.push(`Page ${p}:\n${text}`);
  }
  return pages.join("\n\n") || "(No extractable text)";
}
async function extractHTML(file){ const html=await readAsText(file); const doc=new DOMParser().parseFromString(html,"text/html"); return (doc.body?.innerText||"").trim(); }
async function extractLocally(file){
  const ext=(file.name.split('.').pop()||'').toLowerCase();
  if(ext==='docx') return extractDOCX(file);
  if(ext==='xlsx'||ext==='csv') return extractXLSX_CSV(file,ext);
  if(ext==='pptx') return extractPPTX(file);
  if(ext==='pdf') return extractPDF(file);
  if(ext==='html'||ext==='htm') return extractHTML(file);
  if(ext==='txt') return readAsText(file);
  throw new Error("Unsupported file type: ."+ext);
}

/* ================== FIGURES extraction + gallery ================== */
window.__allFigures = [];      // [{id, dataUrl, name, from, w, h}]
window.__selectedFigures = []; // subset used in rendering/prompts

async function blobToDataURL(blob){
  return await new Promise((res, rej) => {
    const r = new FileReader();
    r.onload = () => res(r.result);
    r.onerror = rej;
    r.readAsDataURL(blob);
  });
}

async function extractImagesFromDOCX(file){
  const ab = await (new Response(file)).arrayBuffer();
  const zip = await JSZip.loadAsync(ab);
  const mediaPaths = Object.keys(zip.files).filter(p => /^word\/media\//i.test(p));
  const out = []; let idx=0;
  for (const p of mediaPaths){
    const f = zip.files[p];
    const blob = await f.async('blob');
    const dataUrl = await blobToDataURL(blob);
    out.push({ id: `docx-${idx++}`, dataUrl, name: p.split('/').pop(), from: 'docx' });
  }
  return out;
}

async function extractImagesFromPPTX(file){
  const ab = await (new Response(file)).arrayBuffer();
  const zip = await JSZip.loadAsync(ab);
  const mediaPaths = Object.keys(zip.files).filter(p => /^ppt\/media\//i.test(p));
  const out = []; let idx=0;
  for (const p of mediaPaths){
    const f = zip.files[p];
    const blob = await f.async('blob');
    const dataUrl = await blobToDataURL(blob);
    out.push({ id:`pptx-${idx++}`, dataUrl, name: p.split('/').pop(), from:'pptx' });
  }
  return out;
}

// PDF snapshots (first 20)
async function extractPageSnapshotsFromPDF(file, maxPages=20, scale=0.9){
  const ab = await (new Response(file)).arrayBuffer();
  const pdf = await pdfjsLib.getDocument({ data: ab }).promise;
  const out = []; const pages = Math.min(pdf.numPages, maxPages);
  for (let p=1; p<=pages; p++){
    const page = await pdf.getPage(p);
    const viewport = page.getViewport({ scale });
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    canvas.width = viewport.width; canvas.height = viewport.height;
    await page.render({ canvasContext: ctx, viewport }).promise;
    const dataUrl = canvas.toDataURL('image/png');
    out.push({ id:`pdfpage-${p}`, dataUrl, name:`page-${p}.png`, from:'pdf', w:canvas.width, h:canvas.height });
  }
  return out;
}

async function extractFigures(file){
  const ext=(file.name.split('.').pop()||'').toLowerCase();
  try{
    if (ext==='docx')  return await extractImagesFromDOCX(file);
    if (ext==='pptx')  return await extractImagesFromPPTX(file);
    if (ext==='pdf')   return await extractPageSnapshotsFromPDF(file);
  }catch(e){ console.warn('figure extraction error:', e); }
  return [];
}

function renderFigureGallery(figs){
  const panel = $('figurePanel');
  const grid  = $('figureGrid');
  const none  = $('noFiguresHint');
  if (!panel || !grid) return;

  grid.innerHTML = '';
  if (!figs.length){
    panel.classList.add('hidden');
    none?.classList.remove('hidden');
    return;
  }
  none?.classList.add('hidden');
  panel.classList.remove('hidden');

  figs.forEach((f)=>{
    const card = document.createElement('div');
    card.className = "border rounded p-2 bg-white";
    card.innerHTML = `
      <label class="flex items-start gap-2">
        <input type="checkbox" class="mt-1 fig-check" data-id="${f.id}">
        <img src="${f.dataUrl}" alt="figure" class="block w-full h-28 object-contain border rounded" />
      </label>
      <input class="mt-2 w-full border rounded px-2 py-1 text-sm fig-caption" data-id="${f.id}" placeholder="Caption (optional)" />
      <div class="mt-1 text-[11px] text-gray-500">${f.name || ''}</div>
    `;
    grid.appendChild(card);
  });

  grid.onchange = (e)=>{
    if (!(e.target.classList.contains('fig-check') || e.target.classList.contains('fig-caption'))) return;
    const checks = grid.querySelectorAll('.fig-check');
    const caps   = grid.querySelectorAll('.fig-caption');
    const capMap = {}; caps.forEach(c => { capMap[c.dataset.id] = c.value.trim(); });
    window.__selectedFigures = Array.from(checks)
      .filter(c => c.checked)
      .map(c => {
        const id = c.dataset.id;
        const fig = figs.find(x=>x.id===id);
        return { ...fig, caption: capMap[id] || '' };
      });
  };
}

/* ================== [[FIG:n]] injection ================== */
function getFigByIndex(n){ const arr = window.__selectedFigures || []; return arr[n-1] || null; }
function renderFigTag(fig, label){
  if (!fig) return "";
 // const cap = fig.caption ? ` — ${String(fig.caption).replace(/</g,"&lt;")}` : "";
  /* return `
    <div class="my-2">
      <div style="font-weight:600;margin:2px 0">Figure ${label}${cap}</div>
      <img src="${fig.dataUrl}" alt="Figure ${label}" style="max-width:100%;height:auto;border:1px solid #ddd;border-radius:6px"/>
    </div>`;
    */
    return `<img src="${fig.dataUrl}" alt="Figure ${label}" style="max-height:120px;vertical-align:middle;margin: 0 6px;border:1px solid #ddd;border-radius:4px">`;
}
function sanitizeInline(html){
  if (window.DOMPurify) {
    return DOMPurify.sanitize(html, {ALLOWED_TAGS:['b','strong','i','em','u','sub','sup','span','br','div','img'], ADD_ATTR:['style','src','alt']});
  }
  return html;
}
function injectInlineFiguresIntoHtml(html){
  return sanitizeInline(String(html).replace(/\[\[FIG:(\d+)\]\]/g, (_, num) => {
    const idx = Number(num);
    const fig = getFigByIndex(idx);
    return renderFigTag(fig, idx) || '';
  }));
}
function stripFigTokens(text){
  return String(text).replace(/\s*\[\[FIG:\d+\]\]\s*/g,' ').replace(/\s{2,}/g,' ').trim();
}

/* ================== Describe images via PHP backend ================== */
function dataURLToBlob(dataUrl){
  const [meta,b64] = dataUrl.split(',');
  const mime = (meta.match(/data:(.*?);base64/)||[])[1] || 'image/png';
  const bin = atob(b64);
  const u8  = new Uint8Array(bin.length);
  for (let i=0;i<bin.length;i++) u8[i] = bin.charCodeAt(i);
  return new Blob([u8], { type: mime });
}
async function describeOneImageViaServer(fig, { model="gemini-2.5-flash" } = {}){
  const fd = new FormData();
  fd.append('image', dataURLToBlob(fig.dataUrl), fig.name || 'figure.png');
  fd.append('model', model);
  fd.append('prompt',
    'Return ONLY JSON with keys caption (<=18 words) and tags (array of 5 short keywords).');

  const res = await fetch('/api/v1/chat/describe.php', { method:'POST', body: fd });
  const data = await res.json().catch(()=> ({}));
  if (!res.ok || data?.ok === false) throw new Error(data?.error || 'Describe failed');

  let caption = fig.caption || fig.name || 'Figure', tags = [];
  try {
    const parsed = JSON.parse(String(data.text||'').trim());
    if (parsed && typeof parsed === 'object'){
      caption = (parsed.caption || caption).trim();
      tags = Array.isArray(parsed.tags) ? parsed.tags : [];
    }
  } catch {
    const txt = String(data.text||'').trim();
    caption = (txt.split('\n').map(s=>s.replace(/^[-•\s]+/,'').trim()).find(Boolean) || caption).slice(0,180);
  }
  return { caption, tags, _raw: data.text || '' };
}
async function describeSelectedImagesViaServer(selected, model="gemini-2.5-flash"){
  const out = [];
  for (const fig of selected){
    try{
      const { caption, tags, _raw } = await describeOneImageViaServer(fig, { model });
      out.push({ ...fig, caption, tags, _descRaw:_raw });
    }catch(err){
      console.warn('describe error:', err);
      out.push({ ...fig, caption: fig.caption || fig.name || 'Figure', tags: [], _descRaw: '' });
    }
  }
  return out;
}

/* ================== AI calls / rendering ================== */
const apikey = "Bearer exam-miner";

function setLoader(main, sub){
  $('loaderMain').textContent=main; $('loaderSub').textContent=sub||'';
  $('loaderBox').classList.remove('hidden'); $('editorBox').classList.add('hidden');
}
function hideLoader(){ $('loaderBox').classList.add('hidden'); }
function showEditor(){ $('editorBox').classList.remove('hidden'); }
function setProgress(pct, text){
  const p = Math.max(0, Math.min(100, Math.round(pct)));
  $('progressBar').style.width = p + '%';
  $('progressText').textContent = (text ? text + ' — ' : '') + p + '%';
}

async function callModelStrict(prompt, model){
  const payload = { model, messages:[
    {role:"system", content:"You generate exam items only, following instructions exactly."},
    {role:"user", content:prompt}
  ]};
  const data = await scheduledJsonFetch({
    url: "https://exam-miner.com/api/v1/chat/completions.php",
    init: {
      method:"POST",
      headers:{ "Content-Type":"application/json", "Authorization":apikey, "X-Title":"Exam Miner 2.0"},
      body: JSON.stringify(payload)
    },
    promptTextForToks: prompt
  });
  return data.choices?.[0]?.message?.content || "";
}

function buildFormatTotals(plan, selectedFormats){
  const totals = Object.fromEntries(selectedFormats.map(f => [f, 0]));
  const mustIncludeMatch = true;
  for(const row of plan){
    const Ls=[ {k:'RU', c:row.RU}, {k:'AA', c:row.AA}, {k:'HOTS', c:row.HOTS} ];
    for(const L of Ls){
      if(L.c<=0) continue;
      const per = distributeAcrossFormats(L.c, selectedFormats, mustIncludeMatch);
      for(const [fmt,cnt] of Object.entries(per)){
        totals[fmt] = (totals[fmt]||0) + (cnt||0);
      }
    }
  }
  if (selectedFormats.includes('Matching Type') && (totals['Matching Type']||0) === 0){
    let donor = null, max = -1;
    for (const f of selectedFormats){
      if (f==='Matching Type') continue;
      if ((totals[f]||0) > max){ donor = f; max = totals[f]||0; }
    }
    if (donor && max>0){ totals[donor]--; totals['Matching Type']=1; }
  }
  return totals;
}

/* ===== FIG token aware prompt ===== */
function buildFormatTask(format, count, meta, difficulty, tosPlan, setIndex, banList, figPlan){
  const diffHint = difficulty==='easy' ? 'Use simpler vocabulary and direct recall where appropriate.'
                : difficulty==='hard' ? 'Favor scenario-based prompts and higher-order wording.'
                : 'Use clear, natural wording with moderate complexity.';

  let fmtGuidance;
  if (format === "Matching Type") {
    const blocks = Math.ceil(count / 5);
    fmtGuidance =
      `Matching Type.\nReturn ONLY a JSON array of ${blocks} objects (no markdown).\nEach object:\n{\n` +
      `  "instruction": "short instruction",\n` +
      `  "left":  ["term 1","term 2","term 3","term 4","term 5"],\n` +
      `  "right": ["definition a","definition b","definition c","definition d","definition e"]\n}\n` +
      `Strings only. Exactly 5 pairs/object.`;
  } else {
    fmtGuidance =
      format==="Multiple Choice" ? "Multiple Choice with choices A–D. Each item lines: stem then A., B., C., D."
      : format==="True or False" ? "True or False. Output only the numbered statements, each starting with underscore (e.g., '_ 1. ...')."
      : format==="Identification" ? "Identification. Short prompts expecting a term/phrase; add a short blank line."
      : format==="Essay" ? "Essay prompts. 1–3 sentences; include long blank lines."
      : (format + ".");
  }

  const tosSummary = tosPlan.map(p => `- ${p.topic}: total ${p.total} (RU ${p.RU}, AA ${p.AA}, HOTS ${p.HOTS})`).join('\n');
  const avoidBlock = banList?.length ? `\nAvoid reusing these stems/phrases:\n${banList.slice(0,120).join('\n')}\n` : '';

  const figureBlock = (figPlan && figPlan.captions?.length)
    ? `\nFIGURES available. When a figure aids the item, insert token [[FIG:n]] INSIDE THE STEM exactly where it belongs (n=1..${figPlan.captions.length}). You MAY also use [[FIG:n]] as a CHOICE if relevant (e.g., “Which diagram shows…?”). Use at most ${(figPlan.perFig||2)} items per the same figure.\n` +
      figPlan.captions.map((c,i)=>`Figure ${i+1}: ${c}`).join('\n') + '\n'
    : '';

  const prompt = `
Create a UNIQUE Set ${setIndex} of exactly ${count} ${format} items for:
Subject: ${meta.subject || "(unspecified)"} | Topic: ${meta.topic || "(unspecified)"} | Grade: ${meta.grade || "(unspecified)"}

STRICT:
- Output ONLY the ${format} items, numbered 1..${count}, plain text.
- ${fmtGuidance}
- If you use a figure, you MUST insert [[FIG:n]] (n from the list provided) in the STEM (or as a CHOICE if relevant).
- NO explanations. NO Answer Key in this step.
- Follow the topical proportions implicitly (do not print them):
${tosSummary}
${avoidBlock}
${figureBlock}
Style: ${diffHint}

Learning Material (+ figure descriptors embedded at the end):
"""${meta.content}"""`.trim();

  return { format, count, prompt };
}

/* ================== parse + render (FIG token aware) ================== */
function extractQuestions(block){
  const lines = block.split(/\r?\n/);
  const q = [], answerLines = [];
  let inKey = false, inMatch = false;

  for (let raw of lines){
    const t = (raw || '').replace(/\s+$/,'');
    if (!t.trim()){ if(inMatch && q.length) q[q.length-1]+="\n"; continue; }

    if (/^answer\s*key[:\s]?$/i.test(t.trim())){ inKey=true; inMatch=false; continue; }
    if (inKey){ answerLines.push(t); continue; }

    if (/^Instruction:\s*/i.test(t) || /^Column A$/i.test(t)){
      if (/^Column A$/i.test(t)) q.push('Instruction: Match the items.');
      else q.push(t);
      inMatch = true; continue;
    }
    if (inMatch){
      if (/^Instruction:\s*/i.test(t)){ q.push(t); }
      else { q[q.length-1] += "\n" + t; }
      continue;
    }

    // if (/^_*\s*\d+[\.\)]\s+/.test(t.trim())) q.push(t.trim()); // 0970
    //if (/^_*\s*\d+\s*[\.\)]\s+/.test(t.trim())) q.push(t.trim()); // 097079
    if (/^(?:\s*\[\[FIG:\d+\]\]\s*)*_*\s*\d+[\.\)]\s+/.test(t.trim())) q.push(t.trim());
    else if (q.length) q[q.length-1] += "\n" + t;
  }
  return { items:q, answerKeyText:answerLines.join("\n") };
}
function shuffleInPlace(arr){ for(let i=arr.length-1;i>0;i--){ const j=Math.floor(Math.random()*(i+1)); [arr[i],arr[j]]=[arr[j]]; } }



// function renderMCQItem(txt, idx){
//  const body = s(txt).replace(/^\s*\d+[\.)]\s*/,'').trim();
/* 0970
  // split stem vs choices
  const choiceStart = body.search(/(?:^|\n)\s*A\.\s+/);
  const stemPart = choiceStart >= 0 ? body.slice(0, choiceStart) : body;
  const rest = choiceStart >= 0 ? body.slice(choiceStart) : '';

  // const stemWith = injectInlineFiguresIntoHtml(stemPart); // safe 0970
    const stemWith = FIG_ALLOWED.has('Multiple Choice')
  ? injectInlineFiguresIntoHtml(stemPart)
  : stripFigTokens(stemPart);

  // parse choices
  const m = rest.match(/(?:^|\n)\s*A\.\s*(.*?)(?:^|\n)\s*B\.\s*(.*?)(?:^|\n)\s*C\.\s*(.*?)(?:^|\n)\s*D\.\s*(.*)$/s);
  if(!m){
    return `<div style="margin:8px 0;"><b>${idx}.</b> ${stemWith}</div>`;
  }
  const [, Araw, Braw, Craw, Draw] = m;
  // IMPORTANT: do NOT strip figure tokens from choices
  // const norm = v => injectInlineFiguresIntoHtml(s(v)).trim() || '&nbsp;';
  const norm = v => injectInlineFiguresIntoHtml(stripFigTokens(s(v))).trim() || '&nbsp;';

  return [
    `<div style="margin:8px 0 2px 0;"><b>${idx}.</b> ${stemWith}</div>`,
    `<div style="margin-left:22px">A. ${norm(Araw)}</div>`,
    `<div style="margin-left:22px">B. ${norm(Braw)}</div>`,
    `<div style="margin-left:22px">C. ${norm(Craw)}</div>`,
    `<div style="margin-left:22px">D. ${norm(Draw)}</div>`
  ].join('\n');
}
*/

// function renderMCQItem(txt, idx){
//  const body = String(txt || '').replace(/^\s*\d+[\.)]\s*/,'').trim();
/* 097079
  // Accept A., A), (A) — at start of line or next line
  const aLabel = '(?:\\(?A\\)?[.)])';
  const bLabel = '(?:\\(?B\\)?[.)])';
  const cLabel = '(?:\\(?C\\)?[.)])';
  const dLabel = '(?:\\(?D\\)?[.)])';

  const choiceStart = body.search(new RegExp('(?:^|\\n)\\s*' + aLabel + '\\s+'));
  const stemPart = choiceStart >= 0 ? body.slice(0, choiceStart) : body;
  const rest = choiceStart >= 0 ? body.slice(choiceStart) : '';

  const stemWith = FIG_ALLOWED.has('Multiple Choice')
    ? injectInlineFiguresIntoHtml(stemPart)
    : stripFigTokens(stemPart);

  const rx = new RegExp(
    '(?:^|\\n)\\s*' + aLabel + '\\s*(.*?)' +
    '(?:^|\\n)\\s*' + bLabel + '\\s*(.*?)' +
    '(?:^|\\n)\\s*' + cLabel + '\\s*(.*?)' +
    '(?:^|\\n)\\s*' + dLabel + '\\s*(.*)$',
    's'
  );
  const m = rest.match(rx);
  if(!m){
    return `<div style="margin:8px 0;"><b>${idx}.</b> ${stemWith}</div>`;
  }

  const [, Araw, Braw, Craw, Draw] = m;
  // keep figures in choices if MCQ is allowed
  const norm = v => {
    const txt = String(v || '');
    return (FIG_ALLOWED.has('Multiple Choice')
      ? injectInlineFiguresIntoHtml(txt)
      : stripFigTokens(txt)
    ).trim() || '&nbsp;';
  };

  return [
    `<div style="margin:8px 0 2px 0;"><b>${idx}.</b> ${stemWith}</div>`,
    `<div style="margin-left:22px">A. ${norm(Araw)}</div>`,
    `<div style="margin-left:22px">B. ${norm(Braw)}</div>`,
    `<div style="margin-left:22px">C. ${norm(Craw)}</div>`,
    `<div style="margin-left:22px">D. ${norm(Draw)}</div>`
  ].join('\n');
}

*/
// 097079
function stripLeadingNumber(s){
  // optional FIG token(s), optional leading underscore, then 1. / 1)
  return String(s||'').replace(/^\s*(?:\[\[FIG:\d+\]\]\s*)*_?\s*\d+[\.)]\s*/,'');
}

function renderMCQItem(txt, idx){
  // const body = String(txt || '').replace(/^\s*\d+[\.)]\s*/,'').trim();
  const body = stripLeadingNumber(txt).trim();

  const aLabel='(?:\\(?A\\)?[.)])', bLabel='(?:\\(?B\\)?[.)])',
        cLabel='(?:\\(?C\\)?[.)])', dLabel='(?:\\(?D\\)?[.)])';

  const choiceStart = body.search(new RegExp('(?:^|\\n)\\s*' + aLabel + '\\s+'));
  const stemPart = choiceStart >= 0 ? body.slice(0, choiceStart) : body;
  const rest = choiceStart >= 0 ? body.slice(choiceStart) : '';

  const stemWith = FIG_ALLOWED.has('Multiple Choice')
    ? injectInlineFiguresIntoHtml(stemPart)
    : stripFigTokens(stemPart);

  const rx = new RegExp(
    '(?:^|\\n)\\s*' + aLabel + '\\s*(.*?)' +
    '(?:^|\\n)\\s*' + bLabel + '\\s*(.*?)' +
    '(?:^|\\n)\\s*' + cLabel + '\\s*(.*?)' +
    '(?:^|\\n)\\s*' + dLabel + '\\s*(.*)$', 's'
  );
  const m = rest.match(rx);

  // Fallback: show whatever text we have rather than a blank
  if(!m){
    const raw = FIG_ALLOWED.has('Multiple Choice')
      ? injectInlineFiguresIntoHtml(body)
      : stripFigTokens(body);
    return `<div style="margin:8px 0;"><b>${idx}.</b> ${raw.replace(/\n/g,'<br>')}</div>`;
  }

  const [, Araw, Braw, Craw, Draw] = m;
  const norm = v => {
    const txt = String(v || '');
    return (FIG_ALLOWED.has('Multiple Choice')
      ? injectInlineFiguresIntoHtml(txt)
      : stripFigTokens(txt)
    ).trim() || '&nbsp;';
  };

  return [
    `<div style="margin:8px 0 2px 0;"><b>${idx}.</b> ${stemWith}</div>`,
    `<div style="margin-left:22px">A. ${norm(Araw)}</div>`,
    `<div style="margin-left:22px">B. ${norm(Braw)}</div>`,
    `<div style="margin-left:22px">C. ${norm(Craw)}</div>`,
    `<div style="margin-left:22px">D. ${norm(Draw)}</div>`
  ].join('\n');
}

function renderMatchingBlock(text){
  const content = (window.DOMPurify ? DOMPurify.sanitize(text) : text);
  return `<pre style="white-space:pre-wrap; font-family:ui-monospace, SFMono-Regular, Menlo, monospace; line-height:1.5; margin:6px 0 10px 0;">${content}</pre>`;
}
function renderExamFromBlocks(blocks, meta, selectedFormats=null, doShuffle=false, setIndex=1){
  const title=`${meta.topic || meta.subject || "Exam"}`.trim();
  const tEsc = window.DOMPurify ? DOMPurify.sanitize(title) : title;
  const lines=[];
  lines.push(`<div style="text-align:center;font-weight:bold;font-size:18pt;margin-bottom:10pt">${tEsc} — SET ${setIndex}</div>`);

  const byTopic={};
  const topic = meta.topic || meta.subject || 'General';
  byTopic[topic] = {};
  for(const b of blocks){
    byTopic[topic][b.format] ||= [];
    byTopic[topic][b.format].push(...b.items);
  }
  for(const [t, map] of Object.entries(byTopic)){
    const tEsc2 = window.DOMPurify ? DOMPurify.sanitize(String(t).toUpperCase()) : String(t).toUpperCase();
    lines.push(`<div style="font-weight:bold;margin-top:10pt">${tEsc2}</div>`);
    const order = selectedFormats?.length ? selectedFormats : Object.keys(map);
    for(const fmt of order){
      // const items = (map[fmt]||[]).slice();
         const items = Array.isArray(map[fmt]) ? map[fmt].filter(Boolean) : [];
      
      if(!items.length) continue;
      if(doShuffle && fmt!=="Matching Type") shuffleInPlace(items);
      const fmtEsc = window.DOMPurify ? DOMPurify.sanitize(fmt) : fmt;
      lines.push(`<div style="font-weight:bold;margin:8px 0 4px 0">${fmtEsc}</div>`);
      if (fmt === "Matching Type"){
        (items).forEach((txt, i) => {
          const norm = normalizeMatchingBlock(txt);
          lines.push(renderMatchingBlock(norm));
          if (i < items.length - 1) lines.push(`<div style="text-align:center; opacity:.35; margin:4px 0;">────</div>`);
        });
      } else if (fmt === "Multiple Choice"){
        (items).forEach((txt,i)=>{ lines.push(renderMCQItem(txt, i+1)); });
      } else {
        (items).forEach((txt,i)=>{
          // let body = s(txt).replace(/^\s*_?\s*\d+[\.)]\s*/,"");  // 097079
          let body = stripLeadingNumber(txt);
          if (fmt==="True or False") body = normalizeTFStem(body);
          // const injected = injectInlineFiguresIntoHtml(body); // 0970
          const injected = FIG_ALLOWED.has(fmt)
              ? injectInlineFiguresIntoHtml(body)
              : stripFigTokens(body);
          
          const label = (fmt==="True or False") ? `_${i+1}.` : `${i+1}.`;
          lines.push(`<div style="margin:8px 0;"><b>${label}</b> ${injected}</div>`);
        });
      }
      lines.push(`<div style="height:8px"></div>`);
    }
    lines.push(`<div style="height:12px"></div>`);
  }
  return lines.join("\n");
}
function escapeHtml(s){ return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }

async function summarizeContent(text){
  const model=$('model').value;
  const payload = { model, messages:[
    { role:"system", content:"Summarize this learning material to the most important topics, keywords, concepts, and definitions. Preserve any final FIGURE descriptors if present."},
    { role:"user", content:text }
  ]};
  const data = await scheduledJsonFetch({
    url: "https://exam-miner.com/api/v1/chat/completions.php",
    init: {
      method:"POST",
      headers:{ "Content-Type":"application/json", "Authorization":apikey, "X-Title":"Exam Miner 2.0"},
      body: JSON.stringify(payload)
    },
    promptTextForToks: text
  });
  if(data.error) throw new Error(data.error.message||'Model error');
  return data.choices?.[0]?.message?.content || null;
}

function hasEditorContent(){
  const html = ($('output')?.innerHTML || '').trim();
  return html.length > 0;
}
function setSaveEnabled(on){
  const b = $('btnSaveDbTop');
  if(!b) return;
  if(on){ b.classList.remove('hidden'); b.disabled=false; b.removeAttribute('aria-disabled'); b.removeAttribute('title'); }
  else { b.disabled=true; b.setAttribute('aria-disabled','true'); b.setAttribute('title','Nothing to save yet'); }
}


function attachFiguresByCaption(blocks, figPlan) {
  if (!figPlan || !figPlan.captions?.length) return blocks;

  const captions = figPlan.captions.map(c => c.toLowerCase());

  for (let n = 0; n < captions.length; n++) {
    const cap = captions[n];
    let bestMatch = null, bestScore = 0;

    for (let bi = 0; bi < blocks.length; bi++) {
      if (!FIG_ALLOWED.has(blocks[bi].format)) continue; // ← skip disallowed formats
      for (let ii = 0; ii < blocks[bi].items.length; ii++) {
        const qtext = String(blocks[bi].items[ii]).toLowerCase();
        let overlap = 0;
        for (const word of cap.split(/\s+/)) {
          if (word.length >= 3 && qtext.includes(word)) overlap++;
        }
        if (overlap > bestScore) { bestScore = overlap; bestMatch = { bi, ii }; }
      }
    }

    if (bestMatch) {
      const token = `[[FIG:${n + 1}]]`;
      const item = blocks[bestMatch.bi].items[bestMatch.ii];
      blocks[bestMatch.bi].items[bestMatch.ii] =
        injectTokenIntoStem(item, token, blocks[bestMatch.bi].format);
    }
  }
  return blocks;
}

/* ================== per-set fresh generation (supports figure hints) ================== */

// Decide if any [[FIG:n]] appears in a list of items
function listHasFigTokens(items) {
  return items.some(it => /\[\[FIG:\d+\]\]/.test(it));
}

// Insert a FIG token into the STEM of an item.
// For MCQ, we inject it right before " A." (first choice). For others, at the end of the first line.
/* 0970
function injectTokenIntoStem(item, token, format) {
  const s = String(item);
  if (format === 'Multiple Choice') {
    const m = s.match(/\sA\.\s/);
    if (m) {
      // inject before the first " A. "
      return s.slice(0, m.index).trimEnd() + ' ' + token + s.slice(m.index);
    }
    // Fallback if choices aren’t formatted
    return s.trimEnd() + ' ' + token;
  } else {
    // Non-MCQ: add to the first line (stem)
    const nl = s.indexOf('\n');
    if (nl === -1) return s.trimEnd() + ' ' + token;
    return s.slice(0, nl).trimEnd() + ' ' + token + s.slice(nl);
  }
}
*/

function injectTokenIntoStem(item, token, format) {
  const str = String(item);
  /* 097079
  if (format === 'Multiple Choice') {
    const rx = /(?:^|\n)\s*(?:\(?A\)?[.)])\s/;
    const m = str.match(rx);
    if (m) {
      // insert right before the first choice label
      return str.slice(0, m.index).trimEnd() + ' ' + token + str.slice(m.index);
    }
    return str.trimEnd() + ' ' + token;
  }
  */
  
  if (format === 'Multiple Choice') {
      const rx = /(?:^|\n)\s*(?:\(?A\)?[.)])\s/;
      const m = str.match(rx);
      if (m) {
        // insert token and force a newline so A. remains at start of a line
        const before = str.slice(0, m.index).trimEnd();
        const after  = str.slice(m.index);
        return `${before} ${token}\n${after}`;
      }
      // fallback: add token at end of stem on its own line
      return `${str.trimEnd()} ${token}\n`;
    } else {
    const nl = str.indexOf('\n');
    if (nl === -1) return str.trimEnd() + ' ' + token;
    return str.slice(0, nl).trimEnd() + ' ' + token + str.slice(nl);
  }
}

function ensureOnePerFigure(blocks, figPlan) {
  if (!figPlan || !figPlan.captions || !figPlan.captions.length) return blocks;

  const holders = [];
  blocks.forEach((b, bi) => {
    // collect only allowed formats
    if (!FIG_ALLOWED.has(b.format)) return;
    b.items.forEach((txt, ii) => holders.push({ bi, ii, fmt: b.format, txt }));
  });

  const hasAnyFig = s => /\[\[FIG:\d+\]\]/.test(s);

  // 1) record already-used figures
  const used = new Set();
  holders.forEach(h => {
    const m = String(h.txt).match(/\[\[FIG:(\d+)\]\]/g);
    if (m) m.forEach(tok => used.add(Number(tok.match(/\d+/)[0])));
  });

  // 2) ensure each figure appears once in an allowed format
  const preferred = Array.from(FIG_ALLOWED); // e.g., ['Multiple Choice','True or False']

  for (let n = 1; n <= figPlan.captions.length; n++) {
    if (used.has(n)) continue;
    const slot = holders.find(h => !hasAnyFig(h.txt) && preferred.includes(h.fmt));
    if (!slot) continue;

    const token = `[[FIG:${n}]]`;
    const newTxt = injectTokenIntoStem(slot.txt, token, slot.fmt);
    blocks[slot.bi].items[slot.ii] = newTxt;
    used.add(n);
  }

  // 3) trim duplicate occurrences (keep the first for each figure id)
  const firstSeen = new Set();
  holders.forEach(h => {
    const matches = [...String(blocks[h.bi].items[h.ii]).matchAll(/\[\[FIG:(\d+)\]\]/g)];
    if (!matches.length) return;
    const keep = new Set();
    let txt = blocks[h.bi].items[h.ii];
    matches.forEach(m => {
      const num = Number(m[1]);
      if (firstSeen.has(num)) {
        txt = txt.replace(new RegExp(`\\s*\\[\\[FIG:${num}\\]\\]`, 'g'), '');
      } else {
        if (!keep.has(num)) keep.add(num), firstSeen.add(num);
      }
    });
    blocks[h.bi].items[h.ii] = txt;
  });

  return blocks;
}

function autoInjectFigTokens(items, format, figPlan) {
  if (!figPlan || !figPlan.captions || !figPlan.captions.length || !FIG_ALLOWED.has(format)) return items;

  const perFig = Math.max(1, figPlan.perFig || 2);
  const out = items.slice();

  // Count how many times each figure is already used
  const used = new Map(); // n -> count
  out.forEach(s => {
    const m = String(s).match(/\[\[FIG:(\d+)\]\]/g);
    if (!m) return;
    m.forEach(tok => {
      const n = Number(tok.match(/\d+/)[0]);
      used.set(n, (used.get(n)||0) + 1);
    });
  });

  // Helper: find next figure with available quota
  function nextFigure(startAt=1){
    const total = figPlan.captions.length;
    for (let step = 0; step < total; step++){
      const n = ((startAt - 1 + step) % total) + 1;
      if ((used.get(n) || 0) < perFig) return n;
    }
    return null;
  }

  let cursor = 1;
  for (let i = 0; i < out.length; i++){
    const s = String(out[i]);
    // Skip if item already has a FIG token
    if (/\[\[FIG:\d+\]\]/.test(s)) continue;

    const n = nextFigure(cursor);
    if (n == null) break; // no more quota
    const token = `[[FIG:${n}]]`;

    // insert into STEM
    out[i] = injectTokenIntoStem(s, token, format);

    used.set(n, (used.get(n)||0) + 1);
    cursor = n + 1;
    if (cursor > figPlan.captions.length) cursor = 1;
  }
  return out;
}
/* ============== Validator helpers (097079) ========== */

// Accept A., A), (A) etc.
const CH_A = '(?:\\(?A\\)?[.)])';
const CH_B = '(?:\\(?B\\)?[.)])';
const CH_C = '(?:\\(?C\\)?[.)])';
const CH_D = '(?:\\(?D\\)?[.)])';

function mcqHasFourChoices(s){
  const rx = new RegExp(
    '(?:^|\\n)\\s*' + CH_A + '\\s+[\\s\\S]*?' +
    '(?:^|\\n)\\s*' + CH_B + '\\s+[\\s\\S]*?' +
    '(?:^|\\n)\\s*' + CH_C + '\\s+[\\s\\S]*?' +
    '(?:^|\\n)\\s*' + CH_D + '\\s+',
    'i'
  );
  return rx.test(String(s||''));
}

function nonEmptyStem(s){
  // after removing the leading number like "1." or "1)"
  const stem = String(s||'').replace(/^\s*_?\s*\d+\s*[\.)]\s*/,'').trim();
  return stem.length >= 5; // tweak if you want
}

/* ============ Matching type helpers to convert it to 1 set of matching type only ========= */
function parseMatchingBlock(blockText){
  const lines = String(blockText).split(/\r?\n/);
  const aIdx = lines.findIndex(l => /^Column A$/i.test(l.trim()));
  const bIdx = lines.findIndex(l => /^Column B$/i.test(l.trim()));
  const A = [], B = [];
  if (aIdx >= 0 && bIdx > aIdx){
    for (let i = aIdx + 1; i < bIdx; i++){
      const m = lines[i].match(/^\s*\d+\.\s*(.+)\s*$/);
      if (m) A.push(m[1]);
    }
    for (let i = bIdx + 1; i < lines.length; i++){
      const m = lines[i].match(/^\s*[a-z]\.\s*(.+)\s*$/i);
      if (m) B.push(m[1]);
    }
  }
  return { A, B };
}

function combineMatchingBlocks(blocks, desiredPairs){
  const letters = 'abcdefghijklmnopqrstuvwxyz';
  let A = [], B = [];
  for (const blk of blocks){
    const { A: aList, B: bList } = parseMatchingBlock(normalizeMatchingBlock(blk));
    A = A.concat(aList);
    B = B.concat(bList);
    if (A.length >= desiredPairs) break;
  }
  A = A.slice(0, desiredPairs);
  B = B.slice(0, desiredPairs);

  const out = [];
  out.push('Instruction: Match the items.');
  out.push('Column A');
  A.forEach((t, i) => out.push(`${i + 1}. ${t}`));
  out.push('');
  out.push('Column B');
  B.forEach((t, i) => out.push(`${letters[i]}. ${t}`));
  return out.join('\n');
}


async function generateBlocksForSet({meta, selectedFormats, plan, difficulty, model, setIndex, banList, figPlan}){
  const totalsByFormat = buildFormatTotals(plan, selectedFormats);
  const formatTasks = Object.entries(totalsByFormat)
    .filter(([,cnt]) => cnt > 0)
    .map(([fmt, cnt]) => buildFormatTask(fmt, cnt, meta, difficulty, plan, setIndex, banList, figPlan));

  const blocks = [];
  for (const t of formatTasks){
    let data = await callModelStrict(t.prompt, model);
    let raw = standardizeTerms(stripModelNoise(decodeEntities(data)));
    let keep = [];

    if (t.format === 'Matching Type'){
      const parsed = extractFirstJson(raw);
      if (Array.isArray(parsed)){
        for (const obj of parsed){
          const block = createMatchingBlockFromJson(obj || {});
          if (block) keep.push(block);
          if (keep.length === t.count) break;
        }
      } else if (parsed && typeof parsed === 'object'){
        const block = createMatchingBlockFromJson(parsed);
        if (block) keep.push(block);
      }
      if (keep.length < t.count){
        let fb = extractQuestions(raw).items;
        fb = fb.map(normalizeMatchingBlock).filter(isValidMatchingBlock);
        for (const it of fb){
          if (keep.length === t.count) break;
          keep.push(it);
        }
      }
      if (keep.length < t.count){
        const missing = t.count - keep.length;
        const follow = `
Return ONLY a JSON array of ${missing} objects, each like:
{
  "instruction": "short instruction",
  "left":  ["term 1","term 2","term 3","term 4","term 5"],
  "right": ["definition a","definition b","definition c","definition d","definition e"]
}`.trim();
        let more = await callModelStrict(follow, model);
        more = standardizeTerms(stripModelNoise(decodeEntities(more)));
        const mp = extractFirstJson(more);
        if (Array.isArray(mp)){
          for (const obj of mp){
            const block = createMatchingBlockFromJson(obj || {});
            if (block) keep.push(block);
            if (keep.length === t.count) break;
          }
        }
      }
      
      // === Make ONE continuous Matching Type block with exactly t.count pairs ===
      // (requires parseMatchingBlock() + combineMatchingBlocks() helpers added earlier)
      const single = combineMatchingBlocks(keep, t.count);
      keep = [single];
      
    } else {
        
        let items = extractQuestions(raw).items;
      // 097079
        if (t.format === 'Multiple Choice') {
          items = items.filter(mcqHasFourChoices);
        } else if (t.format === 'True or False') {
         
         // items = items.map(x => x.replace(/^\s*(_\s*)?\d+[\.)]\s*/,''))  097079
           //            .map(normalizeTFStem)
             //          .filter(nonEmptyStem);
                       
            items = items.map(x => stripLeadingNumber(x))
                         .map(normalizeTFStem)
                         .filter(s => s.trim().length > 0);
        } else {
          // Identification / Essay: just require a non-empty stem
          items = items.filter(nonEmptyStem);
        }
              
      
      if (t.format === 'True or False'){
        items = items.map(x => x.replace(/^\s*(_\s*)?\d+[\.)]\s*/,'')).map(normalizeTFStem).filter(s => s.trim().length > 0); // 0970
      }
      const seen = new Set();
      for (const it of items){
        const h = stemHash(it);
        if (!seen.has(h)){ seen.add(h); keep.push(it); }
        if (keep.length === t.count) break;
      }
      if (keep.length < t.count){
        const missing = t.count - keep.length;
        const follow = `
You produced ${keep.length}/${t.count} ${t.format} items.
Generate exactly ${missing} MORE ${t.format} items continuing numbering from ${keep.length+1}.
Plain text only.`.trim();
        let more = await callModelStrict(follow, model);
        more = standardizeTerms(stripModelNoise(decodeEntities(more)));
        let moreItems = extractQuestions(more).items;
        
        if (t.format === 'Multiple Choice') {
          moreItems = moreItems.filter(mcqHasFourChoices);
        } else if (t.format === 'True or False') {
          moreItems = moreItems.map(x => x.replace(/^\s*(_\s*)?\d+[\.)]\s*/,''))
                               .map(normalizeTFStem)
                               .filter(nonEmptyStem);
        } else {
          moreItems = moreItems.filter(nonEmptyStem);
        }
        
        if (t.format === 'True or False'){
          moreItems = moreItems.map(x => x.replace(/^\s*(_\s*)?\d+[\.)]\s*/,'')).map(normalizeTFStem);
        }
        const seen2 = new Set(keep.map(stemHash));
        for (const mi of moreItems){
          const h = stemHash(mi);
          if (!seen2.has(h)){ seen2.add(h); keep.push(mi); }
          if (keep.length === t.count) break;
        }
      }
    }
    keep = autoInjectFigTokens(keep, t.format, figPlan);
    blocks.push({ topic: meta.topic || meta.subject || 'General', format: t.format, items: keep.slice(0, t.count) });
  }
  
  

  // Guarantee at least one matching if requested
  const needsMatching = selectedFormats.includes('Matching Type') && !blocks.some(b => b.format==='Matching Type' && b.items.length);
  if (needsMatching){
    const oneTask = buildFormatTask('Matching Type', 1, meta, difficulty, plan, setIndex, banList, figPlan);
    const extra = await callModelStrict(oneTask.prompt, model);
    let items = extractQuestions(standardizeTerms(stripModelNoise(decodeEntities(extra)))).items;
    items = items.map(normalizeMatchingBlock).filter(isValidMatchingBlock);
    if (items.length) blocks.push({ topic: meta.topic || meta.subject || 'General', format:'Matching Type', items: [items[0]] });
  }
  
    if (figPlan) {
      // enforce exactly 1 usage per selected image and match the question to image
      attachFiguresByCaption(blocks, figPlan);
    }
    
  return blocks;
}

async function generateAnswerKeyFor(html, model){
  const plain = html.replace(/<[^>]+>/g,' ');
  return standardizeTerms(stripModelNoise(decodeEntities((await callModelStrict(
    `Produce ONLY the answer key for the exam below. No notes, no explanations.\n\nExam:\n"""${plain}"""`,
    model)).trim())));
}

function ensureAtLeastOnePerSelectedFigure(blocks){
  const figs = window.__selectedFigures || [];
  if (!figs.length) return blocks;

  const used = new Set();
  const figRe = /\[\[FIG:(\d+)\]\]/g;

  // mark used only inside allowed formats
  for (const b of blocks){
    if (!FIG_ALLOWED.has(b.format)) continue;
    for (const it of b.items){
      let m; while ((m = figRe.exec(String(it)))) used.add(Number(m[1]));
    }
  }

  // ensure each fig appears at least once, in an allowed format
  for (let n = 1; n <= figs.length; n++){
    if (used.has(n)) continue;
    outer: for (const b of blocks){
      if (!FIG_ALLOWED.has(b.format)) continue;
      for (let i=0;i<b.items.length;i++){
        if (!figRe.test(String(b.items[i]))){
          // b.items[i] = `[[FIG:${n}]] ` + String(b.items[i]); 0970
          b.items[i] = injectTokenIntoStem(String(b.items[i]), `[[FIG:${n}]]`, b.format);
          used.add(n);
          break outer;
        }
      }
    }
  }
  return blocks;
}

async function generateExamFlow(){
  const file=$('material').files?.[0]; if(!file) return showAlert('err','Please upload a learning material file.');
  const subject=$('subject').value.trim(), topic=$('topic').value.trim(), grade=$('grade').value.trim();
  const difficulty = $('difficulty').value;

  const rawQ = Number($('questionCount').value);
  if (rawQ > 100) { showAlert('err','Maximum is 100 questions.'); return; }
  let total = Math.max(1, Math.min(100, Number.isFinite(rawQ) ? rawQ : 20));

  const selectedFormats=Array.from(document.querySelectorAll('.format:checked')).map(cb=>cb.value);
  if(!selectedFormats.length) return showAlert('err','Select at least one exam format.');
  if(!validateTOS()) return showAlert('err','Fix your TOS first.');
  const numSets = Math.max(1, parseInt($('numSets').value, 10) || 1);

  const plan=allocateItemsByTOS(total);
  const model=$('model').value;

  try{
    setLoader('Extracting content…','Reading your file locally.');
    setProgress(5,'Starting');

    // 1) Text
    const extractedText=await extractLocally(file);
    if(!extractedText || !extractedText.trim()){ hideLoader(); return showAlert('err','No extractable text found in the file.'); }
    setProgress(12,'Text extracted');

    // 2) Figures
    const figs = await extractFigures(file);
    window.__allFigures = figs;
    renderFigureGallery(figs);
    setProgress(18, figs.length ? `Found ${figs.length} figure(s)` : 'No figures found');

    // 2.5) USER-SELECTED IMAGES → DESCRIBE ON SERVER
    const perFig = Math.max(1, Math.min(10, parseInt($('itemsPerFigure')?.value||'2',10)));
    const chosen = (window.__selectedFigures || []);
    const useFigItems = chosen.length > 0;
    let enriched = chosen;
    try {
        if (useFigItems && chosen.length){
          setLoader('Analyzing selected images…','Describing each figure.');
          enriched = await describeSelectedImagesViaServer(chosen, "gemini-2.5-flash");
          setProgress(22,'Images analyzed');
        }
    } catch (e) {
        console.warn('describe failed (continuing without captions):', e);
        showAlert('err', 'Image analysis failed. Continuing without figure captions.');
        enriched = chosen.map(f => ({...f, caption: f.caption || f.name || 'Figure'}));
        setProgress(22,'describe failed (continuing without captions)');
    }
    window.__selectedFigures = enriched;

    // 2.6) Append figure descriptors to the learning content BEFORE summarization
    let figuresDescriptorBlock = '';
    if (useFigItems && enriched.length){
      const lines = [];
      lines.push('FIGURES (descriptors):');
      enriched.forEach((f,i)=>{
        lines.push(`- Figure ${i+1}: ${(f.caption||'(no caption)').trim()}`);
      });
      figuresDescriptorBlock = '\n\n' + lines.join('\n');
    }
    const contentWithFigs = (extractedText||'') + figuresDescriptorBlock;

    // 3) Summarize if large (on TEXT + descriptors)
    const NEED_SUMMARY = contentWithFigs.length > 10000;
    if (NEED_SUMMARY){
      setLoader('Summarizing content…','Condensing material (with figure descriptors).');
      const content = await summarizeContent(contentWithFigs);
      if(!content){ hideLoader(); return showAlert('err','Summarization failed.'); }
      window.__genContent = content;
      setProgress(38,'Summary ready');
    } else {
      window.__genContent = contentWithFigs;
      setProgress(38,'Using full content');
    }

    // 3.5) Build figPlan for prompts
    const figPlan = (useFigItems && enriched.length)
      ? { captions: enriched.map(x => (x.caption||'').trim()), perFig: 1 } // force 1:1 
      : null;

    const meta={subject, topic: topic || subject, grade, content: window.__genContent};
    const doShuffle = $('shuffle')?.checked;
    const includeKey=$('answerKey')?.checked;

    const setsHtml = [];
    const banList = []; // discourage reuse across sets

    for (let s=1; s<=numSets; s++){
      setLoader(`Generating Set ${s}…`,'Fresh questions with inline figures.');
      setProgress(40 + Math.round((s-1)*(50/Math.max(1,numSets))), `Generating Set ${s}`);
      let blocks = await generateBlocksForSet({meta, selectedFormats, plan, difficulty, model, setIndex:s, banList, figPlan});
      blocks = ensureAtLeastOnePerSelectedFigure(blocks);
      const paperHtml = renderExamFromBlocks(blocks, meta, selectedFormats, doShuffle, s);

      // Update ban list (first line of each item)
      for (const b of blocks){
        for (const it of b.items){
          const line = String(it).split('\n')[0] || it;
          banList.push(line.slice(0,180));
        }
      }

      setsHtml.push(paperHtml);

      if (includeKey){
        setLoader(`Generating Answer Key — Set ${s}`, 'Key on its own page.');
        setProgress(90,'Generating Answer key');
        const key = await generateAnswerKeyFor(paperHtml, model);
        setsHtml.push(`<div class="page-break"></div>`);
        setsHtml.push(`<div style="font-weight:bold; font-size:14pt; margin-top:6pt;">Answer Key — SET ${s}</div>`);
        setsHtml.push(`<div style="margin-top:8px;">${escapeHtml(key).replace(/\n/g,'<br>')}</div>`);
        setsHtml.push(`<div class="page-break"></div>`);
      } else if (s < numSets) {
        setsHtml.push(`<div class="page-break"></div>`);
      }
    }
    setProgress(100,'Done');
    showAlert('ok','Draft generated! You can edit, then export or save.');
    hideLoader(); showEditor();
       const finalHtml = DOMPurify.sanitize(setsHtml.map(s).join('\n'), { ADD_ATTR:['style','src','alt'] });
    $('output').innerHTML = finalHtml;
    setSaveEnabled(true);

    $('allocNote').textContent = 'Allocation → ' + plan.map(p => `${p.topic}: ${p.total} (RU ${p.RU}, AA ${p.AA}, HOTS ${p.HOTS})`).join(' | ');
    
    
  }catch(e){
    hideLoader(); console.error(e); showAlert('err', e?.message || 'Generation failed. AI service is busy. Please try again shortly.'); return;
  }
}

/* ================== Save to DB ================== */
async function saveExamToDB(e){
  e?.preventDefault?.();

  const raw = $('output').innerHTML || '';
  if(!raw.trim()){ return showAlert('err','Nothing to save. Generate or paste questions first.'); }
  const body_html = DOMPurify.sanitize(raw, { ADD_ATTR: ['style','src','alt'] })
    .replace(/>\s+</g,'><')
    .replace(/\s{2,}/g,' ')
    .replace(/\n{2,}/g,'\n');

  const approxBytes = new Blob([body_html]).size;
  if (approxBytes > 900_000){
    showAlert('err', `Exam content is large (~${(approxBytes/1024/1024).toFixed(2)} MB). If saving fails, reduce items or ask admin to raise post_max_size/client_max_body_size.`);
  }

  const title = $('topic').value.trim() || 'Generated Exam';
  const description = $('subject').value ? (`${$('subject').value}${$('grade').value ? ' • ' + $('grade').value : ''}`) : '';
  const number_of_questions = parseInt($('questionCount').value,10) || 0;

  const formats = Array.from(document.querySelectorAll('.format:checked')).map(cb=>cb.value);
  const exam_type = formats.length===1 ? (formats[0].toLowerCase().replace(/\s+/g,'_')) : 'mixed';
  const sets_of_exam = parseInt($('numSets').value,10) || 1;

  const fd = new FormData();
  fd.append('token', jwt);
  fd.append('title', title);
  fd.append('description', description);
  fd.append('exam_type', exam_type);
  fd.append('number_of_questions', number_of_questions);
  fd.append('sets_of_exam', sets_of_exam);
  fd.append('learning_material', $('material').files?.[0]?.name || '');
  fd.append('body_html', body_html);

  try{
    const res = await fetch('/api/exam_save.php', { method:'POST', headers: acceptHeaders(), body: fd });
    const text = await res.text(); let data;
    try{ data = JSON.parse(text); }catch{ throw new Error(text); }

    if ((data && data.exam_id) || data?.status === 'success'){
      showAlert('ok', data?.message || 'Saved! Opening your exam…');
      const id = data.exam_id || data.id;
      if(id) setTimeout(()=> location.href = `/exam/${id}`, 700);
    } else {
      showAlert('err', data?.message || 'Failed to save exam.');
    }
  }catch(err){
    console.error(err);
    showAlert('err','Server error while saving.');
  }
}
window.saveExamToDB = saveExamToDB;
})();
</script>

</body>
</html>
