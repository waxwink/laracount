<?php

namespace Waxwink\Laracount;

use Waxwink\Accounting\Contracts\HasAccount;
use Waxwink\Laracount\Exceptions\AccountAlreadyExists;
use Waxwink\Laracount\Models\Account;
use Waxwink\Laracount\Repositories\AccountRepository;

class AccountRegistrationService
{
    public function __construct(protected AccountRepository $accountRepository)
    {
    }

    /**
     * @throws AccountAlreadyExists
     */
    public function registerAccountFor(HasAccount $owner): Account
    {
        if ($this->accountRepository->exists($owner))
            throw new AccountAlreadyExists();

        return $this->accountRepository->create($owner);
    }
}
