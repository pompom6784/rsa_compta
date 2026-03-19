<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rename the breakdown columns in the `lines` table from the Doctrine-generated
 * camelCase names (e.g. `breakdownPlaneRenewal`) to the Laravel snake_case
 * convention (e.g. `breakdown_plane_renewal`).
 *
 * Doctrine ORM's DefaultNamingStrategy stores camelCase property names as-is,
 * while Laravel Eloquent expects snake_case column names.
 */
return new class extends Migration
{
    /** @var array<string, string> old (camelCase) => new (snake_case) */
    private const RENAMES = [
        'breakdownPlaneRenewal'     => 'breakdown_plane_renewal',
        'breakdownCustomerFees'     => 'breakdown_customer_fees',
        'breakdownRSAContribution'  => 'breakdown_rsa_contribution',
        'breakdownRSANavContribution' => 'breakdown_rsa_nav_contribution',
        'breakdownFollowUpNav'      => 'breakdown_follow_up_nav',
        'breakdownInternalTransfer' => 'breakdown_internal_transfer',
        'breakdownPenRefund'        => 'breakdown_pen_refund',
        'breakdownMeeting'          => 'breakdown_meeting',
        'breakdownPaypalFees'       => 'breakdown_paypal_fees',
        'breakdownSogecomFees'      => 'breakdown_sogecom_fees',
        'breakdownOsac'             => 'breakdown_osac',
        'breakdownOther'            => 'breakdown_other',
        'breakdownDonation'         => 'breakdown_donation',
        'breakdownVibrationDebit'   => 'breakdown_vibration_debit',
        'breakdownVibrationCredit'  => 'breakdown_vibration_credit',
    ];

    public function up(): void
    {
        Schema::table('lines', function (Blueprint $table) {
            foreach (self::RENAMES as $from => $to) {
                $table->renameColumn($from, $to);
            }
        });
    }

    public function down(): void
    {
        Schema::table('lines', function (Blueprint $table) {
            foreach (array_flip(self::RENAMES) as $from => $to) {
                $table->renameColumn($from, $to);
            }
        });
    }
};
