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
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-coffee-cream font-sans min-h-screen text-coffee-text" x-data="menuCart()">

    <!-- Main Content -->
    <div class="max-w-2xl mx-auto px-4 py-6 pb-32 space-y-6">

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
            <div class="bg-amber-50 border border-amber-100 rounded-2xl p-5 space-y-3">
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
        <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-none sticky top-[38px] bg-coffee-cream py-2 z-20">
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
                            <div class="bg-white rounded-2xl border border-coffee-latte p-4 flex gap-4 coffee-card relative {{ $isHabis ? 'opacity-65' : '' }}">
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
                                        <span class="text-xs font-bold text-coffee-medium block mt-1">Rp {{ number_format($item->harga, 0, ',', '.') }}</span>
                                        @if($isPaket)
                                            <button 
                                                type="button"
                                                @click="openPackageDetails({{ json_encode($item->nama_menu) }}, {{ json_encode($foods) }}, {{ json_encode($drinks) }}, {{ json_encode($addons) }}, {{ json_encode($item->foto ? (str_starts_with($item->foto, 'http') ? $item->foto : asset($item->foto)) : '') }})"
                                                class="inline-flex items-center px-2.5 py-1 bg-coffee-gold/10 hover:bg-coffee-gold/20 text-coffee-dark border border-coffee-gold/20 rounded-full text-[9px] font-extrabold uppercase tracking-wider transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98] mt-2 cursor-pointer shadow-sm"
                                            >
                                                Isi Paket
                                            </button>
                                        @endif
                                    </div>
                                    <div class="flex items-center justify-end mt-2">
                                        @if($isHabis)
                                            <span class="text-[10px] font-extrabold uppercase text-red-600 bg-red-50 border border-red-100 px-2 py-0.5 rounded-md">Habis</span>
                                        @else
                                            <button 
                                                @click="addToCart({{ $item->id_menu }}, '{{ addslashes($item->nama_menu) }}', {{ $item->harga }})"
                                                class="px-3 py-1 bg-coffee-dark text-white rounded-lg text-xs font-bold hover:bg-coffee-medium transition cursor-pointer"
                                            >
                                                Tambah +
                                            </button>
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

        <!-- Sticky Bottom Shopping Cart bar -->
        <div 
            class="fixed bottom-0 left-0 right-0 p-4 bg-white/95 backdrop-blur-md border-t border-coffee-latte z-30 flex justify-center"
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

        <!-- Cart Details Drawer Modal -->
        <template x-teleport="body">
            <div 
                class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 flex items-end sm:items-center justify-center p-0 sm:p-4"
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
                                        <input 
                                            type="text" 
                                            :name="`cart[${id}][note]`"
                                            x-model="item.note"
                                            placeholder="Contoh: Kurang manis, es banyak"
                                            class="flex-grow bg-white border border-coffee-latte/50 rounded-lg px-2.5 py-1 text-[10px] text-coffee-dark focus:outline-none focus:border-coffee-light font-medium"
                                        >
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
        
        <!-- Package Details Modal -->
        <template x-teleport="body">
            <div 
                class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
                x-show="packageModal"
                x-transition
                style="display: none;"
            >
                <div 
                    @click.away="packageModal = false" 
                    class="bg-white w-full max-w-sm rounded-3xl border border-coffee-latte/60 p-6 flex flex-col coffee-card text-center space-y-4 shadow-2xl relative overflow-hidden"
                >
                    <!-- Decorative background blur -->
                    <div class="absolute -right-16 -top-16 w-32 h-32 rounded-full bg-coffee-gold/10 blur-xl"></div>
                    <div class="absolute -left-16 -bottom-16 w-32 h-32 rounded-full bg-coffee-medium/5 blur-xl"></div>

                    <!-- Header -->
                    <div class="flex items-center justify-between border-b border-coffee-latte/80 pb-3.5 z-10 relative">
                        <h3 class="font-extrabold text-coffee-dark text-left text-sm">
                            Detail Isi Paket
                        </h3>
                        <button @click="packageModal = false" class="text-coffee-light hover:text-coffee-dark font-extrabold text-xs p-1.5 hover:bg-coffee-cream rounded-lg transition cursor-pointer">Tutup</button>
                    </div>

                    <!-- Content -->
                    <div class="text-left space-y-5 py-2 z-10 relative">
                        <!-- Menu Photo (If exists) -->
                        <div x-show="packageDetails.foto" class="w-full h-40 rounded-2xl overflow-hidden border border-coffee-latte/50 mb-3 shadow-inner">
                            <img :src="packageDetails.foto" alt="Foto Paket" class="w-full h-full object-cover">
                        </div>

                        <h4 class="font-black text-coffee-dark text-base tracking-wide border-l-4 border-coffee-gold pl-2.5" x-text="packageDetails.name"></h4>
                        
                        <!-- Makanan List -->
                        <div x-show="packageDetails.foods && packageDetails.foods.length > 0" class="space-y-2">
                            <span class="text-[10px] uppercase font-bold text-coffee-medium tracking-widest block">Makanan Included</span>
                            <div class="grid gap-2">
                                <template x-for="food in packageDetails.foods">
                                    <div class="flex items-center gap-2.5 px-3 py-2 bg-coffee-cream/40 border border-coffee-latte/40 rounded-xl">
                                        <span class="flex-shrink-0 w-5 h-5 rounded-md bg-emerald-50 text-emerald-700 flex items-center justify-center text-[10px]">🍗</span>
                                        <span class="text-xs font-bold text-coffee-dark" x-text="food"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Minuman List -->
                        <div x-show="packageDetails.drinks && packageDetails.drinks.length > 0" class="space-y-2">
                            <span class="text-[10px] uppercase font-bold text-coffee-medium tracking-widest block">Minuman Included</span>
                            <div class="grid gap-2">
                                <template x-for="drink in packageDetails.drinks">
                                    <div class="flex items-center gap-2.5 px-3 py-2 bg-coffee-cream/40 border border-coffee-latte/40 rounded-xl">
                                        <span class="flex-shrink-0 w-5 h-5 rounded-md bg-amber-50 text-amber-700 flex items-center justify-center text-[10px]">🥤</span>
                                        <span class="text-xs font-bold text-coffee-dark" x-text="drink"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Addons -->
                        <div x-show="packageDetails.addons" class="space-y-2">
                            <span class="text-[10px] uppercase font-bold text-coffee-medium tracking-widest block">Add-on / Ekstra</span>
                            <div class="flex items-start gap-2.5 px-3 py-2.5 bg-blue-50/40 border border-blue-100 rounded-xl">
                                <span class="flex-shrink-0 w-5 h-5 rounded-md bg-blue-50 text-blue-700 flex items-center justify-center text-[10px]">✨</span>
                                <p class="text-xs font-bold text-coffee-dark leading-normal" x-text="packageDetails.addons"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Action -->
                    <button 
                        @click="packageModal = false" 
                        class="w-full py-3 bg-coffee-dark hover:bg-coffee-medium text-white rounded-2xl font-bold transition text-xs cursor-pointer shadow-md hover:shadow-lg transform active:scale-[0.98] z-10 relative"
                    >
                        Mengerti, Tutup
                    </button>
                </div>
            </div>
        </template>

    </div>

    <!-- Alpine cart JS scripts -->
    <script>
        function menuCart() {
            return {
                activeCategory: 'all',
                showCartDetails: false,
                packageModal: false,
                packageDetails: {
                    name: '',
                    foods: [],
                    drinks: [],
                    addons: '',
                    foto: ''
                },
                cartItems: {},
                
                openPackageDetails(name, foods, drinks, addons, foto) {
                    this.packageDetails = {
                        name: name,
                        foods: foods,
                        drinks: drinks,
                        addons: addons,
                        foto: foto
                    };
                    this.packageModal = true;
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
                }
            }
        }
    </script>
</body>
</html>
