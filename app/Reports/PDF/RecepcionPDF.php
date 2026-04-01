<?php

namespace App\Reports\PDF;

class RecepcionPDF extends InventarioReport
{
    public string $texto = '';

    public string $codigo = '';

    public string $observacion = '';

    public string $planCodigo = '';

    public bool $ajuste = false;

    public function Header(): void
    {
        $this->headerBase();

        $this->SetFont('Times', '', 12);

        // Parrafo del Acta de Recepcion
        $this->MultiCellTagJustificado(190, 7, $this->texto, 1);

        // Tabla de Rubros
        $this->SetTextColor(255, 0, 0);
        $this->SetFont('Times', 'B', 12);
        $label = $this->ajuste ? 'AJUSTE' : 'RECEPCIÓN';
        $this->Cell(0, 10, verUtf8('N.º '.$label.': '.$this->codigo.'    '), 1, 1, 'R');

        $this->SetFont('Times', 'B', 9);
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(161, 208, 73);
        $this->Cell(7, 10, verUtf8('N.º'), 1, 0, 'C', 1);
        $this->Cell(20, 10, verUtf8('F.F.'), 1, 0, 'C', 1);
        $this->Cell(20, 10, verUtf8('F.V.'), 1, 0, 'C', 1);
        $this->Cell(73, 10, verUtf8('DESCRIPCIÓN FÍSICA DEL PRODUCTO'), 1, 0, 'C', 1);
        $this->Cell(20, 10, verUtf8('CANTIDAD'), 1, 0, 'C', 1);
        $this->Cell(20, 10, verUtf8('PESO UND'), 1, 0, 'C', 1);
        $this->Cell(30, 10, verUtf8('PESO TOTAL'), 1, 1, 'C', 1);
    }

    public function Footer(): void
    {
        $this->footerBase();
        $this->SetY(208);
        $this->SetTextColor(0, 0, 0);

        $this->SetFont('Times', 'BU', 8);
        $textp = 'OBSERVACIÓN: '.$this->observacion;
        $this->MultiCell(140, 5, verUtf8($textp));

        $this->SetFont('Times', 'B', 8);
        $this->SetXY(150, 208);
        $this->Cell(0, 5, 'SELLO', 0, 1, 'C');
        $this->Rect(150, 208, 50, 74);
        $this->Line(10, 208, 10, 228);
        $this->Rect(150, 208, 50, 74);
        $this->Line(10, 208, 10, 228);
        $this->Line(10, 208, 150, 208);

        $this->SetY(228);
        $this->SetFont('Times', '', 6);
        $this->Cell(47, 3, 'NOMBRE - FIRMA - CEDULA', 0, 0, 'C');
        $this->Cell(46, 3, 'NOMBRE - FIRMA - CEDULA', 0, 0, 'C');
        $this->Cell(47, 3, 'NOMBRE - FIRMA - CEDULA', 0, 1, 'C');
        $this->Cell(47, 3, 'QUIEN ENTREGA', 0, 0, 'C');
        $this->Cell(46, 3, verUtf8('RESPONSABLE DE ALMACÉN'), 0, 0, 'C');
        $this->Cell(47, 3, 'QUIEN RECIBE', 0, 1, 'C');
        $this->Ln(27);

        $this->Rect(10, 228, 47, 33);
        $this->Rect(57, 228, 46, 33);
        $this->Rect(103, 228, 47, 33);
        $this->Rect(10, 228, 47, 33);
        $this->Rect(57, 228, 46, 33);
        $this->Rect(103, 228, 47, 33);

        $this->Cell(70, 3, 'NOMBRE - FIRMA - CEDULA', 0, 0, 'C');
        $this->Cell(70, 3, $this->planCodigo == 'BM' ? 'NOMBRE - FIRMA - CEDULA' : '', 0, 1, 'C');
        $this->Cell(70, 3, 'SEGURIDAD', 0, 0, 'C');
        $this->Cell(70, 3, $this->planCodigo == 'BM' ? 'INSPECTOR DE CUSPAL' : '', 0, 1, 'C');

        $this->Rect(10, 261, 70, 21);
        $this->Rect(80, 261, 70, 21);
        $this->Rect(10, 261, 70, 21);
        $this->Rect(80, 261, 70, 21);
    }
}
