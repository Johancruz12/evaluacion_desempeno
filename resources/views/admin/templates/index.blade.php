@extends('layouts.app')
@section('title', 'Plantillas de Evaluación')

@section('content')
<div x-data="templatesPage()" class="space-y-6 max-w-6xl">

    {{-- Header --}}
    <div class="anim-slide-left flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-blue-500 to-sky-500 flex items-center justify-center shadow-lg shadow-blue-500/25">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6z"/></svg>
                </div>
                Plantillas
            </h1>
            <p class="text-slate-500 text-sm mt-1 ml-[52px]">Diseña y configura plantillas de evaluación de desempeño</p>
        </div>
        <button @click="openModal = true"
                class="btn-bounce inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white font-semibold px-5 py-3 rounded-2xl transition-all text-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nueva plantilla
        </button>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 anim-fade-up">
        <div class="stat-card bg-white rounded-2xl border border-slate-200 p-4 text-center">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-100 to-sky-100 flex items-center justify-center mx-auto mb-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5z"/></svg>
            </div>
            <p class="text-2xl font-extrabold text-slate-800 count-anim">{{ $templates->count() }}</p>
            <p class="text-xs text-slate-500 mt-0.5">Total</p>
        </div>
        <div class="stat-card bg-white rounded-2xl border border-slate-200 p-4 text-center">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-100 to-green-100 flex items-center justify-center mx-auto mb-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <p class="text-2xl font-extrabold text-emerald-600 count-anim">{{ $templates->where('is_active', true)->count() }}</p>
            <p class="text-xs text-slate-500 mt-0.5">Activas</p>
        </div>
        <div class="stat-card bg-white rounded-2xl border border-slate-200 p-4 text-center">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-100 to-indigo-100 flex items-center justify-center mx-auto mb-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            </div>
            <p class="text-2xl font-extrabold text-blue-600 count-anim">{{ $templates->sum('sections_count') }}</p>
            <p class="text-xs text-slate-500 mt-0.5">Secciones</p>
        </div>
        <div class="stat-card bg-white rounded-2xl border border-slate-200 p-4 text-center">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-100 to-amber-100 flex items-center justify-center mx-auto mb-2">
                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/></svg>
            </div>
            <p class="text-2xl font-extrabold text-orange-600 count-anim">{{ $templates->sum('evaluations_count') }}</p>
            <p class="text-xs text-slate-500 mt-0.5">Evaluaciones</p>
        </div>
    </div>

    {{-- Search --}}
    <div class="anim-fade-up relative">
        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input type="text" x-model="search" placeholder="Buscar plantilla por nombre o área..."
               class="w-full pl-12 pr-10 py-3 bg-white border border-slate-200 rounded-2xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all shadow-sm">
        <button x-show="search.length > 0" @click="search = ''" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    {{-- Templates grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        @forelse($templates as $t)
        <div class="anim-fade-up" x-show="matchesSearch('{{ addslashes($t->name) }}', '{{ addslashes($t->areas->pluck("name")->join(", ")) }}')" x-transition>
            <div class="group stat-card bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden hover:shadow-xl transition-all duration-300">
                <div class="h-1.5 bg-gradient-to-r {{ $t->is_active ? 'from-blue-400 via-sky-400 to-indigo-400' : 'from-slate-300 to-slate-400' }}"></div>
                <div class="p-5">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1 min-w-0 pr-3">
                            <div class="flex items-center gap-2 mb-1">
                                <div class="w-8 h-8 rounded-xl bg-gradient-to-br {{ $t->is_active ? 'from-blue-500 to-sky-500' : 'from-slate-400 to-slate-500' }} flex items-center justify-center text-white flex-shrink-0 shadow-md">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <h3 class="font-bold text-slate-800 text-sm leading-tight truncate">{{ $t->name }}</h3>
                            </div>
                            @if($t->areas->count())
                            <div class="flex flex-wrap gap-1 mt-2">
                                @foreach($t->areas->take(3) as $area)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-100">{{ $area->name }}</span>
                                @endforeach
                                @if($t->areas->count() > 3)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-500">+{{ $t->areas->count() - 3 }}</span>
                                @endif
                            </div>
                            @elseif($t->positionType)
                            <p class="text-xs text-sky-600 mt-1.5 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>
                                {{ $t->positionType->area?->name }} &rarr; {{ $t->positionType->name }}
                            </p>
                            @else
                            <p class="text-xs text-slate-400 mt-1.5 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064"/></svg>
                                Plantilla global
                            </p>
                            @endif
                        </div>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold flex-shrink-0
                            {{ $t->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500 border border-slate-200' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $t->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                            {{ $t->is_active ? 'Activa' : 'Inactiva' }}
                        </span>
                    </div>
                    <div class="flex gap-3 mb-4">
                        <div class="flex-1 bg-slate-50 rounded-xl p-3 text-center border border-slate-100">
                            <p class="text-lg font-extrabold text-slate-800">{{ $t->sections_count }}</p>
                            <p class="text-[10px] text-slate-500 font-medium uppercase tracking-wider">Secciones</p>
                        </div>
                        <div class="flex-1 bg-slate-50 rounded-xl p-3 text-center border border-slate-100">
                            <p class="text-lg font-extrabold text-slate-800">{{ $t->evaluations_count }}</p>
                            <p class="text-[10px] text-slate-500 font-medium uppercase tracking-wider">Evaluaciones</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.templates.edit', $t) }}"
                           class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-xl transition-all border border-blue-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
                            Configurar
                        </a>
                        <form method="POST" action="{{ route('admin.templates.destroy', $t) }}" onsubmit="return confirm('⚠️ ¿Eliminar la plantilla «{{ $t->name }}»?\n\nSe perderán todas sus secciones, criterios y rangos configurados. Esta acción no se puede deshacer.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-10 h-10 rounded-xl bg-rose-100 hover:bg-rose-600 text-rose-600 hover:text-white flex items-center justify-center transition-all duration-200 border border-rose-200 hover:border-rose-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full">
            <div class="bg-white rounded-2xl border-2 border-dashed border-slate-300 py-20 text-center">
                <div class="w-16 h-16 bg-gradient-to-br from-slate-100 to-slate-200 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-inner">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5z"/></svg>
                </div>
                <p class="text-slate-700 font-bold text-lg">Sin plantillas</p>
                <p class="text-slate-400 text-sm mt-1 mb-4">Crea tu primera plantilla de evaluación</p>
                <button @click="openModal = true" class="btn-bounce inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white font-semibold px-5 py-2.5 rounded-xl text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Crear primera plantilla
                </button>
            </div>
        </div>
        @endforelse
    </div>

    {{-- Modal: Nueva plantilla --}}
    <div x-show="openModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm" @keydown.escape.window="openModal = false">
        <div @click.outside="openModal = false"
             x-show="openModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-90 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90"
             class="bg-white rounded-3xl shadow-2xl w-full max-w-4xl max-h-[92vh] overflow-hidden flex flex-col">
            <div class="bg-gradient-to-r from-blue-500 to-sky-500 px-6 py-5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-sm">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white">Nueva plantilla</h3>
                            <p class="text-blue-100 text-xs">Configura los detalles iniciales</p>
                        </div>
                    </div>
                    <button @click="openModal = false" class="w-8 h-8 rounded-xl bg-slate-50 hover:bg-white flex items-center justify-center text-slate-700 transition-colors border border-white/60">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.templates.store') }}" id="create-template-form" class="flex-1 overflow-y-auto p-6 space-y-5"  style="scrollbar-width: thin;">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Nombre de la plantilla</label>
                    <input type="text" name="name" required placeholder="Ej: Evaluación Trimestral de Desempeño"
                           class="w-full px-4 py-3.5 border-2 border-slate-200 rounded-xl text-sm text-slate-800 font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all bg-white placeholder:text-slate-400">
                </div>
                <div x-data="areaSelector()" class="relative">
                    {{-- Hidden inputs para el formulario --}}
                    <template x-for="area in selectedAreas" :key="area.id">
                        <input type="hidden" name="area_ids[]" :value="area.id">
                    </template>
                    
                    <div class="flex items-center justify-between mb-3">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Áreas asignadas</label>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="selectAll()" class="text-xs text-blue-600 hover:text-blue-700 font-semibold px-3 py-1.5 rounded-lg hover:bg-blue-50 transition-all border border-transparent hover:border-blue-200">
                                <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Todas
                            </button>
                            <button type="button" @click="clearAll()" class="text-xs text-slate-600 hover:text-slate-800 font-semibold px-3 py-1.5 rounded-lg hover:bg-slate-100 transition-all border border-transparent hover:border-slate-200">
                                <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                Ninguna
                            </button>
                        </div>
                    </div>
                    
                    {{-- Input principal del multiselect --}}
                    <div @click="open = !open" class="relative cursor-pointer">
                        <div class="min-h-[3rem] w-full px-4 py-2.5 border-2 border-slate-200 rounded-xl bg-white hover:border-blue-400 hover:shadow-md transition-all flex flex-wrap items-center gap-2 shadow-sm">
                            <template x-if="selectedAreas.length === 0">
                                <span class="text-sm text-slate-400 py-1 font-medium">Selecciona las áreas...</span>
                            </template>
                            <template x-for="area in selectedAreas" :key="area.id">
                                <span x-transition:enter="transition ease-out duration-200"
                                      x-transition:enter-start="opacity-0 scale-75"
                                      x-transition:enter-end="opacity-100 scale-100"
                                      class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gradient-to-r from-blue-100 to-blue-50 text-blue-700 rounded-lg text-xs font-bold border border-blue-200 shadow-sm hover:shadow-md transition-all">
                                    <span x-text="area.name"></span>
                                    <button type="button" @click.stop="toggleArea(area)" class="hover:bg-blue-200 rounded-full p-0.5 transition-all hover:scale-110">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </span>
                            </template>
                            <div class="flex-1 min-w-[60px]"></div>
                            <svg class="w-5 h-5 text-slate-400 flex-shrink-0 transition-transform" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>

                    {{-- Dropdown con búsqueda --}}
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         @click.outside="open = false"
                         class="absolute z-50 w-full mt-2 bg-white border-2 border-slate-200 rounded-xl shadow-2xl overflow-hidden">
                        
                        {{-- Búsqueda --}}
                        <div class="p-4 border-b-2 border-slate-100 bg-slate-50">
                            <div class="relative">
                                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <input type="text"
                                       x-model="searchQuery"
                                       @click.stop
                                       placeholder="Buscar área por nombre..."
                                       class="w-full pl-11 pr-4 py-2.5 border-2 border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all bg-white">
                                <button type="button" x-show="searchQuery" @click.stop="searchQuery = ''" class="absolute right-3 top-1/2 -translate-y-1/2 w-6 h-6 rounded-full bg-slate-200 hover:bg-slate-300 flex items-center justify-center text-slate-600 transition-all">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>

                        {{-- Lista de áreas en grid 2 columnas --}}
                        <div class="max-h-96 overflow-y-auto p-3" style="scrollbar-width: thin;">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <template x-for="area in filteredAreas" :key="area.id">
                                    <label class="flex items-center gap-3 px-3 py-2.5 rounded-lg cursor-pointer transition-all group border-2"
                                           :class="isSelected(area) ? 'bg-blue-50 border-blue-300 shadow-sm' : 'border-transparent hover:bg-slate-50 hover:border-slate-200'">
                                        <input type="checkbox"
                                               :value="area.id"
                                               :checked="isSelected(area)"
                                               @change="toggleArea(area)"
                                               class="w-4.5 h-4.5 rounded text-blue-600 border-2 border-slate-300 focus:ring-2 focus:ring-blue-500 cursor-pointer transition-all flex-shrink-0">
                                        <span class="text-sm font-medium text-slate-700 group-hover:text-blue-700 transition-colors flex-1 truncate" :title="area.name" x-text="area.name"></span>
                                        <svg x-show="isSelected(area)" class="w-4 h-4 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </label>
                                </template>
                            </div>
                            <template x-if="filteredAreas.length === 0">
                                <div class="px-4 py-12 text-center text-slate-400">
                                    <svg class="w-14 h-14 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    <p class="text-sm font-semibold text-slate-500">No se encontraron áreas</p>
                                    <p class="text-xs text-slate-400 mt-1">Intenta con otro término de búsqueda</p>
                                </div>
                            </template>
                        </div>

                        {{-- Footer con contador --}}
                        <div class="px-4 py-3 bg-slate-50 border-t-2 border-slate-100 flex items-center justify-between">
                            <span class="text-xs font-semibold text-slate-600">
                                <span x-text="selectedAreas.length"></span> de <span x-text="areas.length"></span> seleccionadas
                            </span>
                            <button type="button" @click="open = false" class="text-xs font-semibold text-blue-600 hover:text-blue-700 px-3 py-1.5 hover:bg-blue-50 rounded-lg transition-all">
                                Cerrar
                            </button>
                        </div>
                    </div>

                    <p class="text-xs text-slate-500 mt-2 flex items-center gap-1.5 bg-blue-50 px-3 py-2 rounded-lg border border-blue-100">
                        <svg class="w-3.5 h-3.5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-blue-700 font-medium">Si no seleccionas ningún área, la plantilla será global para todos</span>
                    </p>
                </div>
            </form>
            <div class="flex justify-end gap-3 px-6 py-4 border-t-2 border-slate-100 bg-slate-50">
                <button type="button" @click="openModal = false" class="px-6 py-2.5 text-sm font-semibold text-slate-600 hover:bg-white hover:text-slate-800 rounded-xl transition-all border-2 border-slate-200 hover:border-slate-300">
                    Cancelar
                </button>
                <button type="submit" form="create-template-form" class="btn-bounce inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm font-bold rounded-xl transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Crear y configurar
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Scrollbar personalizado para el dropdown */
    .max-h-64::-webkit-scrollbar {
        width: 6px;
    }
    
    .max-h-64::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 10px;
    }
    
    .max-h-64::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
    
    .max-h-64::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    
    /* Animación suave para el icono de dropdown */
    .rotate-180 {
        transform: rotate(180deg);
    }
    
    /* Prevenir selección de texto al hacer doble click */
    [x-data="areaSelector()"] .min-h-\[3rem\] {
        user-select: none;
        -webkit-user-select: none;
        -moz-user-select: none;
    }
</style>
@endpush

@push('scripts')
<script>
function templatesPage() {
    return {
        openModal: false,
        search: '',
        matchesSearch(name, areas) {
            if (!this.search) return true;
            const q = this.search.toLowerCase();
            return name.toLowerCase().includes(q) || areas.toLowerCase().includes(q);
        }
    };
}

function areaSelector() {
    return {
        open: false,
        searchQuery: '',
        selectedAreas: [],
        areas: @json($areas->map(fn($a) => ['id' => $a->id, 'name' => $a->name])->values()),
        
        get filteredAreas() {
            if (!this.searchQuery) return this.areas;
            const query = this.searchQuery.toLowerCase();
            return this.areas.filter(area => area.name.toLowerCase().includes(query));
        },
        
        isSelected(area) {
            return this.selectedAreas.some(a => a.id === area.id);
        },
        
        toggleArea(area) {
            const index = this.selectedAreas.findIndex(a => a.id === area.id);
            if (index > -1) {
                this.selectedAreas.splice(index, 1);
            } else {
                this.selectedAreas.push(area);
            }
        },
        
        selectAll() {
            this.selectedAreas = [...this.areas];
        },
        
        clearAll() {
            this.selectedAreas = [];
        }
    };
}
</script>
@endpush
@endsection
