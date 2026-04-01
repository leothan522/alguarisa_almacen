<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Recepcion;
use App\Reports\PDF\RecepcionPDF;
use App\Traits\AlmacenPDFTrait;
use Illuminate\Support\Str;

class RecepcionController extends Controller
{
    use AlmacenPDFTrait;

    public function descargarRecepcion($id)
    {
        // 1. Buscamos la recepción con sus relaciones (si las tiene)
        $recepcion = Recepcion::find($id);

        if (! $recepcion) {
            return modelNotFound('La recepción no existe.');
        }

        // 2. Preparamos los datos dinámicos
        $this->datosDinamicos($recepcion);

        // Obtenemos los rubros como array
        $rubros = $recepcion->items->toArray();

        // --- LÓGICA DE PAGINACIÓN (10 ítems por página) ---
        $paginas = array_chunk($rubros, 10);

        // Instanciamos nuestra clase personalizada
        $pdf = new RecepcionPDF;
        $label = $recepcion->is_adjustment ? 'AJUSTE' : 'RECEPCIÓN';
        $pdf->SetTitle(verUtf8($label.' N.º '.Str::upper($recepcion->numero)));
        $pdf->family = 'Times';
        $pdf->headerTitle = $recepcion->is_adjustment ? 'ENTRADA POR AJUSTE' : 'ACTA DE CONTROL PERCEPTIVO';
        $pdf->headerSubtitle = $recepcion->is_adjustment ? Str::upper($this->model_plan) : 'RECEPCIÓN DE RUBROS';
        $pdf->footerImprecionDerecha = $this->model_plan;
        $pdf->texto = $this->texto;
        $pdf->codigo = $this->model_numero;
        $pdf->observacion = $this->model_observacion ?? '';
        $pdf->planCodigo = $this->model_planCodigo;
        $pdf->ajuste = $recepcion->is_adjustment;

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
        $label = $recepcion->is_adjustment ? 'ajuste-' : 'recepcion-';

        return response($pdf->Output('I', $label.$this->model_numero.'.pdf'), 200)
            ->header('Content-Type', 'application/pdf');
    }

    /**
     * Dibuja una fila con datos reales
     */
    private function dibujarFila($pdf, $item, $num)
    {
        $fabricacion = $item['fecha_fabricacion'] ? getFecha($item['fecha_fabricacion'], 'd/m/Y') : '';
        $vencimiento = $item['fecha_vencimiento'] ? getFecha($item['fecha_vencimiento'], 'd/m/Y') : '';
        $pdf->SetFont('Times', 'B', 9);
        $pdf->Cell(7, 10, verUtf8($num), 1, 0, 'C');
        $pdf->Cell(20, 10, verUtf8($fabricacion), 1, 0, 'C'); // Aquí iría $item['f_fab']
        $pdf->Cell(20, 10, verUtf8($vencimiento), 1, 0, 'C'); // Aquí iría $item['f_venc']
        $pdf->Cell(73, 10, verUtf8(Str::upper($item['rubros_nombre'])), 1, 0, 'C');
        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(20, 10, verUtf8(formatoMillares($item['cantidad_unidades'], 0)), 1, 0, 'C');
        $pdf->Cell(20, 10, verUtf8(formatoMillares($item['peso_unitario'])), 1, 0, 'C');
        $pdf->Cell(30, 10, verUtf8((formatoMillares($item['total'])).' '.$item['rubros_unidad_medida']), 1, 1, 'C');
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
