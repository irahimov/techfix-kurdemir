{{-- resources/views/tickets/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Yeni Müraciət')
@section('page-title', 'Yeni Müraciət Yarat')

@section('content')
<div class="max-w-2xl mx-auto" x-data="ticketForm()">

    {{-- Back --}}
    <a href="{{ isset($is_customer_panel) && $is_customer_panel ? route('customer.panel') : route('tickets.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-300 transition-colors mb-5">
        ← Müraciətlərə qayıt
    </a>

    <div class="glass-strong rounded-3xl overflow-hidden">

        {{-- Header --}}
        <div class="px-8 py-6 border-b border-white/[0.06]">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl btn-primary flex items-center justify-center text-2xl">🎫</div>
                <div>
                    <h2 class="text-lg font-bold text-white">Yeni Texniki Müraciət</h2>
                    <p class="text-sm text-slate-500">Problemini biz üçün ətraflı izah et, tez həll edək.</p>
                </div>
            </div>
        </div>

        {{-- Form --}}
        <form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data" class="p-8 space-y-5">
            @csrf
            @if(isset($is_customer_panel) && $is_customer_panel)
                <input type="hidden" name="from_customer_panel" value="1">
            @endif

            {{-- Başlıq --}}
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-2 uppercase tracking-wider">
                    Başlıq <span class="text-red-400">*</span>
                </label>
                <input type="text" name="title" value="{{ old('title') }}"
                    placeholder="Məs: Dell XPS laptopum açılmır"
                    class="tf-input w-full rounded-xl px-4 py-3 text-sm @error('title') border-red-500/50 @enderror">
                @error('title')
                    <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Kateqoriya + Prioritet --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-2 uppercase tracking-wider">
                        Kateqoriya <span class="text-red-400">*</span>
                    </label>
                    <select name="category_id" class="w-full bg-slate-800/80 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-blue-500 @error('category_id') border-red-500/50 @enderror">
                        <option value="" class="bg-slate-800">Seç...</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" class="bg-slate-800" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->icon }} {{ $cat->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-2 uppercase tracking-wider">
                        Prioritet <span class="text-red-400">*</span>
                    </label>
                    <select name="priority" x-model="priority" class="w-full bg-slate-800/80 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-blue-500">
                        <option value="low" class="bg-slate-800">🔵 Aşağı (24 saat icra müddəti)</option>
                        <option value="medium" class="bg-slate-800" selected>🟡 Orta (12 saat icra müddəti)</option>
                        <option value="high" class="bg-slate-800">🟠 Yüksək (6 saat icra müddəti)</option>
                        <option value="urgent" class="bg-slate-800">🔴 Təcili (2 saat icra müddəti)</option>
                    </select>

                    {{-- SLA info --}}
                    <p class="mt-1.5 text-xs font-mono" :class="{
                        'text-gray-400': priority === 'low',
                        'text-blue-400': priority === 'medium',
                        'text-orange-400': priority === 'high',
                        'text-red-400': priority === 'urgent'
                    }">
                        <span x-text="slaInfo[priority]"></span>
                    </p>
                </div>
            </div>

            {{-- Cihaz məlumatları --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-2 uppercase tracking-wider">
                        Cihaz Modeli
                    </label>
                    <input type="text" name="device_model" value="{{ old('device_model') }}"
                        placeholder="Məs: Dell XPS 15 9530"
                        class="tf-input w-full rounded-xl px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-2 uppercase tracking-wider">
                        Serial Nömrəsi
                    </label>
                    <input type="text" name="device_serial" value="{{ old('device_serial') }}"
                        placeholder="Məs: SN1234567890"
                        class="tf-input w-full rounded-xl px-4 py-3 text-sm">
                </div>
            </div>

            {{-- Açıqlama --}}
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-2 uppercase tracking-wider">
                    Problem Açıqlaması <span class="text-red-400">*</span>
                </label>
                <textarea name="description" rows="5"
                    placeholder="Problemi ətraflı izah et: nə vaxt başladı, nə etdin, hansı xəta görürsən..."
                    x-model="description"
                    class="tf-input w-full rounded-xl px-4 py-3 text-sm resize-none @error('description') border-red-500/50 @enderror">{{ old('description') }}</textarea>
                <div class="flex items-center justify-between mt-1.5">
                    @error('description')
                        <p class="text-xs text-red-400">{{ $message }}</p>
                    @else
                        <span></span>
                    @enderror
                    <p class="text-xs text-slate-600 font-mono" :class="description.length < 20 ? 'text-red-400/60' : 'text-slate-600'">
                        <span x-text="description.length"></span>/5000
                    </p>
                </div>
            </div>

            {{-- Fayl Yükləmə --}}
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-2 uppercase tracking-wider">
                    Əlavə Fayllar (max 5, hər biri 5MB)
                </label>

                <div class="border-2 border-dashed border-white/10 rounded-2xl p-6 text-center hover:border-brand-500/30 transition-colors"
                     @dragover.prevent="dragover = true"
                     @dragleave="dragover = false"
                     @drop.prevent="handleDrop($event)"
                     :class="dragover ? 'border-brand-500/40 bg-brand-500/5' : ''">

                    <input type="file" name="attachments[]" multiple
                        accept=".jpg,.jpeg,.png,.pdf"
                        class="hidden" id="fileInput"
                        @change="handleFiles($event.target.files)">

                    <label for="fileInput" class="cursor-pointer">
                        <div class="text-3xl mb-2">📎</div>
                        <p class="text-sm text-slate-400">Faylları bura sürüklə və ya <span class="text-blue-400 hover:text-blue-300">seç</span></p>
                        <p class="text-xs text-slate-600 mt-1">JPG, PNG, PDF — maks. 5MB</p>
                    </label>

                    {{-- Seçilmiş fayllar --}}
                    <div x-show="files.length > 0" class="mt-4 space-y-2" x-transition>
                        <template x-for="(file, i) in files" :key="i">
                            <div class="flex items-center gap-3 px-3 py-2 rounded-xl bg-white/5 text-left">
                                <span class="text-lg" x-text="file.name.endsWith('.pdf') ? '📄' : '🖼️'"></span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs text-slate-300 truncate" x-text="file.name"></p>
                                    <p class="text-xs text-slate-600 font-mono" x-text="formatSize(file.size)"></p>
                                </div>
                                <button type="button" @click="removeFile(i)" class="text-slate-600 hover:text-red-400 transition-colors text-xs">✕</button>
                            </div>
                        </template>
                    </div>
                </div>

                @error('attachments.*')
                    <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Submit --}}
            <div class="flex items-center gap-4 pt-2">
                <button type="submit" class="flex-1 btn-primary text-white font-semibold py-3 rounded-2xl text-sm transition-all">
                    Müraciəti Göndər →
                </button>
                <a href="{{ isset($is_customer_panel) && $is_customer_panel ? route('customer.panel') : route('tickets.index') }}" class="px-6 py-3 rounded-2xl text-sm text-slate-400 hover:text-white hover:bg-white/5 transition-all">
                    İmtina et
                </a>
            </div>

        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function ticketForm() {
    return {
        priority: 'medium',
        description: '',
        files: [],
        dragover: false,
        slaInfo: {
            urgent: '🔴 İcra müddəti: 2 saat — Kritik problem üçün',
            high:   '🟠 İcra müddəti: 6 saat — Ciddi problemlər üçün',
            medium: '🟡 İcra müddəti: 12 saat — Normal problemlər üçün',
            low:    '🔵 İcra müddəti: 24 saat — Az təcili problemlər üçün',
        },
        handleFiles(fileList) {
            const allowed = ['image/jpeg', 'image/png', 'application/pdf'];
            Array.from(fileList).forEach(f => {
                if (allowed.includes(f.type) && f.size <= 5 * 1024 * 1024) {
                    if (this.files.length < 5) this.files.push(f);
                }
            });
            this.syncInput();
        },
        handleDrop(e) {
            this.dragover = false;
            this.handleFiles(e.dataTransfer.files);
        },
        removeFile(i) {
            this.files.splice(i, 1);
            this.syncInput();
        },
        syncInput() {
            const dt = new DataTransfer();
            this.files.forEach(f => dt.items.add(f));
            document.getElementById('fileInput').files = dt.files;
        },
        formatSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1048576) return (bytes/1024).toFixed(1) + ' KB';
            return (bytes/1048576).toFixed(1) + ' MB';
        }
    }
}
</script>
@endpush