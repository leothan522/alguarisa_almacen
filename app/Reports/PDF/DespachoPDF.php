<?php

namespace App\Reports\PDF;

class DespachoPDF extends InventarioReport
{
    public string $texto = '';

    public string $codigo = '';

    public string $observacion = '';

    public string $planCodigo = '';

    public bool $devolucion = false;

    public bool $nota = false;

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
        $tipo = ! $this->nota ? $this->devolucion ? 'DEVOLUCIÓN' : 'DESPACHO' : 'NOTA';
        if ($this->ajuste){
            $tipo = 'AJUSTE';
        }
        $this->Cell(0, 10, verUtf8('N.º '.$tipo.': '.$this->codigo.'    '), 1, 1, 'R');

        $this->SetFont('Times', 'B', 9);
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(161, 208, 73);
        $this->Cell(7, 10, verUtf8('N.º'), 1, 0, 'C', 1);
        $this->Cell(35, 10, verUtf8('TIPO ADQUISICIÓN'), 1, 0, 'C', 1);
        $this->Cell(78, 10, verUtf8('DESCRIPCIÓN FÍSICA DEL PRODUCTO'), 1, 0, 'C', 1);
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
        $this->Rect(150, 208, 50, 74);
        $this->Line(10, 208, 10, 248);
        $this->Line(10, 208, 10, 248);
        $this->Line(10, 208, 150, 208);

        $this->SetY(228);
        $this->Line(10, 228, 150, 228);
        $this->SetFont('Times', 'B', 9);
        $this->Cell(140, 10, 'AUTORIZADO POR: HUMBERTO ALBANI', 1, 1, 'C');
        $this->SetFont('Times', '', 6);
        $this->Cell(47, 3, 'NOMBRE - FIRMA - CEDULA', 0, 0, 'C');
        $this->Cell(46, 3, 'NOMBRE - FIRMA - CEDULA', 0, 0, 'C');
        $this->Cell(47, 3, 'NOMBRE - FIRMA - CEDULA', 0, 1, 'C');
        $this->Cell(47, 3, 'QUIEN ENTREGA', 0, 0, 'C');
        $this->Cell(46, 3, verUtf8('RESPONSABLE DE ALMACÉN'), 0, 0, 'C');
        $this->Cell(47, 3, 'QUIEN RECIBE', 0, 1, 'C');
        $this->Ln(22);

        $this->Rect(10, 238, 47, 27);
        $this->Rect(57, 238, 46, 27);
        $this->Rect(103, 238, 47, 27);
        $this->Rect(10, 238, 47, 27);
        $this->Rect(57, 238, 46, 27);
        $this->Rect(103, 238, 47, 27);

        $this->Cell(70, 3, 'NOMBRE - FIRMA - CEDULA', 0, 1, 'C');
        $this->Cell(70, 3, 'SEGURIDAD', 0, 0, 'C');

        $this->Rect(10, 265, 70, 17);
        $this->Rect(80, 265, 70, 17);
        $this->Rect(10, 265, 70, 17);
        $this->Rect(80, 265, 70, 17);

    }
}
