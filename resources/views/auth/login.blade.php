<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DCFM</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&family=DM+Serif+Display&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['"DM Sans"', 'sans-serif'], serif: ['"DM Serif Display"', 'serif'], mono: ['"JetBrains Mono"', 'monospace'] },
                    colors: {
                        darkbg: '#0a0f1e',
                        darkcard: '#111827',
                        accent: { blue: '#1d4ed8', purple: '#7c3aed' }
                    }
                }
            }
        }
    </script>
    <style>
        .grid-bg { background-image: linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px), linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px); background-size: 30px 30px; }
        .glass-card { background: rgba(17, 24, 39, 0.8); backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,0.1); }
        .btn-gradient { background: linear-gradient(to right, #1d4ed8, #7c3aed); transition: all 0.3s; }
        .btn-gradient:hover { box-shadow: 0 0 20px rgba(124, 58, 237, 0.5); transform: translateY(-2px); }
        [x-cloak] { display: none !important; }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.4/gsap.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
</head>
<body x-data="loginForm()" x-init="init()" class="bg-darkbg text-white h-screen overflow-hidden relative">

    <!-- Atmospheric Glow -->
    <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-accent-blue/20 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-1/4 right-1/4 w-[500px] h-[500px] bg-accent-purple/20 rounded-full blur-[150px] pointer-events-none"></div>
    <div class="absolute inset-0 grid-bg pointer-events-none"></div>

    <div class="flex items-center justify-center h-full relative z-10 p-4">
        <div class="glass-card w-full max-w-md rounded-2xl p-8 shadow-2xl login-card">
            <div class="text-center mb-8">
                <div class="text-4xl mb-3">⚖️</div>
                <h1 class="font-serif text-3xl">DCFM System</h1>
                <p class="text-gray-400 mt-2">Differentiated Case Flow Management</p>
            </div>

            <div x-show="error" x-cloak class="bg-red-500/20 border border-red-500/50 text-red-200 px-4 py-3 rounded-lg mb-6 text-sm" x-text="error"></div>

            <form @submit.prevent="submitForm" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Email <span class="text-red-400">*</span></label>
                    <input type="email" x-model="form.email" required
                           class="w-full bg-black/30 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-accent-purple focus:border-transparent transition text-white placeholder-gray-500"
                           placeholder="Enter your email">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Password <span class="text-red-400">*</span></label>
                    <input type="password" x-model="form.password" required
                           class="w-full bg-black/30 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-accent-purple focus:border-transparent transition text-white placeholder-gray-500"
                           placeholder="Enter your password">
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" x-model="form.remember" class="rounded border-gray-700 bg-black/30 text-accent-purple focus:ring-accent-purple w-4 h-4">
                        <span class="text-sm text-gray-400">Remember me</span>
                    </label>
                    <a href="#" class="text-sm text-accent-purple hover:text-accent-blue transition">Forgot password?</a>
                </div>

                <button type="submit" :disabled="loading"
                        class="btn-gradient w-full rounded-lg py-3 font-semibold text-white mt-4 flex justify-center items-center gap-2">
                    <span x-show="!loading">Sign In</span>
                    <svg x-show="loading" x-cloak class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-gray-800 text-center">
                <p class="text-sm text-gray-400">Don't have an account? <a href="/register" class="text-accent-purple font-medium hover:text-white transition">Register</a></p>
                
                <div class="mt-6 flex flex-wrap justify-center gap-2">
                    <button @click="fill('admin@dcfm.court', 'password')" class="text-xs bg-gray-800 hover:bg-gray-700 px-2 py-1 rounded transition">Admin Demo</button>
                    <button @click="fill('judge@dcfm.court', 'password')" class="text-xs bg-gray-800 hover:bg-gray-700 px-2 py-1 rounded transition">Judge Demo</button>
                    <button @click="fill('lawyer@dcfm.court', 'password')" class="text-xs bg-gray-800 hover:bg-gray-700 px-2 py-1 rounded transition">Lawyer Demo</button>
                    <button @click="fill('clerk@dcfm.court', 'password')" class="text-xs bg-gray-800 hover:bg-gray-700 px-2 py-1 rounded transition">Clerk Demo</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function loginForm() {
            return {
                form: { email: '', password: '', remember: false },
                loading: false,
                error: null,
                
                init() {
                    gsap.fromTo('.login-card', { y: 20, opacity: 0 }, { y: 0, opacity: 1, duration: 0.8, ease: 'power3.out', delay: 0.2 });
                },
                
                fill(email, pass) {
                    this.form.email = email;
                    this.form.password = pass;
                },
                
                async submitForm() {
                    this.loading = true; this.error = null;
                    try {
                        const res = await fetch('/api/auth/login', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                            body: JSON.stringify(this.form)
                        });
                        const data = await res.json();
                        
                        if (data.success) {
                            localStorage.setItem('dcfm_user', JSON.stringify(data.data.user));
                            localStorage.setItem('dcfm_token', data.data.token);
                            window.location.href = '/dashboard';
                        } else {
                             this.error = data.message || 'Invalid credentials';
                             gsap.fromTo('.login-card', { x: -10 }, { x: 0, duration: 0.4, ease: 'elastic.out(1, 0.3)' });
                        }
                    } catch (e) {
                         this.error = 'Network error. Please try again.';
                    }
                    this.loading = false;
                }
            }
        }
    </script>
</body>
</html>
