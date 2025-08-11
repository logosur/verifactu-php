<?php

declare(strict_types=1);

namespace eseperio\verifactu\tests\Integration;

use eseperio\verifactu\services\VerifactuConfig;
use eseperio\verifactu\services\VerifactuService;
use PHPUnit\Framework\TestCase;

class VerifactuWsdlSelectionTest extends TestCase
{
    public function testServiceUsesCustomWsdlFromConfig(): void
    {
        $customWsdl = 'https://example.org/aeat.wsdl';
        $config = new VerifactuConfig($customWsdl, '/tmp/cert.pem', 'pass', 'https://example.org/qr');
        $service = new VerifactuService($config);

        $ref = new \ReflectionClass($service);
        $prop = $ref->getProperty('config');
        $prop->setAccessible(true);
        $actual = $prop->getValue($service);

        $this->assertSame($customWsdl, $actual->getWsdl());
        $this->assertSame('https://example.org/qr', $actual->getQrValidationUrl());
    }
}
