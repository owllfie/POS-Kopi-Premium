<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    private function getActiveUser()
    {
        if (session()->has('simulated_user_id')) {
            return User::find(session('simulated_user_id'));
        }
        return Auth::user();
    }

    private function getSettings()
    {
        $path = storage_path('app/settings.json');
        $defaults = [
            'nama_restoran' => 'Kopi Premium',
            'pajak' => 10,
            'footer' => 'Terima kasih atas kunjungan Anda!',
            'logo' => null,
            'bahasa' => 'id',
        ];
        if (file_exists($path)) {
            $saved = json_decode(file_get_contents($path), true);
            return array_merge($defaults, is_array($saved) ? $saved : []);
        }
        return $defaults;
    }

    public function index()
    {
        $settings = $this->getSettings();
        return view('setting.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $superadmin = $this->getActiveUser();
        $validated = $request->validate([
            'nama_restoran' => 'required|string|max:255',
            'pajak' => 'required|numeric|min:0|max:100',
            'footer' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'bahasa' => 'required|string|in:id,en,ms,ja,zh',
        ]);

        $settings = $this->getSettings();
        $settings['nama_restoran'] = $validated['nama_restoran'];
        $settings['pajak'] = $validated['pajak'];
        $settings['footer'] = $validated['footer'];
        $settings['bahasa'] = $validated['bahasa'];

        if ($request->filled('cropped_image')) {
            $base64Image = $request->input('cropped_image');
            $filename = 'logo_' . time() . '.png';
            $path = public_path('uploads/' . $filename);
            
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image));
            file_put_contents($path, $imageData);

            if (!empty($settings['logo']) && file_exists(public_path($settings['logo']))) {
                @unlink(public_path($settings['logo']));
            }

            $settings['logo'] = 'uploads/' . $filename;
        } elseif ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads'), $filename);

            // Clean up old logo file if exists
            if (!empty($settings['logo']) && file_exists(public_path($settings['logo']))) {
                @unlink(public_path($settings['logo']));
            }

            $settings['logo'] = 'uploads/' . $filename;
        }

        $path = storage_path('app/settings.json');
        
        // Ensure folder exists
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, json_encode($settings, JSON_PRETTY_PRINT));

        ActivityLog::create([
            'id_user' => $superadmin->id_user,
            'aktivitas' => 'UPDATE_SETTINGS',
            'detail_aktivitas' => 'Superadmin updated global web configurations.',
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Pengaturan sistem berhasil disimpan.');
    }
}
