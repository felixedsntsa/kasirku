<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use App\Services\SaleService;
use App\Services\StockService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // --- Akun demo (password: password) ---
        $admin = User::factory()->admin()->create([
            'name' => 'Admin Demo',
            'email' => 'admin@demo.com',
        ]);

        $kasir = User::factory()->create([
            'name' => 'Kasir Demo',
            'email' => 'kasir@demo.com',
        ]);

        // --- Master data ---
        $categories = collect(['Minuman', 'Makanan Ringan', 'Sembako', 'Perawatan Diri', 'Alat Tulis'])
            ->map(fn (string $name) => Category::create(['name' => $name]));

        $suppliers = collect(['CV Maju Jaya', 'PT Sumber Pangan', 'UD Bersama', 'CV Sentosa', 'PT Global Niaga'])
            ->map(fn (string $name) => Supplier::create([
                'name' => $name,
                'phone' => fake()->numerify('08##########'),
                'address' => fake()->address(),
            ]));

        // --- 50 produk (semua lewat factory; stok awal dicatat via StockService) ---
        $stockService = app(StockService::class);

        $products = Product::factory(50)->create([
            'category_id' => fn () => $categories->random()->id,
            'supplier_id' => fn () => fake()->boolean(80) ? $suppliers->random()->id : null,
        ])->each(function (Product $product) use ($stockService) {
            // products.stock dibuat 0 oleh factory? Tidak — factory menulis stok langsung.
            // Karena ini seeder demo, kita samakan jejak audit-nya: reset ke 0 + recordIn.
            $stock = $product->stock;
            $product->update(['stock' => 0]);

            if ($stock > 0) {
                $stockService->recordIn($product, $stock, 'Stok awal (seed)');
            }
        });

        // Beberapa produk stok menipis untuk memicu peringatan.
        Product::factory(4)->lowStock()->create([
            'category_id' => fn () => $categories->random()->id,
        ]);

        // --- 30 hari transaksi via SaleService (stok & movement konsisten) ---
        $saleService = app(SaleService::class);

        // Query ulang dari DB (instance di $products masih menyimpan stock=0 di memory).
        $sellable = Product::where('stock', '>', 0)->get();

        if ($sellable->isNotEmpty()) {
            foreach (range(1, 30) as $daysAgo) {
                $trxCount = fake()->numberBetween(1, 4);

                foreach (range(1, $trxCount) as $i) {
                    $lines = fake()->numberBetween(1, 3);

                    $cart = collect(range(1, $lines))
                        ->map(fn () => $sellable->random())
                        // Batasi qty agar tidak melebihi stok tersedia.
                        ->map(fn (Product $p) => [
                            'product_id' => $p->id,
                            'qty' => fake()->numberBetween(1, min(3, max(1, $p->fresh()->stock))),
                        ])
                        ->unique('product_id')
                        ->values()
                        ->all();

                    if ($cart === []) {
                        continue;
                    }

                    $subtotal = collect($cart)->sum(
                        fn (array $line) => (float) Product::find($line['product_id'])->sell_price * $line['qty']
                    );

                    try {
                        $sale = $saleService->store($cart, [
                            'paid' => $subtotal,
                            'payment_method' => fake()->randomElement(['cash', 'qris', 'debit']),
                        ], fake()->boolean(30) ? $kasir : $admin);

                        // Mundurkan waktu transaksi agar tersebar 30 hari terakhir.
                        // created_at tidak fillable -> set langsung + save().
                        Sale::withoutTimestamps(function () use ($sale, $daysAgo) {
                            $sale->created_at = now()->subDays($daysAgo)->setTime(
                                fake()->numberBetween(8, 20),
                                fake()->numberBetween(0, 59),
                            );
                            $sale->save();
                        });
                    } catch (\Throwable) {
                        // Lewati bila stok keburu habis — tidak menggagalkan seeder.
                        continue;
                    }
                }
            }
        }
    }
}
