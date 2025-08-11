<?php

declare(strict_types=1);

namespace eseperio\verifactu\tests\Unit;

use eseperio\verifactu\Verifactu;
use PHPUnit\Framework\TestCase;

class VerifactuCriticalConfigTest extends TestCase
{
    public function testCreateServiceRejectsInvalidEnvironment(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Verifactu::createService('/tmp/cert.p12', 'pw', Verifactu::TYPE_CERTIFICATE, 'invalid');
    }

    public function testCreateServiceSupportsBothCertTypes(): void
    {
        $svcCert = Verifactu::createService('/tmp/cert.p12', 'pw', Verifactu::TYPE_CERTIFICATE, Verifactu::ENVIRONMENT_SANDBOX);
        $svcSeal = Verifactu::createService('/tmp/cert.p12', 'pw', Verifactu::TYPE_SEAL, Verifactu::ENVIRONMENT_SANDBOX);

        $this->assertNotNull($svcCert);
        $this->assertNotNull($svcSeal);
        $this->assertNotEquals($svcCert, $svcSeal);
    }
}
