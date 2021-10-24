<?php

namespace Waxwink\Laracount\Test;

use Illuminate\Database\Eloquent\Model;
use Waxwink\Accounting\Contracts\HasAccount;
use Waxwink\Laracount\Concerns\HasAccountTrait;

class User extends Model implements HasAccount
{
    use HasAccountTrait;

    public $timestamps = false;

}