composer require filament/filament
php artisan filament:install --panels
php artisan make:filament-user
php artisan migrate


php artisan make:filament-resource Account --generate
php artisan make:filament-resource Category --generate
php artisan make:filament-resource TransactionEntry --generate
php artisan make:filament-resource Product --generate
php artisan make:filament-resource TransactionItem --generate
php artisan make:filament-resource Company --generate
php artisan make:filament-resource Customer --generate
php artisan make:filament-resource StockLevel --generate
php artisan make:filament-resource StockMovement --generate
php artisan make:filament-resource Debt --generate
php artisan make:filament-resource AccountUser --generate
php artisan make:filament-resource Message --generate
php artisan make:filament-resource Conversation --generate
