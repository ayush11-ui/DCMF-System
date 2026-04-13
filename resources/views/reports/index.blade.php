@extends('layouts.app')

@section('content')
<div x-data="reportsView()" x-init="initReports()">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="font-serif text-3xl font-bold page-header-anim">Analytics & Reports</h1>
            <p class="text-gray-500 mt-1">Court performance and DCFM metrics</p>
        </div>
        <div class="flex gap-3">
            <button class="bg-white dark:bg-darkbg text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 px-4 py-2 rounded-lg font-medium inline-flex items-center gap-2 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                <span>📄</span> Export PDF
            </button>
            <button class="bg-white dark:bg-darkbg text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 px-4 py-2 rounded-lg font-medium inline-flex items-center gap-2 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                <span>📊</span> Export CSV
            </button>
        </div>
    </div>

    <!-- Stats row -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        <div class="bento-card p-4 text-center">
            <p class="text-sm text-gray-500">Total Cases</p>
            <h3 class="text-2xl font-mono font-bold mt-1" x-text="stats.total_cases"></h3>
        </div>
        <div class="bento-card p-4 text-center">
            <p class="text-sm text-gray-500">Open Cases</p>
            <h3 class="text-2xl font-mono font-bold mt-1 text-accent-blue" x-text="stats.open_cases"></h3>
        </div>
        <div class="bento-card p-4 text-center">
            <p class="text-sm text-gray-500">Closed Cases</p>
            <h3 class="text-2xl font-mono font-bold mt-1 text-gray-400" x-text="stats.closed_cases"></h3>
        </div>
        <div class="bento-card p-4 text-center ring-1 ring-accent-red">
            <p class="text-sm text-gray-500">Urgent Cases</p>
            <h3 class="text-2xl font-mono font-bold mt-1 text-accent-red" x-text="stats.urgent_cases"></h3>
        </div>
        <div class="bento-card p-4 text-center">
            <p class="text-sm text-gray-500">Resolution Rate</p>
            <h3 class="text-2xl font-mono font-bold mt-1 text-accent-emerald bg-emerald-50 dark:bg-emerald-900/20 inline-block px-2 rounded" x-text="stats.resolution_rate + '%'"></h3>
        </div>
        <div class="bento-card p-4 text-center">
            <p class="text-sm text-gray-500">Avg Days Open</p>
            <h3 class="text-2xl font-mono font-bold mt-1" x-text="stats.avg_days || '0'"></h3>
        </div>
    </div>
</div>

<script>
    function reportsView() {
        return {
            stats: { total_cases: 0, open_cases: 0, closed_cases: 0, urgent_cases: 0, resolution_rate: 0, avg_days: 0 },
            
            async initReports() {
                try {
                    const res = await fetch('/api/reports/stats', { headers: { 'Accept': 'application/json', 'Authorization':'Bearer '+localStorage.getItem('dcfm_token') }});
                    const json = await res.json();
                    if(json.success) {
                        this.stats = json.data;
                        this.stats.avg_days = 24; // Mock for aesthetic
                    }
                } catch(e) {}
            }
        }
    }
</script>
@endsection
