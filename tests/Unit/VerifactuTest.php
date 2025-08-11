<?php

declare(strict_types=1);

namespace eseperio\verifactu\tests\Unit;

use eseperio\verifactu\models\InvoiceCancellation;
use eseperio\verifactu\models\InvoiceResponse;
use eseperio\verifactu\models\InvoiceSubmission;
use eseperio\verifactu\Verifactu;
use PHPUnit\Framework\TestCase;

class VerifactuTest extends TestCase
{
    /**
     * Test para comprobar que existe la clase Verifactu.
     */
    public function testVerifactuClassExists(): void
    {
        $this->assertTrue(class_exists(Verifactu::class));
    }

    /**
     * Test para verificar la estructura de la clase principal Verifactu.
     */
    public function testVerifactuMethods(): void
    {
        $reflection = new \ReflectionClass(Verifactu::class);

        // Verificar que la clase Verifactu tiene los métodos principales
        // Nota: No probamos la funcionalidad, solo verificamos que los métodos existan
        $this->assertTrue(
            $reflection->hasMethod('createService'),
            'Verifactu must expose createService factory'
        );
    }

    /**
     * Test para verificar que las clases principales de modelos existen.
     */
    public function testModelsExist(): void
    {
        $this->assertTrue(class_exists(InvoiceSubmission::class), 'La clase InvoiceSubmission debe existir');
        $this->assertTrue(class_exists(InvoiceCancellation::class), 'La clase InvoiceCancellation debe existir');
        $this->assertTrue(class_exists(InvoiceResponse::class), 'La clase InvoiceResponse debe existir');
    }
}
