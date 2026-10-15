<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKasirRequest;
use App\Http\Requests\UpdateKasirRequest;
use App\Models\Sale;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Daftar akun kasir (role kasir saja — akun admin dikelola lewat Profil).
     */
    public function index()
    {
        $users = User::where('role', 'kasir')
            ->withCount('sales')
            ->orderBy('name')
            ->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Detail kasir: info akun + statistik penjualan + 10 transaksi terakhir.
     */
    public function show(User $user)
    {
        $this->guardOnlyKasir($user);

        $stats = Sale::where('user_id', $user->id)
            ->where('status', Sale::STATUS_PAID)
            ->selectRaw('COUNT(*) as transactions, COALESCE(SUM(total), 0) as revenue')
            ->first();

        $sales = Sale::withCount('items')
            ->where('user_id', $user->id)
            ->latest('id')
            ->limit(10)
            ->get();

        return view('admin.users.show', compact('user', 'stats', 'sales'));
    }

    /**
     * Form tambah kasir.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Simpan kasir baru — role selalu 'kasir'.
     */
    public function store(StoreKasirRequest $request)
    {
        User::create([
            ...$request->validated(),
            'role' => 'kasir',
        ]);

        return redirect()->route('admin.kasir.index')->with('success', 'Akun kasir berhasil ditambahkan.');
    }

    /**
     * Form edit kasir.
     */
    public function edit(User $user)
    {
        $this->guardOnlyKasir($user);

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update kasir — password dikosongkan bila tidak diganti.
     */
    public function update(UpdateKasirRequest $request, User $user)
    {
        $this->guardOnlyKasir($user);

        $data = $request->validated();

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('admin.kasir.index')->with('success', 'Akun kasir berhasil diperbarui.');
    }

    /**
     * Hapus kasir — ditolak bila punya riwayat transaksi (jejak audit).
     */
    public function destroy(User $user)
    {
        $this->guardOnlyKasir($user);

        if ($user->sales()->exists()) {
            return redirect()->route('admin.kasir.index')
                ->with('error', 'Akun memiliki riwayat transaksi — penghapusan diizinkan agar jejak audit tetap utuh.');
        }

        $user->delete();

        return redirect()->route('admin.kasir.index')->with('success', 'Akun kasir berhasil dihapus.');
    }

    /**
     * Manajemen ini hanya untuk akun kasir; akun admin di luar cakupan (404).
     */
    private function guardOnlyKasir(User $user): void
    {
        abort_if($user->isAdmin(), 404);
    }
}
