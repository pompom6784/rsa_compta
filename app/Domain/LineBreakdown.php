<?php

namespace App\Domain;

final class LineBreakdown
{
    public const PLANE_RENEWAL = "PlaneRenewal"; // Renouvellement
    public const CUSTOMER_FEES = "CustomerFees"; // Frais client
    public const RSA_NAV_CONTRIBUTION = "RSANavContribution"; // Cotisation RSA Nav
    public const RSA_CONTRIBUTION = "RSAContribution"; // Cotisation RSA
    public const FOLLOW_UP_NAV = "FollowUpNav"; // Suivi Nav
    public const INTERNAL_TRANSFER = "InternalTransfer"; // Virement interne
    public const PEN_REFUND = "PenRefund"; // Remboursement PEN
    public const MEETING = "Meeting"; // Réunion / Séminaire
    public const PAYPAL_FEES = "PaypalFees"; // Frais Paypal
    public const SOGECOM_FEES = "SogecomFees"; // Frais Sogecom
    public const OSAC = "Osac"; // OSAC
    public const OTHER = "Other"; // Autre
    public const DONATION = "Donation"; // Don Avance
    public const VIBRATION_DEBIT = "VibrationDebit"; // Vibration Debit
    public const VIBRATION_CREDIT = "VibrationCredit"; // Vibration Credit

    public static function getBreakdowns()
    {
        return [
          self::PLANE_RENEWAL => "Renouvellement",
          self::CUSTOMER_FEES => "Frais client",
          self::RSA_NAV_CONTRIBUTION => "Cotisation RSA Nav",
          self::RSA_CONTRIBUTION => "Cotisation RSA",
          self::FOLLOW_UP_NAV => "Suivi Nav",
          self::INTERNAL_TRANSFER => "Virement interne",
          self::PEN_REFUND => "Remboursement PEN",
          self::MEETING => "Réunion / Séminaire",
          self::PAYPAL_FEES => "Frais Paypal",
          self::SOGECOM_FEES => "Frais Sogecom",
          self::OSAC => "OSAC",
          self::OTHER => "Autre",
          self::DONATION => "Don",
          self::VIBRATION_DEBIT => "Vibration Debit",
          self::VIBRATION_CREDIT => "Vibration Credit",
        ];
    }
}
