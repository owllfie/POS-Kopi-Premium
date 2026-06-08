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
<div class="space-y-6">

    <!-- Auto-refresh Indicator -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-amber-50 border border-amber-100 p-4 rounded-xl text-xs font-semibold">
        <div class="flex items-center gap-2">
            <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>Halaman auto-refresh setiap 30 detik untuk memantau pesanan masuk.</span>
        </div>
        <div class="flex items-center gap-2.5 w-full sm:w-auto">
            <!-- Speaker Toggle Button -->
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
                Segarkan Manual (Refresh)
            </a>
        </div>
    </div>

    <!-- Active Orders Grouped by Table -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($kitchenItems as $mejaId => $details)
            @php $meja = $details->first()->mejaTemp; @endphp
            <div class="bg-white rounded-2xl border border-coffee-latte shadow-md overflow-hidden coffee-card">
                <!-- Header of Table -->
                <div class="bg-coffee-dark text-white p-4 flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <h3 class="font-bold text-sm">{{ $meja && $meja->nomor_meja == 99 ? 'Takeaway' : 'Meja ' . ($meja ? $meja->nomor_meja : '?') }}</h3>

                    </div>
                    <span class="text-[10px] bg-coffee-medium text-coffee-gold px-2 py-0.5 rounded font-extrabold uppercase">
                        {{ $details->count() }} Item Masakan
                    </span>
                </div>
                
                <!-- Items list -->
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
                                <p class="text-[10px] text-coffee-light font-medium">Dipesan: {{ $item->created_at->diffForHumans() }}</p>
                            </div>

                            <!-- Actions based on status -->
                            <div>
                                @if($item->status === 'menunggu')
                                    <form action="{{ route('pesanan.updateStatus', $item->id_detail) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="status" value="dimasak">
                                        <button type="submit" class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-bold transition shadow cursor-pointer">
                                            Mulai Masak
                                        </button>
                                    </form>
                                @elseif($item->status === 'dimasak')
                                    <form action="{{ route('pesanan.updateStatus', $item->id_detail) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="status" value="selesai">
                                        <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow cursor-pointer">
                                            Selesai
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="col-span-2 text-center py-20 bg-white rounded-3xl border border-coffee-latte coffee-card">
                <div class="w-16 h-16 bg-amber-50 rounded-full flex items-center justify-center text-coffee-medium mx-auto mb-4 border border-amber-100">
                    <svg class="w-8 h-8 text-coffee-light" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012-2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </div>
                <h3 class="font-bold text-coffee-dark">Dapur Bersih!</h3>
                <p class="text-xs text-coffee-light font-medium mt-1">Tidak ada hidangan yang menunggu atau sedang dimasak.</p>
            </div>
        @endforelse
    </div>

</div>
@endsection

@section('scripts')
    <script>
        const activeOrders = @json($activeOrdersJson);
        const SPEAKER_STORAGE_KEY = 'chef_speaker_enabled';
        const SPOKEN_ITEMS_KEY = 'chef_spoken_items';

        // Check if speaker is enabled (defaults to true for premium feel)
        let speakerEnabled = localStorage.getItem(SPEAKER_STORAGE_KEY) !== 'false';

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
                // Speak active new orders immediately when toggled ON (only new ones)
                speakNewOrders(false);
            } else {
                if ('speechSynthesis' in window) {
                    window.speechSynthesis.cancel();
                }
            }
        }

        // Speak function
        function speakIndonesian(text) {
            if (!speakerEnabled) return;
            if ('speechSynthesis' in window) {
                // Cancel current speech if speaking
                window.speechSynthesis.cancel();
                
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = 'id-ID';
                
                const voices = window.speechSynthesis.getVoices();
                const idVoice = voices.find(v => v.lang.includes('id-ID') || v.lang.includes('id_ID'));
                if (idVoice) {
                    utterance.voice = idVoice;
                }
                
                utterance.rate = 0.95;
                utterance.pitch = 1;
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
                if (window.speechSynthesis.getVoices().length > 0) {
                    speakNewOrders();
                } else {
                    window.speechSynthesis.onvoiceschanged = function() {
                        speakNewOrders();
                        window.speechSynthesis.onvoiceschanged = null; // Unbind to run only once
                    };
                }
            }
        });
    </script>
@endsection
