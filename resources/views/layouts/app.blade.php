<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'POS Restoran') - Coffee Shop</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- AlpineJS for UI states -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Cropper.js -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    @yield('styles')
</head>
<body class="bg-coffee-cream font-sans min-h-screen text-coffee-text antialiased" x-data="{ sidebarOpen: true }">

    @php
        // Resolve the active simulated user
        $simUser = null;
        if (session()->has('simulated_user_id')) {
            $simUser = \App\Models\User::find(session('simulated_user_id'));
        }
        if (!$simUser && auth()->check()) {
            $simUser = auth()->user();
        }
        
        $roleName = $simUser ? $simUser->role->role : 'Guest';
        
        // Modules lists to render in sidebar
        $sidebarItems = [
            ['module' => 'dashboard', 'route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'chart-bar'],
            ['module' => 'pesanan', 'route' => 'pesanan', 'label' => 'Antrean Pesanan', 'icon' => 'clipboard-list'],
            ['module' => 'laporan', 'route' => 'laporan', 'label' => 'Laporan Keuangan', 'icon' => 'document-report'],
            ['module' => 'transaksi', 'route' => 'transaksi', 'label' => 'Riwayat Transaksi', 'icon' => 'history'],
            ['module' => 'users', 'route' => 'users.index', 'label' => 'Kelola Users', 'icon' => 'users'],
            ['module' => 'menu', 'route' => 'menu.index', 'label' => 'Kelola Menu', 'icon' => 'coffee'],
            ['module' => 'kategori', 'route' => 'kategori.index', 'label' => 'Kelola Kategori', 'icon' => 'tag'],
            ['module' => 'meja', 'route' => 'meja.index', 'label' => 'Kelola Meja & QR', 'icon' => 'table'],
            ['module' => 'bahan_alat', 'route' => 'bahan-alat.index', 'label' => 'Bahan', 'icon' => 'archive'],
            ['module' => 'properti', 'route' => 'properti.index', 'label' => 'Properti Cafe', 'icon' => 'office-building'],
            ['module' => 'shift', 'route' => 'shift.index', 'label' => 'Kelola Shift', 'icon' => 'clock'],
            ['module' => 'akses', 'route' => 'akses.index', 'label' => 'Hak Akses', 'icon' => 'shield'],
            ['module' => 'log', 'route' => 'log', 'label' => 'Log Aktivitas', 'icon' => 'database-log'],
            ['module' => 'setting', 'route' => 'setting.index', 'label' => 'Pengaturan Web', 'icon' => 'cog'],
            ['module' => 'backup', 'route' => 'backup.index', 'label' => 'Backup Database', 'icon' => 'cloud-download'],
        ];
    @endphp

    <!-- Main Wrapper -->
    <div class="flex min-h-[calc(100vh-28px)] relative overflow-hidden">
        
        <!-- Sidebar -->
        <aside 
            class="bg-coffee-dark text-coffee-latte w-64 flex-shrink-0 flex flex-col border-r border-coffee-medium transition-all duration-300 z-30"
            :class="sidebarOpen ? 'translate-x-0 ml-0' : '-translate-x-full -ml-64'"
        >
            <!-- Logo & Brand Header -->
            <div class="p-6 border-b border-coffee-medium flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-coffee-gold/15 flex items-center justify-center text-coffee-gold border border-coffee-gold/20 shadow-inner">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-white tracking-wide">Kopi Premium</h2>
                    </div>
                </div>
            </div>

            <!-- Sidebar Navigation Links -->
            <nav class="flex-1 overflow-y-auto px-4 py-6 space-y-1.5">
                @foreach ($sidebarItems as $item)
                    @if ($simUser && $simUser->canAccess($item['module']))
                        @php
                            $isActive = request()->routeIs($item['route']) || 
                                        (explode('.', $item['route'])[0] !== 'dashboard' && request()->is(explode('.', $item['route'])[0] . '*'));
                        @endphp
                        <a 
                            href="{{ route($item['route']) }}" 
                            class="flex items-center gap-3 py-3 px-4 rounded-xl font-medium text-sm transition-all duration-200 group {{ $isActive ? 'bg-coffee-medium text-coffee-gold shadow-md border-l-4 border-coffee-gold pl-3' : 'hover:bg-coffee-medium/40 hover:text-white' }}"
                        >
                            <span class="w-5 h-5 flex items-center justify-center transition group-hover:scale-105 {{ $isActive ? 'text-coffee-gold' : 'text-coffee-light group-hover:text-coffee-gold' }}">
                                @if($item['icon'] === 'chart-bar')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z"/></svg>
                                @elseif($item['icon'] === 'clipboard-list')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012-2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                @elseif($item['icon'] === 'document-report')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                @elseif($item['icon'] === 'history')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @elseif($item['icon'] === 'users')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                @elseif($item['icon'] === 'coffee')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                @elseif($item['icon'] === 'tag')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                @elseif($item['icon'] === 'table')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                @elseif($item['icon'] === 'clock')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @elseif($item['icon'] === 'shield')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                @elseif($item['icon'] === 'database-log')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                                @elseif($item['icon'] === 'cog')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
                                @elseif($item['icon'] === 'cloud-download')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/></svg>
                                @elseif($item['icon'] === 'archive')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                @elseif($item['icon'] === 'office-building')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                @endif
                            </span>
                            <span class="truncate">{{ $item['label'] }}</span>
                        </a>
                    @endif
                @endforeach
            </nav>

            <!-- Sidebar Footer -->
            <div class="p-4 border-t border-coffee-medium bg-coffee-dark/60 flex items-center justify-between">
                <div class="flex items-center gap-3 overflow-hidden">
                    <div class="w-9 h-9 rounded-full bg-coffee-gold text-coffee-dark font-bold flex items-center justify-center flex-shrink-0 text-sm">
                        {{ strtoupper(substr($simUser ? $simUser->username : 'G', 0, 2)) }}
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-sm font-semibold text-white truncate leading-tight">{{ $simUser ? $simUser->username : 'Guest' }}</p>
                        <p class="text-xs text-coffee-light font-medium truncate uppercase tracking-wider">{{ $roleName }}</p>
                    </div>
                </div>
                <a href="{{ route('logout') }}" class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-red-950/40 hover:text-red-400 text-coffee-light transition duration-150" title="Keluar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </a>
            </div>
        </aside>

        <!-- Content Area -->
        <div class="flex-1 flex flex-col min-w-0">
            <header class="h-16 bg-white border-b border-coffee-latte flex items-center justify-between px-6 z-20">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg hover:bg-coffee-latte text-coffee-medium transition focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <h1 class="text-xl font-bold text-coffee-dark">@yield('page_title', 'Halaman Utama')</h1>
                </div>
                <div class="flex items-center gap-4 text-sm text-coffee-medium">
                    <div id="live-clock" class="font-medium text-xs tracking-wide"></div>
                </div>
            </header>

            <main class="flex-grow p-6 overflow-y-auto">
                @if(session('success'))
                    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center gap-3 text-sm font-medium shadow-sm animate-fade-in">
                        <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl flex items-center gap-3 text-sm font-medium shadow-sm animate-fade-in">
                        <svg class="w-5 h-5 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Live clock & Helpers -->
    <script>
        function updateClock() {
            const now = new Date();
            const clockEl = document.getElementById('live-clock');
            if (clockEl) {
                const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                clockEl.textContent = `${days[now.getDay()]}, ${now.getDate().toString().padStart(2, '0')} ${months[now.getMonth()]} ${now.getFullYear()} — ${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}:${now.getSeconds().toString().padStart(2, '0')}`;
            }
        }
        setInterval(updateClock, 1000);
        updateClock();

        function formatRupiahHelper(value) {
            if (!value) return '';
            let valStr = value.toString().replace(/[^0-9]/g, '');
            if (!valStr) return '';
            return valStr.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }
    </script>

    <!-- Global Image Cropping Modal -->
    <div id="cropper-modal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-black/80 backdrop-blur-md">
        <div class="bg-white rounded-3xl overflow-hidden shadow-2xl max-w-2xl w-full flex flex-col max-h-[90vh]">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-slate-800">Sesuaikan Foto</h3>
                <button onclick="closeCropper()" class="text-slate-400 hover:text-slate-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="flex-grow overflow-hidden bg-slate-50 flex items-center justify-center min-h-[300px]">
                <img id="cropper-image" src="" alt="To Crop" class="max-w-full block">
            </div>
            <div class="p-6 border-t border-slate-100 flex justify-between gap-4">
                <button onclick="closeCropper()" class="px-6 py-2.5 border border-slate-200 text-slate-600 rounded-xl font-bold hover:bg-slate-50 transition text-sm">Batal</button>
                <button id="crop-button" class="px-8 py-2.5 bg-coffee-dark text-white rounded-xl font-bold hover:bg-coffee-medium transition shadow-lg text-sm">Potong & Simpan</button>
            </div>
        </div>
    </div>

    <!-- Global Receipt Modal Handler -->
    @if(session()->has('print_receipt_id'))
        @php $receipt = \App\Models\Pesanan::with(['meja', 'details.menu', 'user'])->find(session('print_receipt_id')); @endphp
        @if($receipt)
            <div x-data="{ showReceipt: true }" x-show="showReceipt" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" id="receipt-modal-wrapper">
                <div class="bg-white max-w-sm w-full rounded-2xl shadow-2xl p-6 border border-coffee-latte flex flex-col justify-between" @click.away="showReceipt = false">
                    <div id="printable-receipt-area" class="bg-white text-black p-4 font-mono text-xs border border-dashed border-slate-300">
                        <div class="text-center space-y-1 mb-4">
                            <h2 class="text-sm font-bold uppercase tracking-wider">Kopi Premium Resto</h2>
                            <p class="text-[10px]">Jl. Cokelat Hangat No. 12</p>
                            <div class="border-b border-dashed border-slate-300 my-2"></div>
                        </div>
                        <div class="space-y-1 text-[10px]">
                            <div class="flex justify-between"><span>No: {{ $receipt->kode_struk }}</span><span>Meja: {{ $receipt->meja->nomor_meja }}</span></div>
                            <div class="flex justify-between"><span>Kasir: {{ $receipt->user ? $receipt->user->username : 'System' }}</span><span>Tgl: {{ $receipt->created_at->format('d/m/y H:i') }}</span></div>
                        </div>
                        <div class="border-b border-dashed border-slate-300 my-2"></div>
                        <div class="space-y-2">
                            @foreach($receipt->details as $item)
                                <div><div class="flex justify-between"><span class="font-semibold">{{ $item->menu->nama_menu }}</span><span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span></div><div class="text-[10px] text-slate-500">{{ $item->jumlah }} x Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</div></div>
                            @endforeach
                        </div>
                        <div class="border-b border-dashed border-slate-300 my-2"></div>
                        <div class="space-y-1">
                            <div class="flex justify-between"><span>Subtotal:</span><span>Rp {{ number_format($receipt->total_harga, 0, ',', '.') }}</span></div>
                            <div class="flex justify-between"><span>Pajak (10%):</span><span>Rp {{ number_format($receipt->pajak, 0, ',', '.') }}</span></div>
                            <div class="flex justify-between font-bold text-sm border-t border-dotted border-slate-300 pt-1.5 mt-1"><span>TOTAL BAYAR:</span><span>Rp {{ number_format($receipt->total_bayar, 0, ',', '.') }}</span></div>
                        </div>
                        <div class="border-b border-dashed border-slate-300 my-2"></div>
                        <div class="text-center text-[9px] text-slate-500 space-y-0.5 mt-2">
                            <p class="font-bold">METODE: {{ strtoupper($receipt->metode_pembayaran) }}</p>
                            <p>Terima kasih atas kunjungan Anda!</p>
                        </div>
                    </div>
                    <div class="flex gap-2 mt-4 receipt-no-print">
                        <button onclick="window.print()" class="w-1/2 py-2.5 bg-coffee-dark hover:bg-coffee-medium text-white text-xs font-bold rounded-xl shadow transition">Cetak Struk</button>
                        <button @click="showReceipt = false" class="w-1/2 py-2.5 bg-coffee-cream hover:bg-coffee-latte text-coffee-dark border border-coffee-light text-xs font-bold rounded-xl transition">Tutup</button>
                    </div>
                </div>
            </div>
            <style>@media print { body * { visibility: hidden; } #printable-receipt-area, #printable-receipt-area * { visibility: visible; } #printable-receipt-area { position: absolute; left: 50%; top: 5%; transform: translateX(-50%); width: 80mm; margin: 0; padding: 10px; border: none !important; } .receipt-no-print { display: none !important; } #receipt-modal-wrapper { background: white !important; backdrop-filter: none !important; } }</style>
        @endif
    @endif

    <script>
        let currentCropper = null;

        function initImageCropper(inputElement, hiddenInputName, previewImgId = null, aspectRatio = 1) {
            if (!inputElement) return;
            inputElement.addEventListener('change', function(e) {
                const files = e.target.files;
                if (files && files.length > 0) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        openCropper(event.target.result, aspectRatio, function(croppedBase64) {
                            let hiddenInput = inputElement.form.querySelector(`input[name="${hiddenInputName}"]`);
                            if (!hiddenInput) {
                                hiddenInput = document.createElement('input');
                                hiddenInput.type = 'hidden';
                                hiddenInput.name = hiddenInputName;
                                inputElement.form.appendChild(hiddenInput);
                            }
                            hiddenInput.value = croppedBase64;
                            if (previewImgId) {
                                const preview = document.getElementById(previewImgId);
                                if (preview) {
                                    preview.src = croppedBase64;
                                    preview.classList.remove('hidden');
                                }
                            }
                        });
                    };
                    reader.readAsDataURL(files[0]);
                }
            });
        }

        function openCropper(imageSrc, aspectRatio, callback) {
            const modal = document.getElementById('cropper-modal');
            const image = document.getElementById('cropper-image');
            const cropBtn = document.getElementById('crop-button');

            image.src = imageSrc;
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            if (currentCropper) currentCropper.destroy();

            currentCropper = new Cropper(image, {
                aspectRatio: aspectRatio,
                viewMode: 1,
                guides: true,
                autoCropArea: 1,
            });

            cropBtn.onclick = function() {
                const canvas = currentCropper.getCroppedCanvas({ width: 500, height: 500 });
                callback(canvas.toDataURL('image/png'));
                closeCropper();
            };
        }

        function closeCropper() {
            const modal = document.getElementById('cropper-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            if (currentCropper) {
                currentCropper.destroy();
                currentCropper = null;
            }
        }
    </script>

    @yield('scripts')
</body>
</html>
