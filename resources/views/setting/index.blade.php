@extends('layouts.app')

@section('title', 'Web Settings')
@section('page_title', 'Pengaturan Global Aplikasi')

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-3xl border border-coffee-latte p-8 coffee-card space-y-6">
    <div class="flex items-center gap-3 border-b border-coffee-latte pb-4">
        <div class="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center text-coffee-medium border border-amber-100">
            <svg class="w-6 h-6 text-coffee-light" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.54 0 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
        </div>
        <div>
            <h3 class="font-extrabold text-coffee-dark">Pengaturan Global Aplikasi</h3>
            <p class="text-xs text-coffee-light font-medium mt-0.5">Konfigurasi info toko, tarif pajak struk belanja, dan footer cetakan.</p>
        </div>
    </div>

    <form action="{{ route('setting.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf
        
        <div class="flex items-center gap-4 bg-amber-50/20 p-4 rounded-2xl border border-coffee-latte/50">
            @if(!empty($settings['logo']))
                <div class="w-16 h-16 rounded-xl border border-coffee-latte bg-white flex items-center justify-center p-1 shadow-sm">
                    <img src="{{ asset($settings['logo']) }}" alt="Logo Restoran" class="max-w-full max-h-full object-contain">
                </div>
            @endif
            <div class="flex-grow">
                <label for="logo" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Logo Restoran</label>
                <input 
                    type="file" 
                    name="logo" 
                    id="logo" 
                    accept="image/*" 
                    class="w-full px-4 py-2 border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white rounded-xl"
                >
            </div>
        </div>

        <div>
            <label for="nama_restoran" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Nama Restoran / Toko</label>
            <input 
                type="text" 
                name="nama_restoran" 
                id="nama_restoran" 
                value="{{ $settings['nama_restoran'] }}" 
                required 
                class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white"
            >
        </div>

        <div>
            <label for="pajak" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Persentase Pajak</label>
            <div class="flex w-32 rounded-xl border border-coffee-latte bg-white focus-within:ring-2 focus-within:ring-coffee-light/50 overflow-hidden">
                <input 
                    type="number" 
                    name="pajak" 
                    id="pajak" 
                    value="{{ $settings['pajak'] }}" 
                    required 
                    min="0" 
                    max="100"
                    class="w-full px-4 py-2.5 text-xs font-bold text-coffee-dark focus:outline-none bg-white"
                >
                <span class="px-4 py-2.5 text-xs font-bold text-coffee-light bg-coffee-cream border-l border-coffee-latte select-none flex items-center">%</span>
            </div>
        </div>

        <div>
            <label for="bahasa" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Bahasa (Language Locale)</label>
            <select name="bahasa" id="bahasa" required class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white">
                <option value="id" {{ $settings['bahasa'] === 'id' ? 'selected' : '' }}>Bahasa Indonesia (ID)</option>
                <option value="en" {{ $settings['bahasa'] === 'en' ? 'selected' : '' }}>English (EN)</option>
                <option value="ms" {{ $settings['bahasa'] === 'ms' ? 'selected' : '' }}>Bahasa Melayu (MS)</option>
                <option value="ja" {{ $settings['bahasa'] === 'ja' ? 'selected' : '' }}>日本語 (JA)</option>
                <option value="zh" {{ $settings['bahasa'] === 'zh' ? 'selected' : '' }}>简体中文 (ZH)</option>
            </select>
        </div>

        <div>
            <label for="footer" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Footer Struk</label>
            <input 
                type="text" 
                name="footer" 
                id="footer" 
                value="{{ $settings['footer'] }}" 
                required 
                class="w-full px-4 py-2.5 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50 bg-white"
            >
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-coffee-latte">
            <button type="submit" class="px-5 py-2.5 bg-coffee-dark text-white rounded-xl text-xs font-bold hover:bg-coffee-medium transition shadow cursor-pointer">
                Simpan Konfigurasi
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const logoInput = document.getElementById('logo');
        if (logoInput) {
            initImageCropper(logoInput, 'cropped_image', 'logo-preview', 1);
            
            // Show preview if hidden
            logoInput.addEventListener('change', function() {
                document.getElementById('logo-preview').classList.remove('hidden');
            });
        }
    });
</script>
@endsection
