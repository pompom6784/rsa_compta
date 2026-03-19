<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckDeliveryLine extends Model
{
    protected $table = 'check_deliveries_line';

    public $timestamps = false;

    protected $fillable = [
        'check_number',
        'name',
        'label',
        'amount',
        'check_delivery_id',
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    public function checkDelivery(): BelongsTo
    {
        return $this->belongsTo(CheckDelivery::class, 'check_delivery_id');
    }
}
