<?php

namespace eseperio\verifactu\tests\Integration;

use eseperio\verifactu\models\Breakdown;
use eseperio\verifactu\models\BreakdownDetail;
use eseperio\verifactu\models\Chaining;
use eseperio\verifactu\models\ComputerSystem;
use eseperio\verifactu\models\enums\InvoiceType;
use eseperio\verifactu\models\enums\OperationQualificationType;
use eseperio\verifactu\models\enums\TaxType;
use eseperio\verifactu\models\enums\YesNoType;
use eseperio\verifactu\models\InvoiceId;
use eseperio\verifactu\models\InvoiceSubmission;
use eseperio\verifactu\models\LegalPerson;
use eseperio\verifactu\services\HashGeneratorService;
use PHPUnit\Framework\TestCase;

class ReadmeAltaExampleTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        // No environment or external dependencies required
    }

    public function testBuildAndValidateInvoiceFromReadme()
    {
        $invoice = new InvoiceSubmission();

        // Set invoice ID (using object-oriented approach)
        $invoiceId = new InvoiceId();
        $invoiceId->issuerNif = 'B12345678';
        $invoiceId->seriesNumber = 'FA2025/' . uniqid();
        $invoiceId->issueDate = date('Y-m-d');
        $invoice->setInvoiceId($invoiceId);

        // Set basic invoice data
        $invoice->issuerName = 'Empresa Ejemplo SL';
        $invoice->invoiceType = InvoiceType::STANDARD;
        $invoice->operationDescription = 'Venta de productos';
        $invoice->taxAmount = 21.00;
        $invoice->totalAmount = 121.00;
        $invoice->simplifiedInvoice = YesNoType::NO;
        $invoice->invoiceWithoutRecipient = YesNoType::NO;

        // Add tax breakdown (using object-oriented approach)
        $breakdown = new Breakdown();
        $detail = new BreakdownDetail();
        $detail->taxType = TaxType::IVA;
        $detail->taxRate = 21.00;
        $detail->taxableBase = 100.00;
        $detail->taxAmount = 21.00;
        $detail->operationQualification = OperationQualificationType::SUBJECT_NO_EXEMPT_NO_REVERSE;
        $breakdown->addDetail($detail);
        $invoice->setBreakdown($breakdown);

        // Set chaining data (using object-oriented approach)
        $chaining = new Chaining();
        $chaining->firstRecord = YesNoType::YES;
        $invoice->setChaining($chaining);

        // Set system information (using object-oriented approach)
        $computerSystem = new ComputerSystem();
        $computerSystem->systemName = 'ERP Company';
        $computerSystem->version = '1.0';
        $computerSystem->providerName = 'Software Provider';
        $computerSystem->systemId = '01';
        $computerSystem->installationNumber = '1';
        $computerSystem->onlyVerifactu = YesNoType::YES;
        $computerSystem->multipleObligations = YesNoType::NO;

        // Set provider information
        $provider = new LegalPerson();
        $provider->name = 'Software Provider SL';
        $provider->nif = 'B87654321';
        $computerSystem->setProviderId($provider);

        $invoice->setSystemInfo($computerSystem);

        // Set other required fields
        $invoice->recordTimestamp = date('c');

        // Optional fields
        $invoice->operationDate = date('Y-m-d');
        $invoice->externalRef = 'REF' . time();

        // Add recipients using LegalPerson and addRecipient()
        $recipient = new LegalPerson();
        $recipient->name = 'Cliente Ejemplo SL';
        $recipient->nif = 'A98765432';
        $invoice->addRecipient($recipient);

        // 1) Validate before hash (excluding hash)
        $preHashValidation = $invoice->validateExcept(['hash']);
        $this->assertTrue($preHashValidation, 'Pre-hash validation failed: ' . (is_array($preHashValidation) ? print_r($preHashValidation, true) : ''));

        // 2) Generate hash and set it on the model
        $invoice->hash = HashGeneratorService::generate($invoice);
        $this->assertNotEmpty($invoice->hash, 'Hash generation failed');

        // 3) Final validation including hash
        $finalValidation = $invoice->validate();
        $this->assertTrue($finalValidation, 'Final validation failed: ' . (is_array($finalValidation) ? print_r($finalValidation, true) : ''));

        // 4) XML generation should include the Huella node
        $xml = $invoice->toXml()->saveXML();
        $this->assertIsString($xml);
        $this->assertStringContainsString('<Huella>', $xml);
    }
}
