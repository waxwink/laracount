<?php

namespace Waxwink\Laracount\Models;

use Illuminate\Database\Eloquent\Model;
use Waxwink\Accounting\Contracts\TransactionRecordInterface;

class TransactionRecord extends Model implements TransactionRecordInterface
{
    protected $fillable = [
        "account_id",
        "voucher_id",
        "debt",
        "credit",
        "description",
        "ref_id",
    ];

}
