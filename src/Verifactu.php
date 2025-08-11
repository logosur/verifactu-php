<?php

declare(strict_types=1);
// Main entry point of the Verifactu library

namespace eseperio\verifactu;

use eseperio\verifactu\models\InvoiceCancellation;
use eseperio\verifactu\models\InvoiceQuery;
use eseperio\verifactu\models\InvoiceRecord;
use eseperio\verifactu\models\InvoiceResponse;
use eseperio\verifactu\models\InvoiceSubmission;
use eseperio\verifactu\models\QueryResponse;
use eseperio\verifactu\services\VerifactuConfig;
use eseperio\verifactu\services\VerifactuService;

class Verifactu
{
    public const ENVIRONMENT_PRODUCTION = 'production';
    public const ENVIRONMENT_SANDBOX = 'sandbox';

    /**
     * Production environment URL.
     */
    public const URL_PRODUCTION = 'https://www1.agenciatributaria.gob.es/wlpl/TIKE-CONT/ws/SistemaFacturacion/VerifactuSOAP';

    /**
     * Production environment URL (seal certificate).
     */
    public const URL_PRODUCTION_SEAL = 'https://www10.agenciatributaria.gob.es/wlpl/TIKE-CONT/ws/SistemaFacturacion/VerifactuSOAP';

    /**
     * Test (homologation) environment URL.
     */
    public const URL_TEST = 'https://prewww1.aeat.es/wlpl/TIKE-CONT/ws/SistemaFacturacion/VerifactuSOAP';

    /**
     * Test (seal certificate) environment URL.
     */
    public const URL_TEST_SEAL = 'https://prewww10.aeat.es/wlpl/TIKE-CONT/ws/SistemaFacturacion/VerifactuSOAP';

    /**
     * QR verification URL (production).
     */
    public const QR_VERIFICATION_URL_PRODUCTION = 'https://www2.agenciatributaria.gob.es/wlpl/TIKE-CONT/ValidarQR';

    /**
     * QR verification URL (testing/homologation).
     */
    public const QR_VERIFICATION_URL_TEST = 'https://prewww2.aeat.es/wlpl/TIKE-CONT/ValidarQR';

    public const TYPE_CERTIFICATE = 'certificate';
    public const TYPE_SEAL = 'seal';

    /**
     * Creates a configured service instance (factory), instead of mutating static state.
     */
    public static function createService(string $certPath, string $certPassword, string $certType, string $environment = self::ENVIRONMENT_PRODUCTION): VerifactuService
    {
        $endpoint = match ($environment) {
            self::ENVIRONMENT_PRODUCTION => $certType === self::TYPE_SEAL ? self::URL_PRODUCTION_SEAL : self::URL_PRODUCTION,
            self::ENVIRONMENT_SANDBOX => $certType === self::TYPE_SEAL ? self::URL_TEST_SEAL : self::URL_TEST,
            default => throw new \InvalidArgumentException("Invalid environment: $environment")
        };

        $qrValidationUrl = match ($environment) {
            self::ENVIRONMENT_PRODUCTION => self::QR_VERIFICATION_URL_PRODUCTION,
            self::ENVIRONMENT_SANDBOX => self::QR_VERIFICATION_URL_TEST,
            default => throw new \InvalidArgumentException("Invalid environment: $environment")
        };

        $config = new VerifactuConfig($endpoint, $certPath, $certPassword, $qrValidationUrl);
        return new VerifactuService($config);
    }

    /**
     * Deprecated static methods kept for BC; internally create a service and delegate.
     */
    public static function config($certPath, $certPassword, $certType, $environment = self::ENVIRONMENT_PRODUCTION): void
    {
        // No-op to avoid breaking API; users should migrate to createService().
        // Left intentionally empty.
    }

    public static function registerInvoice(InvoiceSubmission $invoice): InvoiceResponse
    {
        throw new \BadMethodCallException('Use Verifactu::createService(...)->registerInvoice($invoice) instead.');
    }

    public static function cancelInvoice(InvoiceCancellation $cancellation): InvoiceResponse
    {
        throw new \BadMethodCallException('Use Verifactu::createService(...)->cancelInvoice($cancellation) instead.');
    }

    public static function queryInvoices(InvoiceQuery $query): QueryResponse
    {
        throw new \BadMethodCallException('Use Verifactu::createService(...)->queryInvoices($query) instead.');
    }

    public static function generateInvoiceQr(InvoiceRecord $record): string
    {
        throw new \BadMethodCallException('Use Verifactu::createService(...)->generateInvoiceQr($record) instead.');
    }
}
