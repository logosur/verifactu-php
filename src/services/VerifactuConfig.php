<?php

declare(strict_types=1);

namespace eseperio\verifactu\services;

/**
 * Immutable configuration for Verifactu service.
 */
class VerifactuConfig
{
    private string $wsdl;
    private string $certPath;
    private string $certPassword;
    private string $qrValidationUrl;

    public function __construct(
        ?string $wsdl,
        string $certPath,
        string $certPassword,
        ?string $qrValidationUrl
    ) {
        // Default WSDL to bundled AEAT file if not provided
        $this->wsdl = $wsdl && $wsdl !== ''
            ? $wsdl
            : __DIR__ . '/../../docs/aeat/SistemaFacturacion.wsdl.xml';
        $this->certPath = $certPath;
        $this->certPassword = $certPassword;
        // QR validation URL may be required by QR generation; keep as provided (caller decides)
        $this->qrValidationUrl = $qrValidationUrl ?? '';
    }

    public function getWsdl(): string
    {
        return $this->wsdl;
    }

    public function getCertPath(): string
    {
        return $this->certPath;
    }

    public function getCertPassword(): string
    {
        return $this->certPassword;
    }

    public function getQrValidationUrl(): string
    {
        return $this->qrValidationUrl;
    }
}
