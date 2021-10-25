<?php

namespace Waxwink\Laracount;

use Illuminate\Cache\CacheManager;
use Waxwink\Accounting\Contracts\LockerInterface;

class Locker implements LockerInterface
{
    public function __construct(protected CacheManager $cache)
    {
    }

    public function isLocked(int $account): bool
    {
        return (bool) $this->cache->get("lock_account_$account") ?? false;
    }

    public function lock(int $account): bool
    {
        return $this->cache->set("lock_account_$account", true);
    }

    public function releaseLock(int $account): bool
    {
        return $this->cache->forget("lock_account_$account");
    }
}