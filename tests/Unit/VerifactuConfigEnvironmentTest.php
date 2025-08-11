<?php

declare(strict_types=1);

namespace eseperio\verifactu\tests\Unit;

use eseperio\verifactu\Verifactu;
use eseperio\verifactu\services\VerifactuService;
use PHPUnit\Framework\TestCase;

class VerifactuConfigEnvironmentTest extends TestCase
{
    private function getServiceConfig(VerifactuService $service)
    {
        $ref = new \ReflectionClass($service);
        $prop = $ref->getProperty('config');
        $prop->setAccessible(true);
        return $prop->getValue($service);
    }

    public function testSandboxCertificateEndpoints(): void
    {
        $service = Verifactu::createService('/tmp/dummy-cert.pem', 'secret', Verifactu::TYPE_CERTIFICATE, Verifactu::ENVIRONMENT_SANDBOX);
        $config = $this->getServiceConfig($service);

        $this->assertSame(Verifactu::URL_TEST, $config->getWsdl());
        $this->assertSame(Verifactu::QR_VERIFICATION_URL_TEST, $config->getQrValidationUrl());
    }

    public function testSandboxSealEndpoints(): void
    {
        $service = Verifactu::createService('/tmp/dummy-cert.pem', 'secret', Verifactu::TYPE_SEAL, Verifactu::ENVIRONMENT_SANDBOX);
        $config = $this->getServiceConfig($service);

        $this->assertSame(Verifactu::URL_TEST_SEAL, $config->getWsdl());
        $this->assertSame(Verifactu::QR_VERIFICATION_URL_TEST, $config->getQrValidationUrl());
    }

    public function testProductionCertificateEndpoints(): void
    {
        $service = Verifactu::createService('/tmp/dummy-cert.pem', 'secret', Verifactu::TYPE_CERTIFICATE, Verifactu::ENVIRONMENT_PRODUCTION);
        $config = $this->getServiceConfig($service);

        $this->assertSame(Verifactu::URL_PRODUCTION, $config->getWsdl());
        $this->assertSame(Verifactu::QR_VERIFICATION_URL_PRODUCTION, $config->getQrValidationUrl());
    }

    public function testProductionSealEndpoints(): void
    {
        $service = Verifactu::createService('/tmp/dummy-cert.pem', 'secret', Verifactu::TYPE_SEAL, Verifactu::ENVIRONMENT_PRODUCTION);
        $config = $this->getServiceConfig($service);

        $this->assertSame(Verifactu::URL_PRODUCTION_SEAL, $config->getWsdl());
        $this->assertSame(Verifactu::QR_VERIFICATION_URL_PRODUCTION, $config->getQrValidationUrl());
    }

    public function testInvalidEnvironmentThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Verifactu::createService('/tmp/dummy-cert.pem', 'secret', Verifactu::TYPE_CERTIFICATE, 'invalid-env');
    }
}
