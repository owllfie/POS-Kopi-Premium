<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Aksess;

class AksesMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Load global settings for locale
        $settingsPath = storage_path('app/settings.json');
        if (file_exists($settingsPath)) {
            $settings = json_decode(file_get_contents($settingsPath), true);
            if (!empty($settings['bahasa'])) {
                app()->setLocale($settings['bahasa']);
            }
        }

        $path = $request->path();

        // 1. Always allow auth and public routes
        if ($request->is('login') || $request->is('logout') || $request->is('menu/table-*') || $request->is('simulate-role/*')) {
            $response = $next($request);
            return $this->translateResponse($response);
        }

        // 2. Resolve active user (auth or simulated)
        $user = null;
        if (session()->has('simulated_user_id')) {
            $user = User::find(session('simulated_user_id'));
        }
        
        if (!$user && Auth::check()) {
            $user = Auth::user();
        }

        if (!$user) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        // Share active user in session if not set, to keep simulation working
        if (!session()->has('simulated_user_id')) {
            session(['simulated_user_id' => $user->id_user]);
        }

        // 3. Superadmin has access to everything
        if ($user->role->role === 'superadmin') {
            $response = $next($request);
            return $this->translateResponse($response);
        }

        // 4. Map request path to module
        $module = null;
        if ($request->is('dashboard') || $request->is('/')) {
            $module = 'dashboard';
        } elseif ($request->is('pesanan/*/bayar') || $request->is('pesanan/bayar/*')) {
            $module = 'bayar';
        } elseif ($request->is('pesanan') || $request->is('pesanan/*')) {
            $module = 'pesanan';
        } elseif ($request->is('laporan') || $request->is('laporan/*')) {
            $module = 'laporan';
        } elseif ($request->is('transaksi') || $request->is('transaksi/*')) {
            $module = 'transaksi';
        } elseif ($request->is('users') || $request->is('users/*')) {
            $module = 'users';
        } elseif ($request->is('karyawan') || $request->is('karyawan/*')) {
            $module = 'karyawan';
        } elseif ($request->is('menu') || $request->is('menu/*')) {
            $module = 'menu';
        } elseif ($request->is('kategori') || $request->is('kategori/*')) {
            $module = 'kategori';
        } elseif ($request->is('meja') || $request->is('meja/*')) {
            $module = 'meja';
        } elseif ($request->is('shift') || $request->is('shift/*')) {
            $module = 'shift';
        } elseif ($request->is('akses') || $request->is('akses/*')) {
            $module = 'akses';
        } elseif ($request->is('log') || $request->is('log/*')) {
            $module = 'log';
        } elseif ($request->is('setting') || $request->is('setting/*')) {
            $module = 'setting';
        } elseif ($request->is('backup') || $request->is('backup/*')) {
            $module = 'backup';
        } elseif ($request->is('bahan-alat') || $request->is('bahan-alat/*')) {
            $module = 'bahan_alat';
        } elseif ($request->is('properti') || $request->is('properti/*')) {
            $module = 'properti';
        }

        if ($module) {
            // Check allowed state in database for this role and module
            $allowed = Aksess::where('id_role', $user->id_role)
                ->where('modul', $module)
                ->first();

            if (!$allowed || $allowed->allowed !== '1') {
                return response()->view('errors.403', [
                    'message' => "Anda (Role: " . strtoupper($user->role->role) . ") tidak memiliki akses ke modul " . strtoupper($module) . "."
                ], 403);
            }
        }

        $response = $next($request);
        return $this->translateResponse($response);
    }

    /**
     * Translates response content using the global dictionary if the locale is not Indonesian ('id').
     */
    private function translateResponse($response)
    {
        $locale = app()->getLocale();
        if ($locale !== 'id' && $response instanceof \Symfony\Component\HttpFoundation\Response && method_exists($response, 'getContent')) {
            $content = $response->getContent();
            $dictionaryPath = storage_path('app/translations.json');
            if (file_exists($dictionaryPath)) {
                $dictionary = json_decode(file_get_contents($dictionaryPath), true);
                if (isset($dictionary[$locale])) {
                    $translations = $dictionary[$locale];
                    // Automatically add HTML-encoded variations of dictionary keys to support symbols (e.g. ampersands or quotes)
                    foreach ($translations as $key => $val) {
                        if (str_contains($key, '&') || str_contains($key, "'") || str_contains($key, '"')) {
                            $htmlKey = htmlspecialchars($key, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                            if (!isset($translations[$htmlKey])) {
                                $translations[$htmlKey] = $val;
                            }
                        }
                    }
                    // Perform dynamic translation on the HTML response body
                    $content = strtr($content, $translations);
                    $response->setContent($content);
                }
            }
        }
        return $response;
    }
}
