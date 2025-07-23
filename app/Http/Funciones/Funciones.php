<?php
//Funciones Personalizadas para el Proyecto

function sweetAlert2(array $parametros = []): void
{
    session()->flash('sweetAlert2', $parametros);
}

function cerosIzquierda($cantidad, $cantCeros = 2): int|string
{
    if ($cantidad == 0) {
        return 0;
    }
    return str_pad($cantidad, $cantCeros, "0", STR_PAD_LEFT);
}

function numSizeCodigo(): int
{
    $num = 6;
    $parametro = \App\Models\Parametro::where('nombre', 'size_codigo')->first();
    if ($parametro){
        if (!empty($parametro->valor_id) && $parametro->valor_id >= 1){
            $num = intval($parametro->valor_id);
        }
    }
    return $num;
}

function verImagen($path, $user = false): string
{
    if (!is_null($path)){
        if (file_exists(public_path('storage/'.$path))){
            return asset('storage/'.$path);
        }else{
            if ($user){
                return asset('img/user_placeholder.png');
            }else{
                return asset('img/placeholder.jpg');
            }
        }
    }else{
        if ($user){
            return asset('img/user_placeholder.png');
        }else{
            return asset('img/placeholder.jpg');
        }
    }
}

function verImagenStoragePath($path): string
{
    $response = public_path('img/placeholder.jpg');
    if (!empty($path)) {
        $existe = file_exists(public_path('storage/' . $path));
        if ($existe) {
            $response = storage_path('app/public/' . $path);
        }
    }
    return $response;
}

function getFecha($fecha, $format = null): string
{
    if (is_null($fecha)) {
        if (is_null($format)) {
            $date = \Carbon\Carbon::now(env('APP_TIMEZONE', "America/Caracas"))->toDateString();
        } else {
            $date = \Carbon\Carbon::now(env('APP_TIMEZONE', "America/Caracas"))->format($format);
        }
    } else {
        if (is_null($format)) {
            $date = \Carbon\Carbon::parse($fecha)->format("d/m/Y");
        } else {
            $date = \Carbon\Carbon::parse($fecha)->format($format);
        }
    }
    return $date;
}

function verUtf8($string, $safeNull = false): string
{
    //$utf8_string = "Some UTF-8 encoded BATE QUEBRADO ÑñíÍÁÜ niño ó Ó string: é, ö, ü";
    $response = null;
    $text = 'NULL';
    if ($safeNull) {
        $text = '';
    }
    if (!is_null($string)) {
        $response = mb_convert_encoding($string, 'ISO-8859-1', 'UTF-8');
    }
    if (!is_null($response)) {
        $text = "$response";
    }
    return $text;
}

function formatoMillares($cantidad, $decimal = 2): string
{
    if (!is_numeric($cantidad)){
        $cantidad = 0;
    }
    return number_format($cantidad, $decimal, ',', '.');
}

function qrCodeGenerate(string $content = null, int $size = null, int $margin = null, string $filename = null, string $encoding = null, array $backgroundColor = null, array $foregroundColor = null, string $path = null): string
{
    $content = $content ?? 'Hello World!';
    $size = $size ?? 400;
    $margin = $margin ?? 4;
    $path = $path ? 'storage/'.$path.'/' : 'storage/images-qr/';
    $filename = $filename ? \Illuminate\Support\Str::slug($filename) : 'qrcode';
    $encoding = $encoding ?? \BaconQrCode\Encoder\Encoder::DEFAULT_BYTE_MODE_ENCODING;

    $backgroundColorRed = 255;
    $backgroundColorGreen = 255;
    $backgroundColorBlue = 255;

    $foregroundColorRed = 0;
    $foregroundColorGreen = 0;
    $foregroundColorBlue = 0;

    if (!empty($backgroundColor)){
        $backgroundColorRed = $backgroundColor[0] ?? $backgroundColorRed;
        $backgroundColorGreen = $backgroundColor[1] ?? $backgroundColorGreen;
        $backgroundColorBlue = $backgroundColor[2] ?? $backgroundColorBlue;
    }

    if (!empty($foregroundColor)){
        $foregroundColorRed = $foregroundColor[0] ?? $foregroundColorRed;
        $foregroundColorGreen = $foregroundColor[1] ?? $foregroundColorGreen;
        $foregroundColorBlue = $foregroundColor[2] ?? $foregroundColorBlue;
    }

    if (!extension_loaded('imagick')){
        $imageBackEnd = new \BaconQrCode\Renderer\Image\SvgImageBackEnd();
        $extension = '.svg';
    }else{
        $imageBackEnd  = new \BaconQrCode\Renderer\Image\ImagickImageBackEnd();
        $extension = '.png';
    }

    $module = new \BaconQrCode\Renderer\Module\RoundnessModule(\BaconQrCode\Renderer\Module\RoundnessModule::SOFT);
    $eye = new \BaconQrCode\Renderer\Eye\CompositeEye(\BaconQrCode\Renderer\Eye\PointyEye::instance(), \BaconQrCode\Renderer\Eye\SquareEye::instance());

    $renderer = new \BaconQrCode\Renderer\ImageRenderer(
        new \BaconQrCode\Renderer\RendererStyle\RendererStyle(
            $size,
            $margin,
            $module,
            $eye,
            \BaconQrCode\Renderer\RendererStyle\Fill::uniformColor(
                backgroundColor: new \BaconQrCode\Renderer\Color\Rgb($backgroundColorRed,$backgroundColorGreen,$backgroundColorBlue),
                foregroundColor: new \BaconQrCode\Renderer\Color\Rgb($foregroundColorRed,$foregroundColorGreen, $foregroundColorBlue)
            )
        ),
        imageBackEnd: $imageBackEnd,
    );
    $write = new \BaconQrCode\Writer($renderer);
    $write->writeFile($content, $path . $filename . $extension, $encoding);

    return asset($path . $filename . $extension);

}

function qrCodeGenerateFPDF(string $content = null, int $size = null, int $margin = null, string $filename = null, string $encoding = null, array $backgroundColor = null, array $foregroundColor = null, string $path = null): string
{
    if (!extension_loaded('imagick')){

        $content = $content ?? 'Hello World!';
        $size = $size ?? 400;
        $path = $path ? 'storage/'.$path.'/' : 'storage/images-qr/';
        $filename = $filename ? \Illuminate\Support\Str::slug($filename) : 'qrcode';

        $renderer = new \BaconQrCode\Renderer\GDLibRenderer($size);
        $writer = new \BaconQrCode\Writer($renderer);
        $writer->writeFile($content, $path . $filename . '.png');

        return asset($path . $filename . '.png');

    }else{
        return qrCodeGenerate($content, $size, $margin, $filename, $encoding, $backgroundColor, $foregroundColor, $path);
    }
}

