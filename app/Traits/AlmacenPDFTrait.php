<?php

namespace App\Traits;

use App\Models\Despacho;
use App\Models\Recepcion;
use Illuminate\Support\Str;

trait AlmacenPDFTrait
{
    public string $model_numero;

    public string $model_observacion;

    public mixed $model_plan;

    public mixed $model_planCodigo;

    public string $texto;

    public function datosDinamicos(Recepcion|Despacho $model, $entrega = true, $nota = false): void
    {
        $jefe_nombre = Str::upper($model->jefes_nombre ?? '_____________________');
        $jefe_cedula = Str::upper($model->jefes_cedula ?? '_____________________');
        $hora = date('h:i A', strtotime($model->hora)); // Formato 8:00 am
        $dia = date('d', strtotime($model->fecha));
        $mes = date('m', strtotime($model->fecha));
        $anio = date('Y', strtotime($model->fecha));
        $responsable_nombre = Str::upper($model->responsables_nombre ?? '_____________________');
        $responsable_cedula = Str::upper($model->responsables_cedula ? formatoMillares($model->responsables_cedula, 0) : '_____________________');
        $responsable_empresa = Str::upper($model->responsables_empresa ?? '_____________________');
        $responsable_telefono = Str::upper($model->responsables_telefono ?? '___________');
        $this->model_numero = Str::upper($model->numero) ?? '__________';
        $this->model_observacion = Str::upper($model->observacion);
        $this->model_plan = $model->plan->nombre;
        $this->model_planCodigo = $model->plan->codigo;

        $etiqueta = $entrega ? 'entrega' : 'recibe';

        if (! $nota) {
            $this->texto = "Quien suscribe, <b>$jefe_nombre;</b> titular de la Cédula de la Identidad N.º <b>$jefe_cedula</b> en su carácter de Responsable del Almacén de Rubros de <b>ALIMENTOS DEL GUARICO S.A;</b> siendo las: <b>$hora;</b> del día: <b>$dia / $mes / $anio;</b> en presencia de quien $etiqueta el material señalado en este documento, ciudadano: <b>$responsable_nombre;</b> titular de la Cédula de Identidad o RIF: <b>$responsable_cedula;</b> perteneciente a la institución o empresa: <b>$responsable_empresa,</b> Teléfono: <b>$responsable_telefono.</b>\n";
            $this->texto .= 'El bien y/o servicio, que a continuación se describe, dejando constancia, para los efectos inherentes al proceso de pago:';
        } else {
            $hora = date('h:i A'); // Formato 8:00 am
            $dia = date('d');
            $mes = date('m');
            $anio = date('Y');
            $this->texto = "Quien suscribe, <b>$jefe_nombre;</b> titular de la Cédula de la Identidad N.º <b>$jefe_cedula</b> en su carácter de Responsable del Almacén de Rubros de <b>ALIMENTOS DEL GUARICO S.A;</b> siendo las: <b>$hora;</b> del día: <b>$dia / $mes / $anio;</b> en presencia de quien entrega la <b>RELACIÓN DE PRODUCTOS VENDIDOS EN BODEGA MÓVIL</b> señalado en este documento, ciudadano: <b>$responsable_nombre;</b> titular de la Cédula de la Identidad o RIF: <b>$responsable_cedula;</b> perteneciente a la institución o empresa: <b>$responsable_empresa,</b> Teléfono: <b>$responsable_telefono.</b>\n Los productos vendidos, a continuación se describen, dejando constancia:";
        }
    }
}
