@extends('layouts.app')

@section('content')
<div x-data="dashboardData()" x-init="initDashboard()">
    
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="font-serif text-3xl font-bold page-header-anim">Good <span x-text="greeting"></span>, <span x-text="user.name.split(' ')[0]"></span></h1>
            <p class="text-gray-500 mt-1"><span x-text="dateString"></span></p>
        </div>
        <div x-show="['admin','clerk'].includes(user.role)">
            <a href="/cases/create" class="bg-gradient-to-r from-accent-blue to-accent-purple text-white px-6 py-2.5 rounded-lg btn-glow shadow-lg shadow-accent-purple/30 font-medium inline-flex items-center gap-2">
                <span>➕</span> New Case
            </a>
        </div>
    </div>

    <!-- Stats row 1 -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        
        <!-- Total Cases -->
        <div class="bento-card bg-gradient-to-br from-accent-blue to-accent-purple text-white p-6 relative overflow-hidden group">
            <div class="relative z-10">
                <p class="text-blue-100 font-medium">Total Cases</p>
                <div class="flex items-end gap-3 mt-2">
                    <h3 class="text-4xl font-mono font-bold" x-text="stats.total_cases"></h3>
                    <p class="text-sm bg-black/20 px-2 py-0.5 rounded-full mb-1">
                        <span x-text="stats.resolution_rate"></span>% Resolved
                    </p>
                </div>
            </div>
            <div class="absolute -right-4 -bottom-4 text-white/10 group-hover:scale-110 transition-transform duration-500">
                <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-9 14H6v-2h4v2zm0-4H6v-2h4v2zm0-4H6V7h4v2zm8 8h-6v-2h6v2zm0-4h-6v-2h6v2zm0-4h-6V7h6v2z"/></svg>
            </div>
        </div>

        <!-- Pending Cases -->
        <div class="bento-card p-6 flex flex-col justify-between">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-500">Pending</p>
                    <h3 class="text-3xl font-mono font-bold text-accent-amber mt-1" x-text="stats.pending"></h3>
                </div>
                <div class="p-2 bg-amber-100 text-accent-amber rounded-lg dark:bg-amber-500/20">⏳</div>
            </div>
            <div class="mt-4">
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                    <div class="bg-accent-amber h-1.5 rounded-full" :style="'width: ' + (stats.total_cases ? (stats.pending/stats.total_cases)*100 : 0) + '%'"></div>
                </div>
            </div>
        </div>

        <!-- Ongoing Cases -->
        <div class="bento-card p-6 flex flex-col justify-between">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-500">Active / Ongoing</p>
                    <h3 class="text-3xl font-mono font-bold text-accent-blue mt-1" x-text="stats.ongoing"></h3>
                </div>
                <div class="p-2 bg-blue-100 text-accent-blue rounded-lg dark:bg-blue-500/20">🔄</div>
            </div>
            <div class="mt-4">
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                    <div class="bg-accent-blue h-1.5 rounded-full" :style="'width: ' + (stats.total_cases ? (stats.ongoing/stats.total_cases)*100 : 0) + '%'"></div>
                </div>
            </div>
        </div>

        <!-- Urgent Priority -->
        <div class="bento-card p-6 flex flex-col justify-between border-2 border-transparent transition-colors" :class="stats.urgent_cases > 0 ? 'border-accent-red animate-pulse' : ''">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-500">Urgent Priority</p>
                    <h3 class="text-3xl font-mono font-bold text-accent-red mt-1" x-text="stats.urgent_cases"></h3>
                </div>
                <div class="p-2 bg-red-100 text-accent-red rounded-lg dark:bg-red-500/20">❗</div>
            </div>
            <div class="mt-4 text-xs font-semibold text-accent-red flex items-center gap-1" x-show="stats.urgent_cases > 0">
                <span class="w-2 h-2 rounded-full bg-accent-red inline-block"></span> Requires Attention
            </div>
        </div>
    </div>

    <!-- Charts row -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
        
        <!-- Bar Chart -->
        <div class="bento-card lg:col-span-2 p-6 flex flex-col min-h-[300px]">
            <h3 class="font-bold mb-4 w-full text-left">Cases by Type</h3>
            <div class="relative w-full flex-1 min-h-[250px]">
                <canvas id="typeChart"></canvas>
            </div>
        </div>

        <!-- Doughnut Chart -->
        <div class="bento-card lg:col-span-2 p-6 flex flex-col min-h-[300px]">
            <h3 class="font-bold mb-4 w-full text-left">Cases by Status</h3>
            <div class="relative w-full flex-1 min-h-[250px]">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Lists row -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Upcoming Hearings (col-2, tall) -->
        <div class="bento-card lg:col-span-2 p-6 h-[500px] flex flex-col">
            <div class="flex justify-between items-center mb-4 pb-2 border-b border-gray-100 dark:border-gray-800">
                <h3 class="font-bold">Next 14 Days Hearings</h3>
                <a href="/hearings" class="text-sm text-accent-blue hover:text-accent-purple transition">View Calendar &rarr;</a>
            </div>
            
            <div class="flex-1 overflow-y-auto pr-2 space-y-4 scroll-smooth" data-lenis-prevent>
                <template x-if="hearings.length === 0">
                    <div class="h-full flex items-center justify-center flex-col text-gray-400">
                        <span class="text-4xl mb-2">📅</span>
                        <p>No upcoming hearings</p>
                    </div>
                </template>
                <template x-for="hearing in hearings" :key="hearing.id">
                    <div class="bg-gray-50 dark:bg-darkbg p-4 rounded-xl border-l-4"
                         :class="hearing.is_auto_scheduled ? 'border-accent-purple' : 'border-accent-blue'">
                        <div class="flex gap-4">
                            <div class="flex flex-col items-center justify-center w-14 h-14 bg-white dark:bg-darkcard rounded-lg shadow-sm flex-shrink-0">
                                <span class="text-xs font-bold text-gray-400 uppercase" x-text="new Date(hearing.hearing_date).toLocaleString('default', {month:'short'})"></span>
                                <span class="text-xl font-bold" x-text="new Date(hearing.hearing_date).getDate()"></span>
                            </div>
                            <div class="flex-1">
                                <a :href="'/cases/'+hearing.case_id" class="text-accent-blue font-mono text-sm hover:underline" x-text="hearing.case_file.case_number"></a>
                                <h4 class="font-medium truncate max-w-xs" x-text="hearing.case_file.title"></h4>
                                <div class="text-xs text-gray-500 mt-1 flex gap-2">
                                    <span x-text="hearing.hearing_time.substring(0,5)"></span>
                                    <span>•</span>
                                    <span x-text="hearing.courtroom || 'TBD'"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Recent Cases Table (col-2) -->
        <div class="bento-card lg:col-span-2 p-6 min-h-[500px]">
            <div class="flex justify-between items-center mb-4 pb-2 border-b border-gray-100 dark:border-gray-800">
                <h3 class="font-bold">Recent Cases</h3>
                <a href="/cases" class="text-sm text-accent-blue hover:text-accent-purple transition">View All &rarr;</a>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-800 text-sm text-gray-500">
                            <th class="py-3 px-2 font-medium">Case Info</th>
                            <th class="py-3 px-2 font-medium">Complexity</th>
                            <th class="py-3 px-2 font-medium text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="c in recentCases" :key="c.id">
                            <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-darkbg transition cursor-pointer" @click="window.location.href='/cases/'+c.id">
                                <td class="py-3 px-2">
                                    <p class="font-mono text-accent-blue text-xs" x-text="c.case_number"></p>
                                    <p class="font-medium text-sm truncate max-w-[150px]" x-text="c.title"></p>
                                </td>
                                <td class="py-3 px-2">
                                    <div class="flex gap-1" :title="c.complexity_level">
                                        <div class="w-2.5 h-2.5 rounded-full" :class="['Simple','Standard','Complex'].indexOf(c.complexity_level) >= 0 ? 'bg-accent-emerald' : 'bg-gray-300'"></div>
                                        <div class="w-2.5 h-2.5 rounded-full" :class="['Standard','Complex'].indexOf(c.complexity_level) >= 0 ? 'bg-accent-blue' : 'bg-gray-300'"></div>
                                        <div class="w-2.5 h-2.5 rounded-full" :class="c.complexity_level === 'Complex' ? 'bg-accent-red' : 'bg-gray-300'"></div>
                                    </div>
                                    <span class="text-[10px] text-gray-500 uppercase mt-1" x-text="c.complexity_level"></span>
                                </td>
                                <td class="py-3 px-2 text-right">
                                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium"
                                          :class="{
                                              'bg-blue-100 text-blue-800': c.status === 'Pending',
                                              'bg-indigo-100 text-indigo-800': c.status === 'Ongoing',
                                              'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300': c.status === 'Closed' || c.status === 'Judgment'
                                          }" x-text="c.status">
                                    </span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function dashboardData() {
        return {
            greeting: '',
            dateString: '',
            stats: { total_cases: 0, open_cases: 0, pending: 0, ongoing: 0, closed_cases: 0, urgent_cases: 0, resolution_rate: 0, cases_by_type: [], cases_by_status: [] },
            recentCases: [],
            hearings: [],
            chartsLoaded: false,
            
            initDashboard() {
                const hour = new Date().getHours();
                if (hour < 12) this.greeting = 'morning';
                else if (hour < 18) this.greeting = 'afternoon';
                else this.greeting = 'evening';
                
                this.dateString = new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                
                setTimeout(() => {
                    this.fetchStats();
                    this.fetchRecentCases();
                    this.fetchHearings();
                    
                    // Dashboard Auto-Refresh every 60 seconds
                    setInterval(() => {
                        this.fetchStats();
                        this.fetchRecentCases();
                        this.fetchHearings();
                    }, 60000);
                }, 100);
            },
            
            async fetchStats() {
                try {
                    const res = await fetch('/api/reports/stats', { 
                        credentials: 'same-origin',
                        headers: { 'Accept':'application/json' } 
                    });
                    const json = await res.json();
                    if(json.success) {
                        this.stats = json.data;
                        let pending = 0, ongoing = 0;
                        json.data.cases_by_status.forEach(st => {
                            if(st.status === 'Pending') pending = st.count;
                            if(st.status === 'Ongoing') ongoing = st.count;
                        });
                        this.stats.pending = pending;
                        this.stats.ongoing = ongoing;
                        this.renderCharts();
                    }
                } catch(e) {}
            },
            
            async fetchRecentCases() {
                try {
                    const res = await fetch('/api/cases?per_page=5', { 
                        credentials: 'same-origin',
                        headers: { 'Accept':'application/json' } 
                    });
                    const json = await res.json();
                    if(json.success) this.recentCases = json.data.data; // paginate
                } catch(e) {}
            },
            
            async fetchHearings() {
                try {
                    const start = new Date().toISOString().split('T')[0];
                    const d = new Date(); d.setDate(d.getDate()+14);
                    const end = d.toISOString().split('T')[0];
                    
                    const res = await fetch(`/api/hearings?start=${start}&end=${end}`, { 
                        credentials: 'same-origin',
                        headers: { 'Accept':'application/json' } 
                    });
                    const json = await res.json();
                    if(json.success) {
                        this.hearings = json.data.sort((a,b) => new Date(a.hearing_date) - new Date(b.hearing_date)).slice(0, 5);
                    }
                } catch(e) {}
            },
            
            renderCharts() {
                if(this.chartsLoaded) return;
                
                const typeCtx = document.getElementById('typeChart');
                const statusCtx = document.getElementById('statusChart');
                const dark = document.documentElement.classList.contains('dark');
                const textColor = dark ? '#e5e7eb' : '#374151';
                
                Chart.defaults.color = textColor;
                Chart.defaults.font.family = '"DM Sans", sans-serif';

                if(typeCtx && this.stats.cases_by_type) {
                    new Chart(typeCtx, {
                        type: 'bar',
                        options: { indexAxis: 'y', plugins: { legend: { display: false } }, responsive:true, maintainAspectRatio: false },
                        data: {
                            labels: this.stats.cases_by_type.map(c => c.case_type),
                            datasets: [{ label: 'Cases', data: this.stats.cases_by_type.map(c => c.count), backgroundColor: '#7c3aed', borderRadius: 6 }]
                        }
                    });
                }
                
                if(statusCtx && this.stats.cases_by_status) {
                    const colorMap = {'Pending':'#f59e0b', 'Ongoing':'#1d4ed8', 'Closed':'#9ca3af', 'Adjourned':'#ef4444'};
                    new Chart(statusCtx, {
                        type: 'doughnut',
                        options: { responsive:true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { position: 'right' } } },
                        data: {
                            labels: this.stats.cases_by_status.map(c => c.status),
                            datasets: [{ 
                                data: this.stats.cases_by_status.map(c => c.count),
                                backgroundColor: this.stats.cases_by_status.map(c => colorMap[c.status] || '#10b981'),
                                borderWidth: 0
                            }]
                        }
                    });
                }
                this.chartsLoaded = true;
            }
        }
    }
</script>
@endsection
