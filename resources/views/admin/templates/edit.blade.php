@extends('layouts.app')
@section('title', 'Configurar Plantilla')

@section('content')
@php
// Build type metadata map from DB (cached). Falls back to the old static map if collection is empty.
$sectionTypes = isset($sectionTypes) ? $sectionTypes->keyBy('slug') : collect();
$typeMeta = [];
foreach ($sectionTypes as $st) {
    $typeMeta[$st->slug] = [$st->label, $st->gradient, $st->badge_class . ' ' . $st->border_class, $st->behavior];
}
$totalCriteria = $template->sections->sum(fn($s) => $s->criteria->count());
$totalMaxScore = $template->sections->sum(fn($s) => $s->criteria->sum('max_score'));
@endphp

<div x-data="templateEditor()" class="space-y-6 max-w-5xl">

    {{-- Header --}}
    <div class="anim-slide-left flex items-center gap-4">
        <a href="{{ route('admin.templates.index') }}"
           class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-200 hover:bg-blue-100 hover:border-blue-300 flex items-center justify-center text-blue-500 hover:text-blue-700 transition-all shadow-sm hover:shadow-md">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div class="flex-1 min-w-0">
            <h1 class="text-xl font-extrabold text-slate-800 truncate flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-sky-500 flex items-center justify-center shadow-lg shadow-blue-500/25 flex-shrink-0">
                    <svg class="w-4.5 h-4.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
                </div>
                {{ $template->name }}
            </h1>
            <p class="text-slate-500 text-xs mt-0.5 ml-12">Configura secciones, criterios y rangos de calificación</p>
        </div>
    </div>

    {{-- Quick stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 anim-fade-up">
        <div class="bg-white rounded-xl border border-slate-200 px-4 py-3 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center"><svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg></div>
            <div><p class="text-lg font-extrabold text-slate-800">{{ $template->sections->count() }}</p><p class="text-[10px] text-slate-500 uppercase font-semibold">Secciones</p></div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 px-4 py-3 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center"><svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/></svg></div>
            <div><p class="text-lg font-extrabold text-slate-800">{{ $totalCriteria }}</p><p class="text-[10px] text-slate-500 uppercase font-semibold">Criterios</p></div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 px-4 py-3 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-amber-100 flex items-center justify-center"><svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg></div>
            <div><p class="text-lg font-extrabold text-slate-800">{{ $totalMaxScore }}</p><p class="text-[10px] text-slate-500 uppercase font-semibold">Puntaje máx.</p></div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 px-4 py-3 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-purple-100 flex items-center justify-center"><svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg></div>
            <div><p class="text-lg font-extrabold text-slate-800">{{ $template->scoringRanges->count() }}</p><p class="text-[10px] text-slate-500 uppercase font-semibold">Rangos</p></div>
        </div>
    </div>

    {{-- Template info card --}}
    <div class="anim-fade-up bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

    {{-- Orphaned evaluations warning --}}
    @if($orphanedCount > 0)
    <div class="mx-6 mt-5 flex items-start gap-3 p-4 bg-amber-50 border border-amber-200 rounded-xl">
        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-amber-800">
                {{ $orphanedCount }} evaluación(es) de participantes sin área asignada
            </p>
            <p class="text-xs text-amber-700 mt-0.5">
                @if($template->areas->isEmpty())
                    Esta plantilla no tiene áreas asignadas pero aún existen {{ $orphanedCount }} evaluaciones activas. Usa el botón para eliminarlas.
                @else
                    Existen evaluaciones de empleados cuya área ya no está asignada a esta plantilla.
                @endif
            </p>
        </div>
        <form method="POST" action="{{ route('admin.templates.cleanup-participants', $template) }}"
              onsubmit="return confirm('¿Eliminar {{ $orphanedCount }} evaluación(es) de participantes que ya no pertenecen a las áreas asignadas? Esta acción no se puede deshacer.')">
            @csrf
            <button type="submit"
                    class="flex-shrink-0 inline-flex items-center gap-1.5 px-3 py-2 bg-amber-600 hover:bg-amber-500 text-white text-xs font-semibold rounded-lg transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Limpiar ahora
            </button>
        </form>
    </div>
    @endif
        <div class="h-1 bg-gradient-to-r from-blue-400 via-sky-400 to-indigo-400"></div>
        <div class="p-6">
            <form method="POST" action="{{ route('admin.templates.update', $template) }}" class="space-y-5">
                @csrf @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Nombre</label>
                    <input type="text" name="name" value="{{ old('name', $template->name) }}" required
                           class="w-full px-4 py-3 border border-slate-200 rounded-xl text-slate-800 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all bg-slate-50 focus:bg-white">
                </div>

                {{-- Areas --}}
                <div x-data="{
                    open: false,
                    searchQuery: '',
                    areas: {{ Js::from($areas->map(fn($a) => ['id' => $a->id, 'name' => $a->name, 'checked' => $template->areas->contains($a->id)])->values()) }},
                    get filteredAreas() {
                        if (!this.searchQuery) return this.areas;
                        const q = this.searchQuery.toLowerCase();
                        return this.areas.filter(a => a.name.toLowerCase().includes(q));
                    },
                    get selectedAreas() { return this.areas.filter(a => a.checked); },
                    toggle(area) { area.checked = !area.checked; },
                    selectAll() { this.areas.forEach(a => a.checked = true); },
                    clearAll() { this.areas.forEach(a => a.checked = false); }
                }">
                    {{-- Hidden inputs para el formulario --}}
                    <template x-for="area in selectedAreas" :key="area.id">
                        <input type="hidden" name="area_ids[]" :value="area.id">
                    </template>

                    {{-- Resumen compacto + botón abrir --}}
                    <div class="flex items-center justify-between gap-3 p-4 border-2 border-slate-200 rounded-2xl bg-slate-50 hover:border-blue-300 transition-colors">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1.5">
                                <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>
                                <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">Áreas asignadas</span>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold"
                                      :class="selectedAreas.length > 0 ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-500'"
                                      x-text="selectedAreas.length > 0 ? selectedAreas.length + ' seleccionada' + (selectedAreas.length !== 1 ? 's' : '') : 'Ninguna (global)'"></span>
                            </div>
                            {{-- Chips preview --}}
                            <div class="flex flex-wrap gap-1 min-h-[20px]">
                                <template x-if="selectedAreas.length === 0">
                                    <span class="text-xs text-slate-400 italic">Sin restricción — visible para todas las áreas</span>
                                </template>
                                <template x-for="area in selectedAreas.slice(0,6)" :key="area.id">
                                    <span class="inline-flex items-center px-2 py-0.5 bg-blue-100 text-blue-700 rounded-md text-[11px] font-medium border border-blue-200" x-text="area.name"></span>
                                </template>
                                <template x-if="selectedAreas.length > 6">
                                    <span class="inline-flex items-center px-2 py-0.5 bg-slate-200 text-slate-600 rounded-md text-[11px] font-semibold" x-text="'+' + (selectedAreas.length - 6) + ' más'"></span>
                                </template>
                            </div>
                        </div>
                        <button type="button" @click="open = true; $nextTick(() => $refs.areaSearch.focus())"
                                class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-xl transition-all shadow-sm hover:shadow-blue-400/30 hover:shadow-md">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Gestionar áreas
                        </button>
                    </div>

                    {{-- Modal pantalla completa (teleportado al body para evitar stacking context del layout) --}}
                    <template x-teleport="body">
                    <div x-show="open" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="flex flex-col bg-white"
                         style="position:fixed;top:0;left:0;right:0;bottom:0;z-index:9999;display:none;">

                        {{-- Barra superior --}}
                        <div class="flex-shrink-0 bg-gradient-to-r from-blue-600 to-sky-500 px-6 py-4 flex items-center justify-between shadow-lg">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>
                                </div>
                                <div>
                                    <h2 class="text-lg font-bold text-white">Seleccionar Áreas</h2>
                                    <p class="text-blue-100 text-xs">
                                        <span class="font-semibold" x-text="selectedAreas.length"></span> de
                                        <span class="font-semibold" x-text="areas.length"></span> áreas seleccionadas
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <button type="button" @click="selectAll()"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-white/20 hover:bg-white/30 text-white text-sm font-semibold rounded-xl transition-all border border-white/30">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Seleccionar todas
                                </button>
                                <button type="button" @click="clearAll()"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-white/10 hover:bg-white/20 text-white text-sm font-semibold rounded-xl transition-all border border-white/20">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    Limpiar
                                </button>
                                <button type="button" @click="open = false; searchQuery = ''"
                                        class="w-10 h-10 rounded-xl bg-white/20 hover:bg-white/30 flex items-center justify-center text-white border border-white/30 transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>

                        {{-- Buscador --}}
                        <div class="flex-shrink-0 px-6 py-4 bg-slate-50 border-b border-slate-200">
                            <div class="relative max-w-2xl">
                                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <input type="text" x-model="searchQuery" x-ref="areaSearch"
                                       placeholder="Buscar área por nombre..."
                                       class="w-full pl-12 pr-10 py-3.5 border-2 border-slate-200 rounded-2xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white shadow-sm placeholder:text-slate-400 text-slate-800">
                                <button type="button" x-show="searchQuery" @click="searchQuery = ''"
                                        class="absolute right-4 top-1/2 -translate-y-1/2 w-6 h-6 rounded-full bg-slate-200 hover:bg-slate-300 flex items-center justify-center text-slate-500">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            <p class="text-xs text-slate-400 mt-2 pl-1">
                                Mostrando <span class="font-semibold text-slate-600" x-text="filteredAreas.length"></span>
                                de <span class="font-semibold text-slate-600" x-text="areas.length"></span> áreas
                                <template x-if="searchQuery">
                                    <span> · coincidentes con "<span class="text-blue-600 font-semibold" x-text="searchQuery"></span>"</span>
                                </template>
                            </p>
                        </div>

                        {{-- Grid de áreas --}}
                        <div class="flex-1 overflow-y-auto px-6 py-5" style="scrollbar-width: thin;">
                            <template x-if="filteredAreas.length > 0">
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-2.5">
                                    <template x-for="area in filteredAreas" :key="area.id">
                                        <label @click="toggle(area)"
                                               class="flex items-center gap-3 px-4 py-3.5 rounded-2xl cursor-pointer transition-all group border-2 select-none"
                                               :class="area.checked
                                                   ? 'bg-blue-50 border-blue-500 shadow-sm shadow-blue-100'
                                                   : 'bg-white border-slate-200 hover:border-blue-300 hover:bg-blue-50/50 hover:shadow-sm'">
                                            <div class="w-5 h-5 rounded-md flex-shrink-0 border-2 flex items-center justify-center transition-all"
                                                 :class="area.checked ? 'bg-blue-600 border-blue-600' : 'border-slate-300 bg-white group-hover:border-blue-400'">
                                                <svg x-show="area.checked" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            </div>
                                            <span class="text-sm font-medium flex-1 leading-tight transition-colors"
                                                  :class="area.checked ? 'text-blue-700' : 'text-slate-700 group-hover:text-blue-700'"
                                                  x-text="area.name"></span>
                                        </label>
                                    </template>
                                </div>
                            </template>
                            <template x-if="filteredAreas.length === 0">
                                <div class="flex flex-col items-center justify-center h-full py-24 text-slate-400">
                                    <svg class="w-16 h-16 mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    <p class="text-lg font-semibold text-slate-500">Sin resultados</p>
                                    <p class="text-sm mt-1">No hay áreas que coincidan con "<span class="text-blue-500 font-medium" x-text="searchQuery"></span>"</p>
                                </div>
                            </template>
                        </div>

                        {{-- Pie: chips de seleccionadas + confirmar --}}
                        <div class="flex-shrink-0 border-t-2 border-slate-100 bg-white px-6 py-4">
                            <div class="flex items-start gap-4">
                                <div class="flex-1 min-w-0">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">
                                        <span x-text="selectedAreas.length > 0 ? 'Seleccionadas' : 'Sin restricción de área'"></span>
                                    </p>
                                    <div class="flex flex-wrap gap-1.5 max-h-16 overflow-y-auto" style="scrollbar-width: thin;">
                                        <template x-if="selectedAreas.length === 0">
                                            <span class="text-sm text-slate-400 italic">La plantilla será visible para todas las áreas</span>
                                        </template>
                                        <template x-for="area in selectedAreas" :key="area.id">
                                            <span x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-75" x-transition:enter-end="opacity-100 scale-100"
                                                  class="inline-flex items-center gap-1 pl-2.5 pr-1 py-1 bg-blue-100 text-blue-700 rounded-lg text-xs font-semibold border border-blue-200">
                                                <span x-text="area.name"></span>
                                                <button type="button" @click.stop="toggle(area)"
                                                        class="w-4 h-4 rounded-full bg-blue-200 hover:bg-blue-500 hover:text-white flex items-center justify-center transition-all">
                                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </span>
                                        </template>
                                    </div>
                                </div>
                                <button type="button" @click="open = false; searchQuery = ''"
                                        class="flex-shrink-0 inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-2xl transition-all shadow-md hover:shadow-blue-400/30 text-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Confirmar selección
                                </button>
                            </div>
                        </div>
                    </div>
                    </template>
                </div>

                {{-- Instructions --}}
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                        Instrucciones del PDF
                        <span class="text-[10px] font-normal text-slate-400 normal-case ml-1">— texto que aparece en la sección "1. INSTRUCCIONES"</span>
                    </label>
                    <textarea name="instructions" rows="5"
                              placeholder="Escribe las instrucciones de la evaluación para el PDF…"
                              class="w-full px-4 py-3 border border-slate-200 rounded-xl text-slate-800 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all bg-slate-50 focus:bg-white resize-y">{{ old('instructions', $template->instructions) }}</textarea>
                    <p class="text-xs text-slate-400 mt-1">Cada línea en blanco genera un párrafo separado en el PDF.</p>
                </div>

                {{-- Score scale labels --}}
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-3">
                        Escala de calificación (1 – 5)
                        <span class="text-[10px] font-normal text-slate-400 normal-case ml-1">— descripción de cada nivel de puntuación en el PDF</span>
                    </label>
                    <div class="space-y-2">
                        @php
                            $scaleDefaults = [
                                1 => 'Tiene deficiencias muy significativas en el cumplimiento',
                                2 => 'Tiene algunas deficiencias en el cumplimiento',
                                3 => 'Cumple',
                                4 => 'Tiende a exceder el cumplimiento',
                                5 => 'Excede frecuentemente de manera significativa el cumplimiento',
                            ];
                            $savedScale = $template->score_scale ?? [];
                        @endphp
                        @for($i = 1; $i <= 5; $i++)
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 flex-shrink-0 rounded-lg bg-blue-600 text-white text-xs font-black flex items-center justify-center">{{ $i }}</span>
                            <input type="text" name="score_scale[{{ $i }}]"
                                   value="{{ old("score_scale.$i", $savedScale[$i] ?? $scaleDefaults[$i]) }}"
                                   placeholder="{{ $scaleDefaults[$i] }}"
                                   class="flex-1 px-4 py-2.5 border border-slate-200 rounded-xl text-slate-800 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all bg-slate-50 focus:bg-white">
                        </div>
                        @endfor
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <div class="relative">
                            <input type="checkbox" name="is_active" value="1" {{ $template->is_active ? 'checked' : '' }}
                                   class="sr-only peer" id="toggle-active">
                            <div class="w-11 h-6 bg-slate-200 peer-checked:bg-blue-500 rounded-full transition-colors peer-focus:ring-2 peer-focus:ring-blue-300"></div>
                            <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform peer-checked:translate-x-5"></div>
                        </div>
                        <label for="toggle-active" class="text-sm text-slate-700 font-medium cursor-pointer">Plantilla activa</label>
                    </label>
                    <button type="submit" class="btn-bounce inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-1 bg-white rounded-2xl p-1.5 border border-slate-200 shadow-sm w-fit anim-fade-up">
        <button @click="tab='sections'" :class="tab==='sections' ? 'bg-blue-500 text-white' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'"
                class="px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                Secciones
                <span class="px-2 py-0.5 rounded-full text-xs font-bold" :class="tab==='sections' ? 'bg-white/25' : 'bg-blue-100 text-blue-700'">{{ $template->sections->count() }}</span>
            </span>
        </button>
        <button @click="tab='ranges'" :class="tab==='ranges' ? 'bg-blue-500 text-white' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'"
                class="px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                Rangos
                <span class="px-2 py-0.5 rounded-full text-xs font-bold" :class="tab==='ranges' ? 'bg-white/25' : 'bg-blue-100 text-blue-700'">{{ $template->scoringRanges->count() }}</span>
            </span>
        </button>
    </div>

    {{-- ── SECCIONES ── --}}
    <div x-show="tab==='sections'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-4">

        @foreach($template->sections->sortBy('order') as $section)
        @php [$typeLabel, $typeGradient, $typeClass, $typeBehavior] = $typeMeta[$section->type] ?? ['Otro', 'from-slate-500 to-gray-500', 'bg-slate-50 text-slate-600 border-slate-200', 'calificable']; @endphp

        <div x-data="{open: false}" class="anim-fade-up bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden hover:shadow-md transition-all duration-300">
            {{-- Section accent --}}
            <div class="h-1 bg-gradient-to-r {{ $typeGradient }}"></div>

            {{-- Section header --}}
            <div class="flex items-center gap-3 px-5 py-4 cursor-pointer" @click="open=!open">
                <button type="button" class="w-8 h-8 rounded-xl flex items-center justify-center text-slate-400 flex-shrink-0 transition-all duration-300 hover:bg-slate-100" :class="open ? 'bg-slate-100 rotate-90' : ''">
                    <svg class="w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="font-bold text-slate-800 text-sm">{{ $section->name }}</p>
                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $typeClass }}">{{ $typeLabel }}</span>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">
                        @if($typeBehavior === 'texto')
                            {{ count((array) ($section->content ?? [])) }} párrafo(s) · Sección informativa
                        @elseif($typeBehavior === 'rango')
                            Tabla de rangos
                        @else
                            {{ $section->criteria->count() }} criterio(s) · Puntaje máx: {{ $section->criteria->sum('max_score') }}
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-1 flex-shrink-0" @click.stop>
                    <button type="button" onclick='openEditSection({{ $section->id }}, @json($section->name), "{{ $section->type }}", @json($section->description ?? ""), {{ $section->weight ?? 0 }}, @json($section->content ?? []))'
                            class="w-8 h-8 rounded-xl hover:bg-blue-50 flex items-center justify-center text-slate-400 hover:text-blue-600 transition-all" title="Editar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    </button>
                    <form method="POST" action="{{ route('admin.sections.destroy', $section) }}"
                          data-confirm-title="¿Eliminar sección?"
                          data-confirm="Se eliminarán también todos los criterios de '{{ $section->name }}'. Esta acción no se puede deshacer.">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-8 h-8 rounded-xl hover:bg-rose-50 flex items-center justify-center text-slate-400 hover:text-rose-600 transition-all" title="Eliminar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Criteria list --}}
            <div x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-3" x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 -translate-y-2"
                 class="border-t border-slate-100">

                @if($typeBehavior === 'texto')
                <div class="px-5 py-5 space-y-3">
                    @if(!empty($section->content))
                        @foreach((array) $section->content as $i => $paragraph)
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-lg bg-violet-100 text-violet-600 flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">{{ $i + 1 }}</div>
                            <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line flex-1">{{ $paragraph }}</p>
                        </div>
                        @endforeach
                    @else
                        <p class="text-sm text-slate-400 italic">Sin párrafos. Usa el botón «Editar» para agregar contenido.</p>
                    @endif
                </div>
                @else
                @forelse($section->criteria->sortBy('order') as $c)
                <div class="flex items-center gap-3 px-5 py-3 row-hover border-b border-slate-50 last:border-0 group">
                    <div class="w-7 h-7 rounded-lg bg-gradient-to-br {{ $typeGradient }} flex items-center justify-center text-white text-xs font-bold flex-shrink-0 shadow-sm">
                        {{ $loop->iteration }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-700">{{ $c->name }}</p>
                        @if($c->description)<p class="text-xs text-slate-400 truncate">{{ $c->description }}</p>@endif
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 text-xs font-bold px-2.5 py-1 rounded-lg border border-amber-200">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                            {{ $c->max_score }}
                        </span>
                        <button type="button" onclick="openEditCriteria({{ $c->id }}, '{{ addslashes($c->name) }}', '{{ addslashes($c->description ?? '') }}', {{ $c->max_score }})"
                                class="w-7 h-7 rounded-lg hover:bg-blue-50 flex items-center justify-center text-slate-300 hover:text-blue-600 transition-all opacity-0 group-hover:opacity-100">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        </button>
                        <form method="POST" action="{{ route('admin.criteria.destroy', $c) }}"
                              data-confirm-title="¿Eliminar criterio?"
                              data-confirm="Se eliminará '{{ $c->name }}'. Esta acción no se puede deshacer.">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-7 h-7 rounded-lg hover:bg-rose-50 flex items-center justify-center text-slate-300 hover:text-rose-600 transition-all opacity-0 group-hover:opacity-100">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <div class="px-5 py-6 text-center">
                    <p class="text-slate-400 text-sm">Sin criterios aún</p>
                    <p class="text-slate-300 text-xs mt-0.5">Agrega criterios usando el formulario de abajo</p>
                </div>
                @endforelse

                {{-- Add criteria inline --}}
                <div class="px-5 py-3 bg-gradient-to-r from-slate-50 to-white border-t border-dashed border-slate-200">
                    <form method="POST" action="{{ route('admin.sections.criteria.store', $section) }}" class="flex items-center gap-2">
                        @csrf
                        <input type="text" name="name" required placeholder="Nombre del criterio…"
                               class="flex-1 px-3 py-2 border border-slate-200 rounded-xl text-sm text-slate-700 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all bg-white min-w-0">
                        <input type="number" name="max_score" value="5" min="1" max="10"
                               class="w-16 px-2 py-2 border border-slate-200 rounded-xl text-sm text-center font-bold text-slate-700 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
                        <button type="submit" class="btn-bounce inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all flex-shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Agregar
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
        @endforeach

        {{-- Add section button --}}
        <button type="button" onclick="document.getElementById('modal-add-section').classList.remove('hidden')"
                class="w-full flex items-center justify-center gap-2 px-4 py-4 border-2 border-dashed border-slate-300 rounded-2xl text-slate-500 hover:border-blue-400 hover:text-blue-600 hover:bg-blue-50/50 transition-all text-sm font-semibold group">
            <div class="w-8 h-8 rounded-xl bg-slate-100 group-hover:bg-blue-100 flex items-center justify-center transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            </div>
            Agregar sección
        </button>
    </div>

    {{-- ── RANGOS ── --}}
    <div x-show="tab==='ranges'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-4">
        @foreach($template->scoringRanges->sortByDesc('min_score') as $range)
        <div class="anim-fade-up bg-white rounded-2xl border border-slate-200 shadow-sm p-5 flex items-center gap-4 hover:shadow-md transition-all group">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center shadow-md flex-shrink-0" style="background:{{ $range->color ?? '#94a3b8' }}">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
            </div>
            <div class="flex-1">
                <p class="font-bold text-slate-800 text-sm">{{ $range->label }}</p>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs text-slate-500">{{ $range->min_score }} – {{ $range->max_score }} puntos</span>
                    <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden max-w-[120px]">
                        <div class="h-full rounded-full" style="width: {{ min(100, ($range->max_score / max($template->scoringRanges->max('max_score'), 1)) * 100) }}%; background: {{ $range->color ?? '#94a3b8' }}"></div>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-1 flex-shrink-0">
                <button type="button" onclick="openEditRange({{ $range->id }}, '{{ addslashes($range->label) }}', {{ $range->min_score }}, {{ $range->max_score }}, '{{ $range->color }}')"
                        class="w-8 h-8 rounded-xl hover:bg-blue-50 flex items-center justify-center text-slate-300 group-hover:text-slate-500 hover:!text-blue-600 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                </button>
                <form method="POST" action="{{ route('admin.ranges.destroy', $range) }}"
                      data-confirm-title="¿Eliminar rango?"
                      data-confirm="Se eliminará el rango '{{ $range->label }}'. Esta acción no se puede deshacer.">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-8 h-8 rounded-xl hover:bg-rose-50 flex items-center justify-center text-slate-300 group-hover:text-slate-500 hover:!text-rose-600 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </form>
            </div>
        </div>
        @endforeach

        <button type="button" onclick="document.getElementById('modal-add-range').classList.remove('hidden')"
                class="w-full flex items-center justify-center gap-2 px-4 py-4 border-2 border-dashed border-slate-300 rounded-2xl text-slate-500 hover:border-blue-400 hover:text-blue-600 hover:bg-blue-50/50 transition-all text-sm font-semibold group">
            <div class="w-8 h-8 rounded-xl bg-slate-100 group-hover:bg-blue-100 flex items-center justify-center transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            </div>
            Agregar rango
        </button>
    </div>
</div>

{{-- ══════════════ MODALS ══════════════ --}}

{{-- Modal: Add section --}}
<div id="modal-add-section" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
    <div x-data="sectionForm()" class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden max-h-[90vh] flex flex-col">
        <div class="bg-gradient-to-r from-blue-500 to-sky-500 px-6 py-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nueva sección
                </h3>
                <button onclick="document.getElementById('modal-add-section').classList.add('hidden')" class="w-8 h-8 rounded-xl bg-slate-50 hover:bg-white flex items-center justify-center text-slate-700 transition-colors border border-white/60">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.templates.sections.store', $template) }}" class="p-6 space-y-4 overflow-y-auto">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Nombre / Título</label>
                <input type="text" name="name" required class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50 focus:bg-white">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Tipo</label>
                <select name="type" x-model="type" required class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50 focus:bg-white">
                    @foreach($sectionTypes as $st)
                        <option value="{{ $st->slug }}">{{ $st->icon }} {{ $st->label }}</option>
                    @endforeach
                </select>
                <p x-show="type === 'texto'" class="text-xs text-violet-600 mt-1.5 flex items-start gap-1">
                    <svg class="w-3.5 h-3.5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Las secciones de texto se muestran al evaluado como contenido informativo, sin criterios calificables.
                </p>
            </div>

            {{-- Description for non-text sections --}}
            <div x-show="type !== 'texto'">
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Descripción <span class="text-slate-400 font-normal">(opcional)</span></label>
                <textarea name="description" rows="2" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50 focus:bg-white resize-none"></textarea>
            </div>

            {{-- Multiple descriptions for text sections --}}
            <div x-show="type === 'texto'" x-cloak>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Descripciones</label>
                <p class="text-xs text-slate-500 mb-3">Agrega uno o varios párrafos de texto. Se mostrarán en orden al evaluado.</p>
                <div class="space-y-2">
                    <template x-for="(item, idx) in paragraphs" :key="idx">
                        <div class="flex items-start gap-2">
                            <div class="w-6 h-6 rounded-lg bg-violet-100 text-violet-600 flex items-center justify-center text-xs font-bold flex-shrink-0 mt-2" x-text="idx + 1"></div>
                            <textarea :name="`content[${idx}]`" x-model="paragraphs[idx]" rows="2" placeholder="Escribe el párrafo…"
                                      class="flex-1 px-3 py-2 border border-slate-200 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-violet-500 focus:border-transparent bg-slate-50 focus:bg-white resize-none"></textarea>
                            <button type="button" @click="paragraphs.splice(idx, 1); if(paragraphs.length===0) paragraphs.push('')" class="w-8 h-8 rounded-lg hover:bg-rose-50 flex items-center justify-center text-slate-400 hover:text-rose-600 transition-colors flex-shrink-0 mt-1.5" title="Eliminar párrafo">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </template>
                </div>
                <button type="button" @click="paragraphs.push('')" class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-violet-700 hover:bg-violet-50 rounded-lg border border-violet-200 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Agregar otro párrafo
                </button>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal-add-section').classList.add('hidden')" class="px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">Cancelar</button>
                <button type="submit" class="btn-bounce px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-xl transition-all">Crear sección</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Edit section --}}
<div id="modal-edit-section" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
    <div x-data="editSectionForm()" x-init="$watch('type', () => {})" class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden max-h-[90vh] flex flex-col">
        <div class="bg-gradient-to-r from-blue-500 to-indigo-500 px-6 py-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-white">Editar sección</h3>
                <button onclick="document.getElementById('modal-edit-section').classList.add('hidden')" class="w-8 h-8 rounded-xl bg-slate-50 hover:bg-white flex items-center justify-center text-slate-700 transition-colors border border-white/60">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
        <form id="form-edit-section" method="POST" class="p-6 space-y-4 overflow-y-auto">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Nombre / Título</label>
                <input type="text" name="name" id="es-name" required class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50 focus:bg-white">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Tipo</label>
                <select name="type" id="es-type" x-model="type" required class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50 focus:bg-white">
                    @foreach($sectionTypes as $st)
                        <option value="{{ $st->slug }}">{{ $st->icon }} {{ $st->label }}</option>
                    @endforeach
                </select>
            </div>
            <div x-show="type !== 'texto'">
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Descripción</label>
                <textarea name="description" id="es-desc" rows="2" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50 focus:bg-white resize-none"></textarea>
            </div>

            <div x-show="type === 'texto'" x-cloak>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Descripciones</label>
                <p class="text-xs text-slate-500 mb-3">Edita los párrafos. Se mostrarán en orden al evaluado.</p>
                <div class="space-y-2">
                    <template x-for="(item, idx) in paragraphs" :key="idx">
                        <div class="flex items-start gap-2">
                            <div class="w-6 h-6 rounded-lg bg-violet-100 text-violet-600 flex items-center justify-center text-xs font-bold flex-shrink-0 mt-2" x-text="idx + 1"></div>
                            <textarea :name="`content[${idx}]`" x-model="paragraphs[idx]" rows="2" placeholder="Escribe el párrafo…"
                                      class="flex-1 px-3 py-2 border border-slate-200 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-violet-500 focus:border-transparent bg-slate-50 focus:bg-white resize-none"></textarea>
                            <button type="button" @click="paragraphs.splice(idx, 1); if(paragraphs.length===0) paragraphs.push('')" class="w-8 h-8 rounded-lg hover:bg-rose-50 flex items-center justify-center text-slate-400 hover:text-rose-600 transition-colors flex-shrink-0 mt-1.5" title="Eliminar párrafo">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </template>
                </div>
                <button type="button" @click="paragraphs.push('')" class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-violet-700 hover:bg-violet-50 rounded-lg border border-violet-200 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Agregar otro párrafo
                </button>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal-edit-section').classList.add('hidden')" class="px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">Cancelar</button>
                <button type="submit" class="btn-bounce px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-xl transition-all">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Edit criteria --}}
<div id="modal-edit-criteria" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="bg-gradient-to-r from-blue-500 to-indigo-500 px-6 py-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-white">Editar criterio</h3>
                <button onclick="document.getElementById('modal-edit-criteria').classList.add('hidden')" class="w-8 h-8 rounded-xl bg-slate-50 hover:bg-white flex items-center justify-center text-slate-700 transition-colors border border-white/60">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
        <form id="form-edit-criteria" method="POST" class="p-6 space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Nombre</label>
                <input type="text" name="name" id="ec-name" required class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50 focus:bg-white">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Descripción</label>
                <textarea name="description" id="ec-desc" rows="2" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50 focus:bg-white resize-none"></textarea>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Puntaje máximo</label>
                <input type="number" name="max_score" id="ec-score" min="1" max="10" class="w-24 px-4 py-3 border border-slate-200 rounded-xl text-sm text-center font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50 focus:bg-white">
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal-edit-criteria').classList.add('hidden')" class="px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">Cancelar</button>
                <button type="submit" class="btn-bounce px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-xl transition-all">Guardar</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Add range --}}
<div id="modal-add-range" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="bg-gradient-to-r from-purple-500 to-violet-500 px-6 py-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nuevo rango
                </h3>
                <button onclick="document.getElementById('modal-add-range').classList.add('hidden')" class="w-8 h-8 rounded-xl bg-slate-50 hover:bg-white flex items-center justify-center text-slate-700 transition-colors border border-white/60">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.templates.ranges.store', $template) }}" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Etiqueta</label>
                <input type="text" name="label" required placeholder="Ej: Sobrepasa las expectativas" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50 focus:bg-white">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Puntaje mín.</label>
                    <input type="number" name="min_score" required min="0" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50 focus:bg-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Puntaje máx.</label>
                    <input type="number" name="max_score" required min="0" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50 focus:bg-white">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Color del indicador</label>
                <div class="flex items-center gap-3">
                    <input type="color" name="color" value="#22C55E" class="w-12 h-12 rounded-xl border border-slate-200 cursor-pointer p-1">
                    <span class="text-xs text-slate-500">Elige un color para identificar este rango</span>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal-add-range').classList.add('hidden')" class="px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">Cancelar</button>
                <button type="submit" class="btn-bounce px-5 py-2.5 bg-purple-600 hover:bg-purple-500 text-white text-sm font-semibold rounded-xl transition-all">Crear rango</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Edit range --}}
<div id="modal-edit-range" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="bg-gradient-to-r from-purple-500 to-violet-500 px-6 py-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-white">Editar rango</h3>
                <button onclick="document.getElementById('modal-edit-range').classList.add('hidden')" class="w-8 h-8 rounded-xl bg-slate-50 hover:bg-white flex items-center justify-center text-slate-700 transition-colors border border-white/60">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
        <form id="form-edit-range" method="POST" class="p-6 space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Etiqueta</label>
                <input type="text" name="label" id="er-label" required class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50 focus:bg-white">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Mín.</label>
                    <input type="number" name="min_score" id="er-min" required class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50 focus:bg-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Máx.</label>
                    <input type="number" name="max_score" id="er-max" required class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50 focus:bg-white">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Color</label>
                <input type="color" name="color" id="er-color" class="w-12 h-12 rounded-xl border border-slate-200 cursor-pointer p-1">
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal-edit-range').classList.add('hidden')" class="px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">Cancelar</button>
                <button type="submit" class="btn-bounce px-5 py-2.5 bg-purple-600 hover:bg-purple-500 text-white text-sm font-semibold rounded-xl transition-all">Guardar</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('keydown', e => { if(e.key==='Escape') document.querySelectorAll('[id^="modal-"]').forEach(m=>m.classList.add('hidden')); });

function openEditSection(id, name, type, desc, weight, content) {
    document.getElementById('form-edit-section').action = '/admin/sections/'+id;
    document.getElementById('es-name').value = name;
    document.getElementById('es-desc').value = desc || '';
    // Update Alpine state via the modal's x-data root
    const modal = document.getElementById('modal-edit-section');
    const root = modal.querySelector('[x-data]');
    if (root && root._x_dataStack) {
        const state = root._x_dataStack[0];
        state.type = type;
        const arr = Array.isArray(content) ? content.slice() : [];
        state.paragraphs = arr.length ? arr : [''];
    }
    document.getElementById('es-type').value = type;
    modal.classList.remove('hidden');
}

function sectionForm() {
    return {
        type: 'competencias_org',
        paragraphs: [''],
    };
}
function editSectionForm() {
    return {
        type: 'competencias_org',
        paragraphs: [''],
    };
}
function openEditCriteria(id, name, desc, score) {
    document.getElementById('form-edit-criteria').action = '/admin/criteria/'+id;
    document.getElementById('ec-name').value = name;
    document.getElementById('ec-desc').value = desc;
    document.getElementById('ec-score').value = score;
    document.getElementById('modal-edit-criteria').classList.remove('hidden');
}
function openEditRange(id, label, min, max, color) {
    document.getElementById('form-edit-range').action = '/admin/ranges/'+id;
    document.getElementById('er-label').value = label;
    document.getElementById('er-min').value = min;
    document.getElementById('er-max').value = max;
    document.getElementById('er-color').value = color;
    document.getElementById('modal-edit-range').classList.remove('hidden');
}

function templateEditor() {
    return { tab: 'sections' };
}
</script>
@endpush
