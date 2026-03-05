<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Recepcion;
use App\Reports\PDF\InventarioReport;
use Illuminate\Support\Str;

class RecepcionController extends Controller
{
    public function descargarRecepcion($id)
    {
        // 1. Buscamos la recepción con sus relaciones (si las tiene)
        $recepcion = Recepcion::find($id);

        if (! $recepcion) {
            return modelNotFound('La recepción no existe.');
        }

        // Instanciamos nuestra clase personalizada
        $pdf = new InventarioReport;
        $pdf->SetTitle(verUtf8('RECEPCIÓN N.º '.Str::upper($recepcion->numero)));
        $pdf->family = 'Times';
        $pdf->headerTitle = 'ACTA DE CONTROL PERCEPTIVO';
        $pdf->headerSubtitle = 'RECEPCIÓN DE RUBROS';
        $pdf->AliasNbPages(); // Necesario para el pie de página
        $pdf->AddPage();

        // Contenido específico
        $pdf->SetFont('Times', '', 12);

        // 2. Preparamos los datos dinámicos
        $jefe_nombre = Str::upper($recepcion->jefes_nombre ?? '_____________________');
        $jefe_cedula = Str::upper($recepcion->jefes_cedula ?? '_____________________');
        $hora = date('h:i a', strtotime($recepcion->hora)); // Formato 8:00 am
        $dia = date('d', strtotime($recepcion->fecha));
        $mes = date('m', strtotime($recepcion->fecha));
        $anio = date('Y', strtotime($recepcion->fecha));
        $responsable_nombre = Str::upper($recepcion->responsables_nombre ?? '_____________________');
        $responsable_cedula = Str::upper($recepcion->responsables_cedula ?? '_____________________');
        $responsable_empresa = Str::upper($recepcion->responsables_empresa ?? '_____________________');
        $responsable_telefono = Str::upper($recepcion->responsables_telefono ?? '_____________________');
        $recepcion_numero = Str::upper($recepcion->numero) ?? '__________';

        // Parrafo del Acta de Recepcion
        $texto = "Quien suscribe, <b>$jefe_nombre</b>, titular de la Cédula de la Identidad N.º <b>$jefe_cedula</b> en su carácter de Responsable del Almacén de Rubros de <b>ALIMENTOS DEL GUARICO S.A.</b>; siendo las: <b>$hora</b>; del día: <b>$dia / $mes / $anio</b>; en presencia de quien entrega el material señalado en este documento, ciudadano: <b>$responsable_nombre</b>; titular de la Cédula de la Identidad o RIF: <b>$responsable_cedula</b>; perteneciente a la institución o empresa: <b>$responsable_empresa</b>, Teléfono: <b>$responsable_telefono</b>.\n";
        $texto .= 'El bien y/o servicio, que a continuación se describe, dejando constancia, para los efectos inherentes al proceso de pago:';
        $pdf->MultiCellTagJustificado(190, 7, $texto, 1);

        // Tabla de Rubros
        $pdf->SetFont('Times', 'B', 12);
        $pdf->Cell(0, 10, verUtf8('N.º RECEPCIÓN: '.$recepcion_numero), 1, 1, 'R');

        for ($i = 0; $i < 10; $i++) {
            $pdf->Cell(0, 10, verUtf8('N.º RECEPCIÓN: '.$recepcion_numero), 1, 1, 'R');
        }

        // Espacio después del párrafo
        $pdf->Ln(10);

        return response($pdf->Output('I', 'recepcion-001.pdf'), 200)
            ->header('Content-Type', 'application/pdf');
    }
}
