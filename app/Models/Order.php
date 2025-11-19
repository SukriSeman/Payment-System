<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read   int          $id
 * @property        int          $user_id
 * @property        int          $total_price
 * @property        DateTime     $expires_at
 * @property        string       $status
 * @property        User         $user
 * @property        OrderItem[]  $items
 * @property        Payment[]    $payments
 */
class Order extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'PENDING';
    const STATUS_CONFIRMED = 'CONFIRMED';
    const STATUS_FULFILLED = 'FULFILLED';
    const STATUS_CANCELLED = 'CANCELLED';

    protected $table = 'orders';

    protected $fillable = [
        'user_id',
        'total_price',
        'expires_at',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
        'expires_at' => 'datetime'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
