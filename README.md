# Laravel Accounting System
This package can manage accounting of a Laravel application with an easy approach and great functionalities. 
These functionalities are
* Deposit and withdrawing from wallet
* Calculating the total existing money received from users (Bank account balance)
* Calculating the total revenue and expenses of the system
* Tax payment can also be handled, and the total amount of paid taxes can be calculated
* Race condition can be handled by lock feature
* Important reports can be produced

## Installation
Installation is pretty straightforward. require the package :
```shell
composer require waxwink/laracount 
```

And then install it :
```shell
php artisan accounting:install
```

Now we're ready to go.

## Basic usage
The API is pretty user-friendly as the main service that you are dealing with most of the time is the `AccountingService`.
You make an object from the service using container and start using its methods:

```php
use Waxwink\Laracount\AccountingService;

$service = app(AccountingService::class);
```

### Deposit 
Users may deposit to their accounts:
```php
$service->deposit($user, 15000);
```

### Withdraw 
Users may withdraw from their accounts:
```php
$service->withdraw($user, 7000);
```

### Pay
Users can pay for a service or product or whatever:
```php
$service->pay($user, 3000);
```
Laracount does not care about the invoice or the reason of payment, it just takes care of the accounting.
But a `ref_id` can be passed in order to track the transactions and use them for reporting purposes. 
This key can be the **invoice number** or anything.
```php
$service->pay($user, 3000, $refId);
```

### PayTo
The system can also pay to the users. Like the monthly profit or for the service the users have provided like driving, repairing ,...
```php
$service->payTo($user, 3000);
```

### Refund
Users can refund money
```php
$service->refund($user, 1000);
```
Laracount does not care about the reason of refund, the amount of it or whether the money has been paid before or not.
Like previous methods the transaction can be tracked by a `ref_id` key.
```php
$service->pay($user, 1000, $refId);
```

### Balance
Accounts balance can be retrieved:
```php
$service->balance($user);
```

### Other Balance APIs
Bank, revenue and expense balances can be retrieved by:
```php
$service->bankBalance();
$service->revenueBalance();
$service->expenseBalance();
$service->bankBalanceByRef($refId);
$service->revenueBalanceByRef($refId);
$service->expenseBalanceByRef($refId);
```

### Transactions list
Transactions List of an account can be fetched
```php
$service->transactionsList($user);
```
You can paginate the transactions this way:
```php
$service->transactionsList($user, paginate: true, perPage:5, page:2);
```
Columns can also be defined:
```php
$service->transactionsList($user, columns:['balance', 'created_at', 'description']);
```
Items can be sorted like this:
```php
$service->transactionsList($user, orderBy: "created_at");
$service->transactionsList($user, orderBy: "created_at", direction:"asc");
```
Date filtering is also available:
```php
$service->transactionsList($user, from:"2017-02-01");
$service->transactionsList($user, from:"2017-02-01", to:"2020-01-01");
```
## Cool, Right?
A little thing should be taken care of before using the above methods. The users should provide an `account_id` and that ID must be grater than 10 because there the first tens are reserved for the non-user accounts like **bank, revenue, expense, ...**
So you must implement the `HasAccount` interface and use the `HasAccountTrait`. So your user model becomes something like this :
```php
use Illuminate\Database\Eloquent\Model;
use Waxwink\Accounting\Contracts\HasAccount;
use Waxwink\Laracount\Concerns\HasAccountTrait;

class User extends Model implements HasAccount
{
    use HasAccountTrait;
    
}

```
Another thing you should know is that the customer should be registered in the `accounts` table.
We have a service for that which should be used only once per user (for example when he is registering):
```php
$registrationService= app(\Waxwink\Laracount\AccountRegistrationService::class);
$registrationService->registerAccountFor($user);
```

When you put this code in your registering controller it would become something like this:
```php
class RegisterController extends Controller

    // ..... 
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected AccountRegistrationService $accountRegistrationService)
    {
        $this->middleware('guest');
    }
    
    // ....
    
    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        $this->accountRegistrationService->registerAccountFor($user);
        return $user;
    }
    //....
}
```
That's all. Now you are ready to use the above methods.