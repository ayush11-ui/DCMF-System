@extends('layouts.app')

@section('content')
<div x-data="caseRegistry()" x-init="initCases()">
    
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="font-serif text-3xl font-bold page-header-anim">Case Registry</h1>
            <p class="text-gray-500 mt-1">Total <span x-text="totalCases"></span> cases registered</p>
        </div>
        <div x-show="['admin','clerk'].includes(user.role)">
            <a href="/cases/create" class="bg-gradient-to-r from-accent-blue to-accent-purple text-white px-6 py-2.5 rounded-lg btn-glow shadow-lg shadow-accent-purple/30 font-medium inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg> New Case
            </a>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bento-card p-4 mb-6 relative z-20">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="md:col-span-2 relative">
                <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
                <input type="text" x-model="filters.search" @input.debounce.500ms="fetchCases" placeholder="Search cases, parties..." class="w-full pl-10 pr-4 py-2 border border-gray-200 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-accent-blue outline-none bg-white dark:bg-darkbg">
            </div>
            <div>
                <select x-model="filters.status" @change="fetchCases" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-accent-blue outline-none bg-white dark:bg-darkbg appearance-none">
                    <option value="">All Statuses</option>
                    <option value="Pending">Pending</option>
                    <option value="Ongoing">Ongoing</option>
                    <option value="Judgment">Judgment</option>
                    <option value="Closed">Closed</option>
                </select>
            </div>
            <div>
                <select x-model="filters.complexity" @change="fetchCases" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-accent-blue outline-none bg-white dark:bg-darkbg appearance-none">
                    <option value="">All Complexities</option>
                    <option value="Simple">Simple</option>
                    <option value="Standard">Standard</option>
                    <option value="Complex">Complex</option>
                </select>
            </div>
            <div>
                <select x-model="filters.priority" @change="fetchCases" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-accent-blue outline-none bg-white dark:bg-darkbg appearance-none">
                    <option value="">All Priorities</option>
                    <option value="Low">Low</option>
                    <option value="Medium">Medium</option>
                    <option value="High">High</option>
                    <option value="Urgent">Urgent</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Table content -->
    <div class="bento-card relative z-10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr class="border-b border-gray-200 dark:border-gray-700 text-sm text-gray-500">
                        <th class="py-4 px-6 font-medium cursor-pointer" @click="sortBy('case_number')">Case No.</th>
                        <th class="py-4 px-6 font-medium cursor-pointer" @click="sortBy('title')">Title</th>
                        <th class="py-4 px-6 font-medium">Type & Complexity</th>
                        <th class="py-4 px-6 font-medium">Priority</th>
                        <th class="py-4 px-6 font-medium">Status</th>
                        <th class="py-4 px-6 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr x-show="loading">
                        <td colspan="6" class="p-8 text-center">
                            <div class="animate-pulse flex flex-col gap-4 w-full">
                                <div class="h-10 bg-gray-200 dark:bg-gray-800 rounded w-full"></div>
                                <div class="h-10 bg-gray-200 dark:bg-gray-800 rounded w-full"></div>
                                <div class="h-10 bg-gray-200 dark:bg-gray-800 rounded w-full"></div>
                            </div>
                        </td>
                    </tr>
                    <tr x-show="!loading && cases.length === 0" x-cloak>
                        <td colspan="6" class="p-12 text-center">
                            <div class="w-16 h-16 bg-gray-50 dark:bg-gray-800 text-gray-400 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                            </div>
                            <h3 class="text-xl font-medium text-gray-700 dark:text-gray-300">No cases found</h3>
                            <p class="text-gray-500 mt-2">Adjust your filters or add a new case.</p>
                        </td>
                    </tr>
                    <template x-for="c in cases" :key="c.id">
                        <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                            <td class="py-4 px-6">
                                <a :href="'/cases/'+c.id" class="font-mono text-accent-blue font-medium hover:underline block" x-text="c.case_number"></a>
                                <span class="text-xs text-gray-500" x-text="'Filed ' + new Date(c.filing_date).toLocaleDateString()"></span>
                            </td>
                            <td class="py-4 px-6">
                                <p class="font-medium text-gray-900 dark:text-gray-100 truncate max-wxs" x-text="c.title"></p>
                                <p class="text-xs text-gray-500" x-text="c.petitioner + ' v. ' + c.respondent"></p>
                            </td>
                            <td class="py-4 px-6">
                                <div class="text-sm" x-text="c.case_type"></div>
                                <div class="flex items-center gap-1.5 mt-1">
                                    <div class="flex gap-0.5">
                                        <div class="w-2 h-2 rounded-full" :class="['Simple','Standard','Complex'].indexOf(c.complexity_level) >= 0 ? 'bg-accent-emerald' : 'bg-gray-300'"></div>
                                        <div class="w-2 h-2 rounded-full" :class="['Standard','Complex'].indexOf(c.complexity_level) >= 0 ? 'bg-accent-blue' : 'bg-gray-300'"></div>
                                        <div class="w-2 h-2 rounded-full" :class="c.complexity_level === 'Complex' ? 'bg-accent-red' : 'bg-gray-300'"></div>
                                    </div>
                                    <span class="text-[10px] uppercase font-bold tracking-wider text-gray-500" x-text="c.complexity_level"></span>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="inline-block px-2.5 py-1 rounded-lg text-xs font-medium border"
                                      :class="{
                                          'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/20 dark:text-red-400 dark:border-red-800': c.priority === 'Urgent',
                                          'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-900/20 dark:text-orange-400 dark:border-orange-800': c.priority === 'High',
                                          'bg-gray-50 text-gray-700 border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700': c.priority === 'Medium',
                                      }" x-text="c.priority"></span>
                            </td>
                            <td class="py-4 px-6">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold"
                                      :class="{
                                          'bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-300': c.status === 'Pending',
                                          'bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-300': c.status === 'Ongoing',
                                          'bg-purple-100 text-purple-800 dark:bg-purple-500/20 dark:text-purple-300': c.status === 'Judgment',
                                          'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400': c.status === 'Closed'
                                      }">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="c.status === 'Closed' ? 'bg-current' : 'bg-current animate-pulse'"></span>
                                    <span x-text="c.status"></span>
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right space-x-2">
                                <a :href="'/cases/'+c.id" class="p-2 text-gray-400 hover:text-accent-blue bg-gray-50 dark:bg-gray-800 rounded-lg inline-flex items-center transition" title="View">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                                <a :href="'/cases/'+c.id+'/edit'" x-show="['admin','clerk','judge'].includes(user.role)" class="p-2 text-gray-400 hover:text-amber-500 bg-gray-50 dark:bg-gray-800 rounded-lg inline-flex items-center transition" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </a>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-800 flex items-center justify-between">
            <span class="text-sm text-gray-500" x-text="`Showing ${cases.length} of ${totalCases}`"></span>
            <div class="flex gap-2">
                <button class="px-3 py-1 rounded bg-gray-100 dark:bg-gray-800 disabled:opacity-50" :disabled="currentPage === 1" @click="currentPage--; fetchCases()">Prev</button>
                <button class="px-3 py-1 rounded bg-gray-100 dark:bg-gray-800 disabled:opacity-50" :disabled="cases.length < 15" @click="currentPage++; fetchCases()">Next</button>
            </div>
        </div>
    </div>
</div>

<script>
    function caseRegistry() {
        return {
            loading: true,
            cases: [],
            totalCases: 0,
            currentPage: 1,
            filters: { search: '', status: '', complexity: '', priority: '' },
            sortCol: 'created_at', sortDir: 'desc',
            
            initCases() {
                this.fetchCases();
            },
            
            async fetchCases() {
                this.loading = true;
                const params = new URLSearchParams({
                    page: this.currentPage,
                    search: this.filters.search,
                    status: this.filters.status,
                    complexity_level: this.filters.complexity,
                    priority: this.filters.priority
                });
                
                try {
                    const res = await fetch(`/api/cases?${params}`, { headers: {'Accept':'application/json', 'Authorization': 'Bearer ' + localStorage.getItem('dcfm_token')} });
                    const json = await res.json();
                    if (json.success) {
                        this.cases = json.data.data;
                        this.totalCases = json.data.total;
                    }
                } catch(e) {}
                this.loading = false;
            },
            
            sortBy(col) {
                if(col === this.sortCol) { this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc'; }
                else { this.sortCol = col; this.sortDir = 'asc'; }
                this.cases.sort((a,b) => {
                    let valA = a[col] || ''; let valB = b[col] || '';
                    if(valA < valB) return this.sortDir === 'asc' ? -1 : 1;
                    if(valA > valB) return this.sortDir === 'asc' ? 1 : -1;
                    return 0;
                });
            }
        }
    }
</script>
@endsection
