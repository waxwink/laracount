<?php

namespace Waxwink\Laracount;

use Illuminate\Support\ServiceProvider;
use Waxwink\Accounting\AccountConfiguration;
use Waxwink\Accounting\Contracts\LockerInterface;
use Waxwink\Accounting\Contracts\TransactionRepositoryInterface;
use Waxwink\Accounting\Contracts\VoucherRepositoryInterface;
use Waxwink\Laracount\Console\Installer;
use Waxwink\Laracount\Repositories\TransactionRepository;
use Waxwink\Laracount\Repositories\VoucherRepository;

class LaracountServiceProvider extends ServiceProvider
{
    protected array $defaultAccounts = [
        "bank","revenue", "expense", "tax"
    ];

    public function register()
    {
        $this->app->singleton(TransactionRepositoryInterface::class, TransactionRepository::class);
        $this->app->singleton(LockerInterface::class, Locker::class);
        $this->app->singleton(VoucherRepositoryInterface::class, VoucherRepository::class);
        $this->app->singleton(AccountConfiguration::class,AccountConfiguration::class);

        $this->commands([Installer::class]);

    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . "/../database/");

        foreach ($this->defaultAccounts as $key => $account){
            app(AccountConfiguration::class)->set($account, $key+1);
        }
    }


}