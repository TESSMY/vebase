<?php

use HaydenPierce\ClassFinder\ClassFinder;
use Illuminate\Support\Facades\Route;
use Vecapital\Vebase\Http\Controllers\VeController;
use App\Providers\RouteServiceProvider;

Route::group([
    'prefix' => 'admin',
    'as' => 'admin.',
    'middleware' => (['web', 'admin']),
], function () {
    Route::get('/sales-reports/export/', RouteServiceProvider::ADMIN_NAMESPACE . 'SalesReportController@export')->name('sales-reports.export');
    Route::get('/sales-reports', RouteServiceProvider::ADMIN_NAMESPACE . 'SalesReportController@export')->name('sales-reports.index');
    Route::post('quotations/{quotation}/send', RouteServiceProvider::ADMIN_NAMESPACE . 'QuotationController@send')->name('quotations.send');
    Route::put('quotations/{quotation}/void', RouteServiceProvider::ADMIN_NAMESPACE . 'QuotationController@void')->name('quotations.void');
    Route::get('/inventory-reports', RouteServiceProvider::ADMIN_NAMESPACE . 'InventoryReportController@index')->name('inventory-reports.index');
    Route::get('/inventory-reports/history', RouteServiceProvider::ADMIN_NAMESPACE . 'InventoryReportController@history')->name('inventory-reports.history');
    Route::post('/inventory-reports', RouteServiceProvider::ADMIN_NAMESPACE . 'InventoryReportController@generate')->name('inventory-reports.generate');

    Route::post('/suppliers/import', RouteServiceProvider::ADMIN_NAMESPACE . 'SupplierController@import')->name('suppliers.import');
    Route::post('/suppliers/export', RouteServiceProvider::ADMIN_NAMESPACE . 'SupplierController@export')->name('suppliers.export');

    Route::post('/clients/export', RouteServiceProvider::ADMIN_NAMESPACE . 'ClientController@export')->name('clients.export');
    Route::post('/clients/import', RouteServiceProvider::ADMIN_NAMESPACE . 'ClientController@import')->name('clients.import');
});

$classes = ClassFinder::getClassesInNamespace('App\Models');

foreach ($classes as $class) {
    if (in_array(\Vecapital\Vebase\Traits\VeModel::class, class_uses_recursive($class))) {
        $class = new $class();
        if ($class->hasApiResourceRoute()) {
            $name = (new \ReflectionClass($class))->getShortName();
            $overrideClass = 'App\\Controllers\\' . $name . 'Controller';
            $class = class_exists($overrideClass) ? $overrideClass : VeController::class;
            Route::resource(\Illuminate\Support\Str::plural($name), $class);
        }
    }
}

Route::resource('quotation-requests', RouteServiceProvider::ADMIN_NAMESPACE . 'QuotationRequestController');
Route::post('quotation-requests/{quotationRequest}/send', RouteServiceProvider::ADMIN_NAMESPACE . 'QuotationRequestController@send')->name('quotation-requests.send');
Route::get('quotation-requests/{quotationRequest}/generatePo', RouteServiceProvider::ADMIN_NAMESPACE . 'QuotationRequestController@generatePo')->name('quotation-requests.generatePo');