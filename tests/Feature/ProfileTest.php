<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_dialihkan_ke_login(): void
    {
        $this->get('/profil')->assertRedirect('/login');
    }

    public function test_kasir_bisa_update_profil(): void
    {
        $kasir = User::factory()->create(['name' => 'Nama Lama']);

        $this->actingAs($kasir)->put('/profil', [
            'name' => 'Nama Baru',
            'email' => $kasir->email,
        ])->assertRedirect('/profil');

        $this->assertDatabaseHas('users', ['id' => $kasir->id, 'name' => 'Nama Baru']);
        $this->assertTrue($kasir->fresh()->is($kasir), 'Role tidak berubah.');
    }

    public function test_admin_bisa_update_profil(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->put('/profil', [
            'name' => 'Admin Baru',
            'email' => 'admin.baru@gmail.com',
        ])->assertRedirect('/profil');

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'name' => 'Admin Baru', 'email' => 'admin.baru@gmail.com']);
        $this->assertSame('admin', $admin->fresh()->role, 'Role tidak bisa lewat form profil.');
    }

    public function test_update_tanpa_password_tidak_mengubah_password(): void
    {
        $kasir = User::factory()->create();

        $this->actingAs($kasir)->put('/profil', [
            'name' => $kasir->name,
            'email' => $kasir->email,
        ])->assertRedirect('/profil');

        $this->assertTrue(Hash::check('password', $kasir->fresh()->password));
    }

    public function test_ganti_password_wajib_password_saat_ini(): void
    {
        $kasir = User::factory()->create();

        // Password saat ini salah
        $this->actingAs($kasir)->put('/profil', [
            'name' => $kasir->name,
            'email' => $kasir->email,
            'password' => 'barusaja123',
            'password_confirmation' => 'barusaja123',
            'current_password' => 'salah-banget',
        ])->assertSessionHasErrors('current_password');

        // Password saat ini kosong
        $this->actingAs($kasir)->put('/profil', [
            'name' => $kasir->name,
            'email' => $kasir->email,
            'password' => 'barusaja123',
            'password_confirmation' => 'barusaja123',
        ])->assertSessionHasErrors('current_password');

        $this->assertFalse(Hash::check('barusaja123', $kasir->fresh()->password), 'Password tidak boleh berubah saat verifikasi gagal.');
    }

    public function test_ganti_password_berhasil_dengan_password_saat_ini(): void
    {
        $kasir = User::factory()->create();

        $this->actingAs($kasir)->put('/profil', [
            'name' => $kasir->name,
            'email' => $kasir->email,
            'password' => 'barusaja123',
            'password_confirmation' => 'barusaja123',
            'current_password' => 'password',
        ])->assertRedirect('/profil');

        $this->assertTrue(Hash::check('barusaja123', $kasir->fresh()->password));
    }

    public function test_email_tidak_boleh_dipakai_user_lain(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        $this->actingAs($a)->put('/profil', [
            'name' => $a->name,
            'email' => $b->email,
        ])->assertSessionHasErrors('email');
    }
}
