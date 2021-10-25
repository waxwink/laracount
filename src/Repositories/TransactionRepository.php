<?php

namespace Waxwink\Laracount\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Waxwink\Accounting\Contracts\TransactionRecordInterface;
use Waxwink\Accounting\Contracts\TransactionRepositoryInterface;
use Waxwink\Laracount\Models\TransactionRecord;

class TransactionRepository implements TransactionRepositoryInterface
{

    public function transactional(callable $callback): mixed
    {
        return DB::transaction($callback);
    }

    public function createRecord(array $attributes): TransactionRecordInterface
    {
        return TransactionRecord::create($attributes);
    }

    public function balance($accountId, mixed $ref = null): int
    {
        return DB::table("transaction_records")->select(DB::raw('(SUM(debt) - SUM(credit)) as balance'))
                 ->where("account_id", $accountId)
                 ->when($ref, fn($q) => $q->where("ref_id", $ref))
                 ->first()->balance ?? 0;
    }

    public function findByAccount(int $accountId, array $options): \ArrayAccess
    {
        return $this->getOrPaginate(
            TransactionRecord::where("account_id", $accountId)
                             ->when(key_exists("from", $options),
                                 fn($q) => $q->where("created_at", ">", $options["from"]))
                             ->when(key_exists("to", $options),
                                 fn($q) => $q->where("created_at", ">", $options["to"])), $options);
    }

    protected function getOrPaginate(Builder $query, array $options)
    {
        return $query->when(key_exists("paginate", $options) && $options["paginate"],
            fn($query) => $query->paginate(
                perPage: $options["perPage"] ?? null,
                columns: $options["columns"] ?? ['*'],
                pageName: $options["perPage"] ?? 'page',
                page: $options["page"] ?? null)
            , fn($query) => $query->get(columns: $options["columns"] ?? ['*'])
        );
    }
}