<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password • Exam Miner 2.0</title>
  @vite('resources/css/app.css')
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="min-h-screen flex items-center justify-center relative overflow-hidden">
  <div class="absolute inset-0 gradient-animated"></div>

  <div class="relative z-10 w-full max-w-md">
    <a href="/" class="flex items-center mb-8 hover:opacity-80 transition-opacity duration-200">
      <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center mr-3 shadow-lg">
        <i class="fas fa-graduation-cap text-blue-500 text-xl"></i>
      </div>
      <h1 class="text-3xl font-bold text-white drop-shadow-lg">Exam Miner 2.0</h1>
    </a>

    <div class="glass-effect rounded-2xl shadow-2xl p-8 border border-white border-opacity-30">
      <h2 class="text-2xl font-bold text-white text-center mb-6">Reset Password</h2>

      <div id="flash" class="hidden mb-4 px-4 py-3 rounded-lg text-sm"></div>

      <form id="resetForm" class="space-y-4">
        <div>
          <label class="block text-white text-sm mb-1">Email</label>
          <input id="email" type="email" class="w-full px-4 py-3 bg-white/90 border border-white/50 rounded-xl text-gray-800"
                 readonly>
        </div>

        <div>
          <label class="block text-white text-sm mb-1">New Password</label>
          <input id="password" type="password" minlength="6" class="w-full px-4 py-3 bg-white/90 border border-white/50 rounded-xl text-gray-800" required>
        </div>

        <div>
          <label class="block text-white text-sm mb-1">Confirm Password</label>
          <input id="confirm" type="password" minlength="6" class="w-full px-4 py-3 bg-white/90 border border-white/50 rounded-xl text-gray-800" required>
        </div>

        <button type="submit"
          class="w-full bg-white text-blue-600 py-3 rounded-xl font-bold hover:bg-gray-100 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
          Update Password
        </button>

        <button type="button" onclick="location.href='/login'"
          class="w-full glass-effect text-white py-3 rounded-xl font-medium border border-white/30 mt-2">
          Back to Login
        </button>
      </form>
    </div>
  </div>

  <script>
    (function () {
      const flashBox = document.getElementById('flash');
      function flash(type, msg) {
        flashBox.textContent = msg;
        flashBox.className = 'mb-4 px-4 py-3 rounded-lg text-sm ' + (type === 'ok'
          ? 'bg-green-50 border border-green-200 text-green-700'
          : 'bg-red-50 border border-red-200 text-red-700');
        flashBox.classList.remove('hidden');
        clearTimeout(flashBox._t);
        flashBox._t = setTimeout(()=>flashBox.classList.add('hidden'), 5000);
      }

      const url = new URL(location.href);
      const email = url.searchParams.get('email') || '';
      document.getElementById('email').value = email;

      const form = document.getElementById('resetForm');
      const pass = document.getElementById('password');
      const conf = document.getElementById('confirm');

      form.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!email) { flash('err','Missing email. Go back and try again.'); return; }
        if (pass.value.length < 6) { flash('err','Password must be at least 6 characters.'); return; }
        if (pass.value !== conf.value) { flash('err','Passwords do not match.'); return; }

        const token = localStorage.getItem('recovery_token');
        if (!token) { flash('err','Recovery token missing or expired. Please request a new OTP.'); return; }

        const fd = new FormData();
        fd.append('action', 'reset_password');
        fd.append('email', email);
        fd.append('password', pass.value);
        fd.append('recovery_token', token);

        try {
          const res = await fetch('https://exam-miner.com/api/mail.php', { method:'POST', body: fd });
          const data = await res.json().catch(()=>({}));
          if (data.status === 'success') {
            localStorage.removeItem('recovery_token');
            flash('ok', data.message || 'Password updated. Redirecting to login…');
            setTimeout(()=> location.href = '/login', 800);
          } else {
            flash('err', data.message || 'Unable to update password.');
          }
        } catch {
          flash('err','Network error. Please try again.');
        }
      });
    })();
  </script>

  <style>
    @keyframes gradientShift { 0%{background-position:0% 50%}50%{background-position:100% 50%}100%{background-position:0% 50%} }
    .gradient-animated { background: linear-gradient(-45deg,#1e3a8a,#3b82f6,#60a5fa,#93c5fd,#1e40af,#1d4ed8); background-size:400% 400%; animation: gradientShift 15s ease infinite; }
    .glass-effect { backdrop-filter: blur(20px); background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); }
  </style>
</body>
</html>
