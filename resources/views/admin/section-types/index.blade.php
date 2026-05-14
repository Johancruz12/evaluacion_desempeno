@extends('layouts.app')
@section('title', 'Tipos de sección')

@section('content')
<div class="space-y-6 max-w-5xl">

    <div class="flex items-center gap-4">
        <a href="{{ url()->previous() }}" class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-200 hover:bg-blue-100 flex items-center justify-center text-blue-500 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div class="flex-1">
            <h1 class="text-xl font-extrabold text-slate-800 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-violet-500 to-purple-500 flex items-center justify-center shadow-lg shadow-violet-500/25">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                </div>
                Tipos de sección
            </h1>
            <p class="text-slate-500 text-xs mt-0.5 ml-12">Cataloga los tipos de sección disponibles al armar plantillas de evaluación</p>
        </div>
        <button type="button" onclick="document.getElementById('modal-new-type').classList.remove('hidden')"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-xl shadow-lg shadow-blue-500/20 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nuevo tipo
        </button>
    </div>

    @if(session('success'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $errors->first() }}</div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr class="text-left text-xs font-bold text-slate-600 uppercase">
                    <th class="px-4 py-3">Icono</th>
                    <th class="px-4 py-3">Etiqueta</th>
                    <th class="px-4 py-3">Slug</th>
                    <th class="px-4 py-3">Comportamiento</th>
                    <th class="px-4 py-3">Vista previa</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($types as $t)
                <tr class="hover:bg-slate-50/50">
                    <td class="px-4 py-3 text-2xl">{{ $t->icon }}</td>
                    <td class="px-4 py-3 font-semibold text-slate-800">
                        {{ $t->label }}
                        @if($t->is_system)
                        <span class="ml-2 text-[10px] font-bold text-amber-700 bg-amber-100 border border-amber-200 rounded px-1.5 py-0.5">SISTEMA</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $t->slug }}</td>
                    <td class="px-4 py-3">
                        @if($t->behavior === 'rango')
                            <span class="text-xs font-semibold text-slate-700">Tabla de rangos</span>
                        @elseif($t->behavior === 'texto')
                            <span class="text-xs font-semibold text-violet-700">Solo texto</span>
                        @else
                            <span class="text-xs font-semibold text-blue-700">Calificable</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold {{ $t->badge_class }} border {{ $t->border_class }}">
                            {{ $t->icon }} {{ $t->label }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        @if($t->is_active)
                        <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-700">● Activo</span>
                        @else
                        <span class="inline-flex items-center gap-1 text-xs font-bold text-slate-400">○ Inactivo</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1">
                            <button type="button"
                                onclick='openEditType(@json($t))'
                                class="w-8 h-8 rounded-lg hover:bg-blue-50 flex items-center justify-center text-slate-400 hover:text-blue-600 transition-all" title="Editar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </button>
                            @unless($t->is_system)
                            <form method="POST" action="{{ route('admin.section-types.destroy', $t) }}" onsubmit="return confirm('¿Eliminar el tipo «{{ $t->label }}»?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-lg hover:bg-rose-50 flex items-center justify-center text-slate-400 hover:text-rose-600 transition-all" title="Eliminar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                            @endunless
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Modal: New type --}}
<div id="modal-new-type" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden max-h-[90vh] flex flex-col">
        <div class="bg-gradient-to-r from-blue-500 to-sky-500 px-6 py-4 flex items-center justify-between">
            <h3 class="text-lg font-bold text-white">Nuevo tipo de sección</h3>
            <button onclick="document.getElementById('modal-new-type').classList.add('hidden')" class="w-8 h-8 rounded-xl bg-white/20 hover:bg-white/30 flex items-center justify-center text-white">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.section-types.store') }}" class="p-6 space-y-4 overflow-y-auto">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Etiqueta (nombre visible)</label>
                <input type="text" name="label" required placeholder="Ej: Competencias técnicas" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Icono (emoji)</label>
                    <input type="text" name="icon" value="📋" maxlength="4" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-center text-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Orden</label>
                    <input type="number" name="order" min="0" placeholder="Auto" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Color</label>
                <select name="_preset" onchange="applyPreset(this.form, this.value)" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500">
                    <option value="blue">Azul</option>
                    <option value="sky">Celeste</option>
                    <option value="emerald">Verde</option>
                    <option value="amber">Ámbar</option>
                    <option value="rose">Rosa</option>
                    <option value="violet">Violeta</option>
                    <option value="slate">Gris</option>
                </select>
                <input type="hidden" name="gradient" value="from-blue-500 to-sky-500">
                <input type="hidden" name="badge_class" value="bg-blue-100 text-blue-700">
                <input type="hidden" name="border_class" value="border-blue-200">
                <input type="hidden" name="bg_class" value="bg-blue-50">
            </div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded text-blue-600">
                <span class="text-sm text-slate-700">Activo</span>
            </label>
            <p class="text-xs text-slate-500 bg-blue-50 border border-blue-200 rounded-lg p-3">
                💡 Los nuevos tipos se comportan como <strong>calificables</strong>: las secciones que los usen tendrán criterios con notas (1-5).
            </p>
            <div class="flex justify-end gap-3 pt-2 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-new-type').classList.add('hidden')" class="px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl">Cancelar</button>
                <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-xl">Crear</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Edit type --}}
<div id="modal-edit-type" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden max-h-[90vh] flex flex-col">
        <div class="bg-gradient-to-r from-blue-500 to-indigo-500 px-6 py-4 flex items-center justify-between">
            <h3 class="text-lg font-bold text-white">Editar tipo</h3>
            <button onclick="document.getElementById('modal-edit-type').classList.add('hidden')" class="w-8 h-8 rounded-xl bg-white/20 hover:bg-white/30 flex items-center justify-center text-white">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="form-edit-type" method="POST" class="p-6 space-y-4 overflow-y-auto">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Etiqueta</label>
                <input type="text" name="label" id="et-label" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Icono</label>
                    <input type="text" name="icon" id="et-icon" maxlength="4" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-center text-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Orden</label>
                    <input type="number" name="order" id="et-order" min="0" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Color</label>
                <select id="et-preset" onchange="applyPreset(this.form, this.value)" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500">
                    <option value="blue">Azul</option>
                    <option value="sky">Celeste</option>
                    <option value="emerald">Verde</option>
                    <option value="amber">Ámbar</option>
                    <option value="rose">Rosa</option>
                    <option value="violet">Violeta</option>
                    <option value="slate">Gris</option>
                </select>
                <input type="hidden" name="gradient" id="et-gradient">
                <input type="hidden" name="badge_class" id="et-badge">
                <input type="hidden" name="border_class" id="et-border">
                <input type="hidden" name="bg_class" id="et-bg">
            </div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="et-active" value="1" class="w-4 h-4 rounded text-blue-600">
                <span class="text-sm text-slate-700">Activo</span>
            </label>
            <div class="flex justify-end gap-3 pt-2 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modal-edit-type').classList.add('hidden')" class="px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl">Cancelar</button>
                <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-xl">Guardar</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const PRESETS = {
    blue:    { gradient: 'from-blue-500 to-sky-500',     badge: 'bg-blue-100 text-blue-700',       border: 'border-blue-200',    bg: 'bg-blue-50' },
    sky:     { gradient: 'from-sky-500 to-cyan-500',     badge: 'bg-sky-100 text-sky-700',         border: 'border-sky-200',     bg: 'bg-sky-50' },
    emerald: { gradient: 'from-emerald-500 to-green-500',badge: 'bg-emerald-100 text-emerald-700', border: 'border-emerald-200', bg: 'bg-emerald-50' },
    amber:   { gradient: 'from-amber-500 to-orange-500', badge: 'bg-amber-100 text-amber-700',     border: 'border-amber-200',   bg: 'bg-amber-50' },
    rose:    { gradient: 'from-rose-500 to-pink-500',    badge: 'bg-rose-100 text-rose-700',       border: 'border-rose-200',    bg: 'bg-rose-50' },
    violet:  { gradient: 'from-violet-500 to-purple-500',badge: 'bg-violet-100 text-violet-700',   border: 'border-violet-200',  bg: 'bg-violet-50' },
    slate:   { gradient: 'from-slate-500 to-gray-500',   badge: 'bg-slate-100 text-slate-700',     border: 'border-slate-200',   bg: 'bg-slate-50' },
};

function applyPreset(form, key) {
    const p = PRESETS[key] || PRESETS.blue;
    form.querySelector('[name="gradient"]').value = p.gradient;
    form.querySelector('[name="badge_class"]').value = p.badge;
    form.querySelector('[name="border_class"]').value = p.border;
    form.querySelector('[name="bg_class"]').value = p.bg;
}

function detectPreset(t) {
    for (const [key, p] of Object.entries(PRESETS)) {
        if (p.badge === t.badge_class) return key;
    }
    return 'blue';
}

function openEditType(t) {
    document.getElementById('form-edit-type').action = '/admin/section-types/' + t.id;
    document.getElementById('et-label').value = t.label;
    document.getElementById('et-icon').value  = t.icon || '📋';
    document.getElementById('et-order').value = t.order;
    document.getElementById('et-active').checked = !!t.is_active;
    const preset = detectPreset(t);
    document.getElementById('et-preset').value = preset;
    applyPreset(document.getElementById('form-edit-type'), preset);
    document.getElementById('modal-edit-type').classList.remove('hidden');
}

document.addEventListener('keydown', e => { if(e.key==='Escape') document.querySelectorAll('[id^="modal-"]').forEach(m=>m.classList.add('hidden')); });
</script>
@endpush
