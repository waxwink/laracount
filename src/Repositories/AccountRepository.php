<?php

namespace Waxwink\Laracount\Repositories;

use Illuminate\Database\Eloquent\Model;
use Waxwink\Accounting\Contracts\HasAccount;
use Waxwink\Laracount\Models\Account;

class AccountRepository
{
    public function create(?Model $owner = null, ?string $description = null): Account
    {
        $account = Account::where("description", $description)->first();
        if (! $account){
            return  $account;
        }

        $account = Account::make(["description" => $description]);
        $owner && $account->owner()->associate($owner);
        $account->save();
        return $account;
    }

    public function exists(HasAccount $owner): bool
    {
        return $owner->account !== null;
    }
}