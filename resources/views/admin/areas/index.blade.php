@extends('layouts.app')
@section('title', 'Áreas')

@section('content')
<div x-data="{ showForm: false }" class="space-y-5 max-w-3xl">

    <div class="flex items-center justify-between anim-slide-left">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Áreas</h1>
            <p class="text-slate-500 text-sm mt-0.5">{{ count($areas) }} área(s) configuradas</p>
        </div>
        <button @click="showForm=!showForm"
                class="btn-bounce inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white font-semibold px-4 py-2.5 rounded-xl transition-all shadow-md shadow-blue-500/20 text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nueva área
        </button>
    </div>

    {{-- New area form --}}
    <div x-show="showForm" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
        <h3 class="font-semibold text-slate-800 mb-4">Nueva área</h3>
        <form method="POST" action="{{ route('admin.areas.store') }}">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Nombre</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Descripción</label>
                    <input type="text" name="description" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-4">
                <button type="button" @click="showForm=false" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">Cancelar</button>
                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-xl transition-colors">Crear</button>
            </div>
        </form>
    </div>

    {{-- Areas list --}}
    <div class="anim-fade-up bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        @forelse($areas as $area)
        <div class="border-b border-slate-100 last:border-0">
            <div class="flex items-center gap-3 px-5 py-4 row-hover">
                <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m14 0h2M5 21H3M9 7h1m-1 4h1m4-4h1m-1 4h1M9 21V11h6v10"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="font-semibold text-slate-800 text-sm">{{ $area->name }}</p>
                        @if(!$area->is_active)
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs bg-rose-50 text-rose-600">Inactiva</span>
                        @endif
                    </div>
                    @if($area->description)
                    <p class="text-xs text-slate-400 mt-0.5 truncate">{{ $area->description }}</p>
                    @endif
                    <div class="flex items-center gap-3 mt-0.5 text-xs text-slate-400">
                        <span>{{ $area->position_types_count }} cargo(s)</span>
                        <span>·</span>
                        <span>{{ $area->users_count }} usuario(s)</span>
                    </div>
                </div>
                <button type="button" onclick="document.getElementById('area-edit-{{ $area->id }}').classList.toggle('hidden')"
                        class="w-8 h-8 rounded-lg bg-blue-100 hover:bg-blue-600 flex items-center justify-center text-blue-600 hover:text-white transition-all duration-200 border border-blue-200 hover:border-blue-600 shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                </button>
                <form method="POST" action="{{ route('admin.areas.destroy', $area) }}" class="inline" onsubmit="return confirm('⚠️ ¿Está seguro de eliminar el área «{{ $area->name }}»?\n\nLos cargos y empleados asociados podrían verse afectados. Esta acción no se puede deshacer.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-8 h-8 rounded-lg bg-rose-100 hover:bg-rose-600 flex items-center justify-center text-rose-600 hover:text-white transition-all duration-200 border border-rose-200 hover:border-rose-600">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </form>
            </div>

            {{-- Inline edit --}}
            <div id="area-edit-{{ $area->id }}" class="hidden border-t border-dashed border-slate-200 bg-slate-50 px-5 py-4">
                <form method="POST" action="{{ route('admin.areas.update', $area) }}">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Nombre</label>
                            <input type="text" name="name" value="{{ $area->name }}" required class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Descripción</label>
                            <input type="text" name="description" value="{{ $area->description }}" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 cursor-pointer select-none text-sm">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" {{ $area->is_active ? 'checked' : '' }} class="w-4 h-4 rounded text-blue-600">
                            <span class="text-slate-600">Activa</span>
                        </label>
                        <div class="ml-auto flex gap-2">
                            <button type="button" onclick="document.getElementById('area-edit-{{ $area->id }}').classList.add('hidden')" class="px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">Cancelar</button>
                            <button type="submit" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold rounded-xl transition-colors">Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @empty
        <div class="py-12 text-center text-slate-400 text-sm">No hay áreas registradas</div>
        @endforelse
    </div>

</div>
@endsection
