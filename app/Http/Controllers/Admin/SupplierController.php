<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;

class SupplierController extends Controller
{
    /**
     * Daftar supplier (tabel + pagination).
     */
    public function index()
    {
        $suppliers = Supplier::orderBy('name')->paginate(10);

        return view('admin.suppliers.index', compact('suppliers'));
    }

    /**
     * Form tambah supplier.
     */
    public function create()
    {
        return view('admin.suppliers.create');
    }

    /**
     * Simpan supplier baru.
     */
    public function store(StoreSupplierRequest $request)
    {
        Supplier::create($request->validated());

        return redirect()->route('admin.supplier.index')->with('success', 'Supplier berhasil ditambahkan.');
    }

    /**
     * Form edit supplier.
     */
    public function edit(Supplier $supplier)
    {
        return view('admin.suppliers.edit', compact('supplier'));
    }

    /**
     * Update supplier.
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        $supplier->update($request->validated());

        return redirect()->route('admin.supplier.index')->with('success', 'Supplier berhasil diperbarui.');
    }

    /**
     * Hapus supplier. (Produk terkait otomatis supplier_id = null via FK nullOnDelete.)
     */
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('admin.supplier.index')->with('success', 'Supplier berhasil dihapus.');
    }
}
