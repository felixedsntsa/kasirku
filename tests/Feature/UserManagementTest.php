<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    // --- Root tanpa landing page ---

    public function test_root_guest_dialihkan_ke_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_root_admin_dialihkan_ke_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/')->assertRedirect(route('admin.dashboard'));
    }

    public function test_root_kasir_dialihkan_ke_pos(): void
    {
        $kasir = User::factory()->create();

        $this->actingAs($kasir)->get('/')->assertRedirect(route('kasir.home'));
    }

    // --- Akses manajemen kasir ---

    public function test_kasir_tidak_bisa_akses_kelola_kasir(): void
    {
        $kasir = User::factory()->create();

        $this->actingAs($kasir)->get('/admin/kasir')->assertForbidden();
        $this->actingAs($kasir)->get('/admin/kasir/create')->assertForbidden();
    }

    public function test_admin_bisa_crud_kasir(): void
    {
        $admin = User::factory()->admin()->create();

        // Index
        $this->actingAs($admin)->get('/admin/kasir')->assertOk();

        // Create
        $this->actingAs($admin)->post('/admin/kasir', [
            'name' => 'Kasir Baru',
            'email' => 'kasirbaru@gmail.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertRedirect('/admin/kasir');

        $this->assertDatabaseHas('users', [
            'email' => 'kasirbaru@gmail.com',
            'role' => 'kasir',
        ]);

        $kasir = User::where('email', 'kasirbaru@gmail.com')->firstOrFail();

        // Edit — tanpa password (password lama tetap berlaku)
        $this->actingAs($admin)->put("/admin/kasir/{$kasir->id}", [
            'name' => 'Kasir Diubah',
            'email' => 'kasirbaru@gmail.com',
        ])->assertRedirect('/admin/kasir');

        $this->assertDatabaseHas('users', ['id' => $kasir->id, 'name' => 'Kasir Diubah']);
        $this->assertTrue(Hash::check('rahasia123', $kasir->fresh()->password));

        // Delete
        $this->actingAs($admin)->delete("/admin/kasir/{$kasir->id}")->assertRedirect('/admin/kasir');
        $this->assertDatabaseMissing('users', ['email' => 'kasirbaru@gmail.com']);
    }

    public function test_validasi_form_kasir(): void
    {
        $admin = User::factory()->admin()->create();
        $existing = User::factory()->create();

        $this->actingAs($admin)->post('/admin/kasir', [
            'name' => '',
            'email' => 'bukan-email',
            'password' => 'pendek',
            'password_confirmation' => 'beda',
        ])->assertSessionHasErrors(['name', 'email', 'password']);

        // Email duplikat
        $this->actingAs($admin)->post('/admin/kasir', [
            'name' => 'Kasir',
            'email' => $existing->email,
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertSessionHasErrors('email');
    }

    public function test_kasir_dengan_transaksi_tidak_bisa_dihapus(): void
    {
        $admin = User::factory()->admin()->create();
        $kasir = User::factory()->create();
        Sale::factory()->create(['user_id' => $kasir->id]);

        $this->actingAs($admin)->delete("/admin/kasir/{$kasir->id}")->assertRedirect('/admin/kasir');

        // Jejak audit terjaga — akun tetap ada.
        $this->assertDatabaseHas('users', ['id' => $kasir->id]);
    }

    public function test_admin_bisa_lihat_detail_kasir(): void
    {
        $admin = User::factory()->admin()->create();
        $kasir = User::factory()->create(['name' => 'Kasir Detail']);
        Sale::factory()->create(['user_id' => $kasir->id, 'status' => Sale::STATUS_PAID]);

        $this->actingAs($admin)->get("/admin/kasir/{$kasir->id}")
            ->assertOk()
            ->assertSee('Kasir Detail')
            ->assertSee('Transaksi Lunas');
    }

    public function test_kasir_tidak_bisa_lihat_detail_kasir(): void
    {
        $kasir = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAs($kasir)->get("/admin/kasir/{$target->id}")->assertForbidden();
    }

    public function test_admin_tidak_bisa_kelola_akun_admin_lain_via_url(): void
    {
        $admin = User::factory()->admin()->create();
        $adminLain = User::factory()->admin()->create();

        // Manajemen ini khusus akun kasir — akun admin di luar cakupan.
        $this->actingAs($admin)->get("/admin/kasir/{$adminLain->id}")->assertNotFound();
        $this->actingAs($admin)->get("/admin/kasir/{$adminLain->id}/edit")->assertNotFound();
        $this->actingAs($admin)->put("/admin/kasir/{$adminLain->id}", [
            'name' => 'Diubah',
            'email' => $adminLain->email,
        ])->assertNotFound();
        $this->actingAs($admin)->delete("/admin/kasir/{$adminLain->id}")->assertNotFound();

        $this->assertSame('admin', $adminLain->fresh()->role);
    }
}
