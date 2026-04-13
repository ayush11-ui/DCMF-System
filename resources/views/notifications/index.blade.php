@extends('layouts.app')

@section('content')
<div x-data="notificationsPage()">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="font-serif text-3xl font-bold page-header-anim">Notifications</h1>
            <p class="text-gray-500 mt-1">Your recent alerts and updates</p>
        </div>
        <div>
            <button @click="markAllRead" class="text-accent-blue bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/40 px-4 py-2 rounded-lg font-medium transition disabled:opacity-50" :disabled="unreadCount === 0">
                Mark All Read
            </button>
        </div>
    </div>

    <div class="bento-card max-w-4xl">
        <template x-if="notifications.length === 0">
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-gray-50 dark:bg-gray-800 text-gray-400 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                </div>
                <h3 class="font-medium text-xl">You're all caught up!</h3>
                <p class="text-gray-500">No new notifications.</p>
            </div>
        </template>
        
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            <template x-for="notif in notifications" :key="notif.id">
                <div class="p-4 flex items-start gap-4 hover:bg-gray-50 dark:hover:bg-darkbg transition cursor-pointer" @click="notif.case_id ? window.location.href='/cases/'+notif.case_id : ''">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg flex-shrink-0"
                         :class="{
                             'bg-orange-100 text-orange-600': notif.type === 'hearing_reminder',
                             'bg-purple-100 text-purple-600': notif.type === 'status_change',
                             'bg-blue-100 text-blue-600': notif.type === 'case_assigned',
                             'bg-red-100 text-red-600': notif.type === 'priority_change',
                             'bg-green-100 text-green-600': notif.type === 'hearing_scheduled'
                         }">
                         <svg x-show="notif.type === 'hearing_reminder'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                         <svg x-show="notif.type === 'status_change'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                         <svg x-show="notif.type === 'case_assigned'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                         <svg x-show="notif.type === 'priority_change'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                         <svg x-show="notif.type === 'hearing_scheduled'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between items-start mb-1">
                            <h4 class="font-bold text-gray-900 dark:text-gray-100" x-text="notif.title"></h4>
                            <span class="text-xs text-gray-500 whitespace-nowrap" x-text="new Date(notif.created_at).toLocaleDateString()"></span>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400" x-text="notif.message"></p>
                    </div>
                    <div class="pt-2">
                        <div class="w-2.5 h-2.5 rounded-full bg-accent-blue" x-show="!notif.is_read"></div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
    function notificationsPage() {
        return {
            notifications: [],
            // Fetch directly logic falls back to window.appData state since they share via localstorage or fetch individually.
            // Since this is a standalone view, we fetch list
            async init() {
                try {
                    const res = await fetch('/api/notifications/unread', { headers: { 'Accept': 'application/json', 'Authorization':'Bearer '+localStorage.getItem('dcfm_token') }});
                    const json = await res.json();
                    if(json.success) this.notifications = json.data;
                } catch(e) {}
            }
        }
    }
</script>
@endsection
