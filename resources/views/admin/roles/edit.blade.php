@extends('layouts.app')
@section('title', 'Editar rol: ' . $role->description)

@php $isSystem = in_array($role->slug, ['director_rh','jefe_area','empleado']); @endphp

@section('content')
<div class="space-y-6 max-w-5xl" x-data="{ tab: 'permissions' }">

    {{-- Header --}}
    <div class="anim-slide-left">
        <a href="{{ route('admin.roles.index') }}" class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-blue-600 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Volver a roles
        </a>
        <div class="flex items-center gap-3 flex-wrap">
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">{{ $role->description }}</h1>
            @if($isSystem)
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-100 text-amber-700 ring-1 ring-amber-200">Rol del sistema</span>
            @else
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-100 text-blue-700 ring-1 ring-blue-200">Personalizado</span>
            @endif
        </div>
        <p class="text-slate-500 text-sm mt-0.5 font-mono">{{ $role->slug }}</p>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-2xl text-sm anim-fade-up">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-2xl text-sm anim-fade-up">{{ session('error') }}</div>
    @endif
    @if($errors->any())
    <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-2xl text-sm">
        @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
    </div>
    @endif

    {{-- Tabs --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="flex border-b border-slate-200 bg-slate-50">
            <button type="button" @click="tab = 'permissions'"
                    :class="tab === 'permissions' ? 'bg-white border-b-2 border-blue-500 text-blue-700' : 'text-slate-500 hover:text-slate-700'"
                    class="px-6 py-3 text-sm font-semibold transition-all">
                Permisos
                <span class="ml-1.5 text-xs font-bold bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded-full">{{ count($assignedPermissionIds) }}</span>
            </button>
            <button type="button" @click="tab = 'users'"
                    :class="tab === 'users' ? 'bg-white border-b-2 border-blue-500 text-blue-700' : 'text-slate-500 hover:text-slate-700'"
                    class="px-6 py-3 text-sm font-semibold transition-all">
                Usuarios asignados
                <span class="ml-1.5 text-xs font-bold bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded-full">{{ $assignedUsers->count() }}</span>
            </button>
        </div>

        {{-- ═══ TAB: Permisos ═══ --}}
        <div x-show="tab === 'permissions'" class="p-6">
            <form method="POST" action="{{ route('admin.roles.update', $role) }}"
                  x-data="{ selected: {{ json_encode($assignedPermissionIds) }} }">
                @csrf @method('PUT')

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nombre del rol</label>
                        <input type="text" name="description" value="{{ old('description', $role->description) }}" required maxlength="100"
                               class="w-full rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 px-4 py-2.5 text-sm">
                    </div>

                    @if(!$isSystem)
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Identificador técnico</label>
                        <input type="text" name="slug" value="{{ old('slug', $role->slug) }}" maxlength="80"
                               class="w-full rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 px-4 py-2.5 text-sm font-mono">
                    </div>
                    @endif
                </div>

                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4 mt-6">
                    <h3 class="font-semibold text-slate-800">Permisos del rol</h3>
                    <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-full" x-text="selected.length + ' seleccionado(s)'"></span>
                </div>

                <div class="flex items-center gap-3 mb-4 text-xs">
                    <button type="button" @click="selected = [{{ $permissions->pluck('id')->implode(',') }}]"
                            class="px-3 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium">Seleccionar todo</button>
                    <button type="button" @click="selected = []"
                            class="px-3 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium">Limpiar</button>
                </div>

                <div class="grid sm:grid-cols-2 gap-2">
                    @foreach($permissions as $perm)
                    <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 hover:border-blue-300 hover:bg-blue-50/40 cursor-pointer transition-all"
                           :class="selected.includes({{ $perm->id }}) ? 'border-blue-400 bg-blue-50' : ''">
                        <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                               x-model="selected" :value="{{ $perm->id }}"
                               class="mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-700">{{ $perm->description }}</p>
                            <p class="text-xs text-slate-400 font-mono">{{ $perm->slug }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>

                <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-slate-100">
                    <a href="{{ route('admin.roles.index') }}" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-100">Cancelar</a>
                    <button type="submit" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white font-semibold px-5 py-2.5 rounded-xl transition-all text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>

        {{-- ═══ TAB: Usuarios asignados ═══ --}}
        <div x-show="tab === 'users'" x-cloak class="p-6 space-y-6">

            {{-- Asignar nuevo usuario --}}
            <div class="bg-slate-50 rounded-2xl border border-slate-200 p-4">
                <h3 class="font-semibold text-slate-800 mb-3">Asignar usuario a este rol</h3>
                <form method="POST" action="{{ route('admin.roles.users.attach', $role) }}"
                      x-data="{ q: '', uid: '' }"
                      class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    @csrf
                    <div class="relative flex-1" @click.outside="q = ''">
                        <input type="text" x-model="q" placeholder="Buscar por cédula o nombre..."
                               class="w-full rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 px-4 py-2.5 text-sm">
                        <input type="hidden" name="user_id" :value="uid" required>

                        <div x-show="q.length >= 2" x-cloak
                             class="absolute left-0 right-0 top-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg max-h-64 overflow-y-auto z-20">
                            @foreach($availableUsers as $u)
                            <button type="button"
                                    data-search="{{ Str::lower(($u->person?->first_name ?? '') . ' ' . ($u->person?->last_name ?? '') . ' ' . $u->login) }}"
                                    data-id="{{ $u->id }}"
                                    data-label="{{ trim(($u->person?->first_name ?? '') . ' ' . ($u->person?->last_name ?? '')) }} — {{ $u->login }}"
                                    x-show="$el.dataset.search.includes(q.toLowerCase())"
                                    @click="uid = $el.dataset.id; q = $el.dataset.label"
                                    class="w-full px-4 py-2.5 text-left hover:bg-blue-50 border-b border-slate-100 last:border-0 text-sm">
                                <p class="font-medium text-slate-700">{{ trim(($u->person?->first_name ?? '') . ' ' . ($u->person?->last_name ?? '')) ?: $u->login }}</p>
                                <p class="text-xs text-slate-400">CC {{ $u->login }} @if($u->area) · {{ $u->area->name }} @endif</p>
                            </button>
                            @endforeach
                        </div>
                    </div>
                    <button type="submit" :disabled="!uid"
                            :class="uid ? 'bg-blue-600 hover:bg-blue-500 text-white' : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                            class="inline-flex items-center justify-center gap-2 font-semibold px-5 py-2.5 rounded-xl transition-all text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Asignar
                    </button>
                </form>
                <p class="text-xs text-slate-400 mt-2">Solo se muestran usuarios activos que aún no tienen este rol.</p>
            </div>

            {{-- Lista de usuarios ya asignados --}}
            <div>
                <h3 class="font-semibold text-slate-800 mb-3">Usuarios con este rol ({{ $assignedUsers->count() }})</h3>

                @if($assignedUsers->isEmpty())
                <div class="text-center py-8 text-slate-400 text-sm bg-slate-50 rounded-2xl">
                    Aún no hay usuarios con este rol.
                </div>
                @else
                <div class="bg-white rounded-2xl border border-slate-200 divide-y divide-slate-100">
                    @foreach($assignedUsers as $u)
                    <div class="px-5 py-3 flex items-center gap-4">
                        <div class="w-9 h-9 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-xs font-bold flex-shrink-0">
                            {{ strtoupper(substr($u->person?->first_name ?? '?', 0, 1)) }}{{ strtoupper(substr($u->person?->last_name ?? '', 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-slate-700 text-sm truncate">
                                {{ trim(($u->person?->last_name ?? '') . ' ' . ($u->person?->first_name ?? '')) ?: $u->login }}
                            </p>
                            <p class="text-xs text-slate-400">
                                CC {{ $u->login }}
                                @if($u->area)<span>·</span> {{ $u->area->name }}@endif
                            </p>
                        </div>
                        <form method="POST" action="{{ route('admin.roles.users.detach', [$role, $u]) }}"
                              onsubmit="return confirm('¿Quitar este rol al usuario {{ addslashes(($u->person?->first_name ?? '') . ' ' . ($u->person?->last_name ?? '')) }}?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                Quitar rol
                            </button>
                        </form>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
