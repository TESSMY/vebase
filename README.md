<p align="center"><a href="https://vecapital.asia" target="_blank"><img src="https://vecapital.asia/images/logo.svg" width="400" alt="VE Logo"></a></p>

Add this to the `admin.php` routes
```
...

Route::get('/sales-reports/export/', RouteServiceProvider::ADMIN_NAMESPACE . 'SalesReportController@export')->name('sales-reports.export'); // make sure this line is  before the classfinder

$classes = ClassFinder::getClassesInNamespace('App\Models');

...
```