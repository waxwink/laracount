<?php

namespace Waxwink\Laracount\Console;

use Illuminate\Console\Command;
use Waxwink\Accounting\AccountConfiguration;
use Waxwink\Laracount\Repositories\AccountRepository;

class Installer extends Command
{
    protected $signature = 'accounting:install';

    protected array $defaultAccounts = [
        "bank","revenue", "expense", "tax"
    ];

    public function handle(AccountRepository $accountRepository, AccountConfiguration $accountConfiguration)
    {
        $this->line("migrating the tables ..");
        $this->call("migrate");
        $this->line("migration finished");

        $this->line("creating some default accounts ...");
        foreach ($this->defaultAccounts as $account){
            $accountModel = $accountRepository->create(description: $account);
            $accountConfiguration->set($account, $accountModel->id);

        }
        $this->line("Accounts got created");
    }

}
