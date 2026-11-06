<?php

namespace App\Services\Order;

use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    /**
     * Run the callback in a transaction, retrying when simultaneous requests
     * collide on a unique value such as the next order number.
     */
    public function run(callable $callback, int $attempts = 3)
    {
        for ($attempt = 1; ; $attempt++) {
            try {
                return DB::transaction($callback);
            } catch (UniqueConstraintViolationException $e) {
                if ($attempt >= $attempts) {
                    throw $e;
                }
            }
        }
    }
}
