<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read   int       $id
 * @property        int       $order_id
 * @property        string    $idempotency_key
 * @property        string    $status
 * @property        Order     $order
 * @property        Refund[]  $refunds
 */
class Payment extends Model
{
    use HasFactory;

    const STATUS_AUTHORIZED = 'AUTHORIZED';
    const STATUS_CAPTURED = 'CAPTURED';
    const STATUS_VOIDED = 'VOIDED';
    const STATUS_REFUNDED = 'REFUNDED';

    protected $table = 'payments';

    protected $fillable = [
        'order_id',
        'idempotency_key',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * @return Order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return Refund[]
     */
    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }
}
