@extends('layouts.app')

@section('title', 'Antrean Pesanan')
@section('page_title', 'Antrean Pesanan')

@section('styles')
    <!-- Auto refresh every 30 seconds -->
    <meta http-equiv="refresh" content="30">
@endsection

@section('content')
@php
    $activeOrdersJson = [];
    foreach($kitchenItems as $mejaId => $details) {
        $meja = $details->first()->mejaTemp;
        $mejaNum = $meja ? $meja->nomor_meja : '?';
        $mejaName = $mejaNum == 99 ? 'Takeaway' : 'Meja ' . $mejaNum;
        $nameSpeech = $mejaNum == 99 ? 'Pesanan Bawa Pulang' : 'Meja ' . $mejaNum;

        $itemsList = [];
        foreach($details as $item) {
            $text = $item->jumlah . ' ' . $item->menu->nama_menu;
            if ($item->catatan) {
                $text .= ', catatan: ' . $item->catatan;
            }
            $itemsList[] = [
                'id_detail' => $item->id_detail,
                'text' => $text,
                'status' => $item->status
            ];
        }
        $activeOrdersJson[] = [
            'meja_id' => $mejaId,
            'meja_name' => $mejaName,
            'name_speech' => $nameSpeech,
            'items' => $itemsList
        ];
    }
@endphp
<div class="max-w-[1400px] mx-auto space-y-6" x-data="chefManager()">

    <!-- Tab Navigation -->
    <div class="flex border-b border-coffee-latte gap-4 mb-6">
        <button 
            @click="activeTab = 'antrean'" 
            :class="activeTab === 'antrean' ? 'border-b-2 border-coffee-dark text-coffee-dark' : 'text-coffee-light hover:text-coffee-medium'"
            class="pb-2 px-4 font-bold text-sm transition cursor-pointer"
        >
            Antrean Pesanan
        </button>
        <button 
            @click="activeTab = 'stok'" 
            :class="activeTab === 'stok' ? 'border-b-2 border-coffee-dark text-coffee-dark' : 'text-coffee-light hover:text-coffee-medium'"
            class="pb-2 px-4 font-bold text-sm transition cursor-pointer"
        >
            Atur Stok Bahan
        </button>
    </div>

    <!-- Antrean Tab -->
    <div x-show="activeTab === 'antrean'" class="space-y-6">
        <!-- Auto-refresh Indicator -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-amber-50 border border-amber-100 p-4 rounded-xl text-xs font-semibold">
            <div class="flex items-center gap-2">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Memantau pesanan masuk secara otomatis.</span>
            </div>
            <div class="flex items-center gap-2.5 w-full sm:w-auto">
                <button 
                    type="button" 
                    onclick="toggleSpeaker()" 
                    class="w-full sm:w-auto flex items-center justify-center gap-2 px-3.5 py-1.5 rounded-lg border font-bold transition shadow-sm cursor-pointer text-xs"
                    id="speaker-toggle-btn"
                >
                    <svg id="speaker-icon" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"></svg>
                    <span id="speaker-text">Suara</span>
                </button>

                <a href="{{ route('pesanan') }}" class="w-full sm:w-auto text-center px-3.5 py-1.5 bg-coffee-dark text-white rounded-lg hover:bg-coffee-medium transition">
                    Segarkan
                </a>
            </div>
        </div>

        <!-- Active Orders Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($kitchenItems as $mejaId => $details)
                @php $meja = $details->first()->mejaTemp; @endphp
                <div class="bg-white rounded-2xl border border-coffee-latte shadow-md overflow-hidden coffee-card">
                    <div class="bg-coffee-dark text-white p-4 flex justify-between items-center">
                        <h3 class="font-bold text-sm">{{ $meja && $meja->nomor_meja == 99 ? 'Takeaway' : 'Meja ' . ($meja ? $meja->nomor_meja : '?') }}</h3>
                        <span class="text-[10px] bg-coffee-medium text-coffee-gold px-2 py-0.5 rounded font-extrabold uppercase">
                            {{ $details->sum('jumlah') }} Item
                        </span>
                    </div>
                    <div class="divide-y divide-coffee-latte p-4">
                        @foreach($details as $item)
                            <div class="py-3.5 first:pt-0 last:pb-0 flex items-start justify-between gap-4">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-sm text-coffee-dark">{{ $item->menu->nama_menu }}</span>
                                        <span class="text-xs font-extrabold text-coffee-light">x{{ $item->jumlah }}</span>
                                    </div>
                                    @if($item->catatan)
                                        <p class="text-[11px] bg-red-50 border border-red-100 text-red-700 px-2 py-1 rounded-lg inline-block font-semibold">
                                            Catatan: {{ $item->catatan }}
                                        </p>
                                    @endif
                                </div>
                                <div>
                                    @if($item->status === 'menunggu')
                                        <form action="{{ route('pesanan.updateStatus', $item->id_detail) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="status" value="dimasak">
                                            <button type="submit" class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-bold transition shadow cursor-pointer">Masak</button>
                                        </form>
                                    @elseif($item->status === 'dimasak')
                                        <form action="{{ route('pesanan.updateStatus', $item->id_detail) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="status" value="selesai">
                                            <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow cursor-pointer">Selesai</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-20 bg-white rounded-3xl border border-coffee-latte coffee-card">
                    <h3 class="font-bold text-coffee-dark">Dapur Bersih!</h3>
                    <p class="text-xs text-coffee-light font-medium mt-1">Tidak ada pesanan aktif saat ini.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Stok Tab -->
    <div x-show="activeTab === 'stok'" class="space-y-6" style="display: none;">
        <div class="bg-white rounded-2xl border border-coffee-latte p-6 coffee-card">
            <div class="flex flex-col md:flex-row gap-4 justify-between items-center mb-6">
                <h3 class="font-extrabold text-coffee-dark">Manajemen Stok Bahan</h3>
                <div class="flex gap-3 w-full md:w-auto">
                    <input 
                        type="text" 
                        x-model="searchQuery" 
                        placeholder="Cari bahan..." 
                        class="w-full md:w-64 px-4 py-2 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50"
                    >
                    <select 
                        x-model="selectedCategory"
                        class="px-4 py-2 rounded-xl border border-coffee-latte text-xs font-bold text-coffee-dark focus:outline-none focus:ring-2 focus:ring-coffee-light/50"
                    >
                        <option value="all">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <template x-for="item in filteredBahan()" :key="item.id_item">
                    <div class="p-4 border border-coffee-latte rounded-2xl bg-white hover:border-coffee-light transition space-y-3">
                        <div class="flex justify-between items-start gap-2">
                            <div>
                                <h4 class="font-extrabold text-coffee-dark text-xs truncate max-w-[150px]" x-text="item.nama_item"></h4>
                                <p class="text-[10px] text-coffee-light font-bold" x-text="'Kategori: ' + item.kategori"></p>
                            </div>
                            <span 
                                :class="item.stok > 0 ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-rose-50 text-rose-700 border-rose-100'"
                                class="text-[10px] px-2 py-0.5 rounded border font-bold uppercase" 
                                x-text="item.stok > 0 ? 'Tersedia' : 'Habis'"
                            ></span>
                        </div>
                        
                        <div class="flex items-center justify-between bg-coffee-cream/50 p-2 rounded-xl border border-coffee-latte">
                            <button 
                                @click="updateStok(item.id_item, 'minus')"
                                class="w-8 h-8 flex items-center justify-center bg-white border border-coffee-latte rounded-lg text-coffee-dark hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 transition font-black cursor-pointer"
                            >
                                -
                            </button>
                            <div class="text-center flex flex-col items-center justify-center px-1">
                                <span class="block text-[9px] text-coffee-light font-bold uppercase" x-text="item.satuan"></span>
                                <input 
                                    type="number" 
                                    :value="item.stok" 
                                    @change="setStok(item.id_item, $event.target.value)"
                                    class="w-16 text-center text-sm font-black text-coffee-dark bg-transparent border-b border-dashed border-coffee-light/40 focus:border-coffee-dark focus:outline-none p-0.5"
                                    min="0"
                                >
                            </div>
                            <button 
                                @click="updateStok(item.id_item, 'plus')"
                                class="w-8 h-8 flex items-center justify-center bg-white border border-coffee-latte rounded-lg text-coffee-dark hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-200 transition font-black cursor-pointer"
                            >
                                +
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
    <script>
        function chefManager() {
            return {
                activeTab: 'antrean',
                searchQuery: '',
                selectedCategory: 'all',
                bahan: @json($bahan),
                
                updateStok(itemId, action) {
                    fetch(`/bahan-alat/update-stok/${itemId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ action: action })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            const index = this.bahan.findIndex(b => b.id_item === itemId);
                            if (index !== -1) {
                                this.bahan[index].stok = data.new_stok;
                            }
                        }
                    });
                },

                setStok(itemId, newStok) {
                    const val = parseFloat(newStok);
                    if (isNaN(val) || val < 0) return;

                    fetch(`/bahan-alat/update-stok/${itemId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ stok: val })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            const index = this.bahan.findIndex(b => b.id_item === itemId);
                            if (index !== -1) {
                                this.bahan[index].stok = data.new_stok;
                            }
                        }
                    });
                },
                
                filteredBahan() {
                    return this.bahan.filter(item => {
                        const matchesSearch = item.nama_item.toLowerCase().includes(this.searchQuery.toLowerCase());
                        const matchesCategory = this.selectedCategory === 'all' || item.kategori === this.selectedCategory;
                        return matchesSearch && matchesCategory;
                    });
                }
            };
        }

        // Warm up speech synthesis voices list immediately
        if ('speechSynthesis' in window) {
            window.speechSynthesis.getVoices();
        }

        const activeOrders = @json($activeOrdersJson);
        const SPEAKER_STORAGE_KEY = 'chef_speaker_enabled';
        const SPOKEN_ITEMS_KEY = 'chef_spoken_items';

        // Check if speaker is enabled (defaults to false for safety/as requested, but persists on refresh)
        let speakerEnabled = localStorage.getItem(SPEAKER_STORAGE_KEY) === 'true';

        // Update speaker button UI
        function updateSpeakerUI() {
            const btn = document.getElementById('speaker-toggle-btn');
            const icon = document.getElementById('speaker-icon');
            const text = document.getElementById('speaker-text');
            if (!btn || !icon || !text) return;

            if (speakerEnabled) {
                btn.className = "w-full sm:w-auto flex items-center justify-center gap-2 px-3.5 py-1.5 rounded-lg border font-bold transition shadow-sm cursor-pointer text-xs bg-emerald-50 border-emerald-200 text-emerald-800 hover:bg-emerald-100/70";
                icon.className = "w-4 h-4 text-emerald-600";
                icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" d="M19.114 5.636a9 9 0 0 1 0 12.728M16.463 8.288a5.25 5.25 0 0 1 0 7.424M6.75 8.25l4.72-4.72a.75.75 0 0 1 1.28.53v15.88a.75.75 0 0 1-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.009 9.009 0 0 1 2.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75Z"/>`;
                text.textContent = "Suara Aktif (ON)";
            } else {
                btn.className = "w-full sm:w-auto flex items-center justify-center gap-2 px-3.5 py-1.5 rounded-lg border font-bold transition shadow-sm cursor-pointer text-xs bg-rose-50 border-rose-200 text-rose-800 hover:bg-rose-100/70";
                icon.className = "w-4 h-4 text-rose-600";
                icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" d="M17.25 9.75 19.5 12m0 0 2.25 2.25M19.5 12l2.25-2.25M19.5 12l-2.25 2.25m-10.5-6 4.72-4.72a.75.75 0 0 1 1.28.53v15.88a.75.75 0 0 1-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.009 9.009 0 0 1 2.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75Z"/>`;
                text.textContent = "Suara Mati (OFF)";
            }
        }

        // Toggle speaker
        function toggleSpeaker() {
            speakerEnabled = !speakerEnabled;
            localStorage.setItem(SPEAKER_STORAGE_KEY, speakerEnabled);
            updateSpeakerUI();
            if (speakerEnabled) {
                // Speak confirmation first so user knows it works
                speakIndonesian("Suara pemantau aktif");
                // Speak active new orders immediately when toggled ON (only new ones)
                setTimeout(() => {
                    speakNewOrders(false);
                }, 1500);
            } else {
                if ('speechSynthesis' in window) {
                    window.speechSynthesis.cancel();
                }
            }
        }

        // Helper to find Indonesian voice robustly
        function getIndonesianVoice() {
            if (!('speechSynthesis' in window)) return null;
            const voices = window.speechSynthesis.getVoices();
            
            // Priority list for Indonesian voices
            const searchTerms = ['id-id', 'id_id', 'indonesia', 'bahasa', 'id', 'ms-my', 'ms_my', 'malay'];
            
            for (const term of searchTerms) {
                const voice = voices.find(v => {
                    const lang = v.lang.toLowerCase();
                    const name = v.name.toLowerCase();
                    return lang.includes(term) || name.includes(term);
                });
                if (voice) return voice;
            }
            return null;
        }

        // Speak function
        function speakIndonesian(text) {
            if (!speakerEnabled) return;
            
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
                const utterance = new SpeechSynthesisUtterance(text);
                
                const idVoice = getIndonesianVoice();
                if (idVoice) {
                    utterance.voice = idVoice;
                    utterance.lang = idVoice.lang;
                } else {
                    utterance.lang = 'id-ID';
                }
                
                utterance.rate = 1.0;
                utterance.pitch = 1.0;
                window.speechSynthesis.speak(utterance);
            }
        }

        // Speak new orders
        function speakNewOrders(forceAll = false) {
            if (!speakerEnabled) return;

            let spokenItemIds = [];
            try {
                spokenItemIds = JSON.parse(localStorage.getItem(SPOKEN_ITEMS_KEY)) || [];
            } catch(e) {
                spokenItemIds = [];
            }

            const currentItemIds = [];
            let textToSpeak = '';

            activeOrders.forEach(order => {
                // Find items in 'menunggu' status which haven't been spoken yet
                const newItems = order.items.filter(item => {
                    currentItemIds.push(item.id_detail);
                    return item.status === 'menunggu' && (forceAll || !spokenItemIds.includes(item.id_detail));
                });

                if (newItems.length > 0) {
                    textToSpeak += `Pesanan baru masuk untuk ${order.name_speech}. `;
                    newItems.forEach(item => {
                        textToSpeak += `${item.text}. `;
                    });
                }
            });

            if (textToSpeak) {
                speakIndonesian(textToSpeak);
            }

            // Save active items to local storage so they aren't spoken again on refresh
            localStorage.setItem(SPOKEN_ITEMS_KEY, JSON.stringify(currentItemIds));
        }

        document.addEventListener('DOMContentLoaded', () => {
            updateSpeakerUI();
            
            // Speak new orders on page load
            if ('speechSynthesis' in window) {
                // Try to speak immediately if the Indonesian voice is already loaded
                if (getIndonesianVoice()) {
                    speakNewOrders();
                } else {
                    // Otherwise, wait for voiceschanged event
                    window.speechSynthesis.onvoiceschanged = function() {
                        speakNewOrders();
                        window.speechSynthesis.onvoiceschanged = null; // Unbind to run only once
                    };
                    
                    // Fallback: if voiceschanged doesn't fire or no voice is found after 1.5 seconds, speak anyway
                    setTimeout(() => {
                        if (window.speechSynthesis.onvoiceschanged) {
                            speakNewOrders();
                            window.speechSynthesis.onvoiceschanged = null;
                        }
                    }, 1500);
                }
            }
        });
    </script>
@endsection
