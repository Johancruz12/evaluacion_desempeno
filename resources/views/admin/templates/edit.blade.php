@extends('layouts.app')
@section('title', 'Configurar Plantilla')

@section('content')
@php
$sectionTypes = [
    'competencias_org'   => ['Competencias Organizacionales', 'from-blue-500 to-sky-500', 'bg-blue-50 text-blue-700 border-blue-200'],
    'competencias_cargo' => ['Competencias del Cargo', 'from-sky-500 to-blue-500', 'bg-sky-50 text-sky-700 border-sky-200'],
    'responsabilidades'  => ['Responsabilidades', 'from-orange-500 to-amber-500', 'bg-orange-50 text-orange-700 border-orange-200'],
    'rango'              => ['Tabla de Rangos', 'from-slate-500 to-gray-500', 'bg-slate-50 text-slate-600 border-slate-200'],
];
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
        <div class="h-1 bg-gradient-to-r from-blue-400 via-sky-400 to-indigo-400"></div>
        <div class="p-6">
            <form method="POST" action="{{ route('admin.templates.update', $template) }}" class="space-y-5">
                @csrf @method('PUT')
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Nombre</label>
                        <input type="text" name="name" value="{{ old('name', $template->name) }}" required
                               class="w-full px-4 py-3 border border-slate-200 rounded-xl text-slate-800 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all bg-slate-50 focus:bg-white">
                    </div>
                    <div class="sm:w-64">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Cargo asociado</label>
                        <select name="position_type_id" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-slate-800 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50 focus:bg-white">
                            <option value="">Aplica para todos</option>
                            @foreach($positionTypes as $pt)
                            <option value="{{ $pt->id }}" {{ $template->position_type_id == $pt->id ? 'selected' : '' }}>{{ $pt->area?->name }} &rarr; {{ $pt->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Areas --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider">Áreas asignadas</label>
                        <div class="flex items-center gap-1">
                            <button type="button" onclick="document.querySelectorAll('.edit-area-cb').forEach(cb=>cb.checked=true)" class="text-[11px] text-blue-600 hover:text-blue-700 font-semibold px-2 py-1 rounded-lg hover:bg-blue-50 transition-colors">Todas</button>
                            <span class="text-slate-300 text-xs">|</span>
                            <button type="button" onclick="document.querySelectorAll('.edit-area-cb').forEach(cb=>cb.checked=false)" class="text-[11px] text-slate-500 hover:text-slate-700 font-semibold px-2 py-1 rounded-lg hover:bg-slate-50 transition-colors">Ninguna</button>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 p-3 border border-slate-200 rounded-xl bg-slate-50/50 max-h-36 overflow-y-auto">
                        @foreach($areas as $area)
                        <label class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border cursor-pointer transition-all text-sm hover:shadow-sm
                            {{ $template->areas->contains($area->id) ? 'bg-blue-50 border-blue-300 text-blue-700' : 'bg-white border-slate-200 text-slate-600 hover:border-blue-200' }}">
                            <input type="checkbox" name="area_ids[]" value="{{ $area->id }}" class="edit-area-cb w-3.5 h-3.5 rounded text-blue-600 focus:ring-blue-500"
                                {{ $template->areas->contains($area->id) ? 'checked' : '' }}>
                            <span>{{ $area->name }}</span>
                        </label>
                        @endforeach
                    </div>
                    <p class="text-xs text-slate-400 mt-1.5">Sin áreas seleccionadas = plantilla global</p>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <input type="hidden" name="is_active" value="0">
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
        @php [$typeLabel, $typeGradient, $typeClass] = $sectionTypes[$section->type] ?? ['Otro', 'from-slate-500 to-gray-500', 'bg-slate-50 text-slate-600 border-slate-200']; @endphp

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
                    <p class="text-xs text-slate-400 mt-0.5">{{ $section->criteria->count() }} criterio(s) · Puntaje máx: {{ $section->criteria->sum('max_score') }}</p>
                </div>
                <div class="flex items-center gap-1 flex-shrink-0" @click.stop>
                    <button type="button" onclick="openEditSection({{ $section->id }}, '{{ addslashes($section->name) }}', '{{ $section->type }}', '{{ addslashes($section->description ?? '') }}', {{ $section->weight ?? 0 }})"
                            class="w-8 h-8 rounded-xl hover:bg-blue-50 flex items-center justify-center text-slate-400 hover:text-blue-600 transition-all" title="Editar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    </button>
                    <form method="POST" action="{{ route('admin.sections.destroy', $section) }}" onsubmit="return confirm('⚠️ ¿Eliminar la sección «{{ $section->name }}»?\n\nSe eliminarán también todos sus criterios de evaluación. Esta acción no se puede deshacer.')">
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
                        <form method="POST" action="{{ route('admin.criteria.destroy', $c) }}" onsubmit="return confirm('⚠️ ¿Eliminar el criterio «{{ $c->name }}»?\n\nEsta acción no se puede deshacer.')">
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
                <form method="POST" action="{{ route('admin.ranges.destroy', $range) }}" onsubmit="return confirm('⚠️ ¿Eliminar el rango «{{ $range->label }}»?\n\nEsta acción no se puede deshacer.')">
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
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
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
        <form method="POST" action="{{ route('admin.templates.sections.store', $template) }}" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Nombre</label>
                <input type="text" name="name" required class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50 focus:bg-white">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Tipo</label>
                <select name="type" required class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50 focus:bg-white">
                    <option value="competencias_org">Competencias Organizacionales</option>
                    <option value="competencias_cargo">Competencias del Cargo</option>
                    <option value="responsabilidades">Responsabilidades</option>
                    <option value="rango">Tabla de Rangos</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Descripción <span class="text-slate-400 font-normal">(opcional)</span></label>
                <textarea name="description" rows="2" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50 focus:bg-white resize-none"></textarea>
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
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="bg-gradient-to-r from-blue-500 to-indigo-500 px-6 py-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-white">Editar sección</h3>
                <button onclick="document.getElementById('modal-edit-section').classList.add('hidden')" class="w-8 h-8 rounded-xl bg-slate-50 hover:bg-white flex items-center justify-center text-slate-700 transition-colors border border-white/60">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
        <form id="form-edit-section" method="POST" class="p-6 space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Nombre</label>
                <input type="text" name="name" id="es-name" required class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50 focus:bg-white">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Tipo</label>
                <select name="type" id="es-type" required class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50 focus:bg-white">
                    <option value="competencias_org">Competencias Organizacionales</option>
                    <option value="competencias_cargo">Competencias del Cargo</option>
                    <option value="responsabilidades">Responsabilidades</option>
                    <option value="rango">Tabla de Rangos</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Descripción</label>
                <textarea name="description" id="es-desc" rows="2" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50 focus:bg-white resize-none"></textarea>
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

function openEditSection(id, name, type, desc, weight) {
    document.getElementById('form-edit-section').action = '/admin/sections/'+id;
    document.getElementById('es-name').value = name;
    document.getElementById('es-type').value = type;
    document.getElementById('es-desc').value = desc;
    document.getElementById('modal-edit-section').classList.remove('hidden');
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
