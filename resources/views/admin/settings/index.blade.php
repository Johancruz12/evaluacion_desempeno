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

    <form method="POST" action="{{ route('admin.settings.update') }}" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
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
            <label class="relative inline-flex items-center cursor-pointer flex-shrink-0 mt-0.5">
                <input type="checkbox" name="pdf_enabled" value="1" {{ $pdfEnabled ? 'checked' : '' }}
                       class="sr-only peer">
                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer
                            peer-checked:after:translate-x-full peer-checked:after:border-white after:content-['']
                            after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300
                            after:border after:rounded-full after:h-5 after:w-5 after:transition-all
                            peer-checked:bg-blue-600">
                </div>
            </label>
        </div>

        <div class="border-t border-slate-100"></div>

        {{-- Tipo de período predeterminado --}}
        <div class="space-y-2">
            <div>
                <h3 class="text-sm font-semibold text-slate-800">Tipo de período predeterminado</h3>
                <p class="text-xs text-slate-500 mt-1">Se seleccionará automáticamente en el formulario de asignación de evaluaciones.</p>
            </div>
            <div class="grid grid-cols-3 gap-3 mt-3">
                @foreach(['trimestral' => ['Trimestral','T1·T2·T3·T4','from-blue-500 to-sky-500'], 'semestral' => ['Semestral','S1·S2','from-violet-500 to-purple-500'], 'anual' => ['Anual','Un período por año','from-slate-500 to-slate-600']] as $val => [$label, $hint, $grad])
                <label class="cursor-pointer">
                    <input type="radio" name="default_period_type" value="{{ $val }}" {{ $defaultPeriodType === $val ? 'checked' : '' }} class="sr-only peer">
                    <div class="peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-blue-500 rounded-xl border-2 border-slate-200 peer-checked:border-transparent p-4 transition-all hover:border-blue-300">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br {{ $grad }} flex items-center justify-center text-white mb-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <p class="text-sm font-bold text-slate-800">{{ $label }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $hint }}</p>
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        <div class="border-t border-slate-100"></div>

        {{-- Audiencia predeterminada --}}
        <div class="space-y-2">
            <div>
                <h3 class="text-sm font-semibold text-slate-800">Audiencia predeterminada</h3>
                <p class="text-xs text-slate-500 mt-1">A quién se asignarán las evaluaciones por defecto al crear una nueva.</p>
            </div>
            <div class="grid grid-cols-3 gap-3 mt-3">
                @foreach(['todos' => ['Todos','Empleados y jefes','from-violet-500 to-purple-500'], 'empleados' => ['Solo empleados','Sin jefes/coordinadores','from-blue-500 to-sky-500'], 'jefes' => ['Solo jefes','Jefes y coordinadores','from-indigo-500 to-blue-600']] as $val => [$label, $hint, $grad])
                <label class="cursor-pointer">
                    <input type="radio" name="default_target_audience" value="{{ $val }}" {{ $defaultAudience === $val ? 'checked' : '' }} class="sr-only peer">
                    <div class="peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-blue-500 rounded-xl border-2 border-slate-200 peer-checked:border-transparent p-4 transition-all hover:border-blue-300">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br {{ $grad }} flex items-center justify-center text-white mb-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <p class="text-sm font-bold text-slate-800">{{ $label }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $hint }}</p>
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        {{-- Submit explícito (si JS está deshabilitado) --}}
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

</div>
@endsection
