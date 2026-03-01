sudo chown -R anton:anton /var/www/price-hub
cd /var/www/price-hub
composer require guzzlehttp/guzzle
composer require maatwebsite/excel
php artisan make:command FetchPrices
nano app/Console/Commands/FetchPrices.php
php artisan list | grep prices
cd /var/www/price-hub
php artisan tinker
cd /var/www/price-hub
php artisan make:controller SupplierController --resource
nano routes/web.php
nano app/Http/Controllers/SupplierController.php
mkdir -p resources/views/layouts
nano resources/views/layouts/app.blade.php
mkdir -p resources/views/suppliers
nano resources/views/suppliers/index.blade.php
nano resources/views/suppliers/create.blade.php
nano resources/views/suppliers/edit.blade.php
cd /var/www/price-hub
php artisan serve --host=0.0.0.0 --port=8000
cd /var/www/price-hub
nano resources/views/layouts/app.blade.php
php artisan serve
php artisan serve --host=0.0.0.0 --port=8000
cd /var/www/price-hub
php artisan serve --host=0.0.0.0 --port=8000
cd /var/www/price-hub
php artisan serve --host=0.0.0.0 --port=8000
cd /var/www/price-hub
php artisan make:command ImportPrices
nano app/Http/Controllers/SupplierController.php
php artisan serve --host=0.0.0.0 --port=8000
mysql -u pricehub_user -p pricehub \
cd /var/www/price-hub
mysql -u pricehub_user -p pricehub   -e "SELECT id, price_file_id, supplier_sku, name, price, quantity FROM price_rows ORDER BY id DESC LIMIT 20;"
cd /var/www/price-hub
mysql -u pricehub_user -p pricehub   -e "SELECT id, price_file_id, supplier_sku, name, price, quantity FROM price_rows ORDER BY id DESC LIMIT 20;"
cd /var/www/price-hub
mysql -u pricehub_user -p pricehub   -e "SELECT id, price_file_id, supplier_sku, name, price, quantity FROM price_rows ORDER BY id DESC LIMIT 20;"
cd /var/www/price-hub
mysql -u pricehub_user -p pricehub   -e "SELECT id, price_file_id, supplier_sku, name, price, quantity FROM price_rows ORDER BY id DESC LIMIT 20;"
