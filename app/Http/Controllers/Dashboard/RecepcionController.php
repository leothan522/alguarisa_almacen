<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Recepcion;
use App\Reports\PDF\RecepcionPDF;
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

        // 2. Preparamos los datos dinámicos
        $jefe_nombre = Str::upper($recepcion->jefes_nombre ?? '_____________________');
        $jefe_cedula = Str::upper($recepcion->jefes_cedula ?? '_____________________');
        $hora = date('h:i A', strtotime($recepcion->hora)); // Formato 8:00 am
        $dia = date('d', strtotime($recepcion->fecha));
        $mes = date('m', strtotime($recepcion->fecha));
        $anio = date('Y', strtotime($recepcion->fecha));
        $responsable_nombre = Str::upper($recepcion->responsables_nombre ?? '_____________________');
        $responsable_cedula = Str::upper($recepcion->responsables_cedula ? formatoMillares($recepcion->responsables_cedula, 0) : '_____________________');
        $responsable_empresa = Str::upper($recepcion->responsables_empresa ?? '_____________________');
        $responsable_telefono = Str::upper($recepcion->responsables_telefono ?? '_____________________');
        $recepcion_numero = Str::upper($recepcion->numero) ?? '__________';
        $recepcion_observacion = Str::upper($recepcion->observacion);


        // Obtenemos los rubros como array
        $rubros = $recepcion->items->toArray();

        // --- LÓGICA DE PAGINACIÓN (10 ítems por página) ---
        $paginas = array_chunk($rubros, 10);

        $texto = "Quien suscribe, <b>$jefe_nombre;</b> titular de la Cédula de la Identidad N.º <b>$jefe_cedula</b> en su carácter de Responsable del Almacén de Rubros de <b>ALIMENTOS DEL GUARICO S.A;</b> siendo las: <b>$hora;</b> del día: <b>$dia / $mes / $anio;</b> en presencia de quien entrega el material señalado en este documento, ciudadano: <b>$responsable_nombre;</b> titular de la Cédula de la Identidad o RIF: <b>$responsable_cedula;</b> perteneciente a la institución o empresa: <b>$responsable_empresa,</b> Teléfono: <b>$responsable_telefono.</b>\n";
        $texto .= 'El bien y/o servicio, que a continuación se describe, dejando constancia, para los efectos inherentes al proceso de pago:';

        // Instanciamos nuestra clase personalizada
        $pdf = new RecepcionPDF;
        $pdf->SetTitle(verUtf8('RECEPCIÓN N.º '.Str::upper($recepcion->numero)));
        $pdf->family = 'Times';
        $pdf->headerTitle = 'ACTA DE CONTROL PERCEPTIVO';
        $pdf->headerSubtitle = 'RECEPCIÓN DE RUBROS';
        $pdf->texto = $texto;
        $pdf->codigo = $recepcion_numero;
        $pdf->observacion = $recepcion_observacion ?? '';
        $pdf->AliasNbPages(); // Necesario para el pie de página

        // Contenido
        $num = 0;
        foreach ($paginas as $indicePagina => $itemsPagina) {
            $pdf->AddPage();

            // Dibujamos los items actuales (hasta 10)
            foreach ($itemsPagina as $item) {
                $this->dibujarFila($pdf, $item, ++$num);
            }

            // --- RELLENO DE FILAS VACÍAS ---
            $filasRestantes = 10 - count($itemsPagina);
            for ($i = 0; $i < $filasRestantes; $i++) {
                $this->dibujarFilaVacia($pdf, ++$num);
            }
        }

        return response($pdf->Output('I', 'recepcion-001.pdf'), 200)
            ->header('Content-Type', 'application/pdf');
    }

    /**
     * Dibuja una fila con datos reales
     */
    private function dibujarFila($pdf, $item, $num)
    {
        $pdf->SetFont('Times', 'B', 9);
        $pdf->Cell(7, 10, verUtf8($num), 1, 0, 'C');
        $pdf->Cell(20, 10, verUtf8(getFecha($item['fecha_fabricacion'], 'd/m/Y')), 1, 0, 'C'); // Aquí iría $item['f_fab']
        $pdf->Cell(20, 10, verUtf8(getFecha($item['fecha_vencimiento'], 'd/m/Y')), 1, 0, 'C'); // Aquí iría $item['f_venc']
        $pdf->Cell(73, 10, verUtf8(Str::upper($item['rubros_nombre'])), 1, 0, 'C');
        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(20, 10, verUtf8(formatoMillares($item['cantidad_unidades'], 0)), 1, 0, 'C');
        $pdf->Cell(20, 10, verUtf8(formatoMillares($item['peso_unitario'])), 1, 0, 'C');
        $pdf->Cell(30, 10, verUtf8((formatoMillares($item['total'])).' KG'), 1, 1, 'C');
    }

    /**
     * Dibuja una fila vacía para completar el formato
     */
    private function dibujarFilaVacia($pdf, $num)
    {
        $pdf->SetFont('Times', 'B', 9);
        $pdf->Cell(7, 10, $num, 1, 0, 'C');
        $pdf->Cell(20, 10, '', 1, 0, 'C');
        $pdf->Cell(20, 10, '', 1, 0, 'C');
        $pdf->Cell(73, 10, '', 1, 0, 'C');
        $pdf->Cell(20, 10, '', 1, 0, 'C');
        $pdf->Cell(20, 10, '', 1, 0, 'C');
        $pdf->Cell(30, 10, '', 1, 1, 'C');
    }
}
