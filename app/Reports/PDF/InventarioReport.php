<?php

namespace App\Reports\PDF;

use Codedge\Fpdf\Fpdf\Fpdf;

class InventarioReport extends Fpdf
{
    public string $headerTitle = '';

    public ?string $headerSubtitle = null;

    public ?string $footerImprecionDerecha = null;

    public string $family = 'Arial';

    // Encabezado
    public function Header(): void
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

    // Pie de página
    public function Footer(): void
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

    public function WriteHTML($html): void
    {
        // Intérprete de HTML muy básico
        $html = str_replace("\n", ' ', $html);
        $a = preg_split('/<(.*)>/U', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($a as $i => $e) {
            if ($i % 2 == 0) {
                // Texto normal
                $this->Write(7, utf8_decode($e));
            } else {
                // Etiqueta
                if ($e[0] == '/') {
                    $this->SetFont('Times', '', 12);
                } // Cerrar etiqueta
                else {
                    $this->SetFont('Times', 'B', 12);
                } // Abrir negrita
            }
        }
    }

    public function MultiCellTagJustificado($w, $h, $txt, $border = 0)
    {
        $x_inicio = $this->GetX();
        $y_inicio = $this->GetY();

        // 1. Normalizamos los saltos de línea y separamos por etiquetas
        $txt = str_replace("\r", '', $txt);
        $parts = preg_split('/(<.*>)/U', $txt, -1, PREG_SPLIT_DELIM_CAPTURE);

        $palabras_con_formato = [];
        $current_bold = false;

        foreach ($parts as $part) {
            if ($part == '<b>') {
                $current_bold = true;

                continue;
            }
            if ($part == '</b>') {
                $current_bold = false;

                continue;
            }

            // Buscamos palabras y saltos de línea explícitos
            // Usamos una expresión regular para mantener el \n como una "palabra" especial
            $words = preg_split('/(\s+)/', $part, -1, PREG_SPLIT_DELIM_CAPTURE);

            foreach ($words as $w_text) {
                if ($w_text === '') {
                    continue;
                }

                $palabras_con_formato[] = [
                    'text' => utf8_decode($w_text),
                    'bold' => $current_bold,
                    'is_newline' => (str_contains($w_text, "\n")),
                ];
            }
        }

        // 2. Lógica de Impresión con soporte para \n
        $linea_actual = [];
        $ancho_linea_actual = 0;
        $espacio_vacio = $this->GetStringWidth(' ');

        foreach ($palabras_con_formato as $palabra) {
            // Si encontramos un salto de línea manual (\n)
            if ($palabra['is_newline']) {
                $this->imprimirLineaJustificada($w, $h, $linea_actual, $ancho_linea_actual, true);
                $linea_actual = [];
                $ancho_linea_actual = 0;

                continue;
            }

            $this->SetFont('', $palabra['bold'] ? 'B' : '');
            $ancho_palabra = $this->GetStringWidth($palabra['text']);

            // Si la palabra no cabe en la línea actual (salto automático)
            if ($ancho_linea_actual + $ancho_palabra + $espacio_vacio > $w - 4) {
                $this->imprimirLineaJustificada($w, $h, $linea_actual, $ancho_linea_actual, false);
                $linea_actual = [];
                $ancho_linea_actual = 0;
            }

            if (trim($palabra['text']) !== '') {
                $linea_actual[] = $palabra;
                $ancho_linea_actual += $ancho_palabra + $espacio_vacio;
            }
        }

        // Imprimir la última línea del párrafo
        if (! empty($linea_actual)) {
            $this->imprimirLineaJustificada($w, $h, $linea_actual, $ancho_linea_actual, true);
        }

        $y_final = $this->GetY();

        // 3. Dibujar el borde completo
        if ($border) {
            $this->Rect($x_inicio, $y_inicio, $w, $y_final - $y_inicio);
        }

        $this->Ln(2); // Espacio de seguridad final
    }

    // Función auxiliar mejorada
    private function imprimirLineaJustificada($w, $h, $linea, $ancho_actual, $es_fin_de_parrafo)
    {
        if (empty($linea)) {
            $this->Ln($h);

            return;
        }

        $num_palabras = count($linea);

        // Si es la última línea de un párrafo o solo hay una palabra, NO justificamos (alineamos a la izquierda)
        if ($es_fin_de_parrafo || $num_palabras <= 1) {
            foreach ($linea as $p) {
                $this->SetFont('', $p['bold'] ? 'B' : '');
                $this->Write($h, $p['text'].' ');
            }
            $this->Ln($h);

            return;
        }

        // Justificación normal
        $espacio_total_libre = $w - 4 - ($ancho_actual - ($num_palabras * $this->GetStringWidth(' ')));
        $espacio_por_palabra = $espacio_total_libre / ($num_palabras - 1);

        foreach ($linea as $i => $p) {
            $this->SetFont('', $p['bold'] ? 'B' : '');
            $this->Write($h, $p['text']);
            if ($i < $num_palabras - 1) {
                $this->SetX($this->GetX() + $espacio_por_palabra);
            }
        }
        $this->Ln($h);
    }
}
