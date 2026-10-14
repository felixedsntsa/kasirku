<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * Daftar kategori (tabel + pagination).
     */
    public function index()
    {
        $categories = Category::orderBy('name')->paginate(10);

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Form tambah kategori.
     */
    public function create()
    {
        return view('admin.categories.create');
    }

    /**
     * Simpan kategori baru.
     */
    public function store(StoreCategoryRequest $request)
    {
        Category::create($request->validated());

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Form edit kategori.
     */
    public function edit(Category $kategori)
    {
        return view('admin.categories.edit', compact('kategori'));
    }

    /**
     * Update kategori.
     */
    public function update(UpdateCategoryRequest $request, Category $kategori)
    {
        $kategori->update($request->validated());

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Hapus kategori. Ditolak jika masih dipakai produk (FK restrict).
     */
    public function destroy(Category $kategori)
    {
        if ($kategori->products()->exists()) {
            return redirect()->route('admin.kategori.index')
                ->with('error', 'Kategori masih dipakai produk — pindahkan produknya dulu.');
        }

        $kategori->delete();

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil dihapus.');
    }
}
