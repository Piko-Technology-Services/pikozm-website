<?php

namespace App\Models;

use App\Models\Concerns\LencoPayable;
use Illuminate\Database\Eloquent\Model;

/**
 * A free-form PikoPay payment (/pikopay), collected through Lenco.
 */
class ClientPayment extends Model
{
    use LencoPayable;

    public const REFERENCE_PREFIX = 'PKP';

    protected $fillable = ['reference', 'full_name', 'email', 'purpose', 'amount', 'currency', 'status'];
}
