@extends('layouts.app')
@section('title', 'Configuración del Sistema')

@section('content')
<div class="space-y-5 max-w-2xl">

    <div class="anim-slide-left">
        <h1 class="text-xl font-bold text-slate-800">Configuración del Sistema</h1>
        <p class="text-slate-500 text-sm mt-0.5">Parámetros globales de la aplicación</p>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl px-5 py-3 text-sm font-medium">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}"
          x-data="{
              pdfEnabled: {{ $pdfEnabled ? 'true' : 'false' }},
              periodType: '{{ $defaultPeriodType }}',
              audience: '{{ $defaultAudience }}'
          }"
          class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
        @csrf

        {{-- PDF descarga --}}
        <div class="flex items-start justify-between gap-6">
            <div>
                <h3 class="text-sm font-semibold text-slate-800">Descarga de PDF de evaluaciones</h3>
                <p class="text-xs text-slate-500 mt-1">
                    Habilita o deshabilita el botón de descarga de PDF en las evaluaciones para todos los usuarios.
                    Cuando está desactivado, nadie podrá descargar el informe en PDF.
                </p>
            </div>
            {{-- Toggle controlado por Alpine --}}
            <input type="hidden" name="pdf_enabled" :value="pdfEnabled ? '1' : '0'">
            <button type="button" @click="pdfEnabled = !pdfEnabled"
                    class="relative flex-shrink-0 mt-0.5 w-11 h-6 rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    :class="pdfEnabled ? 'bg-blue-600' : 'bg-slate-200'">
                <span class="block w-5 h-5 bg-white rounded-full shadow transform transition-transform duration-200"
                      :class="pdfEnabled ? 'translate-x-5' : 'translate-x-0.5'"></span>
            </button>
        </div>

        <div class="border-t border-slate-100"></div>

        {{-- Tipo de período predeterminado --}}
        <input type="hidden" name="default_period_type" :value="periodType">
        <div class="space-y-2">
            <div>
                <h3 class="text-sm font-semibold text-slate-800">Tipo de período predeterminado</h3>
                <p class="text-xs text-slate-500 mt-1">Se seleccionará automáticamente en el formulario de asignación de evaluaciones.</p>
            </div>
            <div class="grid grid-cols-3 gap-3 mt-3">
                @foreach(['trimestral' => ['Trimestral','T1·T2·T3·T4','from-blue-500 to-sky-500'], 'semestral' => ['Semestral','S1·S2','from-violet-500 to-purple-500'], 'anual' => ['Anual','Un período por año','from-slate-500 to-slate-600']] as $val => [$label, $hint, $grad])
                <button type="button" @click="periodType = '{{ $val }}'"
                        class="w-full text-left rounded-xl border-2 p-4 transition-all"
                        :class="periodType === '{{ $val }}'
                            ? 'border-blue-500 ring-2 ring-blue-500 ring-offset-2 bg-blue-50'
                            : 'border-slate-200 hover:border-blue-300 bg-white'">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br {{ $grad }} flex items-center justify-center text-white mb-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <p class="text-sm font-bold text-slate-800">{{ $label }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $hint }}</p>
                </button>
                @endforeach
            </div>
        </div>

        <div class="border-t border-slate-100"></div>

        {{-- Audiencia predeterminada --}}
        <input type="hidden" name="default_target_audience" :value="audience">
        <div class="space-y-2">
            <div>
                <h3 class="text-sm font-semibold text-slate-800">Audiencia predeterminada</h3>
                <p class="text-xs text-slate-500 mt-1">A quién se asignarán las evaluaciones por defecto al crear una nueva.</p>
            </div>
            <div class="grid grid-cols-3 gap-3 mt-3">
                @foreach(['todos' => ['Todos','Empleados y jefes','from-violet-500 to-purple-500'], 'empleados' => ['Solo empleados','Sin jefes/coordinadores','from-blue-500 to-sky-500'], 'jefes' => ['Solo jefes','Jefes y coordinadores','from-indigo-500 to-blue-600']] as $val => [$label, $hint, $grad])
                <button type="button" @click="audience = '{{ $val }}'"
                        class="w-full text-left rounded-xl border-2 p-4 transition-all"
                        :class="audience === '{{ $val }}'
                            ? 'border-blue-500 ring-2 ring-blue-500 ring-offset-2 bg-blue-50'
                            : 'border-slate-200 hover:border-blue-300 bg-white'">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br {{ $grad }} flex items-center justify-center text-white mb-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <p class="text-sm font-bold text-slate-800">{{ $label }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $hint }}</p>
                </button>
                @endforeach
            </div>
        </div>

        <div class="pt-2 border-t border-slate-100 flex justify-end">
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white font-semibold px-4 py-2 rounded-xl transition-all shadow-md shadow-blue-500/20 text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Guardar cambios
            </button>
        </div>
    </form>

    {{-- ─── GESTIÓN DE PERÍODOS ─── --}}
    @if(session('info'))
    <div class="flex items-center gap-3 bg-blue-50 border border-blue-200 text-blue-700 rounded-2xl px-5 py-3 text-sm font-medium">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('info') }}
    </div>
    @endif

    @if($errors->has('period_delete'))
    <div class="flex items-center gap-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl px-5 py-3 text-sm font-medium">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ $errors->first('period_delete') }}
    </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-5">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-800">Gestión de períodos</h2>
                <p class="text-xs text-slate-500 mt-0.5">Los períodos activos aparecen en el formulario de asignación de evaluaciones. Solo años actuales o futuros se muestran al crear.</p>
            </div>
        </div>

        {{-- Auto-generar períodos --}}
        <form method="POST" action="{{ route('admin.periods.generate') }}"
              x-data="{ genYear: '{{ $currentYear }}', genType: 'trimestral' }"
              class="flex flex-wrap items-end gap-3 bg-slate-50 rounded-xl p-4 border border-slate-200">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Año</label>
                <input type="number" name="year" x-model="genYear" min="2020" max="2100"
                       class="w-24 px-3 py-2 border border-slate-200 rounded-lg text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Tipo</label>
                <select name="type" x-model="genType"
                        class="px-3 py-2 border border-slate-200 rounded-lg text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="trimestral">Trimestral (T1–T4)</option>
                    <option value="semestral">Semestral (S1–S2)</option>
                    <option value="anual">Anual</option>
                </select>
            </div>
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-4 py-2 rounded-xl text-sm transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Generar períodos
            </button>
            <p class="text-xs text-slate-400 w-full -mt-1">Genera automáticamente todos los períodos del año seleccionado. Los que ya existen no se duplican.</p>
        </form>

        {{-- Lista de períodos agrupados por año --}}
        @if($periodsGrouped->isEmpty())
        <p class="text-sm text-slate-400 text-center py-4">No hay períodos definidos aún. Usa el generador de arriba.</p>
        @else
        <div class="space-y-4">
            @foreach($periodsGrouped as $year => $yearPeriods)
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ $year }}</span>
                    <div class="flex-1 h-px bg-slate-100"></div>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach($yearPeriods as $period)
                    <div class="flex items-center gap-1.5 rounded-xl border px-3 py-1.5 text-sm font-semibold transition-all
                                {{ $period->is_active ? 'border-blue-200 bg-blue-50 text-blue-800' : 'border-slate-200 bg-slate-100 text-slate-400 line-through' }}">
                        <span>{{ $period->label }}</span>
                        <span class="text-xs font-normal opacity-60">
                            {{ ['trimestral'=>'Trim.','semestral'=>'Sem.','anual'=>'Anual'][$period->type] ?? $period->type }}
                        </span>
                        {{-- Toggle active --}}
                        <form method="POST" action="{{ route('admin.periods.toggle', $period) }}" class="inline">
                            @csrf @method('PATCH')
                            <button type="submit" title="{{ $period->is_active ? 'Desactivar' : 'Activar' }}"
                                    class="ml-1 text-slate-400 hover:text-amber-500 transition-colors">
                                @if($period->is_active)
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                @else
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                @endif
                            </button>
                        </form>
                        {{-- Delete --}}
                        <form method="POST" action="{{ route('admin.periods.destroy', $period) }}" class="inline"
                              onsubmit="return confirm('¿Eliminar el período {{ $period->label }}? Solo es posible si ninguna evaluación lo usa.')">
                            @csrf @method('DELETE')
                            <button type="submit" title="Eliminar" class="text-slate-300 hover:text-rose-500 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </form>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Agregar período manual --}}
        <details class="border border-slate-200 rounded-xl overflow-hidden">
            <summary class="px-4 py-3 text-sm font-semibold text-slate-600 cursor-pointer hover:bg-slate-50 select-none">
                + Agregar período manualmente
            </summary>
            <form method="POST" action="{{ route('admin.periods.store') }}"
                  x-data="{ manualYear: '{{ $currentYear }}', manualType: 'trimestral', manualLabel: '' }"
                  class="p-4 border-t border-slate-200 space-y-3">
                @csrf
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Año</label>
                        <input type="number" name="year" x-model="manualYear" min="2020" max="2100"
                               class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Tipo</label>
                        <select name="type" x-model="manualType"
                                class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="trimestral">Trimestral</option>
                            <option value="semestral">Semestral</option>
                            <option value="anual">Anual</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Etiqueta</label>
                        <input type="text" name="label" x-model="manualLabel" placeholder="ej: 2026-T3"
                               class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent uppercase"
                               style="text-transform:uppercase">
                    </div>
                </div>
                @error('label') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold px-4 py-2 rounded-xl text-sm transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Agregar período
                </button>
            </form>
        </details>
    </div>

</div>
@endsection
