<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SupplierController;

Route::get('/', function () {
    return redirect()->route('suppliers.index');
});

Route::prefix('suppliers')->name('suppliers.')->group(function () {
    Route::get('/',              [SupplierController::class, 'index'])->name('index');
    Route::get('/create',        [SupplierController::class, 'create'])->name('create');
    Route::post('/',             [SupplierController::class, 'store'])->name('store');
    Route::get('/{supplier}/edit',[SupplierController::class, 'edit'])->name('edit');
    Route::put('/{supplier}',    [SupplierController::class, 'update'])->name('update');
    Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->name('destroy');

    // дії з прайсами
    Route::post('/{supplier}/fetch',  [SupplierController::class, 'fetch'])->name('fetch');
    Route::post('/{supplier}/import', [SupplierController::class, 'import'])->name('import');
    Route::post('/{supplier}/sync',   [SupplierController::class, 'sync'])->name('sync');

    Route::get('/{supplier}/preview', [SupplierController::class, 'preview'])->name('preview');

    // AJAX для прогрес-бару
    Route::get('/statuses', [SupplierController::class, 'statuses'])->name('statuses');
});
