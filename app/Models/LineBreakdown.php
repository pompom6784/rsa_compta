<?php

namespace App\Models;

final class LineBreakdown
{
    public const PLANE_RENEWAL        = 'PlaneRenewal';
    public const CUSTOMER_FEES        = 'CustomerFees';
    public const RSA_NAV_CONTRIBUTION = 'RSANavContribution';
    public const RSA_CONTRIBUTION     = 'RSAContribution';
    public const FOLLOW_UP_NAV        = 'FollowUpNav';
    public const INTERNAL_TRANSFER    = 'InternalTransfer';
    public const PEN_REFUND           = 'PenRefund';
    public const MEETING              = 'Meeting';
    public const PAYPAL_FEES          = 'PaypalFees';
    public const SOGECOM_FEES         = 'SogecomFees';
    public const OSAC                 = 'Osac';
    public const OTHER                = 'Other';
    public const DONATION             = 'Donation';
    public const VIBRATION_DEBIT      = 'VibrationDebit';
    public const VIBRATION_CREDIT     = 'VibrationCredit';

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            self::PLANE_RENEWAL        => 'Renouvellement',
            self::CUSTOMER_FEES        => 'Frais client',
            self::RSA_NAV_CONTRIBUTION => 'Cotisation RSA Nav',
            self::RSA_CONTRIBUTION     => 'Cotisation RSA',
            self::FOLLOW_UP_NAV        => 'Suivi Nav',
            self::INTERNAL_TRANSFER    => 'Virement interne',
            self::PEN_REFUND           => 'Remboursement PEN',
            self::MEETING              => 'Réunion / Séminaire',
            self::PAYPAL_FEES          => 'Frais Paypal',
            self::SOGECOM_FEES         => 'Frais Sogecom',
            self::OSAC                 => 'OSAC',
            self::OTHER                => 'Autre',
            self::DONATION             => 'Don',
            self::VIBRATION_DEBIT      => 'Vibration Debit',
            self::VIBRATION_CREDIT     => 'Vibration Credit',
        ];
    }

    /** @return array<string, string> */
    public static function getBreakdowns(): array
    {
        return self::labels();
    }

    /**
     * Maps constant values (e.g. 'PlaneRenewal') to Eloquent snake_case column names.
     *
     * @return array<string, string>
     */
    public static function columnKeys(): array
    {
        return [
            self::PLANE_RENEWAL        => 'breakdown_plane_renewal',
            self::CUSTOMER_FEES        => 'breakdown_customer_fees',
            self::RSA_NAV_CONTRIBUTION => 'breakdown_rsa_nav_contribution',
            self::RSA_CONTRIBUTION     => 'breakdown_rsa_contribution',
            self::FOLLOW_UP_NAV        => 'breakdown_follow_up_nav',
            self::INTERNAL_TRANSFER    => 'breakdown_internal_transfer',
            self::PEN_REFUND           => 'breakdown_pen_refund',
            self::MEETING              => 'breakdown_meeting',
            self::PAYPAL_FEES          => 'breakdown_paypal_fees',
            self::SOGECOM_FEES         => 'breakdown_sogecom_fees',
            self::OSAC                 => 'breakdown_osac',
            self::OTHER                => 'breakdown_other',
            self::DONATION             => 'breakdown_donation',
            self::VIBRATION_DEBIT      => 'breakdown_vibration_debit',
            self::VIBRATION_CREDIT     => 'breakdown_vibration_credit',
        ];
    }
}
