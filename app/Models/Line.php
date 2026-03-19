<?php

namespace App\Models;

use App\Casts\SimpleArrayCast;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int                             $id
 * @property string|null                     $type
 * @property \Illuminate\Support\Carbon      $date
 * @property string|null                     $name
 * @property string|null                     $label
 * @property float                           $amount
 * @property array                           $breakdown
 * @property string|null                     $description
 * @property float|null                      $breakdown_plane_renewal
 * @property float|null                      $breakdown_customer_fees
 * @property float|null                      $breakdown_rsa_contribution
 * @property float|null                      $breakdown_rsa_nav_contribution
 * @property float|null                      $breakdown_follow_up_nav
 * @property float|null                      $breakdown_internal_transfer
 * @property float|null                      $breakdown_pen_refund
 * @property float|null                      $breakdown_meeting
 * @property float|null                      $breakdown_paypal_fees
 * @property float|null                      $breakdown_sogecom_fees
 * @property float|null                      $breakdown_osac
 * @property float|null                      $breakdown_other
 * @property float|null                      $breakdown_donation
 * @property float|null                      $breakdown_vibration_debit
 * @property float|null                      $breakdown_vibration_credit
 * @property-read float|null                 $debit
 * @property-read float|null                 $credit
 */
class Line extends Model
{
    protected $table = 'lines';

    public $timestamps = false;

    protected $dateFormat = 'Y-m-d H:i:s';

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
        'breakdown'                      => SimpleArrayCast::class,
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
