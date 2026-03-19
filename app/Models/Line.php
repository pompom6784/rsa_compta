<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Line extends Model
{
    protected $table = 'lines';

    public $timestamps = false;

    protected $fillable = [
        'type',
        'date',
        'name',
        'label',
        'amount',
        'breakdown',
        'description',
        'breakdown_plane_renewal',
        'breakdown_customer_fees',
        'breakdown_rsa_contribution',
        'breakdown_rsa_nav_contribution',
        'breakdown_follow_up_nav',
        'breakdown_internal_transfer',
        'breakdown_pen_refund',
        'breakdown_meeting',
        'breakdown_paypal_fees',
        'breakdown_sogecom_fees',
        'breakdown_osac',
        'breakdown_other',
        'breakdown_donation',
        'breakdown_vibration_debit',
        'breakdown_vibration_credit',
    ];

    protected $casts = [
        'date'                           => 'immutable_datetime',
        'amount'                         => 'float',
        'breakdown'                      => 'array',
        'breakdown_plane_renewal'        => 'float',
        'breakdown_customer_fees'        => 'float',
        'breakdown_rsa_contribution'     => 'float',
        'breakdown_rsa_nav_contribution' => 'float',
        'breakdown_follow_up_nav'        => 'float',
        'breakdown_internal_transfer'    => 'float',
        'breakdown_pen_refund'           => 'float',
        'breakdown_meeting'              => 'float',
        'breakdown_paypal_fees'          => 'float',
        'breakdown_sogecom_fees'         => 'float',
        'breakdown_osac'                 => 'float',
        'breakdown_other'                => 'float',
        'breakdown_donation'             => 'float',
        'breakdown_vibration_debit'      => 'float',
        'breakdown_vibration_credit'     => 'float',
    ];

    protected function debit(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->amount < 0 ? $this->amount : null,
        );
    }

    protected function credit(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->amount > 0 ? $this->amount : null,
        );
    }

    protected $appends = ['debit', 'credit'];
}
