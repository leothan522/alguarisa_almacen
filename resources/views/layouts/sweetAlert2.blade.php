@if(session()->has('sweetAlert2'))
    {!! json_encode(session('sweetAlert2')) !!}
    <script type="application/javascript">
        window.onload = function () {
            SweetAlert.fire({
                @foreach(session('sweetAlert2') as $key => $value)
                {{ $key }}: @if(is_string(gettype($value))) '{{ $value }}' @else {{ $value ?? 'undefined' }}  @endif,
                @endforeach
            });
        }
    </script>
@endif
