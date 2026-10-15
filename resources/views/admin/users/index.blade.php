@extends('layouts.admin')

@section('title', 'Kelola Kasir — KasirKu')
@section('heading', 'Kelola Kasir')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <p class="page-subtitle">Akun kasir yang bisa login ke halaman POS.</p>
        <a href="{{ route('admin.kasir.create') }}" class="btn btn-primary">
            <x-icon name="plus" class="size-4" />
            Tambah Kasir
        </a>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-base">
                <thead>
                    <tr>
                        <th class="w-16">No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th class="text-right">Transaksi</th>
                        <th>Bergabung</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $kasir)
                        <tr>
                            <td class="text-ink-500">{{ $users->firstItem() + $loop->index }}</td>
                            <td class="font-medium">
                                <a href="{{ route('admin.kasir.show', $kasir) }}" class="text-slate-900 hover:text-accent-600">
                                    {{ $kasir->name }}
                                </a>
                            </td>
                            <td>{{ $kasir->email }}</td>
                            <td class="text-right">{{ $kasir->sales_count }}</td>
                            <td class="whitespace-nowrap text-slate-500">{{ $kasir->created_at?->format('d/m/Y') }}</td>
                            <td class="text-right">
                                <div class="inline-flex items-center gap-1">
                                    <a href="{{ route('admin.kasir.edit', $kasir) }}" class="btn btn-ghost btn-sm">Edit</a>
                                    <form method="POST" action="{{ route('admin.kasir.destroy', $kasir) }}"
                                          data-confirm="Apakah Anda yakin menghapus akun kasir {{ $kasir->name }}? Akun dengan riwayat transaksi tidak bisa dihapus. Tindakan ini tidak bisa dibatalkan."
                                          onsubmit="return confirm(this.dataset.confirm)">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-sm text-rose-600 hover:text-rose-700">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">Belum ada akun kasir. Buat akun pertama dengan tombol "Tambah Kasir".</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $users->links() }}
    </div>
@endsection
