<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Reports\PDF\InventarioReport;

class RecepcionController extends Controller
{
    public function descargarRecepcion($id)
    {
        // Instanciamos nuestra clase personalizada
        $pdf = new InventarioReport;
        $pdf->SetTitle('hola yonahhanan');
        $pdf->AliasNbPages(); // Necesario para el pie de página
        $pdf->AddPage();

        $pdf->SetFont('Times', '', 12);

        // Contenido específico de la recepción
        $pdf->Cell(0, 10, "Detalles de la Recepcion ID: $id", 0, 1);

        // Generar muchas líneas para probar el salto de página automático
        for ($i = 1; $i <= 40; $i++) {
            $pdf->Cell(0, 10, "Linea de producto de prueba numero $i", 0, 1);
        }

        return response($pdf->Output('I', 'recepcion-001.pdf'), 200)
            ->header('Content-Type', 'application/pdf');
    }
}
