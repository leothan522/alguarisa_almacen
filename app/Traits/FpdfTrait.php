<?php

namespace App\Traits;

trait FpdfTrait
{
    public function MultiCellTagJustificado($w, $h, $txt, $border = 0): void
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
                    'text' => verUtf8($w_text),
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
    private function imprimirLineaJustificada($w, $h, $linea, $ancho_actual, $es_fin_de_parrafo): void
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

    public function WriteHTML($html): void
    {
        // Intérprete de HTML muy básico
        $html = str_replace("\n", ' ', $html);
        $a = preg_split('/<(.*)>/U', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($a as $i => $e) {
            if ($i % 2 == 0) {
                // Texto normal
                $this->Write(7, verUtf8($e));
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

}
