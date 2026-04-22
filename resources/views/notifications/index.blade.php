@extends('layouts.app')
@section('title', 'Notificaciones')

@section('content')
@php
$typeConfig = [
    'evaluation_created'   => ['icon' => 'M12 4v16m8-8H4',                                    'bg' => 'bg-blue-100',    'text' => 'text-blue-600',    'border' => 'border-blue-200'],
    'deadline_approaching' => ['icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',     'bg' => 'bg-amber-100',   'text' => 'text-amber-600',   'border' => 'border-amber-200'],
    'employee_completed'   => ['icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',   'bg' => 'bg-emerald-100', 'text' => 'text-emerald-600', 'border' => 'border-emerald-200'],
    'boss_review'          => ['icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857', 'bg' => 'bg-indigo-100', 'text' => 'text-indigo-600', 'border' => 'border-indigo-200'],
    'rh_closed'            => ['icon' => 'M5 13l4 4L19 7',                                    'bg' => 'bg-purple-100',  'text' => 'text-purple-600',  'border' => 'border-purple-200'],
    'evaluation_reopened'  => ['icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15', 'bg' => 'bg-orange-100', 'text' => 'text-orange-600', 'border' => 'border-orange-200'],
];
@endphp

<div class="space-y-6 max-w-3xl mx-auto">

    {{-- Header --}}
    <div class="anim-slide-left flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-blue-500 to-sky-500 flex items-center justify-center shadow-lg shadow-blue-500/25">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </div>
            <div>
                <h1 class="text-xl font-extrabold text-slate-800">Notificaciones</h1>
                <p class="text-slate-500 text-xs mt-0.5">Centro de notificaciones del sistema</p>
            </div>
        </div>
        @if($notifications->where('read_at', null)->count() > 0)
        <form method="POST" action="{{ route('notifications.mark-all-read') }}">
            @csrf
            <button type="submit" class="btn-bounce inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-sm font-semibold transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Marcar todas como leídas
            </button>
        </form>
        @endif
    </div>

    {{-- Notifications list --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="h-1 bg-gradient-to-r from-blue-400 via-sky-400 to-indigo-400"></div>

        @forelse($notifications as $notif)
        @php
            $cfg = $typeConfig[$notif->type] ?? ['icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'bg' => 'bg-slate-100', 'text' => 'text-slate-600', 'border' => 'border-slate-200'];
            $isUnread = is_null($notif->read_at);
        @endphp
        <form method="POST" action="{{ route('notifications.mark-read', $notif) }}" class="block">
            @csrf
            <button type="submit" class="w-full text-left flex items-start gap-4 px-5 py-4 border-b border-slate-100 last:border-0 hover:bg-slate-50/50 transition-colors {{ $isUnread ? 'bg-blue-50/30' : '' }}">
                <div class="flex-shrink-0 w-10 h-10 rounded-xl {{ $cfg['bg'] }} flex items-center justify-center mt-0.5">
                    <svg class="w-5 h-5 {{ $cfg['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $cfg['icon'] }}"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="text-sm font-bold text-slate-800 {{ $isUnread ? '' : 'font-semibold text-slate-600' }}">{{ $notif->title }}</p>
                        @if($isUnread)
                        <span class="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0"></span>
                        @endif
                    </div>
                    <p class="text-sm text-slate-600 mt-0.5 line-clamp-2">{{ $notif->message }}</p>
                    <p class="text-xs text-slate-400 mt-1.5">{{ $notif->created_at->diffForHumans() }}</p>
                </div>
                <div class="flex-shrink-0 mt-1">
                    <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
            </button>
        </form>
        @empty
        <div class="py-16 text-center">
            <div class="w-16 h-16 bg-gradient-to-br from-slate-100 to-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-inner">
                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </div>
            <p class="text-slate-700 font-bold text-base">Sin notificaciones</p>
            <p class="text-slate-400 text-sm mt-1">No tienes notificaciones por el momento</p>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($notifications->hasPages())
    <div class="flex justify-center">
        {{ $notifications->links() }}
    </div>
    @endif

</div>
@endsection
