<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Exam Miner 2.0 - Recover Account</title>
  @vite('resources/css/app.css')
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body style="padding:10px" class="min-h-screen flex items-center justify-center relative overflow-hidden">
  <!-- Blue gradient background -->
  <div class="absolute inset-0 gradient-animated"></div>

  <!-- Blue-themed background elements -->
  <div class="absolute inset-0 overflow-hidden">
    <div class="absolute top-1/4 right-1/4 w-96 h-96 bg-gradient-to-r from-blue-500 via-blue-400 to-blue-300 rounded-full opacity-15 blur-3xl animate-pulse"></div>
    <div class="absolute bottom-1/4 left-1/4 w-80 h-80 bg-gradient-to-r from-indigo-500 via-blue-500 to-cyan-400 rounded-full opacity-10 blur-2xl animate-bounce"></div>
    <div class="absolute top-1/2 right-1/6 w-64 h-64 bg-gradient-to-r from-blue-600 to-blue-400 rounded-full opacity-8 blur-xl animate-pulse"></div>
  </div>

  <!-- Main content -->
  <div class="relative z-10 w-full max-w-md">
    <!-- Logo and branding -->
    <a href="/" class="flex items-center mb-8 hover:opacity-80 transition-opacity duration-200">
      <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center mr-3 shadow-lg">
        <i class="fas fa-graduation-cap text-blue-500 text-xl"></i>
      </div>
      <h1 class="text-3xl font-bold text-white drop-shadow-lg">Exam Miner 2.0</h1>
    </a>

    <!-- Recovery card -->
    <div class="glass-effect rounded-2xl shadow-2xl p-8 border border-white border-opacity-30">
      <h2 class="text-2xl font-bold text-white text-center mb-8">Recover Account</h2>

      <!-- Blade errors -->
      @if ($errors->any())
        <div class="bg-red-500 bg-opacity-20 backdrop-blur-sm border border-red-400 border-opacity-50 text-red-100 px-4 py-3 rounded-lg mb-6">
          <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <!-- Blade success -->
      @if (session('success'))
        <div class="bg-green-500 bg-opacity-20 backdrop-blur-sm border border-green-400 border-opacity-50 text-green-100 px-4 py-3 rounded-lg mb-6">
          {{ session('success') }}
        </div>
      @endif

      <!-- JS flash box -->
      <div id="flash" class="hidden mb-4 px-4 py-3 rounded-lg text-sm"></div>

      <form method="POST" action="/recover" id="recoverForm">
        @csrf

        <!-- Email -->
        <div class="mb-6">
          <input
            type="email"
            name="email"
            placeholder="Enter your email"
            class="w-full px-4 py-3 bg-white bg-opacity-90 backdrop-blur-sm border border-white border-opacity-50 rounded-xl text-gray-800 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 focus:border-transparent"
            required
          >
        </div>

        <!-- OTP + Send -->
        <div class="mb-4">
          <div class="flex space-x-3">
            <input style="width: 100px" type="text" name="otp" placeholder="OTP" class="flex-1 px-4 py-3 bg-white bg-opacity-90 backdrop-blur-sm border border-white border-opacity-50 rounded-xl text-gray-800 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 focus:border-transparent" maxlength="6">
            <button
              type="button"
              id="sendOtpBtn"
              class="px-6 py-3 bg-white bg-opacity-90 rounded-xl text-blue-600 border border-white border-opacity-50 hover:bg-white hover:bg-opacity-100 transition-all duration-300 font-medium shadow-lg hover:shadow-xl transform hover:scale-105"
            >
              Send OTP
            </button>
          </div>
        </div>

        <!-- Resend timer -->
        <div class="text-right mb-6">
          <p id="resendTimer" class="text-sm text-white opacity-80 hidden">
            Resend in <span id="timer">1:00</span> mins...
          </p>
        </div>

        <!-- Submit -->
        <button
          type="submit"
          class="w-full bg-white text-blue-600 py-3 rounded-xl font-bold hover:bg-gray-100 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 mb-4"
        >
          Submit
        </button>

        <!-- Back -->
        <button
          type="button"
          onclick="window.history.back()"
          class="w-full glass-effect text-white py-3 rounded-xl font-medium hover:bg-white hover:bg-opacity-20 transition-all duration-300 border border-white border-opacity-30 flex items-center justify-center shadow-lg hover:shadow-xl transform hover:scale-105"
        >
          <i class="fas fa-arrow-left mr-2"></i>
          Go back
        </button>
      </form>
    </div>
  </div>

  <script>
  (function () {
    // Elements
    const form       = document.getElementById('recoverForm');
    const emailInput = form.querySelector('input[name="email"]');
    const otpInput   = form.querySelector('input[name="otp"]');
    const sendBtn    = document.getElementById('sendOtpBtn');
    const submitBtn  = form.querySelector('button[type="submit"]');
    const flashBox   = document.getElementById('flash');
    const timerWrap  = document.getElementById('resendTimer');
    const timerSpan  = document.getElementById('timer');

    // Helpers
    function flash(type, msg) {
      if (!flashBox) { alert(msg); return; }
      flashBox.textContent = msg;
      flashBox.className = 'mb-4 px-4 py-3 rounded-lg text-sm ' + (
        type === 'ok'
          ? 'bg-green-50 border border-green-200 text-green-700'
          : 'bg-red-50 border border-red-200 text-red-700'
      );
      flashBox.classList.remove('hidden');
      clearTimeout(flashBox._t);
      flashBox._t = setTimeout(() => flashBox.classList.add('hidden'), 5000);
    }

    function lockBtn(btn, text) {
      btn.disabled = true;
      btn.dataset.old = btn.textContent;
      btn.textContent = text;
      btn.classList.add('bg-white','bg-opacity-10','text-white','text-opacity-50','cursor-not-allowed');
      btn.classList.remove('shadow-lg','hover:shadow-xl','transform','hover:scale-105');
    }
    function unlockBtn(btn) {
      btn.disabled = false;
      btn.textContent = btn.dataset.old || btn.textContent;
      btn.classList.remove('bg-white','bg-opacity-10','text-white','text-opacity-50','cursor-not-allowed','bg-green-500','bg-opacity-20','text-green-100');
      btn.classList.add('bg-white','bg-opacity-90','text-blue-600','shadow-lg','hover:shadow-xl','transform','hover:scale-105');
    }
    function markSent(btn) {
      btn.textContent = 'OTP Sent';
      btn.classList.remove('bg-white','bg-opacity-10','text-white','text-opacity-50','cursor-not-allowed');
      btn.classList.add('bg-green-500','bg-opacity-20','text-green-100','shadow-lg','hover:shadow-xl','transform','hover:scale-105');
    }

    // Countdown
    let countdown = 0, tInt = null;
    function startCountdown(sec = 60) {
      countdown = sec;
      timerWrap.classList.remove('hidden');
      clearInterval(tInt);
      tInt = setInterval(() => {
        const m = Math.floor(countdown/60);
        const s = String(countdown%60).padStart(2,'0');
        timerSpan.textContent = `${m}:${s}`;
        if (countdown <= 0) {
          clearInterval(tInt);
          timerWrap.classList.add('hidden');
          unlockBtn(sendBtn);
        }
        countdown--;
      }, 1000);
    }

    // Numeric-only OTP
    otpInput.addEventListener('input', () => {
      otpInput.value = otpInput.value.replace(/[^0-9]/g, '');
    });

    // Send OTP (recovery)
    sendBtn.addEventListener('click', async () => {
      const email = emailInput.value.trim();
      if (!email) { flash('err','Please enter your email first.'); return; }

      lockBtn(sendBtn, 'Sending…');

      const fd = new FormData();
      fd.append('action', 'send_recovery');
      fd.append('email', email);

      try {
        const res  = await fetch('https://exam-miner.com/api/mail.php', { method:'POST', body: fd });
        const data = await res.json().catch(() => ({}));
        if (data.status === 'success') {
          markSent(sendBtn);
          flash('ok', data.message || 'Recovery OTP sent.');
          startCountdown(60);
        } else {
          unlockBtn(sendBtn);
          flash('err', data.message || 'Unable to send OTP for recovery.');
        }
      } catch {
        unlockBtn(sendBtn);
        flash('err','Network error. Please try again.');
      }
    });

    // Verify OTP (submit)
    form.addEventListener('submit', async (e) => {
      e.preventDefault(); // always prevent reload
      const email = emailInput.value.trim();
      const otp   = otpInput.value.trim();

      if (!email) { flash('err','Email is required.'); return; }
      if (!/^\d{6}$/.test(otp)) { flash('err','Please enter the 6-digit OTP.'); return; }

      lockBtn(submitBtn, 'Verifying…');

      const fd = new FormData();
      fd.append('action', 'verify_recovery');
      fd.append('email', email);
      fd.append('otp', otp);

      try {
        const res  = await fetch('https://exam-miner.com/api/mail.php', { method:'POST', body: fd });
        const data = await res.json().catch(() => ({}));
        if (data.status === 'success') {
          if (data.recovery_token) localStorage.setItem('recovery_token', data.recovery_token);
          flash('ok','OTP verified. Redirecting to reset password…');
          setTimeout(() => {
            location.href = `/reset-password?email=${encodeURIComponent(email)}`;
          }, 700);
        } else {
          flash('err', data.message || 'Invalid or expired OTP.');
          unlockBtn(submitBtn);
        }
      } catch {
        flash('err','Network error. Please try again.');
        unlockBtn(submitBtn);
      }
    });
  })();
  </script>

  <!-- Blue theme styles -->
  <style>
    @keyframes gradientShift {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }
    @keyframes pulse {
      0%, 100% { transform: scale(1); opacity: 1; }
      50% { transform: scale(1.05); opacity: 0.8; }
    }
    .gradient-animated {
      background: linear-gradient(-45deg, #1e3a8a, #3b82f6, #60a5fa, #93c5fd, #1e40af, #1d4ed8);
      background-size: 400% 400%;
      animation: gradientShift 15s ease infinite;
    }
    .glass-effect {
      backdrop-filter: blur(20px);
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
    .animate-pulse { animation: pulse 3s ease-in-out infinite; }
    .animate-bounce { animation: bounce 2s infinite; }
  </style>
</body>
</html>
