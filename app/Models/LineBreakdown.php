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
}
