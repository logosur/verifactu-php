<?php

declare(strict_types=1);

namespace eseperio\verifactu\tests\Unit;

use eseperio\verifactu\services\VerifactuConfig;
use PHPUnit\Framework\TestCase;

class VerifactuConfigWithContentTest extends TestCase
{
    public function testQrUrlDefaultsToEmptyWhenNull(): void
    {
        $config = new VerifactuConfig(null, '/tmp/cert.pem', 'pass', null);
        $this->assertSame('', $config->getQrValidationUrl());
    }

    public function testCertPathAndPasswordStored(): void
    {
        $config = new VerifactuConfig('https://example/wsdl', '/path/to/cert.p12', 's3cret', 'https://example/qr');
        $this->assertSame('/path/to/cert.p12', $config->getCertPath());
        $this->assertSame('s3cret', $config->getCertPassword());
    }
}
