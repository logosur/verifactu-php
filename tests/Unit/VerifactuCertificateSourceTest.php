<?php

declare(strict_types=1);

namespace eseperio\verifactu\tests\Unit;

use eseperio\verifactu\services\VerifactuConfig;
use eseperio\verifactu\services\VerifactuService;
use PHPUnit\Framework\TestCase;

class VerifactuCertificateSourceTest extends TestCase
{
    public function testServiceHoldsCertificatePathAndPassword(): void
    {
        $config = new VerifactuConfig('https://example/wsdl', '/tmp/cert.p12', 'pw', 'https://example/qr');
        $service = new VerifactuService($config);

        $ref = new \ReflectionClass($service);
        $prop = $ref->getProperty('config');
        $prop->setAccessible(true);
        $actual = $prop->getValue($service);

        $this->assertSame('/tmp/cert.p12', $actual->getCertPath());
        $this->assertSame('pw', $actual->getCertPassword());
    }
}
