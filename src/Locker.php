<?php

namespace Waxwink\Laracount;

use Waxwink\Accounting\Contracts\LockerInterface;

class Locker implements LockerInterface
{

    public function isLocked(int $account): bool
    {
        return false;
    }

    public function lock(int $account): bool
    {
        // TODO: Implement lock() method.
    }

    public function releaseLock(int $account): bool
    {
        // TODO: Implement releaseLock() method.
    }
}