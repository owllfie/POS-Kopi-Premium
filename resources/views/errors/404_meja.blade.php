<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meja Tidak Ditemukan - POS Restoran</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-coffee-cream flex items-center justify-center min-h-screen p-4">
    <div class="max-w-md w-full bg-white rounded-2xl border border-coffee-latte shadow-xl p-8 text-center coffee-card">
        <div class="relative w-20 h-20 mx-auto mb-6 flex items-center justify-center bg-red-50 rounded-full border border-red-100">
            <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>

        <h1 class="text-2xl font-bold text-coffee-dark mb-2">QR Code Tidak Valid</h1>
        
        <p class="text-coffee-medium mb-6 text-sm">
            {{ $message ?? 'Meja yang Anda tuju tidak terdaftar di sistem kami.' }}
        </p>

        <a href="{{ route('login') }}" class="block w-full py-2.5 px-4 bg-coffee-dark text-white rounded-xl font-semibold hover:bg-coffee-medium transition duration-200 shadow-md">
            Halaman Login Staff
        </a>
    </div>
</body>
</html>
