<?php

declare(strict_types=1);

namespace eseperio\verifactu\tests\Integration;

use eseperio\verifactu\Verifactu;
use PHPUnit\Framework\TestCase;

class VerifactuIntegrationEnvironmentTest extends TestCase
{
    public function testFactoryProducesServiceWithExpectedEndpoints(): void
    {
        $service = Verifactu::createService('/tmp/dummy-cert.pem', 'secret', Verifactu::TYPE_CERTIFICATE, Verifactu::ENVIRONMENT_SANDBOX);

        $ref = new \ReflectionClass($service);
        $prop = $ref->getProperty('config');
        $prop->setAccessible(true);
        $config = $prop->getValue($service);

        $this->assertSame(Verifactu::URL_TEST, $config->getWsdl());
        $this->assertSame(Verifactu::QR_VERIFICATION_URL_TEST, $config->getQrValidationUrl());
    }
}
