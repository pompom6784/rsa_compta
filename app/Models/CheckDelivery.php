<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CheckDelivery extends Model
{
    protected $table = 'check_deliveries';

    public $timestamps = false;

    protected $dateFormat = 'Y-m-d H:i:s';

    protected $fillable = [
        'date',
        'amount',
        'converted',
    ];

    protected $casts = [
        'date'      => 'immutable_datetime',
        'amount'    => 'float',
        'converted' => 'boolean',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(CheckDeliveryLine::class, 'check_delivery_id');
    }
}
