<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;

class MenuImageSeeder extends Seeder
{
    public function run(): void
    {
        $images = [
            1 => 'https://images.unsplash.com/photo-1510707577719-fa7c184c7972?q=80&w=400&auto=format&fit=crop', // Espresso
            2 => 'https://images.unsplash.com/photo-1551030173-1d2056c44ef9?q=80&w=400&auto=format&fit=crop', // Americano
            3 => 'https://images.unsplash.com/photo-1572442388796-11668a67e53d?q=80&w=400&auto=format&fit=crop', // Cappuccino
            4 => 'https://images.unsplash.com/photo-1551893086-c0a545f442ee?q=80&w=400&auto=format&fit=crop', // Hazelnut Latte
            5 => 'https://images.unsplash.com/photo-1485808191679-5f86510681a2?q=80&w=400&auto=format&fit=crop', // Caramel Macchiato
            6 => 'https://images.unsplash.com/photo-1515823064-d6e0c04616a7?q=80&w=400&auto=format&fit=crop', // Matcha Latte
            7 => 'https://images.unsplash.com/photo-1544787210-2213d4b39920?q=80&w=400&auto=format&fit=crop', // Chocolate
            8 => 'https://images.unsplash.com/photo-1576092768241-dec231879fc3?q=80&w=400&auto=format&fit=crop', // Earl Grey
            9 => 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?q=80&w=400&auto=format&fit=crop', // Lemon Tea
            10 => 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?q=80&w=400&auto=format&fit=crop', // Croissant
            11 => 'https://images.unsplash.com/photo-1530610476181-d83430b64dcd?q=80&w=400&auto=format&fit=crop', // Pain au Chocolat
            12 => 'https://images.unsplash.com/photo-1509365465985-25d11c17e812?q=80&w=400&auto=format&fit=crop', // Cinnamon Roll
            13 => 'https://images.unsplash.com/photo-1516054817441-2696614467b7?q=80&w=400&auto=format&fit=crop', // Almond Croissant
            14 => 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?q=80&w=400&auto=format&fit=crop', // Tiramisu
            15 => 'https://images.unsplash.com/photo-1533134242443-d4fd215305ad?q=80&w=400&auto=format&fit=crop', // Cheesecake
            16 => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=400&auto=format&fit=crop'  // Fudge Cake
        ];

        foreach ($images as $id => $url) {
            Menu::where('id_menu', $id)->update(['foto' => $url]);
        }
    }
}
