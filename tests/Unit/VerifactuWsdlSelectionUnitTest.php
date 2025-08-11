<?php

declare(strict_types=1);

namespace eseperio\verifactu\tests\Unit;

use eseperio\verifactu\services\VerifactuConfig;
use eseperio\verifactu\services\VerifactuService;
use PHPUnit\Framework\TestCase;

class VerifactuWsdlSelectionUnitTest extends TestCase
{
    public function testExplicitWsdlOverridesDefault(): void
    {
        $customWsdl = 'https://example.com/custom.wsdl';
        $config = new VerifactuConfig($customWsdl, '/tmp/cert.pem', 'pass', 'https://example.com/qr');
        $service = new VerifactuService($config);

        // Use reflection to access protected builder and ensure no exception creating XML
        $ref = new \ReflectionClass($service);
        $prop = $ref->getProperty('config');
        $prop->setAccessible(true);
        $actualConfig = $prop->getValue($service);

        $this->assertSame($customWsdl, $actualConfig->getWsdl());
        $this->assertSame('/tmp/cert.pem', $actualConfig->getCertPath());
        $this->assertSame('pass', $actualConfig->getCertPassword());
        $this->assertSame('https://example.com/qr', $actualConfig->getQrValidationUrl());
    }

    public function testNullWsdlFallsBackToBundled(): void
    {
        $config = new VerifactuConfig(null, '/tmp/cert.pem', 'pass', null);
        $service = new VerifactuService($config);

        $ref = new \ReflectionClass($service);
        $prop = $ref->getProperty('config');
        $prop->setAccessible(true);
        $actualConfig = $prop->getValue($service);

        $this->assertStringEndsWith('/docs/aeat/SistemaFacturacion.wsdl.xml', $actualConfig->getWsdl());
        $this->assertSame('', $actualConfig->getQrValidationUrl());
    }
}
