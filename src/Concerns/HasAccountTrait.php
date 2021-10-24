<?php

namespace Waxwink\Laracount\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Cache;
use Waxwink\Laracount\Models\Account;

trait HasAccountTrait
{
    public function getAccountId(): int
    {
        return Cache::remember("account_id_user_". $this->id, 10*24, fn()=>$this->account()->first()->id);
    }

    public function account(): MorphOne
    {
        return $this->morphOne(Account::class, "owner");
    }
}
