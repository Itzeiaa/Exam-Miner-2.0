<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Exam Miner 2.0 - Profile Settings</title>
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="min-h-screen">
    <!-- Blue gradient background -->
    <div class="absolute inset-0 gradient-animated"></div>

    <script>
      // Require JWT
      const jwt = localStorage.getItem('jwt_token');
      if (!jwt) location.replace('/login');
    </script>

    <div class="flex relative z-10">
        <!-- Left Sidebar -->
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
                <a href="/my-exams" class="flex items-center px-4 py-3 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition-all duration-200 mb-2 group">
                    <i class="fas fa-file-alt mr-3 group-hover:scale-110 transition-transform duration-200"></i>
                    My Exams
                </a>
            </nav>

            <!-- User Profile Section (populated via JS) -->
            <div class="absolute bottom-0 w-64 p-6 border-t border-gray-100 bg-gray-50">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center mr-3 shadow-lg overflow-hidden">
                        <img id="sidebarAvatar" src="/images/default-avatar.png" alt="Avatar" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <p id="sidebarName" class="font-bold text-gray-900">User</p>
                        <a href="/profile" class="text-sm text-blue-600 hover:text-blue-700 cursor-pointer transition-colors duration-200">View Profile</a>
                    </div>
                </div>
                <button id="logoutBtn" class="w-full bg-white text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-100 border border-gray-200 shadow-sm transition-all duration-200 hover:shadow-md">
                    Logout
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex items-center mb-4">
                    <a href="/dashboard" class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center mr-4 shadow-xl hover:shadow-2xl hover:scale-105 transition-all duration-200 cursor-pointer">
                        <i class="fas fa-arrow-left text-blue-500 text-2xl"></i>
                    </a>
                    <div>
                        <h1 class="text-4xl font-bold text-gray-900 mb-2">Profile Settings</h1>
                        <p class="text-gray-600">Manage your account information and preferences.</p>
                    </div>
                </div>
            </div>

            <!-- Alerts -->
            <div id="alertBox" class="hidden mb-6 p-4 rounded-lg"></div>

            <!-- Profile Settings Card -->
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden max-w-5xl mx-auto">
                <form id="profileForm" enctype="multipart/form-data">
                    <div class="p-8">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <!-- Avatar Section -->
                            <div class="lg:col-span-1">
                                <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl p-6 text-center">
                                    <input type="file" id="profilePhoto" name="profile_photo" accept="image/*" class="hidden">
                                    <div class="relative inline-block mb-6">
                                        <div class="w-24 h-24 rounded-full shadow-lg mx-auto ring-2 ring-white ring-opacity-50 border border-gray-300 overflow-hidden">
                                            <img id="profileImage" src="/images/default-avatar.png" alt="Profile Picture" class="w-full h-full object-cover">
                                        </div>
                                        <button type="button" id="pickPhoto" class="absolute -bottom-2 -right-2 w-12 h-12 bg-white rounded-full shadow-lg flex items-center justify-center hover:bg-gray-50 transition-all duration-200 border-4 border-indigo-500 hover:scale-110">
                                            <i class="fas fa-camera text-indigo-500 text-lg"></i>
                                        </button>
                                    </div>
                                    <h3 class="text-xl font-bold text-gray-800 mb-2">Profile Picture</h3>
                                    <p class="text-gray-600 mb-4">Click the camera icon to change your avatar</p>
                                    <button type="button" id="pickPhoto2" class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-6 py-3 rounded-lg hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105 font-medium">
                                        Upload New Photo
                                    </button>
                                </div>
                            </div>

                            <!-- Form Fields -->
                            <div class="lg:col-span-2 space-y-8">
                                <!-- Personal Information -->
                                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-6 border border-blue-100">
                                    <div class="flex items-center mb-6">
                                        <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center mr-3">
                                            <i class="fas fa-user text-white text-lg"></i>
                                        </div>
                                        <h3 class="text-xl font-bold text-gray-800">Personal Information</h3>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-3">Full Name</label>
                                            <div class="relative">
                                                <input type="text" name="name" id="name"
                                                       class="w-full px-4 py-4 pl-12 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white shadow-sm">
                                                <i class="fas fa-user text-gray-400 absolute left-4 top-4 text-lg"></i>
                                            </div>
                                        </div>

                                        <div>
                                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-3">Email Address</label>
                                            <div class="relative">
                                                <input type="email" id="email" disabled readonly aria-disabled="true" tabindex="-1"
                                                       title="Email cannot be changed"
                                                       class="w-full px-4 py-4 pl-12 border-2 border-gray-200 rounded-xl bg-gray-100 text-gray-500 italic cursor-not-allowed">
                                                <i class="fas fa-envelope text-gray-400 absolute left-4 top-4 text-lg"></i>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-2">Email is fixed and cannot be edited.</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Security -->
                                <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl p-6 border border-green-100">
                                    <div class="flex items-center mb-6">
                                        <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-emerald-600 rounded-lg flex items-center justify-center mr-3">
                                            <i class="fas fa-lock text-white text-lg"></i>
                                        </div>
                                        <h3 class="text-xl font-bold text-gray-800">Security Settings</h3>
                                    </div>

                                    <div class="space-y-6">
                                        <div>
                                            <label for="password" class="block text-sm font-semibold text-gray-700 mb-3">New Password</label>
                                            <div class="relative">
                                                <input type="password" name="password" id="password"
                                                       placeholder="Leave blank to keep current password"
                                                       class="w-full px-4 py-4 pl-12 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm">
                                                <i class="fas fa-lock text-gray-400 absolute left-4 top-4 text-lg"></i>
                                            </div>
                                        </div>

                                        <div>
                                            <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-3">Confirm New Password</label>
                                            <div class="relative">
                                                <input type="password" id="password_confirmation"
                                                       placeholder="Confirm new password"
                                                       class="w-full px-4 py-4 pl-12 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm">
                                                <i class="fas fa-lock text-gray-400 absolute left-4 top-4 text-lg"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center justify-between pt-6 border-t-2 border-gray-100">
                                    <div class="text-sm text-gray-500">
                                        <p>Last updated: <span id="lastUpdated">—</span></p>
                                    </div>
                                    <div class="flex space-x-4">
                                        <button type="button" id="cancelBtn" class="px-8 py-3 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-all duration-200 font-semibold hover:border-gray-400">
                                            Cancel
                                        </button>
                                        <button type="submit" id="saveBtn"
                                                class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-8 py-3 rounded-xl hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105 font-semibold flex items-center">
                                            <i class="fas fa-check mr-2"></i>
                                            Save Changes
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <style>
        @keyframes gradientShift { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        .gradient-animated { background: linear-gradient(-45deg,#1e3a8a,#3b82f6,#60a5fa,#93c5fd,#1e40af,#1d4ed8); background-size:400% 400%; animation: gradientShift 15s ease infinite; }
    </style>

   <script>
  const api = { update: '/api/profile_update.php' };

  // -------- token helpers --------
  const TOKEN_KEY = 'jwt_token';
  function getToken() {
    return (localStorage.getItem(TOKEN_KEY) || '')
      .replace(/^Bearer\s+/i,'')
      .replace(/^"|"$/g,'');
  }
  function bearerHeaders(extra = {}) { return { 'Authorization': 'Bearer ' + getToken(), ...extra }; }

  // -------- lightweight profile cache (for avatar/name) --------
  const CACHE_KEY = 'profile_cache'; // {name, email, profile_picture, updated_at}
  function readCache() {
    try { return JSON.parse(localStorage.getItem(CACHE_KEY) || '{}'); } catch { return {}; }
  }
  function writeCache(obj) {
    try { localStorage.setItem(CACHE_KEY, JSON.stringify(obj || {})); } catch {}
  }

  // -------- jwt parse (base64url) --------
  function parseJwt(token){
    try{
      const [,p] = token.split('.');
      if(!p) return {};
      const b64 = p.replace(/-/g,'+').replace(/_/g,'/');
      const json = decodeURIComponent(atob(b64).split('').map(c=>'%'+('00'+c.charCodeAt(0).toString(16)).slice(-2)).join(''));
      return JSON.parse(json);
    }catch{ return {}; }
  }

  // -------- image/URL helpers --------
  const DEFAULT_AVATAR = '/images/default-avatar.png';

  function sanitizeBase64(s){
    return String(s||'').replace(/\s+/g,''); // remove spaces/newlines
  }
  function isRawBase64(s){
    return typeof s==='string' && s.length>100 && /^[A-Za-z0-9+/=]+$/.test(s) && !s.includes(' ');
  }
  function resolveAvatar(pic){
    if(!pic) return DEFAULT_AVATAR;
    const p = String(pic).trim();
    if(p.startsWith('data:image/')) return p;
    if(p.startsWith('http://') || p.startsWith('https://') || p.startsWith('/')) return p;
    if(isRawBase64(p) || /^[A-Za-z0-9+/=\s]+$/.test(p)) {
      return 'data:image/jpeg;base64,' + sanitizeBase64(p);
    }
    return '/images/' + p; // treat as filename
  }
  function setAvatarAll(pic){
    const url = resolveAvatar(pic);
    const main = document.getElementById('profileImage');
    const side = document.getElementById('sidebarAvatar');
    if(main){ main.src = url; main.onerror = ()=> main.src = DEFAULT_AVATAR; }
    if(side){ side.src = url; side.onerror = ()=> side.src = DEFAULT_AVATAR; }
  }

  // -------- UI helpers --------
  const $ = (id)=>document.getElementById(id);
  function showAlert(type, text){
    const box = $('alertBox');
    box.className = 'mb-6 p-4 rounded-lg ' + (type==='ok'
      ? 'bg-green-50 border border-green-200 text-green-700'
      : 'bg-red-50 border border-red-200 text-red-700');
    box.textContent = text; box.classList.remove('hidden');
    setTimeout(()=>box.classList.add('hidden'), 3500);
  }
  function setLastUpdated(dt){
    $('lastUpdated').textContent = dt
      ? new Date(String(dt).replace(' ','T')).toLocaleDateString()
      : new Date().toLocaleDateString();
  }

  // -------- initial hydrate (JWT -> cache -> default) --------
  function hydrateProfile(){
    const payload = parseJwt(getToken());
    const u = payload.user || payload.data || payload;

    // from JWT
    let name  = u.name || u.nick || u.handle;
    let email = u.email || '';
    let pic   = u.profile_picture || u.picture || u.avatar;

    // fallback to cache if JWT missing fields
    const cache = readCache();
    if(!name && cache.name) name = cache.name;
    if(!email && cache.email) email = cache.email;
    if(!pic && cache.profile_picture) pic = cache.profile_picture;

    if($('name')) $('name').value = name || '';
    if($('email')) $('email').value = email || '';
    if($('sidebarName')) $('sidebarName').textContent = name || 'User';
    setAvatarAll(pic);
    setLastUpdated(u.updated_at || cache.updated_at || (payload.iat ? new Date(payload.iat*1000).toISOString() : null));
  }

  // -------- image picker preview --------
  function previewImage(file){
    if(!file) return;
    const r=new FileReader();
    r.onload = e => $('profileImage').src = e.target.result;
    r.readAsDataURL(file);
  }
  $('pickPhoto')?.addEventListener('click', ()=> $('profilePhoto').click());
  $('pickPhoto2')?.addEventListener('click', ()=> $('profilePhoto').click());
  $('profilePhoto')?.addEventListener('change', e => previewImage(e.target.files[0]));

  // -------- confirm password UI --------
  $('password_confirmation')?.addEventListener('input', function(){
    const ok = ($('password').value === this.value) || !this.value;
    this.classList.toggle('border-red-500', !ok);
    this.classList.toggle('border-2', !ok);
  });

  // -------- cancel --------
  $('cancelBtn')?.addEventListener('click', ()=> location.replace('/dashboard'));

  // -------- submit to backend --------
  $('profileForm')?.addEventListener('submit', async (e)=>{
    e.preventDefault();

    const pw  = $('password').value;
    const cpw = $('password_confirmation').value;
    if (pw && pw !== cpw){ showAlert('err','Passwords do not match.'); return; }

    const fd = new FormData();
    fd.set('token', getToken());              // some backends also read from POST
    fd.set('name', $('name').value.trim());
    if (pw) fd.set('password', pw);
    const file = $('profilePhoto').files[0];
    if (file) fd.set('profile_photo', file);

    const btn = $('saveBtn');
    btn.disabled = true; btn.classList.add('opacity-70','cursor-not-allowed');

    try{
      const res  = await fetch(api.update, { method:'POST', headers: bearerHeaders(), body: fd });
      const text = await res.text(); let data;
      try { data = JSON.parse(text); } catch { throw new Error(text); }

      if (data.status === 'success'){
        showAlert('ok','Profile updated successfully.');

        // Persist a tiny cache so avatar shows next load even if JWT isn't rotated yet
        const newUser = data.user || {};
        const cached = readCache();
        writeCache({
          name: newUser.name || cached.name || '',
          email: newUser.email || cached.email || '',
          profile_picture: newUser.profile_picture || cached.profile_picture || '',
          updated_at: newUser.updated_at || new Date().toISOString()
        });

        // If backend issued a new token, store it
        if (data.token) localStorage.setItem(TOKEN_KEY, data.token);

        // Reflect immediately in UI
        if (newUser.name) {
          if ($('sidebarName')) $('sidebarName').textContent = newUser.name;
          if ($('name')) $('name').value = newUser.name;
        }
        if (newUser.profile_picture) setAvatarAll(newUser.profile_picture);
        if (newUser.updated_at) setLastUpdated(newUser.updated_at);

      } else {
        showAlert('err', data.message || 'Failed to update profile.');
      }
    }catch(err){
      console.error(err);
      showAlert('err','Server error while updating profile.');
    }finally{
      btn.disabled = false; btn.classList.remove('opacity-70','cursor-not-allowed');
    }
  });

  // -------- logout --------
  $('logoutBtn')?.addEventListener('click', ()=>{
    localStorage.removeItem(TOKEN_KEY);
    localStorage.removeItem(CACHE_KEY);
    location.replace('/login');
  });

  // init
  hydrateProfile();
</script>



</body>
</html>
