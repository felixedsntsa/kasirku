@extends('layouts.admin')

@section('title', 'Kategori — KasirKu')
@section('heading', 'Kategori')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <p class="page-subtitle">Kelompokkan produk ke dalam kategori.</p>
        <a href="{{ route('admin.kategori.create') }}" class="btn btn-primary">
            <x-icon name="plus" class="size-4" />
            Tambah Kategori
        </a>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-base">
                <thead>
                    <tr>
                        <th class="w-16">No</th>
                        <th>Nama</th>
                        <th>Dibuat</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td class="text-ink-500">{{ $categories->firstItem() + $loop->index }}</td>
                            <td class="font-medium text-slate-900">{{ $category->name }}</td>
                            <td class="text-slate-500">{{ $category->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="text-right">
                                <div class="inline-flex items-center gap-1">
                                    <a href="{{ route('admin.kategori.edit', $category) }}" class="btn btn-ghost btn-sm">Edit</a>
                                    <form method="POST" action="{{ route('admin.kategori.destroy', $category) }}"
                                          onsubmit="return confirm('Hapus kategori {{ $category->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-sm text-rose-600 hover:text-rose-700">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty-state">Belum ada kategori.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $categories->links() }}
    </div>
@endsection
