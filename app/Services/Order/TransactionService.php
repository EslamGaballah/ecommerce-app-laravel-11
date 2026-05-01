<?php

namespace App\Services\Order;

use Illuminate\Support\Facades\DB;
use Throwable;

class TransactionService
{
    public function run(callable $callback)
    {
        DB::beginTransaction();

        try {
            $result = $callback();

            DB::commit();

            return $result;

        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}