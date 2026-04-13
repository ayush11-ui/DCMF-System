@extends('layouts.app')

@section('content')
<div x-data="calendarView()" x-init="initCalendar()">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="font-serif text-3xl font-bold page-header-anim">Hearings & Calendar</h1>
            <p class="text-gray-500 mt-1">Manage scheduled proceedings and court dates</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Calendar (Left Panel 70%) -->
        <div class="lg:col-span-2 bento-card p-6 h-[800px] flex flex-col relative z-10">
            <div id="calendar" class="flex-1"></div>
        </div>

        <!-- Upcoming (Right Panel 30%) -->
        <div class="bento-card p-6 h-[800px] flex flex-col relative z-10 overflow-hidden">
            <h3 class="font-bold text-xl mb-4 border-b border-gray-100 dark:border-gray-800 pb-3">Upcoming Hearings</h3>
            
            <div class="flex-1 overflow-y-auto space-y-4 pr-2 custom-scrollbar">
                <template x-if="hearings.length === 0">
                    <div class="text-center text-gray-500 mt-10">No upcoming hearings scheduled.</div>
                </template>
                <template x-for="h in hearings" :key="h.id">
                    <div class="bg-gray-50 dark:bg-darkbg p-4 rounded-xl border border-gray-100 dark:border-gray-800 hover:border-accent-blue transition cursor-pointer" @click="window.location.href='/cases/'+h.case_id">
                        <div class="flex justify-between items-start mb-2">
                            <span class="text-xs font-bold text-accent-blue bg-blue-100 dark:bg-blue-900/30 px-2 py-1 rounded" x-text="h.hearing_type"></span>
                            <span x-show="h.is_auto_scheduled" class="text-xs bg-purple-100 dark:bg-purple-900/30 text-accent-purple px-2 py-1 rounded-full flex gap-1 items-center" title="Auto-scheduled by DCFM"><span class="w-1.5 h-1.5 bg-accent-purple rounded-full"></span> Auto</span>
                        </div>
                        <a :href="'/cases/'+h.case_id" class="font-mono text-sm text-gray-500 mb-1 hover:underline" x-text="h.case_file.case_number"></a>
                        <h4 class="font-medium mt-1 truncate" x-text="h.case_file.title"></h4>
                        <div class="mt-3 flex items-center text-sm text-gray-500">
                            <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span class="font-medium text-gray-700 dark:text-gray-300" x-text="formatDate(h.hearing_date) + ' • ' + String(h.hearing_time).substring(0,5)"></span>
                        </div>
                        <div class="mt-2 text-sm text-gray-500 flex gap-2 items-center">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            <span x-text="h.courtroom || 'TBD'"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<style>
    /* FullCalendar Dark Mode overrides */
    .dark .fc-theme-standard td, .dark .fc-theme-standard th { border-color: #374151; }
    .dark .fc-col-header-cell-cushion, .dark .fc-daygrid-day-number { color: #f3f4f6; }
    .dark .fc .fc-button-primary { background-color: #1f2937; border-color: #374151; box-shadow: none; }
    .dark .fc .fc-button-primary:hover { background-color: #374151; }
    .dark .fc .fc-button-primary:not(:disabled).fc-button-active { background-color: #1d4ed8; border-color: #1d4ed8; }
    .dark .fc-day-today { background-color: rgba(29, 78, 216, 0.1) !important; }
    .fc-event { border: none; padding: 2px 4px; border-radius: 4px; font-family: "DM Sans"; }
    .fc .fc-bg-event { opacity: 0.1; }
    #calendar { font-family: "DM Sans"; }
</style>

<script>
    function calendarView() {
        return {
            hearings: [],
            calendar: null,
            
            async initCalendar() {
                const el = document.getElementById('calendar');
                this.calendar = new FullCalendar.Calendar(el, {
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,listWeek'
                    },
                    height: '100%',
                    events: async (info, successCallback, failureCallback) => {
                        try {
                            const res = await fetch(`/api/hearings?start=${info.startStr}&end=${info.endStr}`, { headers: {'Accept':'application/json', 'Authorization':'Bearer '+localStorage.getItem('dcfm_token')} });
                            const json = await res.json();
                            if(json.success) {
                                const events = json.data.map(h => ({
                                    id: h.id,
                                    title: h.case_file.case_number + ' | ' + h.hearing_type,
                                    start: h.hearing_date + 'T' + h.hearing_time,
                                    end: new Date(new Date(h.hearing_date + 'T' + h.hearing_time).getTime() + h.duration_minutes*60000).toISOString(),
                                    backgroundColor: h.status === 'Completed' ? '#10b981' : (h.status === 'Adjourned' ? '#f59e0b' : (h.status === 'Cancelled' ? '#ef4444' : '#1d4ed8')),
                                    extendedProps: { case_id: h.case_id }
                                }));
                                successCallback(events);
                            }
                        } catch(e) { failureCallback(e); }
                    },
                    eventClick: (info) => {
                        window.location.href = '/cases/' + info.event.extendedProps.case_id;
                    },
                    eventDidMount: (info) => {
                        gsap.from(info.el, {opacity: 0, scale: 0.9, duration: 0.3});
                    }
                });
                this.calendar.render();
                this.fetchUpcoming();
            },
            
            async fetchUpcoming() {
                try {
                    const start = new Date().toISOString().split('T')[0];
                    const d = new Date(); d.setDate(d.getDate()+30);
                    const end = d.toISOString().split('T')[0];
                    const res = await fetch(`/api/hearings?start=${start}&end=${end}`, { headers: {'Accept':'application/json', 'Authorization':'Bearer '+localStorage.getItem('dcfm_token')} });
                    const json = await res.json();
                    if(json.success) {
                        this.hearings = json.data
                            .filter(h => new Date(h.hearing_date) >= new Date(start))
                            .sort((a,b) => new Date(a.hearing_date) - new Date(b.hearing_date));
                    }
                } catch(e) {}
            },
            
            formatDate(d) {
                return new Date(d).toLocaleDateString('en-US', {month:'short', day:'numeric'});
            }
        }
    }
</script>
@endsection
