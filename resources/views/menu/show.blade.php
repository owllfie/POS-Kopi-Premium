@php
    $settingsPath = storage_path('app/settings.json');
    $webSettings = [
        'nama_restoran' => 'Kopi Premium',
        'logo' => null,
        'pajak' => 10,
        'footer' => 'Terima kasih atas kunjungan Anda!',
        'bahasa' => 'id',
    ];
    if (file_exists($settingsPath)) {
        $webSettings = array_merge($webSettings, json_decode(file_get_contents($settingsPath), true));
    }
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Digital - Meja {{ $meja->nomor_meja }} - {{ $webSettings['nama_restoran'] }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- MediaPipe & Camera SDK for Hand Tracking -->
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils/drawing_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/hands/hands.js" crossorigin="anonymous"></script>
    
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-coffee-cream font-sans min-h-screen text-coffee-text antialiased" x-data="menuCart()">

    <!-- Main Container -->
    <div class="max-w-7xl mx-auto px-4 py-6 pb-32">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            
            <!-- Left Side: Banner, Cooking Queue, Categories, Menus -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Banner Header -->
                <div class="bg-coffee-dark text-white rounded-3xl p-6 shadow-xl border border-coffee-medium relative overflow-hidden">
                    <div class="absolute -right-8 -top-8 w-24 h-24 rounded-full bg-coffee-medium/30 blur-xl"></div>
                    <div class="flex items-center justify-between z-10 relative">
                        <div class="flex items-center gap-3">
                            @if(!empty($webSettings['logo']))
                                <img src="{{ asset($webSettings['logo']) }}" alt="Logo" class="w-12 h-12 rounded-2xl object-cover shadow border border-coffee-light/20">
                            @endif
                            <div>
                                <h2 class="text-xs uppercase font-extrabold text-coffee-gold tracking-widest">Selamat Datang di</h2>
                                <h1 class="text-2xl font-extrabold tracking-wide mt-1">{{ $webSettings['nama_restoran'] }}</h1>
                                <p class="text-xs text-coffee-light font-medium mt-1">Silakan pilih menu dan masukkan ke keranjang belanja.</p>
                            </div>
                        </div>
                        <div class="bg-coffee-medium/40 border border-coffee-light/20 rounded-2xl p-4 text-center">
                            <span class="block text-[10px] text-coffee-light uppercase font-bold tracking-wider">Nomor Meja</span>
                            <strong class="text-3xl font-black text-coffee-gold">{{ $meja->nomor_meja }}</strong>
                        </div>
                    </div>
                </div>

                @if(session('success'))
                    <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center gap-3 text-xs font-semibold shadow-sm">
                        <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl flex items-center gap-3 text-xs font-semibold shadow-sm">
                        <svg class="w-4 h-4 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <!-- Cooking Queue Status Section (for active table orders) -->
                @if($currentOrders->count() > 0)
                    <div class="bg-amber-50 border border-amber-100 rounded-2xl p-5 space-y-3 shadow-sm">
                        <div class="flex items-center justify-between border-b border-amber-200/50 pb-2">
                            <h3 class="text-xs font-bold text-coffee-medium uppercase tracking-wider flex items-center gap-1.5">
                                <span class="inline-block w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                Status Masakan Meja Anda
                            </h3>
                            <span class="text-[10px] bg-amber-100 text-coffee-medium px-2 py-0.5 rounded font-bold uppercase">Proses</span>
                        </div>
                        <div class="space-y-2">
                            @foreach($currentOrders as $item)
                                <div class="flex items-center justify-between text-xs">
                                    <div class="font-medium text-coffee-dark">
                                        <span>{{ $item->menu->nama_menu }}</span>
                                        <span class="text-coffee-light">x{{ $item->jumlah }}</span>
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border 
                                        @if($item->status === 'menunggu') bg-slate-100 border-slate-200 text-slate-600
                                        @elseif($item->status === 'dimasak') bg-amber-100 border-amber-200 text-coffee-light
                                        @else bg-emerald-100 border-emerald-200 text-emerald-800
                                        @endif"
                                    >
                                        @if($item->status === 'menunggu') Menunggu
                                        @elseif($item->status === 'dimasak') Sedang Dimasak
                                        @else Selesai
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                        <p class="text-[10px] text-coffee-light font-medium italic text-right pt-1">* Selesaikan pembayaran di kasir setelah hidangan disajikan.</p>
                    </div>
                @endif

                <!-- Category Menu Tabs -->
                <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-none sticky top-0 bg-coffee-cream py-3 z-20">
                    <button 
                        @click="activeCategory = 'all'" 
                        class="px-4 py-2 rounded-xl text-xs font-bold transition flex-shrink-0 cursor-pointer"
                        :class="activeCategory === 'all' ? 'bg-coffee-dark text-white shadow-md' : 'bg-white border border-coffee-latte text-coffee-light hover:bg-coffee-latte/50'"
                    >
                        Semua Menu
                    </button>
                    @foreach($categories as $cat)
                        <button 
                            @click="activeCategory = 'cat-{{ $cat->id_kategori }}'" 
                            class="px-4 py-2 rounded-xl text-xs font-bold transition flex-shrink-0 cursor-pointer"
                            :class="activeCategory === 'cat-{{ $cat->id_kategori }}' ? 'bg-coffee-dark text-white shadow-md' : 'bg-white border border-coffee-latte text-coffee-light hover:bg-coffee-latte/50'"
                        >
                            {{ $cat->kategori }}
                        </button>
                    @endforeach
                </div>

                <!-- Menu Grid -->
                <div class="space-y-6">
                    @foreach($categories as $cat)
                        <div 
                            x-show="activeCategory === 'all' || activeCategory === 'cat-{{ $cat->id_kategori }}'" 
                            class="space-y-4"
                            x-transition
                        >
                            <h3 class="text-sm font-bold text-coffee-light uppercase tracking-wider border-b border-coffee-latte pb-2">
                                {{ $cat->kategori }}
                            </h3>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @forelse($cat->menus as $item)
                                    @php 
                                        $isHabis = $item->status === 'habis'; 
                                        $foods = $item->getPaketMakananNames();
                                        $drinks = $item->getPaketMinumanNames();
                                        $addons = $item->paket_addons ?? '';
                                        $isPaket = !empty($foods) || !empty($drinks) || !empty($addons);
                                    @endphp
                                    <div 
                                        @click="openMenuDetail({
                                            id: {{ $item->id_menu }},
                                            name: '{{ addslashes($item->nama_menu) }}',
                                            price: {{ $item->harga }},
                                            category: '{{ addslashes($cat->kategori) }}',
                                            description: '{{ addslashes($item->deskripsi ?? '') }}',
                                            foto: '{{ $item->foto ? (str_starts_with($item->foto, 'http') ? $item->foto : asset($item->foto)) : '' }}',
                                            isHabis: {{ $isHabis ? 'true' : 'false' }},
                                            isPaket: {{ $isPaket ? 'true' : 'false' }},
                                            foods: {{ json_encode($foods) }},
                                            drinks: {{ json_encode($drinks) }},
                                            addons: '{{ addslashes($addons) }}'
                                        })"
                                        class="bg-white rounded-2xl border border-coffee-latte p-4 flex gap-4 coffee-card relative cursor-pointer hover:shadow-lg hover:border-coffee-light/50 transition-all duration-200 {{ $isHabis ? 'opacity-65' : '' }}"
                                    >
                                        <!-- Menu Photo Icon -->
                                        <div class="w-16 h-16 rounded-xl bg-coffee-latte flex items-center justify-center text-coffee-medium flex-shrink-0 border border-coffee-latte overflow-hidden">
                                            @if($item->foto)
                                                <img src="{{ str_starts_with($item->foto, 'http') ? $item->foto : asset($item->foto) }}" alt="{{ $item->nama_menu }}" class="w-full h-full object-cover">
                                            @else
                                                <!-- Simple Food SVG -->
                                                <svg class="w-8 h-8 text-coffee-light" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                            @endif
                                        </div>
                                        <div class="flex-grow flex flex-col justify-between">
                                            <div>
                                                <h4 class="text-sm font-bold text-coffee-dark leading-tight">{{ $item->nama_menu }}</h4>
                                            </div>
                                            <div class="flex items-center justify-end mt-2" @click.stop>
                                                @if($isHabis)
                                                    <span class="text-[10px] font-extrabold uppercase text-red-600 bg-red-50 border border-red-100 px-2 py-0.5 rounded-md">Habis</span>
                                                @else
                                                    <!-- Quantity Selector on Card if already in Cart -->
                                                    <template x-if="cartItems[{{ $item->id_menu }}]">
                                                        <div class="flex items-center gap-1.5">
                                                            <!-- Qty Buttons -->
                                                            <div class="flex items-center border border-coffee-latte rounded-lg bg-white overflow-hidden scale-90">
                                                                <button type="button" @click="changeQty({{ $item->id_menu }}, -1)" class="px-2 py-0.5 text-xs font-extrabold hover:bg-coffee-cream transition select-none">-</button>
                                                                <span class="px-2 text-xs font-extrabold text-coffee-dark" x-text="cartItems[{{ $item->id_menu }}].qty"></span>
                                                                <button type="button" @click="changeQty({{ $item->id_menu }}, 1)" class="px-2 py-0.5 text-xs font-extrabold hover:bg-coffee-cream transition select-none">+</button>
                                                            </div>
                                                            <!-- Hand Tracking Button on Menu Card -->
                                                            <button 
                                                                type="button"
                                                                @click="startHandTrackingForMenu({
                                                                    id: {{ $item->id_menu }},
                                                                    name: '{{ addslashes($item->nama_menu) }}',
                                                                    price: {{ $item->harga }}
                                                                })"
                                                                class="p-1.5 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 text-emerald-700 rounded-lg text-xs font-bold transition flex items-center justify-center cursor-pointer shadow-sm"
                                                                title="Atur jumlah dengan jari tangan"
                                                            >
                                                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z"/>
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z"/>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </template>
                                                    
                                                    <!-- Simple Add Button + Hand Tracking if not in Cart -->
                                                    <template x-if="!cartItems[{{ $item->id_menu }}]">
                                                        <div class="flex items-center gap-1.5">
                                                            <button 
                                                                @click="addToCart({{ $item->id_menu }}, '{{ addslashes($item->nama_menu) }}', {{ $item->harga }})"
                                                                class="px-2.5 py-1 bg-coffee-dark text-white rounded-lg text-xs font-bold hover:bg-coffee-medium transition cursor-pointer"
                                                            >
                                                                Tambah +
                                                            </button>
                                                            
                                                            <button 
                                                                type="button"
                                                                @click="startHandTrackingForMenu({
                                                                    id: {{ $item->id_menu }},
                                                                    name: '{{ addslashes($item->nama_menu) }}',
                                                                    price: {{ $item->harga }}
                                                                })"
                                                                class="p-1.5 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 text-emerald-700 rounded-lg text-xs font-bold transition flex items-center justify-center cursor-pointer shadow-sm"
                                                                title="Atur jumlah dengan jari tangan"
                                                            >
                                                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z"/>
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z"/>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </template>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-span-2 text-center py-6 text-xs text-coffee-light font-medium">Belum ada item menu di kategori ini.</div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Right Side: Persistent Cart for Large Screens (Desktop/Tablet) -->
            <div class="hidden lg:block lg:col-span-1 sticky top-6">
                <div class="bg-white rounded-3xl border border-coffee-latte p-6 flex flex-col max-h-[calc(100vh-80px)] coffee-card shadow-lg">
                    <div class="flex items-center gap-2 border-b border-coffee-latte pb-3">
                        <svg class="w-5 h-5 text-coffee-medium" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <h3 class="font-extrabold text-coffee-dark text-sm">Keranjang Belanja</h3>
                    </div>

                    <!-- Empty State -->
                    <template x-if="cartTotalItems === 0">
                        <div class="py-16 text-center space-y-3 flex-grow flex flex-col justify-center">
                            <div class="w-12 h-12 rounded-full bg-coffee-cream flex items-center justify-center text-coffee-light mx-auto border border-coffee-latte">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            </div>
                            <p class="text-xs font-semibold text-coffee-light">Keranjang belanja kosong.</p>
                        </div>
                    </template>

                    <!-- Cart items list -->
                    <template x-if="cartTotalItems > 0">
                        <form action="{{ route('guest.order', $meja->qrcode_token) }}" method="POST" class="flex flex-col flex-grow min-h-0 mt-4 space-y-4">
                            @csrf
                            <div class="flex-grow overflow-y-auto space-y-3 pr-1 max-h-[calc(100vh-280px)]">
                                <template x-for="(item, id) in cartItems" :key="id">
                                    <div class="p-3.5 bg-coffee-cream rounded-2xl border border-coffee-latte space-y-2.5">
                                        <div class="flex justify-between items-start">
                                            <div class="min-w-0 flex-1">
                                                <h4 class="text-xs font-extrabold text-coffee-dark truncate" x-text="item.name"></h4>
                                                <span class="text-[10px] font-bold text-coffee-light mt-0.5 block" x-text="formatRupiah(item.price * item.qty)"></span>
                                            </div>
                                            <!-- Qty Adjustment -->
                                            <div class="flex items-center border border-coffee-latte rounded-lg bg-white overflow-hidden flex-shrink-0 scale-90 origin-right">
                                                <button type="button" @click="changeQty(id, -1)" class="px-2 py-0.5 text-xs font-extrabold hover:bg-coffee-latte transition">-</button>
                                                <span class="px-2 text-xs font-extrabold text-coffee-dark" x-text="item.qty"></span>
                                                <button type="button" @click="changeQty(id, 1)" class="px-2 py-0.5 text-xs font-extrabold hover:bg-coffee-latte transition">+</button>
                                            </div>
                                        </div>
        
                                        <!-- Note input per item -->
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[9px] font-bold text-coffee-light flex-shrink-0">Catatan:</span>
                                            <div class="flex-grow flex items-center gap-1 relative">
                                                <input 
                                                    type="text" 
                                                    :name="`cart[${id}][note]`"
                                                    x-model="item.note"
                                                    :placeholder="recordingItemId === id ? 'Mendengarkan...' : 'Contoh: manis, es banyak'"
                                                    class="flex-grow bg-white border border-coffee-latte/50 rounded-lg pl-2 pr-7 py-0.5 text-[9px] text-coffee-dark focus:outline-none focus:border-coffee-light font-medium"
                                                >
                                                <button 
                                                    type="button" 
                                                    @click="startSpeechRecognition(id)" 
                                                    class="absolute right-1.5 top-1/2 -translate-y-1/2 p-0.5 rounded-md transition duration-150 flex items-center justify-center cursor-pointer"
                                                    :class="recordingItemId === id ? 'text-red-500 bg-red-50 animate-pulse' : 'text-coffee-light hover:text-coffee-dark hover:bg-coffee-cream'"
                                                    :title="recordingItemId === id ? 'Stop Rekam Suara' : 'Rekam Suara (Voice to Text)'"
                                                >
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 0 0 6-6v-1.5m-6 7.5a6 6 0 0 1-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 0 1-3-3V4.5a3 3 0 1 1 6 0v8.25a3 3 0 0 1-3 3Z"/>
                                                    </svg>
                                                </button>
                                            </div>
                                            <!-- Hidden input for quantity submission -->
                                            <input type="hidden" :name="`cart[${id}][qty]`" :value="item.qty">
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Checkout Summary -->
                            <div class="border-t border-coffee-latte pt-3.5 mt-auto space-y-3.5">
                                <div class="flex justify-between items-center text-xs font-bold text-coffee-dark">
                                    <span>Estimasi Total:</span>
                                    <span class="text-sm text-coffee-light font-black" x-text="formatRupiah(cartTotalPrice)"></span>
                                </div>
        
                                <button 
                                    type="submit" 
                                    class="w-full py-3 bg-coffee-dark text-white rounded-xl font-bold hover:bg-coffee-medium transition shadow-md text-xs cursor-pointer block text-center"
                                >
                                    Kirim Pesanan ke Dapur
                                </button>
                            </div>
                        </form>
                    </template>
                </div>
            </div>

        </div>
    </div>

    <!-- Sticky Bottom Shopping Cart bar (Visible on Mobile only) -->
    <div 
        class="fixed bottom-0 left-0 right-0 p-4 bg-white/95 backdrop-blur-md border-t border-coffee-latte z-30 flex justify-center lg:hidden"
        x-show="cartTotalItems > 0"
        x-transition
        style="display: none;"
    >
        <div class="max-w-2xl w-full flex items-center justify-between gap-4">
            <button 
                @click="showCartDetails = true"
                class="flex items-center gap-3 text-left focus:outline-none cursor-pointer"
            >
                <div class="w-11 h-11 bg-coffee-dark text-coffee-gold rounded-xl flex items-center justify-center relative shadow-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <span class="absolute -top-1.5 -right-1.5 bg-coffee-gold text-coffee-dark font-extrabold text-[10px] rounded-full w-5 h-5 flex items-center justify-center shadow" x-text="cartTotalItems"></span>
                </div>
                <div>
                    <span class="text-xs font-bold text-coffee-light block leading-none">Keranjang</span>
                    <strong class="text-sm font-extrabold text-coffee-dark leading-none block mt-1" x-text="formatRupiah(cartTotalPrice)"></strong>
                </div>
            </button>

            <button 
                @click="showCartDetails = true"
                class="py-3 px-6 bg-coffee-dark hover:bg-coffee-medium text-white rounded-xl text-xs font-bold transition shadow-md cursor-pointer"
            >
                Tinjau Pesanan
            </button>
        </div>
    </div>

    <!-- Cart Details Drawer Modal (Mobile only) -->
    <template x-teleport="body">
        <div 
            class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 flex items-end sm:items-center justify-center p-0 sm:p-4 lg:hidden"
            x-show="showCartDetails"
            x-transition
            style="display: none;"
        >
            <div 
                @click.away="showCartDetails = false" 
                class="bg-white w-full max-w-lg rounded-t-3xl sm:rounded-3xl border border-coffee-latte p-6 flex flex-col max-h-[90vh] sm:max-h-[80vh] coffee-card"
            >
                <div class="flex items-center justify-between border-b border-coffee-latte pb-3">
                    <h3 class="font-extrabold text-coffee-dark flex items-center gap-2">
                        <svg class="w-5 h-5 text-coffee-light" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        Tinjau Keranjang Anda
                    </h3>
                    <button @click="showCartDetails = false" class="text-coffee-light hover:text-coffee-dark font-bold text-xs p-1">Tutup</button>
                </div>

                <!-- Form wrapper -->
                <form action="{{ route('guest.order', $meja->qrcode_token) }}" method="POST" class="flex-grow flex flex-col min-h-0 mt-4 space-y-4">
                    @csrf
                    <!-- Cart items list (scrollable) -->
                    <div class="flex-grow overflow-y-auto space-y-3 pr-1">
                        <template x-for="(item, id) in cartItems" :key="id">
                            <div class="p-4 bg-coffee-cream rounded-2xl border border-coffee-latte space-y-3">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="text-sm font-bold text-coffee-dark" x-text="item.name"></h4>
                                        <span class="text-xs font-bold text-coffee-light" x-text="formatRupiah(item.price * item.qty)"></span>
                                    </div>
                                    <!-- Qty Adjustment -->
                                    <div class="flex items-center border border-coffee-latte rounded-xl bg-white overflow-hidden">
                                        <button type="button" @click="changeQty(id, -1)" class="px-2.5 py-1 text-xs font-extrabold hover:bg-coffee-latte transition">-</button>
                                        <span class="px-2.5 text-xs font-extrabold text-coffee-dark" x-text="item.qty"></span>
                                        <button type="button" @click="changeQty(id, 1)" class="px-2.5 py-1 text-xs font-extrabold hover:bg-coffee-latte transition">+</button>
                                    </div>
                                </div>

                                <!-- Note input per item -->
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-bold text-coffee-light">Catatan:</span>
                                    <div class="flex-grow flex items-center gap-1 relative">
                                        <input 
                                            type="text" 
                                            :name="`cart[${id}][note]`"
                                            x-model="item.note"
                                            :placeholder="recordingItemId === id ? 'Mendengarkan...' : 'Contoh: Kurang manis, es banyak'"
                                            class="flex-grow bg-white border border-coffee-latte/50 rounded-lg pl-2.5 pr-8 py-1 text-[10px] text-coffee-dark focus:outline-none focus:border-coffee-light font-medium"
                                        >
                                        <button 
                                            type="button" 
                                            @click="startSpeechRecognition(id)" 
                                            class="absolute right-2 top-1/2 -translate-y-1/2 p-0.5 rounded-md transition duration-150 flex items-center justify-center cursor-pointer"
                                            :class="recordingItemId === id ? 'text-red-500 bg-red-50 animate-pulse' : 'text-coffee-light hover:text-coffee-dark hover:bg-coffee-cream'"
                                            :title="recordingItemId === id ? 'Stop Rekam Suara' : 'Rekam Suara (Voice to Text)'"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 0 0 6-6v-1.5m-6 7.5a6 6 0 0 1-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 0 1-3-3V4.5a3 3 0 1 1 6 0v8.25a3 3 0 0 1-3 3Z"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <!-- Hidden input for quantity submission -->
                                    <input type="hidden" :name="`cart[${id}][qty]`" :value="item.qty">
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Checkout Summary -->
                    <div class="border-t border-coffee-latte pt-4 space-y-4">
                        <div class="flex justify-between items-center text-sm font-bold text-coffee-dark">
                            <span>Estimasi Total:</span>
                            <span class="text-base text-coffee-light font-black" x-text="formatRupiah(cartTotalPrice)"></span>
                        </div>

                        <div class="flex gap-3">
                            <button 
                                type="button" 
                                @click="showCartDetails = false" 
                                class="w-1/3 py-3 border border-coffee-light text-coffee-dark rounded-xl font-semibold hover:bg-coffee-latte transition text-xs"
                            >
                                Tambah Item
                            </button>
                            <button 
                                type="submit" 
                                class="w-2/3 py-3 bg-coffee-dark text-white rounded-xl font-bold hover:bg-coffee-medium transition shadow-md text-xs cursor-pointer"
                            >
                                Kirim Pesanan ke Dapur
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- Menu Detail Modal -->
    <template x-teleport="body">
        <div 
            class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
            x-show="menuDetailModal"
            x-transition
            style="display: none;"
        >
            <div 
                @click.away="menuDetailModal = false" 
                class="bg-white w-full max-w-3xl rounded-3xl border border-coffee-latte/60 p-6 flex flex-col coffee-card shadow-2xl relative overflow-hidden"
            >
                <!-- Decorative background blur -->
                <div class="absolute -right-16 -top-16 w-32 h-32 rounded-full bg-coffee-gold/10 blur-xl"></div>
                <div class="absolute -left-16 -bottom-16 w-32 h-32 rounded-full bg-coffee-medium/5 blur-xl"></div>

                <!-- Close button -->
                <button @click="menuDetailModal = false" class="absolute right-4 top-4 z-20 text-coffee-medium hover:text-coffee-dark font-extrabold text-xs p-1.5 hover:bg-coffee-cream rounded-lg transition cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>

                <!-- Two-Column Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4 items-start">
                    
                    <!-- Left Column: Picture & Description -->
                    <div class="space-y-4">
                        <!-- Image / Header -->
                        <div class="w-full h-48 sm:h-60 rounded-2xl overflow-hidden border border-coffee-latte bg-coffee-cream/40 relative flex items-center justify-center shadow-inner">
                            <template x-if="selectedMenu.foto">
                                <img :src="selectedMenu.foto" :alt="selectedMenu.name" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!selectedMenu.foto">
                                <svg class="w-16 h-16 text-coffee-light" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            </template>
                            <span class="absolute left-3 top-3 px-2.5 py-1 bg-coffee-dark/80 text-coffee-gold text-[10px] font-bold rounded-full uppercase tracking-wider backdrop-blur-sm" x-text="selectedMenu.category"></span>
                        </div>

                        <!-- Description -->
                        <div class="space-y-1.5 text-left">
                            <span class="text-[10px] uppercase font-bold text-coffee-medium tracking-widest block">Deskripsi</span>
                            <p class="text-xs text-coffee-light font-medium leading-relaxed" x-text="selectedMenu.description || 'Tidak ada deskripsi untuk menu ini.'"></p>
                        </div>
                    </div>
                    
                    <!-- Right Column: Details & Add to Cart button -->
                    <div class="space-y-5 flex flex-col justify-between h-full text-left">
                        <div class="space-y-4">
                            <!-- Title & Price -->
                            <div>
                                <h3 class="font-extrabold text-coffee-dark text-lg leading-tight" x-text="selectedMenu.name"></h3>
                                <strong class="text-base text-coffee-medium font-bold mt-1.5 block" x-text="formatRupiah(selectedMenu.price)"></strong>
                            </div>

                            <!-- Package Contents if Paket -->
                            <template x-if="selectedMenu.isPaket">
                                <div class="space-y-3 bg-coffee-cream/40 border border-coffee-latte/50 rounded-2xl p-4 shadow-sm">
                                    <span class="text-[10px] uppercase font-bold text-coffee-medium tracking-widest block">Isi Paket</span>
                                    
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <!-- Makanan -->
                                        <div x-show="selectedMenu.foods && selectedMenu.foods.length > 0" class="space-y-1.5">
                                            <span class="text-[9px] uppercase font-bold text-coffee-light tracking-wider block">Makanan</span>
                                            <template x-for="food in selectedMenu.foods">
                                                <div class="text-xs font-bold text-coffee-dark flex items-center gap-1.5">
                                                    <span>🍗</span>
                                                    <span x-text="food"></span>
                                                </div>
                                            </template>
                                        </div>

                                        <!-- Minuman -->
                                        <div x-show="selectedMenu.drinks && selectedMenu.drinks.length > 0" class="space-y-1.5">
                                            <span class="text-[9px] uppercase font-bold text-coffee-light tracking-wider block">Minuman</span>
                                            <template x-for="drink in selectedMenu.drinks">
                                                <div class="text-xs font-bold text-coffee-dark flex items-center gap-1.5">
                                                    <span>🥤</span>
                                                    <span x-text="drink"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Addons -->
                                    <div x-show="selectedMenu.addons" class="border-t border-coffee-latte/50 pt-2 mt-2">
                                        <span class="text-[9px] uppercase font-bold text-coffee-light tracking-wider block">Add-on / Extra</span>
                                        <p class="text-xs font-medium text-coffee-dark mt-1" x-text="selectedMenu.addons"></p>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Footer Action (Aligned at bottom of right column) -->
                        <div class="border-t border-coffee-latte pt-4 mt-4 space-y-4">
                            <template x-if="!selectedMenu.isHabis">
                                <!-- Qty Selector in Menu Detail Modal -->
                                <div class="flex items-center justify-between bg-coffee-cream/30 border border-coffee-latte/50 rounded-2xl p-3 shadow-sm">
                                    <span class="text-xs font-bold text-coffee-medium uppercase tracking-wider">Jumlah Pesanan</span>
                                    <div class="flex items-center gap-3">
                                        <div class="flex items-center border border-coffee-latte rounded-xl bg-white overflow-hidden">
                                            <button type="button" @click="if(detailQty > 1) detailQty--" class="px-3 py-1.5 text-sm font-extrabold hover:bg-coffee-cream transition select-none">-</button>
                                            <span class="px-4 text-sm font-extrabold text-coffee-dark" x-text="detailQty"></span>
                                            <button type="button" @click="if(detailQty < 20) detailQty++" class="px-3 py-1.5 text-sm font-extrabold hover:bg-coffee-cream transition select-none">+</button>
                                        </div>
                                        
                                        <button 
                                            type="button"
                                            @click="startHandTrackingFromDetail()"
                                            class="px-3 py-1.5 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 text-emerald-700 rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-sm"
                                        >
                                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z"/></svg>
                                            <span>Jari Tangan (AI)</span>
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <div class="flex items-center justify-between gap-4">
                                <template x-if="selectedMenu.isHabis">
                                    <span class="w-full text-center py-3 bg-red-50 text-red-600 border border-red-100 rounded-xl font-bold text-xs uppercase tracking-wider">Menu Habis</span>
                                </template>
                                <template x-if="!selectedMenu.isHabis">
                                    <button 
                                        @click="addToCartFromDetail()" 
                                        class="w-full py-3 bg-coffee-dark hover:bg-coffee-medium text-white rounded-xl font-bold transition text-xs cursor-pointer shadow-md hover:shadow-lg transform active:scale-[0.98] flex items-center justify-center gap-2"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                        Tambah ke Keranjang
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- Hand Tracking Modal -->
    <template x-teleport="body">
        <div 
            class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[100] flex flex-col items-center justify-center p-4"
            x-show="handTrackingModal"
            x-transition
            style="display: none;"
        >
            <div class="bg-white rounded-3xl overflow-hidden shadow-2xl max-w-xl w-full flex flex-col max-h-[95vh] coffee-card border border-coffee-latte relative">
                <!-- Close / Back Button -->
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-extrabold text-coffee-dark text-base">Atur Jumlah dengan Jari</h3>
                        <p class="text-xs text-coffee-light font-medium mt-1">
                            Menu: <span class="text-coffee-medium font-bold" x-text="handTrackingTarget === 'detail' ? selectedMenu.name : (handTrackingTarget ? handTrackingTarget.name : '')"></span>
                        </p>
                        <p class="text-[10px] text-coffee-light font-medium mt-0.5">Angkat jari tangan Anda (1-5) di depan kamera.</p>
                    </div>
                    <button @click="stopHandTracking()" class="text-coffee-medium hover:text-coffee-dark font-extrabold text-xs p-1.5 hover:bg-coffee-cream rounded-lg transition cursor-pointer">
                        Tutup
                    </button>
                </div>
                
                <!-- Video/Canvas Area -->
                <div class="relative bg-slate-950 flex items-center justify-center overflow-hidden aspect-video">
                    <!-- Invisible webcam video -->
                    <video id="hand-webcam" autoplay playsinline muted style="position: absolute; left: -9999px; top: -9999px; width: 1px; height: 1px; opacity: 0; pointer-events: none;"></video>
                    
                    <!-- Mirrored Output Canvas -->
                    <canvas id="hand-canvas" width="640" height="480" class="w-full h-full object-cover transform scale-x-[-1]"></canvas>

                    <!-- Camera Loading Overlay -->
                    <div x-show="isCameraLoading" class="absolute inset-0 bg-coffee-dark/90 flex flex-col items-center justify-center gap-3 text-white">
                        <svg class="animate-spin h-8 w-8 text-coffee-gold" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-xs font-bold text-coffee-gold uppercase tracking-wider">Menyalakan Kamera AI...</span>
                    </div>

                    <!-- Camera Error Overlay -->
                    <div x-show="hasCameraError" class="absolute inset-0 bg-coffee-dark/90 flex flex-col items-center justify-center p-6 text-center gap-3 text-white">
                        <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span class="text-sm font-bold text-red-400">Gagal Mengakses Kamera</span>
                        <p class="text-xs text-coffee-light max-w-sm" x-text="cameraErrorMessage || 'Pastikan Anda telah memberikan izin akses kamera pada browser Anda.'"></p>
                    </div>
                </div>

                <!-- Info and Confirm Button -->
                <div class="p-6 bg-coffee-cream/40 border-t border-slate-100 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-coffee-dark rounded-2xl flex items-center justify-center text-coffee-gold border border-coffee-light/20 shadow-inner">
                            <span class="text-2xl font-black" x-text="detectedFingers">0</span>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-coffee-medium tracking-wider block">Jumlah Terdeteksi</span>
                            <span class="text-xs font-bold text-coffee-dark" x-text="detectedFingers > 0 ? `${detectedFingers} Porsi` : 'Menunggu jari tangan...'"></span>
                        </div>
                    </div>

                    <button 
                        type="button" 
                        @click="useHandCount()"
                        :disabled="detectedFingers === 0"
                        class="px-8 py-3 bg-coffee-dark text-white rounded-xl font-bold hover:bg-coffee-medium transition shadow-lg text-xs cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Gunakan Jumlah Ini
                    </button>
                </div>
            </div>
        </div>
    </template>

    <!-- Alpine cart JS scripts -->
    <script>
        function menuCart() {
            return {
                activeCategory: 'all',
                showCartDetails: false,
                menuDetailModal: false,
                selectedMenu: {
                    id: null,
                    name: '',
                    price: 0,
                    category: '',
                    description: '',
                    foto: '',
                    isHabis: false,
                    isPaket: false,
                    foods: [],
                    drinks: [],
                    addons: ''
                },
                cartItems: {},
                recordingItemId: null,
                recognition: null,
                detailQty: 1,
                
                // Hand Tracking States
                handTrackingModal: false,
                detectedFingers: 0,
                isCameraLoading: false,
                hasCameraError: false,
                cameraErrorMessage: '',
                handTrackingTarget: null, // 'detail' or { id, name, price }
                latestLandmarks: null,
                
                openMenuDetail(item) {
                    this.selectedMenu = item;
                    this.detailQty = 1; // Reset to 1
                    this.menuDetailModal = true;
                },
                
                addToCart(id, name, price) {
                    if (this.cartItems[id]) {
                        this.cartItems[id].qty++;
                    } else {
                        this.cartItems[id] = {
                            name: name,
                            price: price,
                            qty: 1,
                            note: ''
                        };
                    }
                },

                addToCartFromDetail() {
                    const id = this.selectedMenu.id;
                    const name = this.selectedMenu.name;
                    const price = this.selectedMenu.price;
                    const qty = this.detailQty;

                    if (this.cartItems[id]) {
                        this.cartItems[id].qty += qty;
                    } else {
                        this.cartItems[id] = {
                            name: name,
                            price: price,
                            qty: qty,
                            note: ''
                        };
                    }
                    this.menuDetailModal = false;
                },
                
                changeQty(id, delta) {
                    if (!this.cartItems[id]) return;
                    this.cartItems[id].qty += delta;
                    if (this.cartItems[id].qty <= 0) {
                        delete this.cartItems[id];
                    }
                },
                
                get cartTotalItems() {
                    return Object.values(this.cartItems).reduce((sum, item) => sum + item.qty, 0);
                },
                
                get cartTotalPrice() {
                    return Object.values(this.cartItems).reduce((sum, item) => sum + (item.price * item.qty), 0);
                },
                
                formatRupiah(val) {
                    return 'Rp ' + val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                },

                startSpeechRecognition(id) {
                    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                    if (!SpeechRecognition) {
                        alert("Voice-to-text tidak didukung oleh browser Anda. Silakan gunakan Chrome atau Safari.");
                        return;
                    }

                    if (this.recordingItemId === id) {
                        if (this.recognition) {
                            this.recognition.stop();
                        }
                        this.recordingItemId = null;
                        return;
                    }

                    if (this.recognition) {
                        this.recognition.stop();
                    }

                    this.recordingItemId = id;
                    this.recognition = new SpeechRecognition();
                    this.recognition.lang = 'id-ID';
                    this.recognition.interimResults = false;

                    this.recognition.onresult = (event) => {
                        const transcript = event.results[0][0].transcript;
                        if (this.cartItems[id]) {
                            this.cartItems[id].note = transcript;
                        }
                    };

                    this.recordingItemId = null;
                    this.recognition.onerror = (event) => {
                        console.error("Speech recognition error", event.error);
                        this.recordingItemId = null;
                    };

                    this.recognition.onend = () => {
                        this.recordingItemId = null;
                    };

                    this.recognition.start();
                },

                // Hand Tracking Logic
                startHandTrackingFromDetail() {
                    this.handTrackingTarget = 'detail';
                    this.startHandTracking();
                },

                startHandTrackingForMenu(item) {
                    this.handTrackingTarget = item;
                    this.startHandTracking();
                },

                startHandTracking() {
                    this.handTrackingModal = true;
                    this.isCameraLoading = true;
                    this.hasCameraError = false;
                    this.cameraErrorMessage = '';
                    this.detectedFingers = 0;
                    this.latestLandmarks = null;

                    // 1. Check if browser supports mediaDevices and getUserMedia (secure context check)
                    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                        console.error("Camera access not supported or blocked (insecure context/HTTP).");
                        this.isCameraLoading = false;
                        this.hasCameraError = true;
                        this.cameraErrorMessage = "Browser Anda memblokir akses kamera. Gunakan HTTPS atau localhost untuk menjalankannya.";
                        return;
                    }

                    this.$nextTick(() => {
                        const videoElement = document.getElementById('hand-webcam');
                        const canvasElement = document.getElementById('hand-canvas');
                        const canvasCtx = canvasElement.getContext('2d');

                        if (!videoElement || !canvasElement) return;

                        if (!window.handsDetector) {
                            const HandsConstructor = window.Hands || (typeof Hands !== 'undefined' ? Hands : null);
                            if (!HandsConstructor) {
                                console.error("MediaPipe Hands SDK is not loaded.");
                                this.isCameraLoading = false;
                                this.hasCameraError = true;
                                this.cameraErrorMessage = "SDK MediaPipe Hands gagal dimuat dari CDN. Periksa koneksi internet Anda.";
                                return;
                            }

                            // Use unpinned version matching the script tag
                            window.handsDetector = new HandsConstructor({
                                locateFile: (file) => `https://cdn.jsdelivr.net/npm/@mediapipe/hands/${file}`
                            });

                            window.handsDetector.setOptions({
                                maxNumHands: 1,
                                modelComplexity: 1,
                                minDetectionConfidence: 0.6,
                                minTrackingConfidence: 0.6
                            });

                            window.handsDetector.onResults((results) => {
                                let totalFingersUp = 0;

                                if (results.multiHandLandmarks) {
                                    this.latestLandmarks = results.multiHandLandmarks;

                                    for (let index = 0; index < results.multiHandLandmarks.length; index++) {
                                        const landmarks = results.multiHandLandmarks[index];
                                        const fingerTips = [8, 12, 16, 20];
                                        fingerTips.forEach(tipId => {
                                            if (landmarks[tipId].y < landmarks[tipId - 2].y) {
                                                totalFingersUp++;
                                            }
                                        });

                                        if (results.multiHandedness && results.multiHandedness[index]) {
                                            const isLeftHand = results.multiHandedness[index].label === 'Left';
                                            if (isLeftHand) {
                                                if (landmarks[4].x > landmarks[3].x) totalFingersUp++;
                                            } else {
                                                if (landmarks[4].x < landmarks[3].x) totalFingersUp++;
                                            }
                                        }
                                    }
                                } else {
                                    this.latestLandmarks = null;
                                }
                                
                                this.detectedFingers = totalFingersUp;
                            });
                        }

                        const CameraConstructor = window.Camera || (typeof Camera !== 'undefined' ? Camera : null);
                        if (!CameraConstructor) {
                            console.error("MediaPipe Camera SDK is not loaded.");
                            this.isCameraLoading = false;
                            this.hasCameraError = true;
                            this.cameraErrorMessage = "SDK MediaPipe Camera gagal dimuat dari CDN. Periksa koneksi internet Anda.";
                            return;
                        }

                        let isProcessing = false;
                        window.cameraInstance = new CameraConstructor(videoElement, {
                            onFrame: async () => {
                                if (!this.handTrackingModal) return;

                                // 1. Draw raw video feed immediately (buttery smooth 30fps stream)
                                canvasCtx.clearRect(0, 0, canvasElement.width, canvasElement.height);
                                canvasCtx.drawImage(videoElement, 0, 0, canvasElement.width, canvasElement.height);

                                // 2. Draw latest overlays if they exist
                                if (this.latestLandmarks) {
                                    const HandConnections = window.HAND_CONNECTIONS || (typeof HAND_CONNECTIONS !== 'undefined' ? HAND_CONNECTIONS : null);
                                    const drawConnectorsFunc = window.drawConnectors || (typeof drawConnectors !== 'undefined' ? drawConnectors : null);
                                    const drawLandmarksFunc = window.drawLandmarks || (typeof drawLandmarks !== 'undefined' ? drawLandmarks : null);

                                    for (let index = 0; index < this.latestLandmarks.length; index++) {
                                        const landmarks = this.latestLandmarks[index];
                                        if (drawConnectorsFunc && HandConnections) {
                                            drawConnectorsFunc(canvasCtx, landmarks, HandConnections, {color: '#00e676', lineWidth: 4});
                                        }
                                        if (drawLandmarksFunc) {
                                            drawLandmarksFunc(canvasCtx, landmarks, {color: '#ff3d00', lineWidth: 1, radius: 3});
                                        }
                                    }
                                }

                                // 3. Run hands detection in background
                                if (!isProcessing) {
                                    isProcessing = true;
                                    try {
                                        await window.handsDetector.send({image: videoElement});
                                    } catch (e) {
                                        console.error("Hands detector send failed", e);
                                        this.hasCameraError = true;
                                        this.cameraErrorMessage = "AI Error: " + e.message;
                                    }
                                    isProcessing = false;
                                }
                            },
                            width: 640,
                            height: 480
                        });

                        window.cameraInstance.start()
                            .then(() => {
                                // Camera started successfully. Hide loading overlay immediately.
                                this.isCameraLoading = false;
                            })
                            .catch(err => {
                                console.error("Camera start failed", err);
                                this.isCameraLoading = false;
                                this.hasCameraError = true;
                                this.cameraErrorMessage = "Gagal menyalakan kamera. Pastikan izin kamera telah diberikan di browser.";
                            });
                    });
                },

                stopHandTracking() {
                    this.handTrackingModal = false;
                    this.handTrackingTarget = null;
                    this.latestLandmarks = null;
                    if (window.cameraInstance) {
                        try {
                            window.cameraInstance.stop();
                        } catch (e) {
                            console.warn("Error stopping camera helper:", e);
                        }
                        window.cameraInstance = null;
                    }
                    // Release webcam stream manually
                    const videoElement = document.getElementById('hand-webcam');
                    if (videoElement && videoElement.srcObject) {
                        const stream = videoElement.srcObject;
                        const tracks = stream.getTracks();
                        tracks.forEach(track => track.stop());
                        videoElement.srcObject = null;
                    }
                },

                useHandCount() {
                    if (this.detectedFingers > 0) {
                        if (this.handTrackingTarget === 'detail') {
                            this.detailQty = this.detectedFingers;
                        } else if (this.handTrackingTarget && this.handTrackingTarget.id) {
                            const target = this.handTrackingTarget;
                            if (this.cartItems[target.id]) {
                                this.cartItems[target.id].qty = this.detectedFingers;
                            } else {
                                this.cartItems[target.id] = {
                                    name: target.name,
                                    price: target.price,
                                    qty: this.detectedFingers,
                                    note: ''
                                };
                            }
                        }
                    }
                    this.stopHandTracking();
                }
            }
        }
    </script>
</body>
</html>
