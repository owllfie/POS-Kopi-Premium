<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\BahanAlat;
use App\Models\Aksess;

class FinanceInventoryTest extends TestCase
{
    /**
     * Test access permissions for the bahan-alat and properti routes.
     */
    public function test_role_based_access_controls(): void
    {
        // 1. Superadmin (user 1) should access both
        $response = $this->withSession(['simulated_user_id' => 1])->get('/bahan-alat');
        $response->assertStatus(200);

        $response = $this->withSession(['simulated_user_id' => 1])->get('/properti');
        $response->assertStatus(200);

        // 2. Admin (user 2) should access both
        $response = $this->withSession(['simulated_user_id' => 2])->get('/bahan-alat');
        $response->assertStatus(200);

        $response = $this->withSession(['simulated_user_id' => 2])->get('/properti');
        $response->assertStatus(200);

        // 3. Cashier (user 4) should get 403 Forbidden
        $response = $this->withSession(['simulated_user_id' => 4])->get('/bahan-alat');
        $response->assertStatus(403);

        $response = $this->withSession(['simulated_user_id' => 4])->get('/properti');
        $response->assertStatus(403);
    }

    /**
     * Test that properti items are excluded from the bahan-alat index and vice versa.
     */
    public function test_correct_item_type_separation(): void
    {
        // Create test items with "AAA" prefix to ensure they sort to the first page of paginated results
        $material = BahanAlat::create([
            'nama_item' => 'AAA Test Cooking Material XY',
            'tipe' => 'bahan',
            'kategori' => 'Bahan',
            'stok' => 10.0,
            'satuan' => 'kg',
            'harga_estimasi' => 5000,
        ]);

        $property = BahanAlat::create([
            'nama_item' => 'AAA Test Utility Property YZ',
            'tipe' => 'properti',
            'kategori' => 'Utilitas',
            'stok' => 1.0,
            'satuan' => 'bulan',
            'harga_estimasi' => 120000,
        ]);

        $tool = BahanAlat::create([
            'nama_item' => 'AAA Test Grinder Tool WX',
            'tipe' => 'alat',
            'kategori' => 'Peralatan',
            'stok' => 1.0,
            'satuan' => 'bulan',
            'harga_estimasi' => 500000,
        ]);

        // Fetch /bahan-alat with Superadmin (Should only see bahan)
        $response = $this->withSession(['simulated_user_id' => 1])->get('/bahan-alat');
        $response->assertSee('AAA Test Cooking Material XY');
        $response->assertDontSee('AAA Test Utility Property YZ');
        $response->assertDontSee('AAA Test Grinder Tool WX');

        // Fetch /properti with Superadmin (Should see properti AND alat)
        $response = $this->withSession(['simulated_user_id' => 1])->get('/properti');
        $response->assertSee('AAA Test Utility Property YZ');
        $response->assertSee('AAA Test Grinder Tool WX');
        $response->assertDontSee('AAA Test Cooking Material XY');

        // Clean up
        $material->forceDelete();
        $property->forceDelete();
        $tool->forceDelete();
    }

    /**
     * Test CRUD operations on cooking materials & tools.
     */
    public function test_bahan_alat_crud(): void
    {
        // Store
        $response = $this->withSession(['simulated_user_id' => 1])->post('/bahan-alat/store', [
            'nama_item' => 'New Test Spice',
            'kategori' => 'Rempah',
            'stok' => 5.5,
            'satuan' => 'gram',
            'harga_estimasi' => 3000,
            'keterangan' => 'Special test ingredient',
        ]);
        $response->assertStatus(302); // Redirect back

        $item = BahanAlat::where('nama_item', 'New Test Spice')->first();
        $this->assertNotNull($item);
        $this->assertEquals(5.5, $item->stok);

        // Update
        $response = $this->withSession(['simulated_user_id' => 1])->post("/bahan-alat/update/{$item->id_item}", [
            'nama_item' => 'Updated Test Spice',
            'kategori' => 'Rempah',
            'stok' => 10.0,
            'satuan' => 'gram',
            'harga_estimasi' => 3500,
            'keterangan' => 'Updated info',
        ]);
        $response->assertStatus(302);

        $item->refresh();
        $this->assertEquals('Updated Test Spice', $item->nama_item);
        $this->assertEquals(10.0, $item->stok);

        // Delete
        $response = $this->withSession(['simulated_user_id' => 1])->post("/bahan-alat/delete/{$item->id_item}");
        $response->assertStatus(302);

        $item = BahanAlat::where('nama_item', 'Updated Test Spice')->first();
        $this->assertNull($item); // Soft-deleted
    }

    /**
     * Test CRUD operations on property items.
     */
    public function test_properti_crud(): void
    {
        // Store
        $response = $this->withSession(['simulated_user_id' => 1])->post('/properti/store', [
            'nama_item' => 'New Monthly Wifi Fee',
            'tipe' => 'properti',
            'kategori' => 'Utilitas',
            'harga_estimasi' => 350000,
            'keterangan' => 'ISP test billing',
        ]);
        $response->assertStatus(302);

        $item = BahanAlat::where('nama_item', 'New Monthly Wifi Fee')->first();
        $this->assertNotNull($item);
        $this->assertEquals('properti', $item->tipe);
        $this->assertEquals(1.0, $item->stok);
        $this->assertEquals('bulan', $item->satuan);
        $this->assertEquals(350000, $item->harga_estimasi);

        // Update
        $response = $this->withSession(['simulated_user_id' => 1])->post("/properti/update/{$item->id_item}", [
            'nama_item' => 'Updated Wifi Fee',
            'tipe' => 'properti',
            'kategori' => 'Utilitas',
            'harga_estimasi' => 400000,
            'keterangan' => 'Updated ISP rate',
        ]);
        $response->assertStatus(302);

        $item->refresh();
        $this->assertEquals('Updated Wifi Fee', $item->nama_item);
        $this->assertEquals(400000, $item->harga_estimasi);

        // Delete
        $response = $this->withSession(['simulated_user_id' => 1])->post("/properti/delete/{$item->id_item}");
        $response->assertStatus(302);

        $item = BahanAlat::where('nama_item', 'Updated Wifi Fee')->first();
        $this->assertNull($item); // Soft-deleted
    }
}
