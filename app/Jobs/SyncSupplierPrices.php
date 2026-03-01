<?php

namespace App\Jobs;

use App\Models\Supplier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class SyncSupplierPrices implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $supplierId;

    public function __construct(int $supplierId)
    {
        $this->supplierId = $supplierId;
    }

    public function handle(): void
    {
        $supplier = Supplier::find($this->supplierId);
        if (!$supplier) {
            return;
        }

        // 1. скачати
        Artisan::call('prices:fetch', [
            '--supplier' => $supplier->id,
        ]);

        // 2. імпорт
        (new ImportSupplierPrices($supplier->id))->handle();
    }
}
