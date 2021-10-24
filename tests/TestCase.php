<?php

namespace Waxwink\Laracount\Test;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Waxwink\Laracount\Console\Installer;
use Waxwink\Laracount\LaracountServiceProvider;
use Waxwink\Laracount\AccountRegistrationService;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;
    protected AccountRegistrationService $registrationService;

    protected function setUp(): void
    {
        parent::setUp();

        require_once __DIR__. "/../database/2021_01_00_100000_create_accounts_table.php";
        require_once __DIR__. "/../database/2021_01_00_100001_create_vouchers_table.php";
        require_once __DIR__. "/../database/2021_01_00_100002_create_transaction_records_table.php";

        (new \CreateAccountsTable())->up();
        (new \CreateVouchersTable())->up();
        (new \CreateTransactionRecordsTable())->up();
        $this->artisan(Installer::class);

        Schema::create("users", function (Blueprint $table){$table->id();});

        $this->registrationService = app(AccountRegistrationService::class);

    }

    protected function getPackageProviders($app)
    {
        return [LaracountServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        # Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    protected function prepareTestUser()
    {
        $user = User::create();
        $this->registrationService->registerAccountFor($user);
        return $user;
    }
}