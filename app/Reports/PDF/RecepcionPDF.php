<?php

namespace App\Reports\PDF;

class RecepcionPDF extends InventarioReport
{
    public string $texto = '';
    public string $codigo = '';

    public function Header(): void
    {
        $this->headerBase();

        $this->SetFont('Times', '', 12);

        // Parrafo del Acta de Recepcion
        $this->MultiCellTagJustificado(190, 7, $this->texto, 1);

        // Tabla de Rubros
        $this->SetTextColor(255, 0, 0);
        $this->SetFont('Times', 'B', 12);
        $this->Cell(0, 10, verUtf8('N.º RECEPCIÓN: '.$this->codigo.'    '), 1, 1, 'R');

        $this->SetFont('Times', 'B', 9);
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(0, 176, 80);
        $this->Cell(7, 10, verUtf8('N.º'), 1, 0, 'C', 1);
        $this->Cell(20, 10, verUtf8('F.F.'), 1, 0, 'C', 1);
        $this->Cell(20, 10, verUtf8('F.V.'), 1, 0, 'C', 1);
        $this->Cell(73, 10, verUtf8('DESCRIPCIÓN FÍSICA DEL PRODUCTO'), 1, 0, 'C', 1);
        $this->Cell(20, 10, verUtf8('CANTIDAD'), 1, 0, 'C', 1);
        $this->Cell(20, 10, verUtf8('PESO UND'), 1, 0, 'C', 1);
        $this->Cell(30, 10, verUtf8('PESO TOTAL'), 1, 1, 'C', 1);
    }
}
