<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;

class FixMenuImagesSeeder extends Seeder
{
    public function run(): void
    {
        $images = [
            1 => 'https://images.unsplash.com/photo-1510707577719-fa7c184c7972?q=80&w=400&auto=format&fit=crop',
            2 => 'https://images.unsplash.com/photo-1551030173-1d2056c44ef9?q=80&w=400&auto=format&fit=crop',
            4 => 'https://images.unsplash.com/photo-1551893086-c0a545f442ee?q=80&w=400&auto=format&fit=crop',
            7 => 'https://images.unsplash.com/photo-1544787210-2213d4b39920?q=80&w=400&auto=format&fit=crop',
            13 => 'https://images.unsplash.com/photo-1516054817441-2696614467b7?q=80&w=400&auto=format&fit=crop',
        ];

        foreach ($images as $id => $url) {
            Menu::where('id_menu', $id)->update(['foto' => $url]);
        }
    }
}
