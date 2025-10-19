<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Exam Miner 2.0 - Login</title>
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
            <h2 class="text-2xl font-bold text-white text-center mb-8">Log In</h2>

            <form id="loginForm" onsubmit="handleLogin(event)">
                {{-- @csrf  (not needed since we are NOT posting to Laravel) --}}

                <div class="mb-6">
                    <input
                        type="text"
                        name="username"
                        placeholder="Email or Username"
                        class="w-full px-4 py-3 bg-white bg-opacity-90 backdrop-blur-sm border border-white border-opacity-50 rounded-xl text-gray-800 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 focus:border-transparent"
                        required
                    />
                </div>

                <div class="mb-6">
                    <input
                        type="password"
                        name="password"
                        placeholder="Password"
                        class="w-full px-4 py-3 bg-white bg-opacity-90 backdrop-blur-sm border border-white border-opacity-50 rounded-xl text-gray-800 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 focus:border-transparent"
                        required
                    />
                </div>

                <div id="loginMsg" class="text-red-600 font-semibold text-sm mb-3"></div>

                <button type="submit"
                    class="w-full bg-white text-blue-600 py-3 rounded-xl font-bold hover:bg-gray-100 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 mb-4">
                    Log In
                </button>

                <div class="text-center mb-6">
                    <a href="/recover" class="text-white text-sm hover:underline opacity-80 hover:opacity-100">Forgot Password?</a>
                </div>

                <div class="text-center text-sm text-white opacity-80 mb-3">
                    Don't have an account?
                    <a href="/signup" class="text-white hover:underline font-semibold">Sign Up</a>
                </div>
                
                <div class="text-center text-sm text-white opacity-80 my-3">
                    or
                </div>
                
                <div class="text-center mt-3">
                    <!-- Simple link (server generates Google URL and redirects) -->
                    <a href="/api/google_start.php"
                       class="w-full bg-white text-blue-600 py-3 rounded-xl font-bold hover:bg-gray-100 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 inline-flex items-center justify-center">
                      <img src="/images/g-logo.png" alt="" style="width:18px;height:18px;margin-right:8px;">Continue with Google</a>
                </div>
            </form>
        </div>
    </div>

    <script>
    const api = {
      login : '/api/login.php',
      signup: '/api/signup.php'
    };

    // Redirect if already logged in
    if (localStorage.getItem('jwt_token')) location.href = '/dashboard';

    async function handleLogin(e){
      e.preventDefault();
      const form = e.target;
      const msg  = document.getElementById('loginMsg');
      msg.textContent = '';

      const fd = new FormData(form);

      // If user typed an email, your API might accept 'username' or 'email'.
      const userInput = fd.get('username') || '';
      const payload   = new URLSearchParams();
      payload.set('username', userInput);
      payload.set('email', userInput); // safe if API accepts email, ignored otherwise
      payload.set('password', fd.get('password'));

      try{
        const res  = await fetch(api.login, { method:'POST', body: payload });
        const text = await res.text(); // help debug weird responses
        let data;
        try { data = JSON.parse(text); } catch { throw new Error(text); }

        if (data.status === 'success'){
          localStorage.setItem('jwt_token', data.access_token);   // now the SMALL token
          localStorage.setItem('profile_cache', JSON.stringify(data.ui || {}));
          location.href = '/dashboard';
        } else if (data.status === 'unverified'){
          location.href = data.redirect; // e.g. /confirm_otp.html?... (your API response)
        } else {
          msg.textContent = data.message || 'Login failed';
        }
      }catch(err){
        msg.textContent = 'Server error';
        console.error('Login error:', err);
      }
    }
    </script>

    <style>
      @keyframes gradientShift { 0% { background-position: 0% 50% } 50% { background-position: 100% 50% } 100% { background-position: 0% 50% } }
      @keyframes pulse { 0%,100% { transform: scale(1); opacity: 1 } 50% { transform: scale(1.05); opacity: .8 } }
      .gradient-animated { background: linear-gradient(-45deg,#1e3a8a,#3b82f6,#60a5fa,#93c5fd,#1e40af,#1d4ed8); background-size: 400% 400%; animation: gradientShift 15s ease infinite }
      .glass-effect { backdrop-filter: blur(20px); background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.2) }
      .animate-pulse { animation: pulse 3s ease-in-out infinite }
      .animate-bounce { animation: bounce 2s infinite }
      /* Ensure error text shows red even if Tailwind isn’t rebuilt */
      #loginMsg { color: #ef4444; }
    </style>
</body>
</html>
