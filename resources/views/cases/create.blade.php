@extends('layouts.app')

@section('content')
<div x-data="caseForm()" x-init="initForm()">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="/cases" class="text-gray-400 hover:text-accent-blue transition text-sm flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg> Back
                </a>
            </div>
            <h1 class="font-serif text-3xl font-bold page-header-anim">Register New Case</h1>
        </div>
        <div class="flex gap-3">
            <button type="button" @click="window.location.href='/cases'" class="px-5 py-2.5 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition font-medium">Cancel</button>
            <button type="button" @click="submit" :disabled="loading" class="bg-gradient-to-r from-accent-blue to-accent-purple text-white px-6 py-2.5 rounded-lg shadow-lg shadow-accent-purple/30 font-medium inline-flex items-center gap-2 hover:opacity-90 transition disabled:opacity-50">
                <svg x-show="!loading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                <span x-show="!loading">Save Case</span>
                <span x-show="loading">Saving...</span>
            </button>
        </div>
    </div>

    <!-- Main form layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column: Details -->
        <div class="lg:col-span-2 space-y-6">
            
            <div class="bento-card p-6">
                <h3 class="font-bold text-lg mb-4 pb-2 border-b border-gray-100 dark:border-gray-800">Basic Info</h3>
                <div class="grid grid-cols-1 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Case Title *</label>
                        <input type="text" x-model="form.title" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-accent-blue outline-none bg-white dark:bg-darkbg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description *</label>
                        <textarea x-model="form.description" rows="3" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-accent-blue outline-none bg-white dark:bg-darkbg"></textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Petitioner (Plaintiff) *</label>
                            <input type="text" x-model="form.petitioner" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-accent-blue outline-none bg-white dark:bg-darkbg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Respondent (Defendant) *</label>
                            <input type="text" x-model="form.respondent" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-accent-blue outline-none bg-white dark:bg-darkbg">
                        </div>
                    </div>
                </div>
            </div>

            <div class="bento-card p-6">
                <h3 class="font-bold text-lg mb-4 pb-2 border-b border-gray-100 dark:border-gray-800">DCFM Classification Tracker</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Case Type *</label>
                        <select x-model="form.case_type" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-accent-blue outline-none bg-white dark:bg-darkbg">
                            <option value="">Select type...</option>
                            <option value="Civil">Civil</option>
                            <option value="Criminal">Criminal</option>
                            <option value="Family">Family</option>
                            <option value="Commercial">Commercial</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Complexity Level *</label>
                        <select x-model="form.complexity_level" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-accent-blue outline-none bg-white dark:bg-darkbg">
                            <option value="Simple">Simple (Fast-track)</option>
                            <option value="Standard">Standard</option>
                            <option value="Complex">Complex (Extended)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Priority *</label>
                        <select x-model="form.priority" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-accent-blue outline-none bg-white dark:bg-darkbg">
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                            <option value="Urgent">Urgent</option>
                        </select>
                    </div>
                </div>

                <!-- Live DCFM Rule box -->
                <div class="bg-blue-50 border border-blue-100 dark:bg-blue-900/10 dark:border-blue-900/30 rounded-lg p-5 flex items-start gap-4">
                    <div class="text-blue-500 bg-blue-100 dark:bg-blue-900/40 p-2 rounded-lg flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </div>
                    <div>
                        <h4 class="font-medium text-blue-900 dark:text-blue-300">Live DCFM Trajectory</h4>
                        <p class="text-sm text-blue-700 dark:text-blue-400 mt-1">Based on rules, hearings should be scheduled every <strong x-text="calcInterval()"></strong> days. We estimate <strong x-text="calcEstHearings()"></strong> total hearings to resolution.</p>
                    </div>
                </div>
            </div>

            <!-- Notes full width -->
            <div class="bento-card p-6">
                <h3 class="font-bold text-lg mb-4 pb-2 border-b border-gray-100 dark:border-gray-800">Additional Notes</h3>
                <textarea x-model="form.notes" rows="4" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-accent-blue outline-none bg-white dark:bg-darkbg"></textarea>
            </div>
            
        </div>

        <!-- Right Column: Settings -->
        <div class="space-y-6">
            
            <div class="bento-card p-6">
                <h3 class="font-bold text-lg mb-4 pb-2 border-b border-gray-100 dark:border-gray-800">Assignment</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Assigned Judge (Optional)</label>
                        <select x-model="form.assigned_judge_id" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-lg outline-none bg-white dark:bg-darkbg appearance-none">
                            <option value="">-- Unassigned --</option>
                            <template x-for="j in availableJudges" :key="j.id">
                                <option :value="j.id" x-text="j.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Assigned Lawyer (Optional)</label>
                        <select x-model="form.assigned_lawyer_id" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-lg outline-none bg-white dark:bg-darkbg appearance-none">
                            <option value="">-- Unassigned --</option>
                            <template x-for="l in availableLawyers" :key="l.id">
                                <option :value="l.id" x-text="l.name"></option>
                            </template>
                        </select>
                    </div>
                </div>
            </div>

            <div class="bento-card p-6">
                <h3 class="font-bold text-lg mb-4 pb-2 border-b border-gray-100 dark:border-gray-800">Dates & Court</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Court Name *</label>
                        <input type="text" x-model="form.court_name" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-lg outline-none bg-white dark:bg-darkbg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Filing Date *</label>
                        <input type="date" x-model="form.filing_date" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-lg outline-none bg-white dark:bg-darkbg">
                    </div>
                </div>
            </div>

            <div class="bento-card p-6 border-2 border-amber-400/50" x-show="user.role === 'admin'">
                <h3 class="font-bold text-lg mb-4 pb-2 border-b border-gray-100 dark:border-gray-800 flex items-center gap-2 text-amber-600 dark:text-amber-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg> Priority Override
                </h3>
                <div class="space-y-4">
                    <label class="flex items-center gap-2 mb-2 cursor-pointer">
                        <input type="checkbox" x-model="form.priority_overridden" class="rounded text-accent-amber">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Manually override DCFM rules</span>
                    </label>
                    <div x-show="form.priority_overridden">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Override Reason</label>
                        <textarea x-model="form.priority_override_reason" rows="2" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-lg outline-none bg-white dark:bg-darkbg"></textarea>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    function caseForm() {
        return {
            form: {
                title: '', description: '', petitioner: '', respondent: '', case_type: '',
                complexity_level: 'Standard', priority: 'Medium', notes: '', 
                assigned_judge_id: null, assigned_lawyer_id: null, court_name: '',
                filing_date: new Date().toISOString().split('T')[0],
                priority_overridden: false, priority_override_reason: ''
            },
            loading: false,
            availableJudges: [],
            availableLawyers: [],
            
            async initForm() {
                try {
                    const [resJ, resL] = await Promise.all([
                        fetch('/api/users?role=judge', { credentials: 'same-origin', headers: {'Accept':'application/json'} }),
                        fetch('/api/users?role=lawyer', { credentials: 'same-origin', headers: {'Accept':'application/json'} })
                    ]);
                    const [jsonJ, jsonL] = await Promise.all([resJ.json(), resL.json()]);
                    if (jsonJ.success) this.availableJudges = jsonJ.data;
                    else if (jsonJ.message) console.error('Judges fetch error:', jsonJ.message);
                    
                    if (jsonL.success) this.availableLawyers = jsonL.data;
                    else if (jsonL.message) console.error('Lawyers fetch error:', jsonL.message);
                } catch(e) { console.error('Init form error', e); }
            },
            
            calcInterval() {
                const map = {'Simple':14, 'Standard':30, 'Complex':45};
                const pmod = {'Urgent':0.5, 'High':0.75, 'Medium':1.0, 'Low':1.25};
                return Math.round(map[this.form.complexity_level] * pmod[this.form.priority]);
            },
            
            calcEstHearings() {
                const map = {'Simple':3, 'Standard':5, 'Complex':8};
                return map[this.form.complexity_level];
            },
            
            async submit() {
                this.loading = true;
                try {
                    const res = await fetch('/api/cases', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify(this.form)
                    });
                    const data = await res.json();
                    
                    if(data.success) {
                        this.showToast('Case created successfully!', 'success');
                        setTimeout(() => window.location.href = '/cases/'+data.data.id, 1000);
                    } else {
                        const err = data.errors ? Object.values(data.errors).flat().join('\n') : data.message;
                        this.showToast(err, 'error');
                    }
                } catch(e) { this.showToast('Network error', 'error'); }
                this.loading = false;
            }
        }
    }
</script>
@endsection
