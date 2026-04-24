@extends('layouts.app')
@section('title', 'Nuevo rol')

@section('content')
<div class="space-y-6 max-w-4xl">

    <div class="anim-slide-left">
        <a href="{{ route('admin.roles.index') }}" class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-blue-600 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Volver a roles
        </a>
        <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Crear nuevo rol</h1>
        <p class="text-slate-500 text-sm mt-0.5">Define el nombre del rol y selecciona los permisos que tendrá</p>
    </div>

    @if($errors->any())
    <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-2xl text-sm">
        @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('admin.roles.store') }}" x-data="{ selected: {{ json_encode(old('permissions', [])) }} }">
        @csrf

        {{-- Info del rol --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
            <h3 class="font-semibold text-slate-800 border-b border-slate-100 pb-3">Información del rol</h3>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nombre del rol <span class="text-rose-500">*</span></label>
                <input type="text" name="description" value="{{ old('description') }}" required maxlength="100"
                       placeholder="Ej. Supervisor de Área, Auditor, Coordinador"
                       class="w-full rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 px-4 py-2.5 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Identificador técnico (slug)</label>
                <input type="text" name="slug" value="{{ old('slug') }}" maxlength="80"
                       placeholder="Se genera automáticamente si lo dejas vacío"
                       class="w-full rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 px-4 py-2.5 text-sm font-mono">
                <p class="text-xs text-slate-400 mt-1">Solo letras, números y guiones bajos. Se usa internamente y no debería cambiarse después.</p>
            </div>
        </div>

        {{-- Permisos --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mt-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <div>
                    <h3 class="font-semibold text-slate-800">Permisos del rol</h3>
                    <p class="text-xs text-slate-400">Selecciona qué acciones podrá realizar un usuario con este rol</p>
                </div>
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
        </div>

        <div class="flex items-center justify-end gap-3 mt-6">
            <a href="{{ route('admin.roles.index') }}" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-100">Cancelar</a>
            <button type="submit" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white font-semibold px-5 py-2.5 rounded-xl transition-all text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Crear rol
            </button>
        </div>
    </form>
</div>
@endsection
