@extends('layouts.admin')

@section('title', 'Edit Supplier — KasirKu')

@section('content')
    <h1 class="text-xl font-semibold mb-6">Edit Supplier</h1>

    <div class="max-w-md rounded-xl bg-white shadow p-6">
        <form method="POST" action="{{ route('admin.supplier.update', $supplier) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                <input id="name" type="text" name="name" value="{{ old('name', $supplier->name) }}" required autofocus
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Telepon</label>
                <input id="phone" type="text" name="phone" value="{{ old('phone', $supplier->phone) }}"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                <input id="address" type="text" name="address" value="{{ old('address', $supplier->address) }}"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="flex items-center gap-3">
                <button type="submit"
                        class="rounded-lg bg-indigo-600 hover:bg-indigo-700 px-4 py-2 text-sm font-medium text-white">
                    Simpan
                </button>
                <a href="{{ route('admin.supplier.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Batal</a>
            </div>
        </form>
    </div>
@endsection
