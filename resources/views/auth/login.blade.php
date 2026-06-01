<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - POS Restoran Premium</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-coffee-cream flex items-center justify-center min-h-screen p-4">

    <div class="max-w-4xl w-full bg-white rounded-3xl border border-coffee-latte shadow-2xl flex flex-col md:flex-row overflow-hidden coffee-card">
        
        <!-- Left Side: Coffee Shop Vibe & Welcome -->
        <div class="md:w-1/2 bg-coffee-dark text-coffee-cream p-12 flex flex-col justify-between relative overflow-hidden">
            <!-- Decorative wood overlay or coffee elements -->
            <div class="absolute -right-16 -top-16 w-48 h-48 rounded-full bg-coffee-medium/20 blur-2xl"></div>
            <div class="absolute -left-16 -bottom-16 w-48 h-48 rounded-full bg-coffee-light/20 blur-2xl"></div>
            
            <div class="z-10">
                <div class="w-12 h-12 rounded-2xl bg-coffee-gold/25 border border-coffee-gold/20 flex items-center justify-center text-coffee-gold mb-6 shadow-inner">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-extrabold text-white tracking-wide leading-tight mb-2">KOPI PREMIUM</h2>
                <p class="text-coffee-light text-sm font-medium leading-relaxed">Aplikasi modern untuk kemudahan kelola menu, meja, antrean dapur, pembayaran cepat, dan laporan shift otomatis.</p>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="md:w-1/2 p-12 flex flex-col justify-center bg-white">
            <h3 class="text-2xl font-bold text-coffee-dark mb-1">Masuk Aplikasi</h3>
            <p class="text-sm text-coffee-light mb-8">Masukkan email dan password untuk masuk ke aplikasi.</p>

            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center gap-3 text-xs font-semibold">
                    <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl flex items-center gap-3 text-xs font-semibold">
                    <svg class="w-4 h-4 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Email</label>
                    <input 
                        type="email" 
                        name="email" 
                        id="email" 
                        required 
                        class="w-full px-4 py-3 rounded-xl border border-coffee-latte focus:outline-none focus:ring-2 focus:ring-coffee-light/50 focus:border-coffee-light text-sm font-medium transition"
                        placeholder="example@pos.com"
                    >
                </div>
                <div>
                    <label for="password" class="block text-xs font-bold text-coffee-medium uppercase tracking-wider mb-2">Password</label>
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        required 
                        class="w-full px-4 py-3 rounded-xl border border-coffee-latte focus:outline-none focus:ring-2 focus:ring-coffee-light/50 focus:border-coffee-light text-sm font-medium transition"
                        placeholder="••••••••"
                    >
                </div>

                <button 
                    type="submit" 
                    class="w-full py-3.5 px-4 bg-coffee-dark text-white rounded-xl font-semibold hover:bg-coffee-medium transition duration-200 shadow-md shadow-coffee-medium/10 hover:shadow-lg mt-2 cursor-pointer"
                >
                    Masuk
                </button>
            </form>
        </div>
    </div>

    <script>
        function quickFill(email) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = 'password';
        }
    </script>
</body>
</html>
