<?php

namespace Waxwink\Laracount\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Account extends Model
{
    protected $fillable = [
        "description"
    ];
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }
}