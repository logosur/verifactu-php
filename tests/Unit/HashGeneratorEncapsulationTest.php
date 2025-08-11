<?php

declare(strict_types=1);

namespace eseperio\verifactu\tests\Unit;

use eseperio\verifactu\models\Chaining;
use eseperio\verifactu\models\InvoiceId;
use eseperio\verifactu\models\InvoiceSubmission;
use eseperio\verifactu\services\HashGeneratorService;
use PHPUnit\Framework\TestCase;

class HashGeneratorEncapsulationTest extends TestCase
{
    public function testGenerateSubmissionHashWithoutPrevious(): void
    {
        $invoice = new InvoiceSubmission();

        $id = new InvoiceId();
        $id->issuerNif = 'B12345678';
        $id->seriesNumber = 'FA2025/001';
        $id->issueDate = '2025-01-01';
        $invoice->setInvoiceId($id);

        $invoice->invoiceType = 'F1';
        $invoice->taxAmount = 21.00;
        $invoice->totalAmount = 121.00;
        $invoice->recordTimestamp = '2025-01-01T12:00:00+01:00';

        $chaining = new Chaining();
        $chaining->setAsFirstRecord();
        $invoice->setChaining($chaining);

        $generated = HashGeneratorService::generate($invoice);

        $expectedData = implode('&', [
            'IDEmisorFactura=' . 'B12345678',
            'NumSerieFactura=' . 'FA2025/001',
            'FechaExpedicionFactura=' . '2025-01-01',
            'TipoFactura=' . 'F1',
            'CuotaTotal=' . '21',
            'ImporteTotal=' . '121',
            'Huella=' . '',
            'FechaHoraHusoGenRegistro=' . '2025-01-01T12:00:00+01:00',
        ]);
        $expected = base64_encode(hash('sha256', $expectedData, true));

        $this->assertSame($expected, $generated);
    }

    public function testGenerateSubmissionHashWithPrevious(): void
    {
        $invoice = new InvoiceSubmission();

        $id = new InvoiceId();
        $id->issuerNif = 'B12345678';
        $id->seriesNumber = 'FA2025/002';
        $id->issueDate = '2025-01-02';
        $invoice->setInvoiceId($id);

        $invoice->invoiceType = 'F1';
        $invoice->taxAmount = 10.50;
        $invoice->totalAmount = 110.50;
        $invoice->recordTimestamp = '2025-01-02T13:00:00+01:00';

        $chaining = new Chaining();
        $chaining->setPreviousInvoice([
            'issuerNif' => 'B12345678',
            'seriesNumber' => 'FA2025/001',
            'issueDate' => '2025-01-01',
            'hash' => 'prevhash123',
        ]);
        $invoice->setChaining($chaining);

        $generated = HashGeneratorService::generate($invoice);

        // normalize decimals in the same way as service: 10.50 -> 10.5, 110.50 -> 110.5
        $expectedData = implode('&', [
            'IDEmisorFactura=B12345678',
            'NumSerieFactura=FA2025/002',
            'FechaExpedicionFactura=2025-01-02',
            'TipoFactura=F1',
            'CuotaTotal=10.5',
            'ImporteTotal=110.5',
            'Huella=prevhash123',
            'FechaHoraHusoGenRegistro=2025-01-02T13:00:00+01:00',
        ]);
        $expected = base64_encode(hash('sha256', $expectedData, true));

        $this->assertSame($expected, $generated);
    }
}
