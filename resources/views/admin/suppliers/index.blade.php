@extends('layouts.admin')

@section('title', 'Supplier — KasirKu')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold">Supplier</h1>
        <a href="{{ route('admin.supplier.create') }}"
           class="rounded-lg bg-indigo-600 hover:bg-indigo-700 px-4 py-2 text-sm font-medium text-white">
            Tambah Supplier
        </a>
    </div>

    <div class="overflow-x-auto rounded-xl bg-white shadow">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr>
                    <th class="px-4 py-3 font-medium">No</th>
                    <th class="px-4 py-3 font-medium">Nama</th>
                    <th class="px-4 py-3 font-medium">Telepon</th>
                    <th class="px-4 py-3 font-medium">Alamat</th>
                    <th class="px-4 py-3 font-medium text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($suppliers as $supplier)
                    <tr>
                        <td class="px-4 py-3 text-gray-500">{{ $suppliers->firstItem() + $loop->index }}</td>
                        <td class="px-4 py-3">{{ $supplier->name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $supplier->phone ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $supplier->address ?? '-' }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex items-center gap-3">
                                <a href="{{ route('admin.supplier.edit', $supplier) }}" class="text-indigo-600 hover:underline">Edit</a>
                                <form method="POST" action="{{ route('admin.supplier.destroy', $supplier) }}"
                                      onsubmit="return confirm('Hapus supplier {{ $supplier->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-400">Belum ada supplier.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $suppliers->links() }}
    </div>
@endsection
