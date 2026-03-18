<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Despacho;
use App\Reports\PDF\DespachoPDF;
use App\Traits\AlmacenPDFTrait;
use Illuminate\Support\Str;

class DespachoController extends Controller
{
    use AlmacenPDFTrait;

    public function descargarDespacho($id)
    {
        // 1. Buscamos el Despacho con sus relaciones (si las tiene)
        $despacho = Despacho::find($id);
        if (! $despacho) {
            return modelNotFound('El Despacho no existe.');
        }

        // 2. Preparamos los datos dinámicos
        $this->datosDinamicos($despacho, false);

        // Obtenemos los rubros como array
        $rubros = $despacho->detalles->toArray();

        // --- LÓGICA DE PAGINACIÓN (11 ítems por página) ---
        $paginas = array_chunk($rubros, 11);

        $pdf = new DespachoPDF;
        $pdf->SetTitle(verUtf8('DESPACHO N.º '.Str::upper($despacho->numero)));
        $pdf->family = 'Times';
        $pdf->headerTitle = 'DESPACHO DE RUBROS';
        $pdf->headerSubtitle = Str::upper($this->model_plan);
        $pdf->footerImprecionDerecha = $this->model_plan;
        $pdf->texto = $this->texto;
        $pdf->codigo = $this->model_numero;
        $pdf->observacion = $this->model_observacion ?? '';
        $pdf->planCodigo = $this->model_planCodigo;

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
            $filasRestantes = 11 - count($itemsPagina);
            for ($i = 0; $i < $filasRestantes; $i++) {
                $this->dibujarFilaVacia($pdf, ++$num);
            }
        }

        return response($pdf->Output('I', 'depacho-'.$this->model_numero.'.pdf'), 200)
            ->header('Content-Type', 'application/pdf');

    }

    private function dibujarFila($pdf, $item, $num)
    {
        $tipo = $item['tipo_adquisicion'] == 'asignacion' ? 'ASIGNACIÓN' : 'PROPIO';
        $unidades = $item['cantidad_unidades'] ? formatoMillares($item['cantidad_unidades'], 0) : 'MERMA';
        $pdf->SetFont('Times', 'B', 9);
        $pdf->Cell(7, 10, verUtf8($num), 1, 0, 'C');
        $pdf->Cell(35, 10, verUtf8($tipo), 1, 0, 'C');
        $pdf->Cell(78, 10, verUtf8(Str::upper($item['rubros_nombre'])), 1, 0, 'C');
        $pdf->SetFont('Times', 'B', 11);
        $pdf->Cell(20, 10, verUtf8($unidades), 1, 0, 'C');
        $pdf->Cell(20, 10, verUtf8(formatoMillares($item['peso_unitario'])), 1, 0, 'C');
        $pdf->Cell(30, 10, verUtf8((formatoMillares($item['total'])).' '.$item['rubros_unidad_medida']), 1, 1, 'C');
    }

    private function dibujarFilaVacia($pdf, $num)
    {
        $pdf->SetFont('Times', 'B', 9);
        $pdf->Cell(7, 10, $num, 1, 0, 'C');
        $pdf->Cell(35, 10, '', 1, 0, 'C');
        $pdf->Cell(78, 10, '', 1, 0, 'C');
        $pdf->Cell(20, 10, '', 1, 0, 'C');
        $pdf->Cell(20, 10, '', 1, 0, 'C');
        $pdf->Cell(30, 10, '', 1, 1, 'C');
    }
}
