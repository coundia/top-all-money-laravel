php artisan install:api


# swagger

https://github.com/DarkaOnLine/L5-Swagger/wiki/Installation-&-Configuration

`composer require darkaonline/l5-swagger

php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
php artisan l5-swagger:generate

php artisan route:clear
php artisan config:clear
php artisan optimize:clear


php artisan route:list --path=documentation


php artisan storage:link
`
[http://localhost:8000/api/documentation](http://localhost:8000/api/documentation)




