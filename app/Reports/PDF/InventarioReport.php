<?php

namespace App\Reports\PDF;

use Codedge\Fpdf\Fpdf\Fpdf;

class InventarioReport extends Fpdf
{
    // Encabezado
    public function Header(): void
    {
        // Logo (Si tienes uno en public/img/logo.png)
        // $this->Image(public_path('img/logo.png'), 10, 8, 33);

        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(33, 37, 41); // Color gris oscuro tipo Bootstrap

        // Mover a la derecha
        $this->Cell(80);

        // Título del reporte
        $this->Cell(30, 10, verUtf8('SISTEMA DE INVENTARIO - MI EMPRESA'), 0, 0, 'C');

        // Salto de línea
        $this->Ln(20);

        // Línea decorativa
        $this->SetDrawColor(0, 123, 255); // Azul primario
        $this->Line(10, 28, 200, 28);
    }

    // Pie de página
    public function Footer(): void
    {
        // Posición a 1.5 cm del final
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(108, 117, 125);

        // Número de página
        $this->Cell(0, 10, verUtf8('Página ').$this->PageNo().'/{nb}', 0, 0, 'C');

        // Fecha de impresión a la derecha
        $this->Cell(0, 10, date('d/m/Y H:i'), 0, 0, 'R');
    }
}
