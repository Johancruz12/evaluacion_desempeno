@extends('layouts.app')
@section('title', 'Roles y Permisos')

@section('content')
<div class="space-y-6 max-w-6xl">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 anim-slide-left">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Roles y Permisos</h1>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-100 text-amber-700 ring-1 ring-amber-200">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    Super Admin
                </span>
            </div>
            <p class="text-slate-500 text-sm mt-0.5">Crea roles personalizados y asigna los permisos que cada uno puede ejecutar</p>
        </div>
        <a href="{{ route('admin.roles.create') }}"
           class="btn-bounce inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white font-semibold px-5 py-2.5 rounded-xl transition-all text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nuevo rol
        </a>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-2xl text-sm anim-fade-up">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-2xl text-sm anim-fade-up">{{ session('error') }}</div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 text-center">
            <div class="text-2xl font-extrabold text-blue-600">{{ $roles->count() }}</div>
            <div class="text-xs font-medium text-slate-500 mt-1 uppercase tracking-wider">Roles totales</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 text-center">
            <div class="text-2xl font-extrabold text-emerald-600">{{ $totalPermissions }}</div>
            <div class="text-xs font-medium text-slate-500 mt-1 uppercase tracking-wider">Permisos disponibles</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 text-center">
            <div class="text-2xl font-extrabold text-amber-500">{{ $roles->sum('users_count') }}</div>
            <div class="text-xs font-medium text-slate-500 mt-1 uppercase tracking-wider">Asignaciones</div>
        </div>
    </div>

    {{-- Tabla de roles --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-semibold text-slate-800">Roles del sistema</h3>
            <p class="text-xs text-slate-400">Los roles marcados como «Sistema» no pueden eliminarse ni cambiar su identificador interno</p>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($roles as $role)
            @php $isSystem = in_array($role->slug, ['director_rh','jefe_area','empleado']); @endphp
            <div class="px-6 py-4 flex items-center gap-4 hover:bg-slate-50 transition-colors">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-bold flex-shrink-0
                    {{ $isSystem ? 'bg-amber-50 text-amber-700 ring-2 ring-amber-200' : 'bg-blue-50 text-blue-700 ring-2 ring-blue-200' }}">
                    @if($isSystem)
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                    @else
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/></svg>
                    @endif
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="font-semibold text-slate-800">{{ $role->description }}</p>
                        @if($isSystem)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-100 text-amber-700 ring-1 ring-amber-200">Sistema</span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-100 text-blue-700 ring-1 ring-blue-200">Personalizado</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-4 mt-1 text-xs text-slate-500">
                        <span class="font-mono text-slate-400">{{ $role->slug }}</span>
                        <span class="inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            {{ $role->permissions_count }} permisos
                        </span>
                        <span class="inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            {{ $role->users_count }} usuarios
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.roles.edit', $role) }}"
                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Editar
                    </a>
                    @if(!$isSystem && $role->users_count === 0)
                    <form method="POST" action="{{ route('admin.roles.destroy', $role) }}"
                          onsubmit="return confirm('¿Eliminar rol «{{ addslashes($role->description) }}»? Esta acción no se puede deshacer.');">
                        @csrf @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/></svg>
                            Eliminar
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @empty
            <div class="px-6 py-12 text-center text-slate-400">Aún no hay roles registrados.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
