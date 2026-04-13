@extends('layouts.app')

@section('content')
<div x-data="caseDetail({{ $id }})" x-init="fetchDetail()">
    <!-- Skeleton loader -->
    <div x-show="loading" class="animate-pulse space-y-6">
        <div class="h-12 bg-gray-200 dark:bg-gray-800 w-1/3 rounded"></div>
        <div class="h-4 bg-gray-200 dark:bg-gray-800 w-1/4 rounded mb-8"></div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="h-64 bg-gray-200 dark:bg-gray-800 rounded-xl"></div>
            <div class="h-64 bg-gray-200 dark:bg-gray-800 rounded-xl"></div>
        </div>
    </div>

    <!-- Content -->
    <div x-show="!loading && caseData.id" x-cloak>
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="font-mono text-accent-blue font-semibold text-lg" x-text="caseData.case_number"></span>
                    <span class="px-2 py-0.5 rounded text-xs border"
                          :class="{'border-red-500 text-red-500': caseData.priority === 'Urgent', 'border-gray-500 text-gray-500': caseData.priority !== 'Urgent'}"
                          x-text="caseData.priority"></span>
                    <span class="px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300" x-text="caseData.status"></span>
                    <span x-show="caseData.priority_overridden" class="px-2 py-0.5 rounded text-xs font-semibold bg-purple-100 text-purple-800 border border-purple-200">DCFM Override</span>
                </div>
                <h1 class="font-serif text-3xl font-bold page-header-anim truncate" x-text="caseData.title"></h1>
                <p class="text-gray-500 mt-1">
                    Filed <span x-text="diffDays(caseData.filing_date)"></span> days ago • <span x-text="caseData.case_type"></span>
                </p>
            </div>
            
            <div class="flex flex-wrap gap-3">
                <a :href="'/cases/'+caseData.id+'/edit'" x-show="['admin','clerk','judge'].includes(user.role)" class="px-4 py-2 border border-gray-200 dark:border-gray-700 bg-white dark:bg-darkcard hover:bg-gray-50 transition rounded-lg text-sm font-medium shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg> Edit
                </a>
                
                <button @click="autoSchedule" x-show="['admin','clerk'].includes(user.role)" class="px-4 py-2 bg-gradient-to-r from-accent-blue to-accent-purple text-white transition rounded-lg text-sm font-medium shadow-sm flex items-center gap-2 hover:opacity-90">
                    <span x-show="!schedulingParams.loading" class="flex gap-2 items-center"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg> Auto-Schedule Hearing</span>
                    <span x-show="schedulingParams.loading" class="animate-pulse">Scheduling...</span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            
            <!-- Case Information Grid (Span-2) -->
            <div class="bento-card p-6 lg:col-span-2">
                <h3 class="font-bold border-b border-gray-100 dark:border-gray-800 pb-3 mb-4">Case Information</h3>
                <div class="grid grid-cols-2 gap-y-4 gap-x-2 text-sm">
                    <div>
                        <p class="text-gray-500">Petitioner</p>
                        <p class="font-medium" x-text="caseData.petitioner"></p>
                    </div>
                    <div>
                        <p class="text-gray-500">Respondent</p>
                        <p class="font-medium" x-text="caseData.respondent"></p>
                    </div>
                    <div>
                        <p class="text-gray-500">Complexity</p>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <div class="flex gap-0.5">
                                <div class="w-2 h-2 rounded-full" :class="['Simple','Standard','Complex'].indexOf(caseData.complexity_level) >= 0 ? 'bg-accent-emerald' : 'bg-gray-300'"></div>
                                <div class="w-2 h-2 rounded-full" :class="['Standard','Complex'].indexOf(caseData.complexity_level) >= 0 ? 'bg-accent-blue' : 'bg-gray-300'"></div>
                                <div class="w-2 h-2 rounded-full" :class="caseData.complexity_level === 'Complex' ? 'bg-accent-red' : 'bg-gray-300'"></div>
                            </div>
                            <p class="font-medium" x-text="caseData.complexity_level"></p>
                        </div>
                    </div>
                    <div>
                        <p class="text-gray-500">Court</p>
                        <p class="font-medium" x-text="caseData.court_name"></p>
                    </div>
                    <div>
                        <p class="text-gray-500">Next Hearing</p>
                        <p class="font-medium" :class="getNextColor()" x-text="formatDate(caseData.next_hearing_date)"></p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 text-sm">
                    <p class="text-gray-500 mb-1">Description</p>
                    <p x-text="caseData.description" class="text-gray-700 dark:text-gray-300"></p>
                </div>
            </div>

            <!-- DCFM Progress Tracker (Span-2) -->
            <div class="bento-card p-6 lg:col-span-2">
                <h3 class="font-bold border-b border-gray-100 dark:border-gray-800 pb-3 mb-4">DCFM Progress Tracker</h3>
                <div class="flex flex-col h-full">
                    
                    <div class="flex items-center gap-6 mb-6">
                        <!-- Circular progress simulated via CSS conic-gradient in style logic -->
                        <div class="relative w-24 h-24 rounded-full flex items-center justify-center bg-gray-100 border border-gray-200 shadow-inner overflow-hidden" 
                             :style="`background: conic-gradient(#1d4ed8 ${(caseData.hearings_held/caseData.estimated_hearings)*100}%, transparent 0);`">
                             <div class="w-20 h-20 bg-white dark:bg-darkcard rounded-full flex items-center justify-center flex-col shadow-sm">
                                 <span class="text-xl font-bold" x-text="Math.round((caseData.hearings_held/caseData.estimated_hearings)*100)+'%'"></span>
                             </div>
                        </div>
                        <div class="flex-1 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Hearings Held</span>
                                <span class="font-bold" x-text="caseData.hearings_held"></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Estimated Total</span>
                                <span class="font-bold" x-text="caseData.estimated_hearings"></span>
                            </div>
                            <div class="flex justify-between text-sm pt-2 border-t border-gray-200">
                                <span class="text-gray-500">Expected Resolution</span>
                                <span class="font-medium" x-text="getExpectedRes()"></span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-blue-50/50 dark:bg-blue-900/10 rounded-lg p-3 border border-blue-100 dark:border-blue-900/30 flex gap-4 text-xs mt-auto mb-4">
                        <div class="p-2 bg-white dark:bg-darkbg rounded shadow-sm text-center">
                            <p class="font-bold text-accent-blue" x-text="caseData.hearing_interval_days + 'd'"></p>
                            <p class="text-gray-500 uppercase scale-90">Interval</p>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 py-1">DCFM track requires subsequent hearings to be scheduled within <strong x-text="caseData.hearing_interval_days"></strong> working days.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 border-t border-gray-100 dark:border-gray-800 pt-4 mt-auto">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-500 dark:bg-blue-900/30 flex-shrink-0 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </div>
                            <div class="overflow-hidden">
                                <p class="text-xs text-gray-500">Judge</p>
                                <p class="text-sm font-medium truncate" x-text="caseData.judge?.name || 'Unassigned'"></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-amber-50 text-amber-500 dark:bg-amber-900/30 flex-shrink-0 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            </div>
                            <div class="overflow-hidden">
                                <p class="text-xs text-gray-500">Lawyer</p>
                                <p class="text-sm font-medium truncate" x-text="caseData.lawyer?.name || 'Unassigned'"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hearing Schedule Table (Span-4) -->
            <div class="bento-card p-6 lg:col-span-4">
                <div class="flex justify-between items-center mb-4 pb-3 border-b border-gray-100 dark:border-gray-800">
                    <h3 class="font-bold">Hearing Schedule</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-600">
                            <tr>
                                <th class="py-3 px-4 font-medium rounded-l-lg">Date & Time</th>
                                <th class="py-3 px-4 font-medium">Type</th>
                                <th class="py-3 px-4 font-medium">Courtroom</th>
                                <th class="py-3 px-4 font-medium">Status</th>
                                <th class="py-3 px-4 font-medium">Outcome/Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="!caseData.hearings || caseData.hearings.length === 0">
                                <tr><td colspan="5" class="py-6 text-center text-gray-400">No hearings scheduled yet.</td></tr>
                            </template>
                            <template x-for="h in caseData.hearings" :key="h.id">
                                <tr class="border-b border-gray-100 dark:border-gray-800 last:border-0 hover:bg-gray-50 dark:hover:bg-darkbg/50 transition">
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="font-medium" x-text="formatDate(h.hearing_date)"></div>
                                        <div class="text-xs text-gray-500" x-text="String(h.hearing_time).substring(0,5)"></div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded text-xs" x-text="h.hearing_type"></span>
                                        <span x-show="h.is_auto_scheduled" class="ml-1 text-[10px] text-accent-purple bg-purple-50 dark:bg-purple-900/20 px-1 py-0.5 rounded border border-purple-200">Auto</span>
                                    </td>
                                    <td class="py-3 px-4 text-gray-600" x-text="h.courtroom || '-'"></td>
                                    <td class="py-3 px-4">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold inline-flex"
                                              :class="{
                                              'bg-blue-100 text-blue-800': h.status === 'Scheduled',
                                              'bg-green-100 text-green-800': h.status === 'Completed',
                                              'bg-amber-100 text-amber-800': h.status === 'Adjourned',
                                              'bg-red-100 text-red-800': h.status === 'Cancelled'
                                              }" x-text="h.status"></span>
                                    </td>
                                    <td class="py-3 px-4 max-w-xs truncate text-gray-500" :title="h.outcome" x-text="h.outcome || h.notes || '-'"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
    </div>
</div>

<script>
    function caseDetail(id) {
        return {
            caseId: id,
            loading: true,
            caseData: {},
            schedulingParams: { loading: false },
            
            async fetchDetail() {
                try {
                    const res = await fetch('/api/cases/' + this.caseId, { headers: { 'Accept': 'application/json', 'Authorization':'Bearer '+localStorage.getItem('dcfm_token') }});
                    const json = await res.json();
                    if(json.success) this.caseData = json.data;
                } catch(e) {}
                this.loading = false;
            },
            
            diffDays(date) {
                if(!date) return 0;
                const ms = Date.now() - new Date(date).getTime();
                return Math.floor(ms / (1000 * 60 * 60 * 24));
            },
            
            formatDate(date) {
                if(!date) return 'Not set';
                return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
            },
            
            getNextColor() {
                if(!this.caseData.next_hearing_date) return 'text-gray-500';
                if(new Date(this.caseData.next_hearing_date) < new Date()) return 'text-accent-red font-bold';
                return 'text-accent-blue';
            },
            
            getExpectedRes() {
                if(!this.caseData.filing_date) return '';
                const d = new Date(this.caseData.filing_date);
                d.setDate(d.getDate() + (this.caseData.hearing_interval_days * this.caseData.estimated_hearings));
                return this.formatDate(d);
            },
            
            async autoSchedule() {
                if(!confirm('DCFM will automatically calculate the next optimum date and schedule a hearing. Proceed?')) return;
                
                this.schedulingParams.loading = true;
                try {
                    const res = await fetch(`/api/cases/${this.caseId}/auto-schedule`, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'Authorization':'Bearer '+localStorage.getItem('dcfm_token'), 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                    });
                    const json = await res.json();
                    if(json.success) {
                        this.showToast(json.message, 'success');
                        this.fetchDetail(); // Reload
                    } else {
                        this.showToast(json.message || 'Auto-scheduling failed due to conflict', 'error');
                    }
                } catch(e) { this.showToast('Network error', 'error'); }
                this.schedulingParams.loading = false;
            }
        }
    }
</script>
@endsection
