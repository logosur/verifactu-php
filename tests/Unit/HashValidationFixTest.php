<?php

declare(strict_types=1);

namespace eseperio\verifactu\tests\Unit;

use eseperio\verifactu\models\Chaining;
use eseperio\verifactu\models\ComputerSystem;
use eseperio\verifactu\models\InvoiceId;
use eseperio\verifactu\models\InvoiceSubmission;
use eseperio\verifactu\models\enums\HashType;
use eseperio\verifactu\models\enums\InvoiceType;
use eseperio\verifactu\services\HashGeneratorService;
use eseperio\verifactu\models\Breakdown;
use eseperio\verifactu\models\BreakdownDetail;
use eseperio\verifactu\models\enums\OperationQualificationType;
use PHPUnit\Framework\TestCase;

class HashValidationFixTest extends TestCase
{
    public function testValidateExceptAllowsHashToBeMissingThenFinalValidatePasses(): void
    {
        $invoice = new InvoiceSubmission();

        // Minimal required fields for validateExcept(['hash']) to pass
        $id = new InvoiceId();
        $id->issuerNif = 'B12345678';
        $id->seriesNumber = 'FA2025/003';
        $id->issueDate = '2025-01-03';
        $invoice->setInvoiceId($id);

        $invoice->issuerName = 'Company';
        $invoice->operationDescription = 'Desc';
        $invoice->invoiceType = InvoiceType::STANDARD;
        $invoice->taxAmount = 21.00;
        $invoice->totalAmount = 121.00;
        $invoice->recordTimestamp = '2025-01-03T14:00:00+01:00';
        $invoice->hashType = HashType::SHA_256;

        // Provide a Breakdown with one detail
        $breakdown = new Breakdown();
        $detail = new BreakdownDetail();
        $detail->taxableBase = 100.00;
        $detail->taxRate = 21.00;
        $detail->taxAmount = 21.00;
        $detail->operationQualification = OperationQualificationType::SUBJECT_NO_EXEMPT_NO_REVERSE;
        $breakdown->addDetail($detail);
        $invoice->setBreakdown($breakdown);

        // Chaining and SystemInfo instances
        $chaining = new Chaining();
        $chaining->setAsFirstRecord();
        $invoice->setChaining($chaining);
        $invoice->setSystemInfo(new ComputerSystem());

        // Pre-hash validation should pass even without hash
        $pre = $invoice->validateExcept(['hash']);
        $this->assertTrue($pre, 'Pre-hash validation must pass without hash');

        // Now generate hash and final validate must pass
        $invoice->hash = HashGeneratorService::generate($invoice);
        $final = $invoice->validate();
        $this->assertTrue($final, 'Final validation must pass with hash set');
    }
}
