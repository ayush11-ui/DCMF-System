<!DOCTYPE html>
<html lang="en" class="dark">
<!-- Same head and styling as login -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - DCFM</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600&family=DM+Serif+Display&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: { fontFamily: { sans: ['"DM Sans"', 'sans-serif'], serif: ['"DM Serif Display"', 'serif'] }, colors: { darkbg: '#0a0f1e', darkcard: '#111827', accent: { blue: '#1d4ed8', purple: '#7c3aed' } } }
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
<body x-data="registerForm()" x-init="init()" class="bg-darkbg text-white h-screen overflow-hidden relative">

    <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-accent-blue/20 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-1/4 right-1/4 w-[500px] h-[500px] bg-accent-purple/20 rounded-full blur-[150px] pointer-events-none"></div>
    <div class="absolute inset-0 grid-bg pointer-events-none"></div>

    <div class="flex items-center justify-center h-full relative z-10 p-4">
        <div class="glass-card w-full max-w-2xl rounded-2xl p-8 shadow-2xl login-card h-auto max-h-[90vh] overflow-y-auto" style="scrollbar-width: thin;">
            <div class="text-center mb-6">
                <h1 class="font-serif text-3xl">Create Account</h1>
                <p class="text-gray-400 mt-1">Join the DCFM Network</p>
            </div>

            <div x-show="error" x-cloak class="bg-red-500/20 border border-red-500/50 text-red-200 px-4 py-3 rounded-lg mb-6 text-sm" x-text="error"></div>

            <form @submit.prevent="submitForm" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Full Name *</label>
                    <input type="text" x-model="form.name" required class="w-full bg-black/30 border border-gray-700 rounded-lg px-4 py-2.5 focus:outline-none focus:border-accent-purple transition text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Email Base *</label>
                    <input type="email" x-model="form.email" required class="w-full bg-black/30 border border-gray-700 rounded-lg px-4 py-2.5 focus:outline-none focus:border-accent-purple transition text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Password *</label>
                    <input type="password" x-model="form.password" required class="w-full bg-black/30 border border-gray-700 rounded-lg px-4 py-2.5 focus:outline-none focus:border-accent-purple transition text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Confirm Password *</label>
                    <input type="password" x-model="form.password_confirmation" required class="w-full bg-black/30 border border-gray-700 rounded-lg px-4 py-2.5 focus:outline-none focus:border-accent-purple transition text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Phone Number</label>
                    <input type="text" x-model="form.phone" class="w-full bg-black/30 border border-gray-700 rounded-lg px-4 py-2.5 focus:outline-none focus:border-accent-purple transition text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Role *</label>
                    <select x-model="form.role" required class="w-full bg-black/30 border border-gray-700 rounded-lg px-4 py-2.5 focus:outline-none focus:border-accent-purple transition text-white appearance-none">
                        <option value="client">Client (Petitioner/Respondent)</option>
                        <option value="lawyer">Lawyer</option>
                        <option value="judge">Judge</option>
                        <option value="clerk">Clerk</option>
                    </select>
                </div>
                
                <!-- Conditional Fields -->
                <div x-show="form.role === 'lawyer'" class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-medium text-gray-300 mb-1">Bar Registration Number *</label>
                    <input type="text" x-model="form.bar_number" :required="form.role === 'lawyer'" class="w-full bg-black/30 border border-gray-700 rounded-lg px-4 py-2.5 outline-none focus:border-accent-purple text-white">
                </div>
                <div x-show="['judge', 'clerk'].includes(form.role)" class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-medium text-gray-300 mb-1">Court ID / Reference *</label>
                    <input type="text" x-model="form.court_id" :required="['judge', 'clerk'].includes(form.role)" class="w-full bg-black/30 border border-gray-700 rounded-lg px-4 py-2.5 outline-none focus:border-accent-purple text-white">
                </div>

                <div class="col-span-1 md:col-span-2 mt-4">
                    <button type="submit" :disabled="loading" class="btn-gradient w-full rounded-lg py-3 font-semibold text-white flex justify-center items-center gap-2">
                        <span x-show="!loading">Create Account</span>
                        <svg x-show="loading" x-cloak class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </button>
                    <div class="text-center mt-6">
                        <p class="text-sm text-gray-400">Already a user? <a href="/login" class="text-accent-purple font-medium hover:text-white transition">Sign In</a></p>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function registerForm() {
            return {
                form: { name: '', email: '', password: '', password_confirmation: '', phone: '', role: 'client', bar_number: '', court_id: '' },
                loading: false, error: null,
                init() { gsap.fromTo('.login-card', { y: 20, opacity: 0 }, { y: 0, opacity: 1, duration: 0.8, ease: 'power3.out', delay: 0.2 }); },
                async submitForm() {
                    this.loading = true; this.error = null;
                    try {
                        const res = await fetch('/api/auth/register', {
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
                             const errors = data.errors ? Object.values(data.errors).flat().join(' ') : 'Registration failed.';
                             this.error = data.message + ' ' + errors;
                             gsap.fromTo('.login-card', { x: -10 }, { x: 0, duration: 0.4, ease: 'elastic.out(1, 0.3)' });
                        }
                    } catch (e) { this.error = 'Network error.'; }
                    this.loading = false;
                }
            }
        }
    </script>
</body>
</html>
