<?php

declare(strict_types=1);

namespace eseperio\verifactu\services;

use eseperio\verifactu\models\InvoiceCancellation;
use eseperio\verifactu\models\InvoiceQuery;
use eseperio\verifactu\models\InvoiceRecord;
use eseperio\verifactu\models\InvoiceResponse;
use eseperio\verifactu\models\InvoiceSubmission;
use eseperio\verifactu\models\QueryResponse;

/**
 * Service orchestrating all high-level Verifactu operations:
 * registration, cancellation, query, QR generation.
 */
class VerifactuService
{
    /** Immutable configuration injected via constructor. */
    private VerifactuConfig $config;

    /** Soap instance for communication with AEAT. */
    private ?\SoapClient $client = null;

    public function __construct(VerifactuConfig $config)
    {
        $this->config = $config;
    }

    /** Returns the SOAP client, creating it if necessary. @return \SoapClient */
    private function getClient()
    {
        if ($this->client === null) {
            $this->client = SoapClientFactoryService::createSoapClient(
                $this->config->getWsdl(),
                $this->config->getCertPath(),
                $this->config->getCertPassword(),
                []
            );
        }
        return $this->client;
    }

    /**
     * Registers a new invoice with AEAT via VERI*FACTU.
     *
     * @return InvoiceResponse
     * @throws \DOMException
     * @throws \SoapFault
     */
    public function registerInvoice(InvoiceSubmission $invoice)
    {
        // 1. Validate input (excluding hash which will be generated)
        $validation = $invoice->validateExcept(['hash']);

        if ($validation !== true) {
            throw new \InvalidArgumentException('InvoiceSubmission validation failed: ' . print_r($validation, true));
        }

        // 2. Generate hash (huella)
        $invoice->hash = HashGeneratorService::generate($invoice);

        // 3. Final validation including hash
        $finalValidation = $invoice->validate();

        if ($finalValidation !== true) {
            throw new \InvalidArgumentException('InvoiceSubmission final validation failed: ' . print_r($finalValidation, true));
        }

        // 3. Prepare XML
        $xml = $this->buildInvoiceXml($invoice);

        // 4. Sign XML (file-based certificate)
        $signedXml = XmlSignerService::signXml(
            $xml,
            $this->config->getCertPath(),
            $this->config->getCertPassword()
        );

        // 5. Get SOAP client
        $client = $this->getClient();

        // 6. Call AEAT web service
        $params = ['RegistroAlta' => $signedXml];
        $responseXml = $client->__soapCall('SuministroLR', [$params]);

        // 7. Parse AEAT response
        return ResponseParserService::parseInvoiceResponse($responseXml);
    }

    /**
     * Cancels an invoice with AEAT via VERI*FACTU.
     *
     * @return InvoiceResponse
     */
    public function cancelInvoice(InvoiceCancellation $cancellation)
    {
        // 1. Validate input (excluding hash which will be generated)
        $validation = $cancellation->validateExcept(['hash']);

        if ($validation !== true) {
            throw new \InvalidArgumentException('InvoiceCancellation validation failed: ' . print_r($validation, true));
        }

        // 2. Generate hash (huella)
        $cancellation->hash = HashGeneratorService::generate($cancellation);

        // 3. Final validation including hash
        $finalValidation = $cancellation->validate();

        if ($finalValidation !== true) {
            throw new \InvalidArgumentException('InvoiceCancellation final validation failed: ' . print_r($finalValidation, true));
        }
        $xml = $this->buildCancellationXml($cancellation);

        // Sign with file-based certificate
        $signedXml = XmlSignerService::signXml(
            $xml,
            $this->config->getCertPath(),
            $this->config->getCertPassword()
        );

        $client = $this->getClient();
        $params = ['RegistroAnulacion' => $signedXml];
        $responseXml = $client->__soapCall('SuministroLR', [$params]);

        return ResponseParserService::parseInvoiceResponse($responseXml);
    }

    /**
     * Queries submitted invoices from AEAT via VERI*FACTU.
     *
     * @return QueryResponse
     * @throws \SoapFault
     */
    public function queryInvoices(InvoiceQuery $query)
    {
        $validation = $query->validate();

        if ($validation !== true) {
            throw new \InvalidArgumentException('InvoiceQuery validation failed: ' . print_r($validation, true));
        }
        $xml = $this->buildQueryXml($query);
        $client = $this->getClient();
        $params = ['ConsultaFactuSistemaFacturacion' => $xml];
        $responseXml = $client->__soapCall('ConsultaLR', [$params]);

        return ResponseParserService::parseQueryResponse($responseXml);
    }

    public function generateInvoiceQr(
        InvoiceRecord $record,
        $destination = QrGeneratorService::DESTINATION_STRING,
        $size = 300,
        $engine = QrGeneratorService::RENDERER_GD
    ) {
        $baseUrl = $this->config->getQrValidationUrl();

        return QrGeneratorService::generateQr($record, $baseUrl, $destination, $size, $engine);
    }

    /** Serializes an InvoiceSubmission to AEAT-compliant RegistroAlta XML. @return string XML string @throws \DOMException */
    protected function buildInvoiceXml(InvoiceSubmission $invoice): string|false
    {
        $invoiceDom = $invoice->toXml();

        return $invoiceDom->saveXML();
    }

    /** Serializes an InvoiceCancellation to AEAT-compliant RegistroAnulacion XML. @return string XML string @throws \DOMException */
    protected function buildCancellationXml(InvoiceCancellation $cancellation): string|false
    {
        // Get the XML element from the model
        $cancellationDom = $cancellation->toXml();

        return $cancellationDom->saveXML();
    }

    /** Serializes an InvoiceQuery to AEAT-compliant ConsultaFactuSistemaFacturacion XML. @return string XML string @throws \DOMException */
    protected function buildQueryXml(InvoiceQuery $query): string|false
    {
        $queryDom = $query->toXml();

        return $queryDom->saveXML();
    }
}
