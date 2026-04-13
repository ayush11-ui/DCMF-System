<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DCFM Court Management</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&family=DM+Serif+Display&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    
    <!-- CSS / Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"DM Sans"', 'sans-serif'],
                        serif: ['"DM Serif Display"', 'serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        navy: '#0f172a',
                        offwhite: '#f8f7f4',
                        darkbg: '#0a0f1e',
                        darkcard: '#111827',
                        accent: {
                            blue: '#1d4ed8',
                            purple: '#7c3aed',
                            emerald: '#10b981',
                            amber: '#f59e0b',
                            red: '#ef4444'
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background-color: rgba(156, 163, 175, 0.5); border-radius: 20px; }
        
        .light body { background-color: theme('colors.offwhite'); color: theme('colors.gray.900'); }
        .dark body { background-color: theme('colors.darkbg'); color: theme('colors.gray.100'); }
        
        .bento-card {
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: transform 0.2sease, box-shadow 0.2s ease, background-color 0.3s ease;
            background: #ffffff;
            border: 1px solid #e5e7eb;
        }
        .dark .bento-card {
            background: theme('colors.darkcard');
            border-color: #374151;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.5);
        }
        .bento-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .dark .bento-card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5);
        }
        
        /* Subtle dot background for more interesting UI */
        .bg-dots {
            background-image: radial-gradient(rgba(156, 163, 175, 0.3) 1px, transparent 1px);
            background-size: 24px 24px;
        }
        .dark .bg-dots {
            background-image: radial-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px);
        }
        
        .btn-glow { transition: all 0.2s; }
        .btn-glow:hover { transform: translateY(-1px); box-shadow: 0 0 15px currentColor; }
        
        [x-cloak] { display: none !important; }
        
        .toast-enter { transform: translateX(100%); opacity: 0; }
        .toast-enter-active { transform: translateX(0); opacity: 1; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .toast-leave { transform: translateX(0); opacity: 1; }
        .toast-leave-active { transform: translateX(100%); opacity: 0; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    </style>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.4/gsap.min.js"></script>
    <script src="https://unpkg.com/@studio-freight/lenis@1.0.32/dist/lenis.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="appData()" x-init="initApp()" class="antialiased overflow-x-hidden transition-colors duration-300">

    <!-- Toast Notifications -->
    <div class="fixed top-4 right-4 z-50 flex flex-col gap-2">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="toast.visible" 
                 class="px-4 py-3 rounded-lg shadow-lg flex items-center gap-3 backdrop-blur-md"
                 :class="toast.type === 'success' ? 'bg-green-500/90 text-white' : 'bg-red-500/90 text-white'"
                 x-transition:enter="toast-enter-active"
                 x-transition:enter-start="toast-enter"
                 x-transition:enter-end="toast-leave"
                 x-transition:leave="toast-leave-active"
                 x-transition:leave-start="toast-leave"
                 x-transition:leave-end="toast-enter">
                <span x-text="toast.message"></span>
                <button @click="removeToast(toast.id)" class="opacity-70 hover:opacity-100">&times;</button>
            </div>
        </template>
    </div>

    <!-- Sidebar & Main Content -->
    <div class="flex min-h-screen w-full relative z-0">
        <!-- Sidebar -->
        <aside class="bg-navy text-white h-screen flex-shrink-0 transition-all duration-300 z-20 sticky top-0 hidden md:flex flex-col"
               :class="sidebarCollapsed ? 'w-[72px]' : 'w-[260px]'">
            <div class="h-16 flex items-center px-4 border-b border-white/10 mt-2">
                <svg class="w-8 h-8 mr-3 text-accent-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                <span class="font-serif text-xl tracking-wide whitespace-nowrap overflow-hidden transition-opacity"
                      :class="sidebarCollapsed ? 'opacity-0 w-0' : 'opacity-100'">DCFM System</span>
            </div>
            
            <nav class="flex-1 mt-6 px-3 space-y-2 overflow-y-auto overflow-hidden scroll-smooth" data-lenis-prevent>
                <template x-for="item in navItems" :key="item.path">
                    <a :href="item.path" x-show="item.roles.includes(user.role)"
                       class="flex items-center px-3 py-2.5 rounded-lg transition-colors hover:bg-white/10"
                       :class="window.location.pathname === item.path ? 'bg-accent-blue/80' : ''">
                        <div x-html="item.icon" class="w-6 h-6 flex-shrink-0"></div>
                        <span class="ml-3 font-medium whitespace-nowrap overflow-hidden transition-all"
                              x-text="item.label"
                              :class="sidebarCollapsed ? 'opacity-0 hidden' : 'opacity-100'"></span>
                    </a>
                </template>
            </nav>
            
            <div class="border-t border-white/10 p-4 mt-auto">
                <div class="flex items-center">
                    <div class="h-10 w-10 rounded-full bg-gradient-to-tr from-accent-blue to-accent-purple flex items-center justify-center text-white flex-shrink-0">
                        <span x-text="user.name ? user.name.charAt(0) : 'U'"></span>
                    </div>
                    <div class="ml-3 overflow-hidden whitespace-nowrap" :class="sidebarCollapsed ? 'hidden' : 'block'">
                        <p class="text-sm font-medium" x-text="user.name"></p>
                        <p class="text-xs text-gray-400 capitalize" x-text="user.role"></p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Wrapper -->
        <main class="flex-1 flex flex-col w-full min-h-screen relative z-10 bg-dots">
            <!-- Topbar -->
            <header class="h-16 px-6 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between bg-white/80 dark:bg-darkcard/80 backdrop-blur-md sticky top-0 z-30">
                <div class="flex items-center gap-4">
                    <button @click="sidebarCollapsed = !sidebarCollapsed" class="hidden md:block p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <h2 class="font-serif text-2xl page-header-anim tracking-tight" x-text="pageTitle">Dashboard</h2>
                </div>
                
                <div class="flex items-center gap-4 shrink-0">
                    <!-- Dark mode toggle -->
                    <button @click="toggleDark()" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                        <svg x-show="!isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                        <svg x-show="isDark" x-cloak class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </button>
                    
                    <!-- Notification Bell -->
                    <div class="relative" @click.away="notifsOpen = false">
                        <button @click="notifsOpen = !notifsOpen" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition relative text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            <span x-show="unreadCount > 0" x-cloak class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full animate-pulse border border-white dark:border-darkcard"></span>
                        </button>
                        
                        <div x-show="notifsOpen" x-transition.opacity x-cloak class="absolute right-0 mt-2 w-80 bg-white dark:bg-darkcard border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl backdrop-blur-md overflow-hidden">
                            <div class="p-3 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                                <h3 class="font-semibold">Notifications</h3>
                                <button @click="markAllRead" class="text-xs text-accent-blue hover:text-accent-purple transition" x-show="unreadCount > 0">Mark all read</button>
                            </div>
                            <div class="max-h-64 overflow-y-auto">
                                <template x-if="notifications.length === 0">
                                    <div class="p-4 text-center text-sm text-gray-500">You're all caught up!</div>
                                </template>
                                <template x-for="notif in notifications" :key="notif.id">
                                    <div @click="markRead(notif.id)" class="p-3 border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50 cursor-pointer flex items-start gap-3 transition">
                                        <div class="w-2 h-2 mt-1.5 rounded-full flex-shrink-0" :class="notif.is_read ? 'bg-transparent' : 'bg-accent-blue'"></div>
                                        <div>
                                            <p class="text-sm font-medium" :class="notif.is_read ? 'text-gray-600 dark:text-gray-400' : 'text-gray-900 dark:text-white'" x-text="notif.title"></p>
                                            <p class="text-xs text-gray-500 mt-1" x-text="notif.message"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <a href="/notifications" class="block p-2 text-center text-sm text-accent-blue bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-800 transition">View all</a>
                        </div>
                    </div>

                    <!-- Logout -->
                    <button @click="logout" class="p-2 rounded-lg text-gray-600 hover:text-accent-red dark:text-gray-400 transition" title="Logout">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    </button>
                </div>
            </header>

            <!-- Page Content via slot -->
            <div class="flex-1 p-6 relative z-10" id="main-scroll">
                @yield('content')
            </div>
            
            <!-- Mobile Bottom Nav -->
            <div class="md:hidden fixed bottom-0 left-0 right-0 h-16 bg-white dark:bg-darkcard border-t border-gray-200 dark:border-gray-800 flex justify-around items-center z-30">
                <template x-for="item in navItems.slice(0, 5)" :key="item.path">
                    <a :href="item.path" x-show="item.roles.includes(user.role)"
                       class="flex flex-col items-center p-2"
                       :class="window.location.pathname === item.path ? 'text-accent-blue' : 'text-gray-500'">
                        <div x-html="item.icon" class="w-6 h-6"></div>
                    </a>
                </template>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script>
        function appData() {
            return {
                isDark: localStorage.getItem('dcfm_dark') === 'true',
                sidebarCollapsed: false,
                notifsOpen: false,
                unreadCount: 0,
                notifications: [],
                pageTitle: 'Dashboard',
                user: JSON.parse(localStorage.getItem('dcfm_user') || '{}'),
                toasts: [],
                toastId: 0,
                
                navItems: [
                    { label: 'Dashboard', path: '/dashboard', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>', roles: ['admin', 'judge', 'lawyer', 'clerk', 'client'] },
                    { label: 'Cases Registry', path: '/cases', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>', roles: ['admin', 'judge', 'lawyer', 'clerk', 'client'] },
                    { label: 'Hearings', path: '/hearings', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>', roles: ['admin', 'judge', 'lawyer', 'clerk'] },
                    { label: 'Reports', path: '/reports', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>', roles: ['admin', 'judge', 'clerk'] },
                    { label: 'Users', path: '/users', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>', roles: ['admin'] }
                ],
                
                initApp() {
                    if (this.isDark) document.documentElement.classList.add('dark');
                    else document.documentElement.classList.remove('dark');
                    
                    // Route auth check (redirect to login if not authenticated and not on auth pages)
                    if (!this.user.id && !window.location.pathname.includes('/login') && !window.location.pathname.includes('/register')) {
                        window.location.href = '/login';
                        return;
                    }
                    
                    // Fetch notifications poll
                    if (this.user.id) {
                        this.fetchNotifications();
                        
                        // Real-time notifications
                        window.Echo?.private(`notifications.${this.user.id}`)
                            .listen('.notification.received', (e) => {
                                this.fetchNotifications();
                                this.showToast(e.notification.title + ': ' + e.notification.message);
                            });
                    }
                    
                    // Init Lenis
                    const lenis = new Lenis({ duration: 1.2, easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)) });
                    function raf(time) { lenis.raf(time); requestAnimationFrame(raf); }
                    requestAnimationFrame(raf);
                    
                    // Init GSAP specific to this layout
                    gsap.fromTo('.page-header-anim', { y: -16, opacity: 0 }, { y: 0, opacity: 1, duration: 0.5, ease: 'power2.out' });
                },
                
                toggleDark() {
                    this.isDark = !this.isDark;
                    localStorage.setItem('dcfm_dark', this.isDark);
                    if (this.isDark) document.documentElement.classList.add('dark');
                    else document.documentElement.classList.remove('dark');
                },
                
                async fetchNotifications() {
                    try {
                        const res = await fetch('/api/notifications/unread', {
                            credentials: 'same-origin',
                            headers: { 'Accept': 'application/json' }
                        });
                        const json = await res.json();
                        if (json.success) {
                            this.notifications = json.data;
                            this.unreadCount = this.notifications.length;
                        }
                    } catch (e) { console.error('Notifs error', e); }
                },
                
                async markRead(id) {
                    try {
                        await fetch(`/api/notifications/${id}/read`, { 
                            method: 'POST', 
                            credentials: 'same-origin',
                            headers: { 
                                'Accept': 'application/json', 
                                'Content-Type': 'application/json', 
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                            }
                        });
                        this.fetchNotifications();
                    } catch (e) {}
                },
                
                async markAllRead() {
                    try {
                        await fetch(`/api/notifications/read-all`, { 
                            method: 'POST', 
                            credentials: 'same-origin',
                            headers: { 
                                'Accept': 'application/json', 
                                'Content-Type': 'application/json', 
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                            }
                        });
                        this.notifsOpen = false;
                        this.fetchNotifications();
                    } catch (e) {}
                },
                
                async logout() {
                    try {
                        await fetch('/api/auth/logout', { 
                            method: 'POST', 
                            credentials: 'same-origin',
                            headers: { 
                                'Accept': 'application/json', 
                                'Content-Type': 'application/json', 
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                            }
                        });
                        localStorage.removeItem('dcfm_user');
                        localStorage.removeItem('dcfm_token');
                        window.location.href = '/login';
                    } catch (e) {
                         localStorage.removeItem('dcfm_user');
                         localStorage.removeItem('dcfm_token');
                         window.location.href = '/login';
                    }
                },
                
                showToast(message, type = 'success') {
                    const id = ++this.toastId;
                    this.toasts.push({ id, message, type, visible: true });
                    setTimeout(() => {
                        const t = this.toasts.find(x => x.id === id);
                        if (t) t.visible = false;
                    }, 4000);
                    // Cleanup array later to prevent memory leak
                    setTimeout(() => {
                        this.toasts = this.toasts.filter(x => x.id !== id);
                    }, 4500);
                },
                
                removeToast(id) {
                    const t = this.toasts.find(x => x.id === id);
                    if (t) t.visible = false;
                }
            }
        }
        
        // Define GSAP entering for bento grids inside content
        document.addEventListener("DOMContentLoaded", (event) => {
           setTimeout(() => {
               gsap.fromTo('.bento-card', 
                   { y: 24, opacity: 0 }, 
                   { y: 0, opacity: 1, stagger: 0.06, duration: 0.6, ease: 'power2.out' }
               );
           }, 100);
        });
    </script>
</body>
</html>
