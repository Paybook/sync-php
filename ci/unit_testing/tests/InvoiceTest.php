<?php

declare (strict_types=1);

use PHPUnit\Framework\TestCase;

/*
 * @covers Taxpayer
 */

paybook\Paybook::init(true);

final class InvoiceTest extends TestCase
{   
    const ID_PROVIDER = '57e9ab29088986e9028b45e3';# ACME
    const TAXPAYER = 'AAA010101AAA';
    const INVOICE_DATA = [ 
        "serie" => "BBBBA",
        "folio" => "35",
        "fecha" => "",
        "formaDePago" => "Parcialidad 1 de 30",
        "condicionesDePago" => "Valido por 30 días",
        "subTotal" => "30.55",
        "descuento" => "20",
        "motivoDescuento" => "Promocion mensual",
        "tipoCambio" => "18.22",
        "moneda" => "MXN",
        "total" => "100.111",
        "tipoDeComprobante" => "ingreso",
        "metodoDePago" => "01",
        "lugarExpedicion" => "Ciudad de México",
        "emisor" => [
            "nombre" => "Alejandro Hernandez Rodriguez",
            "rfc" => "AAA010101AAA",
            "domicilioFiscal" => [
                "calle" => "Calle 25",
                "municipio" => "Monterrey",
                "estado" => "Nuevo Leon",
                "pais" => "Mexico",
                "codigoPostal" => "64450 "
            ],
            "expedidoEn" => [
                "calle" => "Calle 25",
                "municipio" => "Monterrey",
                "estado" => "Nuevo Leon",
                "pais" => "Mexico",
                "codigoPostal" => "64450 "
            ],
            "regimenFiscal" => [
                ["regimen" => "Empleado Honorarios"],
                ["regimen" => "Empleado222"]
            ],
        ],
        "receptor" => [
            "rfc" => "AOOM8309271A8",
            "nombre" => "Pedro Perez Hernandez",
            "domicilio" => [
                "calle" => "Calle 25",
                "municipio" => "Monterrey",
                "estado" => "Nuevo Leon",
                "pais" => "Mexico",
                "codigoPostal" => "64450 "
            ]
        ],
        "conceptos" => [
            [
                "cantidad" => "10.5",
                "unidad" => "Kg",
                "descripcion" => "Alambre calibre 22",
                "noIdentificacion" => "SK3218932190",
                "valorUnitario" => "10",
                "importe" => "10.5"
            ],
            [
                "cantidad" => "6",
                "unidad" => "1",
                "descripcion" => "Producto Importado",
                "valorUnitario" => "100",
                "importe" => "600",
                "noIdentificacion" => "SKU120312954"
            ],
            [
                "cantidad" => "1",
                "unidad" => "2",
                "descripcion" => "Pago PRedial Vivienda",
                "noIdentificacion" => "H22",
                "valorUnitario" => "1563.22",
                "importe" => "1563.22",
                "cuentaPredial" => [
                    "numero" => "PRE03185430011"
                ]
            ]
        ],
        "impuestos" => [
            "totalImpuestosRetenidos" => "100.22",
            "totalImpuestosTrasladados" => "89.11",
            "retenciones" =>[
                [
                    "impuesto" => "ISR",
                    "importe" => "12.33"
                ]
            ],
            "traslados" => [
                [
                    "impuesto" => "IVA",
                    "tasa" => "122.11",
                    "importe" => "12.33"
                ]
            ]
        ],
        "complemento" => [
            "impLocal" => [
                "totalDeRetenciones" => "22",
                "totalDeTraslados" => "11",
                "retencionesLocales" => [[
                    "impLocRetenido" => "21",
                    "tasaDeRetencion" => "1",
                    "importe" => "41"
                ]],
                "trasladosLocales" => [[
                    "impLocTrasladado" => "11",
                    "tasaDeTraslado" => "22",
                    "importe" => "33"
                ]]
            ]
        ]
    ];

    public function testCreateInvoice()
    {
        global $TESTING_CONFIG;
        global $Utilities;

        $id_user = $TESTING_CONFIG['id_user'];

        $user = new paybook\User(null, $id_user);

        $session = new paybook\Session($user);
            
        $taxpayer = self::TAXPAYER;
        $invoice_data = self::INVOICE_DATA;

        $date = new DateTime();
        $invoice_data['fecha'] = $date->format('Y-m-d\TH:i:s');
        $id_provider = self::ID_PROVIDER;
        $invoice = new paybook\Invoice($session,null, $taxpayer, $invoice_data,null, $id_provider);

        /*
        Check invoice instance type:
        */

        $this->assertInstanceOf(paybook\Invoice::class, $invoice);

        /*
        Check invoice instance structure and content:
        */
        $Utilities['assertAPIObject']($this, $TESTING_CONFIG['responses']['invoices'], $invoice);
    }
}
