<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A checkout problem the customer can understand and act on, so its message is safe to show them.
 */
class CheckoutException extends RuntimeException
{
}
