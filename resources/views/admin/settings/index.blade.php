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
                       class="sr-only peer"
                       onchange="this.form.submit()">
                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer
                            peer-checked:after:translate-x-full peer-checked:after:border-white after:content-['']
                            after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300
                            after:border after:rounded-full after:h-5 after:w-5 after:transition-all
                            peer-checked:bg-blue-600">
                </div>
            </label>
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
