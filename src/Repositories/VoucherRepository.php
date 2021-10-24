<?php

namespace Waxwink\Laracount\Repositories;

use Waxwink\Accounting\Contracts\VoucherInterface;
use Waxwink\Accounting\Contracts\VoucherRepositoryInterface;
use Waxwink\Laracount\Models\Voucher;

class VoucherRepository implements VoucherRepositoryInterface
{

    public function create(): VoucherInterface
    {
        return Voucher::create();
    }

    public function exists(string $voucherId): bool
    {
        return Voucher::exists($voucherId);
    }
}