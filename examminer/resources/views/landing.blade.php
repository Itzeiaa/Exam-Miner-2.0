<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Exam Miner 2.0 - AI-powered exam creation</title>
    @vite('resources/css/app.css')
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="min-h-screen overflow-x-hidden md:overflow-hidden relative" id="landingBody">
    <!-- Modern gradient background -->
    <div class="absolute inset-0 gradient-animated"></div>
    
    <!-- Clean background elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <!-- Blue-themed floating orbs (kept look, fixed invalid utils) -->
        <div class="absolute top-1/4 right-1/4 w-96 h-96 bg-gradient-to-r from-blue-500 via-blue-400 to-blue-300 rounded-full blur-3xl animate-pulse" style="opacity:0.15;"></div>
        <div class="absolute bottom-1/4 left-1/4 w-80 h-80 bg-gradient-to-r from-indigo-500 via-blue-500 to-cyan-400 rounded-full blur-2xl animate-bounce" style="opacity:0.10;"></div>
        <div class="absolute top-1/2 right-[16%] w-64 h-64 bg-gradient-to-r from-blue-600 to-blue-400 rounded-full blur-xl animate-pulse" style="opacity:0.08;"></div>
    </div>

    <!-- Modern header with glass effect -->
    <header class="relative z-10 flex justify-between items-center p-6 glass-effect">
        <!-- Logo and branding -->
        <div class="flex items-center cursor-pointer hover:opacity-80 transition-opacity duration-200" onclick="window.location.reload()">
            <div class="w-10 h-10 sm:w-14 sm:h-14 bg-white rounded-2xl flex items-center justify-center mr-3 sm:mr-4 shadow-xl">
                <img style="width:30px" src="/images/icon.png"></img>
            </div>
           <h1 class="font-bold text-white drop-shadow-lg whitespace-nowrap leading-tight text-[clamp(1.125rem,5.2vw,1.5rem)] sm:text-3xl">Exam Miner 2.0</h1>
        </div>

        <!-- Header buttons (smaller on mobile, original on ≥sm) -->
        <div class="flex space-x-3 sm:space-x-4">
            <a style="padding: 10px" href="/login" class="glass-effect text-white px-4 py-2 sm:px-6 sm:py-3 rounded-xl text-sm sm:text-base font-medium hover:bg-white hover:bg-opacity-20 transition-all duration-300 border border-white border-opacity-30 shadow-lg hover:shadow-xl transform hover:scale-105">
                Log In
            </a>
            <a style="padding: 10px" href="/signup" class="bg-white text-blue-600 px-4 py-2 sm:px-6 sm:py-3 rounded-xl text-sm sm:text-base font-medium hover:bg-gray-100 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                Sign up
            </a>
        </div>
    </header>

    <!-- Main content with modern layout -->
    <main class="relative z-10 flex flex-col-reverse lg:flex-row items-center justify-between px-6 py-12 max-w-7xl mx-auto gap-8">
        <!-- Left side - Enhanced text content -->
        <div class="flex-1 max-w-2xl w-full">
            <div class="animate-fade-in-up">
                <div class="inline-flex items-center bg-white bg-opacity-20 backdrop-blur-sm rounded-full px-4 py-2 mb-6 border border-white border-opacity-30">
                    <div class="w-2 h-2 bg-yellow-400 rounded-full mr-3 animate-pulse"></div>
                    <p class="text-blue-600 text-sm font-bold drop-shadow-lg"></p>
                </div>
                
                <h2 class="text-4xl sm:text-5xl font-bold text-white mb-6 leading-tight drop-shadow-lg">
                    The Best WebApp for<br>
                    <span class="bg-gradient-to-r from-yellow-300 via-orange-300 to-pink-300 bg-clip-text text-transparent animate-pulse">
                        Exam Generation
                    </span>
                </h2>
                
                <p class="text-white text-base sm:text-lg mb-8 opacity-90 leading-relaxed max-w-lg">
                    Transform your learning materials into intelligent exams with our cutting-edge AI technology. 
                    <span class="text-yellow-300 font-semibold">Fast, accurate, and effortless.</span>
                </p>
                
                <!-- Modern feature highlights -->
                <div class="flex flex-wrap gap-4 mb-8">
                    <div class="glass-effect rounded-xl p-4 border border-white border-opacity-30 hover:bg-white hover:bg-opacity-20 transition-all duration-300 transform hover:scale-105">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-gradient-to-r from-yellow-400 to-orange-400 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-star text-white text-sm"></i>
                            </div>
                            <span class="text-white text-sm font-bold drop-shadow-lg">AI-Powered</span>
                        </div>
                    </div>
                    
                    <div class="glass-effect rounded-xl p-4 border border-white border-opacity-30 hover:bg-white hover:bg-opacity-20 transition-all duration-300 transform hover:scale-105">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-gradient-to-r from-green-400 to-emerald-400 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-check text-white text-sm"></i>
                            </div>
                            <span class="text-white text-sm font-bold drop-shadow-lg">Instant Results</span>
                        </div>
                    </div>
                    
                    <div class="glass-effect rounded-xl p-4 border border-white border-opacity-30 hover:bg-white hover:bg-opacity-20 transition-all duration-300 transform hover:scale-105">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-gradient-to-r from-blue-400 to-cyan-400 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-file-alt text-white text-sm"></i>
                            </div>
                            <span class="text-white text-sm font-bold drop-shadow-lg">Multiple Formats</span>
                        </div>
                    </div>
                </div>
                
                <!-- Modern CTA buttons (smaller on mobile only) -->
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="/signup" class="bg-gradient-to-r from-white to-gray-100 text-blue-600 px-6 py-3 sm:px-8 sm:py-4 rounded-xl font-bold text-base sm:text-lg hover:from-gray-100 hover:to-white transition-all duration-300 inline-flex items-center justify-center shadow-xl hover:shadow-2xl transform hover:scale-105">
                        <i class="fas fa-rocket mr-2"></i>
                        Try for free
                    </a>
                    <a href="#features" onclick="enableScrolling(event)" class="glass-effect text-white px-6 py-3 sm:px-8 sm:py-4 rounded-xl font-medium text-base sm:text-lg hover:bg-white hover:bg-opacity-20 transition-all duration-300 inline-flex items-center justify-center border border-white border-opacity-30 shadow-lg hover:shadow-xl transform hover:scale-105">
                        Learn more
                    </a>
                </div>
            </div>
        </div>

        <!-- Right side - Enhanced robot image -->
        <div class="flex-1 flex justify-center items-center w-full">
            <div class="relative animate-float">
                <!-- Responsive circular container that governs ALL layers -->
                <div class="relative robot-size mx-auto">
                    <img src="/images/logo.png" 
                         alt="Exam Miner Robot" 
                         class="relative z-10 w-full h-full object-contain drop-shadow-2xl"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    
                    <!-- Blue-themed layered backgrounds (scale with container) -->
                    <div class="absolute ring-1 rounded-full -z-10 shadow-2xl animate-pulse
                                bg-gradient-to-br from-blue-500 via-blue-400 to-blue-300"></div>
                    <div class="absolute ring-2 rounded-full -z-20 opacity-80
                                bg-gradient-to-br from-blue-400 via-blue-300 to-blue-200"></div>
                    <div class="absolute ring-3 rounded-full -z-30 opacity-60
                                bg-gradient-to-br from-blue-300 via-blue-200 to-blue-100"></div>
                    
                    <!-- Modern floating elements -->
                    <div class="absolute top-12 left-12 w-4 h-4 bg-yellow-300 rounded-full animate-ping opacity-60 shadow-lg"></div>
                    <div class="absolute top-24 right-20 w-3 h-3 bg-green-300 rounded-full animate-pulse opacity-70 shadow-lg"></div>
                    <div class="absolute bottom-20 left-20 w-3 h-3 bg-purple-300 rounded-full animate-bounce opacity-60 shadow-lg"></div>
                    <div class="absolute bottom-24 right-16 w-4 h-4 bg-pink-300 rounded-full animate-ping opacity-50 shadow-lg"></div>
                    <div class="absolute top-1/2 left-8 w-2 h-2 bg-cyan-300 rounded-full animate-pulse opacity-80 shadow-lg"></div>
                    <div class="absolute top-1/3 right-8 w-2 h-2 bg-orange-300 rounded-full animate-bounce opacity-70 shadow-lg"></div>
                    
                    <!-- Fallback content -->
                    <div class="hidden absolute inset-0 rounded-full items-center justify-center shadow-2xl
                                bg-gradient-to-br from-blue-500 via-blue-400 to-blue-300">
                        <div class="text-center">
                            <div class="w-32 h-32 glass-effect rounded-full mx-auto mb-6 flex items-center justify-center border border-white border-opacity-30">
                                <i class="fas fa-robot text-white text-6xl"></i>
                            </div>
                            <p class="text-white text-xl font-bold drop-shadow-lg">Robot Logo</p>
                            <p class="text-white text-sm opacity-80">Place robot-logo.png in public/images/</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Features Section -->
    <section id="features" class="relative z-10 py-20 px-6">
        <div class="max-w-7xl mx-auto">
            <!-- Section Header -->
            <div class="text-center mb-16">
                <div class="inline-flex items-center bg-white bg-opacity-20 backdrop-blur-sm rounded-full px-4 py-2 mb-6 border border-white border-opacity-30">
                    <div class="w-2 h-2 bg-blue-400 rounded-full mr-3 animate-pulse"></div>
                    <p class="text-white text-sm font-medium">Powerful Features</p>
                </div>
                <h2 class="text-4xl font-bold text-white mb-6 drop-shadow-lg">
                    Everything You Need for
                    <span class="bg-gradient-to-r from-blue-300 via-purple-300 to-pink-300 bg-clip-text text-transparent">
                        Perfect Exams
                    </span>
                </h2>
                <p class="text-white text-lg opacity-90 max-w-2xl mx-auto">
                    Discover the comprehensive toolkit that makes exam creation effortless and professional.
                </p>
            </div>

            <!-- Features Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1: AI-Powered Generation -->
                <div class="glass-effect rounded-2xl p-8 border border-white border-opacity-30 hover:bg-white hover:bg-opacity-20 transition-all duration-300 transform hover:scale-105 group">
                    <div class="w-16 h-16 bg-gradient-to-r from-purple-500 to-pink-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-brain text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-4">AI-Powered Generation</h3>
                    <p class="text-white opacity-80 leading-relaxed">
                        Advanced AI algorithms analyze your content and generate intelligent, contextually relevant questions automatically.
                    </p>
                </div>

                <!-- Feature 2: Multiple Question Types -->
                <div class="glass-effect rounded-2xl p-8 border border-white border-opacity-30 hover:bg-white hover:bg-opacity-20 transition-all duration-300 transform hover:scale-105 group">
                    <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-list-ul text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-4">Multiple Question Types</h3>
                    <p class="text-white opacity-80 leading-relaxed">
                        Create multiple choice, true/false, essay, and mixed format questions to test different learning objectives.
                    </p>
                </div>

                <!-- Feature 3: File Format Support -->
                <div class="glass-effect rounded-2xl p-8 border border-white border-opacity-30 hover:bg-white hover:bg-opacity-20 transition-all duration-300 transform hover:scale-105 group">
                    <div class="w-16 h-16 bg-gradient-to-r from-green-500 to-emerald-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-file-upload text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-4">Universal File Support</h3>
                    <p class="text-white opacity-80 leading-relaxed">
                        Upload DOCX, PPT, PDF, and more. Our system extracts content from any format seamlessly.
                    </p>
                </div>

                <!-- Feature 4: Instant Results -->
                <div class="glass-effect rounded-2xl p-8 border border-white border-opacity-30 hover:bg-white hover:bg-opacity-20 transition-all duration-300 transform hover:scale-105 group">
                    <div class="w-16 h-16 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-bolt text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-4">Lightning Fast</h3>
                    <p class="text-white opacity-80 leading-relaxed">
                        Generate comprehensive exams in seconds, not hours. Get your questions ready instantly.
                    </p>
                </div>

                <!-- Feature 5: Multiple Exam Sets -->
                <div class="glass-effect rounded-2xl p-8 border border-white border-opacity-30 hover:bg-white hover:bg-opacity-20 transition-all duration-300 transform hover:scale-105 group">
                    <div class="w-16 h-16 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-copy text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-4">Multiple Exam Sets</h3>
                    <p class="text-white opacity-80 leading-relaxed">
                        Create multiple versions of the same exam with different question orders to prevent cheating.
                    </p>
                </div>

                <!-- Feature 6: Export & Download -->
                <div class="glass-effect rounded-2xl p-8 border border-white border-opacity-30 hover:bg-white hover:bg-opacity-20 transition-all duration-300 transform hover:scale-105 group">
                    <div class="w-16 h-16 bg-gradient-to-r from-red-500 to-pink-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-download text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-4">Easy Export</h3>
                    <p class="text-white opacity-80 leading-relaxed">
                        Download your exams in multiple formats. Print-ready PDFs, Word documents, and more.
                    </p>
                </div>
            </div>

            <!-- Call to Action -->
            <div class="text-center mt-16">
                <a href="/signup" class="bg-gradient-to-r from-blue-500 to-purple-600 text-white px-12 py-4 rounded-xl font-bold text-lg hover:from-blue-600 hover:to-purple-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 inline-flex items-center">
                    <i class="fas fa-plus mr-2"></i>
                    Start Creating Exams Now
                </a>
            </div>
        </div>
    </section>

    <!-- Modern animations and effects -->
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(2deg); }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
        }
        .animate-float { animation: float 6s ease-in-out infinite; }
        .animate-fade-in-up { animation: fadeInUp 1s ease-out; }
        .animate-pulse { animation: pulse 3s ease-in-out infinite; }
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

        /* MOBILE-SAFE ROBOT CIRCLE (no layout overflow) */
        .robot-size{
          width: clamp(220px, 60vw, 400px);
          height: clamp(220px, 60vw, 400px);
        }
        .ring-1{ inset: 0; }
        .ring-2{ inset: 6%; }   /* ≈24px when 400px */
        .ring-3{ inset: 12%; }  /* ≈48px when 400px */

        html, body { overflow-x: hidden; }
        html { scroll-behavior: smooth; }
    </style>

    <script>
        // Ensure page starts at top on load/refresh
        window.addEventListener('load', function() { window.scrollTo(0, 0); });
        window.scrollTo(0, 0);

        function enableScrolling(event) {
            if (event) event.preventDefault();
            document.getElementById('landingBody').classList.remove('overflow-hidden');
            setTimeout(() => {
                document.getElementById('features').scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'start'
                });
            }, 100);
        }
    </script>
</body>
</html>
