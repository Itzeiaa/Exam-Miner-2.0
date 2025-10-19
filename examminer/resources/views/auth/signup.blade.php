<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Exam Miner 2.0 - Sign Up</title>
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
</head>
<body style="padding:10px" class="min-h-screen flex items-center justify-center relative overflow-hidden">
    <div class="absolute inset-0 gradient-animated"></div>

    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute top-1/4 right-1/4 w-96 h-96 bg-gradient-to-r from-blue-500 via-blue-400 to-blue-300 rounded-full opacity-15 blur-3xl animate-pulse"></div>
        <div class="absolute bottom-1/4 left-1/4 w-80 h-80 bg-gradient-to-r from-indigo-500 via-blue-500 to-cyan-400 rounded-full opacity-10 blur-2xl animate-bounce"></div>
        <div class="absolute top-1/2 right-1/6 w-64 h-64 bg-gradient-to-r from-blue-600 to-blue-400 rounded-full opacity-8 blur-xl animate-pulse"></div>
    </div>

    <div class="relative z-10 w-full max-w-md">
        <a href="/" class="flex items-center mb-8 hover:opacity-80 transition-opacity duration-200">
            <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center mr-3 shadow-lg">
                <i class="fas fa-graduation-cap text-blue-500 text-xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-white drop-shadow-lg">Exam Miner 2.0</h1>
        </a>

        <div class="glass-effect rounded-2xl shadow-2xl p-8 border border-white border-opacity-30">
            <h2 class="text-2xl font-bold text-white text-center mb-8">Sign up</h2>

            <form id="signupForm" onsubmit="handleSignup(event)">
                {{-- @csrf (not posting to Laravel) --}}

                <div class="mb-4">
                    <input type="text" name="name" placeholder="Full Name"
                           class="w-full px-4 py-3 bg-white bg-opacity-90 backdrop-blur-sm border border-white border-opacity-50 rounded-xl text-gray-800 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 focus:border-transparent"
                           required />
                </div>

                <div class="mb-4">
                    <input id="email" type="email" name="email" placeholder="Email"
                           class="w-full px-4 py-3 bg-white bg-opacity-90 backdrop-blur-sm border border-white border-opacity-50 rounded-xl text-gray-800 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 focus:border-transparent"
                           required />
                </div>
                <div id="emailError" class="hidden mt-2 mb-3 text-red-600 text-sm font-semibold">Please enter a valid email address.</div>

                {{-- Your API expects a username --}}
                <div class="mb-4">
                    <input type="text" name="username" placeholder="Username"
                           class="w-full px-4 py-3 bg-white bg-opacity-90 backdrop-blur-sm border border-white border-opacity-50 rounded-xl text-gray-800 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 focus:border-transparent"
                           required />
                </div>

                <div class="mb-4">
                    <input type="password" name="password" id="password" placeholder="Password"
                           class="w-full px-4 py-3 bg-white bg-opacity-90 backdrop-blur-sm border border-white border-opacity-50 rounded-xl text-gray-800 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 focus:border-transparent"
                           required />
                </div>

                <div class="mb-1">
                    <input type="password" id="confirmPassword" placeholder="Confirm Password"
                           class="w-full px-4 py-3 bg-white bg-opacity-90 backdrop-blur-sm border border-white border-opacity-50 rounded-xl text-gray-800 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 focus:border-transparent"
                           required />
                </div>
                <div id="passwordError" class="mb-3 hidden text-red-600 text-sm font-semibold">Passwords do not match.</div>

                <div id="signupMsg" class="text-red-600 font-semibold text-sm mb-3"></div>

                <button type="submit"
                        class="w-full bg-white text-blue-600 py-3 rounded-xl font-bold hover:bg-gray-100 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 mb-4">
                    Submit
                </button>

                <div class="text-center text-sm text-white opacity-80">
                    Already have an account?
                    <a href="/login" class="text-white hover:underline font-semibold">Log In</a>
                </div>
            </form>
            <div class="text-center text-sm text-white opacity-80 my-3">
                    or
                </div>
                
                <div class="text-center mt-3">
                    <!-- Simple link (server generates Google URL and redirects) -->
                    <a href="/api/google_start.php"
                       class="w-full bg-white text-blue-600 py-3 rounded-xl font-bold hover:bg-gray-100 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 inline-flex items-center justify-center">
                      <img src="/images/g-logo.png" alt="" style="width:18px;height:18px;margin-right:8px;">Continue with Google</a>
                </div>
            
        </div>
        <br/><br/><br/><br/><br/>
    </div>

    <script>
    const api = {
      login : '/api/login.php',
      signup: '/api/signup.php'
    };

    function validatePasswords() {
      const pw  = document.getElementById('password').value;
      const cpw = document.getElementById('confirmPassword').value;
      const err = document.getElementById('passwordError');
      const cpwInput = document.getElementById('confirmPassword');
      if (cpw && pw !== cpw) {
        err.classList.remove('hidden');
        cpwInput.classList.add('border-red-500');
      } else {
        err.classList.add('hidden');
        cpwInput.classList.remove('border-red-500');
      }
      return pw === cpw;
    }

    document.getElementById('password').addEventListener('input', validatePasswords);
    document.getElementById('confirmPassword').addEventListener('input', validatePasswords);

    function validateEmailField(){
      const input = document.getElementById('email');
      const err   = document.getElementById('emailError');
      const ok    = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test((input.value||'').trim());
      if (!ok) { err.classList.remove('hidden'); input.classList.add('border-red-500'); }
      else { err.classList.add('hidden'); input.classList.remove('border-red-500'); }
      return ok;
    }
    
      function passwordPolicyOk(pw) {
        const rules = [
          v => v.length >= 6,
          v => /[A-Z]/.test(v),
          v => /[a-z]/.test(v),
          v => /\d/.test(v),
          v => /[^A-Za-z0-9]/.test(v),
          v => !/\s/.test(v)
        ];
        return rules.every(r => r(pw));
      }

    document.getElementById('email').addEventListener('input', validateEmailField);

    async function handleSignup(e){
      e.preventDefault();
      const form = e.target;
      const msg  = document.getElementById('signupMsg');
      msg.textContent='';

      // Email + password validations
      const email = form.querySelector('input[name="email"]').value.trim();
      const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
      if (!emailOk) {
        validateEmailField();
        msg.textContent = 'Please enter a valid email address (must contain @).';
        return;
      }

      if (!validatePasswords()) { msg.textContent = 'Passwords do not match'; return; }
      
       const pw = form.querySelector('#password').value;
          if (!passwordPolicyOk(pw)) {
            msg.textContent = 'Password must be ≥6 chars and include an uppercase, lowercase, number, and symbol (no spaces).';
            return;
          }

      const fd = new FormData(form);
      // Only send what your API expects. Phone can be sent if your API supports it.
      const payload = new URLSearchParams();
      ['name','email','username','password','phone'].forEach(k => { if (fd.get(k)) payload.set(k, fd.get(k)); });

      try{
        const res  = await fetch(api.signup, {
          method:'POST',
          headers: { 'Content-Type':'application/x-www-form-urlencoded' },
          body: payload
        });
        const text = await res.text();
        let data; try { data = JSON.parse(text); } catch { throw new Error(text); }

        if (data.status === 'success'){
          const email = fd.get('email');
          // your original flow:
          location.href = `confirm_otp.html?user_id=${data.user_id}&username=${data.username}&email=${encodeURIComponent(email)}`;
        } else {
          msg.textContent = data.message || 'Signup failed';
        }
      }catch(err){
        msg.textContent = 'Server error';
        console.error('Signup error:', err);
      }
    }
    </script>

    <style>
      @keyframes gradientShift { 0%{background-position:0% 50%} 50%{background-position:100% 50%} 100%{background-position:0% 50%} }
      @keyframes pulse { 0%,100%{transform:scale(1);opacity:1} 50%{transform:scale(1.05);opacity:.8} }
      .gradient-animated { background:linear-gradient(-45deg,#1e3a8a,#3b82f6,#60a5fa,#93c5fd,#1e40af,#1d4ed8); background-size:400% 400%; animation:gradientShift 15s ease infinite }
      .glass-effect{ backdrop-filter:blur(20px); background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.2) }
      .animate-pulse{ animation:pulse 3s ease-in-out infinite }
      .animate-bounce{ animation:bounce 2s infinite }
      /* Force critical error color if CSS pipeline isn't rebuilt */
      #signupMsg { color: #ef4444; }
      #emailError { color: #ef4444; }
      #passwordError { color: #ef4444; font-weight: 600; }
      .border-red-500 { border-color: #ef4444 !important; }
      /* keep subtle theme spacing */
    </style>
</body>
</html>
