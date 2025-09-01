# Model 
php artisan make:model Account -m
php artisan make:model Category -m
php artisan make:model TransactionEntry -m
php artisan make:model Product -m
php artisan make:model TransactionItem -m
php artisan make:model Company -m
php artisan make:model Customer -m
php artisan make:model StockLevel -m
php artisan make:model StockMovement -m
php artisan make:model Debt -m
php artisan make:model AccountUser -m
php artisan make:model Message -m
php artisan make:model Conversation -m

# api
php artisan make:controller Api/AccountController --api --model=Account
php artisan make:controller Api/CategoryController --api --model=Category
php artisan make:controller Api/TransactionEntryController --api --model=TransactionEntry
php artisan make:controller Api/ProductController --api --model=Product
php artisan make:controller Api/TransactionItemController --api --model=TransactionItem
php artisan make:controller Api/CompanyController --api --model=Company
php artisan make:controller Api/CustomerController --api --model=Customer
php artisan make:controller Api/StockLevelController --api --model=StockLevel
php artisan make:controller Api/StockMovementController --api --model=StockMovement
php artisan make:controller Api/DebtController --api --model=Debt
php artisan make:controller Api/AccountUserController --api --model=AccountUser
php artisan make:controller Api/MessageController --api --model=Message
php artisan make:controller Api/ConversationController --api --model=Conversation

# swagger



[http://localhost:8000/api/documentation](http://localhost:8000/api/documentation)


php artisan route:clear
php artisan config:clear
php artisan optimize:clear

php artisan l5-swagger:generate

php artisan route:list --path=documentation 


php artisan storage:link
