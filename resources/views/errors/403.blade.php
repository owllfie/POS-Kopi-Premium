<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak - POS Restoran</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-coffee-cream flex items-center justify-center min-h-screen p-4">
    <div class="max-w-md w-full bg-white rounded-2xl border border-coffee-latte shadow-xl p-8 text-center coffee-card">
        <!-- Coffee cup icon with warning indicator -->
        <div class="relative w-24 h-24 mx-auto mb-6 flex items-center justify-center bg-amber-50 rounded-full border border-amber-100">
            <svg class="w-12 h-12 text-coffee-light" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"></path>
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-coffee-dark mb-2">Akses Ditolak (403)</h1>
        
        <p class="text-coffee-medium mb-6 text-sm">
            {{ $message ?? 'Anda tidak memiliki hak akses untuk membuka halaman ini.' }}
        </p>

        <div class="space-y-3">
            <a href="{{ route('dashboard') }}" class="block w-full py-2.5 px-4 bg-coffee-dark text-white rounded-xl font-semibold hover:bg-coffee-medium transition duration-200 shadow-md">
                Kembali ke Dashboard
            </a>
            
            <a href="{{ route('login') }}" class="block w-full py-2.5 px-4 border border-coffee-light text-coffee-dark rounded-xl font-semibold hover:bg-coffee-latte transition duration-200">
                Ganti Akun / Login Ulang
            </a>
        </div>
    </div>
</body>
</html>
