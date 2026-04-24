@extends('layouts.app')
@section('title', 'Empleados')

@section('content')
<div x-data="employeesPage()" class="space-y-6 max-w-5xl">

    {{-- ── Header ── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 anim-slide-left">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Empleados</h1>
            <p class="text-slate-500 text-sm mt-0.5">Sincroniza todo desde un archivo Excel</p>
        </div>
        <button @click="showUpload = !showUpload"
                class="btn-bounce inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white font-semibold px-5 py-2.5 rounded-xl transition-all text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            Cargar Excel
        </button>
    </div>

    {{-- ── Flash messages (server redirect) ── --}}
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 8000)"
         x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="anim-fade-up bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-2xl text-sm flex items-start gap-3">
        <svg class="w-5 h-5 text-emerald-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="flex-1">{{ session('success') }}</p>
    </div>
    @endif

    {{-- ── AJAX result toast ── --}}
    <template x-if="resultMsg">
        <div x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-2"
             :class="resultOk ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800'"
             class="border p-4 rounded-2xl text-sm flex items-start gap-3">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" :class="resultOk ? 'text-emerald-500' : 'text-rose-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path x-show="resultOk" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                <path x-show="!resultOk" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="flex-1" x-text="resultMsg"></p>
            <button @click="resultMsg = ''" class="opacity-60 hover:opacity-100"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
    </template>

    @if($errors->any())
    <div class="anim-fade-up bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-2xl text-sm">
        @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
    </div>
    @endif

    {{-- ── Stats cards ── --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="stat-card anim-fade-up bg-white rounded-2xl border border-slate-200 shadow-sm p-4 text-center">
            <div class="text-2xl font-extrabold text-blue-600 count-anim">{{ $totalActive }}</div>
            <div class="text-xs font-medium text-slate-500 mt-1 uppercase tracking-wider">Activos</div>
        </div>
        <div class="stat-card anim-fade-up bg-white rounded-2xl border border-slate-200 shadow-sm p-4 text-center">
            <div class="text-2xl font-extrabold text-slate-400 count-anim">{{ $totalInactive }}</div>
            <div class="text-xs font-medium text-slate-500 mt-1 uppercase tracking-wider">Inactivos</div>
        </div>
        <div class="stat-card anim-fade-up bg-white rounded-2xl border border-slate-200 shadow-sm p-4 text-center">
            <div class="text-2xl font-extrabold text-blue-600 count-anim">{{ $areas->count() }}</div>
            <div class="text-xs font-medium text-slate-500 mt-1 uppercase tracking-wider">Áreas</div>
        </div>
        <div class="stat-card anim-fade-up bg-white rounded-2xl border border-slate-200 shadow-sm p-4 text-center">
            <div class="text-2xl font-extrabold text-amber-500 count-anim text-xs">{{ $lastImport ?? '—' }}</div>
            <div class="text-xs font-medium text-slate-500 mt-1 uppercase tracking-wider">Últ. importación</div>
        </div>
    </div>

    {{-- ── Upload panel ── --}}
    <div x-show="showUpload" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-3" x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

        <div class="px-6 py-5 border-b border-slate-100 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <h3 class="font-semibold text-slate-800">Cargar archivo de empleados</h3>
                <p class="text-xs text-slate-400">El archivo reemplaza la lista de empleados activos</p>
            </div>
        </div>

        <div class="px-6 py-5">
            {{-- Dropzone --}}
            <label for="excelFile"
                   class="group relative flex flex-col items-center justify-center gap-3 py-10 border-2 border-dashed rounded-2xl cursor-pointer transition-all duration-300"
                   :class="fileName ? 'border-blue-400 bg-blue-50/50' : 'border-slate-300 hover:border-blue-400 hover:bg-slate-50'">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center transition-colors"
                     :class="fileName ? 'bg-blue-100 text-blue-600' : 'bg-slate-100 text-slate-400 group-hover:bg-blue-50 group-hover:text-blue-500'">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                </div>
                <div class="text-center">
                    <p class="font-semibold text-sm" :class="fileName ? 'text-blue-700' : 'text-slate-600'" x-text="fileName || 'Arrastra o haz clic para seleccionar'"></p>
                    <p class="text-xs text-slate-400 mt-1">Formatos: .xlsx, .xls, .csv</p>
                </div>
                <input id="excelFile" type="file" accept=".xlsx,.xls,.csv" class="sr-only"
                       @change="fileName = $event.target.files[0]?.name || ''; selectedFile = $event.target.files[0] || null">
            </label>

            {{-- ════════════ PROGRESS BAR ════════════ --}}
            <div x-show="uploading" x-transition class="mt-5 space-y-3">
                <div class="relative w-full h-4 bg-slate-100 rounded-full overflow-hidden">
                    <div class="absolute inset-y-0 left-0 rounded-full transition-all duration-500 ease-out"
                         :class="progressPercent >= 100 ? 'bg-emerald-500' : 'bg-gradient-to-r from-blue-500 to-sky-500'"
                         :style="'width: ' + progressPercent + '%'">
                        <div class="absolute inset-0 bg-white/20 animate-pulse" x-show="progressPercent < 100"></div>
                    </div>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500 font-medium" x-text="progressPhase"></span>
                    <span class="font-bold" :class="progressPercent >= 100 ? 'text-emerald-600' : 'text-blue-600'" x-text="progressPercent + '%'"></span>
                </div>
            </div>

            {{-- Format guide --}}
            <div class="mt-4 bg-slate-50 rounded-xl p-4" x-show="!uploading">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Formato esperado del archivo</p>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="text-left text-slate-400">
                                <th class="pr-4 pb-1 font-medium">Doc</th>
                                <th class="pr-4 pb-1 font-medium">Documento</th>
                                <th class="pr-4 pb-1 font-medium">Apellido y Nombres</th>
                                <th class="pr-4 pb-1 font-medium">Cargo</th>
                                <th class="pr-4 pb-1 font-medium">Area</th>
                                <th class="pb-1 font-medium">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="text-slate-600">
                            <tr>
                                <td class="pr-4 py-0.5">CC</td>
                                <td class="pr-4 py-0.5">1001234567</td>
                                <td class="pr-4 py-0.5">Pérez López Juan Carlos</td>
                                <td class="pr-4 py-0.5">Médico General</td>
                                <td class="pr-4 py-0.5">Consulta Externa</td>
                                <td class="py-0.5">Activo</td>
                            </tr>
                            <tr class="text-slate-400">
                                <td class="pr-4 py-0.5">CC</td>
                                <td class="pr-4 py-0.5">1009876543</td>
                                <td class="pr-4 py-0.5">García Martínez Ana María</td>
                                <td class="pr-4 py-0.5">Jefe de Recursos Humanos</td>
                                <td class="pr-4 py-0.5">Recursos Humanos</td>
                                <td class="py-0.5">Activo</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-400">
                    <span>• <strong>Documento</strong> = Cédula (login y contraseña inicial)</span>
                    <span>• <strong>Área + Cargo</strong> se crean automáticamente</span>
                    <span>• Empleados NO incluidos se desactivan</span>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-between mt-5" x-show="!uploading">
                <button type="button" @click="showUpload = false; fileName = ''; selectedFile = null"
                        class="px-4 py-2 text-sm font-medium text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition-colors">
                    Cancelar
                </button>
                <button type="button" @click="startImport()"
                        :disabled="!fileName"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 text-white font-semibold text-sm rounded-xl transition-all disabled:opacity-40 disabled:cursor-not-allowed hover:bg-blue-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Sincronizar empleados
                </button>
            </div>
        </div>
    </div>

    {{-- ── Search & filter bar ── --}}
    <div class="anim-fade-up flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" x-model="search" placeholder="Buscar por nombre, cédula o cargo..."
                   class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 placeholder:text-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow">
        </div>
        <select x-model="filterArea"
                class="px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">Todas las áreas</option>
            @foreach($areas as $area)
            <option value="{{ $area->id }}">{{ $area->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- ── Employees grouped by area ── --}}
    @forelse($allUsersGrouped as $areaId => $users)
    @php
        $area = $users->first()->area;
        $areaName = $area ? $area->name : 'Sin Área';
    @endphp
    <div class="anim-fade-up area-block" data-area-id="{{ $areaId }}"
         x-show="filterArea === '' || filterArea === '{{ $areaId }}'"
         x-transition>

        {{-- Area header --}}
        <button @click="toggleArea({{ $areaId }})" type="button"
                class="w-full flex items-center gap-3 bg-white hover:bg-slate-50 rounded-2xl border border-slate-200 shadow-sm px-5 py-4 transition-all group">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                 style="background: {{ $area ? 'linear-gradient(135deg, #0d9488, #14b8a6)' : '#94a3b8' }};">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m14 0h2M5 21H3M9 7h1m-1 4h1m4-4h1m-1 4h1M9 21V11h6v10"/></svg>
            </div>
            <div class="flex-1 text-left">
                <p class="font-semibold text-slate-800 text-sm">{{ $areaName }}</p>
                <p class="text-xs text-slate-400">{{ $users->count() }} empleado(s)</p>
            </div>
            <svg class="w-5 h-5 text-slate-400 transition-transform duration-300"
                 :class="openAreas.includes({{ $areaId }}) ? 'rotate-180' : ''"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>

        {{-- Employee list --}}
        <div x-show="openAreas.includes({{ $areaId }})"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="mt-1 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden divide-y divide-slate-100">

            @foreach($users as $user)
            <div class="row-hover px-5 py-3 flex items-center gap-4 employee-row {{ $user->is_active ? '' : 'bg-slate-50/70 opacity-75' }}"
                 data-search="{{ Str::lower(($user->person?->first_name ?? '') . ' ' . ($user->person?->last_name ?? '') . ' ' . ($user->person?->document_number ?? '') . ' ' . ($user->positionType?->name ?? '')) }}">
                <div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0
                    {{ $user->roles->contains('slug', 'director_rh') ? 'bg-amber-100 text-amber-700 ring-2 ring-amber-300' : 'bg-slate-100 text-slate-500' }}">
                    {{ strtoupper(substr($user->person?->first_name ?? '?', 0, 1)) }}{{ strtoupper(substr($user->person?->last_name ?? '', 0, 1)) }}
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="font-semibold text-slate-700 text-sm truncate">{{ $user->person?->last_name }} {{ $user->person?->first_name }}</p>
                        @if($user->roles->contains('slug', 'director_rh'))
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-100 text-amber-700 ring-1 ring-amber-200">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            Super
                        </span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 mt-0.5 text-xs text-slate-400">
                        <span>{{ $user->person?->document_type ?? 'CC' }} {{ $user->person?->document_number }}</span>
                        @if($user->positionType)
                        <span>·</span>
                        <span class="truncate">{{ $user->positionType->name }}</span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full {{ $user->is_active ? 'bg-emerald-400' : 'bg-slate-300' }}"></span>
                        <span class="text-xs {{ $user->is_active ? 'text-emerald-600' : 'text-slate-400' }}">{{ $user->is_active ? 'Activo' : 'Inactivo' }}</span>
                    </div>

                    @if($user->id !== auth()->id())
                    <form method="POST" action="{{ route('admin.employees.toggle-active', $user) }}"
                          onsubmit="return confirm('{{ $user->is_active ? '¿Inactivar a ' . addslashes(($user->person?->first_name ?? '') . ' ' . ($user->person?->last_name ?? '')) . '? No podrá ingresar al sistema.' : '¿Activar a ' . addslashes(($user->person?->first_name ?? '') . ' ' . ($user->person?->last_name ?? '')) . '?' }}');">
                        @csrf
                        @method('PATCH')
                        @if($user->is_active)
                        <button type="submit" title="Inactivar usuario"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                            Inactivar
                        </button>
                        @else
                        <button type="submit" title="Activar usuario"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-50 hover:bg-emerald-100 text-emerald-600 border border-emerald-200 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Activar
                        </button>
                        @endif
                    </form>
                    @else
                    <span class="text-[10px] text-slate-400 italic px-2">(tu cuenta)</span>
                    @endif
                </div>
            </div>
            @endforeach

        </div>
    </div>
    @empty
    <div class="anim-fade-up text-center py-16">
        <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </div>
        <h3 class="text-slate-600 font-semibold">Sin empleados registrados</h3>
        <p class="text-sm text-slate-400 mt-1">Sube un archivo Excel para comenzar</p>
    </div>
    @endforelse

</div>

<script>
function employeesPage() {
    return {
        showUpload: false,
        fileName: '',
        selectedFile: null,
        search: '',
        filterArea: '',
        openAreas: [],
        // Import
        uploading: false,
        progressPercent: 0,
        progressPhase: '',
        progressTimer: null,
        // Result
        resultMsg: '',
        resultOk: false,

        toggleArea(id) {
            const idx = this.openAreas.indexOf(id);
            idx > -1 ? this.openAreas.splice(idx, 1) : this.openAreas.push(id);
        },

        // Animated progress that advances at a realistic pace
        startProgressAnimation() {
            const phases = [
                { upTo: 15, label: 'Leyendo archivo…',          speed: 400 },
                { upTo: 35, label: 'Preparando áreas y cargos…', speed: 300 },
                { upTo: 55, label: 'Sincronizando personas…',    speed: 250 },
                { upTo: 75, label: 'Sincronizando usuarios…',    speed: 350 },
                { upTo: 88, label: 'Asignando roles…',           speed: 300 },
                { upTo: 94, label: 'Desactivando ausentes…',     speed: 500 },
            ];
            let currentPhaseIdx = 0;

            this.progressTimer = setInterval(() => {
                if (currentPhaseIdx >= phases.length) {
                    // Stay at 94% waiting for server response
                    clearInterval(this.progressTimer);
                    return;
                }
                const phase = phases[currentPhaseIdx];
                this.progressPhase = phase.label;
                this.progressPercent += 1;

                if (this.progressPercent >= phase.upTo) {
                    currentPhaseIdx++;
                    if (currentPhaseIdx < phases.length) {
                        clearInterval(this.progressTimer);
                        this.progressTimer = setInterval(() => {
                            if (currentPhaseIdx >= phases.length) return;
                            const p = phases[currentPhaseIdx];
                            this.progressPhase = p.label;
                            this.progressPercent += 1;
                            if (this.progressPercent >= p.upTo) {
                                currentPhaseIdx++;
                            }
                        }, phases[currentPhaseIdx].speed);
                    }
                }
            }, phases[0].speed);
        },

        async startImport() {
            if (!this.selectedFile) return;

            this.uploading = true;
            this.progressPercent = 0;
            this.progressPhase = 'Subiendo archivo…';
            this.resultMsg = '';

            this.startProgressAnimation();

            const form = new FormData();
            form.append('employees_file', this.selectedFile);
            form.append('_token', document.querySelector('meta[name="csrf-token"]').content);

            try {
                const res = await fetch('{{ route("admin.employees.import") }}', {
                    method: 'POST',
                    body: form,
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                });

                clearInterval(this.progressTimer);
                const data = await res.json();

                if (res.ok && data.success) {
                    this.progressPercent = 100;
                    this.progressPhase = '¡Completado!';
                    const s = data.stats;
                    this.resultOk = true;
                    this.resultMsg = `Sincronización completada — Creados: ${s.created}, Actualizados: ${s.updated}, Áreas nuevas: ${s.areas_new}, Cargos nuevos: ${s.positions_new}, Omitidos: ${s.skipped}, Desactivados: ${s.deactivated}.`;
                    setTimeout(() => location.reload(), 2000);
                } else {
                    this.resultOk = false;
                    this.resultMsg = data.error || data.message || 'Error desconocido al importar.';
                    this.uploading = false;
                    this.progressPercent = 0;
                }
            } catch (err) {
                clearInterval(this.progressTimer);
                this.resultOk = false;
                this.resultMsg = 'Error de conexión: ' + err.message;
                this.uploading = false;
                this.progressPercent = 0;
            }
        },

        init() {
            this.$watch('search', (val) => {
                const q = val.toLowerCase().trim();
                document.querySelectorAll('.employee-row').forEach(row => {
                    row.style.display = (!q || (row.dataset.search || '').includes(q)) ? '' : 'none';
                });
            });
        }
    };
}
</script>
@endsection
