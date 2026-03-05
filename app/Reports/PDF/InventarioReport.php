<?php

namespace App\Reports\PDF;

use App\Traits\FpdfTrait;
use Codedge\Fpdf\Fpdf\Fpdf;

class InventarioReport extends Fpdf
{
    use FpdfTrait;

    public string $headerTitle = '';

    public ?string $headerSubtitle = null;

    public ?string $footerImprecionDerecha = null;

    public string $family = 'Arial';

    // Encabezado
    public function Header(): void
    {
        $this->headerBase();
    }

    // Pie de página
    public function Footer(): void
    {
        $this->footerBase();
    }

    public function headerBase(): void
    {
        // Logo (Si tienes uno en public/img/logo.png)
        $this->Image(public_path('img/pdf/membrete-recepcion.png'), 11, 9, 190);
        $this->Ln($this->headerSubtitle ? 6 : 9);

        $this->SetFont($this->family, 'B', 14);
        $this->SetTextColor(33, 37, 41); // Color gris oscuro tipo Bootstrap

        // Título del reporte
        $this->Cell(0, 7, verUtf8($this->headerTitle), 0, 1, 'C');
        if ($this->headerSubtitle) {
            $this->Cell(0, 7, verUtf8($this->headerSubtitle), 0, 1, 'C');
        }

        // Salto de línea
        $this->Ln(7);
        /* // Línea decorativa
        $this->SetDrawColor(0, 123, 255); // Azul primario
        $this->Line(10, 28, 200, 28);*/
    }

    public function footerBase(): void
    {
        // Posición a 1.5 cm del final
        $this->SetY(-15);
        $this->SetFont($this->family, 'I', 8);
        $this->SetTextColor(108, 117, 125);

        // Número de página
        $this->Cell(0, 10, verUtf8('Página ').$this->PageNo().'/{nb}', 0, 0, 'C');

        // Fecha de impresión a la derecha
        $this->Cell(0, 10, verUtf8($this->footerImprecionDerecha ?? date('d/m/Y h:i a')), 0, 0, 'R');
    }
}
