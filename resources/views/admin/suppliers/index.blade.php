@extends('layouts.admin')

@section('title', 'Supplier — KasirKu')
@section('heading', 'Supplier')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <p class="page-subtitle">Daftar pemasok barang untuk toko Anda.</p>
        <a href="{{ route('admin.supplier.create') }}" class="btn btn-primary">
            <x-icon name="plus" class="size-4" />
            Tambah Supplier
        </a>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-base">
                <thead>
                    <tr>
                        <th class="w-16">No</th>
                        <th>Nama</th>
                        <th>Telepon</th>
                        <th>Alamat</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($suppliers as $supplier)
                        <tr>
                            <td class="text-slate-400">{{ $suppliers->firstItem() + $loop->index }}</td>
                            <td class="font-medium text-slate-900">{{ $supplier->name }}</td>
                            <td class="text-slate-600">{{ $supplier->phone ?? '—' }}</td>
                            <td class="max-w-xs truncate text-slate-500">{{ $supplier->address ?? '—' }}</td>
                            <td class="text-right">
                                <div class="inline-flex items-center gap-1">
                                    <a href="{{ route('admin.supplier.edit', $supplier) }}" class="btn btn-ghost btn-sm">Edit</a>
                                    <form method="POST" action="{{ route('admin.supplier.destroy', $supplier) }}"
                                          onsubmit="return confirm('Hapus supplier {{ $supplier->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-sm text-rose-600 hover:text-rose-700">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-state">Belum ada supplier.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $suppliers->links() }}
    </div>
@endsection
